<?php
// includes/public_header.php
// Used for tool pages when the visitor isn't logged in.
// Expects $pageTitle.
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="google-site-verification" content="ovfu6mw2R72pShX4rQxwSjcPnWBQ3nHPmCuqEdka7VU" />
    <title><?= isset($pageTitle) ? htmlspecialchars($pageTitle) . ' — ' : '' ?>Email Bot</title>
    <link rel="stylesheet" href="/assets/style.css">
    <?php if (\App\Ads::enabledForCurrentUser()): ?>
    <script async src="https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js?client=<?= htmlspecialchars(\App\Ads::clientId()) ?>" crossorigin="anonymous"></script>
    <?php endif; ?>
</head>
<body class="landing">

<header class="landing-nav">
    <a href="/" class="landing-logo">
        <svg width="26" height="26" viewBox="0 0 24 24" fill="none">
            <rect width="24" height="24" rx="6" fill="#C9A227"/>
            <path d="M5 8.5 12 13l7-4.5" stroke="#14120F" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/>
            <rect x="5" y="7" width="14" height="10" rx="1.6" stroke="#14120F" stroke-width="1.6" fill="none"/>
        </svg>
    </a>
    <?php require __DIR__ . '/tools_menu.php'; ?>
    <div class="landing-nav-actions">
        <a href="/login.php" class="btn-secondary btn">Sign in</a>
        <a href="/register.php" class="btn">Get started</a>
    </div>
</header>

<main class="public-tool-main">
