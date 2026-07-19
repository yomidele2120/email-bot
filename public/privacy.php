<?php
require __DIR__ . '/../includes/bootstrap.php';

use App\Auth;

$loggedIn = Auth::check();
$pageTitle = 'Privacy Policy';
require __DIR__ . '/../includes/' . ($loggedIn ? 'header.php' : 'public_header.php');
?>

<div style="max-width:720px">
<h1>Privacy Policy</h1>
<p style="color:var(--text-muted)">Last updated: <?= date('F j, Y') ?></p>

<p>This Privacy Policy explains how Email Bot collects, uses, and protects your information.</p>

<h2 style="margin-top:28px">Information we collect</h2>
<p>When you create an account, we collect your name, email address, and a securely hashed password. When you use the platform, we store the contacts you upload, the campaigns you create, and the content of the templates you send. Some tools (URL Shortener, Share Files) store the links or files you submit through them.</p>

<h2 style="margin-top:28px">How we use it</h2>
<p>We use this information to operate your account, send the campaigns you create through our email delivery provider (SendGrid), and maintain the security of the service. We do not sell your data to third parties.</p>

<h2 style="margin-top:28px">Third-party services</h2>
<p>Email Bot relies on SendGrid to deliver emails, and Railway to host the application and database. These providers only receive the information necessary to perform their function.</p>

<h2 style="margin-top:28px">Cookies</h2>
<p>We use session cookies to keep you signed in and to track free trial usage on tools that don't require an account. We don't use tracking cookies for advertising.</p>

<h2 style="margin-top:28px">Your contacts' data</h2>
<p>If you upload a contact list, you are responsible for having a lawful basis to email those contacts. Every email sent through Email Bot includes a working unsubscribe link.</p>

<h2 style="margin-top:28px">Data retention</h2>
<p>We retain your account data for as long as your account is active. You can request deletion of your account and associated data by contacting us.</p>

<h2 style="margin-top:28px">Your rights</h2>
<p>You may request access to, correction of, or deletion of your personal data at any time.</p>

<h2 style="margin-top:28px">Contact</h2>
<p>Questions about this policy can be sent to the contact address listed on your account.</p>
</div>

<?php require __DIR__ . '/../includes/' . ($loggedIn ? 'footer.php' : 'public_footer.php'); ?>
