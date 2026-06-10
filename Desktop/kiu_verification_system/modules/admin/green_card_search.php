<?php
require_once '../../config/init.php';
require_login();
require_role([ROLE_ADMIN, ROLE_REGISTRAR, ROLE_FINANCE]);

$page_title = 'Green Card Search & Reports';

$rawRegistration = (string)($_GET['registration_number'] ?? '');
$rawName = (string)($_GET['name'] ?? '');
$rawStatus = (string)($_GET['status'] ?? '');
$rawDocType = (string)($_GET['document_type'] ?? '');
$rawDateFrom = (string)($_GET['date_from'] ?? '');
$rawDateTo = (string)($_GET['date_to'] ?? '');

$filters = [
    'registration_number' => gc_normalize_registration_number($rawRegistration),
    'name' => gc_normalize_text_input($rawName, 100),
    'verification_status' => gc_normalize_status($rawStatus),
    'document_type' => gc_normalize_document_type($rawDocType),
    'date_from' => gc_normalize_date_input($rawDateFrom, false),
    'date_to' => gc_normalize_date_input($rawDateTo, true)
];

$errors = [];

if ($rawRegistration !== '' && $filters['registration_number'] === '') {
    $errors[] = 'Registration number format is invalid.';
}
if ($rawStatus !== '' && $filters['verification_status'] === '') {
    $errors[] = 'Status filter is invalid.';
}
if ($rawDocType !== '' && $filters['document_type'] === '') {
    $errors[] = 'Document type filter is invalid.';
}
if ($rawDateFrom !== '' && $filters['date_from'] === null) {
    $errors[] = 'Start date is invalid.';
}
if ($rawDateTo !== '' && $filters['date_to'] === null) {
    $errors[] = 'End date is invalid.';
}
if ($filters['date_from'] && $filters['date_to']) {
    if (strtotime($filters['date_from']) > strtotime($filters['date_to'])) {
        $errors[] = 'Start date cannot be after end date.';
    }
}

$page = max(1, (int)($_GET['page'] ?? 1));
$perPage = (int)($_GET['per_page'] ?? (defined('RECORDS_PER_PAGE') ? RECORDS_PER_PAGE : 20));
$perPage = max(1, min($perPage, 100));

$results = null;
$summary = null;
$trends = [];

$hasQuery = ($rawRegistration !== '' || $rawName !== '' || $rawStatus !== '' || $rawDocType !== '' || $rawDateFrom !== '' || $rawDateTo !== '');

if ($hasQuery && empty($errors)) {
    $results = gc_search_green_cards($db, $filters, $page, $perPage);
    $summary = gc_build_summary($db, $filters);
    $trends = gc_build_trends($db, $filters);
}

function gc_badge_class($status)
{
    $map = [
        'APPROVED' => 'badge-success',
        'REJECTED' => 'badge-danger',
        'REVIEW' => 'badge-warning'
    ];
    return $map[$status] ?? 'badge-secondary';
}

include '../../includes/header.php';
?>

<div class="dashboard">
    <div class="page-header">
        <h1>Green Card Search & Reports</h1>
        <p>Search green cards, filter by verification status, and export reports.</p>
    </div>

    <?php if (!empty($errors)): ?>
        <div class="alert alert-danger">
            <?php echo htmlspecialchars(implode(' ', $errors)); ?>
        </div>
    <?php endif; ?>

    <div class="card">
        <div class="card-header">
            <h3>Search Filters</h3>
        </div>
        <div class="card-body">
            <form method="GET">
                <div class="form-row">
                    <div class="form-group" style="flex:1;">
                        <label>Registration Number</label>
                        <input class="form-control" type="text" name="registration_number" value="<?php echo htmlspecialchars($rawRegistration); ?>" placeholder="e.g. 2026-01-1000">
                    </div>
                    <div class="form-group" style="flex:1;">
                        <label>Student Name</label>
                        <input class="form-control" type="text" name="name" value="<?php echo htmlspecialchars($rawName); ?>" placeholder="Partial name">
                    </div>
                    <div class="form-group" style="flex:1;">
                        <label>Status</label>
                        <select class="form-control" name="status">
                            <option value="">All</option>
                            <option value="APPROVED" <?php echo $rawStatus === 'APPROVED' ? 'selected' : ''; ?>>APPROVED</option>
                            <option value="REVIEW" <?php echo $rawStatus === 'REVIEW' ? 'selected' : ''; ?>>REVIEW</option>
                            <option value="REJECTED" <?php echo $rawStatus === 'REJECTED' ? 'selected' : ''; ?>>REJECTED</option>
                        </select>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group" style="flex:1;">
                        <label>Document Type</label>
                        <select class="form-control" name="document_type">
                            <option value="">All</option>
                            <option value="s6_certificate" <?php echo $rawDocType === 's6_certificate' ? 'selected' : ''; ?>>S6 Certificate</option>
                            <option value="national_id" <?php echo $rawDocType === 'national_id' ? 'selected' : ''; ?>>National ID</option>
                            <option value="school_id" <?php echo $rawDocType === 'school_id' ? 'selected' : ''; ?>>School ID</option>
                            <option value="passport_photo" <?php echo $rawDocType === 'passport_photo' ? 'selected' : ''; ?>>Passport Photo</option>
                            <option value="bank_slip" <?php echo $rawDocType === 'bank_slip' ? 'selected' : ''; ?>>Bank Slip</option>
                            <option value="admission_letter" <?php echo $rawDocType === 'admission_letter' ? 'selected' : ''; ?>>Admission Letter</option>
                            <option value="award_letter" <?php echo $rawDocType === 'award_letter' ? 'selected' : ''; ?>>Award Letter</option>
                            <option value="other" <?php echo $rawDocType === 'other' ? 'selected' : ''; ?>>Other</option>
                        </select>
                    </div>
                    <div class="form-group" style="flex:1;">
                        <label>Issue Date From</label>
                        <input class="form-control" type="date" name="date_from" value="<?php echo htmlspecialchars($rawDateFrom); ?>">
                    </div>
                    <div class="form-group" style="flex:1;">
                        <label>Issue Date To</label>
                        <input class="form-control" type="date" name="date_to" value="<?php echo htmlspecialchars($rawDateTo); ?>">
                    </div>
                    <div class="form-group" style="align-self:flex-end;">
                        <button class="btn btn-primary" type="submit">Search</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <?php if ($summary !== null): ?>
        <div class="stats-grid">
            <div class="stat-card stat-primary">
                <div class="stat-content">
                    <h3><?php echo (int)$summary['total_cards']; ?></h3>
                    <p>Total Green Cards</p>
                </div>
            </div>
            <div class="stat-card stat-success">
                <div class="stat-content">
                    <h3><?php echo (int)$summary['total_approved']; ?></h3>
                    <p>Approved</p>
                </div>
            </div>
            <div class="stat-card stat-danger">
                <div class="stat-content">
                    <h3><?php echo (int)$summary['total_rejected']; ?></h3>
                    <p>Rejected</p>
                </div>
            </div>
            <div class="stat-card stat-warning">
                <div class="stat-content">
                    <h3><?php echo (int)$summary['total_review']; ?></h3>
                    <p>Under Review</p>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <?php if ($results !== null): ?>
        <div class="card">
            <div class="card-header">
                <h3>Search Results</h3>
                <?php if ($results['total_records'] > 0): ?>
                    <a href="<?php echo BASE_URL; ?>report_green_cards.php?export=csv&amp;<?php echo http_build_query([
                        'registration_number' => $rawRegistration,
                        'name' => $rawName,
                        'status' => $rawStatus,
                        'document_type' => $rawDocType,
                        'date_from' => $rawDateFrom,
                        'date_to' => $rawDateTo
                    ]); ?>">Export CSV</a>
                <?php endif; ?>
            </div>
            <div class="card-body">
                <?php if ($results['total_records'] === 0): ?>
                    <div class="alert alert-warning">No green cards matched the filters.</div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Student</th>
                                    <th>Registration #</th>
                                    <th>Status</th>
                                    <th>Issue Date</th>
                                    <th>Confidence</th>
                                    <th>Document</th>
                                    <th>Card #</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($results['records'] as $row): ?>
                                    <?php
                                        $status = strtoupper((string)($row['verification_status'] ?? 'REVIEW'));
                                        $confidence = $row['confidence_score'] !== null ? (int)$row['confidence_score'] : null;
                                        $isSuspicious = $confidence !== null && $confidence < 50;
                                    ?>
                                    <tr <?php echo $isSuspicious ? 'style="background:#fff7ed;"' : ''; ?>>
                                        <td><?php echo htmlspecialchars($row['student_name'] ?? '-'); ?></td>
                                        <td><?php echo htmlspecialchars($row['registration_number'] ?? '-'); ?></td>
                                        <td><span class="badge <?php echo gc_badge_class($status); ?>"><?php echo htmlspecialchars($status); ?></span></td>
                                        <td><?php echo htmlspecialchars($row['issue_date'] ?? '-'); ?></td>
                                        <td><?php echo $confidence !== null ? (int)$confidence : '-'; ?></td>
                                        <td><?php echo htmlspecialchars($row['document_type'] ?? '-'); ?></td>
                                        <td><?php echo htmlspecialchars($row['card_number'] ?? '-'); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <div class="pagination" style="margin-top: 12px;">
                        <?php
                            $totalPages = (int)$results['total_pages'];
                            $query = $_GET;
                        ?>
                        <?php if ($page > 1): ?>
                            <?php $query['page'] = $page - 1; ?>
                            <a class="btn btn-secondary" href="?<?php echo http_build_query($query); ?>">Previous</a>
                        <?php endif; ?>
                        <?php if ($page < $totalPages): ?>
                            <?php $query['page'] = $page + 1; ?>
                            <a class="btn btn-secondary" href="?<?php echo http_build_query($query); ?>">Next</a>
                        <?php endif; ?>
                        <span style="margin-left: 10px;">Page <?php echo (int)$page; ?> of <?php echo (int)$totalPages; ?></span>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php include '../../includes/footer.php'; ?>
