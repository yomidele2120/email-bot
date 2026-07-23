<?php
require __DIR__ . '/../includes/bootstrap.php';

use App\Auth;
use App\Database;

Auth::requireLogin();
$userId = Auth::id();
$pdo = Database::connect();

$message = '';

// Handle bulk delete of selected contacts
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['delete_ids'])) {
    $ids = array_map('intval', $_POST['delete_ids']);
    $placeholders = implode(',', array_fill(0, count($ids), '?'));
    $stmt = $pdo->prepare("DELETE FROM contacts WHERE user_id = ? AND id IN ($placeholders)");
    $stmt->execute(array_merge([$userId], $ids));
    $message = count($ids) . ' contact(s) removed.';
}

// Find case-insensitive duplicates (shouldn't normally exist due to the unique
// constraint, but catches variations like Jane@x.com vs jane@x.com)
$dupStmt = $pdo->prepare(
    "SELECT LOWER(email) AS norm_email, GROUP_CONCAT(id) AS ids, GROUP_CONCAT(email) AS emails, COUNT(*) AS cnt
     FROM contacts WHERE user_id = :uid GROUP BY LOWER(email) HAVING cnt > 1"
);
$dupStmt->execute([':uid' => $userId]);
$duplicates = $dupStmt->fetchAll();

// Find contacts with clearly malformed emails (defensive, in case old data predates validation)
$allStmt = $pdo->prepare("SELECT id, email, name FROM contacts WHERE user_id = :uid");
$allStmt->execute([':uid' => $userId]);
$invalid = array_filter($allStmt->fetchAll(), fn($c) => !filter_var($c['email'], FILTER_VALIDATE_EMAIL));

$pageTitle = 'Contact Cleanup';
$activeNav = 'tools_clean';
$allowAds = true;
require __DIR__ . '/../includes/header.php';
?>

<p><a href="/tools.php" style="color:var(--text-muted);font-size:13px">← Tools</a></p>
<h1>Contact Cleanup</h1>
<p style="color:var(--text-muted)">Duplicate and invalid entries found in your contact list.</p>

<?php if ($message): ?><div class="alert alert-success"><?= htmlspecialchars($message) ?></div><?php endif; ?>

<h2 style="margin-top:32px">Duplicates <span class="mono" style="color:var(--text-muted);font-size:14px">(<?= count($duplicates) ?>)</span></h2>

<?php if (empty($duplicates)): ?>
    <p style="color:var(--text-muted)">No duplicate emails found.</p>
<?php else: ?>
    <form method="POST">
        <table>
            <thead><tr><th></th><th>Email</th><th>Occurrences</th></tr></thead>
            <tbody>
            <?php foreach ($duplicates as $d):
                $ids = explode(',', $d['ids']);
                $keepId = array_shift($ids); // keep the first, offer to delete the rest
            ?>
                <?php foreach ($ids as $extraId): ?>
                    <tr>
                        <td><input type="checkbox" name="delete_ids[]" value="<?= $extraId ?>" checked></td>
                        <td><?= htmlspecialchars($d['norm_email']) ?></td>
                        <td style="color:var(--text-muted)">1 of <?= $d['cnt'] ?> duplicate copies</td>
                    </tr>
                <?php endforeach; ?>
            <?php endforeach; ?>
            </tbody>
        </table>
        <button type="submit">Remove selected duplicates</button>
    </form>
<?php endif; ?>

<h2 style="margin-top:36px">Invalid emails <span class="mono" style="color:var(--text-muted);font-size:14px">(<?= count($invalid) ?>)</span></h2>

<?php if (empty($invalid)): ?>
    <p style="color:var(--text-muted)">No malformed emails found.</p>
<?php else: ?>
    <form method="POST">
        <table>
            <thead><tr><th></th><th>Email</th><th>Name</th></tr></thead>
            <tbody>
            <?php foreach ($invalid as $c): ?>
                <tr>
                    <td><input type="checkbox" name="delete_ids[]" value="<?= $c['id'] ?>" checked></td>
                    <td><?= htmlspecialchars($c['email']) ?></td>
                    <td><?= htmlspecialchars($c['name'] ?: '—') ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        <button type="submit">Remove selected</button>
    </form>
<?php endif; ?>

<?php require __DIR__ . '/../includes/footer.php'; ?>
