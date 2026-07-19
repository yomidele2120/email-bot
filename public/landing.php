<?php
require __DIR__ . '/../includes/bootstrap.php';

use App\Auth;

if (Auth::check()) {
    header('Location: /dashboard.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Email Bot — Email Campaigns, QR Codes, URL Shortener & File Tools</title>
    <meta name="description" content="Send personalized email campaigns with built-in unsubscribe compliance, plus free tools: QR code generator, URL shortener, email list verifier, archive/ZIP converter, and file sharing. Most tools work without an account.">
    <meta name="keywords" content="email marketing automation, bulk email sender, email campaign tool, QR code generator, URL shortener, email verifier, ZIP converter, TAR to ZIP, file sharing tool, unsubscribe link generator">
    <link rel="canonical" href="<?= htmlspecialchars(rtrim($_ENV['APP_URL'] ?? '', '/')) ?>/">

    <meta property="og:type" content="website">
    <meta property="og:title" content="Email Bot — Email Campaigns, QR Codes, URL Shortener & File Tools">
    <meta property="og:description" content="Send personalized email campaigns with built-in unsubscribe compliance, plus free QR, URL shortener, email verifier, and file conversion tools.">
    <meta property="og:url" content="<?= htmlspecialchars(rtrim($_ENV['APP_URL'] ?? '', '/')) ?>/">

    <meta name="twitter:card" content="summary">
    <meta name="twitter:title" content="Email Bot — Email Campaigns, QR Codes, URL Shortener & File Tools">
    <meta name="twitter:description" content="Send personalized email campaigns, plus free QR, URL shortener, email verifier, and file conversion tools.">

    <script type="application/ld+json">
    {
        "@context": "https://schema.org",
        "@type": "WebApplication",
        "name": "Email Bot",
        "url": "<?= htmlspecialchars(rtrim($_ENV['APP_URL'] ?? '', '/')) ?>/",
        "applicationCategory": "BusinessApplication",
        "operatingSystem": "Web",
        "description": "Email campaign automation with personalized templates and built-in unsubscribe compliance, plus free tools for QR codes, URL shortening, email verification, and archive conversion.",
        "offers": {
            "@type": "Offer",
            "price": "0",
            "priceCurrency": "USD"
        }
    }
    </script>

    <link rel="stylesheet" href="/assets/style.css">
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
    <?php require __DIR__ . '/../includes/tools_menu.php'; ?>
    <div class="landing-nav-actions">
        <a href="/login.php" class="btn-secondary btn">Sign in</a>
        <a href="/register.php" class="btn">Get started</a>
    </div>
</header>

<section class="landing-hero">
    <div class="hero-grid-bg"></div>
    <h1>Campaigns that<br><span class="gold-text">send themselves.</span></h1>
    <p class="hero-sub">Upload a contact list, drop in a template, walk away. A queue drains it in the background so you never touch a mail client again.</p>
    <div class="hero-actions">
        <a href="/register.php" class="btn">Create free account</a>
        <a href="/login.php" class="btn btn-secondary">Sign in</a>
    </div>

    <div class="hero-scene" aria-hidden="true">
        <div class="scene-ring"></div>
        <div class="scene-ring-2"></div>
        <div class="scene-envelope">
            <svg width="140" height="102" viewBox="0 0 88 64" fill="none">
                <rect x="2" y="2" width="84" height="60" rx="8" fill="#ffffff" stroke="#0969da" stroke-width="2.5"/>
                <path d="M4 8 44 38 84 8" stroke="#0969da" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" fill="none"/>
            </svg>
        </div>
        <div class="scene-particle p1"><svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#0969da" stroke-width="2"><path d="M22 12h-6l-2 3h-4l-2-3H2"/><path d="M5.45 5.11 2 12v6a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2v-6l-3.45-6.89A2 2 0 0 0 16.76 4H7.24a2 2 0 0 0-1.79 1.11z"/></svg></div>
        <div class="scene-particle p2"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#C9A227" stroke-width="2"><circle cx="12" cy="8" r="4"/><path d="M6 21v-2a4 4 0 0 1 4-4h4a4 4 0 0 1 4 4v2"/></svg></div>
        <div class="scene-particle p3"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#1a7f37" stroke-width="2.5"><path d="M20 6 9 17l-5-5"/></svg></div>
        <div class="scene-particle p4"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#0969da" stroke-width="2"><circle cx="12" cy="8" r="4"/><path d="M6 21v-2a4 4 0 0 1 4-4h4a4 4 0 0 1 4 4v2"/></svg></div>
        <div class="scene-particle p5"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#1a7f37" stroke-width="2.5"><path d="M20 6 9 17l-5-5"/></svg></div>
        <div class="scene-particle p6"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#C9A227" stroke-width="2"><rect x="3" y="5" width="18" height="14" rx="2"/><path d="m3 7 9 6 9-6"/></svg></div>
    </div>

    <div class="hero-mock mono">
        <div class="hero-mock-row"><span class="hero-mock-dim">POST</span> /campaign_create.php</div>
        <div class="hero-mock-row"><span class="hero-mock-dim">→</span> Imported 4,213 contacts, skipped 0 invalid rows.</div>
        <div class="hero-mock-row"><span class="hero-mock-dim">→</span> Campaign #92 created and queued.</div>
        <div class="hero-mock-row"><span class="hero-mock-dim">worker</span> Sent to jane@company.com <span class="dot-inline"></span></div>
        <div class="hero-mock-row"><span class="hero-mock-dim">worker</span> Sent to marcus@studio.io <span class="dot-inline"></span></div>
    </div>
</section>

<section class="trust-strip">
    <div class="trust-item">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 6 9 17l-5-5"/></svg>
        No credit card to start
    </div>
    <div class="trust-item">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 6 9 17l-5-5"/></svg>
        Most tools work without signup
    </div>
    <div class="trust-item">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 6 9 17l-5-5"/></svg>
        Unsubscribe link built into every send
    </div>
    <div class="trust-item">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 6 9 17l-5-5"/></svg>
        Cancel anytime
    </div>
</section>

<section class="landing-section" id="features">
    <h2 class="section-heading">Everything the send needs, nothing you have to babysit</h2>
    <div class="feature-grid">
        <div class="feature-card">
            <h3>Batch-safe sending</h3>
            <p>A cron worker drains your queue a little at a time, so you stay well under provider rate limits and out of spam folders.</p>
        </div>
        <div class="feature-card">
            <h3>Personalized templates</h3>
            <p>Drop in <span class="mono">{{name}}</span>, <span class="mono">{{email}}</span>, or any column from your CSV. Merged per recipient, automatically.</p>
        </div>
        <div class="feature-card">
            <h3>Built-in compliance</h3>
            <p>Every send includes a working unsubscribe link. No extra setup, no risk of forgetting it.</p>
        </div>
        <div class="feature-card">
            <h3>Live campaign status</h3>
            <p>Watch a campaign move from queued to sending to completed, with sent and failed counts as it happens.</p>
        </div>
    </div>
</section>

<section class="landing-section" id="tools">
    <h2 class="section-heading">A few extra tools, because you're already here</h2>
    <div class="feature-grid">
        <div class="feature-card">
            <h3>QR codes</h3>
            <p>Turn any campaign link into a scannable code for print or in-person use.</p>
        </div>
        <div class="feature-card">
            <h3>URL shortener</h3>
            <p>Short trackable links with click counts, no separate service needed.</p>
        </div>
        <div class="feature-card">
            <h3>Email verification</h3>
            <p>Catch typos and dead domains in a list before you send to them.</p>
        </div>
        <div class="feature-card">
            <h3>Contact cleanup</h3>
            <p>Find duplicates and malformed entries hiding in an imported list.</p>
        </div>
    </div>
</section>

<footer class="landing-footer">
    <span>Email Bot</span>
    <span style="display:flex;gap:16px">
        <a href="/privacy.php" style="color:var(--text-muted)">Privacy Policy</a>
        <a href="/terms.php" style="color:var(--text-muted)">Terms of Service</a>
    </span>
</footer>

<script>
// Tools mega-dropdown: click to toggle (works on touch), closes on outside click
const dd = document.getElementById('toolsMegaDropdown');
const trigger = document.getElementById('toolsMegaTrigger');
trigger.addEventListener('click', (e) => {
    e.stopPropagation();
    dd.classList.toggle('open');
});
document.addEventListener('click', (e) => {
    if (!dd.contains(e.target)) dd.classList.remove('open');
});
</script>

</body>
</html>
