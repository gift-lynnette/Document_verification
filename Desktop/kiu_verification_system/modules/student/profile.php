<?php
require_once '../../config/init.php';
require_login();
require_role(ROLE_STUDENT);

$user_id = (int)($_SESSION['user_id'] ?? 0);
$profile = [];

$query = "
    SELECT u.admission_number, u.email, u.created_at, u.last_login_at
";

if (table_exists($db, 'student_profiles')) {
    $intakesJoin = table_exists($db, 'intakes') ? 'LEFT JOIN intakes i ON sp.intake_id = i.intake_id' : '';
    $query .= ",
           sp.full_name, sp.phone_number, sp.alternative_phone, sp.program,
        sp.faculty, sp.department, sp.intake_year, sp.intake_semester,
        sp.student_type, sp.study_mode, sp.address, sp.city, sp.country,
        sp.intake_id, " . (table_exists($db, 'intakes') ? 'i.intake_name' : "NULL AS intake_name") . "
    FROM users u
    LEFT JOIN student_profiles sp ON u.user_id = sp.user_id
    {$intakesJoin}
    ";
} else {
    $query .= ",
           NULL AS full_name, NULL AS phone_number, NULL AS alternative_phone, NULL AS program,
        NULL AS faculty, NULL AS department, NULL AS intake_year, NULL AS intake_semester,
        NULL AS student_type, NULL AS study_mode, NULL AS address, NULL AS city, NULL AS country,
        NULL AS intake_id, NULL AS intake_name
    FROM users u
    ";
}

$query .= "WHERE u.user_id = :user_id";

$stmt = $db->prepare($query);
$stmt->execute(['user_id' => $user_id]);
$profile = $stmt->fetch() ?: [];

$page_title = 'Student Profile';
include '../../includes/header.php';
?>

<div class="container student-narrow">
    <div class="page-header">
        <h1>Student Profile</h1>
        <p>Review your account and programme information.</p>
    </div>

    <div class="card">
        <div class="card-header">
            <h3>Account Details</h3>
        </div>
        <div class="card-body">
            <table class="info-table">
                <tr>
                    <th>Full Name</th>
                    <td><?php echo htmlspecialchars($profile['full_name'] ?? $_SESSION['admission_number'] ?? 'Student'); ?></td>
                </tr>
                <tr>
                    <th>Admission Number</th>
                    <td><?php echo htmlspecialchars($profile['admission_number'] ?? ''); ?></td>
                </tr>
                <tr>
                    <th>Email</th>
                    <td><?php echo htmlspecialchars($profile['email'] ?? ''); ?></td>
                </tr>
                <tr>
                    <th>Phone Number</th>
                    <td><?php echo htmlspecialchars($profile['phone_number'] ?? 'Not provided'); ?></td>
                </tr>
                <tr>
                    <th>Alternative Phone</th>
                    <td><?php echo htmlspecialchars($profile['alternative_phone'] ?? 'Not provided'); ?></td>
                </tr>
                <tr>
                    <th>Created</th>
                    <td><?php echo !empty($profile['created_at']) ? format_date($profile['created_at'], DISPLAY_DATETIME_FORMAT) : 'N/A'; ?></td>
                </tr>
                <tr>
                    <th>Last Login</th>
                    <td><?php echo !empty($profile['last_login_at']) ? format_date($profile['last_login_at'], DISPLAY_DATETIME_FORMAT) : 'N/A'; ?></td>
                </tr>
            </table>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h3>Academic Information</h3>
        </div>
        <div class="card-body">
            <table class="info-table">
                <tr>
                    <th>Program</th>
                    <td><?php echo htmlspecialchars($profile['program'] ?? 'Not available'); ?></td>
                </tr>
                <tr>
                    <th>Faculty</th>
                    <td><?php echo htmlspecialchars($profile['faculty'] ?? 'Not available'); ?></td>
                </tr>
                <tr>
                    <th>Department</th>
                    <td><?php echo htmlspecialchars($profile['department'] ?? 'Not available'); ?></td>
                </tr>
                <tr>
                    <th>Intake</th>
                    <td>
                        <?php
                        $intake = [];
                        if (!empty($profile['intake_name'])) {
                            $intake[] = $profile['intake_name'];
                        }
                        if (!empty($profile['intake_year'])) {
                            $intake[] = $profile['intake_year'];
                        }
                        echo htmlspecialchars(!empty($intake) ? implode(' - ', $intake) : 'Not available');
                        ?>
                    </td>
                </tr>
                <tr>
                    <th>Student Type</th>
                    <td><?php echo htmlspecialchars($profile['student_type'] ?? 'Not available'); ?></td>
                </tr>
                <tr>
                    <th>Study Mode</th>
                    <td><?php echo htmlspecialchars($profile['study_mode'] ?? 'Not available'); ?></td>
                </tr>
                <tr>
                    <th>Address</th>
                    <td><?php echo htmlspecialchars(trim(implode(', ', array_filter([$profile['address'] ?? '', $profile['city'] ?? '', $profile['country'] ?? '']))) ?: 'Not available'); ?></td>
                </tr>
            </table>
        </div>
    </div>

    <div class="profile-actions">
        <a href="<?php echo BASE_URL; ?>modules/student/dashboard.php" class="btn btn-primary">Back to Dashboard</a>
        <a href="<?php echo BASE_URL; ?>change_password.php" class="btn btn-secondary">Change Password</a>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>
