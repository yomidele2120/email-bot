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

<p>This Privacy Policy explains how Email Bot ("we," "us," or "the service") collects, uses, discloses, and protects information when you use this website and its tools.</p>

<h2 style="margin-top:28px">1. Who we are</h2>
<p>Email Bot is an email campaign and utility tool platform. This policy applies to everyone who visits the site, whether or not you create an account.</p>

<h2 style="margin-top:28px">2. Information we collect</h2>
<p><strong>Account information:</strong> when you register, we collect your name, email address, and a securely hashed password (we never store your password in plain text, and we cannot see it).</p>
<p><strong>Contact and campaign data:</strong> if you use the campaign feature, we store the contacts you upload (their email addresses, names, and any custom fields in your CSV) and the content of the templates you create.</p>
<p><strong>Tool usage data:</strong> some tools store what you submit to operate the feature, for example, URLs you shorten, files you upload for temporary sharing, or documents generated through the policy generator. These are described further in the sections below.</p>
<p><strong>Anonymous usage tracking:</strong> tools that offer free trials without an account (QR generator, URL shortener, email verifier, archive converter, file sharing, policy generator) track how many times your browser session has used that specific tool, using a session cookie, so we can apply the free-trial limit fairly. We don't tie this to your identity beyond the session.</p>
<p><strong>Technical data:</strong> like most websites, our hosting and email delivery providers automatically log technical details such as IP address, browser type, and timestamps, as a normal part of operating the service and preventing abuse.</p>

<h2 style="margin-top:28px">3. How we use your information</h2>
<ul style="color:#d8d8d8;line-height:1.8">
<li>To create and operate your account</li>
<li>To send the email campaigns you create, through our delivery provider</li>
<li>To operate the free tools (QR codes, URL shortening, file sharing, document generation, etc.)</li>
<li>To maintain the security and reliability of the service, including detecting abuse</li>
<li>To communicate with you about your account when necessary</li>
</ul>
<p>We do not sell your personal data, or your contacts' data, to third parties. We do not use your contact lists for any purpose other than sending the campaigns you personally create.</p>

<h2 style="margin-top:28px">4. Third-party services we rely on</h2>
<p>Email Bot is built on top of a small number of infrastructure providers, each only receiving the data necessary to perform their function:</p>
<ul style="line-height:1.8">
<li><strong>SendGrid</strong> — delivers the emails you send through campaigns</li>
<li><strong>Railway</strong> — hosts the application and the database</li>
</ul>
<p>These providers have their own privacy policies governing how they handle data on our behalf.</p>

<h2 style="margin-top:28px">5. Cookies and sessions</h2>
<p>We use a session cookie to keep you signed in, and to track free-trial usage on tools that don't require an account. We do not use third-party advertising or tracking cookies.</p>

<h2 style="margin-top:28px">6. Your contacts' data</h2>
<p>If you upload a contact list, you confirm that you have a lawful basis to email those contacts (for example, they opted in to hear from you). Every campaign email sent through this platform includes a working unsubscribe link, and unsubscribed contacts are permanently excluded from future sends on your account.</p>

<h2 style="margin-top:28px">7. File sharing and temporary uploads</h2>
<p>Files uploaded through the Share Files tool are automatically deleted after 48 hours. Files converted through the Archive Converter tool are deleted immediately after download, or automatically expire after 10 minutes if not downloaded.</p>

<h2 style="margin-top:28px">8. Data retention</h2>
<p>We retain your account, contacts, and campaign data for as long as your account remains active. If you'd like your account and associated data deleted, contact us using the details below.</p>

<h2 style="margin-top:28px">9. Your rights</h2>
<p>Depending on where you're located, you may have rights to access, correct, export, or delete your personal data, and to object to or restrict certain processing. To exercise any of these rights, contact us using the details below.</p>

<h2 style="margin-top:28px">10. Children's privacy</h2>
<p>Email Bot is not directed at children under 13 (or the relevant minimum age in your jurisdiction), and we do not knowingly collect information from children.</p>

<h2 style="margin-top:28px">11. Changes to this policy</h2>
<p>We may update this Privacy Policy from time to time. Material changes will be reflected by an updated "Last updated" date at the top of this page.</p>

<h2 style="margin-top:28px">12. Contact us</h2>
<p>Questions or requests regarding this Privacy Policy can be sent to the contact address associated with your account, or to the site owner directly.</p>
</div>

<?php require __DIR__ . '/../includes/' . ($loggedIn ? 'footer.php' : 'public_footer.php'); ?>
