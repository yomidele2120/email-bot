<?php
namespace App;

use PDO;

class Campaign
{
    public static function create(string $subject, string $templateHtml): int
    {
        $pdo = Database::connect();
        $stmt = $pdo->prepare(
            "INSERT INTO campaigns (subject, template_html, status) VALUES (:subject, :html, 'draft')"
        );
        $stmt->execute([':subject' => $subject, ':html' => $templateHtml]);
        return (int)$pdo->lastInsertId();
    }

    /**
     * Queue every active contact for this campaign.
     */
    public static function queueAll(int $campaignId): int
    {
        $pdo = Database::connect();
        $contacts = Contact::allActive();

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
}
