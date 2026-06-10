<?php
/**
 * Green card issuance service.
 *
 * Handles generation of registration number (if missing), QR metadata,
 * HTML template rendering, Dompdf PDF generation, and workflow transition.
 */

use Dompdf\Dompdf;
use Dompdf\Options;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;

/**
 * Issue (or return existing) green card for a submission.
 *
 * Must be called inside an existing transaction.
 */
function issue_green_card_for_submission(PDO $db, int $submissionId, int $issuedByUserId, string $actorDepartment = 'system'): array
{
    $intakeIdSelect = column_exists($db, 'document_submissions', 'intake_id')
        ? 'ds.intake_id'
        : 'NULL AS intake_id';

    $stmt = $db->prepare("
        SELECT ds.submission_id, ds.user_id, ds.status, ds.registration_number,
               ds.full_name, ds.program, ds.faculty, ds.passport_photo_path,
               ds.intake_year, ds.intake_semester, {$intakeIdSelect},
               u.admission_number,
               av.is_approved AS admissions_approved,
               fc.is_cleared, fc.forwarded_to_admissions,
               gc.card_id, gc.card_number, gc.pdf_path
        FROM document_submissions ds
        INNER JOIN users u ON ds.user_id = u.user_id
        LEFT JOIN admissions_verifications av ON ds.submission_id = av.submission_id
        LEFT JOIN finance_clearances fc ON ds.submission_id = fc.submission_id
        LEFT JOIN green_cards gc ON ds.submission_id = gc.submission_id
        WHERE ds.submission_id = :submission_id
        FOR UPDATE
    ");
    $stmt->execute(['submission_id' => $submissionId]);
    $submission = $stmt->fetch();

    if (!$submission) {
        throw new Exception('Submission not found while issuing green card.');
    }

    if ($submission['status'] === STATUS_FINANCE_APPROVED) {
        transition_submission_status(
            $db,
            $submissionId,
            STATUS_FINANCE_APPROVED,
            STATUS_PENDING_GREENCARD,
            $issuedByUserId,
            'system',
            'Normalized legacy state for strict workflow'
        );
        $submission['status'] = STATUS_PENDING_GREENCARD;
    }

    if (!empty($submission['card_id'])) {
        return [
            'created' => false,
            'card_id' => (int)$submission['card_id'],
            'card_number' => (string)$submission['card_number'],
            'registration_number' => (string)$submission['registration_number'],
            'pdf_path' => (string)$submission['pdf_path']
        ];
    }

    if ($submission['status'] !== STATUS_PENDING_GREENCARD) {
        throw new Exception('Invalid state transition. Submission must be in pending_greencard.');
    }

    if (!(int)$submission['admissions_approved']) {
        throw new Exception('Cannot issue green card before Admissions approval.');
    }

    if (!(int)$submission['is_cleared'] || !(int)$submission['forwarded_to_admissions']) {
        throw new Exception('Cannot issue green card before Finance clearance and handoff.');
    }

    $registrationNumber = (string)($submission['registration_number'] ?? '');
    $intakeLabel = gc_resolve_intake_label(
        $db,
        $submission['intake_id'] ?? null,
        (int)($submission['user_id'] ?? 0),
        (string)($submission['intake_semester'] ?? '')
    );
    $intakeYearValue = (int)$submission['intake_year'];
    $expectedPrefix = '';
    if ($intakeYearValue > 0) {
        $expectedPrefix = sprintf('%04d-%s-', $intakeYearValue, gc_semester_code($intakeLabel));
    }

    if (
        $registrationNumber === '' ||
        preg_match('/^\d{10}$/', $registrationNumber) === 1 ||
        ($expectedPrefix !== '' && strpos($registrationNumber, $expectedPrefix) !== 0)
    ) {
        gc_acquire_registration_number_lock($db, $intakeYearValue, $intakeLabel);
        $registrationNumber = gc_generate_registration_number(
            $db,
            $intakeYearValue,
            $intakeLabel
        );
        gc_persist_registration_number($db, $submissionId, (int)$submission['user_id'], $registrationNumber);
    }

    $cardNumber = gc_generate_card_number($db);
    $verificationToken = gc_generate_verification_token();
    $verificationTokenHash = gc_hash_verification_token($verificationToken);
    $verificationUrl = PUBLIC_BASE_URL . 'verify_card.php?token=' . rawurlencode($verificationToken);
    $issueDate = date('Y-m-d');
    $validityYears = gc_green_card_validity_years_for_program((string)$submission['program']);
    $expiryDate = date('Y-m-d', strtotime('+' . $validityYears . ' years', strtotime($issueDate)));
    $intakeYear = (int)$submission['intake_year'];
    if ($intakeYear <= 0) {
        $intakeYear = (int)date('Y');
    }
    $academicYear = gc_green_card_academic_year($intakeYear, (string)$submission['program']);
    $semester = 'semester_1';
    $studyYear = max(1, ((int)date('Y')) - ((int)$submission['intake_year']) + 1);

    $qr = gc_generate_qr_code_asset($verificationUrl, $cardNumber);
    $photoSrc = gc_image_source_from_relative_path((string)$submission['passport_photo_path']);
    if ($photoSrc === null) {
        $photoSrc = gc_placeholder_photo_data_uri();
    }

    $templateData = [
        'card_number' => $cardNumber,
        'full_name' => (string)$submission['full_name'],
        'registration_number' => $registrationNumber,
        'admission_number' => (string)$submission['admission_number'],
        'course' => (string)$submission['program'],
        'college' => (string)$submission['faculty'],
        'department' => (string)$submission['faculty'],
        'semester' => $semester,
        'study_year' => (string)$studyYear,
        'academic_year' => $academicYear,
        'director_signature' => 'DIRECTOR SIGNATURE',
        'director_signature_image' => gc_resolve_director_signature_image_src(),
        'issue_date' => $issueDate,
        'expiry_date' => $expiryDate,
        'verification_url' => $verificationUrl,
        'photo_src' => $photoSrc,
        'qr_src' => $qr['image_src']
    ];

    $pdfPath = gc_generate_green_card_pdf($templateData);

    $qrData = json_encode([
        'registration_number' => $registrationNumber,
        'issue_date' => $issueDate,
        'card_number' => $cardNumber,
        'verification_url' => $verificationUrl,
        'verification_token_hash' => $verificationTokenHash
    ], JSON_UNESCAPED_SLASHES);

    $greenCardColumns = [
        'submission_id', 'user_id', 'registration_number', 'card_number',
        'qr_code_data', 'qr_code_image_path', 'full_name', 'program', 'faculty',
        'student_photo_path', 'issue_date', 'expiry_date', 'academic_year', 'semester',
        'pdf_path', 'issued_by_user_id'
    ];
    $greenCardParams = [
        'submission_id' => $submissionId,
        'user_id' => (int)$submission['user_id'],
        'registration_number' => $registrationNumber,
        'card_number' => $cardNumber,
        'qr_code_data' => (string)$qrData,
        'qr_code_image_path' => $qr['relative_path'],
        'full_name' => (string)$submission['full_name'],
        'program' => (string)$submission['program'],
        'faculty' => (string)$submission['faculty'],
        'student_photo_path' => (string)$submission['passport_photo_path'],
        'issue_date' => $issueDate,
        'expiry_date' => $expiryDate,
        'academic_year' => $academicYear,
        'semester' => $semester,
        'pdf_path' => $pdfPath,
        'issued_by_user_id' => $issuedByUserId
    ];

    if (column_exists($db, 'green_cards', 'verification_token_hash')) {
        $greenCardColumns[] = 'verification_token_hash';
        $greenCardParams['verification_token_hash'] = $verificationTokenHash;
    }

    $greenCardPlaceholders = array_map(function ($column) {
        return ':' . $column;
    }, $greenCardColumns);

    $insert = $db->prepare(
        'INSERT INTO green_cards (' . implode(', ', $greenCardColumns) . ') VALUES (' . implode(', ', $greenCardPlaceholders) . ')'
    );
    $insert->execute($greenCardParams);

    $department = in_array($actorDepartment, ['student', 'admissions', 'finance', 'system'], true)
        ? $actorDepartment
        : 'system';

    transition_submission_status(
        $db,
        $submissionId,
        STATUS_PENDING_GREENCARD,
        STATUS_GREENCARD_ISSUED,
        $issuedByUserId,
        $department,
        "Green card issued automatically. Reg#: {$registrationNumber}, Card#: {$cardNumber}"
    );

    return [
        'created' => true,
        'card_id' => (int)$db->lastInsertId(),
        'card_number' => $cardNumber,
        'registration_number' => $registrationNumber,
        'pdf_path' => $pdfPath
    ];
}

function gc_generate_verification_token(): string
{
    return rtrim(strtr(base64_encode(random_bytes(32)), '+/', '-_'), '=');
}

function gc_hash_verification_token(string $token): string
{
    return hash_hmac('sha256', $token, JWT_SECRET);
}

function gc_extract_verification_url_from_qr_data($qrCodeData): string
{
    $raw = trim((string)$qrCodeData);
    if ($raw === '') {
        return '';
    }

    $decoded = json_decode($raw, true);
    if (is_array($decoded) && !empty($decoded['verification_url'])) {
        return (string)$decoded['verification_url'];
    }

    if (preg_match('/^https?:\/\//i', $raw)) {
        return $raw;
    }

    return '';
}

function gc_ensure_verification_token(PDO $db, int $cardId): ?string
{
    if (!table_exists($db, 'green_cards') || !column_exists($db, 'green_cards', 'verification_token_hash')) {
        return null;
    }

    $token = gc_generate_verification_token();
    $tokenHash = gc_hash_verification_token($token);
    $verificationUrl = PUBLIC_BASE_URL . 'verify_card.php?token=' . rawurlencode($token);

    $stmt = $db->prepare("
        UPDATE green_cards
        SET verification_token_hash = :token_hash,
            qr_code_data = :qr_code_data
        WHERE card_id = :card_id
    ");
    $stmt->execute([
        'token_hash' => $tokenHash,
        'qr_code_data' => json_encode(['verification_url' => $verificationUrl, 'verification_token_hash' => $tokenHash], JSON_UNESCAPED_SLASHES),
        'card_id' => $cardId
    ]);

    return $token;
}

/**
 * Registration format: YYYY-MM-#### (e.g. 2026-01-1001).
 */
function gc_generate_registration_number(PDO $db, int $intakeYear = 0, string $intakeLabel = ''): string
{
    $year = $intakeYear > 0 ? $intakeYear : (int)date('Y');
    if ($year < 2000 || $year > 2100) {
        $year = (int)date('Y');
    }

    $monthCode = gc_semester_code($intakeLabel);
    $prefix = sprintf('%04d-%s-', $year, $monthCode);

    $stmt = $db->prepare("
        SELECT registration_number
        FROM green_cards
        WHERE registration_number LIKE :prefix
        ORDER BY registration_number DESC
        LIMIT 1
        FOR UPDATE
    ");
    $stmt->execute(['prefix' => $prefix . '%']);
    $last = $stmt->fetchColumn();

    $next = 1001;
    if (is_string($last) && preg_match('/^\d{4}-\d{2}-(\d{4})$/', $last, $m)) {
        $next = ((int)$m[1]) + 1;
    }

    for ($attempt = 0; $attempt < 50; $attempt++) {
        $candidate = $prefix . str_pad((string)($next + $attempt), 4, '0', STR_PAD_LEFT);
        $check = $db->prepare("
            SELECT
                (SELECT COUNT(*) FROM green_cards WHERE registration_number = :green_card_registration_number) +
                (SELECT COUNT(*) FROM document_submissions WHERE registration_number = :submission_registration_number)
        ");
        $check->execute([
            'green_card_registration_number' => $candidate,
            'submission_registration_number' => $candidate
        ]);
        if ((int)$check->fetchColumn() === 0) {
            return $candidate;
        }
    }

    throw new Exception('Unable to generate unique registration number.');
}

function gc_acquire_registration_number_lock(PDO $db, int $intakeYear, string $intakeLabel): string
{
    $year = $intakeYear > 0 ? $intakeYear : (int)date('Y');
    if ($year < 2000 || $year > 2100) {
        $year = (int)date('Y');
    }

    $lockName = sprintf('kiu_registration_%04d_%s', $year, gc_semester_code($intakeLabel));
    $stmt = $db->prepare('SELECT GET_LOCK(:lock_name, 10)');
    $stmt->execute(['lock_name' => $lockName]);
    if ((int)$stmt->fetchColumn() !== 1) {
        throw new Exception('Unable to reserve the next registration number. Please try again.');
    }

    register_shutdown_function(function () use ($db, $lockName) {
        gc_release_registration_number_lock($db, $lockName);
    });

    return $lockName;
}

function gc_release_registration_number_lock(PDO $db, ?string $lockName): void
{
    if (!$lockName) {
        return;
    }

    try {
        $stmt = $db->prepare('SELECT RELEASE_LOCK(:lock_name)');
        $stmt->execute(['lock_name' => $lockName]);
    } catch (Throwable $e) {
        error_log('Failed to release registration number lock: ' . $e->getMessage());
    }
}

function gc_semester_code(string $semester): string
{
    $normalized = strtolower(trim($semester));
    if (strpos($normalized, 'january') !== false || preg_match('/(^|[^0-9])01([^0-9]|$)/', $normalized) === 1) {
        return '01';
    }
    if (strpos($normalized, 'april') !== false || preg_match('/(^|[^0-9])04([^0-9]|$)/', $normalized) === 1) {
        return '04';
    }
    if (strpos($normalized, 'august') !== false || preg_match('/(^|[^0-9])08([^0-9]|$)/', $normalized) === 1) {
        return '08';
    }
    if ($normalized === 'semester_1' || $normalized === 'semester 1' || $normalized === '1') {
        return '01';
    }
    return '01';
}

function gc_green_card_validity_years_for_program(string $program): int
{
    $normalized = strtolower(trim($program));

    if (
        strpos($normalized, 'bachelor') !== false ||
        preg_match('/\bbsc\b|\bba\b|\bllb\b/', $normalized) === 1
    ) {
        return 3;
    }

    if (
        strpos($normalized, 'certificate') !== false ||
        strpos($normalized, 'diploma') !== false ||
        strpos($normalized, 'pgd') !== false ||
        strpos($normalized, 'postgraduate diploma') !== false ||
        strpos($normalized, 'master') !== false ||
        strpos($normalized, 'phd') !== false ||
        strpos($normalized, 'ph.d') !== false ||
        strpos($normalized, 'doctor of philosophy') !== false
    ) {
        return 2;
    }

    return max(1, (int)(defined('GREEN_CARD_VALIDITY_YEARS') ? GREEN_CARD_VALIDITY_YEARS : 1));
}

function gc_green_card_academic_year(int $startYear, string $program): string
{
    if ($startYear <= 0) {
        $startYear = (int)date('Y');
    }

    $validityYears = gc_green_card_validity_years_for_program($program);
    return $startYear . '/' . ($startYear + $validityYears);
}

function gc_resolve_intake_label(PDO $db, $intakeId, int $userId, string $fallback): string
{
    $intakeId = (int)$intakeId;
    if ($intakeId <= 0 && $userId > 0 && table_exists($db, 'student_profiles') && column_exists($db, 'student_profiles', 'intake_id')) {
        try {
            $stmt = $db->prepare('SELECT intake_id FROM student_profiles WHERE user_id = :user_id LIMIT 1');
            $stmt->execute(['user_id' => $userId]);
            $profileIntakeId = (int)($stmt->fetchColumn() ?? 0);
            if ($profileIntakeId > 0) {
                $intakeId = $profileIntakeId;
            }
        } catch (Exception $e) {
            error_log('Failed to resolve profile intake: ' . $e->getMessage());
        }
    }

    if ($intakeId > 0 && table_exists($db, 'intakes')) {
        try {
            $stmt = $db->prepare('SELECT intake_name FROM intakes WHERE intake_id = :intake_id LIMIT 1');
            $stmt->execute(['intake_id' => $intakeId]);
            $name = (string)($stmt->fetchColumn() ?? '');
            if ($name !== '') {
                return $name;
            }
        } catch (Exception $e) {
            error_log('Failed to resolve intake label: ' . $e->getMessage());
        }
    }

    return $fallback !== '' ? $fallback : 'semester_1';
}

function gc_persist_registration_number(PDO $db, int $submissionId, int $userId, string $registrationNumber): void
{
    $stmt = $db->prepare("
        UPDATE document_submissions
        SET registration_number = :registration_number
        WHERE submission_id = :submission_id
    ");
    $stmt->execute([
        'registration_number' => $registrationNumber,
        'submission_id' => $submissionId
    ]);

    $stmt = $db->prepare("
        UPDATE student_profiles
        SET registration_number = :registration_number
        WHERE user_id = :user_id
    ");
    $stmt->execute([
        'registration_number' => $registrationNumber,
        'user_id' => $userId
    ]);

    $stmt = $db->prepare("
        UPDATE admissions_verifications
        SET registration_number = :registration_number,
            registration_generated_at = NOW()
        WHERE submission_id = :submission_id
    ");
    $stmt->execute([
        'registration_number' => $registrationNumber,
        'submission_id' => $submissionId
    ]);
}

function gc_generate_card_number(PDO $db): string
{
    $prefix = 'GC' . date('Y');

    $stmt = $db->prepare("
        SELECT card_number
        FROM green_cards
        WHERE card_number LIKE :prefix
        ORDER BY card_number DESC
        LIMIT 1
        FOR UPDATE
    ");
    $stmt->execute(['prefix' => $prefix . '%']);
    $last = $stmt->fetchColumn();

    $next = 1;
    if ($last) {
        $next = ((int)substr((string)$last, strlen($prefix))) + 1;
    }

    for ($attempt = 0; $attempt < 50; $attempt++) {
        $candidate = $prefix . str_pad((string)($next + $attempt), 6, '0', STR_PAD_LEFT);
        $check = $db->prepare("SELECT COUNT(*) FROM green_cards WHERE card_number = :card_number");
        $check->execute(['card_number' => $candidate]);
        if ((int)$check->fetchColumn() === 0) {
            return $candidate;
        }
    }

    throw new Exception('Unable to generate unique green card number.');
}

function gc_generate_qr_code_asset(string $payload, string $cardNumber): array
{
    $relativePath = 'uploads/qr_codes/' . $cardNumber . '.png';
    $absolutePath = QR_CODE_DIR . $cardNumber . '.png';

    // Ensure directory exists
    if (!is_dir(QR_CODE_DIR)) {
        mkdir(QR_CODE_DIR, 0755, true);
    }

    // Try remote API first (faster for first-time generation)
    $remoteUrl = 'https://api.qrserver.com/v1/create-qr-code/?size=260x260&data=' . rawurlencode($payload);
    $content = @file_get_contents($remoteUrl);
    
    if ($content !== false && @file_put_contents($absolutePath, $content) !== false) {
        // Remote API succeeded and file saved
        $localImage = gc_image_source_from_relative_path($relativePath);
        if ($localImage !== null) {
            return [
                'relative_path' => $relativePath,
                'image_src' => $localImage
            ];
        }
    }

    // If remote API failed or local save failed, generate locally using endroid/qr-code
    // This ensures permanent, self-contained QR code generation
    try {
        $qrCode = new QrCode($payload);
        $qrCode->setSize(260);
        $qrCode->setMargin(10);
        
        $writer = new PngWriter();
        $result = $writer->write($qrCode);
        
        if (@file_put_contents($absolutePath, $result->getString()) !== false) {
            $localImage = gc_image_source_from_relative_path($relativePath);
            if ($localImage !== null) {
                return [
                    'relative_path' => $relativePath,
                    'image_src' => $localImage
                ];
            }
        }
    } catch (Throwable $e) {
        error_log('QR code generation error: ' . $e->getMessage());
    }

    // If both methods failed, return empty state instead of remote URL
    // This ensures we don't fall back to an unreliable external dependency
    error_log("Warning: Failed to generate QR code for card $cardNumber");
    return [
        'relative_path' => null,
        'image_src' => ''
    ];
}

function gc_generate_green_card_pdf(array $templateData, string $mode = 'default'): string
{
    $cardNumber = preg_replace('/[^A-Z0-9]/', '', (string)$templateData['card_number']);
    if ($cardNumber === '') {
        throw new Exception('Invalid card number for PDF generation.');
    }

    $relativePath = 'uploads/green_cards/' . $cardNumber . '.pdf';
    $absolutePath = GREEN_CARD_DIR . $cardNumber . '.pdf';

    if (!is_dir(GREEN_CARD_DIR)) {
        mkdir(GREEN_CARD_DIR, 0755, true);
    }

    // Preferred renderer: headless browser for near-identical parity with on-screen card.
    if (gc_headless_browser_pdf_available()) {
        try {
            gc_generate_browser_green_card_pdf($absolutePath, $templateData, $mode);
            if (is_file($absolutePath) && filesize($absolutePath) > 0) {
                return $relativePath;
            }
        } catch (Throwable $e) {
            error_log('Headless browser PDF generation failed, trying Dompdf: ' . $e->getMessage());
        }
    }

    if (gc_dompdf_available()) {
        try {
            $html = gc_render_green_card_template($templateData, $mode);

            $options = new Options();
            $options->set('isRemoteEnabled', true);
            $options->set('isHtml5ParserEnabled', true);
            $options->set('chroot', SITE_ROOT);

            $dompdf = new Dompdf($options);
            $dompdf->loadHtml($html, 'UTF-8');
            $pageBox = gc_green_card_page_box($mode);
            $dompdf->setPaper($pageBox);
            $dompdf->render();

            $output = $dompdf->output();
            if (@file_put_contents($absolutePath, $output) !== false) {
                return $relativePath;
            }
        } catch (Throwable $e) {
            error_log('Dompdf generation failed, using fallback PDF generator: ' . $e->getMessage());
        }
    }

    gc_generate_fallback_green_card_pdf($absolutePath, $templateData, $mode);
    return $relativePath;
}

function gc_render_green_card_template(array $templateData, string $mode = 'default'): string
{
    if (!defined('GREEN_CARD_TEMPLATE') || !is_string(GREEN_CARD_TEMPLATE) || !file_exists(GREEN_CARD_TEMPLATE)) {
        throw new Exception('Green card template not found.');
    }

    $cardData = $templateData;
    $cardData['_card_mode'] = $mode;
    ob_start();
    include GREEN_CARD_TEMPLATE;
    return (string)ob_get_clean();
}

function gc_resolve_director_signature_image_src(): string
{
    $candidates = [
        'assets/img/director_signature.png',
        'assets/img/director_signature.jpg',
        'assets/img/director_signature.jpeg',
        'assets/img/director_signature.webp'
    ];

    foreach ($candidates as $candidate) {
        if (is_file(SITE_ROOT . '/' . $candidate)) {
            $dataSrc = gc_image_source_from_relative_path($candidate);
            if ($dataSrc !== null) {
                return $dataSrc;
            }

            return BASE_URL . $candidate;
        }
    }

    return '';
}

function gc_require_dompdf(): void
{
    if (!gc_dompdf_available()) {
        throw new Exception('Dompdf is required. Install dependency: composer require dompdf/dompdf');
    }
}

function gc_dompdf_available(): bool
{
    if (class_exists(Dompdf::class)) {
        return true;
    }

    $autoloadPath = SITE_ROOT . '/vendor/autoload.php';
    if (file_exists($autoloadPath)) {
        require_once $autoloadPath;
    }

    return class_exists(Dompdf::class);
}

function gc_green_card_page_size_mm(string $mode = 'default'): array
{
    // Download mode uses a more compact size so QR appears right after photo
    if ($mode === 'download') {
        return ['width' => 176.0, 'height' => 95.0];
    }
    // Viewing and issuance mode uses standard size
    return ['width' => 176.0, 'height' => 118.0];
}

function gc_green_card_page_box(string $mode = 'default'): array
{
    $page = gc_green_card_page_size_mm($mode);
    $widthPoints = $page['width'] * 72 / 25.4;
    $heightPoints = $page['height'] * 72 / 25.4;

    return [0, 0, $widthPoints, $heightPoints];
}

function gc_headless_browser_pdf_available(): bool
{
    return gc_find_headless_browser_executable() !== null && function_exists('proc_open');
}

function gc_find_headless_browser_executable(): ?string
{
    static $resolved = false;
    static $browserPath = null;

    if ($resolved) {
        return $browserPath;
    }

    $resolved = true;
    $candidates = [
        'C:\\Program Files\\Google\\Chrome\\Application\\chrome.exe',
        'C:\\Program Files (x86)\\Google\\Chrome\\Application\\chrome.exe',
        'C:\\Program Files (x86)\\Microsoft\\Edge\\Application\\msedge.exe',
        'C:\\Program Files\\Microsoft\\Edge\\Application\\msedge.exe',
        '/usr/bin/google-chrome',
        '/usr/bin/chromium-browser',
        '/usr/bin/chromium'
    ];

    foreach ($candidates as $candidate) {
        $isAvailable = is_file($candidate) && (DIRECTORY_SEPARATOR === '\\' || is_executable($candidate));
        if ($isAvailable) {
            $browserPath = $candidate;
            break;
        }
    }

    return $browserPath;
}

function gc_generate_browser_green_card_pdf(string $absolutePath, array $templateData, string $mode = 'default'): void
{
    $browser = gc_find_headless_browser_executable();
    if ($browser === null) {
        throw new Exception('No supported headless browser was found.');
    }

    $htmlData = $templateData;
    $htmlData['show_back_link'] = false;
    $htmlData['is_print_render'] = true;

    $html = gc_render_green_card_template($htmlData, $mode);
    $tempBase = rtrim((string)sys_get_temp_dir(), '\\/');
    if ($tempBase === '') {
        $tempBase = GREEN_CARD_DIR;
    }

    $token = uniqid('gc_pdf_', true);
    $tempHtml = $tempBase . DIRECTORY_SEPARATOR . $token . '.html';
    $tempProfileDir = $tempBase . DIRECTORY_SEPARATOR . $token . '_profile';

    if (@file_put_contents($tempHtml, $html) === false) {
        throw new Exception('Unable to create temporary HTML for browser PDF rendering.');
    }

    if (!is_dir($tempProfileDir) && !@mkdir($tempProfileDir, 0755, true) && !is_dir($tempProfileDir)) {
        @unlink($tempHtml);
        throw new Exception('Unable to create temporary browser profile directory.');
    }

    try {
        $fileUri = gc_path_to_file_uri($tempHtml);
        $command = implode(' ', [
            gc_shell_escape_arg($browser),
            '--headless=new',
            '--disable-gpu',
            '--no-sandbox',
            '--no-first-run',
            '--disable-extensions',
            '--disable-crashpad-for-testing',
            '--disable-dev-shm-usage',
            '--disable-features=RendererCodeIntegrity',
            '--allow-file-access-from-files',
            '--print-to-pdf-no-header',
            '--user-data-dir=' . gc_shell_escape_arg($tempProfileDir),
            '--print-to-pdf=' . gc_shell_escape_arg($absolutePath),
            gc_shell_escape_arg($fileUri)
        ]);

        $descriptors = [
            0 => ['pipe', 'r'],
            1 => ['pipe', 'w'],
            2 => ['pipe', 'w']
        ];

        $process = @proc_open($command, $descriptors, $pipes, null, null);
        if (!is_resource($process)) {
            throw new Exception('Unable to start headless browser process.');
        }

        fclose($pipes[0]);
        $stdout = stream_get_contents($pipes[1]);
        $stderr = stream_get_contents($pipes[2]);
        fclose($pipes[1]);
        fclose($pipes[2]);

        $exitCode = proc_close($process);
        if ($exitCode !== 0 || !is_file($absolutePath) || filesize($absolutePath) <= 0) {
            throw new Exception('Headless browser did not create a valid PDF. ' . trim($stdout . ' ' . $stderr));
        }
    } finally {
        @unlink($tempHtml);
        gc_remove_directory_tree($tempProfileDir);
    }
}

function gc_generate_fallback_green_card_pdf(string $absolutePath, array $templateData, string $mode = 'default'): void
{
    $fields = [
        ['STUDENT\'S NAME', (string)($templateData['full_name'] ?? 'N/A')],
        ['REGISTRATION No.', (string)($templateData['registration_number'] ?? 'N/A')],
        ['COURSE', (string)($templateData['course'] ?? 'N/A')],
        ['TERM / SEMESTER', ucwords(str_replace('_', ' ', (string)($templateData['semester'] ?? 'N/A')))],
        ['YEAR', (string)($templateData['study_year'] ?? 'N/A')],
        ['ACADEMIC YEAR', (string)($templateData['academic_year'] ?? 'N/A')],
        ['DEPARTMENT', (string)($templateData['department'] ?? 'N/A')]
    ];

    $pageBox = gc_green_card_page_box($mode);
    $pageWidth = (float)$pageBox[2];
    $pageHeight = (float)$pageBox[3];
    $outerX = 10.0;
    $outerY = 10.0;
    $outerWidth = $pageWidth - 20.0;
    $outerHeight = $pageHeight - 20.0;
    $headerHeight = 46.0;
    $footerHeight = 26.0;
    $photoX = 28.0;
    $photoY = $pageHeight - 154.0;
    $photoWidth = 112.0;
    $photoHeight = 138.0;
    $qrY = 42.0;
    $qrSize = 82.0;
    $contentX = 162.0;
    $lineWidth = $pageWidth - 184.0;

    $stream = '';
    $stream .= "q 0.91 0.97 0.93 rg {$outerX} {$outerY} {$outerWidth} {$outerHeight} re f Q\n";
    $stream .= "0.06 0.32 0.20 RG 1.1 w {$outerX} {$outerY} {$outerWidth} {$outerHeight} re S\n";
    $stream .= "q 0.06 0.42 0.27 rg {$outerX} " . ($pageHeight - $headerHeight - $outerY) . " {$outerWidth} {$headerHeight} re f Q\n";
    $stream .= "q 0.60 0.76 0.65 rg BT /F2 84 Tf 0.866 0.50 -0.50 0.866 120 126 Tm (KIU) Tj ET Q\n";
    $stream .= "0 0 0 rg\n";
    $stream .= "0 0 0 RG\n";

    $stream .= "BT /F2 15 Tf 126 " . ($pageHeight - 26) . " Tm 1 1 1 rg (" . gc_pdf_escape_text(gc_pdf_ascii('Kampala International University')) . ") Tj ET\n";
    $stream .= "BT /F2 11 Tf 187 " . ($pageHeight - 42) . " Tm 1 1 1 rg (" . gc_pdf_escape_text(gc_pdf_ascii('STUDENT GREEN CARD')) . ") Tj ET\n";

    $stream .= "0.43 0.65 0.51 RG 0.8 w {$photoX} {$photoY} {$photoWidth} {$photoHeight} re S\n";
    $stream .= "BT /F2 9 Tf 67 " . ($photoY + 66) . " Tm 0 0 0 rg (" . gc_pdf_escape_text(gc_pdf_ascii('PHOTO')) . ") Tj ET\n";
    $stream .= "BT /F2 8 Tf {$photoX} " . ($photoY - 16) . " Tm 0 0 0 rg (" . gc_pdf_escape_text(gc_pdf_ascii('ADM NO: ' . (string)($templateData['admission_number'] ?? 'N/A'))) . ") Tj ET\n";

    $stream .= "0.43 0.65 0.51 RG 0.8 w {$photoX} {$qrY} {$qrSize} {$qrSize} re S\n";
    $stream .= "BT /F2 9 Tf 57 " . ($qrY + 38) . " Tm 0 0 0 rg (" . gc_pdf_escape_text(gc_pdf_ascii('QR')) . ") Tj ET\n";

    $y = $pageHeight - 72.0;
    foreach ($fields as $field) {
        $label = gc_pdf_escape_text(gc_pdf_ascii($field[0]));
        $value = gc_pdf_escape_text(gc_pdf_ascii($field[1]));
        $stream .= "BT /F2 8 Tf {$contentX} {$y} Tm 0 0 0 rg ({$label}) Tj ET\n";
        $stream .= "BT /F2 12 Tf {$contentX} " . ($y - 15) . " Tm 0 0 0 rg ({$value}) Tj ET\n";
        $stream .= "0.49 0.66 0.55 RG 0.55 w {$contentX} " . ($y - 20) . " m " . ($contentX + $lineWidth) . " " . ($y - 20) . " l S\n";
        $y -= 33.0;
    }

    $statementA = 'THIS IS TO CERTIFY THAT THE ABOVE NAMED HAS REGISTERED AS A STUDENT';
    $statementB = 'OF THE STATED COURSE FOR THE ACADEMIC YEAR INDICATED ABOVE.';
    $stream .= "BT /F2 7 Tf {$contentX} 56 Tm 0 0 0 rg (" . gc_pdf_escape_text(gc_pdf_ascii($statementA)) . ") Tj ET\n";
    $stream .= "BT /F2 7 Tf {$contentX} 44 Tm 0 0 0 rg (" . gc_pdf_escape_text(gc_pdf_ascii($statementB)) . ") Tj ET\n";
    $stream .= "BT /F2 8 Tf {$contentX} 30 Tm 0 0 0 rg (" . gc_pdf_escape_text(gc_pdf_ascii('CERTIFICATE ISSUED BY')) . ") Tj ET\n";
    $stream .= "0.25 0.44 0.32 RG 0.8 w " . ($contentX + 104) . " 26 m " . ($contentX + 218) . " 26 l S\n";
    $stream .= "BT /F2 8 Tf " . ($contentX + 114) . " 32 Tm 0 0 0 rg (" . gc_pdf_escape_text(gc_pdf_ascii((string)($templateData['director_signature'] ?? 'DIRECTOR SIGNATURE'))) . ") Tj ET\n";
    $stream .= "BT /F2 7 Tf " . ($contentX + 116) . " 16 Tm 0 0 0 rg (" . gc_pdf_escape_text(gc_pdf_ascii('DIRECTOR OF ADMISSIONS')) . ") Tj ET\n";

    $stream .= "q 0.91 0.97 0.93 rg {$outerX} {$outerY} {$outerWidth} {$footerHeight} re f Q\n";
    $stream .= "0.65 0.79 0.70 RG 0.6 w {$outerX} " . ($outerY + $footerHeight) . " m " . ($outerX + $outerWidth) . " " . ($outerY + $footerHeight) . " l S\n";
    $stream .= "BT /F1 7.6 Tf 24 17 Tm 0 0 0 rg (" . gc_pdf_escape_text(gc_pdf_ascii('Issued: ' . (string)($templateData['issue_date_display'] ?? $templateData['issue_date'] ?? ''))) . ") Tj ET\n";
    $stream .= "BT /F2 7.6 Tf " . ($pageWidth - 112) . " 17 Tm 0.04 0.36 0.23 rg (" . gc_pdf_escape_text(gc_pdf_ascii('Official Green Card')) . ") Tj ET\n";

    $objects = [];
    $objects[1] = "<< /Type /Catalog /Pages 2 0 R >>";
    $objects[2] = "<< /Type /Pages /Kids [3 0 R] /Count 1 >>";
    $objects[3] = "<< /Type /Page /Parent 2 0 R /MediaBox [0 0 {$pageWidth} {$pageHeight}] /Contents 4 0 R /Resources << /Font << /F1 5 0 R /F2 6 0 R >> >> >>";
    $objects[4] = "<< /Length " . strlen($stream) . " >>\nstream\n" . $stream . "\nendstream";
    $objects[5] = "<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica >>";
    $objects[6] = "<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica-Bold >>";

    $pdf = "%PDF-1.4\n";
    $offsets = [];
    $count = count($objects);

    for ($i = 1; $i <= $count; $i++) {
        $offsets[$i] = strlen($pdf);
        $pdf .= $i . " 0 obj\n" . $objects[$i] . "\nendobj\n";
    }

    $xrefOffset = strlen($pdf);
    $pdf .= "xref\n0 " . ($count + 1) . "\n";
    $pdf .= "0000000000 65535 f \n";
    for ($i = 1; $i <= $count; $i++) {
        $pdf .= sprintf("%010d 00000 n \n", $offsets[$i]);
    }
    $pdf .= "trailer << /Size " . ($count + 1) . " /Root 1 0 R >>\n";
    $pdf .= "startxref\n" . $xrefOffset . "\n%%EOF";

    if (@file_put_contents($absolutePath, $pdf) === false) {
        throw new Exception('Failed to persist green card PDF.');
    }
}

function gc_pdf_escape_text(string $text): string
{
    return str_replace(['\\', '(', ')'], ['\\\\', '\\(', '\\)'], $text);
}

function gc_pdf_ascii(string $text): string
{
    $converted = @iconv('UTF-8', 'ISO-8859-1//TRANSLIT//IGNORE', $text);
    if ($converted === false || $converted === '') {
        $converted = preg_replace('/[^\x20-\x7E]/', '?', $text);
        if (!is_string($converted)) {
            return '';
        }
    }
    return $converted;
}

function gc_image_source_from_relative_path(string $relativePath): ?string
{
    $normalized = gc_normalize_relative_path($relativePath);
    if ($normalized === null) {
        return null;
    }

    $absolutePath = SITE_ROOT . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $normalized);
    if (!is_file($absolutePath) || !is_readable($absolutePath)) {
        return null;
    }

    $mime = @mime_content_type($absolutePath);
    if (!$mime || strpos((string)$mime, 'image/') !== 0) {
        return null;
    }

    $raw = @file_get_contents($absolutePath);
    if ($raw === false) {
        return null;
    }

    return 'data:' . $mime . ';base64,' . base64_encode($raw);
}

function gc_normalize_relative_path(string $path): ?string
{
    $trimmed = trim(str_replace('\\', '/', $path));
    if ($trimmed === '' || strpos($trimmed, '..') !== false) {
        return null;
    }

    if (preg_match('/^[a-z]+:\/\//i', $trimmed)) {
        return null;
    }

    return ltrim($trimmed, '/');
}

function gc_placeholder_photo_data_uri(): string
{
    $svg = '<svg xmlns="http://www.w3.org/2000/svg" width="200" height="240" viewBox="0 0 200 240"><rect width="200" height="240" fill="#f0f4f8"/><circle cx="100" cy="85" r="42" fill="#c9d2dc"/><rect x="45" y="140" width="110" height="62" rx="12" fill="#c9d2dc"/><text x="100" y="224" text-anchor="middle" font-family="Arial" font-size="14" fill="#5a6876">No Photo</text></svg>';
    return 'data:image/svg+xml;base64,' . base64_encode($svg);
}

function gc_path_to_file_uri(string $path): string
{
    $normalized = str_replace('\\', '/', $path);

    if (preg_match('/^[A-Za-z]:\//', $normalized) === 1) {
        return 'file:///' . str_replace('%2F', '/', rawurlencode($normalized));
    }

    return 'file://' . $normalized;
}

function gc_shell_escape_arg(string $value): string
{
    if (DIRECTORY_SEPARATOR === '\\') {
        return '"' . str_replace('"', '""', $value) . '"';
    }

    return escapeshellarg($value);
}

function gc_remove_directory_tree(string $path): void
{
    if ($path === '' || !is_dir($path)) {
        return;
    }

    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($path, FilesystemIterator::SKIP_DOTS),
        RecursiveIteratorIterator::CHILD_FIRST
    );

    foreach ($iterator as $item) {
        if ($item->isDir()) {
            @rmdir($item->getPathname());
        } else {
            @unlink($item->getPathname());
        }
    }

    @rmdir($path);
}
