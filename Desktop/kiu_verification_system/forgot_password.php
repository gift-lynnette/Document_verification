<?php
require_once 'config/init.php';

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $identifier = sanitize_input($_POST['identifier'] ?? '');
    if ($identifier !== '') {
        $message = 'Password reset is not automated in this local deployment yet. Please contact the system administrator or registrar for assistance.';
    } else {
        $message = 'Enter your email address or admission number first.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password - <?php echo APP_NAME; ?></title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/style.css">
    <style>
        body {
            margin: 0;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            background: #f5f8f6;
        }

        .reset-card {
            width: 100%;
            max-width: 440px;
            background: #fff;
            border: 1px solid #e2e8f0;
            border-radius: 14px;
            box-shadow: 0 10px 24px rgba(15, 41, 66, 0.08);
            overflow: hidden;
        }

        .reset-header {
            padding: 24px 24px 18px;
            background: #3aa76d;
            color: #fff;
        }

        .reset-header h1 {
            margin: 0 0 6px;
            font-size: 1.6rem;
        }

        .reset-body {
            padding: 24px;
        }

        .reset-body p {
            margin-bottom: 1rem;
        }

        .reset-actions {
            display: flex;
            gap: 0.75rem;
            flex-wrap: wrap;
            margin-top: 1rem;
        }
    </style>
</head>
<body>
    <div class="reset-card">
        <div class="reset-header">
            <h1>Forgot Password</h1>
            <p>Enter your email or admission number to continue.</p>
        </div>
        <div class="reset-body">
            <?php if ($message !== ''): ?>
                <div class="alert alert-info"><?php echo htmlspecialchars($message); ?></div>
            <?php endif; ?>

            <form method="POST" action="">
                <div class="form-group">
                    <label for="identifier">Email Address / Admission Number</label>
                    <input type="text" id="identifier" name="identifier" class="form-control" placeholder="you@example.com or KIU/2026/001" required>
                </div>
                <button type="submit" class="btn btn-primary btn-block">Continue</button>
            </form>

            <div class="reset-actions">
                <a href="<?php echo BASE_URL; ?>login.php" class="btn btn-secondary">Back to Login</a>
            </div>
        </div>
    </div>
</body>
</html>
