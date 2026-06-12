<?php
require_once '../../config/init.php';
require_login();
require_role([ROLE_ADMIN]);

if (!table_exists($db, 'intakes')) {
    $_SESSION['error'] = "Database migration required: run database_migration_intakes.sql.";
    redirect('modules/admin/dashboard.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        Session::setFlash('danger', 'Invalid CSRF token.');
        header('Location: ' . BASE_URL . 'modules/admin/intakes.php');
        exit;
    }

    $action = $_POST['action'] ?? '';
    try {
        if ($action === 'create_intake') {
            $name = trim((string)($_POST['intake_name'] ?? ''));
            $year = (int)($_POST['intake_year'] ?? 0);
            $start = $_POST['admission_period_start'] ?? null;
            $end = $_POST['admission_period_end'] ?? null;
            $isActive = isset($_POST['is_active']) ? 1 : 0;

            if ($name === '' || $year <= 0) {
                throw new Exception('Intake name and year are required.');
            }

            $stmt = $db->prepare(
                'INSERT INTO intakes (intake_name, intake_year, admission_period_start, admission_period_end, is_active)
                 VALUES (:name, :year, :start, :end, :active)'
            );
            $stmt->execute([
                'name' => $name,
                'year' => $year,
                'start' => $start !== '' ? $start : null,
                'end' => $end !== '' ? $end : null,
                'active' => $isActive
            ]);

            Session::setFlash('success', 'Intake created successfully.');
        } elseif ($action === 'update_intake') {
            $intakeId = (int)($_POST['intake_id'] ?? 0);
            $name = trim((string)($_POST['intake_name'] ?? ''));
            $year = (int)($_POST['intake_year'] ?? 0);
            $start = $_POST['admission_period_start'] ?? null;
            $end = $_POST['admission_period_end'] ?? null;
            $isActive = isset($_POST['is_active']) ? 1 : 0;

            if ($intakeId <= 0 || $name === '' || $year <= 0) {
                throw new Exception('Invalid intake data.');
            }

            $stmt = $db->prepare(
                'UPDATE intakes
                 SET intake_name = :name,
                     intake_year = :year,
                     admission_period_start = :start,
                     admission_period_end = :end,
                     is_active = :active
                 WHERE intake_id = :intake_id'
            );
            $stmt->execute([
                'name' => $name,
                'year' => $year,
                'start' => $start !== '' ? $start : null,
                'end' => $end !== '' ? $end : null,
                'active' => $isActive,
                'intake_id' => $intakeId
            ]);

            Session::setFlash('success', 'Intake updated successfully.');
        } elseif ($action === 'toggle_intake') {
            $intakeId = (int)($_POST['intake_id'] ?? 0);
            if ($intakeId <= 0) {
                throw new Exception('Invalid intake ID.');
            }
            $stmt = $db->prepare('SELECT is_active FROM intakes WHERE intake_id = :intake_id');
            $stmt->execute(['intake_id' => $intakeId]);
            $row = $stmt->fetch();
            if (!$row) {
                throw new Exception('Intake not found.');
            }
            $newValue = ((int)$row['is_active'] === 1) ? 0 : 1;
            $update = $db->prepare('UPDATE intakes SET is_active = :active WHERE intake_id = :intake_id');
            $update->execute(['active' => $newValue, 'intake_id' => $intakeId]);

            Session::setFlash('success', 'Intake status updated.');
        }
    } catch (Exception $e) {
        Session::setFlash('danger', $e->getMessage());
    }

    header('Location: ' . BASE_URL . 'modules/admin/intakes.php');
    exit;
}

$intakes = $db->query('SELECT * FROM intakes ORDER BY intake_year DESC, intake_name ASC')->fetchAll();

$page_title = 'Intake Management';
include '../../includes/header.php';
?>

<div class="dashboard">
    <div class="page-header">
        <h1>Intake Management</h1>
        <p>Create and manage admission intakes for new students.</p>
    </div>

    <div class="card" style="margin-bottom: 18px;">
        <div class="card-header"><h3>Create Intake</h3></div>
        <div class="card-body">
            <form method="POST" style="display:grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap:12px;">
                <?php echo csrf_token_field(); ?>
                <input type="hidden" name="action" value="create_intake">
                <input class="form-control" type="text" name="intake_name" placeholder="January Intake" required>
                <input class="form-control" type="number" name="intake_year" placeholder="2026" required>
                <input class="form-control" type="date" name="admission_period_start" placeholder="Start date">
                <input class="form-control" type="date" name="admission_period_end" placeholder="End date">
                <label style="display:flex; align-items:center; gap:6px; margin:0;">
                    <input type="checkbox" name="is_active" value="1" checked>
                    Active
                </label>
                <div style="display:flex; align-items:flex-end;">
                    <button class="btn btn-primary" type="submit">Create Intake</button>
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-header"><h3>Intake Records</h3></div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-sm">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Intake</th>
                            <th>Year</th>
                            <th>Admission Period</th>
                            <th>Active</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($intakes as $intake): ?>
                            <tr>
                                <td><?php echo (int)$intake['intake_id']; ?></td>
                                <td><?php echo htmlspecialchars((string)$intake['intake_name']); ?></td>
                                <td><?php echo htmlspecialchars((string)$intake['intake_year']); ?></td>
                                <td>
                                    <?php
                                        $period = array_filter([
                                            $intake['admission_period_start'] ?? null,
                                            $intake['admission_period_end'] ?? null
                                        ]);
                                        echo htmlspecialchars(!empty($period) ? implode(' to ', $period) : 'Not set');
                                    ?>
                                </td>
                                <td><?php echo ((int)$intake['is_active'] === 1) ? 'Yes' : 'No'; ?></td>
                                <td style="display:flex; gap:8px; flex-wrap:wrap;">
                                    <form method="POST" style="display:grid; grid-template-columns: repeat(auto-fit, minmax(120px, 1fr)); gap:6px;" onsubmit="return confirm('Update this intake?');">
                                        <?php echo csrf_token_field(); ?>
                                        <input type="hidden" name="action" value="update_intake">
                                        <input type="hidden" name="intake_id" value="<?php echo (int)$intake['intake_id']; ?>">
                                        <input class="form-control" type="text" name="intake_name" value="<?php echo htmlspecialchars((string)$intake['intake_name']); ?>" required>
                                        <input class="form-control" type="number" name="intake_year" value="<?php echo htmlspecialchars((string)$intake['intake_year']); ?>" required>
                                        <input class="form-control" type="date" name="admission_period_start" value="<?php echo htmlspecialchars((string)($intake['admission_period_start'] ?? '')); ?>">
                                        <input class="form-control" type="date" name="admission_period_end" value="<?php echo htmlspecialchars((string)($intake['admission_period_end'] ?? '')); ?>">
                                        <label style="display:flex; align-items:center; gap:4px; margin:0;">
                                            <input type="checkbox" name="is_active" value="1" <?php echo ((int)$intake['is_active'] === 1) ? 'checked' : ''; ?>> Active
                                        </label>
                                        <button class="btn btn-secondary btn-sm" type="submit">Save</button>
                                    </form>
                                    <form method="POST" onsubmit="return confirm('Toggle this intake status?');">
                                        <?php echo csrf_token_field(); ?>
                                        <input type="hidden" name="action" value="toggle_intake">
                                        <input type="hidden" name="intake_id" value="<?php echo (int)$intake['intake_id']; ?>">
                                        <button class="btn btn-secondary btn-sm" type="submit">Toggle Active</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>
