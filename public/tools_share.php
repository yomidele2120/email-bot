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
<?php
$toolTitle = 'Share Files';
$toolDesc = 'Upload a file, get a link anyone can use to download it. Expires automatically after 48 hours. Max 20MB.';
require __DIR__ . '/../includes/tool_header.php';
?>
<?php if (!$loggedIn): ?>
    <p style="color:var(--text-muted);font-size:13px">Free to try <strong class="mono"><?= TrialGate::usesLeft('share') ?></strong> more time(s) without an account.</p>
<?php endif; ?>

<?php if ($message): ?>
    <div class="alert alert-<?= $messageType ?>"><?= htmlspecialchars($message) ?></div>
<?php endif; ?>

<?php if ($shareUrl): ?>
    <div class="result-panel">
        <div class="result-panel-icon">
            <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="#1a7f37" stroke-width="2"><path d="M20 6 9 17l-5-5"/></svg>
        </div>
        <div class="result-panel-info" style="min-width:0">
            <strong class="mono" style="overflow:hidden;text-overflow:ellipsis;white-space:nowrap;display:block"><?= htmlspecialchars($shareUrl) ?></strong>
            <span>Valid for 48 hours</span>
        </div>
        <button type="button" class="btn btn-secondary" style="margin:0" onclick="navigator.clipboard.writeText('<?= htmlspecialchars($shareUrl) ?>'); this.textContent='Copied!'">Copy link</button>
    </div>
<?php endif; ?>

<form method="POST" enctype="multipart/form-data">
    <label class="dropzone" for="shareInput" id="shareDropzone">
        <input type="file" name="upload" id="shareInput" required hidden>
        <div class="dropzone-icon">
            <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M22 2 11 13"/><path d="M22 2 15 22l-4-9-9-4z"/></svg>
        </div>
        <div class="dropzone-text">
            <strong id="shareLabel">Select a file to share</strong>
            <span>or drag and drop it here — up to 20MB</span>
        </div>
    </label>
    <button type="submit" class="btn" style="width:100%">Upload &amp; get link</button>
</form>

<script>
const dz2 = document.getElementById('shareDropzone');
const input2 = document.getElementById('shareInput');
const label2 = document.getElementById('shareLabel');

input2.addEventListener('change', () => {
    if (input2.files[0]) label2.textContent = input2.files[0].name;
});

['dragover', 'dragenter'].forEach(evt => dz2.addEventListener(evt, (e) => {
    e.preventDefault();
    dz2.classList.add('dropzone-active');
}));
['dragleave', 'drop'].forEach(evt => dz2.addEventListener(evt, (e) => {
    e.preventDefault();
    dz2.classList.remove('dropzone-active');
}));
dz2.addEventListener('drop', (e) => {
    if (e.dataTransfer.files[0]) {
        input2.files = e.dataTransfer.files;
        label2.textContent = e.dataTransfer.files[0].name;
    }
});
</script>

<?php require __DIR__ . '/../includes/' . ($loggedIn ? 'footer.php' : 'public_footer.php'); ?>
