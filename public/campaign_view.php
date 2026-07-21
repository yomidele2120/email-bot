<?php
require __DIR__ . '/../includes/bootstrap.php';

use App\Auth;
use App\Campaign;
use App\Contact;

Auth::requireLogin();
$userId = Auth::id();
$campaignId = (int)($_GET['id'] ?? 0);

$campaign = Campaign::findForUser($campaignId, $userId);
if (!$campaign) {
    header('Location: /campaigns.php');
    exit;
}

$message = '';

// Resend: re-queue the same subject/template to whichever contacts are selected
// (or, if none picked, everyone the original campaign reached). Creates a
// brand-new campaign, this one's history stays untouched.
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'resend') {
    $selectedIds = array_map('intval', $_POST['resend_contacts'] ?? []);

    if (empty($selectedIds)) {
        // Fall back to the original recipient list by looking up their contact IDs
        $pdo = \App\Database::connect();
        $stmt = $pdo->prepare("SELECT DISTINCT contact_id FROM campaign_queue WHERE campaign_id = :id");
        $stmt->execute([':id' => $campaignId]);
        $selectedIds = array_map('intval', array_column($stmt->fetchAll(), 'contact_id'));
    }

    if (!empty($selectedIds)) {
        $newId = Campaign::create($userId, $campaign['subject'], $campaign['template_html']);
        $count = Campaign::queueSelected($newId, $selectedIds);
        header("Location: /campaign_view.php?id=$newId&resent=$count");
        exit;
    }
}

$resentCount = isset($_GET['resent']) ? (int)$_GET['resent'] : null;
$recipients = Campaign::recipientsFor($campaignId);
$existingContacts = Contact::allActive($userId);

$pageTitle = $campaign['subject'];
$activeNav = 'campaigns';
require __DIR__ . '/../includes/header.php';
?>

<p><a href="/campaigns.php" style="color:var(--text-muted);font-size:13px">← Campaigns</a></p>

<?php if ($resentCount !== null): ?>
    <div class="alert alert-success">Resent as a new campaign, queued for <?= $resentCount ?> contact(s).</div>
<?php endif; ?>

<div style="display:flex;align-items:flex-start;justify-content:space-between;gap:20px;flex-wrap:wrap">
    <div>
        <h1><?= htmlspecialchars($campaign['subject']) ?></h1>
        <p style="color:var(--text-muted)">
            <?php if ($campaign['status'] === 'draft'): ?>
                <span class="badge badge-draft">Draft</span>
            <?php elseif ($campaign['status'] === 'queued' && $campaign['sent_count'] > 0): ?>
                <span class="badge badge-sending"><span class="dot"></span> Sending</span>
            <?php elseif ($campaign['status'] === 'completed'): ?>
                <span class="badge badge-completed">Delivered</span>
            <?php else: ?>
                <span class="badge badge-queued">Queued</span>
            <?php endif; ?>
            &nbsp;·&nbsp; Created <?= date('M j, Y g:ia', strtotime($campaign['created_at'])) ?>
        </p>
    </div>
    <div style="display:flex;gap:8px">
        <button type="button" class="btn btn-secondary" onclick="copyContent()">Copy content</button>
        <button type="button" class="btn" onclick="document.getElementById('resendPanel').classList.toggle('open')">Resend</button>
    </div>
</div>

<div class="stat-grid">
    <div class="stat-card"><div class="value mono"><?= $campaign['total_recipients'] ?></div><div class="label">Recipients</div></div>
    <div class="stat-card"><div class="value mono"><?= $campaign['sent_count'] ?></div><div class="label">Sent</div></div>
    <div class="stat-card"><div class="value mono" style="color:<?= $campaign['failed_count'] > 0 ? 'var(--danger)' : 'inherit' ?>"><?= $campaign['failed_count'] ?></div><div class="label">Failed</div></div>
</div>

<!-- Resend panel -->
<div id="resendPanel" class="tool-panel resend-panel" style="margin-bottom:24px">
    <h2 style="margin-top:0">Resend this message</h2>
    <p style="color:var(--text-muted);font-size:13px">Sends the exact same subject and content again, as a brand-new campaign. Pick who gets it, or leave everything unchecked to resend to the original list.</p>
    <form method="POST">
        <input type="hidden" name="action" value="resend">
        <?php if (!empty($existingContacts)): ?>
            <div class="contact-picker" style="margin-top:12px">
                <div class="contact-picker-header">
                    <label style="display:flex;align-items:center;gap:8px;margin:0;font-weight:500;font-size:13px">
                        <input type="checkbox" id="resendSelectAll" style="width:auto"> Select all (<?= count($existingContacts) ?>)
                    </label>
                </div>
                <div class="contact-picker-list">
                    <?php foreach ($existingContacts as $c): ?>
                        <label class="contact-picker-row">
                            <input type="checkbox" name="resend_contacts[]" value="<?= $c['id'] ?>" class="resend-checkbox" style="width:auto">
                            <span class="contact-picker-avatar"><?= strtoupper(substr($c['name'] ?: $c['email'], 0, 1)) ?></span>
                            <span style="font-size:13px"><?= htmlspecialchars($c['email']) ?></span>
                        </label>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>
        <button type="submit" style="margin-top:16px">Confirm resend</button>
    </form>
</div>

<!-- Content preview -->
<div class="tool-panel" style="padding:0;overflow:hidden;margin-bottom:24px">
    <div style="padding:8px 14px;background:var(--surface);border-bottom:1px solid var(--border);font-size:12px;color:var(--text-muted)">Message content</div>
    <iframe class="preview-frame" style="height:340px" srcdoc="<?= htmlspecialchars(str_replace(['{{name}}', '{{unsubscribe_link}}'], ['Jane', '#'], $campaign['template_html'])) ?>"></iframe>
</div>
<textarea id="rawContent" readonly rows="8" style="font-family:'JetBrains Mono',monospace;font-size:12px;margin-bottom:24px"><?= htmlspecialchars($campaign['template_html']) ?></textarea>

<!-- Recipient detail -->
<h2>Recipients</h2>
<table>
    <thead><tr><th>Email</th><th>Name</th><th>Status</th><th>Attempted</th><th>Detail</th></tr></thead>
    <tbody>
    <?php foreach ($recipients as $r): ?>
        <tr>
            <td><?= htmlspecialchars($r['email']) ?></td>
            <td><?= htmlspecialchars($r['name'] ?: '—') ?></td>
            <td>
                <?php if ($r['status'] === 'sent'): ?>
                    <span class="badge badge-completed">Sent</span>
                <?php elseif ($r['status'] === 'failed'): ?>
                    <span class="badge badge-danger">Failed</span>
                <?php else: ?>
                    <span class="badge badge-draft">Pending</span>
                <?php endif; ?>
            </td>
            <td style="color:var(--text-muted)"><?= $r['attempted_at'] ? date('M j, g:ia', strtotime($r['attempted_at'])) : '—' ?></td>
            <td style="color:var(--danger);font-size:12px"><?= htmlspecialchars($r['error_message'] ?? '') ?></td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>

<script>
function copyContent() {
    navigator.clipboard.writeText(document.getElementById('rawContent').value);
    event.target.textContent = 'Copied!';
}

const resendSelectAll = document.getElementById('resendSelectAll');
if (resendSelectAll) {
    resendSelectAll.addEventListener('change', () => {
        document.querySelectorAll('.resend-checkbox').forEach(cb => cb.checked = resendSelectAll.checked);
    });
}
</script>

<?php require __DIR__ . '/../includes/footer.php'; ?>
