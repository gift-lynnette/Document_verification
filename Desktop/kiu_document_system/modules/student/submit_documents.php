<?php
require_once '../../config/init.php';
require_once '../../includes/document_analyzer.php';
require_login();
require_role(ROLE_STUDENT);

// Program Catalog
$program_catalog = [
    'certificate' => [
        'Computer Science Certificate',
        'Business Management Certificate',
        'Information Technology Certificate',
        'Accounting Certificate'
    ],
    'bachelors' => [
        'Bachelor of Science in Information Technology',
        'Bachelor of Science in Computer Science',
        'Bachelor of Business Administration',
        'Bachelor of Accounting',
        'Bachelor of Education (Science)',
        'Bachelor of Engineering',
        'Bachelor of Law',
        'Bachelor of Medicine and Surgery'
    ],
    'pgd' => [
        'PGD in Business Administration',
        'PGD in Information Technology',
        'PGD in Education',
        'PGD in Public Health'
    ],
    'masters' => [
        'Master of Business Administration (MBA)',
        'Master of Science in Information Technology',
        'Master of Science in Computer Science',
        'Master of Science in Public Health',
        'Master of Education',
        'Master of Engineering'
    ],
    'phd' => [
        'Doctor of Philosophy (Ph.D.) in Computer Science',
        'Doctor of Philosophy (Ph.D.) in Business',
        'Doctor of Philosophy (Ph.D.) in Education',
        'Doctor of Philosophy (Ph.D.) in Engineering'
    ]
];

// Main Campus Units
$main_campus_units = [
    'School of mathematics and computing',
    'School of Business and Economics',
    'School of Education',
    'School of Engineering',
    'School of Health Sciences',
    'School of Law',
    'School of Agriculture and Environmental Sciences',
    'School of Humanities and Social Sciences'
];

$allowed_payment_currencies = ['UGX', 'USD'];

$validator = new Validator();
$audit = new AuditLog($db);
$user_id = $_SESSION['user_id'];

// Get current student admission number
$current_admission_number = '';
$stmt = $db->prepare("SELECT admission_number FROM users WHERE user_id = :user_id");
$stmt->execute(['user_id' => $user_id]);
$admissionRow = $stmt->fetch();
if ($admissionRow && !empty($admissionRow['admission_number'])) {
    $current_admission_number = (string)$admissionRow['admission_number'];
}

// Get student profile
$profile = null;
$stmt = $db->prepare("SELECT * FROM student_profiles WHERE user_id = :user_id");
$stmt->execute(['user_id' => $user_id]);
$profile = $stmt->fetch() ?: [];

$intakeMonthOptions = ['January Intake', 'April Intake', 'August Intake'];
$intakeStartYear = 2026;
$intakeEndYear = max((int)date('Y') + 10, $intakeStartYear + 10);
$selected_intake_name = $_POST['intake_name'] ?? '';
$selected_intake_year = $_POST['intake_year'] ?? ($profile['intake_year'] ?? '');

if ($selected_intake_name === '' && !empty($profile['intake_id']) && table_exists($db, 'intakes')) {
    $profileIntakeStmt = $db->prepare('SELECT intake_name, intake_year FROM intakes WHERE intake_id = :intake_id LIMIT 1');
    $profileIntakeStmt->execute(['intake_id' => (int)$profile['intake_id']]);
    $profileIntake = $profileIntakeStmt->fetch();
    if ($profileIntake) {
        $selected_intake_name = (string)($profileIntake['intake_name'] ?? '');
        $selected_intake_year = (string)($profileIntake['intake_year'] ?? $selected_intake_year);
    }
}

$selected_intake_year_is_custom = ((int)$selected_intake_year > $intakeEndYear);

// Check for existing submissions
$existing_submission = null;
$last_submission = null;
$stmt = $db->prepare("SELECT * FROM document_submissions WHERE user_id = :user_id ORDER BY submitted_at DESC LIMIT 1");
$stmt->execute(['user_id' => $user_id]);
$lastRow = $stmt->fetch();
if ($lastRow) {
    if (in_array($lastRow['status'], ['pending_admissions', 'under_admissions_review', 'pending_finance', 'under_finance_review', 'pending_greencard'], true)) {
        $existing_submission = $lastRow;
    }
    $last_submission = $lastRow;
}

// Runtime column bootstrap for document_uploads table
if (table_exists($db, 'document_uploads')) {
    try {
        if (!column_exists($db, 'document_uploads', 'classification_result')) {
            $db->exec("ALTER TABLE document_uploads ADD COLUMN classification_result JSON");
        }
        if (!column_exists($db, 'document_uploads', 'ownership_verification_status')) {
            $db->exec("ALTER TABLE document_uploads ADD COLUMN ownership_verification_status VARCHAR(50)");
        }
        if (!column_exists($db, 'document_uploads', 'manual_review_required')) {
            $db->exec("ALTER TABLE document_uploads ADD COLUMN manual_review_required BOOLEAN DEFAULT FALSE");
        }
        if (!column_exists($db, 'document_uploads', 'file_hash')) {
            $db->exec("ALTER TABLE document_uploads ADD COLUMN file_hash CHAR(64) NULL");
            $db->exec("CREATE INDEX idx_document_uploads_hash ON document_uploads (file_hash)");
        }
        if (!column_exists($db, 'document_uploads', 'ocr_extracted_text')) {
            $db->exec("ALTER TABLE document_uploads ADD COLUMN ocr_extracted_text MEDIUMTEXT NULL");
        }
        if (!column_exists($db, 'document_uploads', 'confidence_score')) {
            $db->exec("ALTER TABLE document_uploads ADD COLUMN confidence_score INT NULL");
        }
        if (!column_exists($db, 'document_uploads', 'verification_status')) {
            $db->exec("ALTER TABLE document_uploads ADD COLUMN verification_status VARCHAR(20) NULL");
        }
        if (!column_exists($db, 'document_uploads', 'extracted_data')) {
            $db->exec("ALTER TABLE document_uploads ADD COLUMN extracted_data JSON NULL");
        }
        if (!column_exists($db, 'document_uploads', 'risk_flags')) {
            $db->exec("ALTER TABLE document_uploads ADD COLUMN risk_flags TEXT NULL");
        }
        if (!column_exists($db, 'document_uploads', 'verification_document_type')) {
            $db->exec("ALTER TABLE document_uploads ADD COLUMN verification_document_type VARCHAR(50) NULL");
        }
        if (!column_exists($db, 'document_uploads', 'verification_engine_version')) {
            $db->exec("ALTER TABLE document_uploads ADD COLUMN verification_engine_version VARCHAR(30) NULL");
        }
        if (!column_exists($db, 'document_uploads', 'verification_error')) {
            $db->exec("ALTER TABLE document_uploads ADD COLUMN verification_error TEXT NULL");
        }
        if (!column_exists($db, 'document_uploads', 'verified_at')) {
            $db->exec("ALTER TABLE document_uploads ADD COLUMN verified_at TIMESTAMP NULL");
        }
    } catch (Exception $e) {
        error_log("Column bootstrap warning: " . $e->getMessage());
    }
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_documents'])) {
    $audit = new AuditLog($db);

    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        $error = 'Invalid request. Please try again.';
    } else {
        $full_name = sanitize_input($_POST['full_name'] ?? '');
        $date_of_birth = $_POST['date_of_birth'] ?? '';
        $program_level = sanitize_input($_POST['program_level'] ?? '');
        $program = sanitize_input($_POST['program'] ?? '');
        $faculty = sanitize_input($_POST['faculty'] ?? '');
        $intake_name_input = sanitize_input($_POST['intake_name'] ?? '');
        $intake_year_input = (int)($_POST['intake_year'] ?? 0);

        $intake_id = 0;
        $intake = null;
        $intake_year = $intake_year_input > 0 ? $intake_year_input : '';
        $intake_semester = 'semester_1';
        $year_of_study = 1;
        $payment_amount_input = $_POST['payment_amount'] ?? '';
        $payment_currency = 'UGX';
        $payment_reference = null;
        $payment_date = null;
        $is_bursary = isset($_POST['is_bursary']) ? 1 : 0;

        $validator->required('full_name', $full_name, 'Full Name');
        $validator->required('date_of_birth', $date_of_birth, 'Date of Birth');
        $validator->date('date_of_birth', $date_of_birth, 'Date of Birth');
        $dobValidation = validate_student_date_of_birth($date_of_birth, 18);
        if (!$dobValidation['valid']) {
            $validator->addError('date_of_birth', $dobValidation['message']);
        }
        $validator->required('program_level', $program_level, 'Program Level');
        $validator->required('program', $program, 'Program');
        $validator->required('faculty', $faculty, 'Faculty');
        $validator->required('intake_name', $intake_name_input, 'Intake Month');
        $validator->required('intake_year', $intake_year, 'Intake Year');
        if ($intake_name_input !== '' && !in_array($intake_name_input, $intakeMonthOptions, true)) {
            $validator->addError('intake_name', 'Selected intake month is invalid');
        }
        if ($intake_year !== '' && (int)$intake_year < $intakeStartYear) {
            $validator->addError('intake_year', 'Intake year must be ' . $intakeStartYear . ' or later');
        }
        $validator->required('payment_amount', $payment_amount_input, 'Payment Amount');
        $validator->numeric('payment_amount', $payment_amount_input, 'Payment Amount');
        $validator->amount('payment_amount', $payment_amount_input, 'Payment Amount');
        if (!empty($program_level)) {
            if (!isset($program_catalog[$program_level])) {
                $validator->addError('program_level', 'Selected program level is invalid');
            } elseif (!in_array($program, $program_catalog[$program_level], true)) {
                $validator->addError('program', 'Selected program does not match the chosen program level');
            }
        }

        $s6_cert = $_FILES['s6_certificate'] ?? null;
        $admission_letter = $_FILES['admission_letter'] ?? null;
        $national_id = $_FILES['national_id'] ?? null;
        $school_id = $_FILES['school_id'] ?? null;
        $passport_photo = $_FILES['passport_photo'] ?? null;
        $bank_slip = $_FILES['bank_slip'] ?? null;
        $award_letter = $_FILES['award_letter'] ?? null;
        $has_award_letter = $award_letter && $award_letter['error'] !== UPLOAD_ERR_NO_FILE;
        if ($has_award_letter) {
            $is_bursary = 1;
        }

        if (!$admission_letter || $admission_letter['error'] === UPLOAD_ERR_NO_FILE) {
            $validator->addError('admission_letter', 'Admission letter is required');
        }
        if (!$s6_cert || $s6_cert['error'] === UPLOAD_ERR_NO_FILE) {
            $validator->addError('s6_certificate', 'Academic supporting document is required');
        }

        $has_national_id = $national_id && $national_id['error'] !== UPLOAD_ERR_NO_FILE;
        $has_school_id = $school_id && $school_id['error'] !== UPLOAD_ERR_NO_FILE;

        if (!$has_national_id && !$has_school_id) {
            $validator->addError('identification', 'Please upload either National ID or Former School ID');
        }
        if (!$passport_photo || $passport_photo['error'] === UPLOAD_ERR_NO_FILE) {
            $validator->addError('passport_photo', 'Passport photo is required');
        }
        if (!$bank_slip || $bank_slip['error'] === UPLOAD_ERR_NO_FILE) {
            $validator->addError('bank_slip', 'Bank slip (payment proof) is required');
        }
        if ($is_bursary && (!$award_letter || $award_letter['error'] === UPLOAD_ERR_NO_FILE)) {
            $validator->addError('award_letter', 'Bursary award letter is required for bursary students');
        }

        if ($validator->hasErrors()) {
            $error = 'Please correct the errors below';
        } else {
            $payment_amount = round((float)$payment_amount_input, 2);
            try {
                if (empty($intake) && $intake_name_input !== '' && $intake_year !== '' && table_exists($db, 'intakes')) {
                    $intakeLookup = $db->prepare('SELECT intake_id, intake_year FROM intakes WHERE intake_name = :name AND intake_year = :year LIMIT 1');
                    $intakeLookup->execute([
                        'name' => $intake_name_input,
                        'year' => $intake_year
                    ]);
                    $existingIntake = $intakeLookup->fetch();

                    if ($existingIntake) {
                        $intake_id = (int)$existingIntake['intake_id'];
                        $intake_year = (int)$existingIntake['intake_year'];
                    } else {
                        $intakeInsert = $db->prepare(
                            'INSERT INTO intakes (intake_name, intake_year, is_active) VALUES (:name, :year, 1)'
                        );
                        $intakeInsert->execute([
                            'name' => $intake_name_input,
                            'year' => $intake_year
                        ]);
                        $intake_id = (int)$db->lastInsertId();
                    }
                }
                $db->beginTransaction();

                $documentPolicies = [
                    'admission_letter' => ['label' => 'Admission Letter', 'folder' => 'admission_letter', 'required_categories' => ['admission_letter'], 'allowed_mime_types' => ['application/pdf'], 'max_size' => DOCUMENT_UPLOAD_MAX_SIZE, 'require_text' => true, 'requires_identity_match' => true],
                    's6_certificate' => ['label' => 'Academic Supporting Document', 'folder' => 'academic_documents', 'required_categories' => ['academic_supporting_document'], 'allowed_mime_types' => ['application/pdf'], 'max_size' => DOCUMENT_UPLOAD_MAX_SIZE, 'require_text' => true, 'requires_identity_match' => true],
                    'national_id' => ['label' => 'National ID / Passport', 'folder' => 'national_id', 'required_categories' => ['national_id_passport'], 'allowed_mime_types' => ['application/pdf', 'image/jpeg', 'image/png'], 'max_size' => DOCUMENT_UPLOAD_MAX_SIZE, 'require_text' => true, 'requires_identity_match' => true],
                    'school_id' => ['label' => 'Former School ID', 'folder' => 'former_school_id', 'required_categories' => ['former_school_id'], 'allowed_mime_types' => ['application/pdf', 'image/jpeg', 'image/png'], 'max_size' => DOCUMENT_UPLOAD_MAX_SIZE, 'require_text' => true, 'requires_identity_match' => true],
                    'passport_photo' => ['label' => 'Passport Photo', 'folder' => 'passport_photo', 'required_categories' => ['passport_photo'], 'allowed_mime_types' => ['image/jpeg', 'image/png'], 'max_size' => PASSPORT_PHOTO_MAX_SIZE, 'require_text' => false, 'requires_identity_match' => false],
                    'bank_slip' => ['label' => 'Bank Slip', 'folder' => 'bank_slip', 'required_categories' => ['bank_slip'], 'allowed_mime_types' => ['application/pdf', 'image/jpeg', 'image/png'], 'max_size' => BANK_SLIP_UPLOAD_MAX_SIZE, 'require_text' => false, 'requires_identity_match' => true],
                    'award_letter' => ['label' => 'Bursary Award Letter', 'folder' => 'award_letter', 'required_categories' => ['academic_supporting_document'], 'allowed_mime_types' => ['application/pdf'], 'max_size' => DOCUMENT_UPLOAD_MAX_SIZE, 'require_text' => true, 'requires_identity_match' => true]
                ];

                $fileInputs = [
                    'admission_letter' => $admission_letter,
                    's6_certificate' => $s6_cert,
                    'national_id' => $national_id,
                    'school_id' => $school_id,
                    'passport_photo' => $passport_photo,
                    'bank_slip' => $bank_slip,
                    'award_letter' => $award_letter
                ];

                $uploadedPaths = [
                    'admission_letter' => null,
                    's6_certificate' => null,
                    'national_id' => null,
                    'school_id' => null,
                    'passport_photo' => null,
                    'bank_slip' => null,
                    'award_letter' => null
                ];

                $documentAudit = [];
                $seenHashes = [];
                $seenFingerprints = [];
                $flagReasons = [];
                $submissionFlagged = false;
                $documentRows = [];
                $verificationEngine = new DocumentVerificationEngine($db);
                $pythonExpectedTypes = [
                    'admission_letter' => 'admission_letter',
                    's6_certificate' => 'certificate',
                    'national_id' => 'national_id_passport',
                    'school_id' => 'former_school_id',
                    'passport_photo' => 'passport_photo',
                    'bank_slip' => 'bank_slip',
                    'award_letter' => 'award_letter'
                ];
                $bankSlipOcrData = [
                    'amount' => null,
                    'currency' => 'UGX',
                    'reference' => null,
                    'payment_date' => null,
                    'ocr_succeeded' => false
                ];
                $totalRequiredFee = null;
                $paymentBalance = null;

                $fastSubmission = defined('FAST_SUBMISSION_MODE') && FAST_SUBMISSION_MODE;

                foreach ($fileInputs as $fieldName => $file) {
                    if (!$file || ($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
                        continue;
                    }

                    $policy = $documentPolicies[$fieldName];
                    $tmpName = (string)($file['tmp_name'] ?? '');
                    if ($tmpName === '' || !is_uploaded_file($tmpName)) {
                        throw new Exception("{$policy['label']} upload is invalid.");
                    }

                    if (($file['error'] ?? UPLOAD_ERR_OK) !== UPLOAD_ERR_OK) {
                        throw new Exception("{$policy['label']} could not be uploaded.");
                    }

                    if ((int)($file['size'] ?? 0) <= 0) {
                        throw new Exception("{$policy['label']} appears to be empty or corrupted.");
                    }

                    if ((int)($file['size'] ?? 0) > (int)$policy['max_size']) {
                        throw new Exception("{$policy['label']} exceeds the maximum size of " . format_file_size((int)$policy['max_size']) . ".");
                    }

                    $finfo = finfo_open(FILEINFO_MIME_TYPE);
                    $mimeType = $finfo ? (finfo_file($finfo, $tmpName) ?: '') : '';
                    if ($finfo) {
                        finfo_close($finfo);
                    }

                    if (!in_array($mimeType, $policy['allowed_mime_types'], true)) {
                        throw new Exception("{$policy['label']} must be a PDF, JPG, or PNG file.");
                    }

                    if ($fieldName === 'passport_photo') {
                        $imageInfo = @getimagesize($tmpName);
                        if ($imageInfo === false) {
                            throw new Exception('Passport photo must be a readable image file.');
                        }
                        $width = (int)$imageInfo[0];
                        $height = (int)$imageInfo[1];
                        if ($width < 240 || $height < 240) {
                            throw new Exception('Passport photo must be at least 240x240 pixels.');
                        }
                        $ratio = $width / max(1, $height);
                        if ($ratio < 0.55 || $ratio > 1.25) {
                            throw new Exception('Passport photo aspect ratio is invalid. Upload a clear portrait image.');
                        }
                    }

                    $fileHash = hash_file('sha256', $tmpName);
                    $fingerprint = $fileHash . '|' . (int)$file['size'] . '|' . strtolower((string)$file['name']);

                    if (in_array($fileHash, $seenHashes, true) || in_array($fingerprint, $seenFingerprints, true)) {
                        throw new Exception('Duplicate file detected. Please upload distinct documents for each field.');
                    }

                    if ($fieldName === 'bank_slip' && kiu_has_duplicate_document_hash($db, $fileHash, 'bank_slip')) {
                        throw new Exception('This bank slip has already been submitted. Please upload a unique payment receipt.');
                    }

                    $documentText = kiu_extract_document_text($file);
                    $textUnavailableForVerification = kiu_is_likely_pdf_binary_blob($documentText);
                    $categoryText = $textUnavailableForVerification ? '' : $documentText;
                    $detected = kiu_detect_document_category($categoryText, $mimeType, $fieldName);
                    $fieldFlagged = false;

                    if ($fieldName === 'passport_photo') {
                        $detected = [
                            'category' => 'passport_photo',
                            'confidence' => 1.0,
                            'keywords' => ['readable_image', 'portrait_ratio']
                        ];
                    }

                    if ($fieldName === 'bank_slip') {
                        $bankSlipOcrData = kiu_extract_bank_slip_payment_data($documentText);
                        $bankSlipOcrData['amount'] = null;
                        $bankSlipOcrData['ocr_succeeded'] = false;
                        if (in_array($bankSlipOcrData['currency'] ?? 'UGX', $allowed_payment_currencies, true)) {
                            $payment_currency = $bankSlipOcrData['currency'];
                        }
                        if ($bankSlipOcrData['reference'] !== null) {
                            $payment_reference = $bankSlipOcrData['reference'];
                        }
                        if ($bankSlipOcrData['payment_date'] !== null) {
                            $payment_date = $bankSlipOcrData['payment_date'];
                        }
                    }

                    if ($fieldName !== 'passport_photo' && ($detected['category'] ?? '') === 'passport_photo') {
                        $detected['category'] = $policy['required_categories'][0] ?? $fieldName;
                        $detected['confidence'] = min((float)($detected['confidence'] ?? 0), 0.5);
                    }

                    if ($fieldName === 'admission_letter' && ($detected['category'] ?? '') === 'other') {
                        $detected['category'] = 'admission_letter';
                        $detected['confidence'] = max((float)($detected['confidence'] ?? 0), 0.35);
                        $fieldFlagged = true;
                        $submissionFlagged = true;
                        $flagReasons[] = 'Admission Letter could not be confidently classified and will need manual review.';
                    }

                    if ($textUnavailableForVerification && isset($pythonExpectedTypes[$fieldName])) {
                        $detected = [
                            'category' => $policy['required_categories'][0] ?? $fieldName,
                            'confidence' => 0.5,
                            'keywords' => ['pdf_text_extraction_unavailable']
                        ];
                        $fieldFlagged = true;
                        $submissionFlagged = true;
                        $flagReasons[] = "{$policy['label']} could not be fully read automatically and will need manual review.";
                    }

                    if ($policy['require_text'] && trim($categoryText) === '' && !$textUnavailableForVerification) {
                        throw new Exception("{$policy['label']} is not readable or contains no extractable text.");
                    }

                    if (!in_array($detected['category'], $policy['required_categories'], true)) {
                        $detectedLabel = $detected['category'] === 'other' ? 'unclassified document' : ucfirst(str_replace('_', ' ', $detected['category']));
                        $detected['category'] = $policy['required_categories'][0] ?? $fieldName;
                        $detected['confidence'] = min((float)($detected['confidence'] ?? 0), 0.5);
                        $fieldFlagged = true;
                        $submissionFlagged = true;
                        $flagReasons[] = "{$policy['label']} could not be confidently verified automatically and will need manual review ({$detectedLabel}).";
                    }

                    $identity = kiu_extract_document_identity($categoryText);

                    if ($policy['requires_identity_match']) {
                        if (!empty($identity['full_name']) && !kiu_names_match($full_name, $identity['full_name'])) {
                            $fieldFlagged = true;
                            $flagReasons[] = "{$policy['label']} name does not match the submitted student name.";
                        }

                        if (!empty($profile['full_name']) && !empty($identity['full_name']) && !kiu_names_match((string)$profile['full_name'], $identity['full_name'])) {
                            $fieldFlagged = true;
                            $flagReasons[] = "{$policy['label']} name does not match the stored student profile.";
                        }

                        if (!empty($identity['admission_number']) && $current_admission_number !== '' && strcasecmp($identity['admission_number'], $current_admission_number) !== 0) {
                            $fieldFlagged = true;
                            $flagReasons[] = "{$policy['label']} admission number does not match the logged-in student.";
                        }

                        if (!empty($identity['registration_number']) && $current_admission_number !== '' && strcasecmp($identity['registration_number'], $current_admission_number) !== 0) {
                            $fieldFlagged = true;
                            $flagReasons[] = "{$policy['label']} registration number does not match the logged-in student.";
                        }
                    }

                    if (!empty($identity['full_name']) && !kiu_names_match($full_name, $identity['full_name'])) {
                        $fieldFlagged = true;
                        $flagReasons[] = "{$policy['label']} appears to belong to a different person.";
                    }

                    $uploadDir = SECURE_UPLOAD_DIR . 'students/' . $user_id . '/' . $policy['folder'] . '/';
                    $uploader = new FileUpload($uploadDir, $policy['allowed_mime_types'], (int)$policy['max_size']);
                    $customName = $fieldName . '_' . $user_id . '_' . substr($fileHash, 0, 12);
                    $uploadResult = $uploader->upload($file, $customName);

                    if (!$uploadResult) {
                        throw new Exception(implode(' ', $uploader->getErrors()) ?: "Failed to store {$policy['label']}.");
                    }

                    $storedPath = (string)($uploadResult['path'] ?? '');
                    $normalizedStoredPath = str_replace('\\', '/', $storedPath);
                    $normalizedRoot = str_replace('\\', '/', SITE_ROOT);
                    if ($storedPath !== '' && strpos($normalizedStoredPath, $normalizedRoot) === 0) {
                        $storedPath = ltrim(substr($normalizedStoredPath, strlen($normalizedRoot)), '/');
                    }

                    $uploadedPaths[$fieldName] = $storedPath;
                    // Analyze stored document for content and template similarity
                    $analysis = null;
                    if (!$fastSubmission) {
                        try {
                            $analysis = analyzeDocument($storedPath, $fieldName);
                        } catch (Throwable $e) {
                            $analysis = [
                                'confidence_score' => 0,
                                'status' => 'suspicious',
                                'reasons' => ["Analyzer error: " . $e->getMessage()],
                                'extracted_data' => []
                            ];
                        }
                    }

                    // If analyzer marked invalid, keep the upload but flag for review.
                    // Python verification performs the final strict rejection decision.
                    if (is_array($analysis) && ($analysis['status'] ?? '') === 'invalid') {
                        $fieldFlagged = true;
                        $submissionFlagged = true;
                        $flagReasons[] = $policy['label'] . ' needs manual review because preliminary analyzer confidence is low.';
                    }

                    // If suspicious, flag for manual review
                    if (is_array($analysis) && ($analysis['status'] ?? '') === 'suspicious') {
                        $fieldFlagged = true;
                        $submissionFlagged = true;
                        $flagReasons[] = $policy['label'] . ' flagged by document analyzer for manual review.';
                    }

                    if ($fieldName === 'bank_slip' && is_array($analysis)) {
                        $analysisExtracted = $analysis['extracted_data'] ?? [];
                        $analyzedAmount = $analysisExtracted['total_amount_paid'] ?? null;
                        $analyzedCurrency = $analysisExtracted['currency'] ?? null;
                        $analyzedReference = $analysisExtracted['payment_reference'] ?? null;
                        $analyzedDate = $analysisExtracted['payment_date'] ?? null;

                        if ($analyzedAmount !== null && preg_match('/^\d+(\.\d{1,2})?$/', (string)$analyzedAmount)) {
                            $bankSlipOcrData['amount'] = (float)$analyzedAmount;
                        }

                        if (in_array($analyzedCurrency, $allowed_payment_currencies, true)) {
                            $payment_currency = $analyzedCurrency;
                            $bankSlipOcrData['currency'] = $analyzedCurrency;
                        }

                        if ($analyzedReference !== null && $analyzedReference !== '') {
                            $payment_reference = $analyzedReference;
                            $bankSlipOcrData['reference'] = $analyzedReference;
                        }

                        if ($analyzedDate !== null && $analyzedDate !== '') {
                            $payment_date = $analyzedDate;
                            $bankSlipOcrData['payment_date'] = $analyzedDate;
                        }
                    }

                    $pythonVerification = null;
                    if (!$fastSubmission && isset($pythonExpectedTypes[$fieldName])) {
                        $pythonVerification = $verificationEngine->verify(
                            $storedPath,
                            $pythonExpectedTypes[$fieldName],
                            $fileHash
                        );

                        $pythonStatus = $pythonVerification['status'] ?? 'REVIEW';
                        if ($pythonStatus === 'REJECTED') {
                            $missing = $pythonVerification['missing_fields'] ?? [];
                            $flags = $pythonVerification['risk_flags'] ?? [];
                            $reason = implode(', ', array_filter(array_merge($missing, $flags)));
                            throw new Exception($policy['label'] . ' failed intelligent verification' . ($reason ? ': ' . $reason : '.'));
                        }

                        if ($pythonStatus === 'REVIEW' || $pythonStatus === 'REJECTED') {
                            $fieldFlagged = true;
                            $submissionFlagged = true;
                            $flagReasons[] = $policy['label'] . ' requires manual review by the local Python verification engine.';
                        }

                        if ($fieldName === 'bank_slip') {
                            $pythonFields = $pythonVerification['extracted_fields'] ?? [];
                            if (!empty($pythonFields['amount']) && preg_match('/^\d+(\.\d{1,2})?$/', (string)$pythonFields['amount'])) {
                                $bankSlipOcrData['amount'] = (float)$pythonFields['amount'];
                            }
                            if (!empty($pythonFields['reference'])) {
                                $payment_reference = (string)$pythonFields['reference'];
                                $bankSlipOcrData['reference'] = $payment_reference;
                            }
                            if (!empty($pythonFields['date'])) {
                                $payment_date = (string)$pythonFields['date'];
                                $bankSlipOcrData['payment_date'] = $payment_date;
                            }
                        }
                    }

                    $seenHashes[] = $fileHash;
                    $seenFingerprints[] = $fingerprint;
                    $submissionFlagged = $submissionFlagged || $fieldFlagged;

                    $documentAudit[$fieldName] = [
                        'status' => $fieldFlagged ? 'flagged' : 'verified',
                        'label' => $policy['label'],
                        'detected_category' => $detected['category'],
                        'classification_confidence' => $detected['confidence'],
                        'hash' => $fileHash,
                        'file_name' => basename((string)$file['name']),
                        'mime_type' => $mimeType,
                        'identity' => $identity,
                        'keywords' => $detected['keywords'],
                        'storage_path' => $storedPath,
                        'analyzer' => $analysis,
                        'python_verification' => $pythonVerification
                    ];

                    $documentRows[$fieldName] = [
                        'document_type' => $fieldName,
                        'file_path' => $storedPath,
                        'original_filename' => basename((string)$file['name']),
                        'file_size_bytes' => (int)$file['size'],
                        'mime_type' => $mimeType,
                        'file_hash' => $fileHash,
                        'classification_result' => json_encode($detected, JSON_UNESCAPED_SLASHES),
                        'ownership_verification_status' => $fieldFlagged ? 'flagged' : 'verified',
                        'manual_review_required' => $fieldFlagged ? 1 : 0,
                        'ocr_extracted_text' => kiu_truncate_text_for_db($documentText),
                        'confidence_score' => is_array($pythonVerification)
                            ? (int)($pythonVerification['confidence_score'] ?? 0)
                            : (is_array($analysis) ? (int)($analysis['confidence_score'] ?? 0) : null),
                        'verification_status' => is_array($pythonVerification)
                            ? ($pythonVerification['status'] ?? null)
                            : (is_array($analysis) ? ($analysis['status'] ?? null) : null),
                        'verification_document_type' => $pythonExpectedTypes[$fieldName] ?? null,
                        'extracted_data' => is_array($pythonVerification)
                            ? json_encode($pythonVerification['extracted_fields'] ?? [], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
                            : null,
                        'risk_flags' => is_array($pythonVerification)
                            ? json_encode($pythonVerification['risk_flags'] ?? [], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
                            : null,
                        'verification_engine_version' => is_array($pythonVerification) ? ($pythonVerification['engine_version'] ?? null) : null,
                        'verification_error' => is_array($pythonVerification) ? ($pythonVerification['error'] ?? null) : null,
                        'verified_at' => is_array($pythonVerification) ? date('Y-m-d H:i:s') : null
                    ];
                }

                if (!$uploadedPaths['admission_letter'] || !$uploadedPaths['s6_certificate'] || !$uploadedPaths['passport_photo'] || !$uploadedPaths['bank_slip']) {
                    throw new Exception('Required documents could not be stored. Please review the upload errors and try again.');
                }

                if (!$uploadedPaths['national_id'] && !$uploadedPaths['school_id']) {
                    throw new Exception('Please upload either National ID or Former School ID.');
                }

                if ($is_bursary && !$uploadedPaths['award_letter']) {
                    throw new Exception('Bursary award letter is required for bursary students.');
                }

                $paymentAmountForDb = (float)$payment_amount;

                $feeStructure = resolve_fee_structure_for_submission($db, $program, $faculty, $intake_semester, $intake_year);
                $feeRequirements = calculate_finance_fee_requirements($feeStructure, $program, $faculty);
                $semesterExchangeRate = get_semester_exchange_rate_ugx($db, $intake_year, $intake_semester);
                $paymentAmountUgx = $paymentAmountForDb !== null
                    ? convert_amount_to_ugx($paymentAmountForDb, $payment_currency, $semesterExchangeRate)
                    : 0.00;
                $totalRequiredFee = (float)$feeRequirements['total_required_fee'];
                $paymentBalance = max(0, $totalRequiredFee - $paymentAmountUgx);

                $verificationMode = $fastSubmission ? 'fast_submission_deferred' : 'strict_document_ingestion';
                $submissionNotes = json_encode([
                    'verification_mode' => $verificationMode,
                    'ownership_flagged' => $submissionFlagged,
                    'flag_reasons' => array_values(array_unique($flagReasons)),
                    'document_statuses' => $documentAudit,
                    'bank_slip_ocr' => $bankSlipOcrData,
                    'fee_calculation' => [
                        'total_required_fee_ugx' => $totalRequiredFee,
                        'submitted_amount' => $paymentAmountForDb,
                        'submitted_currency' => $payment_currency,
                        'submitted_amount_ugx' => $paymentAmountUgx,
                        'balance_ugx' => $paymentBalance,
                        'exchange_rate_used' => $semesterExchangeRate
                    ]
                ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

                if ($submissionNotes === false) {
                    $submissionNotes = $submissionFlagged
                        ? 'Strict validation completed with ownership flags.'
                        : 'Strict validation completed successfully.';
                }

                $priorityLevel = $submissionFlagged ? 'urgent' : 'normal';

                $submissionColumns = [
                    'user_id', 'admission_letter_path', 'is_bursary', 'bursary_award_letter_path',
                    's6_certificate_path', 'national_id_path', 'school_id_path',
                    'passport_photo_path', 'bank_slip_path', 'full_name', 'date_of_birth',
                    'program', 'faculty', 'intake_year', 'intake_semester',
                    'payment_reference', 'payment_amount', 'payment_currency', 'payment_date',
                    'status', 'resubmission_count', 'priority_level', 'notes'
                ];
                $submissionParams = [
                    'user_id' => $user_id,
                    'admission_letter_path' => $uploadedPaths['admission_letter'],
                    'is_bursary' => $is_bursary,
                    'bursary_award_letter_path' => $uploadedPaths['award_letter'],
                    's6_certificate_path' => $uploadedPaths['s6_certificate'],
                    'national_id_path' => $uploadedPaths['national_id'],
                    'school_id_path' => $uploadedPaths['school_id'],
                    'passport_photo_path' => $uploadedPaths['passport_photo'],
                    'bank_slip_path' => $uploadedPaths['bank_slip'],
                    'full_name' => $full_name,
                    'date_of_birth' => $date_of_birth,
                    'program' => $program,
                    'faculty' => $faculty,
                    'intake_year' => $intake_year,
                    'intake_semester' => $intake_semester,
                    'payment_reference' => $payment_reference,
                    'payment_amount' => $paymentAmountForDb,
                    'payment_currency' => $payment_currency,
                    'payment_date' => $payment_date,
                    'status' => STATUS_PENDING_ADMISSIONS,
                    'resubmission_count' => (($last_submission && in_array($last_submission['status'], ['admissions_rejected', 'finance_rejected', 'resubmission_requested'], true))
                        ? ((int)$last_submission['resubmission_count'] + 1)
                        : 0),
                    'priority_level' => $priorityLevel,
                    'notes' => $submissionNotes
                ];

                if (column_exists($db, 'document_submissions', 'intake_id')) {
                    $submissionColumns[] = 'intake_id';
                    $submissionParams['intake_id'] = $intake_id > 0 ? $intake_id : null;
                }
                if (column_exists($db, 'document_submissions', 'year_of_study')) {
                    $submissionColumns[] = 'year_of_study';
                    $submissionParams['year_of_study'] = $year_of_study;
                }

                $optionalSubmissionValues = [
                    'payment_amount_ocr' => null,
                    'total_required_fee' => $totalRequiredFee,
                    'payment_balance' => $paymentBalance,
                    'bank_slip_hash' => $documentRows['bank_slip']['file_hash'] ?? null,
                    'bank_slip_ocr_status' => 'manual_entry',
                    'manual_review_required' => $submissionFlagged ? 1 : 0
                ];

                foreach ($optionalSubmissionValues as $column => $value) {
                    if (column_exists($db, 'document_submissions', $column)) {
                        $submissionColumns[] = $column;
                        $submissionParams[$column] = $value;
                    }
                }

                $placeholders = array_map(function ($column) {
                    return ':' . $column;
                }, $submissionColumns);

                $stmt = $db->prepare(
                    'INSERT INTO document_submissions (' . implode(', ', $submissionColumns) . ') VALUES (' . implode(', ', $placeholders) . ')'
                );
                $stmt->execute($submissionParams);

                $submission_id = $db->lastInsertId();

                if (table_exists($db, 'student_profiles')) {
                    $profileUpdates = [
                        'intake_year' => $intake_year,
                        'intake_semester' => $intake_semester
                    ];
                    if (column_exists($db, 'student_profiles', 'intake_id')) {
                        $profileUpdates['intake_id'] = $intake_id > 0 ? $intake_id : null;
                    }
                    if (column_exists($db, 'student_profiles', 'year_of_study')) {
                        $profileUpdates['year_of_study'] = 1;
                    }

                    $setClauses = [];
                    $profileParams = ['user_id' => $user_id];
                    foreach ($profileUpdates as $column => $value) {
                        $setClauses[] = "{$column} = :{$column}";
                        $profileParams[$column] = $value;
                    }

                    if (!empty($setClauses)) {
                        $profileStmt = $db->prepare(
                            'UPDATE student_profiles SET ' . implode(', ', $setClauses) . ' WHERE user_id = :user_id'
                        );
                        $profileStmt->execute($profileParams);
                    }
                }

                if (table_exists($db, 'document_uploads')) {
                    foreach ($documentRows as $row) {
                        $uploadColumns = [
                            'submission_id',
                            'document_type',
                            'file_path',
                            'original_filename',
                            'file_size_bytes',
                            'mime_type',
                            'is_encrypted'
                        ];
                        $uploadParams = [
                            'submission_id' => $submission_id,
                            'document_type' => in_array($row['document_type'], ['s6_certificate', 'national_id', 'school_id', 'passport_photo', 'bank_slip', 'other'], true)
                                ? $row['document_type']
                                : 'other',
                            'file_path' => $row['file_path'],
                            'original_filename' => $row['original_filename'],
                            'file_size_bytes' => $row['file_size_bytes'],
                            'mime_type' => $row['mime_type'],
                            'is_encrypted' => 0
                        ];

                        foreach ([
                            'file_hash',
                            'classification_result',
                            'ownership_verification_status',
                            'manual_review_required',
                            'ocr_extracted_text',
                            'confidence_score',
                            'verification_status',
                            'extracted_data',
                            'risk_flags',
                            'verification_document_type',
                            'verification_engine_version',
                            'verification_error',
                            'verified_at'
                        ] as $column) {
                            if (column_exists($db, 'document_uploads', $column)) {
                                $uploadColumns[] = $column;
                                $uploadParams[$column] = $row[$column];
                            }
                        }

                        $uploadPlaceholders = array_map(function ($column) {
                            return ':' . $column;
                        }, $uploadColumns);

                        $uploadStmt = $db->prepare(
                            'INSERT INTO document_uploads (' . implode(', ', $uploadColumns) . ') VALUES (' . implode(', ', $uploadPlaceholders) . ')'
                        );
                        $uploadStmt->execute($uploadParams);
                    }
                }

                $workflowNotes = ($last_submission && in_array($last_submission['status'], ['admissions_rejected', 'finance_rejected', 'resubmission_requested'], true))
                    ? 'Student resubmitted documents'
                    : 'Initial document submission';

                if ($submissionFlagged) {
                    $workflowNotes .= ' | Ownership inconsistencies flagged for manual review';
                }

                $stmt = $db->prepare("\n                    INSERT INTO workflow_history (submission_id, from_status, to_status, changed_by_user_id, department, notes)\n                    VALUES (:submission_id, :from_status, 'pending_admissions', :user_id, 'student', :notes)\n                ");
                $stmt->execute([
                    'submission_id' => $submission_id,
                    'from_status' => ($last_submission && in_array($last_submission['status'], ['admissions_rejected', 'finance_rejected', 'resubmission_requested'], true)) ? $last_submission['status'] : null,
                    'user_id' => $user_id,
                    'notes' => $workflowNotes
                ]);

                $notification = new NotificationService($db);
                $notification->notify(
                    $user_id,
                    'submission_received',
                    $submissionFlagged ? 'Your documents were submitted for manual review' : 'Your documents have been submitted successfully',
                    $submissionFlagged
                        ? 'Your documents were received, but one or more ownership checks were flagged for manual review by the Admissions Office.'
                        : 'Your academic documents have been received and are now pending review by the Admissions Office. You will be notified once the review is complete.'
                );

                $stmt = $db->prepare("SELECT user_id FROM users WHERE role = 'registrar' AND is_active = TRUE");
                $stmt->execute();
                $registrars = $stmt->fetchAll();

                foreach ($registrars as $registrar) {
                    $notification->notify(
                        $registrar['user_id'],
                        'new_submission',
                        $submissionFlagged ? 'Flagged document submission pending manual review' : 'New document submission pending review',
                        $submissionFlagged
                            ? "Student {$full_name} submitted documents with ownership inconsistencies that require manual review."
                            : "Student {$full_name} has submitted documents for verification."
                    );
                }

                $audit->log(
                    $user_id,
                    'DOCUMENT_SUBMIT',
                    'document_submission',
                    $submission_id,
                    $submissionFlagged
                        ? "Student submitted documents for {$program} with ownership flags"
                        : "Student submitted documents for {$program}"
                );

                $db->commit();

                $_SESSION['success'] = $submissionFlagged
                    ? 'Your documents have been submitted, but some documents were flagged for manual review.'
                    : 'Your documents have been submitted successfully! They will be reviewed by the Admissions Office.';
                redirect('modules/student/dashboard.php');

            } catch (Throwable $e) {
                try {
                    if ($db instanceof PDO && $db->inTransaction()) {
                        $db->rollBack();
                    }
                } catch (Throwable $rollbackError) {
                    error_log('Document submission rollback failed: ' . $rollbackError->getMessage());
                }

                $error = $e->getMessage();
                if (stripos($error, 'server has gone away') !== false || stripos($error, 'max_allowed_packet') !== false) {
                    $error = 'The submission was too large for the database connection. Please try again with smaller PDF files or clearer compressed scans.';
                }
                error_log('Document submission error: ' . $e->getMessage());
            }
        }
    }
}

$page_title = 'Submit Documents';
include '../../includes/header.php';
?>

<div class="container">
    <div class="page-header">
        <h1>Submit Academic Documents</h1>
        <p>Complete your student details, attach the required files, and submit them to Admissions for verification.</p>
    </div>
    
    <?php if ($existing_submission): ?>
    <div class="alert alert-info">
        <strong>Existing Submission Found</strong>
        <p>You already have a submission in progress. Current status: <strong><?php echo ucwords(str_replace('_', ' ', $existing_submission['status'])); ?></strong></p>
        <p>Submitted on: <?php echo date('d/m/Y', strtotime($existing_submission['submitted_at'])); ?></p>
        <a href="dashboard.php" class="btn btn-primary">View Status</a>
    </div>
    <?php else: ?>

    <?php if ($last_submission && $last_submission['status'] === 'resubmission_requested'): ?>
    <div class="alert alert-warning">
        <strong>Resubmission Requested</strong>
        <p>Please address Admissions feedback and submit updated documents.</p>
    </div>
    <?php endif; ?>
    
    <?php if ($error): ?>
    <div class="alert alert-danger">
        <?php echo htmlspecialchars($error); ?>
    </div>
    <?php endif; ?>
    
    <?php if (!empty($validator->getErrors())): ?>
    <div class="alert alert-danger">
        <ul>
            <?php foreach ($validator->getErrors() as $field_error): ?>
            <li><?php echo htmlspecialchars($field_error); ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
    <?php endif; ?>
    
    <div class="workflow-info">
        <h3>Document Submission Process</h3>
        <ol>
            <li><strong>You are here:</strong> Submit documents to Admissions Office</li>
            <li>Admissions verifies documents and generates Registration Number</li>
            <li>Admissions forwards to Finance for payment confirmation</li>
            <li>Finance confirms payment and sends clearance back</li>
            <li>Admissions issues your Green Card</li>
        </ol>
    </div>
    
    <form method="POST" action="" enctype="multipart/form-data" class="document-form">
        <?php echo csrf_token_field(); ?>
        <?php $selected_faculty = $_POST['faculty'] ?? ($profile['faculty'] ?? ''); ?>
        <?php
            $selected_program = $_POST['program'] ?? ($profile['program'] ?? '');
            $selected_program_level = $_POST['program_level'] ?? '';
            if ($selected_program_level === '' && !empty($selected_program)) {
                foreach ($program_catalog as $level_key => $level_programs) {
                    if (in_array($selected_program, $level_programs, true)) {
                        $selected_program_level = $level_key;
                        break;
                    }
                }
            }
        ?>
        
        <div class="form-section">
            <div class="form-section-header">
                <h2>Personal Information</h2>
                <p>Use the names and date of birth shown on your admission documents.</p>
            </div>

            <div class="form-grid">
                <div class="form-group">
                    <label for="full_name">Full Name *</label>
                    <input type="text" name="full_name" id="full_name" 
                           value="<?php echo htmlspecialchars($profile['full_name'] ?? ''); ?>" 
                           class="form-control" required>
                </div>
                
                <div class="form-group">
                    <label for="date_of_birth">Date of Birth *</label>
                    <input type="date" name="date_of_birth" id="date_of_birth" 
                           value="<?php echo htmlspecialchars($profile['date_of_birth'] ?? ''); ?>" 
                           class="form-control" required>
                </div>
            </div>
        </div>
        
        <div class="form-section">
            <div class="form-section-header">
                <h2>Academic Information</h2>
                <p>Select your enrolled programme, faculty, and intake.</p>
            </div>

            <div class="form-grid">
                <div class="form-group">
                    <label for="program_level">Program Level *</label>
                    <select name="program_level" id="program_level" class="form-control" required>
                        <option value="">Select Program Level</option>
                        <option value="certificate" <?php echo $selected_program_level === 'certificate' ? 'selected' : ''; ?>>Certificate</option>
                        <option value="bachelors" <?php echo $selected_program_level === 'bachelors' ? 'selected' : ''; ?>>Bachelors</option>
                        <option value="pgd" <?php echo $selected_program_level === 'pgd' ? 'selected' : ''; ?>>PGD</option>
                        <option value="masters" <?php echo $selected_program_level === 'masters' ? 'selected' : ''; ?>>Masters</option>
                        <option value="phd" <?php echo $selected_program_level === 'phd' ? 'selected' : ''; ?>>PhD</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="program">Program Choice *</label>
                    <select name="program" id="program" class="form-control" required>
                        <option value="">Select Program Choice</option>
                        <?php if (!empty($selected_program_level) && isset($program_catalog[$selected_program_level])): ?>
                            <?php foreach ($program_catalog[$selected_program_level] as $course): ?>
                            <option value="<?php echo htmlspecialchars($course); ?>" <?php echo $selected_program === $course ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($course); ?>
                            </option>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="faculty">College/School/Faculty *</label>
                    <select name="faculty" id="faculty" class="form-control" required>
                        <option value="">Select College/School/Faculty</option>
                        <?php foreach ($main_campus_units as $unit): ?>
                        <option value="<?php echo htmlspecialchars($unit); ?>" <?php echo $selected_faculty === $unit ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($unit); ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="intake_name">Intake Month *</label>
                    <select name="intake_name" id="intake_name" class="form-control" required>
                        <option value="">Select Intake Month</option>
                        <?php foreach ($intakeMonthOptions as $intakeMonth): ?>
                            <option value="<?php echo htmlspecialchars($intakeMonth); ?>" <?php echo $selected_intake_name === $intakeMonth ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars(str_replace(' Intake', '', $intakeMonth)); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="intake_year_choice">Intake Year *</label>
                    <select name="intake_year_choice" id="intake_year_choice" class="form-control" required>
                        <option value="">Select Intake Year</option>
                        <?php for ($year = $intakeStartYear; $year <= $intakeEndYear; $year++): ?>
                            <option value="<?php echo $year; ?>" <?php echo (string)$selected_intake_year === (string)$year ? 'selected' : ''; ?>>
                                <?php echo $year; ?>
                            </option>
                        <?php endfor; ?>
                        <option value="custom" <?php echo $selected_intake_year_is_custom ? 'selected' : ''; ?>>Other academic year...</option>
                    </select>
                    <input type="hidden" name="intake_year" id="intake_year" value="<?php echo htmlspecialchars((string)$selected_intake_year); ?>">
                    <input type="number" id="custom_intake_year" class="form-control intake-year-custom <?php echo $selected_intake_year_is_custom ? '' : 'is-hidden'; ?>"
                           min="<?php echo $intakeStartYear; ?>" step="1"
                           value="<?php echo $selected_intake_year_is_custom ? htmlspecialchars((string)$selected_intake_year) : ''; ?>"
                           placeholder="Enter start year, e.g. 2037">
                    <small>Select the intake year, or choose another year if it is not listed.</small>
                </div>
            </div>
        </div>
        
        <div class="form-section">
            <div class="form-section-header">
                <h2>Academic Documents</h2>
                <p>Upload clear official files. Admission letter and academic document must be PDFs.</p>
            </div>

            <div class="upload-grid">
                <div class="form-group">
                      <label for="admission_letter">Admission Letter * (PDF)</label>
                    <input type="file" name="admission_letter" id="admission_letter"
                          accept=".pdf,application/pdf" class="form-control" required>
                    <small>Upload your official KIU admission letter</small>
                </div>
                
                <div class="form-group">
                      <label for="s6_certificate">Academic Supporting Document * (PDF)</label>
                    <input type="file" name="s6_certificate" id="s6_certificate" 
                          accept=".pdf,application/pdf" class="form-control" required>
                      <small>Upload your required academic supporting document</small>
                </div>
                
                <div class="form-group">
                      <label for="national_id">National ID or Passport (PDF, JPG, or PNG)</label>
                    <input type="file" name="national_id" id="national_id" 
                          accept=".pdf,.jpg,.jpeg,.png,application/pdf,image/jpeg,image/png" class="form-control">
                    <small>Upload National ID or Passport if available</small>
                </div>
                
                <div class="form-group">
                      <label for="school_id">Former School ID (PDF, JPG, or PNG)</label>
                    <input type="file" name="school_id" id="school_id" 
                          accept=".pdf,.jpg,.jpeg,.png,application/pdf,image/jpeg,image/png" class="form-control">
                    <small>Upload this if you do not have National ID</small>
                </div>
                
                <div class="form-group">
                    <label for="passport_photo">Passport Photo * (JPG or PNG)</label>
                    <input type="file" name="passport_photo" id="passport_photo" 
                           accept=".jpg,.jpeg,.png" class="form-control" required>
                    <small>Passport-size photo for your Green Card</small>
                </div>
            </div>
            
            <p class="alert alert-info">
                <strong>Note:</strong> You must upload either National ID OR Former School ID (or both)
            </p>

            <div class="form-group">
                <label class="checkbox-label">
                    <input type="checkbox" name="is_bursary" id="is_bursary" value="1">
                    I am a bursary-sponsored student
                </label>
            </div>

            <div class="form-group is-hidden" id="award_letter_group">
                  <label for="award_letter">Bursary Award Letter * (PDF)</label>
                <input type="file" name="award_letter" id="award_letter"
                      accept=".pdf,application/pdf" class="form-control">
                <small>Required for bursary-sponsored students</small>
            </div>
        </div>
        
        <div class="form-section">
            <div class="form-section-header">
                <h2>Payment Information</h2>
                <p>Enter the bank slip amount and upload the receipt for Finance verification.</p>
            </div>
            <div class="form-grid">
                <div class="form-group">
                    <label for="payment_amount">Bank Slip Amount (UGX) *</label>
                    <input type="number" name="payment_amount" id="payment_amount" 
                           step="0.01" min="0.01" class="form-control" required>
                    <small>Enter the exact amount paid as shown on your bank slip</small>
                </div>
                <div class="form-group">
                      <label for="bank_slip">Bank Slip/Receipt * (PDF, JPG, or PNG)</label>
                    <input type="file" name="bank_slip" id="bank_slip" 
                          accept=".pdf,.jpg,.jpeg,.png,application/pdf,image/jpeg,image/png" class="form-control" required>
                    <small>Upload your bank slip for verification</small>
                </div>
            </div>
        </div>
        
        <div class="form-actions">
            <button type="submit" name="submit_documents" class="btn btn-primary btn-lg">
                Submit Documents to Admissions Office
            </button>
            <a href="dashboard.php" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
    
    <?php endif; ?>
</div>

<script>
const programCatalog = <?php echo json_encode($program_catalog, JSON_UNESCAPED_UNICODE); ?>;
const uploadVerificationUrl = <?php echo json_encode(BASE_URL . 'php/upload.php'); ?>;
const uploadExpectedTypes = {
    admission_letter: 'admission_letter',
    s6_certificate: 'academic_supporting_document',
    national_id: 'national_id_passport',
    school_id: 'former_school_id',
    passport_photo: 'passport_photo',
    bank_slip: 'bank_slip',
    award_letter: 'bursary_award_letter'
};
const uploadVerificationState = {};

function updateProgramChoices(selectedValue = '') {
    const levelSelect = document.getElementById('program_level');
    const programSelect = document.getElementById('program');
    if (!levelSelect || !programSelect) return;

    const level = levelSelect.value;
    const options = programCatalog[level] || [];

    programSelect.innerHTML = '';

    const placeholder = document.createElement('option');
    placeholder.value = '';
    placeholder.textContent = 'Select Program Choice';
    programSelect.appendChild(placeholder);

    options.forEach((course) => {
        const option = document.createElement('option');
        option.value = course;
        option.textContent = course;
        if (selectedValue && selectedValue === course) {
            option.selected = true;
        }
        programSelect.appendChild(option);
    });
}

document.getElementById('program_level')?.addEventListener('change', function() {
    updateProgramChoices('');
});

function updateIntakeYearValue() {
    const choice = document.getElementById('intake_year_choice');
    const hiddenYear = document.getElementById('intake_year');
    const customYear = document.getElementById('custom_intake_year');
    if (!choice || !hiddenYear || !customYear) return;

    if (choice.value === 'custom') {
        customYear.classList.remove('is-hidden');
        customYear.required = true;
        hiddenYear.value = customYear.value;
    } else {
        customYear.classList.add('is-hidden');
        customYear.required = false;
        hiddenYear.value = choice.value;
    }
}

document.getElementById('intake_year_choice')?.addEventListener('change', updateIntakeYearValue);
document.getElementById('custom_intake_year')?.addEventListener('input', updateIntakeYearValue);

document.addEventListener('DOMContentLoaded', function() {
    const currentProgram = <?php echo json_encode($selected_program, JSON_UNESCAPED_UNICODE); ?>;
    updateProgramChoices(currentProgram);
    updateIntakeYearValue();
});

document.getElementById('is_bursary')?.addEventListener('change', function() {
    const group = document.getElementById('award_letter_group');
    const input = document.getElementById('award_letter');
    if (!group || !input) return;
    if (this.checked) {
        group.classList.remove('is-hidden');
        input.required = true;
    } else {
        group.classList.add('is-hidden');
        input.required = false;
    }
});

document.getElementById('award_letter')?.addEventListener('change', function() {
    const checkbox = document.getElementById('is_bursary');
    const group = document.getElementById('award_letter_group');
    if (this.files && this.files[0] && checkbox) {
        checkbox.checked = true;
        group?.classList.remove('is-hidden');
        this.required = true;
    }
});

function getUploadStatusElement(input) {
    let status = document.getElementById(input.id + '_verification_status');
    if (!status) {
        status = document.createElement('div');
        status.id = input.id + '_verification_status';
        status.className = 'upload-verification-status';
        input.insertAdjacentElement('afterend', status);
    }
    return status;
}

function setUploadStatus(input, state, message) {
    const status = getUploadStatusElement(input);
    status.className = 'upload-verification-status ' + state;
    status.textContent = message;
}

function summarizeVerification(result) {
    const parts = [];
    if (Array.isArray(result.missing_fields) && result.missing_fields.length) {
        parts.push('missing ' + result.missing_fields.join(', '));
    }
    if (Array.isArray(result.risk_flags) && result.risk_flags.length) {
        parts.push(result.risk_flags.join(', '));
    }
    if (result.error) {
        parts.push(result.error);
    }
    return parts.join('; ');
}

function updateSubmitAvailability() {
    const form = document.querySelector('.document-form');
    const submitButton = form?.querySelector('button[type="submit"]');
    if (!submitButton) return;

    const activeChecks = Object.values(uploadVerificationState).some((item) => item.status === 'checking');
    submitButton.disabled = activeChecks;
}

async function verifyUploadInput(input) {
    const expectedType = uploadExpectedTypes[input.name];
    if (!expectedType || !input.files || !input.files[0]) return;

    uploadVerificationState[input.name] = { status: 'checking' };
    setUploadStatus(input, 'checking', 'Checking this file now...');
    updateSubmitAvailability();

    const token = document.querySelector('input[name="csrf_token"]')?.value || '';
    const formData = new FormData();
    formData.append('csrf_token', token);
    formData.append('expected_type', expectedType);
    formData.append('document', input.files[0]);

    try {
        const response = await fetch(uploadVerificationUrl, {
            method: 'POST',
            body: formData,
            credentials: 'same-origin'
        });
        const payload = await response.json();
        if (!payload.success) {
            throw new Error(payload.message || 'Verification failed');
        }

        const verification = payload.data?.verification || {};
        const status = String(verification.status || 'REVIEW').toUpperCase();
        if (status === 'APPROVED') {
            uploadVerificationState[input.name] = { status: 'approved' };
            setUploadStatus(input, 'approved', 'Accepted by automated verification.');
        } else if (status === 'REJECTED') {
            const details = summarizeVerification(verification);
            uploadVerificationState[input.name] = { status: 'review' };
            setUploadStatus(input, 'review', 'Needs server review' + (details ? ': ' + details : '.'));
        } else {
            const details = summarizeVerification(verification);
            uploadVerificationState[input.name] = { status: 'review' };
            setUploadStatus(input, 'review', 'Accepted for manual review' + (details ? ': ' + details : '.'));
        }
    } catch (error) {
        uploadVerificationState[input.name] = { status: 'review' };
        setUploadStatus(input, 'review', error.message || 'Quick verification could not complete. The file will be checked during submission.');
    } finally {
        updateSubmitAvailability();
    }
}

Object.keys(uploadExpectedTypes).forEach((fieldName) => {
    const input = document.querySelector('input[type="file"][name="' + fieldName + '"]');
    if (!input) return;
    input.addEventListener('change', function() {
        delete uploadVerificationState[this.name];
        if (this.files && this.files[0]) {
            verifyUploadInput(this);
        } else {
            setUploadStatus(this, '', '');
            updateSubmitAvailability();
        }
    });
});

document.querySelector('.document-form')?.addEventListener('submit', function(event) {
    const activeChecks = Object.values(uploadVerificationState).some((item) => item.status === 'checking');
    if (activeChecks) {
        event.preventDefault();
        alert('Please wait for document verification to finish.');
    }
});
</script>

<?php include '../../includes/footer.php'; ?>
