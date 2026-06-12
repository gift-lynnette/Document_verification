<?php
/**
 * Common Helper Functions
 */

/**
 * Sanitize input
 */
function sanitize_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    return $data;
}

/**
 * Generate random string
 */
function generate_random_string($length = 32) {
    return bin2hex(random_bytes($length / 2));
}

/**
 * Format currency
 */
function format_currency($amount, $currency = PAYMENT_CURRENCY) {
    return $currency . ' ' . number_format($amount, 2);
}

/**
 * Format date
 */
function format_date($date, $format = DISPLAY_DATE_FORMAT) {
    return date($format, strtotime($date));
}

/**
 * Calculate time ago
 */
function time_ago($datetime) {
    $timestamp = strtotime($datetime);
    $difference = time() - $timestamp;

    if ($difference < 60) {
        return $difference . ' seconds ago';
    } elseif ($difference < 3600) {
        return floor($difference / 60) . ' minutes ago';
    } elseif ($difference < 86400) {
        return floor($difference / 3600) . ' hours ago';
    } elseif ($difference < 604800) {
        return floor($difference / 86400) . ' days ago';
    }

    return format_date($datetime);
}


/**
 * Get status badge HTML
 */
function get_status_badge($status) {
    $badges = [
        'pending' => '<span class="badge badge-warning">Pending</span>',
        'under_review' => '<span class="badge badge-info">Under Review</span>',
        'verified' => '<span class="badge badge-success">Verified</span>',
        'rejected' => '<span class="badge badge-danger">Rejected</span>',
        'pending_admissions' => '<span class="badge badge-warning">Pending Admissions</span>',
        'under_admissions_review' => '<span class="badge badge-info">Admissions Review</span>',
        'admissions_approved' => '<span class="badge badge-success">Admissions Approved</span>',
        'admissions_rejected' => '<span class="badge badge-danger">Admissions Rejected</span>',
        'resubmission_requested' => '<span class="badge badge-warning">Resubmission Requested</span>',
        'pending_finance' => '<span class="badge badge-warning">Pending Finance</span>',
        'under_finance_review' => '<span class="badge badge-info">Finance Review</span>',
        'finance_approved' => '<span class="badge badge-success">Finance Approved</span>',
        'finance_rejected' => '<span class="badge badge-danger">Finance Rejected</span>',
        'finance_pending' => '<span class="badge badge-warning">Finance Pending</span>',
        'greencard_issued' => '<span class="badge badge-success">Green Card Issued</span>',
        'cancelled' => '<span class="badge badge-secondary">Cancelled</span>',
        'incomplete' => '<span class="badge badge-warning">Incomplete</span>',
        'suspicious' => '<span class="badge badge-danger">Suspicious</span>',
        'mismatch' => '<span class="badge badge-warning">Mismatch</span>'
    ];
    return $badges[$status] ?? '<span class="badge badge-secondary">Unknown</span>';
}

/**
 * Render a reusable inline SVG icon.
 */
function ui_icon($name, $class = '') {
    $icons = [
        'users' => '<svg viewBox="0 0 24 24" fill="none"><circle cx="9" cy="8" r="3.25"></circle><circle cx="16.5" cy="9.25" r="2.5"></circle><path d="M3.5 18a5.5 5.5 0 0 1 11 0"></path><path d="M13.5 18a3.8 3.8 0 0 1 7 0"></path></svg>',
        'student' => '<svg viewBox="0 0 24 24" fill="none"><path d="M3 9l9-4 9 4-9 4-9-4z"></path><path d="M7 11.5V15c0 2.1 2.2 3.7 5 3.7s5-1.6 5-3.7v-3.5"></path></svg>',
        'document' => '<svg viewBox="0 0 24 24" fill="none"><path d="M7 3h7l5 5v13H7z"></path><path d="M14 3v5h5"></path><path d="M10 13h6"></path><path d="M10 17h6"></path></svg>',
        'card' => '<svg viewBox="0 0 24 24" fill="none"><rect x="3" y="5" width="18" height="14" rx="2"></rect><path d="M3 10h18"></path><circle cx="8" cy="14" r="1.2"></circle><path d="M11 14h6"></path></svg>',
        'settings' => '<svg viewBox="0 0 24 24" fill="none"><circle cx="12" cy="12" r="2.8"></circle><path d="M19 12a7.1 7.1 0 0 0-.1-1.1l2-1.6-2-3.4-2.4 1a7.6 7.6 0 0 0-1.9-1.1L14.2 3h-4.4l-.4 2.8a7.6 7.6 0 0 0-1.9 1.1l-2.4-1-2 3.4 2 1.6A7.1 7.1 0 0 0 5 12c0 .4 0 .7.1 1.1l-2 1.6 2 3.4 2.4-1a7.6 7.6 0 0 0 1.9 1.1l.4 2.8h4.4l.4-2.8a7.6 7.6 0 0 0 1.9-1.1l2.4 1 2-3.4-2-1.6c.1-.4.1-.7.1-1.1z"></path></svg>',
        'chart' => '<svg viewBox="0 0 24 24" fill="none"><path d="M4 20h16"></path><rect x="6" y="11" width="2.8" height="7"></rect><rect x="10.6" y="8" width="2.8" height="10"></rect><rect x="15.2" y="5" width="2.8" height="13"></rect></svg>',
        'health' => '<svg viewBox="0 0 24 24" fill="none"><path d="M3 12h4l2-4 3.2 8L15 11h6"></path></svg>',
        'audit' => '<svg viewBox="0 0 24 24" fill="none"><rect x="6" y="3" width="12" height="18" rx="2"></rect><path d="M9 8h6"></path><path d="M9 12h6"></path><path d="M9 16h4"></path></svg>',
        'backup' => '<svg viewBox="0 0 24 24" fill="none"><ellipse cx="12" cy="6" rx="6.5" ry="2.8"></ellipse><path d="M5.5 6v8c0 1.5 2.9 2.8 6.5 2.8s6.5-1.3 6.5-2.8V6"></path><path d="M12 20v-6"></path><path d="M9.7 16.4L12 14l2.3 2.4"></path></svg>',
        'mail' => '<svg viewBox="0 0 24 24" fill="none"><rect x="3" y="6" width="18" height="12" rx="2"></rect><path d="M3 8l9 6 9-6"></path></svg>',
        'upload' => '<svg viewBox="0 0 24 24" fill="none"><path d="M12 15V5"></path><path d="M8.5 8.5L12 5l3.5 3.5"></path><path d="M4 16.5v2a1.5 1.5 0 0 0 1.5 1.5h13a1.5 1.5 0 0 0 1.5-1.5v-2"></path></svg>',
        'receipt' => '<svg viewBox="0 0 24 24" fill="none"><path d="M7 3h10v18l-2-1.4L13 21l-2-1.4L9 21l-2-1.4L7 3z"></path><path d="M9.5 8h5"></path><path d="M9.5 12h5"></path><path d="M9.5 16h3"></path></svg>',
        'download' => '<svg viewBox="0 0 24 24" fill="none"><path d="M12 5v10"></path><path d="M8.5 11.5L12 15l3.5-3.5"></path><path d="M4 18.5v1a1.5 1.5 0 0 0 1.5 1.5h13a1.5 1.5 0 0 0 1.5-1.5v-1"></path></svg>',
        'bell' => '<svg viewBox="0 0 24 24" fill="none"><path d="M7 10a5 5 0 0 1 10 0v4l1.5 2H5.5L7 14z"></path><path d="M10 18a2 2 0 0 0 4 0"></path></svg>',
        'search' => '<svg viewBox="0 0 24 24" fill="none"><circle cx="11" cy="11" r="6"></circle><path d="M20 20l-4.2-4.2"></path></svg>',
        'check' => '<svg viewBox="0 0 24 24" fill="none"><circle cx="12" cy="12" r="9"></circle><path d="M8.5 12.5l2.2 2.2 4.8-4.8"></path></svg>',
        'reject' => '<svg viewBox="0 0 24 24" fill="none"><circle cx="12" cy="12" r="9"></circle><path d="M9 9l6 6"></path><path d="M15 9l-6 6"></path></svg>',
        'clock' => '<svg viewBox="0 0 24 24" fill="none"><circle cx="12" cy="12" r="9"></circle><path d="M12 7v5l3 2"></path></svg>',
        'payment' => '<svg viewBox="0 0 24 24" fill="none"><rect x="3" y="6" width="18" height="12" rx="2"></rect><path d="M3 10h18"></path><path d="M7 14h4"></path></svg>',
        'calendar' => '<svg viewBox="0 0 24 24" fill="none"><rect x="3" y="5" width="18" height="16" rx="2"></rect><path d="M16 3v4"></path><path d="M8 3v4"></path><path d="M3 10h18"></path></svg>',
        'edit' => '<svg viewBox="0 0 24 24" fill="none"><path d="M4 20l4.2-1 9.4-9.4-3.2-3.2L5 15.8 4 20z"></path><path d="M13.8 5l3.2 3.2"></path></svg>',
        'pin' => '<svg viewBox="0 0 24 24" fill="none"><path d="M8 3h8l-2.2 5.2 3.2 3.2H7l3.2-3.2L8 3z"></path><path d="M12 11v10"></path></svg>',
        'info' => '<svg viewBox="0 0 24 24" fill="none"><circle cx="12" cy="12" r="9"></circle><path d="M12 10.5v5"></path><circle cx="12" cy="7.5" r="0.9" fill="currentColor" stroke="none"></circle></svg>',
        'warning' => '<svg viewBox="0 0 24 24" fill="none"><path d="M12 4l9 16H3l9-16z"></path><path d="M12 9v5"></path><circle cx="12" cy="17" r="1" fill="currentColor" stroke="none"></circle></svg>'
    ];

    $svg = $icons[$name] ?? $icons['document'];
    $classAttr = trim('ui-icon ' . $class);
    return '<span class="' . htmlspecialchars($classAttr, ENT_QUOTES, 'UTF-8') . '" aria-hidden="true">' . $svg . '</span>';
}

/**
 * Allowed workflow status transitions for document_submissions.
 */
function get_workflow_transition_map() {
    return [
        STATUS_PENDING_ADMISSIONS => [STATUS_UNDER_ADMISSIONS_REVIEW, STATUS_CANCELLED],
        STATUS_UNDER_ADMISSIONS_REVIEW => [STATUS_PENDING_FINANCE, STATUS_RESUBMISSION_REQUESTED, STATUS_ADMISSIONS_REJECTED, STATUS_CANCELLED],
        STATUS_ADMISSIONS_APPROVED => [STATUS_PENDING_FINANCE, STATUS_UNDER_FINANCE_REVIEW, STATUS_CANCELLED], // legacy compatibility
        STATUS_RESUBMISSION_REQUESTED => [STATUS_PENDING_ADMISSIONS, STATUS_CANCELLED],
        STATUS_ADMISSIONS_REJECTED => [STATUS_PENDING_ADMISSIONS, STATUS_CANCELLED],
        STATUS_PENDING_FINANCE => [STATUS_UNDER_FINANCE_REVIEW, STATUS_FINANCE_PENDING, STATUS_FINANCE_REJECTED, STATUS_PENDING_GREENCARD, STATUS_CANCELLED],
        STATUS_UNDER_FINANCE_REVIEW => [STATUS_FINANCE_PENDING, STATUS_FINANCE_REJECTED, STATUS_PENDING_GREENCARD, STATUS_CANCELLED],
        STATUS_FINANCE_PENDING => [STATUS_UNDER_FINANCE_REVIEW, STATUS_FINANCE_REJECTED, STATUS_PENDING_GREENCARD, STATUS_CANCELLED],
        STATUS_FINANCE_APPROVED => [STATUS_PENDING_GREENCARD, STATUS_CANCELLED], // legacy compatibility
        STATUS_FINANCE_REJECTED => [STATUS_PENDING_ADMISSIONS, STATUS_CANCELLED],
        STATUS_PENDING_GREENCARD => [STATUS_GREENCARD_ISSUED, STATUS_CANCELLED],
        STATUS_GREENCARD_ISSUED => [],
        STATUS_CANCELLED => []
    ];
}

/**
 * Check whether a workflow status transition is allowed.
 */
function can_transition_workflow_status($from_status, $to_status) {
    if ($from_status === $to_status) {
        return true;
    }

    $map = get_workflow_transition_map();
    if (!array_key_exists($from_status, $map)) {
        return false;
    }

    return in_array($to_status, $map[$from_status], true);
}

/**
 * Throws when transition is not allowed.
 */
function assert_workflow_transition($from_status, $to_status) {
    if (!can_transition_workflow_status($from_status, $to_status)) {
        throw new Exception("Invalid workflow transition: {$from_status} -> {$to_status}");
    }
}

/**
 * Atomically transition document_submissions.status and write workflow history.
 * extra_updates keys must be valid document_submissions column names.
 */
function transition_submission_status($db, $submission_id, $from_status, $to_status, $changed_by_user_id, $department, $notes = '', $extra_updates = []) {
    assert_workflow_transition($from_status, $to_status);

    $set_clauses = ['status = :to_status'];
    $params = [
        'to_status' => $to_status,
        'submission_id' => $submission_id,
        'from_status' => $from_status
    ];

    foreach ($extra_updates as $column => $value) {
        if (!preg_match('/^[a-zA-Z_][a-zA-Z0-9_]*$/', $column)) {
            throw new Exception("Invalid update column: {$column}");
        }

        $param_name = 'extra_' . $column;
        $set_clauses[] = "{$column} = :{$param_name}";
        $params[$param_name] = $value;
    }

    $sql = "
        UPDATE document_submissions
        SET " . implode(",\n            ", $set_clauses) . "
        WHERE submission_id = :submission_id
          AND status = :from_status
    ";

    $stmt = $db->prepare($sql);
    $stmt->execute($params);

    if ($stmt->rowCount() !== 1) {
        throw new Exception("Workflow transition failed (stale state): {$from_status} -> {$to_status}");
    }

    if (table_exists($db, 'workflow_history')) {
        $history_stmt = $db->prepare("
            INSERT INTO workflow_history (submission_id, from_status, to_status, changed_by_user_id, department, notes)
            VALUES (:submission_id, :from_status, :to_status, :changed_by_user_id, :department, :notes)
        ");
        $history_stmt->execute([
            'submission_id' => $submission_id,
            'from_status' => $from_status,
            'to_status' => $to_status,
            'changed_by_user_id' => $changed_by_user_id,
            'department' => $department,
            'notes' => $notes
        ]);
    }
}

/**
 * Send JSON response
 */
function json_response($success, $message, $data = [], $status_code = 200) {
    http_response_code($status_code);
    header('Content-Type: application/json');
    echo json_encode([
        'success' => $success,
        'message' => $message,
        'data' => $data
    ]);
    exit;
}

/**
 * Redirect
 */
function redirect($url) {
    header("Location: " . BASE_URL . $url);
    exit;
}

/**
 * Check if user is logged in
 */
function is_logged_in() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

/**
 * Check user role
 */
function has_role($required_role) {
    if (!is_logged_in()) return false;
    
    $user_role = $_SESSION['role'] ?? '';
    
    // Admin has access to everything
    if ($user_role === ROLE_ADMIN) return true;
    
    // Check specific role
    if (is_array($required_role)) {
        return in_array($user_role, $required_role);
    }
    
    return $user_role === $required_role;
}

/**
 * Require login
 */
function require_login() {
    if (!is_logged_in()) {
        $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'];
        redirect('login.php');
    }
}

/**
 * Require role
 */
function require_role($required_role) {
    require_login();
    if (!has_role($required_role)) {
        http_response_code(403);
        die('Access denied');
    }
}

/**
 * Generate CSRF token
 */
function generate_csrf_token() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Render CSRF hidden input field.
 */
function csrf_token_field() {
    $token = htmlspecialchars(generate_csrf_token(), ENT_QUOTES, 'UTF-8');
    return '<input type="hidden" name="csrf_token" value="' . $token . '">';
}

/**
 * Verify CSRF token
 */
function verify_csrf_token($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Log activity
 */
function log_activity($action, $details = '') {
    global $db;
    
    try {
        $stmt = $db->prepare("
            INSERT INTO audit_logs (user_id, action, changes_summary, ip_address, user_agent)
            VALUES (:user_id, :action, :details, :ip, :user_agent)
        ");
        
        $stmt->execute([
            'user_id' => $_SESSION['user_id'] ?? null,
            'action' => $action,
            'details' => $details,
            'ip' => $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0',
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown'
        ]);
    } catch (Exception $e) {
        error_log("Failed to log activity: " . $e->getMessage());
    }
}

/**
 * Get file extension
 */
function get_file_extension($filename) {
    return strtolower(pathinfo($filename, PATHINFO_EXTENSION));
}

/**
 * Format file size
 */
function format_file_size($bytes) {
    $units = ['B', 'KB', 'MB', 'GB'];
    $bytes = max($bytes, 0);
    $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
    $pow = min($pow, count($units) - 1);
    $bytes /= pow(1024, $pow);
    return round($bytes, 2) . ' ' . $units[$pow];
}

/**
 * Check whether a table exists in the current database.
 */
function table_exists($db, $table_name) {
    try {
        $stmt = $db->prepare("SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = :table_name");
        $stmt->execute(['table_name' => $table_name]);
        return (int)$stmt->fetchColumn() > 0;
    } catch (Exception $e) {
        error_log("Table exists check failed: " . $e->getMessage());
        return false;
    }
}

/**
 * Check whether a column exists in a table in the current database.
 */
function column_exists($db, $table_name, $column_name) {
    try {
        $stmt = $db->prepare("
            SELECT COUNT(*)
            FROM information_schema.columns
            WHERE table_schema = DATABASE()
              AND table_name = :table_name
              AND column_name = :column_name
        ");
        $stmt->execute([
            'table_name' => $table_name,
            'column_name' => $column_name
        ]);
        return (int)$stmt->fetchColumn() > 0;
    } catch (Exception $e) {
        error_log("Column exists check failed: " . $e->getMessage());
        return false;
    }
}

/**
 * Validate date of birth for student eligibility.
 */
function validate_student_date_of_birth($date_of_birth, $minimum_age = 18) {
    $dob = DateTime::createFromFormat('Y-m-d', (string)$date_of_birth);
    $errors = [];

    if (!$dob || $dob->format('Y-m-d') !== (string)$date_of_birth) {
        return ['valid' => false, 'message' => 'Date of Birth is not a valid date'];
    }

    $today = new DateTime('today');
    if ($dob > $today) {
        $errors[] = 'Date of Birth cannot be in the future';
    }

    $age = $dob->diff($today)->y;
    if ($age < $minimum_age) {
        $errors[] = 'Student must be at least ' . (int)$minimum_age . ' years old';
    }

    return [
        'valid' => empty($errors),
        'message' => implode('. ', $errors),
        'age' => $age
    ];
}

function kiu_shell_escape_arg($value) {
    if (DIRECTORY_SEPARATOR === '\\') {
        return '"' . str_replace('"', '""', (string)$value) . '"';
    }

    return escapeshellarg((string)$value);
}

function kiu_find_executable($envName, $candidates) {
    $configured = getenv($envName);
    if (is_string($configured) && $configured !== '' && is_file($configured)) {
        return $configured;
    }

    foreach ($candidates as $candidate) {
        if (is_file($candidate) || (DIRECTORY_SEPARATOR !== '\\' && is_executable($candidate))) {
            return $candidate;
        }
    }

    return null;
}

function kiu_run_command_capture_text($command, $timeoutSeconds = 20) {
    if (!function_exists('proc_open')) {
        return '';
    }

    $descriptors = [
        0 => ['pipe', 'r'],
        1 => ['pipe', 'w'],
        2 => ['pipe', 'w']
    ];

    $process = @proc_open($command, $descriptors, $pipes);
    if (!is_resource($process)) {
        return '';
    }

    fclose($pipes[0]);
    stream_set_blocking($pipes[1], false);
    stream_set_blocking($pipes[2], false);

    $output = '';
    $started = time();
    while (true) {
        $output .= (string)stream_get_contents($pipes[1]);
        $status = proc_get_status($process);
        if (!$status['running']) {
            break;
        }
        if (time() - $started > $timeoutSeconds) {
            proc_terminate($process);
            break;
        }
        usleep(100000);
    }

    $output .= (string)stream_get_contents($pipes[1]);
    fclose($pipes[1]);
    fclose($pipes[2]);
    proc_close($process);

    return trim($output);
}

function kiu_extract_text_with_tesseract($path) {
    $tesseract = kiu_find_executable('TESSERACT_PATH', [
        'C:\\Program Files\\Tesseract-OCR\\tesseract.exe',
        'C:\\Program Files (x86)\\Tesseract-OCR\\tesseract.exe',
        '/usr/bin/tesseract',
        '/usr/local/bin/tesseract'
    ]);

    if ($tesseract === null) {
        return '';
    }

    $command = kiu_shell_escape_arg($tesseract) . ' ' . kiu_shell_escape_arg($path) . ' stdout --psm 6';
    return kiu_run_command_capture_text($command, 25);
}

function kiu_extract_text_with_pdftotext($path) {
    $localAppData = getenv('LOCALAPPDATA') ?: '';
    $pdftotextCandidates = [
        (defined('SITE_ROOT') ? rtrim(SITE_ROOT, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . 'tools\\poppler\\bin\\pdftotext.exe' : ''),
        'C:\\Program Files\\poppler\\Library\\bin\\pdftotext.exe',
        'C:\\Program Files\\Git\\mingw64\\bin\\pdftotext.exe',
        'C:\\poppler\\Library\\bin\\pdftotext.exe',
        '/usr/bin/pdftotext',
        '/usr/local/bin/pdftotext'
    ];
    if ($localAppData !== '') {
        array_unshift(
            $pdftotextCandidates,
            rtrim($localAppData, "\\/") . '\\Microsoft\\WinGet\\Packages\\oschwartz10612.Poppler_Microsoft.Winget.Source_8wekyb3d8bbwe\\poppler-25.07.0\\Library\\bin\\pdftotext.exe'
        );
    }

    $pdftotext = kiu_find_executable('PDFTOTEXT_PATH', $pdftotextCandidates);

    if ($pdftotext === null) {
        return '';
    }

    $command = kiu_shell_escape_arg($pdftotext) . ' -layout ' . kiu_shell_escape_arg($path) . ' -';
    return kiu_run_command_capture_text($command, 25);
}

/**
 * Fetch semester exchange rate (USD -> UGX).
 * Uses semester_exchange_rates table when available, otherwise falls back to default.
 */
function get_semester_exchange_rate_ugx($db, $intake_year, $intake_semester) {
    $default_rate = 3800.00;

    // New-student flow always uses Semester 1 for fee/FX rules.
    $intake_semester = 'semester_1';
    if ($intake_semester === '') {
        return $default_rate;
    }

    if (!table_exists($db, 'semester_exchange_rates')) {
        return $default_rate;
    }

    try {
        $stmt = $db->prepare("
            SELECT usd_to_ugx_rate
            FROM semester_exchange_rates
            WHERE intake_year = :intake_year
              AND intake_semester = :intake_semester
              AND is_active = 1
            ORDER BY effective_from DESC, rate_id DESC
            LIMIT 1
        ");
        $stmt->execute([
            'intake_year' => $intake_year,
            'intake_semester' => $intake_semester
        ]);

        $rate = $stmt->fetchColumn();
        if ($rate !== false && (float)$rate > 0) {
            return (float)$rate;
        }
    } catch (Exception $e) {
        error_log('Exchange rate lookup failed: ' . $e->getMessage());
    }

    return $default_rate;
}

/**
 * Normalize intake semester values to semester_1/2/3.
 */
function normalize_intake_semester_value($value) {
    $normalized = strtolower(trim((string)$value));
    if ($normalized === '') {
        return '';
    }

    $map = [
        'semester_1' => 'semester_1',
        'semester 1' => 'semester_1',
        '1' => 'semester_1',
        'january' => 'semester_1',
        'jan' => 'semester_1',
        '01' => 'semester_1'
    ];

    return $map[$normalized] ?? '';
}

/**
 * Fetch active intakes ordered by year and name.
 */
function get_active_intakes($db) {
    if (!table_exists($db, 'intakes')) {
        return [];
    }

    try {
        $stmt = $db->query(
            "SELECT intake_id, intake_name, intake_year, admission_period_start, admission_period_end, is_active\n" .
            "FROM intakes\n" .
            "WHERE is_active = 1\n" .
            "ORDER BY intake_year DESC, intake_name ASC"
        );
        return $stmt->fetchAll();
    } catch (Exception $e) {
        error_log('Intake lookup failed: ' . $e->getMessage());
        return [];
    }
}

/**
 * Resolve intake by ID.
 */
function get_intake_by_id($db, $intake_id) {
    if (!table_exists($db, 'intakes')) {
        return null;
    }

    $intake_id = (int)$intake_id;
    if ($intake_id <= 0) {
        return null;
    }

    try {
        $stmt = $db->prepare(
            "SELECT intake_id, intake_name, intake_year, admission_period_start, admission_period_end, is_active\n" .
            "FROM intakes\n" .
            "WHERE intake_id = :intake_id\n" .
            "LIMIT 1"
        );
        $stmt->execute(['intake_id' => $intake_id]);
        return $stmt->fetch() ?: null;
    } catch (Exception $e) {
        error_log('Intake resolve failed: ' . $e->getMessage());
        return null;
    }
}

/**
 * Convert amount to UGX from supported currencies.
 */
function convert_amount_to_ugx($amount, $currency, $usd_to_ugx_rate) {
    $amount = (float)$amount;
    $currency = strtoupper((string)$currency);

    if ($currency === 'USD') {
        return round($amount * (float)$usd_to_ugx_rate, 2);
    }

    return round($amount, 2);
}

/**
 * Determine the fee policy profile for a programme/faculty.
 */
function resolve_program_fee_profile($program, $faculty = '') {
    $program_l = strtolower((string)$program);
    $faculty_l = strtolower((string)$faculty);

    if (strpos($program_l, 'phd') !== false || strpos($program_l, 'master') !== false || strpos($program_l, 'masters') !== false) {
        return ['level' => 'masters_phd', 'default_functional_fee' => 0.00, 'research_fee' => 1000000.00];
    }

    if (strpos($program_l, 'certificate') !== false || strpos($program_l, 'national certificate') !== false) {
        return ['level' => 'certificate', 'default_functional_fee' => 250000.00, 'research_fee' => 0.00];
    }

    if (strpos($program_l, 'diploma') !== false) {
        return ['level' => 'diploma', 'default_functional_fee' => 400000.00, 'research_fee' => 0.00];
    }

    $health_hint = (strpos($faculty_l, 'health') !== false)
        || (strpos($program_l, 'medicine') !== false)
        || (strpos($program_l, 'pharmacy') !== false)
        || (strpos($program_l, 'nursing') !== false)
        || (strpos($program_l, 'public health') !== false);

    if ($health_hint) {
        return ['level' => 'health_science_degree', 'default_functional_fee' => 600000.00, 'research_fee' => 0.00];
    }

    return ['level' => 'bachelors', 'default_functional_fee' => 500000.00, 'research_fee' => 0.00];
}

/**
 * Calculate fee requirements under finance rules.
 */
function calculate_finance_fee_requirements($fee_structure, $program, $faculty = '') {
    $profile = resolve_program_fee_profile($program, $faculty);

    $tuition = 0.00;
    $functional = $profile['default_functional_fee'];
    $other = 0.00;
    $minimum_payment_override = null;

    if (!empty($fee_structure)) {
        $tuition = (float)($fee_structure['tuition_amount'] ?? 0);
        if (isset($fee_structure['functional_fees']) && $fee_structure['functional_fees'] !== null) {
            $functional = (float)$fee_structure['functional_fees'];
        }
        if (isset($fee_structure['other_fees']) && $fee_structure['other_fees'] !== null) {
            $other = (float)$fee_structure['other_fees'];
        }
        if (isset($fee_structure['minimum_payment']) && $fee_structure['minimum_payment'] !== null) {
            $minimum_payment_override = (float)$fee_structure['minimum_payment'];
        }
    }

    $research_fee = (float)$profile['research_fee'];
    $total_required = $tuition + $functional + $other + $research_fee;
    if (!empty($fee_structure) && isset($fee_structure['total_amount']) && (float)$fee_structure['total_amount'] > 0) {
        $total_required = (float)$fee_structure['total_amount'];
    }

    $threshold_50_percent = $minimum_payment_override !== null
        ? $minimum_payment_override
        : ($total_required * 0.5);
    $bursary_tuition_threshold = $tuition * 0.5;

    return [
        'level' => $profile['level'],
        'tuition_fee' => round($tuition, 2),
        'functional_fee' => round($functional, 2),
        'other_fees' => round($other, 2),
        'research_fee' => round($research_fee, 2),
        'total_required_fee' => round($total_required, 2),
        'threshold_50_percent' => round($threshold_50_percent, 2),
        'bursary_tuition_threshold' => round($bursary_tuition_threshold, 2)
    ];
}

/**
 * Normalize text used for fee-structure matching.
 */
function kiu_normalize_fee_lookup_text($text) {
    $text = strtolower((string)$text);
    $text = preg_replace('/[^a-z0-9\s]/', ' ', $text);
    $text = preg_replace('/\s+/', ' ', $text);
    return trim($text);
}

/**
 * Score the similarity between two normalized strings using token overlap.
 */
function kiu_fee_lookup_similarity_score($a, $b) {
    $a = kiu_normalize_fee_lookup_text($a);
    $b = kiu_normalize_fee_lookup_text($b);

    if ($a === '' || $b === '') {
        return 0.0;
    }

    if ($a === $b) {
        return 1.0;
    }

    if (strpos($a, $b) !== false || strpos($b, $a) !== false) {
        return 0.9;
    }

    $tokensA = array_values(array_filter(explode(' ', $a)));
    $tokensB = array_values(array_filter(explode(' ', $b)));
    if (empty($tokensA) || empty($tokensB)) {
        return 0.0;
    }

    $shared = array_intersect($tokensA, $tokensB);
    $longest = max(count($tokensA), count($tokensB));

    return min(1.0, count($shared) / $longest);
}

/**
 * Resolve the best matching active fee structure for a submission.
 */
function resolve_fee_structure_for_submission($db, $program, $faculty = '', $semester = '', $intake_year = null) {
    if (!table_exists($db, 'fee_structures')) {
        return null;
    }

    // Fees are uniform for all intakes; always use Semester 1.
    $semester = 'semester_1';
    if ($semester === '') {
        return null;
    }

    $stmt = $db->prepare("\n        SELECT *\n        FROM fee_structures\n        WHERE semester = :semester\n          AND is_active = 1\n          AND effective_from <= CURDATE()\n          AND (effective_to IS NULL OR effective_to >= CURDATE())\n        ORDER BY effective_from DESC, updated_at DESC, created_at DESC\n    ");
    $stmt->execute(['semester' => $semester]);
    $rows = $stmt->fetchAll();

    if (!$rows) {
        $stmt = $db->prepare("\n            SELECT *\n            FROM fee_structures\n            WHERE semester = :semester\n              AND is_active = 1\n            ORDER BY effective_from DESC, updated_at DESC, created_at DESC\n        ");
        $stmt->execute(['semester' => $semester]);
        $rows = $stmt->fetchAll();
    }

    if (!$rows) {
        $stmt = $db->prepare("\n            SELECT *\n            FROM fee_structures\n            WHERE semester = :semester\n            ORDER BY is_active DESC, effective_from DESC, updated_at DESC, created_at DESC\n        ");
        $stmt->execute(['semester' => $semester]);
        $rows = $stmt->fetchAll();
    }

    if (!$rows) {
        return null;
    }

    $programNorm = kiu_normalize_fee_lookup_text($program);
    $facultyNorm = kiu_normalize_fee_lookup_text($faculty);
    $profile = resolve_program_fee_profile($program, $faculty);
    $bestRow = null;
    $bestScore = 0.0;

    foreach ($rows as $row) {
        $rowProgram = (string)($row['program_name'] ?? '');
        $rowFaculty = (string)($row['faculty'] ?? '');
        $rowStudentType = (string)($row['student_type'] ?? '');
        $rowStudyMode = (string)($row['study_mode'] ?? '');
        $rowAcademicYear = (string)($row['academic_year'] ?? '');

        $score = 0.0;
        $score += kiu_fee_lookup_similarity_score($programNorm, $rowProgram) * 0.65;
        $score += kiu_fee_lookup_similarity_score($facultyNorm, $rowFaculty) * 0.15;

        if (!empty($profile['level'])) {
            $level = $profile['level'];
            if ($level === 'bachelors' && $rowStudentType === 'undergraduate') {
                $score += 0.10;
            } elseif ($level === 'certificate' && $rowStudentType === 'certificate') {
                $score += 0.10;
            } elseif ($level === 'diploma' && $rowStudentType === 'diploma') {
                $score += 0.10;
            } elseif ($level === 'masters_phd' && $rowStudentType === 'postgraduate') {
                $score += 0.10;
            }
        }

        if ($rowStudyMode !== '') {
            if ($profile['level'] === 'bachelors' && $rowStudyMode === 'full_time') {
                $score += 0.05;
            } elseif ($profile['level'] === 'certificate' && $rowStudyMode === 'part_time') {
                $score += 0.05;
            } elseif ($profile['level'] === 'masters_phd' && in_array($rowStudyMode, ['part_time', 'evening'], true)) {
                $score += 0.05;
            }
        }

        if ($intake_year !== null && $intake_year !== '') {
            $intakeYear = (int)$intake_year;
            if ($intakeYear > 0) {
                $currentAcademicYear = $intakeYear . '/' . ($intakeYear + 1);
                $previousAcademicYear = ($intakeYear - 1) . '/' . $intakeYear;
                if ($rowAcademicYear === $currentAcademicYear) {
                    $score += 0.05;
                } elseif ($rowAcademicYear === $previousAcademicYear) {
                    $score += 0.03;
                }
            }
        }

        if ($score > $bestScore) {
            $bestScore = $score;
            $bestRow = $row;
        }
    }

    return ($bestScore >= 0.35) ? $bestRow : null;
}

/**
 * Check bursary status from forwarded bursary list.
 */
function get_bursary_status_for_submission($db, $submission) {
    $has_award_letter = !empty($submission['bursary_award_letter_path']);
    $claims_bursary = !empty($submission['is_bursary']) || $has_award_letter;

    if (!table_exists($db, 'bursary_forward_list')) {
        return [
            'claims_bursary' => $claims_bursary,
            'status' => $has_award_letter ? 'Yes' : 'No',
            'is_confirmed' => $has_award_letter,
            'list_record' => null
        ];
    }

    $record = null;
    try {
        $stmt = $db->prepare("
            SELECT *
            FROM bursary_forward_list
            WHERE is_active = 1
              AND (
                    user_id = :user_id
                 OR admission_number = :admission_number
                 OR full_name = :full_name
              )
            ORDER BY forwarded_at DESC, bursary_id DESC
            LIMIT 1
        ");
        $stmt->execute([
            'user_id' => (int)$submission['user_id'],
            'admission_number' => (string)($submission['admission_number'] ?? ''),
            'full_name' => (string)($submission['full_name'] ?? '')
        ]);
        $record = $stmt->fetch();
    } catch (Exception $e) {
        error_log('Bursary lookup failed: ' . $e->getMessage());
    }

    if (!$record) {
        return [
            'claims_bursary' => $claims_bursary,
            'status' => $has_award_letter ? 'Yes' : 'No',
            'is_confirmed' => $has_award_letter,
            'list_record' => null
        ];
    }

    if ($has_award_letter) {
        return [
            'claims_bursary' => true,
            'status' => 'Yes',
            'is_confirmed' => true,
            'list_record' => $record
        ];
    }

    $confirmation = strtolower((string)($record['confirmation_status'] ?? 'confirmed'));
    if ($confirmation === 'pending') {
        return [
            'claims_bursary' => $claims_bursary,
            'status' => 'Pending Confirmation',
            'is_confirmed' => false,
            'list_record' => $record
        ];
    }

    return [
        'claims_bursary' => $claims_bursary,
        'status' => 'Yes',
        'is_confirmed' => true,
        'list_record' => $record
    ];
}

/**
 * Attempt to extract bank slip amount from OCR text when available.
 */
function kiu_parse_bank_slip_amount_from_text($text) {
    $paymentData = kiu_extract_bank_slip_payment_data($text);
    return isset($paymentData['amount']) ? $paymentData['amount'] : null;
}

function kiu_bank_slip_ocr_text_looks_like_app_ui($text) {
    $textNorm = strtolower(preg_replace('/\s+/u', ' ', (string)$text));
    if ($textNorm === '') {
        return false;
    }
    $cues = [
        'payment information',
        'amount paid:',
        'slip ocr status:',
        'slip ocr status',
        'automated verification',
        'verification result',
        'document verification module',
        'verification engine',
        'verification status',
        'submit verification decision',
        'risk flags:',
        '| expected:',
        'expected: bank slip',
        'bank slipapproved'
    ];
    foreach ($cues as $needle) {
        if (strpos($textNorm, $needle) !== false) {
            return true;
        }
    }
    $patterns = [
        '/bank_name\s*:/',
        '/admission_number\s*\./',
        '/verification_status\s*[=:]/',
        '/confidence_score\s*[=:]/',
        '/extracted_fields\s*[=:]/',
        '/risk_flags\s*[=:]/',
        '/confidence\s*:\s*\d+\s*%/iu',
        '/missing_fields\s*[=:]/iu'
    ];
    foreach ($patterns as $regex) {
        if (preg_match($regex, (string)$text)) {
            return true;
        }
    }
    return false;
}

function kiu_detect_bank_slip_currency_from_text($text, $defaultCurrency = 'UGX') {
    $text = strtolower((string)$text);
    if ($text === '') {
        return $defaultCurrency;
    }

    if (preg_match('/\busd\b|\$/i', $text)) {
        return 'USD';
    }

    if (preg_match('/\bugx\b|\bush\b|\bshs\b|uganda\s+shilling/i', $text)) {
        return 'UGX';
    }

    return $defaultCurrency;
}

function kiu_is_likely_pdf_binary_blob($text) {
    $text = (string)$text;
    if ($text === '') {
        return false;
    }

    $head = substr($text, 0, 1200);
    if (strpos($head, '%PDF-') !== false && preg_match('/\bobj\b/i', $head)) {
        return true;
    }

    return false;
}

function kiu_persist_backfilled_bank_slip_amount($db, $submission_id, $amount, $currency, $status) {
    if (!table_exists($db, 'document_submissions')) {
        return;
    }

    $setParts = [];
    $params = [
        'submission_id' => $submission_id
    ];

    if (column_exists($db, 'document_submissions', 'payment_amount_ocr')) {
        $setParts[] = 'payment_amount_ocr = :payment_amount_ocr';
        $params['payment_amount_ocr'] = $amount;
    }

    if (column_exists($db, 'document_submissions', 'payment_currency')) {
        $setParts[] = 'payment_currency = :payment_currency';
        $params['payment_currency'] = in_array($currency, ['UGX', 'USD'], true) ? $currency : 'UGX';
    }

    if (column_exists($db, 'document_submissions', 'bank_slip_ocr_status')) {
        $setParts[] = 'bank_slip_ocr_status = :bank_slip_ocr_status';
        $params['bank_slip_ocr_status'] = $status;
    }

    if (empty($setParts)) {
        return;
    }

    try {
        $stmt = $db->prepare('UPDATE document_submissions SET ' . implode(', ', $setParts) . ' WHERE submission_id = :submission_id');
        $stmt->execute($params);
    } catch (Exception $e) {
        error_log('Backfill persist failed: ' . $e->getMessage());
    }
}

function kiu_mark_bank_slip_manual_review($db, $submission_id, $clearAmount = false) {
    if (!table_exists($db, 'document_submissions')) {
        return;
    }

    $setParts = [];
    $params = ['submission_id' => $submission_id];

    if (column_exists($db, 'document_submissions', 'bank_slip_ocr_status')) {
        $setParts[] = 'bank_slip_ocr_status = :bank_slip_ocr_status';
        $params['bank_slip_ocr_status'] = 'manual_review';
    }

    if ($clearAmount && column_exists($db, 'document_submissions', 'payment_amount_ocr')) {
        $setParts[] = 'payment_amount_ocr = NULL';
    }

    if (empty($setParts)) {
        return;
    }

    try {
        $stmt = $db->prepare('UPDATE document_submissions SET ' . implode(', ', $setParts) . ' WHERE submission_id = :submission_id');
        $stmt->execute($params);
    } catch (Exception $e) {
        error_log('Manual review mark failed: ' . $e->getMessage());
    }
}

function kiu_extract_bank_slip_amount_for_submission_impl($db, $submission_id, $fallback_amount = 0.00) {
    $fallback = (float)$fallback_amount;
    $storedAmount = null;
    $storedCurrency = 'UGX';

    if (table_exists($db, 'document_submissions')) {
        try {
            $submissionColumns = [];
            foreach (['payment_currency', 'payment_amount'] as $column) {
                if (column_exists($db, 'document_submissions', $column)) {
                    $submissionColumns[] = $column;
                }
            }

            if (!empty($submissionColumns)) {
                $stmt = $db->prepare('SELECT ' . implode(', ', $submissionColumns) . ' FROM document_submissions WHERE submission_id = :submission_id LIMIT 1');
                $stmt->execute(['submission_id' => $submission_id]);
                $submissionRow = $stmt->fetch();

                if ($submissionRow) {
                    $storedCurrency = in_array((string)($submissionRow['payment_currency'] ?? 'UGX'), ['UGX', 'USD'], true)
                        ? (string)$submissionRow['payment_currency']
                        : 'UGX';
                    $storedAmount = array_key_exists('payment_amount', $submissionRow) && $submissionRow['payment_amount'] !== null && $submissionRow['payment_amount'] !== ''
                        ? (float)$submissionRow['payment_amount']
                        : null;
                }
            }
        } catch (Exception $e) {
            error_log('Manual payment amount lookup failed: ' . $e->getMessage());
        }
    }

    if ($storedAmount !== null && $storedAmount > 0) {
        return [
            'amount' => round($storedAmount, 2),
            'currency' => $storedCurrency,
            'source' => 'manual_entry'
        ];
    }

    return ['amount' => $fallback, 'currency' => $storedCurrency, 'source' => 'manual_entry'];
}

function extract_bank_slip_amount_for_submission($db, $submission_id, $fallback_amount = 0.00) {
    return kiu_extract_bank_slip_amount_for_submission_impl($db, $submission_id, $fallback_amount);
}

/**
 * Generate admission number
 */
function generate_admission_number($year = null) {
    if (!$year) {
        $year = date('Y');
    }
    return 'KIU/' . $year . '/' . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);
}

/**
 * Send email notification
 */
function send_email($to, $subject, $message, $from = SMTP_FROM_EMAIL) {
    // This is a placeholder - implement actual email sending using PHPMailer
    // or similar library
    return @mail($to, $subject, $message, "From: $from");
}

/**
 * Get current user info
 */
function get_logged_in_user() {
    if (!is_logged_in()) return null;
    
    global $db;
    
    try {
        $stmt = $db->prepare("
            SELECT u.*, sp.full_name, sp.phone_number
            FROM users u
            LEFT JOIN student_profiles sp ON u.user_id = sp.user_id
            WHERE u.user_id = :user_id
        ");
        $stmt->execute(['user_id' => $_SESSION['user_id']]);
        return $stmt->fetch();
    } catch (Exception $e) {
        error_log("Failed to get current user: " . $e->getMessage());
        return null;
    }
}

/**
 * Normalize document text for comparison
 */
function kiu_normalize_document_text($text) {
    $text = (string)($text ?? '');
    $text = strtolower($text);
    $text = preg_replace('/\s+/', ' ', $text);
    $text = preg_replace('/[^a-z0-9\s]/', '', $text);
    return trim($text);
}

/**
 * Extract text from uploaded document
 */
function kiu_extract_document_text($file) {
    if (!is_array($file) || empty($file['tmp_name'])) {
        return '';
    }

    $tmpPath = (string)($file['tmp_name'] ?? '');
    $mimeType = (string)($file['type'] ?? '');

    if (!is_uploaded_file($tmpPath)) {
        return '';
    }

    $text = '';

    // Handle PDF files
    if (stripos($mimeType, 'pdf') !== false || stripos($tmpPath, '.pdf') !== false) {
        if (extension_loaded('pdfparser')) {
            try {
                $parser = new \Smalot\PdfParser\Parser();
                $pdf = $parser->parseFile($tmpPath);
                $text = $pdf->getText();
            } catch (Exception $e) {
                error_log('PDF parsing failed: ' . $e->getMessage());
            }
        }

        if (trim($text) === '') {
            $text = kiu_extract_text_with_pdftotext($tmpPath);
        }

        if (trim($text) === '') {
            $raw = @file_get_contents($tmpPath, false, null, 0, 262144);
            if (is_string($raw)) {
                $text = preg_replace('/[^\x09\x0A\x0D\x20-\x7E]/', ' ', $raw) ?: '';
            }
        }
    }
    // Handle image files with local Tesseract OCR when available.
    elseif (in_array($mimeType, ['image/jpeg', 'image/png', 'image/jpg'], true)) {
        $text = kiu_extract_text_with_tesseract($tmpPath);
        // Match python/ocr.py: very wide strips (e.g. TOTAL-row-only crops) are unreliable for amounts.
        $dims = @getimagesize($tmpPath);
        if (is_array($dims) && isset($dims[0], $dims[1]) && (int)$dims[1] > 0) {
            $iw = (int)$dims[0];
            $ih = (int)$dims[1];
            if (($iw / $ih) >= 2.5 && stripos((string)$text, 'DOCUMENT_SUBTYPE probable_total_row_only') === false) {
                $text = trim((string)$text . "\nDOCUMENT_SUBTYPE probable_total_row_only");
            }
        }
    }

    return (string)$text;
}

function kiu_extract_bank_slip_payment_data($text) {
    $text = (string)$text;
    $amount = null;
    $reference = null;
    $paymentDate = null;
    $currency = 'UGX';

    $returnEmpty = [
        'amount' => null,
        'currency' => 'UGX',
        'reference' => null,
        'payment_date' => null,
        'ocr_succeeded' => trim($text) !== ''
    ];

    if (kiu_bank_slip_ocr_text_looks_like_app_ui($text)) {
        return $returnEmpty;
    }

    // Narrow TOTAL-row crops (tagged during OCR): do not infer payment amount — ask for full slip.
    if (stripos($text, 'DOCUMENT_SUBTYPE probable_total_row_only') !== false) {
        return $returnEmpty;
    }

    if (preg_match('/\bUGX\b|\bUSh\b|\bSHS\b|UGANDA\s+SHILLINGS?/i', $text)) {
        $currency = 'UGX';
    } elseif (preg_match('/\bUSD\b|\$/i', $text)) {
        $currency = 'USD';
    }

    $amountCandidates = [];
    $lines = preg_split('/\R/', $text) ?: [];
    $numberTokenPattern = '/[0-9OIl\|][0-9OIl\|,\s\.\/]{2,}/';
    $lineLooksLikeCharges = static function ($line) {
        return preg_match('/bank\s*(?:charge|charges|fee)|\bcharges?\s*([^a-z]|$)|commission|stamp\s*duty|handling\s*f/i', (string)$line) === 1;
    };

    $parseAmountToken = static function ($token) {
        $token = (string)$token;
        if ($token === '') {
            return null;
        }

        $token = strtr($token, [
            'O' => '0',
            'o' => '0',
            'I' => '1',
            'l' => '1',
            '|' => '1'
        ]);
        $token = preg_replace('/[^0-9,\.\s\/]/', '', $token);
        $token = trim((string)$token, " ,./");
        if ($token === '') {
            return null;
        }

        $compact = preg_replace('/[\s\/,]/', '', $token);
        if (!is_string($compact) || $compact === '') {
            return null;
        }

        if (!preg_match('/^\d+(\.\d{1,2})?$/', $compact)) {
            $compact = preg_replace('/[^0-9]/', '', $compact);
        }
        if (!is_string($compact) || $compact === '') {
            return null;
        }

        $value = (float)$compact;
        return $value > 0 ? round($value, 2) : null;
    };

    $addCandidate = static function (&$bucket, $score, $value, $candidateCurrency) {
        if ($value === null || $value <= 0) {
            return;
        }
        $bucket[] = [
            'score' => (int)$score,
            'amount' => round((float)$value, 2),
            'currency' => $candidateCurrency === 'USD' ? 'USD' : 'UGX'
        ];
    };

    // Strongest signal: amount explicitly attached to TOTAL.
    if (preg_match_all('/\bt[o0]t[a4]l\b[\s:=\-]{0,20}([0-9OIl\|][0-9OIl\|,\s\.\/]{2,})/i', $text, $matches)) {
        foreach ($matches[1] as $token) {
            $addCandidate($amountCandidates, 360, $parseAmountToken($token), 'UGX');
        }
    }

    // TOTAL line and immediate following lines.
    foreach ($lines as $idx => $line) {
        if (!preg_match('/\bt[o0]t[a4]l\b/i', (string)$line)) {
            continue;
        }
        for ($offset = 0; $offset <= 2; $offset++) {
            $sample = (string)($lines[$idx + $offset] ?? '');
            if ($sample === '' || $lineLooksLikeCharges($sample)) {
                continue;
            }
            if (preg_match_all($numberTokenPattern, $sample, $lineMatches)) {
                foreach ($lineMatches[0] as $token) {
                    $addCandidate($amountCandidates, 320, $parseAmountToken($token), 'UGX');
                }
            }
        }
    }

    // Labeled amount fields (omit "cash" — often denomination column or mis-attached OCR).
    $labeledPattern = '/\b(amount|paid|deposit|total|sum)\b\s*[:=\-]?\s*(?:UGX|USh|SHS|USD|\$)?\s*([0-9OIl\|][0-9OIl\|,\s\.\/]{2,})/i';
    if (preg_match_all($labeledPattern, $text, $matches, PREG_OFFSET_CAPTURE)) {
        $countTokens = isset($matches[2]) ? count($matches[2]) : 0;
        for ($ti = 0; $ti < $countTokens; $ti++) {
            $capture = $matches[2][$ti];
            $tokenText = isset($capture[0]) ? (string)$capture[0] : '';
            $tokenPos = isset($capture[1]) ? (int)$capture[1] : null;
            if ($tokenPos === null) {
                continue;
            }
            $pre = substr($text, max(0, $tokenPos - 400), min(400, $tokenPos));
            $cursor = substr($text, $tokenPos, 400);
            $lineParts = preg_split('/\R/', $cursor, 2);
            $lineCombined = isset($lineParts[0]) ? (string)$lineParts[0] : $cursor;
            if ($lineLooksLikeCharges($pre . $lineCombined)) {
                continue;
            }
            $value = $parseAmountToken($tokenText);
            $labelPart = isset($matches[1][$ti][0]) ? (string)$matches[1][$ti][0] : '';
            $label = strtolower($labelPart);
            $candCurrency = preg_match('/\bUSD\b|\$/iu', $lineCombined) ? 'USD' : 'UGX';
            $boost = (($label === 'total' || $label === 'sum') ? 30 : 0);
            $addCandidate($amountCandidates, 190 + $boost, $value, $candCurrency);
        }
    }

    // Currency-prefixed values.
    if (preg_match_all('/\b(UGX|USh|SHS|USD)\b\s*([0-9OIl\|][0-9OIl\|,\s\.\/]{1,})/i', $text, $matches, PREG_OFFSET_CAPTURE)) {
        if (!empty($matches[2])) {
            foreach ($matches[2] as $i => $numCap) {
                $numStr = isset($numCap[0]) ? (string)$numCap[0] : '';
                $numPos = isset($numCap[1]) ? (int)$numCap[1] : -1;
                if ($numPos < 0) {
                    continue;
                }
                $curToken = isset($matches[1][$i][0]) ? (string)$matches[1][$i][0] : '';
                $pre = substr($text, max(0, $numPos - 420), min(420, $numPos));
                $suffix = substr($text, $numPos, min(380, strlen($text) - $numPos));
                if ($lineLooksLikeCharges($pre . $suffix)) {
                    continue;
                }
                $candCurrency = preg_match('/USD/i', $curToken) ? 'USD' : 'UGX';
                $addCandidate($amountCandidates, 130, $parseAmountToken($numStr), $candCurrency);
            }
        }
    }

    if (preg_match_all('/\$\s*([0-9OIl\|][0-9OIl\|,\s\.\/]{1,})/', $text, $matches)) {
        foreach ($matches[1] as $token) {
            $addCandidate($amountCandidates, 120, $parseAmountToken($token), 'USD');
        }
    }

    // Generic grouped fallback, bounded to realistic school-payment range.
    if (preg_match_all('/\b([0-9]{1,3}(?:,[0-9]{3})+(?:\.[0-9]{1,2})?)\b/', $text, $matches)) {
        foreach ($matches[1] as $token) {
            $value = $parseAmountToken($token);
            if ($value !== null && $value >= 1000 && $value <= 50000000) {
                $addCandidate($amountCandidates, 80, $value, 'UGX');
            }
        }
    }

    // Unformatted large totals "1183900" when TOTAL is present (avoid admission/reg rows).
    $admissionLine = '/admiss|reg\.?\s*no|registration|student\s*(?:no|number)|nin\b/i';
    if (preg_match('/\bt[o0]t[a4]l\b/i', $text)
        && preg_match_all('/\b\d{6,9}\b/', $text, $matches, PREG_OFFSET_CAPTURE)) {
        if (!empty($matches[0])) {
            foreach ($matches[0] as $cap) {
                $digits = isset($cap[0]) ? (string)$cap[0] : '';
                $pos = isset($cap[1]) ? (int)$cap[1] : -1;
                if ($pos < 0 || $digits === '') {
                    continue;
                }
                $pre = substr($text, max(0, $pos - 520), min(520, $pos));
                $suffix = substr($text, $pos, min(420, strlen($text) - $pos));
                $ctx = $pre . $suffix;
                if ($lineLooksLikeCharges($ctx) || preg_match($admissionLine, $ctx) === 1) {
                    continue;
                }
                $value = (float)$digits;
                if ($value >= 280000 && $value <= 50000000) {
                    $addCandidate($amountCandidates, 275, $value, 'UGX');
                }
            }
        }
    }

    if (!empty($amountCandidates)) {
        usort($amountCandidates, static function ($a, $b) {
            if ($a['score'] === $b['score']) {
                return $b['amount'] <=> $a['amount'];
            }
            return $b['score'] <=> $a['score'];
        });
        $amount = $amountCandidates[0]['amount'];
        $currency = $amountCandidates[0]['currency'];
    }

    if (preg_match('/(?:ref(?:erence)?|receipt|transaction|txn)\s*(?:no\.?|number|#)?\s*[:=\-]?\s*([A-Z0-9\-\/]{5,40})/i', $text, $matches)) {
        $reference = strtoupper(trim($matches[1]));
    }

    if (preg_match('/(?:date|paid on)\s*[:=\-]?\s*(\d{1,2}[\/\-]\d{1,2}[\/\-]\d{2,4}|\d{4}[\/\-]\d{1,2}[\/\-]\d{1,2})/i', $text, $matches)) {
        $rawDate = str_replace('/', '-', trim($matches[1]));
        $timestamp = strtotime($rawDate);
        if ($timestamp !== false && $timestamp <= time()) {
            $paymentDate = date('Y-m-d', $timestamp);
        }
    }

    return [
        'amount' => $amount,
        'currency' => $currency,
        'reference' => $reference,
        'payment_date' => $paymentDate,
        'ocr_succeeded' => trim($text) !== ''
    ];
}

function kiu_truncate_text_for_db($text, $maxLength = 20000) {
    $text = trim((string)$text);
    if ($text === '') {
        return null;
    }

    if (function_exists('mb_strlen') && function_exists('mb_substr')) {
        if (mb_strlen($text, 'UTF-8') > $maxLength) {
            return mb_substr($text, 0, $maxLength, 'UTF-8') . "\n[TRUNCATED]";
        }
        return $text;
    }

    if (strlen($text) > $maxLength) {
        return substr($text, 0, $maxLength) . "\n[TRUNCATED]";
    }

    return $text;
}

function kiu_has_duplicate_document_hash($db, $hash, $documentType = null, $excludeSubmissionId = null) {
    if (!table_exists($db, 'document_uploads') || !column_exists($db, 'document_uploads', 'file_hash')) {
        return false;
    }

    $sql = "SELECT COUNT(*) FROM document_uploads WHERE file_hash = :file_hash";
    $params = ['file_hash' => $hash];

    if ($documentType !== null) {
        $sql .= " AND document_type = :document_type";
        $params['document_type'] = $documentType;
    }

    if ($excludeSubmissionId !== null) {
        $sql .= " AND submission_id <> :submission_id";
        $params['submission_id'] = (int)$excludeSubmissionId;
    }

    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    return (int)$stmt->fetchColumn() > 0;
}

/**
 * Detect document category based on text and field name
 */
function kiu_detect_document_category($documentText, $mimeType, $fieldName) {
    $text = kiu_normalize_document_text($documentText);
    $confidence = 0;
    $category = 'other';
    $keywords = [];

    // Define category patterns
    $patterns = [
        'admission_letter' => ['admission', 'admitted', 'welcome', 'congratulation', 'enrollment', 'kiu'],
        'academic_supporting_document' => ['certificate', 'diploma', 's6', 'secondary', 'examination', 'result', 'transcript'],
        'national_id_passport' => ['national id', 'passport', 'identity', 'date of birth', 'citizenship', 'passport number'],
        'former_school_id' => ['school', 'student id', 'student number', 'institution', 'registration'],
        'passport_photo' => ['photo', 'photograph', 'image'],
        'bank_slip' => ['bank', 'account', 'transaction', 'deposit', 'payment', 'slip', 'receipt', 'amount', 'ugx', 'usd']
    ];

    $fieldPatterns = $patterns[$fieldName] ?? [];

    foreach ($patterns as $cat => $catKeywords) {
        $matches = 0;
        foreach ($catKeywords as $keyword) {
            if (strpos($text, $keyword) !== false) {
                $matches++;
                $keywords[] = $keyword;
            }
        }
        if ($matches > 0) {
            $matchRatio = $matches / count($catKeywords);
            if ($matchRatio > $confidence) {
                $confidence = $matchRatio;
                $category = $cat;
            }
        }
    }

    if ($fieldName === 'admission_letter') {
        $admissionHints = [
            'admission letter',
            'admitted',
            'admission',
            'offer letter',
            'provisional admission',
            'accepted',
            'congratulations',
            'welcome to',
            'program',
            'enrollment'
        ];

        $admissionMatches = 0;
        foreach ($admissionHints as $hint) {
            if (strpos($text, $hint) !== false) {
                $admissionMatches++;
                $keywords[] = $hint;
            }
        }

        if ($admissionMatches > 0) {
            $admissionConfidence = min(0.95, 0.35 + ($admissionMatches * 0.12));
            if ($admissionConfidence >= $confidence) {
                $category = 'admission_letter';
                $confidence = $admissionConfidence;
            }
        }
    }

    return [
        'category' => $category,
        'confidence' => round($confidence, 2),
        'keywords' => array_values(array_unique($keywords))
    ];
}

/**
 * Extract identity information from document text
 */
function kiu_extract_document_identity($documentText) {
    $text = (string)$documentText;
    $identity = [
        'full_name' => '',
        'date_of_birth' => '',
        'admission_number' => '',
        'registration_number' => '',
        'national_id_number' => '',
        'passport_number' => ''
    ];

    // Extract full name (look for name-like patterns)
    if (preg_match('/(?:name|named|applicant)[:\s]+([A-Z][a-z]+(?:\s+[A-Z][a-z]+)+)/i', $text, $matches)) {
        $identity['full_name'] = trim($matches[1]);
    }

    // Extract date of birth
    if (preg_match('/(?:date of birth|dob)[:\s]+(\d{1,2}[\/\-]\d{1,2}[\/\-]\d{2,4})/i', $text, $matches)) {
        $identity['date_of_birth'] = trim($matches[1]);
    }

    // Extract admission number
    if (preg_match('/(?:admission|admission number|adm)[:\s]+([A-Z0-9]+)/i', $text, $matches)) {
        $identity['admission_number'] = trim($matches[1]);
    }

    // Extract registration number
    if (preg_match('/(?:registration|reg|registration number)[:\s]+([A-Z0-9]+)/i', $text, $matches)) {
        $identity['registration_number'] = trim($matches[1]);
    }

    // Extract national ID
    if (preg_match('/(?:national id|id number)[:\s]+([A-Z0-9]+)/i', $text, $matches)) {
        $identity['national_id_number'] = trim($matches[1]);
    }

    // Extract passport number
    if (preg_match('/(?:passport|passport number)[:\s]+([A-Z0-9]+)/i', $text, $matches)) {
        $identity['passport_number'] = trim($matches[1]);
    }

    return $identity;
}

/**
 * Check if two names match (fuzzy comparison)
 */
function kiu_names_match($name1, $name2) {
    $n1 = kiu_normalize_document_text($name1);
    $n2 = kiu_normalize_document_text($name2);

    if ($n1 === $n2) {
        return true;
    }

    $parts1 = array_filter(explode(' ', $n1));
    $parts2 = array_filter(explode(' ', $n2));

    if (count($parts1) === 0 || count($parts2) === 0) {
        return false;
    }

    // Check if all parts of the shorter name appear in the longer name
    $shorter = count($parts1) <= count($parts2) ? $parts1 : $parts2;
    $longer = count($parts1) <= count($parts2) ? $parts2 : $parts1;

    $matches = 0;
    foreach ($shorter as $part) {
        if (in_array($part, $longer, true)) {
            $matches++;
        }
    }

    $matchRatio = $matches / count($shorter);
    return $matchRatio >= 0.75;
}
