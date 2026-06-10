<?php
if (php_sapi_name() !== 'cli') {
    exit("CLI only\n");
}

require_once __DIR__ . '/../config/init.php';

$submissionId = isset($argv[1]) ? (int)$argv[1] : 14;

$stmt = $db->prepare("SELECT submission_id, payment_amount_ocr, payment_currency, bank_slip_ocr_status FROM document_submissions WHERE submission_id = :submission_id LIMIT 1");
$stmt->execute(['submission_id' => $submissionId]);
$submission = $stmt->fetch(PDO::FETCH_ASSOC);

$stmt = $db->prepare("SELECT ocr_extracted_text, file_path, mime_type FROM document_uploads WHERE submission_id = :submission_id AND document_type IN ('bank_slip', 'bank-slip', 'receipt') LIMIT 1");
$stmt->execute(['submission_id' => $submissionId]);
$upload = $stmt->fetch(PDO::FETCH_ASSOC);

echo "Submission: " . json_encode($submission) . PHP_EOL;
echo "Upload meta: " . json_encode(['file_path' => $upload['file_path'] ?? null, 'mime_type' => $upload['mime_type'] ?? null]) . PHP_EOL;

$ocrText = (string)($upload['ocr_extracted_text'] ?? '');
echo "OCR preview: " . substr(preg_replace('/\s+/', ' ', $ocrText), 0, 500) . PHP_EOL;

$paymentData = kiu_extract_bank_slip_payment_data($ocrText);
echo "Parsed from OCR text: " . json_encode($paymentData) . PHP_EOL;
echo "OCR looks binary PDF: " . (kiu_is_likely_pdf_binary_blob($ocrText) ? 'yes' : 'no') . PHP_EOL;

$resolvedPath = (string)($upload['file_path'] ?? '');
if ($resolvedPath !== '' && !is_file($resolvedPath) && defined('SITE_ROOT')) {
    $candidate = rtrim(SITE_ROOT, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . ltrim($resolvedPath, DIRECTORY_SEPARATOR);
    if (is_file($candidate)) {
        $resolvedPath = $candidate;
    }
}

if ($resolvedPath !== '' && is_file($resolvedPath)) {
    $directText = kiu_extract_text_with_pdftotext($resolvedPath);
    if (trim($directText) === '') {
        $directText = kiu_extract_text_with_tesseract($resolvedPath);
    }
    echo "Direct text preview: " . substr(preg_replace('/\s+/', ' ', (string)$directText), 0, 500) . PHP_EOL;
    echo "Parsed from direct text: " . json_encode(kiu_extract_bank_slip_payment_data((string)$directText)) . PHP_EOL;
}

$resolved = extract_bank_slip_amount_for_submission($db, $submissionId, 0);
echo "Resolver output: " . json_encode($resolved) . PHP_EOL;
