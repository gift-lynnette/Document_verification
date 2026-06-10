<?php
require_once '../../../config/init.php';

$registrationNumber = strtoupper(trim((string)($_GET['reg'] ?? '')));
if ($registrationNumber === '' || !preg_match('/^[A-Z0-9\/\-]{4,50}$/', $registrationNumber)) {
    json_response(false, 'Valid registration number is required', [], 400);
}

try {
    $stmt = $db->prepare("
        SELECT gc.registration_number, gc.card_number, gc.full_name, gc.program, gc.faculty,
               gc.issue_date, gc.expiry_date, gc.academic_year, gc.semester, gc.is_active,
               u.admission_number
        FROM green_cards gc
        LEFT JOIN users u ON u.user_id = gc.user_id
        WHERE gc.registration_number = :registration_number
        LIMIT 1
    ");
    $stmt->execute(['registration_number' => $registrationNumber]);
    $card = $stmt->fetch();

    if (!$card) {
        json_response(false, 'Green card not found', [], 404);
    }

    $status = 'valid';
    if (!(int)$card['is_active']) {
        $status = 'revoked';
    } elseif (!empty($card['expiry_date']) && $card['expiry_date'] < date('Y-m-d')) {
        $status = 'expired';
    }

    json_response(true, 'Green card found', [
        'registration_number' => $card['registration_number'],
        'card_number' => $card['card_number'],
        'full_name' => $card['full_name'],
        'program' => $card['program'],
        'faculty' => $card['faculty'],
        'admission_number' => $card['admission_number'],
        'issue_date' => $card['issue_date'],
        'expiry_date' => $card['expiry_date'],
        'academic_year' => $card['academic_year'],
        'semester' => $card['semester'],
        'status' => $status
    ]);
} catch (Throwable $e) {
    error_log('green-card search error: ' . $e->getMessage());
    json_response(false, 'Search failed', [], 500);
}

