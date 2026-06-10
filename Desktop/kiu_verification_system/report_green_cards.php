<?php
require_once __DIR__ . '/config/init.php';
require_login();
require_role([ROLE_ADMIN, ROLE_REGISTRAR, ROLE_FINANCE]);

$rawStatus = (string)($_GET['status'] ?? '');
$rawDocType = (string)($_GET['document_type'] ?? '');
$rawDateFrom = (string)($_GET['date_from'] ?? '');
$rawDateTo = (string)($_GET['date_to'] ?? '');
$rawName = (string)($_GET['name'] ?? '');
$rawRegistration = (string)($_GET['registration_number'] ?? '');
$export = strtolower((string)($_GET['export'] ?? ($_GET['format'] ?? '')));

if ($export !== 'csv') {
    header('Content-Type: application/json');
}

$filters = [
    'registration_number' => gc_normalize_registration_number($rawRegistration),
    'name' => gc_normalize_text_input($rawName, 100),
    'verification_status' => gc_normalize_status($rawStatus),
    'document_type' => gc_normalize_document_type($rawDocType),
    'date_from' => gc_normalize_date_input($rawDateFrom, false),
    'date_to' => gc_normalize_date_input($rawDateTo, true)
];

if ($rawStatus !== '' && $filters['verification_status'] === '') {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Invalid status filter']);
    exit;
}

if ($rawDocType !== '' && $filters['document_type'] === '') {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Invalid document type']);
    exit;
}

if ($rawDateFrom !== '' && $filters['date_from'] === null) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Invalid date_from value']);
    exit;
}

if ($rawDateTo !== '' && $filters['date_to'] === null) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Invalid date_to value']);
    exit;
}

if ($filters['date_from'] && $filters['date_to']) {
    if (strtotime($filters['date_from']) > strtotime($filters['date_to'])) {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => 'date_from cannot be after date_to']);
        exit;
    }
}

$page = max(1, (int)($_GET['page'] ?? 1));
$perPage = (int)($_GET['per_page'] ?? (defined('RECORDS_PER_PAGE') ? RECORDS_PER_PAGE : 20));
$perPage = max(1, min($perPage, 100));

try {
    if ($export === 'csv') {
        $exportLimit = (int)($_GET['export_limit'] ?? 2000);
        $exportLimit = max(1, min($exportLimit, 5000));

        $result = gc_search_green_cards($db, $filters, 1, $exportLimit);
        $records = $result['records'];

        foreach ($records as &$record) {
            $record['verification_status'] = strtoupper((string)($record['verification_status'] ?? 'REVIEW'));
        }
        unset($record);

        $csv = gc_generate_report_csv($records);
        $fileName = 'green_card_report_' . date('Ymd_His') . '.csv';

        header('Content-Type: text/csv; charset=UTF-8');
        header('Content-Disposition: attachment; filename="' . $fileName . '"');
        echo $csv;
        exit;
    }

    $summary = gc_build_summary($db, $filters);
    $trends = gc_build_trends($db, $filters);
    $details = gc_search_green_cards($db, $filters, $page, $perPage);

    foreach ($details['records'] as &$record) {
        $confidence = isset($record['confidence_score']) ? (int)$record['confidence_score'] : null;
        $record['verification_status'] = strtoupper((string)($record['verification_status'] ?? 'REVIEW'));
        $record['confidence_score'] = $confidence;
        $record['suspicious'] = $confidence !== null ? ($confidence < 50) : false;
    }
    unset($record);

    $audit = new AuditLog($db);
    $audit->log(
        'GREEN_CARD_REPORT',
        'green_cards',
        null,
        null,
        null,
        json_encode([
            'status' => $filters['verification_status'],
            'document_type' => $filters['document_type'],
            'date_from' => $filters['date_from'],
            'date_to' => $filters['date_to'],
            'page' => $page,
            'per_page' => $perPage
        ])
    );

    echo json_encode([
        'status' => 'success',
        'message' => 'Report generated',
        'data' => [
            'summary' => $summary,
            'trends' => $trends,
            'details' => $details
        ]
    ]);
} catch (Exception $e) {
    error_log('Green card report failed: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Report generation failed']);
}
