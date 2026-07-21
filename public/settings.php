<?php
require __DIR__ . '/../includes/bootstrap.php';

use App\Auth;
use App\Database;

Auth::requireLogin();
$userId = Auth::id();
$pdo = Database::connect();

$message = '';
$messageType = 'success';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'update_profile') {
        $name = trim($_POST['name'] ?? '');
        if ($name) {
            $pdo->prepare("UPDATE users SET name = :name WHERE id = :id")
                ->execute([':name' => $name, ':id' => $userId]);
            $_SESSION['user_name'] = $name;
            $message = 'Profile updated.';
        }
    }

    if ($action === 'update_sender') {
        $senderName = trim($_POST['sender_name'] ?? '');
        $replyTo = trim($_POST['reply_to_email'] ?? '');

        if ($replyTo && !filter_var($replyTo, FILTER_VALIDATE_EMAIL)) {
            $message = 'Enter a valid reply-to email address.';
            $messageType = 'error';
        } else {
            $pdo->prepare("UPDATE users SET sender_name = :sn, reply_to_email = :rt WHERE id = :id")
                ->execute([':sn' => $senderName ?: null, ':rt' => $replyTo ?: null, ':id' => $userId]);
            $message = 'Sender identity updated. Future campaigns will use this.';
        }
    }

    if ($action === 'change_password') {
        $current = $_POST['current_password'] ?? '';
        $new = $_POST['new_password'] ?? '';

        $stmt = $pdo->prepare("SELECT password_hash FROM users WHERE id = :id");
        $stmt->execute([':id' => $userId]);
        $hash = $stmt->fetchColumn();

        if (!password_verify($current, $hash)) {
            $message = 'Current password is incorrect.';
            $messageType = 'error';
        } elseif (strlen($new) < 8) {
            $message = 'New password must be at least 8 characters.';
            $messageType = 'error';
        } else {
            $pdo->prepare("UPDATE users SET password_hash = :hash WHERE id = :id")
                ->execute([':hash' => password_hash($new, PASSWORD_DEFAULT), ':id' => $userId]);
            $message = 'Password changed.';
        }
    }
}

$stmt = $pdo->prepare("SELECT sender_name, reply_to_email, email FROM users WHERE id = :id");
$stmt->execute([':id' => $userId]);
$senderInfo = $stmt->fetch();

$pageTitle = 'Settings';
$activeNav = 'settings';
require __DIR__ . '/../includes/header.php';
?>

<h1>Settings</h1>

<?php if ($message): ?><div class="alert alert-<?= $messageType ?>"><?= htmlspecialchars($message) ?></div><?php endif; ?>

<h2 style="margin-top:24px">Profile</h2>
<form method="POST" style="max-width:400px">
    <input type="hidden" name="action" value="update_profile">
    <label style="margin-top:0">Name</label>
    <input type="text" name="name" value="<?= htmlspecialchars(Auth::name()) ?>" required>
    <button type="submit">Save</button>
</form>

<h2 style="margin-top:36px">Sender identity</h2>
<div class="alert" style="background:#ddf4ff;border-color:#54aeff;color:#0969da;">
    Every email still sends through our verified domain (needed for deliverability), but recipients will see <strong>your name</strong> as the sender, and replies go straight to <strong>your email</strong>, not ours.
</div>
<form method="POST" style="max-width:400px">
    <input type="hidden" name="action" value="update_sender">
    <label style="margin-top:0">Sender name <span style="color:var(--text-muted);font-weight:400">(shown to recipients)</span></label>
    <input type="text" name="sender_name" placeholder="<?= htmlspecialchars(Auth::name()) ?>" value="<?= htmlspecialchars($senderInfo['sender_name'] ?? '') ?>">
    <label>Reply-to email <span style="color:var(--text-muted);font-weight:400">(where replies land)</span></label>
    <input type="email" name="reply_to_email" placeholder="<?= htmlspecialchars($senderInfo['email']) ?>" value="<?= htmlspecialchars($senderInfo['reply_to_email'] ?? '') ?>">
    <button type="submit">Save sender identity</button>
</form>

<h2 style="margin-top:36px">Change password</h2>
<form method="POST" style="max-width:400px">
    <input type="hidden" name="action" value="change_password">
    <label style="margin-top:0">Current password</label>
    <input type="password" name="current_password" required>
    <label>New password</label>
    <input type="password" name="new_password" required minlength="8">
    <button type="submit">Update password</button>
</form>

<?php require __DIR__ . '/../includes/footer.php'; ?>
