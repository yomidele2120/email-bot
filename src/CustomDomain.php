<?php
namespace App;

class CustomDomain
{
    /** The host apps should CNAME their custom domain to. */
    public static function targetHost(): string
    {
        $appUrl = $_ENV['APP_URL'] ?? '';
        return parse_url($appUrl, PHP_URL_HOST) ?: $appUrl;
    }

    public static function add(int $userId, string $domain): array
    {
        $domain = strtolower(trim($domain));
        $domain = preg_replace('#^https?://#', '', $domain);
        $domain = rtrim($domain, '/');

        if (!preg_match('/^[a-z0-9]([a-z0-9\-]{0,61}[a-z0-9])?(\.[a-z0-9]([a-z0-9\-]{0,61}[a-z0-9])?)+$/', $domain)) {
            return ['success' => false, 'error' => 'Enter a valid domain, e.g. links.yourbrand.com'];
        }

        $pdo = Database::connect();
        $stmt = $pdo->prepare("SELECT id FROM custom_domains WHERE domain = :d");
        $stmt->execute([':d' => $domain]);
        if ($stmt->fetch()) {
            return ['success' => false, 'error' => 'That domain is already registered.'];
        }

        $pdo->prepare("INSERT INTO custom_domains (user_id, domain) VALUES (:uid, :d)")
            ->execute([':uid' => $userId, ':d' => $domain]);

        return ['success' => true, 'id' => (int) $pdo->lastInsertId(), 'domain' => $domain];
    }

    /** Checks the domain's CNAME actually points at this app, and marks it verified if so. */
    public static function verify(int $userId, int $domainId): array
    {
        $pdo = Database::connect();
        $stmt = $pdo->prepare("SELECT * FROM custom_domains WHERE id = :id AND user_id = :uid");
        $stmt->execute([':id' => $domainId, ':uid' => $userId]);
        $row = $stmt->fetch();

        if (!$row) {
            return ['success' => false, 'error' => 'Domain not found.'];
        }

        $target = self::targetHost();
        $records = @dns_get_record($row['domain'], DNS_CNAME);
        $pointsHere = false;

        if ($records) {
            foreach ($records as $rec) {
                if (isset($rec['target']) && rtrim(strtolower($rec['target']), '.') === strtolower($target)) {
                    $pointsHere = true;
                    break;
                }
            }
        }

        if (!$pointsHere) {
            return ['success' => false, 'error' => "No CNAME pointing to $target found yet. DNS changes can take a while to propagate — try again shortly."];
        }

        $pdo->prepare("UPDATE custom_domains SET verified = 1, verified_at = NOW() WHERE id = :id")
            ->execute([':id' => $domainId]);

        return ['success' => true];
    }

    public static function allForUser(int $userId): array
    {
        $pdo = Database::connect();
        $stmt = $pdo->prepare("SELECT * FROM custom_domains WHERE user_id = :uid ORDER BY created_at DESC");
        $stmt->execute([':uid' => $userId]);
        return $stmt->fetchAll();
    }

    public static function setActive(int $userId, ?int $domainId): void
    {
        $pdo = Database::connect();
        if ($domainId !== null) {
            // Only allow activating a domain that's verified and actually owned by this user
            $stmt = $pdo->prepare("SELECT id FROM custom_domains WHERE id = :id AND user_id = :uid AND verified = 1");
            $stmt->execute([':id' => $domainId, ':uid' => $userId]);
            if (!$stmt->fetch()) {
                return;
            }
        }
        $pdo->prepare("UPDATE users SET active_domain_id = :domain_id WHERE id = :uid")
            ->execute([':domain_id' => $domainId, ':uid' => $userId]);
    }

    /** The domain to display for this user's short links, or null to fall back to the app's own domain. */
    public static function activeDomainFor(int $userId): ?string
    {
        $pdo = Database::connect();
        $stmt = $pdo->prepare(
            "SELECT cd.domain FROM users u
             JOIN custom_domains cd ON cd.id = u.active_domain_id AND cd.verified = 1
             WHERE u.id = :uid"
        );
        $stmt->execute([':uid' => $userId]);
        $domain = $stmt->fetchColumn();
        return $domain ?: null;
    }
}
