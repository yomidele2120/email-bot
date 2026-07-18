<?php
require __DIR__ . '/../includes/bootstrap.php';

use App\Auth;
use App\ShortLink;

Auth::requireLogin();
$userId = Auth::id();

$message = '';
$messageType = 'success';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $url = trim($_POST['target_url'] ?? '');
    $result = ShortLink::create($userId, $url);

    if ($result['success']) {
        $shortUrl = rtrim($_ENV['APP_URL'] ?? '', '/') . '/r.php?s=' . $result['slug'];
        $message = "Short link created: $shortUrl";
    } else {
        $message = $result['error'];
        $messageType = 'error';
    }
}

$links = ShortLink::allForUser($userId);
$appUrl = rtrim($_ENV['APP_URL'] ?? '', '/');

$pageTitle = 'URL Shortener';
$activeNav = 'tools_shortener';
require __DIR__ . '/../includes/header.php';
?>

<p><a href="/tools.php" style="color:var(--text-muted);font-size:13px">← Tools</a></p>
<h1>URL Shortener</h1>
<p style="color:var(--text-muted)">Shorten links for campaigns, and see how many people click.</p>

<?php if ($message): ?>
    <div class="alert alert-<?= $messageType ?>"><?= htmlspecialchars($message) ?></div>
<?php endif; ?>

<form method="POST" style="max-width:480px;display:flex;gap:10px;align-items:flex-end">
    <div style="flex:1">
        <label style="margin-top:0">Long URL</label>
        <input type="text" name="target_url" placeholder="https://example.com/a-very-long-link" required>
    </div>
    <button type="submit" style="margin-top:0">Shorten</button>
</form>

<h2 style="margin-top:36px">Your links</h2>

<?php if (empty($links)): ?>
    <div class="empty-state">
        <h3>No short links yet</h3>
        <p>Create one above to get started.</p>
    </div>
<?php else: ?>
    <table>
        <thead><tr><th>Short link</th><th>Destination</th><th>Clicks</th><th>Created</th></tr></thead>
        <tbody>
        <?php foreach ($links as $link): ?>
            <tr>
                <td class="mono"><a href="/r.php?s=<?= htmlspecialchars($link['slug']) ?>" target="_blank"><?= htmlspecialchars($appUrl . '/r.php?s=' . $link['slug']) ?></a></td>
                <td style="max-width:280px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap"><?= htmlspecialchars($link['target_url']) ?></td>
                <td class="mono"><?= $link['clicks'] ?></td>
                <td style="color:var(--text-muted)"><?= date('M j, Y', strtotime($link['created_at'])) ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>

<?php require __DIR__ . '/../includes/footer.php'; ?>
