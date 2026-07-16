<?php
// worker/send_queue.php
// Run this on a schedule (e.g. every 1 minute) via Railway's cron / a scheduled job.
// It sends one batch of pending emails, then exits. This keeps each run short
// and avoids hitting SendGrid rate limits or PHP timeouts.

require __DIR__ . '/../vendor/autoload.php';

use App\Database;
use App\Mailer;
use App\Template;

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->safeLoad();

$pdo = Database::connect();
$batchSize = (int)($_ENV['BATCH_SIZE'] ?? 25);

// Pull the next batch of pending sends, joined with contact + campaign data
$stmt = $pdo->prepare(
    "SELECT cq.id AS queue_id, cq.campaign_id, cq.contact_id,
            c.email, c.name, c.custom_fields,
            camp.subject, camp.template_html
     FROM campaign_queue cq
     JOIN contacts c ON c.id = cq.contact_id
     JOIN campaigns camp ON camp.id = cq.campaign_id
     WHERE cq.status = 'pending' AND c.unsubscribed = 0
     ORDER BY cq.id ASC
     LIMIT :limit"
);
$stmt->bindValue(':limit', $batchSize, PDO::PARAM_INT);
$stmt->execute();
$rows = $stmt->fetchAll();

if (empty($rows)) {
    echo "No pending emails.\n";
    exit(0);
}

foreach ($rows as $row) {
    // Generate (or reuse) an unsubscribe token for this contact
    $tokenStmt = $pdo->prepare("SELECT token FROM unsubscribe_tokens WHERE contact_id = :cid");
    $tokenStmt->execute([':cid' => $row['contact_id']]);
    $token = $tokenStmt->fetchColumn();

    if (!$token) {
        $token = bin2hex(random_bytes(16));
        $pdo->prepare("INSERT INTO unsubscribe_tokens (token, contact_id) VALUES (:t, :c)")
            ->execute([':t' => $token, ':c' => $row['contact_id']]);
    }

    $unsubscribeLink = rtrim($_ENV['APP_URL'], '/') . "/unsubscribe.php?token=$token";
    $html = Template::render($row['template_html'], $row, $unsubscribeLink);

    $result = Mailer::send($row['email'], $row['name'], $row['subject'], $html);

    if ($result['success']) {
        $pdo->prepare("UPDATE campaign_queue SET status='sent', attempted_at=NOW() WHERE id=:id")
            ->execute([':id' => $row['queue_id']]);
        echo "Sent to {$row['email']}\n";
    } else {
        $pdo->prepare("UPDATE campaign_queue SET status='failed', error_message=:err, attempted_at=NOW() WHERE id=:id")
            ->execute([':err' => $result['error'], ':id' => $row['queue_id']]);
        echo "Failed for {$row['email']}: {$result['error']}\n";
    }

    // Small delay to stay well under SendGrid's rate limits
    usleep(150000); // 150ms
}

// Mark campaign completed if nothing pending is left
$campaignId = $rows[0]['campaign_id'];
$remaining = $pdo->prepare("SELECT COUNT(*) FROM campaign_queue WHERE campaign_id=:id AND status='pending'");
$remaining->execute([':id' => $campaignId]);
if ((int)$remaining->fetchColumn() === 0) {
    $pdo->prepare("UPDATE campaigns SET status='completed' WHERE id=:id")->execute([':id' => $campaignId]);
}
