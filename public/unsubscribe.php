<?php
require __DIR__ . '/../includes/bootstrap.php';

use App\Database;
use App\Contact;

$token = $_GET['token'] ?? '';
$pdo = Database::connect();

$stmt = $pdo->prepare("SELECT contact_id FROM unsubscribe_tokens WHERE token = :t");
$stmt->execute([':t' => $token]);
$contactId = $stmt->fetchColumn();

$success = false;
if ($contactId) {
    Contact::unsubscribe((int)$contactId);
    $success = true;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Unsubscribe</title>
    <link rel="stylesheet" href="/assets/style.css">
</head>
<body>
<div class="auth-wrap">
    <div class="auth-card" style="text-align:center">
        <div class="brand" style="justify-content:center">Email Bot</div>
        <?php if ($success): ?>
            <h2 style="margin-top:16px">You're unsubscribed</h2>
            <p class="sub">You won't receive any further emails from this sender.</p>
        <?php else: ?>
            <h2 style="margin-top:16px">Link not found</h2>
            <p class="sub">This unsubscribe link is invalid or has already been used.</p>
        <?php endif; ?>
    </div>
</div>
</body>
</html>
