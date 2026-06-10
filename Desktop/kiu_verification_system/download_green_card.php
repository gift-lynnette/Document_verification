<?php
if (!ob_get_level()) {
    ob_start();
}

require_once __DIR__ . '/config/init.php';
require_login();

$cardIdRaw = $_GET['id'] ?? '';
if (!is_string($cardIdRaw) || !ctype_digit($cardIdRaw)) {
    http_response_code(400);
    exit('Invalid request.');
}

$mode = isset($_GET['mode']) ? strtolower(trim((string)$_GET['mode'])) : 'download';
if (!in_array($mode, ['view', 'pdf', 'card', 'download'], true)) {
    $mode = 'download';
}

$cardId = (int)$cardIdRaw;
$greenCardTokenSelect = column_exists($db, 'green_cards', 'verification_token_hash')
    ? 'gc.qr_code_data, gc.verification_token_hash,'
    : 'gc.qr_code_data,';
$stmt = $db->prepare("
    SELECT gc.card_id, gc.submission_id, gc.user_id, gc.card_number, gc.pdf_path,
           gc.registration_number, gc.full_name, gc.program, gc.faculty, gc.student_photo_path,
           gc.issue_date, gc.expiry_date, gc.academic_year, gc.semester, gc.qr_code_image_path,
           ds.intake_year,
           {$greenCardTokenSelect}
           u.admission_number
    FROM green_cards gc
    LEFT JOIN document_submissions ds ON ds.submission_id = gc.submission_id
    LEFT JOIN users u ON u.user_id = gc.user_id
    WHERE gc.card_id = :card_id
    LIMIT 1
");
$stmt->execute(['card_id' => $cardId]);
$card = $stmt->fetch();

if (!$card) {
    http_response_code(404);
    exit('Green card not found.');
}

$role = $_SESSION['role'] ?? '';
$sessionUserId = (int)($_SESSION['user_id'] ?? 0);
$isOwnerStudent = ($role === ROLE_STUDENT) && ($sessionUserId === (int)$card['user_id']);
$isAdminUser = in_array($role, [ROLE_ADMIN, ROLE_REGISTRAR], true);

if (!$isOwnerStudent && !$isAdminUser) {
    http_response_code(403);
    exit('Access denied.');
}

$validityYears = gc_green_card_validity_years_for_program((string)($card['program'] ?? ''));
$issueTimestamp = strtotime((string)($card['issue_date'] ?? ''));
if ($issueTimestamp === false) {
    $issueTimestamp = time();
}

$expectedExpiryRaw = date('Y-m-d', strtotime('+' . $validityYears . ' years', $issueTimestamp));
$baseAcademicYear = (int)date('Y', $issueTimestamp);
$existingAcademicYear = trim((string)($card['academic_year'] ?? ''));
if (preg_match('/^(\d{4})\/(\d{4})$/', $existingAcademicYear, $parts)) {
    $baseAcademicYear = (int)$parts[1];
} elseif ((int)($card['intake_year'] ?? 0) > 0) {
    $baseAcademicYear = (int)$card['intake_year'];
}
$expectedAcademicYear = gc_green_card_academic_year($baseAcademicYear, (string)($card['program'] ?? ''));

$needsCardFieldRefresh =
    ((string)($card['expiry_date'] ?? '') !== $expectedExpiryRaw) ||
    ($existingAcademicYear !== $expectedAcademicYear);

if ($needsCardFieldRefresh) {
    try {
        $refreshStmt = $db->prepare(
            'UPDATE green_cards SET expiry_date = :expiry_date, academic_year = :academic_year WHERE card_id = :card_id'
        );
        $refreshStmt->execute([
            'expiry_date' => $expectedExpiryRaw,
            'academic_year' => $expectedAcademicYear,
            'card_id' => $cardId
        ]);
        $card['expiry_date'] = $expectedExpiryRaw;
        $card['academic_year'] = $expectedAcademicYear;
    } catch (Throwable $e) {
        error_log('Green card field refresh failed: ' . $e->getMessage());
    }
}

if ($mode === 'download') {
    $update = $db->prepare("
        UPDATE green_cards
        SET download_count = download_count + 1,
            downloaded_at = COALESCE(downloaded_at, NOW())
        WHERE card_id = :card_id
    ");
    $update->execute(['card_id' => $cardId]);
}

$semesterLabel = ucwords(str_replace('_', ' ', (string)($card['semester'] ?? '')));
$academicYear = trim((string)($card['academic_year'] ?? ''));

$issueDateLabel = 'N/A';
$issueDateRaw = (string)($card['issue_date'] ?? '');
if ($issueDateRaw !== '' && strtotime($issueDateRaw) !== false) {
    $issueDateLabel = date('d M Y', strtotime($issueDateRaw));
}

$expiryDateLabel = 'N/A';
$expiryDateRaw = (string)($card['expiry_date'] ?? '');
if ($expiryDateRaw !== '' && strtotime($expiryDateRaw) !== false) {
    $expiryDateLabel = date('d M Y', strtotime($expiryDateRaw));
}

$studyYear = '';
if (preg_match('/^(\d{4})\/(\d{4})$/', $academicYear, $m)) {
    $startYear = (int)$m[1];
    $issueYear = (int)date('Y', $issueTimestamp);
    $studyYear = (string)max(1, $issueYear - $startYear + 1);
}

$fullName = trim((string)($card['full_name'] ?? ''));
$registrationNo = trim((string)($card['registration_number'] ?? ''));
$admissionNumber = trim((string)($card['admission_number'] ?? ''));
$courseName = trim((string)($card['program'] ?? ''));
$departmentName = trim((string)($card['faculty'] ?? ''));

if ($fullName === '') { $fullName = 'N/A'; }
if ($registrationNo === '') { $registrationNo = 'N/A'; }
if ($admissionNumber === '') { $admissionNumber = 'N/A'; }
if ($courseName === '') { $courseName = 'N/A'; }
if ($semesterLabel === '') { $semesterLabel = 'N/A'; }
if ($studyYear === '') { $studyYear = 'N/A'; }
if ($academicYear === '') { $academicYear = 'N/A'; }
if ($departmentName === '') { $departmentName = 'N/A'; }

$verificationUrl = gc_extract_verification_url_from_qr_data($card['qr_code_data'] ?? '');
if ($verificationUrl === '' || strpos($verificationUrl, 'verify_card.php?token=') === false) {
    $freshToken = gc_ensure_verification_token($db, $cardId);
    $verificationUrl = $freshToken !== null
        ? PUBLIC_BASE_URL . 'verify_card.php?token=' . rawurlencode($freshToken)
        : PUBLIC_BASE_URL . 'verify_card.php?reg=' . rawurlencode((string)$card['registration_number']);
}
$qrPayload = gc_generate_qr_code_asset($verificationUrl, (string)$card['card_number']);

$photoSrc = gc_image_source_from_relative_path((string)($card['student_photo_path'] ?? ''));
if ($photoSrc === null) {
    $photoSrc = gc_placeholder_photo_data_uri();
}

$templateData = [
    'card_number' => (string)($card['card_number'] ?? ''),
    'full_name' => $fullName,
    'registration_number' => $registrationNo,
    'admission_number' => $admissionNumber,
    'course' => $courseName,
    'college' => $departmentName,
    'department' => $departmentName,
    'semester' => (string)($card['semester'] ?? ''),
    'study_year' => $studyYear,
    'academic_year' => $academicYear,
    'director_signature' => 'DIRECTOR SIGNATURE',
    'director_signature_image' => gc_resolve_director_signature_image_src(),
    'issue_date' => (string)($card['issue_date'] ?? date('Y-m-d')),
    'expiry_date' => (string)($card['expiry_date'] ?? date('Y-m-d')),
    'issue_date_display' => $issueDateLabel,
    'expiry_date_display' => $expiryDateLabel,
    'verification_url' => $verificationUrl,
    'photo_src' => $photoSrc,
    'qr_src' => (string)($qrPayload['image_src'] ?? ''),
    'show_back_link' => $mode === 'card',
    'back_url' => BASE_URL . 'modules/student/dashboard.php'
];

if ($mode === 'card') {
    if (ob_get_length()) {
        ob_clean();
    }

    header('Content-Type: text/html; charset=UTF-8');
    echo gc_render_green_card_template($templateData);
    exit;
}

try {
    $templateData['show_back_link'] = false;
    $newPdfPath = gc_generate_green_card_pdf($templateData, 'download');
    if (is_string($newPdfPath) && $newPdfPath !== '') {
        $card['pdf_path'] = $newPdfPath;
        $db->prepare(
            'UPDATE green_cards SET pdf_path = :pdf_path, qr_code_image_path = :qr_code_image_path WHERE card_id = :card_id'
        )->execute([
            'pdf_path' => $newPdfPath,
            'qr_code_image_path' => (string)($qrPayload['relative_path'] ?? ($card['qr_code_image_path'] ?? '')),
            'card_id' => $cardId
        ]);
    }
} catch (Throwable $e) {
    error_log('Green card PDF regeneration failed: ' . $e->getMessage());
}

$relativePath = trim(str_replace('\\', '/', (string)$card['pdf_path']));
if (
    $relativePath === '' ||
    strpos($relativePath, '..') !== false ||
    strpos($relativePath, 'uploads/green_cards/') !== 0
) {
    http_response_code(404);
    exit('Card file path is invalid.');
}

$baseDir = realpath(GREEN_CARD_DIR);
$absolutePath = realpath(SITE_ROOT . '/' . $relativePath);

if (
    $baseDir === false ||
    $absolutePath === false ||
    strpos(strtolower($absolutePath), strtolower($baseDir)) !== 0 ||
    !is_file($absolutePath) ||
    !is_readable($absolutePath)
) {
    http_response_code(404);
    exit('Card file not found.');
}

while (ob_get_level() > 0) {
    ob_end_clean();
}

header('Cache-Control: private, no-transform, no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');
header('Expires: 0');
header('Content-Type: application/pdf');

$disposition = ($mode === 'download') ? 'attachment' : 'inline';
$fileName = basename((string)$card['card_number']) . '.pdf';
header('Content-Disposition: ' . $disposition . '; filename="' . $fileName . '"; filename*=UTF-8\'\'' . rawurlencode($fileName));

if ($mode === 'download') {
    header('Content-Description: File Transfer');
    header('Content-Transfer-Encoding: binary');
    header('Accept-Ranges: bytes');
}

header('Content-Length: ' . (string)filesize($absolutePath));
header('X-Content-Type-Options: nosniff');
readfile($absolutePath);
exit;
