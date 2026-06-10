<?php
/**
 * CLI-only emergency admin password reset utility.
 *
 * Usage:
 *   set KIU_ADMIN_EMAIL=admin@kiu.ac.ug
 *   set KIU_ADMIN_NEW_PASSWORD=StrongPassword123
 *   C:\xampp\php\php.exe reset_admin_password.php
 */

if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit('Not found.');
}

require_once __DIR__ . '/config/init.php';

$adminEmail = strtolower(trim((string)(getenv('KIU_ADMIN_EMAIL') ?: 'admin@kiu.ac.ug')));
$newPassword = (string)(getenv('KIU_ADMIN_NEW_PASSWORD') ?: '');

$validator = new Validator();
$validator->email('email', $adminEmail, 'Admin email');
$validator->password('password', $newPassword, 'New password');

if ($validator->hasErrors()) {
    fwrite(STDERR, $validator->getFirstError() . PHP_EOL);
    exit(1);
}

try {
    $stmt = $db->prepare("
        UPDATE users
        SET password_hash = :password_hash,
            login_attempts = 0,
            locked_until = NULL
        WHERE LOWER(email) = :email
          AND role = :role
    ");
    $stmt->execute([
        'password_hash' => password_hash($newPassword, PASSWORD_BCRYPT),
        'email' => $adminEmail,
        'role' => ROLE_ADMIN
    ]);

    if ($stmt->rowCount() !== 1) {
        fwrite(STDERR, 'Admin user not found or password unchanged.' . PHP_EOL);
        exit(1);
    }

    log_activity('ADMIN_PASSWORD_RESET_CLI', 'Admin password reset from CLI utility for ' . $adminEmail);
    fwrite(STDOUT, 'Admin password reset completed for ' . $adminEmail . PHP_EOL);
} catch (Throwable $e) {
    error_log('Admin password reset failed: ' . $e->getMessage());
    fwrite(STDERR, 'Admin password reset failed.' . PHP_EOL);
    exit(1);
}

