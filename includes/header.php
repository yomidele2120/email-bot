<?php
// includes/header.php
// Expects $activeNav to be set by the including page
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($pageTitle) ? htmlspecialchars($pageTitle) . ' — ' : '' ?>Email Bot</title>
    <link rel="stylesheet" href="/assets/style.css">
</head>
<body>
<div class="app-shell">
    <nav class="sidebar">
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
        <a href="/contacts.php" class="<?= ($activeNav ?? '') === 'contacts' ? 'active' : '' ?>">Contacts</a>

        <div class="sidebar-section">Tools</div>
        <a href="/tools.php" class="<?= ($activeNav ?? '') === 'tools' ? 'active' : '' ?>">All Tools</a>
        <a href="/tools_qr.php" class="<?= ($activeNav ?? '') === 'tools_qr' ? 'active' : '' ?>">QR Generator</a>
        <a href="/tools_shortener.php" class="<?= ($activeNav ?? '') === 'tools_shortener' ? 'active' : '' ?>">URL Shortener</a>
        <a href="/tools_email_verify.php" class="<?= ($activeNav ?? '') === 'tools_verify' ? 'active' : '' ?>">Email Verifier</a>
        <a href="/tools_contacts_clean.php" class="<?= ($activeNav ?? '') === 'tools_clean' ? 'active' : '' ?>">Contact Cleanup</a>

        <div class="sidebar-section">Account</div>
        <a href="/settings.php" class="<?= ($activeNav ?? '') === 'settings' ? 'active' : '' ?>">Settings</a>

        <div class="user-row">
            Signed in as <strong><?= htmlspecialchars(\App\Auth::name() ?? '') ?></strong><br>
            <a href="/logout.php">Sign out</a>
        </div>
    </nav>
    <div class="content-col">
        <header class="topbar">
            <form action="/search.php" method="GET" class="topbar-search">
                <input type="text" name="q" placeholder="Search campaigns, contacts, tools...">
            </form>
            <div class="topbar-actions">
                <button type="button" class="icon-btn" title="Notifications" aria-label="Notifications">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 8a6 6 0 0 0-12 0c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/></svg>
                </button>
                <div class="topbar-avatar" title="<?= htmlspecialchars(\App\Auth::name() ?? '') ?>">
                    <?= strtoupper(substr(\App\Auth::name() ?? 'U', 0, 1)) ?>
                </div>
            </div>
        </header>
        <main class="main">
