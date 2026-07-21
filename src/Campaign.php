<?php
namespace App;

class Campaign
{
    public static function findForUser(int $campaignId, int $userId): ?array
    {
        $pdo = Database::connect();
        $stmt = $pdo->prepare(
            "SELECT c.*,
                (SELECT COUNT(*) FROM campaign_queue q WHERE q.campaign_id = c.id) AS total_recipients,
                (SELECT COUNT(*) FROM campaign_queue q WHERE q.campaign_id = c.id AND q.status = 'sent') AS sent_count,
                (SELECT COUNT(*) FROM campaign_queue q WHERE q.campaign_id = c.id AND q.status = 'failed') AS failed_count
             FROM campaigns c
             WHERE c.id = :id AND c.user_id = :uid"
        );
        $stmt->execute([':id' => $campaignId, ':uid' => $userId]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    /**
     * Every recipient row for a campaign, joined with contact info and per-send status.
     */
    public static function recipientsFor(int $campaignId): array
    {
        $pdo = Database::connect();
        $stmt = $pdo->prepare(
            "SELECT q.status, q.error_message, q.attempted_at, c.email, c.name
             FROM campaign_queue q
             JOIN contacts c ON c.id = q.contact_id
             WHERE q.campaign_id = :id
             ORDER BY q.id ASC"
        );
        $stmt->execute([':id' => $campaignId]);
        return $stmt->fetchAll();
    }

    public static function create(int $userId, string $subject, string $templateHtml, ?int $sequenceId = null): int
    {
        $pdo = Database::connect();
        $stmt = $pdo->prepare(
            "INSERT INTO campaigns (user_id, sequence_id, subject, template_html, status) VALUES (:user_id, :sequence_id, :subject, :html, 'draft')"
        );
        $stmt->execute([':user_id' => $userId, ':sequence_id' => $sequenceId, ':subject' => $subject, ':html' => $templateHtml]);
        return (int)$pdo->lastInsertId();
    }

    /**
     * Queue only the given contact IDs for this campaign, not the whole list.
     * A contact that was uploaded/used in a past campaign is just as eligible
     * here as a brand-new one, this only cares about the current selection.
     */
    public static function queueSelected(int $campaignId, array $contactIds): int
    {
        $pdo = Database::connect();
        $contactIds = array_unique(array_map('intval', $contactIds));
        if (empty($contactIds)) {
            return 0;
        }

        $stmt = $pdo->prepare(
            "INSERT IGNORE INTO campaign_queue (campaign_id, contact_id, status)
             VALUES (:campaign_id, :contact_id, 'pending')"
        );
        foreach ($contactIds as $contactId) {
            $stmt->execute([':campaign_id' => $campaignId, ':contact_id' => $contactId]);
        }

        $pdo->prepare("UPDATE campaigns SET status = 'queued' WHERE id = :id")
            ->execute([':id' => $campaignId]);

        return count($contactIds);
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
