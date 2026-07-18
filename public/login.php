<?php
require __DIR__ . '/../includes/bootstrap.php';

use App\Auth;

if (Auth::check()) {
    header('Location: /dashboard.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    $result = Auth::attempt($email, $password);
    if ($result['success']) {
        header('Location: /dashboard.php');
        exit;
    }
    $error = $result['error'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign in — Email Bot</title>
    <link rel="stylesheet" href="/assets/style.css">
</head>
<body>
<div class="auth-wrap">
    <div class="auth-card">
        <div class="brand">Email Bot</div>
        <p class="sub">Sign in to manage your campaigns.</p>

        <?php if ($error): ?><div class="alert alert-error"><?= htmlspecialchars($error) ?></div><?php endif; ?>

        <form method="POST">
            <label>Email</label>
            <input type="email" name="email" required value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">

            <label>Password</label>
            <input type="password" name="password" required>

            <button type="submit" style="width:100%">Sign in</button>
        </form>

        <p style="margin-top:20px;font-size:13px;color:var(--text-muted)">
            Don't have an account? <a href="/register.php">Create one</a>
        </p>
    </div>
</div>
</body>
</html>
