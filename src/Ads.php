<?php
namespace App;

class Ads
{
    public static function clientId(): string
    {
        return $_ENV['ADSENSE_CLIENT_ID'] ?? 'ca-pub-6552300898888939';
    }

    public static function toolSlotId(): string
    {
        return $_ENV['ADSENSE_SLOT_TOOL'] ?? '';
    }

    public static function enabledForCurrentUser(): bool
    {
        if (!self::clientId()) {
            return false;
        }

        if (!Auth::check()) {
            return true;
        }

        return Plan::config(PlanGate::currentPlan(Auth::id()))['ads'];
    }
}