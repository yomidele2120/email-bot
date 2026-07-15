<?php
namespace App;

use League\Csv\Reader;
use PDO;

class Contact
{
    /**
     * Import contacts from an uploaded CSV file.
     * Expected columns: email, name, plus any extra columns become custom_fields.
     * Returns [imported => int, skipped => int]
     */
    public static function importFromCsv(string $filePath): array
    {
        $pdo = Database::connect();
        $csv = Reader::createFromPath($filePath, 'r');
        $csv->setHeaderOffset(0);

        $imported = 0;
        $skipped = 0;

        $stmt = $pdo->prepare(
            "INSERT INTO contacts (email, name, custom_fields)
             VALUES (:email, :name, :custom_fields)
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
                ':email' => $email,
                ':name' => $name,
                ':custom_fields' => json_encode($customFields),
            ]);
            $imported++;
        }

        return ['imported' => $imported, 'skipped' => $skipped];
    }

    public static function allActive(): array
    {
        $pdo = Database::connect();
        $stmt = $pdo->query("SELECT * FROM contacts WHERE unsubscribed = 0");
        return $stmt->fetchAll();
    }

    public static function unsubscribe(int $contactId): void
    {
        $pdo = Database::connect();
        $stmt = $pdo->prepare("UPDATE contacts SET unsubscribed = 1 WHERE id = :id");
        $stmt->execute([':id' => $contactId]);
    }
}
