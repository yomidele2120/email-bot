<?php
require __DIR__ . '/../includes/bootstrap.php';

use App\Auth;
use App\CustomDomain;
use App\PlanGate;
use App\Plan;

Auth::requireLogin();
$userId = Auth::id();
$plan = PlanGate::currentPlan($userId);
$canUseCustomDomain = Plan::config($plan)['custom_domain'];

$message = '';
$messageType = 'success';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $canUseCustomDomain) {
    $action = $_POST['action'] ?? '';

    if ($action === 'add_domain') {
        $result = CustomDomain::add($userId, $_POST['domain'] ?? '');
        if ($result['success']) {
            $message = "Domain added. Point a CNAME record at " . CustomDomain::targetHost() . ", then verify it below.";
        } else {
            $message = $result['error'];
            $messageType = 'error';
        }
    }

    if ($action === 'verify_domain') {
        $result = CustomDomain::verify($userId, (int) ($_POST['domain_id'] ?? 0));
        $message = $result['success'] ? 'Domain verified! You can now make it active below.' : $result['error'];
        $messageType = $result['success'] ? 'success' : 'error';
    }

    if ($action === 'set_active') {
        $domainId = $_POST['domain_id'] === '' ? null : (int) $_POST['domain_id'];
        CustomDomain::setActive($userId, $domainId);
        $message = $domainId ? 'Active domain updated. New short links will use it.' : 'Reverted to the default domain.';
    }
}

$domains = CustomDomain::allForUser($userId);
$targetHost = CustomDomain::targetHost();

$pageTitle = 'Custom Domain';
$activeNav = 'domains';
require __DIR__ . '/../includes/header.php';
?>

<h1>Custom domain</h1>
<p style="color:var(--text-muted)">Use your own domain for short links instead of <?= htmlspecialchars($targetHost) ?>.</p>

<?php if ($message): ?>
    <div class="alert alert-<?= $messageType ?>"><?= htmlspecialchars($message) ?></div>
<?php endif; ?>

<?php if (!$canUseCustomDomain): ?>
    <div class="alert" style="background:#ddf4ff;border-color:#54aeff;color:#0969da;">
        Custom domains are available on the Growth and Agency plans. <a href="/billing.php">Upgrade to unlock this</a>.
    </div>
<?php else: ?>

    <form method="POST" style="max-width:420px;margin-top:20px">
        <input type="hidden" name="action" value="add_domain">
        <label style="margin-top:0">Domain</label>
        <input type="text" name="domain" placeholder="links.yourbrand.com" required>
        <button type="submit">Add domain</button>
    </form>

    <h2 style="margin-top:36px">Your domains</h2>
    <?php if (empty($domains)): ?>
        <div class="empty-state">
            <h3>No custom domains yet</h3>
            <p>Add one above to get started.</p>
        </div>
    <?php else: ?>
        <table>
            <thead><tr><th>Domain</th><th>Status</th><th>Active</th><th></th></tr></thead>
            <tbody>
            <?php foreach ($domains as $d): ?>
                <tr>
                    <td class="mono"><?= htmlspecialchars($d['domain']) ?></td>
                    <td>
                        <?php if ($d['verified']): ?>
                            <span class="badge badge-completed">Verified</span>
                        <?php else: ?>
                            <span class="badge">Pending — add a CNAME to <?= htmlspecialchars($targetHost) ?></span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php if ($d['verified']): ?>
                            <form method="POST" style="display:inline">
                                <input type="hidden" name="action" value="set_active">
                                <input type="hidden" name="domain_id" value="<?= $d['id'] ?>">
                                <button type="submit" class="btn btn-secondary" style="margin-top:0;padding:4px 10px;font-size:12px">Make active</button>
                            </form>
                        <?php else: ?>
                            <span style="color:var(--text-muted);font-size:12px">—</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php if (!$d['verified']): ?>
                            <form method="POST" style="display:inline">
                                <input type="hidden" name="action" value="verify_domain">
                                <input type="hidden" name="domain_id" value="<?= $d['id'] ?>">
                                <button type="submit" class="btn btn-secondary" style="margin-top:0;padding:4px 10px;font-size:12px">Verify now</button>
                            </form>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        <form method="POST" style="margin-top:12px">
            <input type="hidden" name="action" value="set_active">
            <input type="hidden" name="domain_id" value="">
            <button type="submit" class="btn btn-secondary" style="margin-top:0">Use default domain instead</button>
        </form>
    <?php endif; ?>

<?php endif; ?>

<?php require __DIR__ . '/../includes/footer.php'; ?>
