<?php
require __DIR__ . '/../includes/bootstrap.php';

use App\Auth;

$loggedIn = Auth::check();

$pageTitle = 'Tools';
$activeNav = 'tools';
require __DIR__ . '/../includes/' . ($loggedIn ? 'header.php' : 'public_header.php');
?>

<h1>Tools</h1>
<p style="color:var(--text-muted)">Everything alongside the email bot. Try most of these free, no account needed for your first 3 uses.</p>

<h2 style="margin-top:32px">Archive &amp; File Tools</h2>
<div class="tool-grid">
    <a href="/tools_archive.php?mode=zip" class="tool-card">
        <div class="icon"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/></svg></div>
        <h3>Extract / Convert ZIP</h3>
        <p>Unpack a ZIP or repackage it clean.</p>
    </a>
    <a href="/tools_archive.php?mode=tar" class="tool-card">
        <div class="icon"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="7" width="18" height="13" rx="2"/><path d="M8 7V5a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg></div>
        <h3>TAR to ZIP</h3>
        <p>Convert a .tar archive into a ZIP.</p>
    </a>
    <a href="/tools_archive.php?mode=targz" class="tool-card">
        <div class="icon"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="7" width="18" height="13" rx="2"/><path d="M8 7V5a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg></div>
        <h3>TAR.GZ to ZIP</h3>
        <p>Convert a gzipped tarball into a ZIP.</p>
    </a>
    <a href="/tools_archive.php?mode=jar" class="tool-card">
        <div class="icon"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="18" height="18" rx="2"/></svg></div>
        <h3>Extract JAR</h3>
        <p>JAR files are ZIPs underneath, unpack them the same way.</p>
    </a>
    <a href="/tools_share.php" class="tool-card">
        <div class="icon"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 2 11 13"/><path d="M22 2 15 22l-4-9-9-4z"/></svg></div>
        <h3>Share Files</h3>
        <p>Get a temporary download link for any file, 48 hours.</p>
    </a>
    <div class="tool-card disabled">
        <div class="icon"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="18" height="18" rx="2"/></svg></div>
        <h3>RAR / 7Z Extraction</h3>
        <p>Needs extra system tools we haven't wired up yet.</p>
        <span class="soon-tag">Coming soon</span>
    </div>
    <div class="tool-card disabled">
        <div class="icon"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="9"/></svg></div>
        <h3>Disc &amp; System Images</h3>
        <p>ISO, VMDK, DMG, and similar formats.</p>
        <span class="soon-tag">Coming soon</span>
    </div>
    <div class="tool-card disabled">
        <div class="icon"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="5" y="2" width="14" height="20" rx="2"/></svg></div>
        <h3>Apps &amp; Packages</h3>
        <p>APK, IPA, EXE, DEB inspection.</p>
        <span class="soon-tag">Coming soon</span>
    </div>
</div>

<h2 style="margin-top:36px">Email &amp; Contacts</h2>
<div class="tool-grid">
    <a href="/tools_qr.php" class="tool-card">
        <div class="icon"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/><path d="M14 14h3v3h-3zM18 18h3v3h-3zM14 21h3M21 14v3"/></svg></div>
        <h3>QR Code Generator</h3>
        <p>Turn any link into a scannable QR code.</p>
    </a>
    <a href="/tools_shortener.php" class="tool-card">
        <div class="icon"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71"/><path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71"/></svg></div>
        <h3>URL Shortener</h3>
        <p>Shorten links and track clicks.</p>
    </a>
    <a href="/tools_email_verify.php" class="tool-card">
        <div class="icon"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><path d="M22 4 12 14.01l-3-3"/></svg></div>
        <h3>Email List Verifier</h3>
        <p>Check a list of emails before you send to them.</p>
    </a>

    <?php if ($loggedIn): ?>
        <a href="/tools_contacts_clean.php" class="tool-card">
            <div class="icon"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 6h18M8 6V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2m3 0-1 14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2L4 6"/></svg></div>
            <h3>Contact Cleanup</h3>
            <p>Find and remove duplicates in your list.</p>
        </a>
        <a href="/campaign_create.php" class="tool-card">
            <div class="icon"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="5" width="18" height="14" rx="2"/><path d="m3 7 9 6 9-6"/></svg></div>
            <h3>Email Campaigns</h3>
            <p>Send personalized emails to your list.</p>
        </a>
    <?php else: ?>
        <a href="/register.php" class="tool-card">
            <div class="icon"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 6h18M8 6V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2m3 0-1 14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2L4 6"/></svg></div>
            <h3>Contact Cleanup</h3>
            <p>Requires an account, it works on your saved list.</p>
            <span class="soon-tag">Sign in</span>
        </a>
        <a href="/register.php" class="tool-card">
            <div class="icon"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="5" width="18" height="14" rx="2"/><path d="m3 7 9 6 9-6"/></svg></div>
            <h3>Email Campaigns</h3>
            <p>Real sends need an account, keeps this from being abused for spam.</p>
            <span class="soon-tag">Sign in</span>
        </a>
    <?php endif; ?>

    <div class="tool-card disabled">
        <div class="icon"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg></div>
        <h3>WhatsApp / SMS Broadcast</h3>
        <p>Not just email.</p>
        <span class="soon-tag">Coming soon</span>
    </div>
</div>

<h2 style="margin-top:36px">Legal &amp; Docs</h2>
<div class="tool-grid">
    <a href="/tools_policy_generator.php" class="tool-card">
        <div class="icon"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><path d="M14 2v6h6M9 15h6M9 11h6"/></svg></div>
        <h3>Privacy Policy &amp; Terms Generator</h3>
        <p>Answer a few questions, get a template for your own site.</p>
    </a>
</div>

<?php require __DIR__ . '/../includes/' . ($loggedIn ? 'footer.php' : 'public_footer.php'); ?>
