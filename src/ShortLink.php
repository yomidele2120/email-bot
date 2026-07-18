<?php
namespace App;

class ShortLink
{
    public static function create(int $userId, string $targetUrl): array
    {
        if (!filter_var($targetUrl, FILTER_VALIDATE_URL)) {
            return ['success' => false, 'error' => 'Enter a valid URL, including https://'];
        }

        $pdo = Database::connect();
        $slug = self::generateUniqueSlug($pdo);

        $stmt = $pdo->prepare(
            "INSERT INTO short_links (user_id, slug, target_url) VALUES (:user_id, :slug, :url)"
        );
        $stmt->execute([':user_id' => $userId, ':slug' => $slug, ':url' => $targetUrl]);

        return ['success' => true, 'slug' => $slug];
    }

    private static function generateUniqueSlug(\PDO $pdo): string
    {
        do {
            $slug = substr(str_shuffle('abcdefghijkmnpqrstuvwxyz23456789'), 0, 6);
            $stmt = $pdo->prepare("SELECT id FROM short_links WHERE slug = :slug");
            $stmt->execute([':slug' => $slug]);
        } while ($stmt->fetch());

        return $slug;
    }

    public static function allForUser(int $userId): array
    {
        $pdo = Database::connect();
        $stmt = $pdo->prepare("SELECT * FROM short_links WHERE user_id = :user_id ORDER BY created_at DESC");
        $stmt->execute([':user_id' => $userId]);
        return $stmt->fetchAll();
    }

    public static function resolveAndTrack(string $slug): ?string
    {
        $pdo = Database::connect();
        $stmt = $pdo->prepare("SELECT id, target_url FROM short_links WHERE slug = :slug");
        $stmt->execute([':slug' => $slug]);
        $row = $stmt->fetch();

        if (!$row) {
            return null;
        }

        $pdo->prepare("UPDATE short_links SET clicks = clicks + 1 WHERE id = :id")
            ->execute([':id' => $row['id']]);

        return $row['target_url'];
    }
}
