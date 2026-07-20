<?php
namespace App;

use League\Csv\Reader;

class Contact
{
    public static function importFromCsv(string $filePath, int $userId): array
    {
        $pdo = Database::connect();
        $csv = Reader::createFromPath($filePath, 'r');
        $csv->setHeaderOffset(0);

        $imported = 0;
        $skipped = 0;
        $importedEmails = [];

        $stmt = $pdo->prepare(
            "INSERT INTO contacts (user_id, email, name, custom_fields)
             VALUES (:user_id, :email, :name, :custom_fields)
             ON DUPLICATE KEY UPDATE name = VALUES(name), custom_fields = VALUES(custom_fields)"
        );

        foreach ($csv->getRecords() as $record) {
            $email = trim($record['email'] ?? '');
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $skipped++;
                continue;
            }

            $name = trim($record['name'] ?? '');
            $customFields = $record;
            unset($customFields['email'], $customFields['name']);

            $stmt->execute([
                ':user_id' => $userId,
                ':email' => $email,
                ':name' => $name,
                ':custom_fields' => json_encode($customFields),
            ]);
            $imported++;
            $importedEmails[] = $email;
        }

        return ['imported' => $imported, 'skipped' => $skipped, 'emails' => $importedEmails];
    }

    /**
     * Look up contact IDs for this user by email, used right after a CSV import
     * to find out which contact_id each imported row landed on (new or existing).
     */
    public static function idsForEmails(int $userId, array $emails): array
    {
        if (empty($emails)) {
            return [];
        }
        $pdo = Database::connect();
        $placeholders = implode(',', array_fill(0, count($emails), '?'));
        $stmt = $pdo->prepare("SELECT id FROM contacts WHERE user_id = ? AND email IN ($placeholders)");
        $stmt->execute(array_merge([$userId], $emails));
        return array_map('intval', array_column($stmt->fetchAll(), 'id'));
    }

    public static function allActive(int $userId): array
    {
        $pdo = Database::connect();
        $stmt = $pdo->prepare("SELECT * FROM contacts WHERE user_id = :user_id AND unsubscribed = 0");
        $stmt->execute([':user_id' => $userId]);
        return $stmt->fetchAll();
    }

    public static function allForUser(int $userId): array
    {
        $pdo = Database::connect();
        $stmt = $pdo->prepare("SELECT * FROM contacts WHERE user_id = :user_id ORDER BY created_at DESC");
        $stmt->execute([':user_id' => $userId]);
        return $stmt->fetchAll();
    }

    public static function countForUser(int $userId): int
    {
        $pdo = Database::connect();
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM contacts WHERE user_id = :user_id");
        $stmt->execute([':user_id' => $userId]);
        return (int)$stmt->fetchColumn();
    }

    public static function unsubscribe(int $contactId): void
    {
        $pdo = Database::connect();
        $stmt = $pdo->prepare("UPDATE contacts SET unsubscribed = 1 WHERE id = :id");
        $stmt->execute([':id' => $contactId]);
    }
}
