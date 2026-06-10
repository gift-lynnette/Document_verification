<?php
require_once dirname(__DIR__) . '/config/init.php';
require_login();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_response(false, 'POST upload required', [], 405);
}

if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
    json_response(false, 'Invalid request', [], 400);
}

$file = $_FILES['document'] ?? null;
$expectedType = $_POST['expected_type'] ?? null;
$allowedExpectedTypes = [
    'admission_letter',
    'certificate',
    'academic_supporting_document',
    'national_id_passport',
    'former_school_id',
    'passport_photo',
    'bank_slip',
    'bursary_award_letter'
];
if ($expectedType !== null && !in_array($expectedType, $allowedExpectedTypes, true)) {
    json_response(false, 'Invalid expected document type', [], 400);
}

if (!$file || ($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
    json_response(false, 'No file uploaded', [], 400);
}

$uploader = new FileUpload(UPLOAD_DIR . 'verification/', ALLOWED_DOCUMENT_TYPES, DOCUMENT_UPLOAD_MAX_SIZE);
$uploadResult = $uploader->upload($file);
if (!$uploadResult) {
    json_response(false, implode(' ', $uploader->getErrors()), [], 400);
}

$pythonExpectedTypes = [
    'admission_letter' => 'admission_letter',
    'certificate' => 'certificate',
    'academic_supporting_document' => 'certificate',
    'national_id_passport' => 'national_id_passport',
    'former_school_id' => 'former_school_id',
    'passport_photo' => 'passport_photo',
    'bank_slip' => 'bank_slip',
    'bursary_award_letter' => 'award_letter'
];

$engine = new DocumentVerificationEngine($db);
$result = $engine->verify(
    $uploadResult['path'],
    $expectedType !== null ? ($pythonExpectedTypes[$expectedType] ?? $expectedType) : null,
    $uploadResult['hash']
);

json_response(true, 'Document processed', [
    'file_path' => str_replace('\\', '/', $uploadResult['path']),
    'verification' => $result
]);
