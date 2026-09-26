<?php
// login.php
session_start();
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/functions.php';

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');

    require_once __DIR__ . '/includes/auth.php';
    if (loginUser($username, $password)) {
        header('Location: index.php');
        exit;
    } else {
        $error = 'Invalid username or password.';
    }
}

$settings = getClinicSettings();
?>
<!DOCTYPE html>
<html lang="en" data-bs-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - <?= htmlspecialchars($settings['clinic_name']) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        body {
            background: linear-gradient(135deg, #0f172a 0%, #0284c7 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .login-card {
            width: 100%;
            max-width: 420px;
            border-radius: 16px;
            box-shadow: 0 20px 25px -5px rgba(0,0,0,0.3);
            border: none;
        }
    </style>
</head>
<body>
<div class="container">
    <div class="card login-card p-4 p-md-5 mx-auto bg-body text-body">
        <div class="text-center mb-4">
            <div class="sidebar-brand-icon mx-auto mb-3" style="width:56px; height:56px; font-size:1.8rem;">
                <i class="fa-solid fa-user-doctor"></i>
            </div>
            <h4 class="fw-bold mb-1"><?= htmlspecialchars($settings['clinic_name']) ?></h4>
            <p class="text-muted fs-7"><?= htmlspecialchars($settings['doctor_name']) ?> Management Portal</p>
        </div>

        <?php if ($error): ?>
            <div class="alert alert-danger py-2 fs-7" role="alert">
                <i class="fa-solid fa-triangle-exclamation me-2"></i><?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="login.php">
            <div class="mb-3">
                <label class="form-label fw-semibold">Username</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="fa-solid fa-user text-muted"></i></span>
                    <input type="text" name="username" class="form-control" placeholder="Enter username" value="doctor" required autofocus>
                </div>
            </div>

            <div class="mb-4">
                <label class="form-label fw-semibold">Password</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="fa-solid fa-lock text-muted"></i></span>
                    <input type="password" name="password" class="form-control" placeholder="Enter password" value="doctor123" required>
                </div>
            </div>

            <button type="submit" class="btn btn-primary w-100 py-2 fw-semibold">
                <i class="fa-solid fa-right-to-bracket me-2"></i> Log In
            </button>
        </form>

        <div class="mt-4 text-center fs-8 text-muted">
            <p class="mb-1">Default Demo Credentials:</p>
            <code>Username: doctor | Password: doctor123</code>
        </div>
    </div>
</div>
</body>
</html>
