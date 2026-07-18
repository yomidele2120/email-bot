<?php
require __DIR__ . '/../includes/bootstrap.php';

use App\Auth;

Auth::requireLogin();

$pageTitle = 'Tools';
$activeNav = 'tools';
require __DIR__ . '/../includes/header.php';
?>

<h1>Tools</h1>
<p style="color:var(--text-muted)">Small utilities that go with your campaigns.</p>

<div class="tool-grid">
    <a href="/tools_qr.php" class="tool-card">
        <div class="icon">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/><path d="M14 14h3v3h-3zM18 18h3v3h-3zM14 21h3M21 14v3"/></svg>
        </div>
        <h3>QR Code Generator</h3>
        <p>Turn any link into a scannable QR code, useful for flyers and print.</p>
    </a>

    <a href="/tools_shortener.php" class="tool-card">
        <div class="icon">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71"/><path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71"/></svg>
        </div>
        <h3>URL Shortener</h3>
        <p>Shorten long links for campaigns and track how many people click.</p>
    </a>

    <a href="/tools_email_verify.php" class="tool-card">
        <div class="icon">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><path d="M22 4 12 14.01l-3-3"/></svg>
        </div>
        <h3>Email List Verifier</h3>
        <p>Paste a list of emails and check which ones are valid before sending.</p>
    </a>

    <a href="/tools_contacts_clean.php" class="tool-card">
        <div class="icon">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 6h18M8 6V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2m3 0-1 14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2L4 6"/></svg>
        </div>
        <h3>Contact Cleanup</h3>
        <p>Find and remove duplicate or invalid entries from your contact list.</p>
    </a>

    <div class="tool-card disabled">
        <div class="icon">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
        </div>
        <h3>WhatsApp / SMS Broadcast</h3>
        <p>Send campaigns over WhatsApp or SMS, not just email.</p>
        <span class="soon-tag">Coming soon</span>
    </div>

    <div class="tool-card disabled">
        <div class="icon">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><path d="M14 2v6h6M9 15h6M9 11h6"/></svg>
        </div>
        <h3>Invoice Generator</h3>
        <p>Create and send professional invoices to your clients.</p>
        <span class="soon-tag">Coming soon</span>
    </div>
</div>

<?php require __DIR__ . '/../includes/footer.php'; ?>
