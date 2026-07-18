<?php
require __DIR__ . '/../includes/bootstrap.php';

use App\Auth;
use App\Database;
use App\TrialGate;

$loggedIn = Auth::check();
$message = '';
$messageType = 'error';
$shareUrl = '';
$showPaywall = false;
$appUrl = rtrim($_ENV['APP_URL'] ?? '', '/');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_FILES['upload']['tmp_name'])) {
    if (!TrialGate::check('share')) {
        $showPaywall = true;
    } elseif ($_FILES['upload']['size'] > 20 * 1024 * 1024) {
        $message = 'File is too large. Limit is 20MB.';
    } else {
        $dir = __DIR__ . '/uploads/shared';
        if (!is_dir($dir)) mkdir($dir, 0755, true);

        $token = bin2hex(random_bytes(10));
        $originalName = $_FILES['upload']['name'];
        $storedName = $token . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '_', $originalName);
        move_uploaded_file($_FILES['upload']['tmp_name'], $dir . '/' . $storedName);

        $pdo = Database::connect();
        $stmt = $pdo->prepare(
            "INSERT INTO shared_files (token, original_name, stored_name, size_bytes, expires_at)
             VALUES (:token, :name, :stored, :size, DATE_ADD(NOW(), INTERVAL 48 HOUR))"
        );
        $stmt->execute([
            ':token' => $token,
            ':name' => $originalName,
            ':stored' => $storedName,
            ':size' => $_FILES['upload']['size'],
        ]);

        $shareUrl = $appUrl . '/dl.php?t=' . $token;
        $message = 'Uploaded. Link is valid for 48 hours.';
        $messageType = 'success';
    }
}

$pageTitle = 'Share Files';
$activeNav = 'tools_share';
require __DIR__ . '/../includes/' . ($loggedIn ? 'header.php' : 'public_header.php');
?>

<?php if ($loggedIn): ?><p><a href="/tools.php" style="color:var(--text-muted);font-size:13px">← Tools</a></p><?php endif; ?>
<h1>Share Files</h1>
<p style="color:var(--text-muted)">Upload a file, get a link anyone can use to download it. Expires automatically after 48 hours. Max 20MB.</p>
<?php if (!$loggedIn): ?>
    <p style="color:var(--text-muted);font-size:13px">Free to try <strong class="mono"><?= TrialGate::usesLeft('share') ?></strong> more time(s) without an account.</p>
<?php endif; ?>

<?php if ($message): ?>
    <div class="alert alert-<?= $messageType ?>">
        <?= htmlspecialchars($message) ?>
        <?php if ($shareUrl): ?><br><a href="<?= htmlspecialchars($shareUrl) ?>" class="mono"><?= htmlspecialchars($shareUrl) ?></a><?php endif; ?>
    </div>
<?php endif; ?>

<form method="POST" enctype="multipart/form-data" style="max-width:440px">
    <label style="margin-top:0">File</label>
    <input type="file" name="upload" required>
    <button type="submit">Upload &amp; get link</button>
</form>

<?php require __DIR__ . '/../includes/' . ($loggedIn ? 'footer.php' : 'public_footer.php'); ?>
