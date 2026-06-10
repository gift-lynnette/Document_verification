<?php
/**
 * Ensure document_submissions has columns required for OCR backfill persistence.
 *
 * Usage:
 *   C:\xampp\php\php.exe scripts\apply_ocr_backfill_columns.php
 */

if (php_sapi_name() !== 'cli') {
    http_response_code(403);
    exit("This script can only run from CLI.\n");
}

require_once __DIR__ . '/../config/init.php';

$columns = [
    'payment_amount_ocr' => "ALTER TABLE document_submissions ADD COLUMN payment_amount_ocr DECIMAL(12,2) NULL AFTER payment_amount",
    'bank_slip_ocr_status' => "ALTER TABLE document_submissions ADD COLUMN bank_slip_ocr_status VARCHAR(32) NULL AFTER payment_amount_ocr"
];

try {
    foreach ($columns as $column => $ddl) {
        if (column_exists($db, 'document_submissions', $column)) {
            echo "Exists: {$column}" . PHP_EOL;
            continue;
        }

        $db->exec($ddl);
        echo "Added: {$column}" . PHP_EOL;
    }

    echo 'Schema update complete.' . PHP_EOL;
} catch (Exception $e) {
    fwrite(STDERR, 'Schema update failed: ' . $e->getMessage() . PHP_EOL);
    exit(1);
}
