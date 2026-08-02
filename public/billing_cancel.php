<?php
require __DIR__ . '/../includes/bootstrap.php';

use App\Auth;
use App\Database;
use App\Paystack;

Auth::requireLogin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /billing.php');
    exit;
}

$userId = Auth::id();
$pdo = Database::connect();
$stmt = $pdo->prepare("SELECT paystack_subscription_code, paystack_email_token, plan_expires_at FROM users WHERE id = :id");
$stmt->execute([':id' => $userId]);
$user = $stmt->fetch();

if (!$user || !$user['paystack_subscription_code'] || !$user['paystack_email_token']) {
    header('Location: /billing.php?flash=' . urlencode('No active subscription found to cancel.') . '&type=error');
    exit;
}

$result = Paystack::disableSubscription($user['paystack_subscription_code'], $user['paystack_email_token']);

if (empty($result['status'])) {
    $msg = $result['message'] ?? 'Could not cancel the subscription. Please try again or contact support.';
    header('Location: /billing.php?flash=' . urlencode($msg) . '&type=error');
    exit;
}

// The subscription.disable webhook will clear the stored subscription code shortly.
// Access is intentionally left untouched here — the user keeps what they already paid for.
$expiryDate = $user['plan_expires_at'] ? date('F j, Y', strtotime($user['plan_expires_at'])) : 'the end of your billing period';

header('Location: /billing.php?flash=' . urlencode("Subscription cancelled. You'll keep your current plan's access until $expiryDate, then it'll move to Free automatically.") . '&type=success');
exit;
