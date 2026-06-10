<?php
declare(strict_types=1);

$projectRoot = dirname(__DIR__);

require_once $projectRoot . '/config/database.php';

const PDF_SOURCE = 'KIU_Programmes in usd.pdf';
const PDF_EFFECTIVE_DATE = '2026-03-17';
const PDF_EXCHANGE_RATE = 3770.95;

/**
 * Official tuition figures extracted from KIU_Programmes in usd.pdf.
 * Functional and research fees follow the same PDF footer table.
 */
$verifiedUpdates = [
    [
        'program_name' => 'Bachelor of Science in Computer Science',
        'faculty' => 'Faculty of Science and Technology',
        'student_type' => 'undergraduate',
        'study_mode' => 'full_time',
        'semester' => 'semester_1',
        'tuition_amount' => 1862000.00,
        'functional_fees' => 500000.00,
        'other_fees' => 0.00,
        'minimum_payment' => 1181000.00,
        'payment_deadline' => '2024-10-31',
        'late_payment_penalty' => 60000.00,
        'academic_year' => '2024/2025',
        'notes' => 'Alias of official PDF label "Bachelor of Computer Science".'
    ],
    [
        'program_name' => 'Bachelor of Information Technology',
        'faculty' => 'Faculty of Science and Technology',
        'student_type' => 'undergraduate',
        'study_mode' => 'full_time',
        'semester' => 'semester_1',
        'tuition_amount' => 1862000.00,
        'functional_fees' => 500000.00,
        'other_fees' => 0.00,
        'minimum_payment' => 1181000.00,
        'payment_deadline' => '2025-10-31',
        'late_payment_penalty' => 100000.00,
        'academic_year' => '2025/2026',
        'notes' => 'Exact KIU PDF match.'
    ],
    [
        'program_name' => 'Bachelor of Business Administration',
        'faculty' => 'Faculty of Business and Management',
        'student_type' => 'undergraduate',
        'study_mode' => 'full_time',
        'semester' => 'semester_1',
        'tuition_amount' => 1105100.00,
        'functional_fees' => 500000.00,
        'other_fees' => 0.00,
        'minimum_payment' => 802550.00,
        'payment_deadline' => '2024-10-31',
        'late_payment_penalty' => 100000.00,
        'academic_year' => '2024/2025',
        'notes' => 'Exact KIU PDF match.'
    ],
    [
        'program_name' => 'Master of Business Administration',
        'faculty' => 'Faculty of Business and Management',
        'student_type' => 'postgraduate',
        'study_mode' => 'part_time',
        'semester' => 'semester_1',
        'tuition_amount' => 2050000.00,
        'functional_fees' => 0.00,
        'other_fees' => 0.00,
        'minimum_payment' => 1525000.00,
        'payment_deadline' => '2024-10-31',
        'late_payment_penalty' => 150000.00,
        'academic_year' => '2024/2025',
        'notes' => 'Matches KIU PDF MBA tuition; minimum includes research fee policy.'
    ],
    [
        'program_name' => 'Bachelor of Engineering (Civil)',
        'faculty' => 'Faculty of Engineering',
        'student_type' => 'undergraduate',
        'study_mode' => 'full_time',
        'semester' => 'semester_1',
        'tuition_amount' => 2593000.00,
        'functional_fees' => 500000.00,
        'other_fees' => 0.00,
        'minimum_payment' => 1546500.00,
        'payment_deadline' => '2024-10-31',
        'late_payment_penalty' => 120000.00,
        'academic_year' => '2024/2025',
        'notes' => 'Mapped to KIU PDF engineering group: Telecommunication/Electrical/Mechanical/Civil/Computer Engineering.'
    ],
    [
        'program_name' => 'Master of Public Health',
        'faculty' => 'Faculty of Health Sciences',
        'student_type' => 'postgraduate',
        'study_mode' => 'evening',
        'semester' => 'semester_1',
        'tuition_amount' => 2000000.00,
        'functional_fees' => 0.00,
        'other_fees' => 0.00,
        'minimum_payment' => 1500000.00,
        'payment_deadline' => '2025-03-31',
        'late_payment_penalty' => 150000.00,
        'academic_year' => '2024/2025',
        'notes' => 'Matches KIU PDF MPH tuition; minimum includes research fee policy.'
    ],
    [
        'program_name' => 'Bachelor of Arts in Economics',
        'faculty' => 'Faculty of Social Sciences',
        'student_type' => 'undergraduate',
        'study_mode' => 'full_time',
        'semester' => 'semester_1',
        'tuition_amount' => 1105100.00,
        'functional_fees' => 500000.00,
        'other_fees' => 0.00,
        'minimum_payment' => 802550.00,
        'payment_deadline' => '2024-10-31',
        'late_payment_penalty' => 90000.00,
        'academic_year' => '2024/2025',
        'notes' => 'Exact KIU PDF match.'
    ],
    [
        'program_name' => 'Bachelor of Science in Mathematics',
        'faculty' => 'Faculty of Science and Technology',
        'student_type' => 'undergraduate',
        'study_mode' => 'full_time',
        'semester' => 'semester_1',
        'tuition_amount' => 1862000.00,
        'functional_fees' => 500000.00,
        'other_fees' => 0.00,
        'minimum_payment' => 1181000.00,
        'payment_deadline' => '2025-10-31',
        'late_payment_penalty' => 100000.00,
        'academic_year' => '2025/2026',
        'notes' => 'Exact KIU PDF match.'
    ],
];

$exchangeRateYears = [2024, 2025, 2026];
$exchangeRateSemesters = ['semester_1'];

function upsertFeeStructure(PDO $db, array $row): void
{
    $existing = $db->prepare(
        'SELECT fee_id
         FROM fee_structures
         WHERE program_name = :program_name
           AND student_type = :student_type
           AND study_mode = :study_mode
           AND academic_year = :academic_year
           AND semester = :semester
         LIMIT 1'
    );

    $existing->execute([
        'program_name' => $row['program_name'],
        'student_type' => $row['student_type'],
        'study_mode' => $row['study_mode'],
        'academic_year' => $row['academic_year'],
        'semester' => $row['semester'],
    ]);

    $insertParams = [
        'program_name' => $row['program_name'],
        'faculty' => $row['faculty'],
        'student_type' => $row['student_type'],
        'study_mode' => $row['study_mode'],
        'academic_year' => $row['academic_year'],
        'semester' => $row['semester'],
        'tuition_amount' => $row['tuition_amount'],
        'functional_fees' => $row['functional_fees'],
        'other_fees' => $row['other_fees'],
        'minimum_payment' => $row['minimum_payment'],
        'currency' => 'UGX',
        'payment_deadline' => $row['payment_deadline'],
        'late_payment_penalty' => $row['late_payment_penalty'],
        'effective_from' => PDF_EFFECTIVE_DATE,
        'created_by' => 1,
    ];

    $feeId = $existing->fetchColumn();

    if ($feeId !== false) {
        $update = $db->prepare(
            'UPDATE fee_structures
             SET faculty = :faculty,
                 tuition_amount = :tuition_amount,
                 functional_fees = :functional_fees,
                 other_fees = :other_fees,
                 minimum_payment = :minimum_payment,
                 currency = :currency,
                 payment_deadline = :payment_deadline,
                 late_payment_penalty = :late_payment_penalty,
                 is_active = 1,
                 effective_from = :effective_from,
                 effective_to = NULL,
                 created_by = :created_by,
                 updated_at = NOW()
             WHERE fee_id = :fee_id'
        );

        $update->execute([
            'faculty' => $row['faculty'],
            'tuition_amount' => $row['tuition_amount'],
            'functional_fees' => $row['functional_fees'],
            'other_fees' => $row['other_fees'],
            'minimum_payment' => $row['minimum_payment'],
            'currency' => 'UGX',
            'payment_deadline' => $row['payment_deadline'],
            'late_payment_penalty' => $row['late_payment_penalty'],
            'effective_from' => PDF_EFFECTIVE_DATE,
            'created_by' => 1,
            'fee_id' => $feeId,
        ]);
        return;
    }

    $insert = $db->prepare(
        'INSERT INTO fee_structures (
            program_name,
            faculty,
            student_type,
            study_mode,
            academic_year,
            semester,
            tuition_amount,
            functional_fees,
            other_fees,
            minimum_payment,
            currency,
            payment_deadline,
            late_payment_penalty,
            is_active,
            effective_from,
            effective_to,
            created_by,
            created_at,
            updated_at
        ) VALUES (
            :program_name,
            :faculty,
            :student_type,
            :study_mode,
            :academic_year,
            :semester,
            :tuition_amount,
            :functional_fees,
            :other_fees,
            :minimum_payment,
            :currency,
            :payment_deadline,
            :late_payment_penalty,
            1,
            :effective_from,
            NULL,
            :created_by,
            NOW(),
            NOW()
        )'
    );

    $insert->execute($insertParams);
}

function upsertExchangeRate(PDO $db, int $year, string $semester): void
{
    $deactivate = $db->prepare(
        'UPDATE semester_exchange_rates
         SET is_active = 0
         WHERE intake_year = :intake_year
           AND intake_semester = :intake_semester
           AND is_active = 1'
    );

    $deactivate->execute([
        'intake_year' => $year,
        'intake_semester' => $semester,
    ]);

    $existing = $db->prepare(
        'SELECT rate_id
         FROM semester_exchange_rates
         WHERE intake_year = :intake_year
           AND intake_semester = :intake_semester
           AND effective_from = :effective_from
         LIMIT 1'
    );

    $insertParams = [
        'intake_year' => $year,
        'intake_semester' => $semester,
        'usd_to_ugx_rate' => PDF_EXCHANGE_RATE,
        'effective_from' => PDF_EFFECTIVE_DATE,
        'published_by_user_id' => 1,
        'notes' => 'Loaded from ' . PDF_SOURCE . ' using 1 USD = 3,770.95 UGX.',
    ];

    $existing->execute([
        'intake_year' => $year,
        'intake_semester' => $semester,
        'effective_from' => PDF_EFFECTIVE_DATE,
    ]);

    $rateId = $existing->fetchColumn();

    if ($rateId !== false) {
        $update = $db->prepare(
            'UPDATE semester_exchange_rates
             SET usd_to_ugx_rate = :usd_to_ugx_rate,
                 published_by_user_id = :published_by_user_id,
                 published_at = NOW(),
                 is_active = 1,
                 notes = :notes
             WHERE rate_id = :rate_id'
        );

        $update->execute([
            'usd_to_ugx_rate' => PDF_EXCHANGE_RATE,
            'published_by_user_id' => 1,
            'notes' => 'Loaded from ' . PDF_SOURCE . ' using 1 USD = 3,770.95 UGX.',
            'rate_id' => $rateId,
        ]);
        return;
    }

    $insert = $db->prepare(
        'INSERT INTO semester_exchange_rates (
            intake_year,
            intake_semester,
            usd_to_ugx_rate,
            effective_from,
            published_by_user_id,
            published_at,
            is_active,
            notes
        ) VALUES (
            :intake_year,
            :intake_semester,
            :usd_to_ugx_rate,
            :effective_from,
            :published_by_user_id,
            NOW(),
            1,
            :notes
        )'
    );

    $insert->execute($insertParams);
}

try {
    $database = new Database();
    $db = $database->getConnection();
    $db->beginTransaction();

    foreach ($verifiedUpdates as $row) {
        upsertFeeStructure($db, $row);
    }

    foreach ($exchangeRateYears as $year) {
        foreach ($exchangeRateSemesters as $semester) {
            upsertExchangeRate($db, $year, $semester);
        }
    }

    $db->commit();

    echo "Updated fee structures from " . PDF_SOURCE . PHP_EOL;
    echo "Applied " . count($verifiedUpdates) . " verified programme updates." . PHP_EOL;
    echo "Loaded exchange rate " . number_format(PDF_EXCHANGE_RATE, 2) . " UGX/USD for " . count($exchangeRateYears) * count($exchangeRateSemesters) . " semester records." . PHP_EOL;
    echo PHP_EOL;
    echo "Updated programmes:" . PHP_EOL;

    foreach ($verifiedUpdates as $row) {
        echo '- ' . $row['program_name']
            . ' [' . $row['semester'] . ']'
            . ' => tuition ' . number_format((float)$row['tuition_amount'], 2)
            . ', functional ' . number_format((float)$row['functional_fees'], 2)
            . ', minimum ' . number_format((float)$row['minimum_payment'], 2)
            . PHP_EOL;
    }
} catch (Throwable $e) {
    if (isset($db) && $db instanceof PDO && $db->inTransaction()) {
        $db->rollBack();
    }

    fwrite(STDERR, 'Fee structure update failed: ' . $e->getMessage() . PHP_EOL);
    exit(1);
}
