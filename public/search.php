<?php
require __DIR__ . '/../includes/bootstrap.php';

use App\Auth;
use App\Database;

Auth::requireLogin();
$userId = Auth::id();
$q = trim($_GET['q'] ?? '');

$campaigns = [];
$contacts = [];

if ($q !== '') {
    $pdo = Database::connect();

    $stmt = $pdo->prepare("SELECT id, subject, status FROM campaigns WHERE user_id = :uid AND subject LIKE :q LIMIT 20");
    $stmt->execute([':uid' => $userId, ':q' => "%$q%"]);
    $campaigns = $stmt->fetchAll();

    $stmt = $pdo->prepare("SELECT id, email, name FROM contacts WHERE user_id = :uid AND (email LIKE :q OR name LIKE :q) LIMIT 20");
    $stmt->execute([':uid' => $userId, ':q' => "%$q%"]);
    $contacts = $stmt->fetchAll();
}

$pageTitle = 'Search';
require __DIR__ . '/../includes/header.php';
?>

<h1>Search results for "<?= htmlspecialchars($q) ?>"</h1>

<h2 style="margin-top:24px">Campaigns</h2>
<?php if (empty($campaigns)): ?>
    <p style="color:var(--text-muted)">No matching campaigns.</p>
<?php else: ?>
    <table>
        <thead><tr><th>Subject</th><th>Status</th></tr></thead>
        <tbody>
        <?php foreach ($campaigns as $c): ?>
            <tr><td><?= htmlspecialchars($c['subject']) ?></td><td><?= htmlspecialchars($c['status']) ?></td></tr>
        <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>

<h2 style="margin-top:36px">Contacts</h2>
<?php if (empty($contacts)): ?>
    <p style="color:var(--text-muted)">No matching contacts.</p>
<?php else: ?>
    <table>
        <thead><tr><th>Email</th><th>Name</th></tr></thead>
        <tbody>
        <?php foreach ($contacts as $c): ?>
            <tr><td><?= htmlspecialchars($c['email']) ?></td><td><?= htmlspecialchars($c['name'] ?: '—') ?></td></tr>
        <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>

<?php require __DIR__ . '/../includes/footer.php'; ?>
