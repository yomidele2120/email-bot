<?php
namespace App;

class PlanGate
{
    /** Returns the logged-in user's effective plan key ('free' if none/lapsed). */
    public static function currentPlan(int $userId): string
    {
        $pdo = Database::connect();
        $stmt = $pdo->prepare("SELECT plan, plan_expires_at FROM users WHERE id = :id");
        $stmt->execute([':id' => $userId]);
        $row = $stmt->fetch();
        if (!$row) return 'free';
        return Plan::effectiveFor($row['plan'], $row['plan_expires_at']);
    }

    public static function limits(int $userId): array
    {
        return Plan::config(self::currentPlan($userId));
    }

    public static function isAdFree(int $userId): bool
    {
        return !self::limits($userId)['ads'];
    }

    public static function hasBranding(int $userId): bool
    {
        return self::limits($userId)['branding'];
    }

    // ---- Contacts ----

    public static function canAddContacts(int $userId, int $countToAdd): bool
    {
        $limit = self::limits($userId)['contacts'];
        if ($limit === -1) return true;
        $existing = Contact::countForUser($userId);
        return ($existing + $countToAdd) <= $limit;
    }

    public static function contactsRemaining(int $userId): int
    {
        $limit = self::limits($userId)['contacts'];
        if ($limit === -1) return -1;
        return max(0, $limit - Contact::countForUser($userId));
    }

    // ---- Sequences ----

    public static function canCreateSequence(int $userId): bool
    {
        $limit = self::limits($userId)['sequences'];
        if ($limit === -1) return true;
        $pdo = Database::connect();
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM sequences WHERE user_id = :id");
        $stmt->execute([':id' => $userId]);
        return ((int) $stmt->fetchColumn()) < $limit;
    }

    // ---- Monthly metered usage (emails sent, verifier checks) ----

    private static function period(): string
    {
        return date('Y-m');
    }

    private static function usageCount(int $userId, string $metric): int
    {
        $pdo = Database::connect();
        $stmt = $pdo->prepare(
            "SELECT count FROM usage_monthly WHERE user_id = :uid AND period = :period AND metric = :metric"
        );
        $stmt->execute([':uid' => $userId, ':period' => self::period(), ':metric' => $metric]);
        return (int) ($stmt->fetchColumn() ?: 0);
    }

    private static function incrementUsage(int $userId, string $metric, int $by): void
    {
        $pdo = Database::connect();
        $stmt = $pdo->prepare(
            "INSERT INTO usage_monthly (user_id, period, metric, count) VALUES (:uid, :period, :metric, :by)
             ON DUPLICATE KEY UPDATE count = count + VALUES(count)"
        );
        $stmt->execute([':uid' => $userId, ':period' => self::period(), ':metric' => $metric, ':by' => $by]);
    }

    public static function emailsRemainingThisMonth(int $userId): int
    {
        $limit = self::limits($userId)['emails_per_month'];
        if ($limit === -1) return -1;
        return max(0, $limit - self::usageCount($userId, 'emails_sent'));
    }

    public static function canSendEmails(int $userId, int $countToSend): bool
    {
        $limit = self::limits($userId)['emails_per_month'];
        if ($limit === -1) return true;
        return (self::usageCount($userId, 'emails_sent') + $countToSend) <= $limit;
    }

    public static function recordEmailsSent(int $userId, int $count): void
    {
        self::incrementUsage($userId, 'emails_sent', $count);
    }

    public static function verifierChecksRemaining(int $userId): int
    {
        $limit = self::limits($userId)['verifier_checks_per_month'];
        if ($limit === -1) return -1;
        return max(0, $limit - self::usageCount($userId, 'verifier_checks'));
    }

    public static function canRunVerifierChecks(int $userId, int $count): bool
    {
        $limit = self::limits($userId)['verifier_checks_per_month'];
        if ($limit === -1) return true;
        return (self::usageCount($userId, 'verifier_checks') + $count) <= $limit;
    }

    public static function recordVerifierChecks(int $userId, int $count): void
    {
        self::incrementUsage($userId, 'verifier_checks', $count);
    }
}
