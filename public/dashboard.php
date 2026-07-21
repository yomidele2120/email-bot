<?php
require __DIR__ . '/../includes/bootstrap.php';

use App\Auth;
use App\Campaign;
use App\Contact;

Auth::requireLogin();

$userId = Auth::id();
$contactCount = Contact::countForUser($userId);
$campaignCount = Campaign::countForUser($userId);
$sentCount = Campaign::totalSentForUser($userId);
$recentCampaigns = array_slice(Campaign::allForUser($userId), 0, 5);

$pageTitle = 'Overview';
$activeNav = 'dashboard';
require __DIR__ . '/../includes/header.php';
?>

<h1>Overview</h1>
<p style="color:var(--text-muted)">Welcome back, <?= htmlspecialchars(Auth::name()) ?>.</p>

<div class="stat-grid">
    <div class="stat-card">
        <div class="value mono"><?= $contactCount ?></div>
        <div class="label">Contacts</div>
    </div>
    <div class="stat-card">
        <div class="value mono"><?= $campaignCount ?></div>
        <div class="label">Campaigns</div>
    </div>
    <div class="stat-card">
        <div class="value mono"><?= $sentCount ?></div>
        <div class="label">Emails sent</div>
    </div>
</div>

<h2>Recent campaigns</h2>

<?php if (empty($recentCampaigns)): ?>
    <div class="empty-state">
        <h3>No campaigns yet</h3>
        <p>Create your first campaign to start sending.</p>
        <a href="/campaign_create.php" class="btn">New Campaign</a>
    </div>
<?php else: ?>
    <table>
        <thead>
            <tr><th>Subject</th><th>Status</th><th>Progress</th><th>Created</th></tr>
        </thead>
        <tbody>
        <?php foreach ($recentCampaigns as $c): ?>
            <tr>
                <td><a href="/campaign_view.php?id=<?= $c['id'] ?>"><?= htmlspecialchars($c['subject']) ?></a></td>
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
                <td class="mono"><?= $c['sent_count'] ?>/<?= $c['total_recipients'] ?></td>
                <td style="color:var(--text-muted)"><?= date('M j, g:ia', strtotime($c['created_at'])) ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    <p style="margin-top:16px"><a href="/campaigns.php">View all campaigns →</a></p>
<?php endif; ?>

<?php require __DIR__ . '/../includes/footer.php'; ?>
