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
    $name = trim($_POST['name'] ?? '');

    if (!filter_var($email, FILTER_VALIDATE_EMAIL) || !$name) {
        $error = 'Enter a valid name and email address.';
    } else {
        $result = Auth::register($email, $password, $name);
        if ($result['success']) {
            header('Location: /dashboard.php');
            exit;
        }
        $error = $result['error'];
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create account — Email Bot</title>
    <link rel="stylesheet" href="/assets/style.css">
</head>
<body>
<div class="auth-wrap">
    <div class="auth-card">
        <div class="brand">Email Bot</div>
        <p class="sub">Create an account to start sending campaigns.</p>

        <?php if ($error): ?><div class="alert alert-error"><?= htmlspecialchars($error) ?></div><?php endif; ?>

        <form method="POST">
            <label>Your name</label>
            <input type="text" name="name" required value="<?= htmlspecialchars($_POST['name'] ?? '') ?>">

            <label>Email</label>
            <input type="email" name="email" required value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">

            <label>Password</label>
            <input type="password" name="password" required minlength="8">

            <button type="submit" style="width:100%">Create account</button>
        </form>

        <p style="margin-top:20px;font-size:13px;color:var(--text-muted)">
            Already have an account? <a href="/login.php">Sign in</a>
        </p>
    </div>
</div>
</body>
</html>
