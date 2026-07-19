<?php
require __DIR__ . '/../includes/bootstrap.php';

use App\Auth;

$loggedIn = Auth::check();
$pageTitle = 'Terms of Service';
require __DIR__ . '/../includes/' . ($loggedIn ? 'header.php' : 'public_header.php');
?>

<div style="max-width:720px">
<h1>Terms of Service</h1>
<p style="color:var(--text-muted)">Last updated: <?= date('F j, Y') ?></p>

<p>By using Email Bot, you agree to these terms.</p>

<h2 style="margin-top:28px">Acceptable use</h2>
<p>Email Bot is built for sending email campaigns to contacts who have agreed to receive them. You may not use this service to send unsolicited bulk email (spam), to contacts who haven't opted in, or in any way that violates applicable anti-spam law (including Nigeria's NDPR, CAN-SPAM, or GDPR where relevant).</p>

<h2 style="margin-top:28px">Your account</h2>
<p>You're responsible for keeping your login credentials secure and for all activity that happens under your account.</p>

<h2 style="margin-top:28px">The free tools</h2>
<p>Tools like the QR generator, URL shortener, email verifier, and archive converter are offered free for limited anonymous use, and unlimited use with an account. We may adjust these limits at any time.</p>

<h2 style="margin-top:28px">Prohibited uses</h2>
<p>You may not use Email Bot to distribute malware, infringe copyright, harass others, or attempt to circumvent the security or rate limits of the service.</p>

<h2 style="margin-top:28px">Termination</h2>
<p>We may suspend or terminate accounts that violate these terms, particularly around spam and abuse of the sending infrastructure.</p>

<h2 style="margin-top:28px">Limitation of liability</h2>
<p>Email Bot is provided as-is. We aren't liable for indirect or consequential damages arising from your use of the service.</p>

<h2 style="margin-top:28px">Changes</h2>
<p>We may update these terms from time to time. Continued use after changes means you accept the new terms.</p>
</div>

<?php require __DIR__ . '/../includes/' . ($loggedIn ? 'footer.php' : 'public_footer.php'); ?>
