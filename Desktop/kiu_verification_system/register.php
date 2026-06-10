<?php
require_once 'config/init.php';

function redirect_by_role($role) {
    if ($role === ROLE_STUDENT) {
        redirect('modules/student/dashboard.php');
    } elseif ($role === ROLE_FINANCE) {
        redirect('modules/finance/dashboard.php');
    } elseif ($role === ROLE_REGISTRAR) {
        redirect('modules/admissions/dashboard.php');
    } elseif ($role === ROLE_ADMIN) {
        redirect('modules/admin/dashboard.php');
    } else {
        redirect('index.php');
    }
}

// Redirect if already logged in
if (is_logged_in()) {
    redirect_by_role($_SESSION['role'] ?? '');
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $validator = new Validator();
    $auth = new Auth($db);
    
    $admission_number = sanitize_input($_POST['admission_number'] ?? '');
    $email = sanitize_input($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        $validator->addError('csrf_token', 'Invalid request. Please refresh the page and try again.');
    }

    // Validate inputs
    $validator->required('admission_number', $admission_number, 'Admission Number');
    $validator->admissionNumber('admission_number', $admission_number, 'Admission Number');
    $validator->required('email', $email, 'Email');
    $validator->email('email', $email, 'Email');
    $validator->required('password', $password, 'Password');
    $validator->password('password', $password, 'Password');
    $validator->match('confirm_password', $confirm_password, $password, 'Confirm Password', 'Password');
    
    if (!$validator->hasErrors()) {
        $result = $auth->register($admission_number, $email, $password);
        
        if ($result['success']) {
            $success = $result['message'];
            // Redirect to login after 2 seconds
            header("refresh:2;url=login.php");
        } else {
            $error = $result['message'];
        }
    } else {
        $error = $validator->getFirstError();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Registration - <?php echo APP_NAME; ?></title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/style.css">
    <style>
        :root {
            --ink: #f5fbff;
            --muted: rgba(245, 251, 255, 0.78);
            --line: rgba(255, 255, 255, 0.24);
            --danger-bg: rgba(129, 17, 44, 0.28);
            --danger-text: #fff1f4;
            --success-bg: rgba(17, 99, 59, 0.28);
            --success-text: #f0fff6;
            --button-start: #28b463;
            --button-end: #1f8f4d;
        }

        * { box-sizing: border-box; }

        body {
            margin: 0;
            min-height: 100vh;
            font-family: "Segoe UI", "Trebuchet MS", Tahoma, sans-serif;
            background:
                linear-gradient(180deg, rgba(8, 31, 22, 0.2), rgba(13, 43, 31, 0.4)),
                url('<?php echo BASE_URL; ?>student.jpg') center center / cover no-repeat;
            color: var(--ink);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            position: relative;
            overflow: hidden;
        }

        body::before {
            content: "";
            position: absolute;
            inset: 0;
            background:
                radial-gradient(circle at top right, rgba(144, 255, 221, 0.2), transparent 30%),
                radial-gradient(circle at bottom left, rgba(122, 175, 255, 0.22), transparent 35%);
            pointer-events: none;
        }

        .register-shell {
            width: 100%;
            display: flex;
            justify-content: center;
            position: relative;
            z-index: 1;
        }

        .auth-box {
            width: min(100%, 300px);
            max-width: 300px;
            padding: 26px 22px 20px;
            border: 1px solid var(--line);
            border-radius: 22px;
            background: linear-gradient(180deg, rgba(255, 255, 255, 0.22), rgba(255, 255, 255, 0.12));
            box-shadow: 0 18px 42px rgba(6, 26, 24, 0.28);
            backdrop-filter: blur(14px);
            -webkit-backdrop-filter: blur(14px);
        }

        .auth-header {
            text-align: center;
            margin-bottom: 18px;
        }

        .auth-header h2 {
            margin: 0;
            font-size: 28px;
            font-weight: 700;
            text-shadow: 0 2px 10px rgba(0, 0, 0, 0.16);
        }

        .auth-header p {
            margin: 8px 0 0;
            color: var(--muted);
            font-size: 13px;
        }

        .verification-note {
            margin-top: 14px;
            padding: 12px 12px 10px;
            border-radius: 14px;
            border: 1px solid rgba(255, 255, 255, 0.18);
            background: rgba(18, 38, 30, 0.34);
            color: rgba(255, 255, 255, 0.95);
            font-size: 12.5px;
            line-height: 1.45;
        }

        .verification-note strong {
            display: block;
            margin-bottom: 4px;
            font-size: 13px;
        }

        .alert {
            margin-top: 14px;
            border-radius: 10px;
            padding: 10px 12px;
            font-size: 13px;
        }

        .alert-danger {
            border: 1px solid rgba(255, 204, 216, 0.38);
            background: var(--danger-bg);
            color: var(--danger-text);
        }

        .alert-success {
            border: 1px solid rgba(209, 255, 224, 0.3);
            background: var(--success-bg);
            color: var(--success-text);
        }

        .auth-form { margin-top: 20px; }

        .form-group {
            margin-bottom: 14px;
        }

        .form-group label {
            display: block;
            margin-bottom: 6px;
            font-size: 13px;
            font-weight: 600;
            color: rgba(255, 255, 255, 0.92);
        }

        .form-group input {
            width: 100%;
            height: 40px;
            border: 1px solid rgba(255, 255, 255, 0.34);
            border-radius: 8px;
            padding: 0 12px;
            font-size: 14px;
            color: #21443a;
            background: rgba(255, 255, 255, 0.92);
        }

        .form-group input:focus {
            outline: none;
            border-color: rgba(170, 204, 255, 0.9);
            box-shadow: 0 0 0 3px rgba(144, 191, 255, 0.22);
        }

        .form-group small {
            display: block;
            margin-top: 6px;
            color: rgba(255, 255, 255, 0.82);
            font-size: 11.5px;
        }

        .btn {
            width: 100%;
            height: 44px;
            border: 0;
            border-radius: 10px;
            background: linear-gradient(90deg, var(--button-start), var(--button-end));
            color: #fff;
            font-size: 14px;
            font-weight: 700;
            cursor: pointer;
            box-shadow: 0 10px 24px rgba(19, 90, 48, 0.34);
        }

        .btn:hover {
            filter: brightness(1.06);
        }

        .auth-footer {
            margin-top: 16px;
            padding: 14px 12px 10px;
            border-radius: 14px;
            background: rgba(18, 38, 30, 0.34);
            border: 1px solid rgba(255, 255, 255, 0.18);
            text-align: center;
            font-size: 13px;
        }

        .auth-footer p {
            margin: 0;
            color: rgba(255, 255, 255, 0.98);
        }

        .auth-footer a {
            color: #ffffff;
            text-decoration: underline;
            font-weight: 600;
            text-shadow: 0 1px 6px rgba(0, 0, 0, 0.3);
        }

        @media (max-width: 480px), (max-height: 820px) {
            body {
                padding: 16px;
                align-items: flex-start;
                overflow-y: auto;
            }

            .register-shell {
                padding: 12px 14px 16px;
            }

            .auth-box {
                width: min(100%, 290px);
                max-width: 290px;
                padding: 20px 16px 18px;
                border-radius: 18px;
            }

            .auth-header h2 {
                font-size: 24px;
            }
        }
    </style>
</head>
<body>
    <div class="register-shell">
        <div class="auth-box">
            <div class="auth-header">
                <h2>Student Registration</h2>
                <p>KIU Automated Verification System</p>
            </div>

            <div class="verification-note">
                <strong>OCR-backed document checks</strong>
                When documents are uploaded in the student submission flow, the system reads bank slips, extracts the total paid amount, and stores it for review.
            </div>
            
            <?php if ($error): ?>
                <div class="alert alert-danger"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div class="alert alert-success"><?php echo $success; ?></div>
            <?php endif; ?>
            
            <form method="POST" action="" class="auth-form">
                <?php echo csrf_token_field(); ?>
                <div class="form-group">
                    <label for="admission_number">Admission Number</label>
                    <input type="text" id="admission_number" name="admission_number" 
                           value="<?php echo htmlspecialchars($_POST['admission_number'] ?? ''); ?>" 
                           required placeholder="e.g., KIU/2024/001">
                    <small>Enter your university admission number</small>
                </div>
                
                <div class="form-group">
                    <label for="email">Email Address</label>
                    <input type="email" id="email" name="email" 
                           value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>" 
                           required placeholder="your.email@student.kiu.ac.ug">
                </div>
                
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" required 
                           placeholder="Minimum 8 characters">
                    <small>Must contain uppercase, lowercase, and numbers</small>
                </div>
                
                <div class="form-group">
                    <label for="confirm_password">Confirm Password</label>
                    <input type="password" id="confirm_password" name="confirm_password" 
                           required placeholder="Re-enter your password">
                </div>
                
                <button type="submit" class="btn btn-primary btn-block">Register</button>
            </form>
            
            <div class="auth-footer">
                <p>Already have an account? <a href="login.php">Login here</a></p>
            </div>
        </div>
    </div>
</body>
</html>
