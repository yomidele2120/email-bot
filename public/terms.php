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

<p>These Terms of Service govern your use of Email Bot. By creating an account or using any tool on this site, you agree to these terms.</p>

<h2 style="margin-top:28px">1. Who can use Email Bot</h2>
<p>You must be able to form a legally binding contract to create an account. If you're using Email Bot on behalf of a business, you're confirming you have the authority to bind that business to these terms.</p>

<h2 style="margin-top:28px">2. Acceptable use of the campaign tool</h2>
<p>Email Bot is built for sending campaigns to contacts who have agreed to receive them. You agree that you will:</p>
<ul style="line-height:1.8">
<li>Only email contacts who have opted in to hear from you</li>
<li>Never upload purchased, scraped, or rented contact lists</li>
<li>Keep the unsubscribe link intact in every template you send</li>
<li>Comply with applicable anti-spam law, including Nigeria's NDPR, CAN-SPAM (US), and GDPR (EU/UK) where relevant to your recipients</li>
</ul>
<p>We reserve the right to suspend or terminate any account that violates these requirements, particularly around unsolicited bulk email, since this directly affects the deliverability and reputation of the platform for everyone.</p>

<h2 style="margin-top:28px">3. Your account</h2>
<p>You're responsible for keeping your login credentials secure and for all activity that occurs under your account. Notify us promptly if you believe your account has been compromised.</p>

<h2 style="margin-top:28px">4. The free tools</h2>
<p>Tools like the QR generator, URL shortener, email verifier, archive converter, file sharing, and policy generator are offered free for a limited number of anonymous uses per browser session, and without limit for registered accounts. We may adjust these limits, add, or remove tools at any time.</p>
<p>Files uploaded through Share Files expire after 48 hours. Converted archives expire after 10 minutes or immediately after download, whichever comes first. Short links remain active indefinitely unless removed.</p>

<h2 style="margin-top:28px">5. The AI template generator</h2>
<p>The AI-assisted template generator produces draft HTML based on your description. You are responsible for reviewing generated content before sending it to your contacts, we don't guarantee the generated content is free of errors, and it does not constitute professional design, legal, or marketing advice.</p>

<h2 style="margin-top:28px">6. The policy generator</h2>
<p>Documents produced by the Privacy Policy and Terms of Service generator are general templates based on the information you provide. They are not legal advice, and we strongly recommend having a qualified lawyer review any generated document before publishing it on your own site.</p>

<h2 style="margin-top:28px">7. Prohibited uses</h2>
<p>Across every part of the platform, you may not use Email Bot to:</p>
<ul style="line-height:1.8">
<li>Distribute malware, viruses, or harmful code</li>
<li>Infringe the intellectual property or privacy rights of others</li>
<li>Harass, defraud, or impersonate any person or entity</li>
<li>Attempt to circumvent rate limits, free-trial restrictions, or security controls</li>
<li>Use the file-sharing or archive tools to distribute illegal content</li>
</ul>

<h2 style="margin-top:28px">8. Termination</h2>
<p>We may suspend or terminate your account at our discretion, particularly for spam, abuse, or violation of these terms. You may stop using the service and request account deletion at any time.</p>

<h2 style="margin-top:28px">9. Disclaimer and limitation of liability</h2>
<p>Email Bot is provided "as is" without warranties of any kind, express or implied. We do not guarantee uninterrupted availability, error-free operation, or that emails sent through the platform will be delivered or avoid spam filters. To the fullest extent permitted by law, Email Bot and its operators are not liable for indirect, incidental, or consequential damages arising from your use of the service.</p>

<h2 style="margin-top:28px">10. Changes to these terms</h2>
<p>We may update these Terms from time to time. Continued use of Email Bot after changes take effect constitutes acceptance of the revised Terms.</p>

<h2 style="margin-top:28px">11. Governing law</h2>
<p>These Terms are governed by the laws of Nigeria, without regard to conflict of law principles, unless otherwise required by mandatory law applicable to you.</p>

<h2 style="margin-top:28px">12. Contact</h2>
<p>Questions about these Terms can be sent to the contact address associated with your account, or to the site owner directly.</p>
</div>

<?php require __DIR__ . '/../includes/' . ($loggedIn ? 'footer.php' : 'public_footer.php'); ?>
