<?php
require __DIR__ . '/../includes/bootstrap.php';

use App\Auth;
use App\Database;
use App\Plan;
use App\Paystack;

Auth::requireLogin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /billing.php');
    exit;
}

$userId = Auth::id();
$plan = $_POST['plan'] ?? '';

if (!in_array($plan, Plan::paidTiersOrdered(), true)) {
    header('Location: /billing.php?flash=' . urlencode('Unknown plan selected.') . '&type=error');
    exit;
}

if (!Paystack::isConfigured()) {
    header('Location: /billing.php?flash=' . urlencode("Payments aren't configured yet.") . '&type=error');
    exit;
}

$pdo = Database::connect();
$stmt = $pdo->prepare("SELECT email FROM users WHERE id = :id");
$stmt->execute([':id' => $userId]);
$userEmail = $stmt->fetchColumn();

$amountKobo = Plan::config($plan)['price_kobo'];
$reference = 'plan_' . $plan . '_' . $userId . '_' . bin2hex(random_bytes(6));

// Log the attempt as 'pending' before redirecting, so billing_callback.php has
// something to verify against even if Paystack's own webhook is delayed.
$pdo->prepare(
    "INSERT INTO payments (user_id, plan, reference, amount_kobo, currency, status) VALUES (:uid, :plan, :ref, :amt, 'NGN', 'pending')"
)->execute([':uid' => $userId, ':plan' => $plan, ':ref' => $reference, ':amt' => $amountKobo]);

$callbackUrl = rtrim($_ENV['APP_URL'] ?? '', '/') . '/billing_callback.php';

$result = Paystack::initializeTransaction($userEmail, $amountKobo, $reference, $callbackUrl, [
    'user_id' => $userId,
    'plan' => $plan,
]);

if (!empty($result['status']) && !empty($result['data']['authorization_url'])) {
    header('Location: ' . $result['data']['authorization_url']);
    exit;
}

$errorMsg = $result['message'] ?? 'Could not start payment. Please try again.';
header('Location: /billing.php?flash=' . urlencode($errorMsg) . '&type=error');
exit;
