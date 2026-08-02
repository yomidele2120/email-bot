<?php
require __DIR__ . '/../includes/bootstrap.php';

use App\Auth;
use App\Database;
use App\Paystack;

Auth::requireLogin();
$userId = Auth::id();

$reference = $_GET['reference'] ?? $_GET['trxref'] ?? '';

if (!$reference) {
    header('Location: /billing.php?flash=' . urlencode('Missing payment reference.') . '&type=error');
    exit;
}

$pdo = Database::connect();
$stmt = $pdo->prepare("SELECT * FROM payments WHERE reference = :ref AND user_id = :uid");
$stmt->execute([':ref' => $reference, ':uid' => $userId]);
$payment = $stmt->fetch();

if (!$payment) {
    header('Location: /billing.php?flash=' . urlencode('Payment record not found.') . '&type=error');
    exit;
}

// Already processed (e.g. user refreshed the callback page) — don't double-grant.
if ($payment['status'] === 'success') {
    header('Location: /billing.php?flash=' . urlencode('You are already on this plan.') . '&type=success');
    exit;
}

$verification = Paystack::verifyTransaction($reference);
$verifiedOk = !empty($verification['status'])
    && ($verification['data']['status'] ?? '') === 'success'
    && (int) ($verification['data']['amount'] ?? 0) === (int) $payment['amount_kobo'];

if (!$verifiedOk) {
    $pdo->prepare("UPDATE payments SET status = 'failed', paystack_response = :resp WHERE id = :id")
        ->execute([':resp' => json_encode($verification), ':id' => $payment['id']]);

    header('Location: /billing.php?flash=' . urlencode('Payment could not be verified. If you were charged, contact support.') . '&type=error');
    exit;
}

// Grant 30 days of access, stacking on top of any remaining time on the same plan.
// This is for immediate UX only — the webhook (paystack_webhook.php) is the real
// source of truth for renewals, since Paystack charges those automatically on
// schedule without the user ever visiting this callback page again.
$stmt = $pdo->prepare("SELECT plan, plan_expires_at FROM users WHERE id = :id");
$stmt->execute([':id' => $userId]);
$user = $stmt->fetch();

$now = time();
$currentExpiry = $user['plan_expires_at'] ? strtotime($user['plan_expires_at']) : $now;
$baseTime = ($user['plan'] === $payment['plan'] && $currentExpiry > $now) ? $currentExpiry : $now;
$newExpiry = date('Y-m-d H:i:s', $baseTime + (30 * 86400));

$customerCode = $verification['data']['customer']['customer_code'] ?? null;

$pdo->prepare("UPDATE users SET plan = :plan, plan_expires_at = :exp, paystack_customer_code = COALESCE(:cc, paystack_customer_code) WHERE id = :id")
    ->execute([':plan' => $payment['plan'], ':exp' => $newExpiry, ':cc' => $customerCode, ':id' => $userId]);

$pdo->prepare("UPDATE payments SET status = 'success', paystack_response = :resp WHERE id = :id")
    ->execute([':resp' => json_encode($verification), ':id' => $payment['id']]);

header('Location: /billing.php?flash=' . urlencode('Payment successful — your subscription is active. It will renew automatically each month.') . '&type=success');
exit;
