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

function redirect_to_requested_page_if_available() {
    $requested = $_SESSION['redirect_after_login'] ?? '';
    if (empty($requested)) {
        return false;
    }

    unset($_SESSION['redirect_after_login']);
    $path = ltrim(parse_url($requested, PHP_URL_PATH) ?? '', '/');
    $basePath = trim(parse_url(BASE_URL, PHP_URL_PATH) ?? '', '/');

    if ($basePath !== '' && strpos($path, $basePath . '/') === 0) {
        $path = substr($path, strlen($basePath) + 1);
    }

    if ($path !== '') {
        redirect($path);
        return true;
    }

    return false;
}

if (isset($_GET['switch']) && is_logged_in()) {
    $auth = new Auth($db);
    $auth->logout();
    redirect('login.php');
}

$error = '';
$info = '';

if (is_logged_in()) {
    $currentRole = $_SESSION['role'] ?? 'unknown';
    $info = "You are currently logged in as " . htmlspecialchars($_SESSION['admission_number'] ?? 'user') . " ({$currentRole}). Log in again below to switch account.";
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $auth = new Auth($db);
    
    $login_identifier = sanitize_input($_POST['login_identifier'] ?? '');
    $password = $_POST['password'] ?? '';
    
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        $error = 'Invalid request. Please refresh the page and try again.';
    } elseif (!empty($login_identifier) && !empty($password)) {
        $result = $auth->login($login_identifier, $password);
        
        if ($result['success']) {
            session_regenerate_id(true);
            if (!redirect_to_requested_page_if_available()) {
                redirect_by_role($result['user']['role'] ?? '');
            }
        } else {
            $error = $result['message'];
        }
    } else {
        $error = 'Please provide your Institution ID (or email) and password';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - <?php echo APP_NAME; ?></title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/style.css">
    <style>
        :root {
            --ink: #f5fbff;
            --muted: rgba(245, 251, 255, 0.78);
            --line: rgba(255, 255, 255, 0.24);
            --panel: rgba(255, 255, 255, 0.18);
            --panel-strong: rgba(255, 255, 255, 0.24);
            --danger-bg: rgba(129, 17, 44, 0.28);
            --danger-text: #fff1f4;
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

        .login-shell {
            width: 100%;
            display: flex;
            justify-content: center;
            position: relative;
            z-index: 1;
        }

        .login-card {
            width: 100%;
            max-width: 360px;
            padding: 26px 22px 22px;
            border: 1px solid var(--line);
            border-radius: 22px;
            background: linear-gradient(180deg, rgba(255, 255, 255, 0.22), rgba(255, 255, 255, 0.12));
            box-shadow: 0 18px 42px rgba(6, 26, 24, 0.28);
            backdrop-filter: blur(14px);
            -webkit-backdrop-filter: blur(14px);
        }

        .brand-block {
            text-align: center;
            margin-bottom: 16px;
        }

        .brand-logo {
            width: 250px;
            max-width: 100%;
            height: auto;
            object-fit: contain;
            display: inline-block;
            margin-bottom: 8px;
            filter: drop-shadow(0 8px 18px rgba(0, 0, 0, 0.22));
        }

        .brand-name {
            margin: 0;
            font-size: 10px;
            letter-spacing: 1.8px;
            text-transform: uppercase;
            color: rgba(255, 255, 255, 0.82);
        }

        .form-head h2 {
            margin: 0;
            font-size: 27px;
            font-weight: 700;
            text-align: center;
            text-shadow: 0 2px 10px rgba(0, 0, 0, 0.16);
        }

        .form-head p {
            margin: 8px 0 0;
            color: var(--muted);
            font-size: 13px;
            text-align: center;
        }

        .error-box {
            margin-top: 14px;
            border: 1px solid rgba(255, 204, 216, 0.38);
            background: var(--danger-bg);
            color: var(--danger-text);
            border-radius: 10px;
            padding: 10px 12px;
            font-size: 13px;
        }

        .auth-form { margin-top: 20px; }
        .form-group { margin-bottom: 14px; }

        .form-group label {
            display: block;
            margin-bottom: 6px;
            font-size: 13px;
            font-weight: 600;
            color: rgba(255, 255, 255, 0.92);
        }

        .form-group input[type="text"],
        .form-group input[type="password"] {
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

        .remember-row {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 16px;
            font-size: 12px;
            color: rgba(255, 255, 255, 0.86);
        }

        .btn-login {
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

        .btn-login:hover { filter: brightness(1.06); }

        .auth-footer {
            margin-top: 16px;
            padding: 14px 12px 10px;
            border-radius: 14px;
            background: rgba(18, 38, 30, 0.34);
            border: 1px solid rgba(255, 255, 255, 0.18);
            text-align: center;
            font-size: 13px;
        }

        .auth-footer a {
            color: #ffffff;
            text-decoration: underline;
            font-weight: 600;
            text-shadow: 0 1px 6px rgba(0, 0, 0, 0.3);
        }

        .auth-footer p {
            margin: 8px 0 0;
            color: rgba(255, 255, 255, 0.98);
        }

        .alert-info {
            border: 1px solid rgba(255, 255, 255, 0.22);
            background: rgba(255, 255, 255, 0.14);
            color: #f7fbff;
            border-radius: 10px;
        }

        @media (max-width: 480px) {
            body {
                padding: 16px;
            }

            .login-card {
                max-width: 100%;
                padding: 22px 18px 20px;
                border-radius: 18px;
            }

            .form-head h2 {
                font-size: 24px;
            }
        }
    </style>
</head>
<body>
    <div class="login-shell">
        <div class="login-card">
            <div class="brand-block">
                <img src="<?php echo BASE_URL; ?>logs/kiu.png" alt="KIU Logo" class="brand-logo">
                <p class="brand-name">Automated Verification System</p>
            </div>
            <div class="form-head">
                <h2>System Login</h2>
                <p>Use your Institution ID or email with your password.</p>
            </div>

            <?php if ($info): ?>
                <div class="alert alert-info" style="margin-top: 14px;">
                    <?php echo $info; ?>
                    <br><a href="<?php echo BASE_URL; ?>login.php?switch=1">Logout current session</a>
                </div>
            <?php endif; ?>
            
            <?php if ($error): ?>
                <div class="error-box"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>
            
            <form method="POST" action="" class="auth-form">
                <?php echo csrf_token_field(); ?>
                <div class="form-group">
                          <label for="login_identifier">Institution ID or Email</label>
                          <input type="text" id="login_identifier" name="login_identifier" 
                              value="<?php echo htmlspecialchars($_POST['login_identifier'] ?? ''); ?>" 
                           required placeholder="KIU/2026/0001, FIN-001, or you@example.com">
                </div>
                
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" 
                           required placeholder="Password">
                </div>
                
                <div class="remember-row">
                    <input type="checkbox" id="remember_me" name="remember_me">
                    <label for="remember_me">Remember me</label>
                </div>
                
                <button type="submit" class="btn-login">Sign In</button>
            </form>

            <div class="auth-footer">
                <p><a href="forgot_password.php">Forgot password?</a></p>
                <p>Don't have an account? <a href="register.php">Register here</a></p>
            </div>
        </div>
    </div>
</body>
</html>
