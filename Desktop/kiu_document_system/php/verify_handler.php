<?php
require_once dirname(__DIR__) . '/config/init.php';

function run_document_verification($filePath, $expectedType = null, $fileHash = null) {
    global $db;
    $engine = new DocumentVerificationEngine($db);
    return $engine->verify($filePath, $expectedType, $fileHash);
}
