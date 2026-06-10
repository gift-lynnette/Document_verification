<?php
/**
 * Green Card Search + Reporting Service
 * Provides query builders for search/report endpoints.
 */

function gc_normalize_text_input($value, $maxLength = 100): string
{
    $text = trim((string)$value);
    if ($text === '') {
        return '';
    }

    if (strlen($text) > $maxLength) {
        $text = substr($text, 0, $maxLength);
    }

    return $text;
}

function gc_normalize_registration_number($value): string
{
    $reg = strtoupper(trim((string)$value));
    if ($reg === '') {
        return '';
    }

    if (strlen($reg) > 50) {
        $reg = substr($reg, 0, 50);
    }

    if (!preg_match('/^[A-Z0-9\/-]+$/', $reg)) {
        return '';
    }

    return $reg;
}

function gc_normalize_document_type($value): string
{
    $doc = trim((string)$value);
    if ($doc === '') {
        return '';
    }

    if (strlen($doc) > 50) {
        $doc = substr($doc, 0, 50);
    }

    if (!preg_match('/^[a-zA-Z0-9_\-]+$/', $doc)) {
        return '';
    }

    return $doc;
}

function gc_normalize_status($value): string
{
    $status = strtoupper(trim((string)$value));
    if (!in_array($status, ['APPROVED', 'REJECTED', 'REVIEW'], true)) {
        return '';
    }

    return $status;
}

function gc_normalize_date_input($value, $endOfDay = false): ?string
{
    $raw = trim((string)$value);
    if ($raw === '') {
        return null;
    }

    $dt = DateTime::createFromFormat('Y-m-d', $raw);
    if (!$dt || $dt->format('Y-m-d') !== $raw) {
        return null;
    }

    if ($endOfDay) {
        return $dt->format('Y-m-d 23:59:59');
    }

    return $dt->format('Y-m-d 00:00:00');
}

function gc_issue_date_column(PDO $db): string
{
    if (column_exists($db, 'green_cards', 'issue_date')) {
        return 'gc.issue_date';
    }

    if (column_exists($db, 'green_cards', 'issued_at')) {
        return 'gc.issued_at';
    }

    if (column_exists($db, 'green_cards', 'created_at')) {
        return 'gc.created_at';
    }

    return 'gc.card_id';
}

function gc_student_name_column(PDO $db): string
{
    if (column_exists($db, 'green_cards', 'full_name')) {
        return 'gc.full_name';
    }

    if (column_exists($db, 'document_submissions', 'full_name')) {
        return 'ds.full_name';
    }

    return "''";
}

function gc_document_uploads_join(PDO $db, array $filters): array
{
    if (!table_exists($db, 'document_uploads')) {
        return [
            'join' => "LEFT JOIN (SELECT NULL AS submission_id, NULL AS verification_status, NULL AS confidence_score, NULL AS document_type, NULL AS document_uploaded_at) du_latest ON du_latest.submission_id = gc.submission_id",
            'select' => [
                'du_latest.verification_status',
                'du_latest.confidence_score',
                'du_latest.document_type',
                'du_latest.document_uploaded_at'
            ],
            'params' => []
        ];
    }

    $docTypeColumn = null;
    if (column_exists($db, 'document_uploads', 'verification_document_type')) {
        $docTypeColumn = 'verification_document_type';
    } elseif (column_exists($db, 'document_uploads', 'document_type')) {
        $docTypeColumn = 'document_type';
    }

    $docStatusColumn = column_exists($db, 'document_uploads', 'verification_status') ? 'verification_status' : null;
    $confidenceColumn = column_exists($db, 'document_uploads', 'confidence_score') ? 'confidence_score' : null;

    $timestampColumn = null;
    if (column_exists($db, 'document_uploads', 'uploaded_at')) {
        $timestampColumn = 'uploaded_at';
    } elseif (column_exists($db, 'document_uploads', 'created_at')) {
        $timestampColumn = 'created_at';
    }

    if ($timestampColumn === null) {
        return [
            'join' => "LEFT JOIN (SELECT NULL AS submission_id, NULL AS verification_status, NULL AS confidence_score, NULL AS document_type, NULL AS document_uploaded_at) du_latest ON du_latest.submission_id = gc.submission_id",
            'select' => [
                'du_latest.verification_status',
                'du_latest.confidence_score',
                'du_latest.document_type',
                'du_latest.document_uploaded_at'
            ],
            'params' => []
        ];
    }

    $docWhere = [];
    $params = [];

    if (!empty($filters['document_type']) && $docTypeColumn) {
        $docWhere[] = "du.{$docTypeColumn} = :document_type";
        $params['document_type'] = $filters['document_type'];
    }

    if (!empty($filters['verification_status']) && $docStatusColumn) {
        $docWhere[] = "UPPER(du.{$docStatusColumn}) = :verification_status";
        $params['verification_status'] = $filters['verification_status'];
    }

    $docWhereSql = $docWhere ? 'WHERE ' . implode(' AND ', $docWhere) : '';
    $joinType = (!empty($filters['document_type']) || !empty($filters['verification_status'])) ? 'INNER JOIN' : 'LEFT JOIN';

    $docTypeExpr = $docTypeColumn ? "du.{$docTypeColumn}" : 'NULL';
    $docStatusExpr = $docStatusColumn ? "du.{$docStatusColumn}" : 'NULL';
    $confidenceExpr = $confidenceColumn ? "du.{$confidenceColumn}" : 'NULL';

    $subQuery = "
        SELECT
            du.submission_id,
            {$docStatusExpr} AS verification_status,
            {$confidenceExpr} AS confidence_score,
            {$docTypeExpr} AS document_type,
            du.{$timestampColumn} AS document_uploaded_at
        FROM document_uploads du
        INNER JOIN (
            SELECT submission_id, MAX({$timestampColumn}) AS max_uploaded
            FROM document_uploads du
            {$docWhereSql}
            GROUP BY submission_id
        ) latest ON latest.submission_id = du.submission_id AND du.{$timestampColumn} = latest.max_uploaded
        {$docWhereSql}
    ";

    $joinSql = "{$joinType} ({$subQuery}) du_latest ON du_latest.submission_id = gc.submission_id";

    return [
        'join' => $joinSql,
        'select' => [
            'du_latest.verification_status',
            'du_latest.confidence_score',
            'du_latest.document_type',
            'du_latest.document_uploaded_at'
        ],
        'params' => $params
    ];
}

function gc_build_green_card_query(PDO $db, array $filters): array
{
    $issueDateColumn = gc_issue_date_column($db);
    $nameColumn = gc_student_name_column($db);

    $select = [
        'gc.card_id',
        'gc.card_number',
        'gc.registration_number',
        "{$issueDateColumn} AS issue_date",
        'gc.is_active',
        'u.admission_number',
        'ds.status AS workflow_status'
    ];

    if (column_exists($db, 'green_cards', 'expiry_date')) {
        $select[] = 'gc.expiry_date';
    } elseif (column_exists($db, 'green_cards', 'expires_at')) {
        $select[] = 'gc.expires_at AS expiry_date';
    } else {
        $select[] = 'NULL AS expiry_date';
    }

    if (column_exists($db, 'green_cards', 'full_name')) {
        $select[] = 'gc.full_name AS student_name';
    } else {
        $select[] = 'ds.full_name AS student_name';
    }

    $joins = [
        'LEFT JOIN document_submissions ds ON ds.submission_id = gc.submission_id',
        'LEFT JOIN users u ON u.user_id = gc.user_id'
    ];

    $docJoin = gc_document_uploads_join($db, $filters);
    if ($docJoin['join'] !== '') {
        $joins[] = $docJoin['join'];
    }

    $select = array_merge($select, $docJoin['select']);

    $where = [];
    $params = $docJoin['params'];

    if (!empty($filters['registration_number'])) {
        $where[] = 'gc.registration_number = :registration_number';
        $params['registration_number'] = $filters['registration_number'];
    }

    if (!empty($filters['name']) && $nameColumn !== "''") {
        $where[] = "{$nameColumn} LIKE :student_name";
        $params['student_name'] = '%' . $filters['name'] . '%';
    }

    if (!empty($filters['date_from'])) {
        $where[] = "{$issueDateColumn} >= :date_from";
        $params['date_from'] = $filters['date_from'];
    }

    if (!empty($filters['date_to'])) {
        $where[] = "{$issueDateColumn} <= :date_to";
        $params['date_to'] = $filters['date_to'];
    }

    $whereSql = $where ? 'WHERE ' . implode(' AND ', $where) : '';

    return [
        'select' => $select,
        'joins' => $joins,
        'where' => $whereSql,
        'params' => $params,
        'issue_date_column' => $issueDateColumn
    ];
}

function gc_search_green_cards(PDO $db, array $filters, int $page, int $perPage): array
{
    $query = gc_build_green_card_query($db, $filters);

    $countSql = "SELECT COUNT(*) FROM green_cards gc\n" . implode("\n", $query['joins']) . "\n" . $query['where'];
    $countStmt = $db->prepare($countSql);
    foreach ($query['params'] as $key => $value) {
        $countStmt->bindValue(':' . $key, $value);
    }
    $countStmt->execute();
    $totalRecords = (int)$countStmt->fetchColumn();

    $offset = max(0, ($page - 1) * $perPage);

    $dataSql = "SELECT " . implode(', ', $query['select']) . "\nFROM green_cards gc\n" . implode("\n", $query['joins']) . "\n" . $query['where'] . "\nORDER BY {$query['issue_date_column']} DESC\nLIMIT :limit OFFSET :offset";
    $dataStmt = $db->prepare($dataSql);
    foreach ($query['params'] as $key => $value) {
        $dataStmt->bindValue(':' . $key, $value);
    }
    $dataStmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
    $dataStmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $dataStmt->execute();
    $records = $dataStmt->fetchAll(PDO::FETCH_ASSOC);

    $totalPages = $perPage > 0 ? (int)ceil($totalRecords / $perPage) : 0;

    return [
        'records' => $records,
        'total_records' => $totalRecords,
        'current_page' => $page,
        'total_pages' => $totalPages
    ];
}

function gc_build_summary(PDO $db, array $filters): array
{
    $query = gc_build_green_card_query($db, $filters);

    $summarySql = "SELECT\n" .
        "    COUNT(*) AS total_cards,\n" .
        "    SUM(CASE WHEN UPPER(COALESCE(du_latest.verification_status, 'REVIEW')) = 'APPROVED' THEN 1 ELSE 0 END) AS total_approved,\n" .
        "    SUM(CASE WHEN UPPER(COALESCE(du_latest.verification_status, 'REVIEW')) = 'REJECTED' THEN 1 ELSE 0 END) AS total_rejected,\n" .
        "    SUM(CASE WHEN UPPER(COALESCE(du_latest.verification_status, 'REVIEW')) = 'REVIEW' THEN 1 ELSE 0 END) AS total_review\n" .
        "FROM green_cards gc\n" . implode("\n", $query['joins']) . "\n" . $query['where'];

    $stmt = $db->prepare($summarySql);
    foreach ($query['params'] as $key => $value) {
        $stmt->bindValue(':' . $key, $value);
    }
    $stmt->execute();
    $summary = $stmt->fetch(PDO::FETCH_ASSOC) ?: [];

    return [
        'total_cards' => (int)($summary['total_cards'] ?? 0),
        'total_approved' => (int)($summary['total_approved'] ?? 0),
        'total_rejected' => (int)($summary['total_rejected'] ?? 0),
        'total_review' => (int)($summary['total_review'] ?? 0)
    ];
}

function gc_build_trends(PDO $db, array $filters): array
{
    $query = gc_build_green_card_query($db, $filters);

    $trendSql = "SELECT DATE_FORMAT({$query['issue_date_column']}, '%Y-%m') AS period, COUNT(*) AS total\n" .
        "FROM green_cards gc\n" . implode("\n", $query['joins']) . "\n" . $query['where'] . "\n" .
        "GROUP BY period\n" .
        "ORDER BY period ASC";

    $stmt = $db->prepare($trendSql);
    foreach ($query['params'] as $key => $value) {
        $stmt->bindValue(':' . $key, $value);
    }
    $stmt->execute();

    return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
}

function gc_generate_report_csv(array $records): string
{
    $stream = fopen('php://temp', 'r+');
    fputcsv($stream, [
        'student_name',
        'registration_number',
        'status',
        'issue_date',
        'confidence_score',
        'document_type',
        'card_number',
        'admission_number'
    ]);

    foreach ($records as $row) {
        fputcsv($stream, [
            (string)($row['student_name'] ?? ''),
            (string)($row['registration_number'] ?? ''),
            strtoupper((string)($row['verification_status'] ?? 'REVIEW')),
            (string)($row['issue_date'] ?? ''),
            (string)($row['confidence_score'] ?? ''),
            (string)($row['document_type'] ?? ''),
            (string)($row['card_number'] ?? ''),
            (string)($row['admission_number'] ?? '')
        ]);
    }

    rewind($stream);
    $csv = stream_get_contents($stream);
    fclose($stream);

    return $csv ?: '';
}
