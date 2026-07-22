<?php
require __DIR__ . '/../includes/bootstrap.php';

use App\Auth;
use App\Plan;
use App\PlanGate;
use App\Paystack;

Auth::requireLogin();
$userId = Auth::id();
$currentPlan = PlanGate::currentPlan($userId);

$flash = $_GET['flash'] ?? '';
$flashType = $_GET['type'] ?? 'success';

$pageTitle = 'Billing';
$activeNav = 'billing';
require __DIR__ . '/../includes/header.php';
?>

<h1>Billing</h1>
<p style="color:var(--text-muted)">You're currently on the <strong><?= htmlspecialchars(Plan::label($currentPlan)) ?></strong> plan.</p>

<?php if ($flash): ?>
    <div class="alert alert-<?= htmlspecialchars($flashType) ?>"><?= htmlspecialchars($flash) ?></div>
<?php endif; ?>

<?php if (!Paystack::isConfigured()): ?>
    <div class="alert alert-error">Payments aren't configured yet — set <code>PAYSTACK_SECRET_KEY</code> in your environment to enable upgrades.</div>
<?php endif; ?>

<div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(220px, 1fr)); gap:16px; margin-top:24px; max-width:1000px;">
    <?php foreach (array_merge(['free'], Plan::paidTiersOrdered()) as $tierKey):
        $tier = Plan::config($tierKey);
        $isCurrent = $tierKey === $currentPlan;
    ?>
    <div class="tool-card" style="<?= $isCurrent ? 'border-color:var(--accent);' : '' ?>">
        <h3><?= htmlspecialchars($tier['label']) ?></h3>
        <p style="font-family:'Fraunces',serif;font-size:20px;color:var(--text);margin:6px 0 10px;"><?= Plan::priceFormatted($tierKey) ?></p>
        <ul style="margin:0 0 14px;padding-left:18px;font-size:13px;color:var(--text-muted);line-height:1.7;">
            <li><?= $tier['contacts'] === -1 ? 'Unlimited' : number_format($tier['contacts']) ?> contacts</li>
            <li><?= $tier['emails_per_month'] === -1 ? 'Unlimited' : number_format($tier['emails_per_month']) ?> emails/month</li>
            <li><?= $tier['sequences'] === -1 ? 'Unlimited' : $tier['sequences'] ?> active sequence<?= $tier['sequences'] === 1 ? '' : 's' ?></li>
            <li><?= $tier['verifier_checks_per_month'] === -1 ? 'Unlimited' : number_format($tier['verifier_checks_per_month']) ?> verifier checks/month</li>
            <?php if (!$tier['branding']): ?><li>No branding on QR codes / links</li><?php endif; ?>
            <?php if (!$tier['ads']): ?><li>No ads</li><?php endif; ?>
            <?php if ($tier['custom_domain']): ?><li>Custom short-link domain</li><?php endif; ?>
            <?php if ($tier['white_label']): ?><li>White-label / client sub-accounts</li><?php endif; ?>
        </ul>
        <?php if ($isCurrent): ?>
            <span class="soon-tag">Current plan</span>
        <?php elseif ($tierKey === 'free'): ?>
            <span style="font-size:12px;color:var(--text-muted)">Downgrade automatically if your paid plan lapses.</span>
        <?php else: ?>
            <form method="POST" action="/billing_initialize.php">
                <input type="hidden" name="plan" value="<?= htmlspecialchars($tierKey) ?>">
                <button type="submit" class="btn" style="width:100%;margin-top:0;" <?= Paystack::isConfigured() ? '' : 'disabled' ?>>Upgrade</button>
            </form>
        <?php endif; ?>
    </div>
    <?php endforeach; ?>
</div>

<p style="margin-top:24px;font-size:12px;color:var(--text-muted);max-width:600px;">
    Payments are processed securely by Paystack in Nigerian Naira. Plans renew monthly — upgrading again before your
    current period ends simply extends your access by another 30 days.
</p>

<?php require __DIR__ . '/../includes/footer.php'; ?>
