<?php
namespace App;

class TrialGate
{
    const LIMIT = 3;

    /**
     * Call this when an anonymous user is about to actually USE a tool
     * (not just view the page). Logged-in users always pass.
     * Returns true if allowed (and increments the count), false if the limit is hit.
     */
    public static function check(string $toolKey): bool
    {
        if (Auth::check()) {
            return true;
        }

        if (!isset($_SESSION['trial'])) {
            $_SESSION['trial'] = [];
        }

        $count = $_SESSION['trial'][$toolKey] ?? 0;

        if ($count >= self::LIMIT) {
            return false;
        }

        $_SESSION['trial'][$toolKey] = $count + 1;
        return true;
    }

    public static function usesLeft(string $toolKey): int
    {
        if (Auth::check()) {
            return -1; // unlimited
        }
        $count = $_SESSION['trial'][$toolKey] ?? 0;
        return max(0, self::LIMIT - $count);
    }
}
