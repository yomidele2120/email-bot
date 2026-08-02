<?php
// public/paystack_webhook.php
// Paystack calls this URL directly (not the user's browser) whenever a
// subscription event happens: the first charge, every monthly renewal
// charge, cancellations, and failed renewal attempts. This is the real
// source of truth for subscription state — billing_callback.php only
// handles the immediate "just paid" UX for the first charge.
//
// Configure this URL in your Paystack Dashboard under Settings > API Keys
// & Webhooks: https://www.reachkit.site/paystack_webhook.php

require __DIR__ . '/../includes/bootstrap.php';

use App\Database;
use App\Paystack;
use App\Plan;

$rawBody = file_get_contents('php://input');
$signature = $_SERVER['HTTP_X_PAYSTACK_SIGNATURE'] ?? null;

if (!Paystack::verifyWebhookSignature($rawBody, $signature)) {
    http_response_code(401);
    exit('Invalid signature');
}

$payload = json_decode($rawBody, true);
$event = $payload['event'] ?? '';
$data = $payload['data'] ?? [];

$pdo = Database::connect();

/** Records that we've already handled this exact event, so Paystack's retries don't double-process it. */
function alreadyProcessed(\PDO $pdo, string $dedupeKey): bool
{
    $stmt = $pdo->prepare("SELECT id FROM paystack_webhook_events WHERE dedupe_key = :key");
    $stmt->execute([':key' => $dedupeKey]);
    return (bool) $stmt->fetch();
}

function markProcessed(\PDO $pdo, string $dedupeKey, string $eventType): void
{
    try {
        $pdo->prepare("INSERT INTO paystack_webhook_events (dedupe_key, event_type) VALUES (:key, :type)")
            ->execute([':key' => $dedupeKey, ':type' => $eventType]);
    } catch (\Throwable $e) {
        // Unique constraint hit = a race with another delivery of the same event. Fine, ignore.
    }
}

function findUserByEmail(\PDO $pdo, string $email): ?array
{
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = :email");
    $stmt->execute([':email' => $email]);
    $row = $stmt->fetch();
    return $row ?: null;
}

switch ($event) {

    // First charge AND every subsequent monthly renewal charge land here.
    case 'charge.success': {
        $reference = $data['reference'] ?? '';
        $dedupeKey = "charge.success:$reference";
        if (!$reference || alreadyProcessed($pdo, $dedupeKey)) break;

        $planCode = $data['plan'] ?? ($data['plan_object']['plan_code'] ?? null);
        $email = $data['customer']['email'] ?? null;
        if (!$planCode || !$email) break; // not a subscription charge, ignore

        $tier = Plan::tierForPaystackPlanCode($planCode);
        $user = findUserByEmail($pdo, $email);
        if (!$tier || !$user) break;

        $now = time();
        $currentExpiry = $user['plan_expires_at'] ? strtotime($user['plan_expires_at']) : $now;
        // Renewals extend from the current expiry if still active, otherwise from now.
        $baseTime = ($user['plan'] === $tier && $currentExpiry > $now) ? $currentExpiry : $now;
        $newExpiry = date('Y-m-d H:i:s', $baseTime + (30 * 86400));

        $pdo->prepare("UPDATE users SET plan = :plan, plan_expires_at = :exp WHERE id = :id")
            ->execute([':plan' => $tier, ':exp' => $newExpiry, ':id' => $user['id']]);

        // Log it in payments too so renewal charges show in the same history as the first charge.
        $exists = $pdo->prepare("SELECT id FROM payments WHERE reference = :ref");
        $exists->execute([':ref' => $reference]);
        if (!$exists->fetch()) {
            $pdo->prepare(
                "INSERT INTO payments (user_id, plan, reference, amount_kobo, currency, status, paystack_response)
                 VALUES (:uid, :plan, :ref, :amt, 'NGN', 'success', :resp)"
            )->execute([
                ':uid' => $user['id'],
                ':plan' => $tier,
                ':ref' => $reference,
                ':amt' => (int) ($data['amount'] ?? 0),
                ':resp' => json_encode($data),
            ]);
        }

        markProcessed($pdo, $dedupeKey, $event);
        break;
    }

    // Fires shortly after the first successful charge on a plan — this is where
    // we get the subscription_code + email_token needed to let the user cancel later.
    case 'subscription.create': {
        $subCode = $data['subscription_code'] ?? '';
        $dedupeKey = "subscription.create:$subCode";
        if (!$subCode || alreadyProcessed($pdo, $dedupeKey)) break;

        $email = $data['customer']['email'] ?? null;
        $emailToken = $data['email_token'] ?? null;
        if (!$email) break;

        $user = findUserByEmail($pdo, $email);
        if (!$user) break;

        $pdo->prepare("UPDATE users SET paystack_subscription_code = :sub, paystack_email_token = :token WHERE id = :id")
            ->execute([':sub' => $subCode, ':token' => $emailToken, ':id' => $user['id']]);

        markProcessed($pdo, $dedupeKey, $event);
        break;
    }

    // User (or Paystack, after repeated failed renewal attempts) cancelled the
    // subscription. We don't yank access immediately — they keep whatever time
    // they already paid for; plan_expires_at lapsing naturally handles the
    // downgrade to free via Plan::effectiveFor().
    case 'subscription.disable':
    case 'subscription.not_renew': {
        $subCode = $data['subscription_code'] ?? '';
        $dedupeKey = "$event:$subCode";
        if (!$subCode || alreadyProcessed($pdo, $dedupeKey)) break;

        $pdo->prepare("UPDATE users SET paystack_subscription_code = NULL, paystack_email_token = NULL WHERE paystack_subscription_code = :sub")
            ->execute([':sub' => $subCode]);

        markProcessed($pdo, $dedupeKey, $event);
        break;
    }

    default:
        // Other events (invoice.create, invoice.update, etc.) — nothing to do yet.
        break;
}

http_response_code(200);
echo 'OK';
