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
<div class="auth-split">
    <div class="auth-visual">
        <div class="auth-visual-blob blob-1"></div>
        <div class="auth-visual-blob blob-2"></div>
        <div class="auth-visual-content">
            <span class="brand-mark-lg">
                <svg width="40" height="40" viewBox="0 0 24 24" fill="none">
                    <rect width="24" height="24" rx="6" fill="#ffffff"/>
                    <path d="M5 8.5 12 13l7-4.5" stroke="#0969da" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/>
                    <rect x="5" y="7" width="14" height="10" rx="1.6" stroke="#0969da" stroke-width="1.6" fill="none"/>
                </svg>
            </span>
            <h2>Welcome back. Your queue's been waiting.</h2>
            <p>Pick up your campaigns, contacts, and tools right where you left off.</p>
            <div class="floaty-icons">
                <span><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2"><rect x="3" y="5" width="18" height="14" rx="2"/><path d="m3 7 9 6 9-6"/></svg></span>
                <span><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2"><circle cx="12" cy="8" r="4"/><path d="M6 21v-2a4 4 0 0 1 4-4h4a4 4 0 0 1 4 4v2"/></svg></span>
                <span><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2"><path d="M20 6 9 17l-5-5"/></svg></span>
            </div>
        </div>
    </div>
    <div class="auth-form-col">
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
</div>
</body>
</html>
