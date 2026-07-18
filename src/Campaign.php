<?php
namespace App;

class Campaign
{
    public static function create(int $userId, string $subject, string $templateHtml): int
    {
        $pdo = Database::connect();
        $stmt = $pdo->prepare(
            "INSERT INTO campaigns (user_id, subject, template_html, status) VALUES (:user_id, :subject, :html, 'draft')"
        );
        $stmt->execute([':user_id' => $userId, ':subject' => $subject, ':html' => $templateHtml]);
        return (int)$pdo->lastInsertId();
    }

    public static function queueAll(int $campaignId, int $userId): int
    {
        $pdo = Database::connect();
        $contacts = Contact::allActive($userId);

        $stmt = $pdo->prepare(
            "INSERT IGNORE INTO campaign_queue (campaign_id, contact_id, status)
             VALUES (:campaign_id, :contact_id, 'pending')"
        );

        foreach ($contacts as $contact) {
            $stmt->execute([':campaign_id' => $campaignId, ':contact_id' => $contact['id']]);
        }

        $pdo->prepare("UPDATE campaigns SET status = 'queued' WHERE id = :id")
            ->execute([':id' => $campaignId]);

        return count($contacts);
    }

    public static function allForUser(int $userId): array
    {
        $pdo = Database::connect();
        $stmt = $pdo->prepare(
            "SELECT c.*,
                (SELECT COUNT(*) FROM campaign_queue q WHERE q.campaign_id = c.id) AS total_recipients,
                (SELECT COUNT(*) FROM campaign_queue q WHERE q.campaign_id = c.id AND q.status = 'sent') AS sent_count,
                (SELECT COUNT(*) FROM campaign_queue q WHERE q.campaign_id = c.id AND q.status = 'failed') AS failed_count
             FROM campaigns c
             WHERE c.user_id = :user_id
             ORDER BY c.created_at DESC"
        );
        $stmt->execute([':user_id' => $userId]);
        return $stmt->fetchAll();
    }

    public static function countForUser(int $userId): int
    {
        $pdo = Database::connect();
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM campaigns WHERE user_id = :user_id");
        $stmt->execute([':user_id' => $userId]);
        return (int)$stmt->fetchColumn();
    }

    public static function totalSentForUser(int $userId): int
    {
        $pdo = Database::connect();
        $stmt = $pdo->prepare(
            "SELECT COUNT(*) FROM campaign_queue q
             JOIN campaigns c ON c.id = q.campaign_id
             WHERE c.user_id = :user_id AND q.status = 'sent'"
        );
        $stmt->execute([':user_id' => $userId]);
        return (int)$stmt->fetchColumn();
    }
}
