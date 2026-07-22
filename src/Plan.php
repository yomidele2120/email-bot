<?php
namespace App;

class Plan
{
    // Every limit is "per-user". -1 means unlimited.
    const TIERS = [
        'free' => [
            'label' => 'Free',
            'price_kobo' => 0,
            'contacts' => 100,
            'emails_per_month' => 200,
            'sequences' => 1,
            'verifier_checks_per_month' => 20,
            'branding' => true,   // watermark on QR codes / short links
            'custom_domain' => false,
            'ads' => true,        // AdSense shown to this tier
            'white_label' => false,
        ],
        'starter' => [
            'label' => 'Starter',
            'price_kobo' => 450000, // ₦4,500
            'contacts' => 1000,
            'emails_per_month' => 2500,
            'sequences' => -1,
            'verifier_checks_per_month' => 200,
            'branding' => false,
            'custom_domain' => false,
            'ads' => false,
            'white_label' => false,
        ],
        'growth' => [
            'label' => 'Growth',
            'price_kobo' => 1200000, // ₦12,000
            'contacts' => 10000,
            'emails_per_month' => 15000,
            'sequences' => -1,
            'verifier_checks_per_month' => 2000,
            'branding' => false,
            'custom_domain' => true,
            'ads' => false,
            'white_label' => false,
        ],
        'agency' => [
            'label' => 'Agency',
            'price_kobo' => 3500000, // ₦35,000
            'contacts' => -1,
            'emails_per_month' => -1,
            'sequences' => -1,
            'verifier_checks_per_month' => -1,
            'branding' => false,
            'custom_domain' => true,
            'ads' => false,
            'white_label' => true,
        ],
    ];

    public static function exists(string $plan): bool
    {
        return isset(self::TIERS[$plan]);
    }

    public static function config(string $plan): array
    {
        return self::TIERS[$plan] ?? self::TIERS['free'];
    }

    public static function label(string $plan): string
    {
        return self::config($plan)['label'];
    }

    public static function priceNaira(string $plan): int
    {
        return (int) round(self::config($plan)['price_kobo'] / 100);
    }

    public static function priceFormatted(string $plan): string
    {
        $naira = self::priceNaira($plan);
        return $naira === 0 ? 'Free' : '₦' . number_format($naira) . '/mo';
    }

    /** Ordered list of paid tiers, cheapest first, for pricing pages. */
    public static function paidTiersOrdered(): array
    {
        return ['starter', 'growth', 'agency'];
    }

    /**
     * The user's *effective* plan: falls back to 'free' if a paid plan lapsed
     * (plan_expires_at in the past).
     */
    public static function effectiveFor(?string $plan, ?string $expiresAt): string
    {
        if (!$plan || $plan === 'free') {
            return 'free';
        }
        if ($expiresAt && strtotime($expiresAt) < time()) {
            return 'free';
        }
        return self::exists($plan) ? $plan : 'free';
    }
}
