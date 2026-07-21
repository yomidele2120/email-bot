<?php
require __DIR__ . '/../includes/bootstrap.php';

use App\Auth;
use App\Sequence;

Auth::requireLogin();
$userId = Auth::id();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $sequenceId = (int)($_POST['sequence_id'] ?? 0);
    $action = $_POST['action'] ?? '';
    if (in_array($action, ['paused', 'active', 'stopped'], true)) {
        Sequence::setStatus($sequenceId, $userId, $action);
    }
    header('Location: /sequences.php');
    exit;
}

$sequences = Sequence::allForUser($userId);

$pageTitle = 'Sequences';
$activeNav = 'sequences';
require __DIR__ . '/../includes/header.php';
?>

<div style="display:flex;align-items:center;justify-content:space-between">
    <div>
        <h1>Sequences</h1>
        <p style="color:var(--text-muted)">Rotate different messages on a repeating schedule, so contacts don't get the same email twice in a row.</p>
    </div>
    <a href="/sequence_create.php" class="btn">New sequence</a>
</div>

<?php if (empty($sequences)): ?>
    <div class="empty-state" style="margin-top:20px">
        <h3>No sequences yet</h3>
        <p>Create one to start rotating messages automatically.</p>
        <a href="/sequence_create.php" class="btn">New sequence</a>
    </div>
<?php else: ?>
    <table style="margin-top:20px">
        <thead>
            <tr><th>Name</th><th>Status</th><th>Messages</th><th>Sent so far</th><th>Repeats every</th><th>Next run</th><th></th></tr>
        </thead>
        <tbody>
        <?php foreach ($sequences as $seq): ?>
            <tr>
                <td><?= htmlspecialchars($seq['name']) ?></td>
                <td>
                    <?php if ($seq['status'] === 'active'): ?>
                        <span class="badge badge-sending"><span class="dot"></span> Active</span>
                    <?php elseif ($seq['status'] === 'paused'): ?>
                        <span class="badge badge-queued">Paused</span>
                    <?php else: ?>
                        <span class="badge badge-draft">Stopped</span>
                    <?php endif; ?>
                </td>
                <td class="mono"><?= $seq['step_count'] ?></td>
                <td class="mono"><?= $seq['runs_so_far'] ?></td>
                <td><?= $seq['interval_days'] == 1 ? 'Day' : $seq['interval_days'] . ' days' ?></td>
                <td style="color:var(--text-muted)"><?= $seq['status'] === 'stopped' ? '—' : date('M j, Y', strtotime($seq['next_run_date'])) ?></td>
                <td>
                    <form method="POST" style="display:inline">
                        <input type="hidden" name="sequence_id" value="<?= $seq['id'] ?>">
                        <?php if ($seq['status'] === 'active'): ?>
                            <button type="submit" name="action" value="paused" class="btn-secondary btn" style="margin:0;padding:5px 12px;font-size:12px">Pause</button>
                        <?php elseif ($seq['status'] === 'paused'): ?>
                            <button type="submit" name="action" value="active" class="btn-secondary btn" style="margin:0;padding:5px 12px;font-size:12px">Resume</button>
                        <?php endif; ?>
                        <?php if ($seq['status'] !== 'stopped'): ?>
                            <button type="submit" name="action" value="stopped" class="btn-secondary btn" style="margin:0;padding:5px 12px;font-size:12px;color:var(--danger)" onclick="return confirm('Stop this sequence for good? This can\'t be undone.')">Stop</button>
                        <?php endif; ?>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>

<?php require __DIR__ . '/../includes/footer.php'; ?>
