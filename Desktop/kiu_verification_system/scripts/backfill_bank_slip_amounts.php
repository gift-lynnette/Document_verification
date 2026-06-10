<?php
/**
 * Backfill OCR payment amount/status for existing submissions.
 *
 * Usage:
 *   C:\xampp\php\php.exe scripts\backfill_bank_slip_amounts.php
 */

if (php_sapi_name() !== 'cli') {
    http_response_code(403);
    exit("This script can only run from CLI.\n");
}

require_once __DIR__ . '/../config/init.php';

try {
    $hasPaymentAmountOcr = column_exists($db, 'document_submissions', 'payment_amount_ocr');
    $hasBankSlipStatus = column_exists($db, 'document_submissions', 'bank_slip_ocr_status');

    echo 'Schema: payment_amount_ocr=' . ($hasPaymentAmountOcr ? 'yes' : 'no') . ', bank_slip_ocr_status=' . ($hasBankSlipStatus ? 'yes' : 'no') . PHP_EOL;

    $paymentAmountSelect = $hasPaymentAmountOcr ? 'ds.payment_amount_ocr' : 'NULL AS payment_amount_ocr';
    $bankSlipStatusSelect = $hasBankSlipStatus ? 'ds.bank_slip_ocr_status' : "'' AS bank_slip_ocr_status";

    $query = "
        SELECT DISTINCT ds.submission_id, {$paymentAmountSelect}, {$bankSlipStatusSelect}
        FROM document_submissions ds
        INNER JOIN document_uploads du ON du.submission_id = ds.submission_id
        WHERE du.document_type IN ('bank_slip', 'bank-slip', 'receipt')
        ORDER BY ds.submission_id ASC
    ";

    $stmt = $db->query($query);
    $submissions = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $total = count($submissions);
    $updated = 0;
    $skipped = 0;

    foreach ($submissions as $row) {
        $submissionId = (int)$row['submission_id'];
        $beforeAmount = isset($row['payment_amount_ocr']) ? (float)$row['payment_amount_ocr'] : 0.0;
        $beforeStatus = (string)($row['bank_slip_ocr_status'] ?? '');

        $result = extract_bank_slip_amount_for_submission($db, $submissionId, 0.00);

        $afterSelect = $hasPaymentAmountOcr ? 'payment_amount_ocr' : 'NULL AS payment_amount_ocr';
        $afterSelect .= ', ';
        $afterSelect .= $hasBankSlipStatus ? 'bank_slip_ocr_status' : "'' AS bank_slip_ocr_status";

        $afterStmt = $db->prepare('SELECT ' . $afterSelect . ' FROM document_submissions WHERE submission_id = :submission_id LIMIT 1');
        $afterStmt->execute(['submission_id' => $submissionId]);
        $after = $afterStmt->fetch(PDO::FETCH_ASSOC);

        $afterAmount = isset($after['payment_amount_ocr']) ? (float)$after['payment_amount_ocr'] : 0.0;
        $afterStatus = (string)($after['bank_slip_ocr_status'] ?? '');

        $changed = ($afterAmount !== $beforeAmount) || ($afterStatus !== $beforeStatus);
        if ($changed) {
            $updated++;
            echo "Updated submission {$submissionId}: amount={$afterAmount}, status={$afterStatus}, source=" . ($result['source'] ?? 'unknown') . PHP_EOL;
        } else {
            $skipped++;
        }
    }

    echo PHP_EOL;
    echo "Backfill complete." . PHP_EOL;
    echo "Total submissions scanned: {$total}" . PHP_EOL;
    echo "Updated: {$updated}" . PHP_EOL;
    echo "Skipped: {$skipped}" . PHP_EOL;
} catch (Exception $e) {
    fwrite(STDERR, 'Backfill failed: ' . $e->getMessage() . PHP_EOL);
    exit(1);
}
