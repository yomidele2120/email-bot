<?php
// includes/header.php
// Expects $activeNav to be set by the including page (e.g. 'dashboard', 'campaigns', 'contacts', 'new_campaign')
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
        <div class="brand">Email Bot<span>Campaign Manager</span></div>
        <a href="/dashboard.php" class="<?= ($activeNav ?? '') === 'dashboard' ? 'active' : '' ?>">Overview</a>
        <a href="/campaigns.php" class="<?= ($activeNav ?? '') === 'campaigns' ? 'active' : '' ?>">Campaigns</a>
        <a href="/campaign_create.php" class="<?= ($activeNav ?? '') === 'new_campaign' ? 'active' : '' ?>">New Campaign</a>
        <a href="/contacts.php" class="<?= ($activeNav ?? '') === 'contacts' ? 'active' : '' ?>">Contacts</a>
        <div class="user-row">
            Signed in as <strong><?= htmlspecialchars(\App\Auth::name() ?? '') ?></strong><br>
            <a href="/logout.php">Sign out</a>
        </div>
    </nav>
    <main class="main">
