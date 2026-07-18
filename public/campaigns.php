<?php
require __DIR__ . '/../includes/bootstrap.php';

use App\Auth;
use App\Campaign;

Auth::requireLogin();

$campaigns = Campaign::allForUser(Auth::id());

$pageTitle = 'Campaigns';
$activeNav = 'campaigns';
require __DIR__ . '/../includes/header.php';
?>

<h1>Campaigns</h1>
<p style="color:var(--text-muted)">Every campaign you've created, and how it's going.</p>

<?php if (empty($campaigns)): ?>
    <div class="empty-state">
        <h3>No campaigns yet</h3>
        <p>Create your first campaign to start sending.</p>
        <a href="/campaign_create.php" class="btn">New Campaign</a>
    </div>
<?php else: ?>
    <table>
        <thead>
            <tr><th>Subject</th><th>Status</th><th>Sent</th><th>Failed</th><th>Recipients</th><th>Created</th></tr>
        </thead>
        <tbody>
        <?php foreach ($campaigns as $c): ?>
            <tr>
                <td><?= htmlspecialchars($c['subject']) ?></td>
                <td>
                    <?php if ($c['status'] === 'queued' && $c['sent_count'] > 0): ?>
                        <span class="badge badge-sending"><span class="dot"></span> Sending</span>
                    <?php elseif ($c['status'] === 'completed'): ?>
                        <span class="badge badge-completed">Completed</span>
                    <?php elseif ($c['status'] === 'queued'): ?>
                        <span class="badge badge-queued">Queued</span>
                    <?php else: ?>
                        <span class="badge badge-draft">Draft</span>
                    <?php endif; ?>
                </td>
                <td class="mono"><?= $c['sent_count'] ?></td>
                <td class="mono" style="color:<?= $c['failed_count'] > 0 ? 'var(--danger)' : 'inherit' ?>"><?= $c['failed_count'] ?></td>
                <td class="mono"><?= $c['total_recipients'] ?></td>
                <td style="color:var(--text-muted)"><?= date('M j, Y g:ia', strtotime($c['created_at'])) ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>

<?php require __DIR__ . '/../includes/footer.php'; ?>
