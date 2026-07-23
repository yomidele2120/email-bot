<?php
// includes/header.php
// Expects $activeNav to be set by the including page
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="google-site-verification" content="ovfu6mw2R72pShX4rQxwSjcPnWBQ3nHPmCuqEdka7VU" />
    <title><?= isset($pageTitle) ? htmlspecialchars($pageTitle) . ' — ' : '' ?>Email Bot</title>
    <link rel="stylesheet" href="/assets/style.css">
    <?php if (!empty($allowAds) && \App\Ads::enabledForCurrentUser()): ?>
    <script async src="https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js?client=<?= htmlspecialchars(\App\Ads::clientId()) ?>" crossorigin="anonymous"></script>
    <?php endif; ?>
</head>
<body>
<div class="app-shell">
    <div class="sidebar-overlay" id="sidebarOverlay"></div>
    <nav class="sidebar" id="sidebar">
        <button type="button" class="sidebar-close" id="sidebarClose" aria-label="Close menu">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
        </button>
        <a href="/dashboard.php" class="brand-mark" title="Email Bot">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none">
                <rect width="24" height="24" rx="6" fill="#C9A227"/>
                <path d="M5 8.5 12 13l7-4.5" stroke="#14120F" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/>
                <rect x="5" y="7" width="14" height="10" rx="1.6" stroke="#14120F" stroke-width="1.6" fill="none"/>
            </svg>
        </a>
        <a href="/dashboard.php" class="<?= ($activeNav ?? '') === 'dashboard' ? 'active' : '' ?>">Overview</a>
        <a href="/campaigns.php" class="<?= ($activeNav ?? '') === 'campaigns' ? 'active' : '' ?>">Campaigns</a>
        <a href="/campaign_create.php" class="<?= ($activeNav ?? '') === 'new_campaign' ? 'active' : '' ?>">New Campaign</a>
        <a href="/sequences.php" class="<?= ($activeNav ?? '') === 'sequences' ? 'active' : '' ?>">Sequences</a>
        <a href="/contacts.php" class="<?= ($activeNav ?? '') === 'contacts' ? 'active' : '' ?>">Contacts</a>

        <div class="sidebar-section">Tools</div>
        <a href="/tools.php" class="<?= ($activeNav ?? '') === 'tools' ? 'active' : '' ?>">All Tools</a>
        <a href="/tools_qr.php" class="<?= ($activeNav ?? '') === 'tools_qr' ? 'active' : '' ?>">QR Generator</a>
        <a href="/tools_shortener.php" class="<?= ($activeNav ?? '') === 'tools_shortener' ? 'active' : '' ?>">URL Shortener</a>
        <a href="/domains.php" class="<?= ($activeNav ?? '') === 'domains' ? 'active' : '' ?>">Custom Domain</a>
        <a href="/tools_email_verify.php" class="<?= ($activeNav ?? '') === 'tools_verify' ? 'active' : '' ?>">Email Verifier</a>
        <a href="/tools_contacts_clean.php" class="<?= ($activeNav ?? '') === 'tools_clean' ? 'active' : '' ?>">Contact Cleanup</a>

        <div class="sidebar-section">Account</div>
        <a href="/settings.php" class="<?= ($activeNav ?? '') === 'settings' ? 'active' : '' ?>">Settings</a>
        <a href="/billing.php" class="<?= ($activeNav ?? '') === 'billing' ? 'active' : '' ?>">Billing</a>

        <div class="user-row">
            Signed in as <strong><?= htmlspecialchars(\App\Auth::name() ?? '') ?></strong><br>
            <span style="color:var(--text-muted)">Plan: <?= htmlspecialchars(\App\Plan::label(\App\PlanGate::currentPlan(\App\Auth::id()))) ?></span> · <a href="/billing.php">Upgrade</a><br>
            <a href="/logout.php">Sign out</a>
        </div>
    </nav>
    <div class="content-col">
        <header class="topbar">
            <button type="button" class="icon-btn menu-trigger" id="menuTrigger" title="Menu" aria-label="Open menu" aria-expanded="false" aria-controls="sidebar">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><line x1="4" y1="7" x2="20" y2="7"/><line x1="4" y1="12" x2="20" y2="12"/><line x1="4" y1="17" x2="20" y2="17"/></svg>
            </button>
            <form action="/search.php" method="GET" class="topbar-search">
                <input type="text" name="q" placeholder="Search campaigns, contacts, tools...">
            </form>
            <div class="topbar-actions">
                <button type="button" class="icon-btn" title="Notifications" aria-label="Notifications">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 8a6 6 0 0 0-12 0c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/></svg>
                </button>
                <div class="account-dropdown" id="accountDropdown">
                    <button type="button" class="topbar-avatar" id="accountTrigger" title="<?= htmlspecialchars(\App\Auth::name() ?? '') ?>">
                        <?= strtoupper(substr(\App\Auth::name() ?? 'U', 0, 1)) ?>
                    </button>
                    <div class="account-dropdown-panel">
                        <div class="account-dropdown-header">
                            <div class="topbar-avatar" style="margin:0">
                                <?= strtoupper(substr(\App\Auth::name() ?? 'U', 0, 1)) ?>
                            </div>
                            <strong><?= htmlspecialchars(\App\Auth::name() ?? '') ?></strong>
                        </div>
                        <div class="account-dropdown-divider"></div>
                        <a href="/dashboard.php" class="account-dropdown-item">
                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="9"/><rect x="14" y="3" width="7" height="5"/><rect x="14" y="12" width="7" height="9"/><rect x="3" y="16" width="7" height="5"/></svg>
                            Overview
                        </a>
                        <a href="/campaigns.php" class="account-dropdown-item">
                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="5" width="18" height="14" rx="2"/><path d="m3 7 9 6 9-6"/></svg>
                            Campaigns
                        </a>
                        <a href="/contacts.php" class="account-dropdown-item">
                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="8" r="4"/><path d="M6 21v-2a4 4 0 0 1 4-4h4a4 4 0 0 1 4 4v2"/></svg>
                            Contacts
                        </a>
                        <a href="/tools.php" class="account-dropdown-item">
                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/></svg>
                            Tools
                        </a>
                        <div class="account-dropdown-divider"></div>
                        <a href="/billing.php" class="account-dropdown-item">
                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="5" width="20" height="14" rx="2"/><line x1="2" y1="10" x2="22" y2="10"/></svg>
                            Billing
                        </a>
                        <a href="/settings.php" class="account-dropdown-item">
                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 1 1-2.83 2.83l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-4 0v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 1 1-2.83-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1 0-4h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 1 1 2.83-2.83l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 4 0v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 1 1 2.83 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 0 4h-.09a1.65 1.65 0 0 0-1.51 1z"/></svg>
                            Settings
                        </a>
                        <div class="account-dropdown-divider"></div>
                        <a href="/logout.php" class="account-dropdown-item account-dropdown-danger">
                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><path d="m16 17 5-5-5-5"/><path d="M21 12H9"/></svg>
                            Sign out
                        </a>
                    </div>
                </div>
            </div>
        </header>
        <main class="main">
