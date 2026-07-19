<?php
require __DIR__ . '/../includes/bootstrap.php';

use App\Auth;
use App\TrialGate;

$loggedIn = Auth::check();
$showPaywall = false;
$generatedDoc = '';
$docLabel = '';

function pg_list($items) {
    if (empty($items)) return 'no additional categories beyond the basics described above';
    return implode(', ', $items);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!TrialGate::check('policy')) {
        $showPaywall = true;
    } else {
        $siteName = trim($_POST['site_name'] ?? 'the Website');
        $siteUrl = trim($_POST['site_url'] ?? '');
        $contactEmail = trim($_POST['contact_email'] ?? '');
        $docType = $_POST['doc_type'] ?? 'privacy';
        $country = $_POST['country'] ?? 'International';
        $dataItems = $_POST['data_collected'] ?? [];
        $thirdParty = $_POST['third_party'] ?? [];

        $dataLabels = [
            'name' => 'name', 'email' => 'email address', 'ip' => 'IP address',
            'cookies' => 'cookies and usage data', 'payment' => 'payment information',
            'files' => 'files you upload', 'location' => 'general location',
        ];
        $thirdPartyLabels = [
            'analytics' => 'analytics providers (e.g. Google Analytics)',
            'payments' => 'payment processors (e.g. Stripe, Paystack, Flutterwave)',
            'email' => 'email delivery providers (e.g. SendGrid, Mailgun)',
            'ads' => 'advertising networks (e.g. Google Ads, Meta Ads)',
        ];

        $dataList = pg_list(array_map(fn($k) => $dataLabels[$k] ?? $k, $dataItems));
        $thirdPartyList = pg_list(array_map(fn($k) => $thirdPartyLabels[$k] ?? $k, $thirdParty));
        $today = date('F j, Y');

        if ($docType === 'terms') {
            $docLabel = 'Terms of Service';
            $generatedDoc = <<<DOC
TERMS OF SERVICE FOR {$siteName}

Last updated: {$today}

1. ACCEPTANCE OF TERMS
By accessing or using {$siteName} ({$siteUrl}), you agree to be bound by these Terms of Service. If you do not agree, do not use this website.

2. USE OF THE SERVICE
You agree to use {$siteName} only for lawful purposes and in accordance with these Terms. You are responsible for any content you upload, submit, or transmit through the service.

3. ACCOUNTS
If {$siteName} requires an account, you are responsible for maintaining the confidentiality of your login credentials and for all activity under your account.

4. PROHIBITED USES
You may not use {$siteName} to: violate any applicable law; infringe the intellectual property rights of others; transmit spam, malware, or harmful code; or attempt to gain unauthorized access to the service or its underlying systems.

5. THIRD-PARTY SERVICES
{$siteName} may rely on third-party providers, including {$thirdPartyList}, to operate. Your use of the service is also subject to those providers' own terms where applicable.

6. TERMINATION
We reserve the right to suspend or terminate access to {$siteName} for any user who violates these Terms.

7. LIMITATION OF LIABILITY
{$siteName} is provided "as is" without warranties of any kind. To the fullest extent permitted by law, {$siteName} and its operators are not liable for indirect, incidental, or consequential damages arising from use of the service.

8. GOVERNING LAW
These Terms are governed by the laws applicable in {$country}, without regard to conflict of law principles.

9. CHANGES TO THESE TERMS
We may update these Terms from time to time. Continued use of {$siteName} after changes constitutes acceptance of the revised Terms.

10. CONTACT
Questions about these Terms can be sent to {$contactEmail}.

---
This document was generated from a general template based on the information provided and does not constitute legal advice. Have it reviewed by a qualified lawyer before publishing, to confirm it fits your specific business and the laws that apply to your users (e.g. GDPR, NDPR, CCPA).
DOC;
        } else {
            $docLabel = 'Privacy Policy';
            $generatedDoc = <<<DOC
PRIVACY POLICY FOR {$siteName}

Last updated: {$today}

1. INTRODUCTION
This Privacy Policy explains how {$siteName} ({$siteUrl}) collects, uses, and protects information when you use this website.

2. INFORMATION WE COLLECT
We may collect the following information: {$dataList}.

3. HOW WE USE YOUR INFORMATION
We use collected information to operate and improve {$siteName}, communicate with you, process transactions where applicable, and maintain the security of the service.

4. COOKIES
{$siteName} may use cookies or similar technologies to keep you signed in, remember preferences, and understand how the site is used.

5. THIRD-PARTY SERVICES
{$siteName} may share information with third-party service providers necessary to operate the site, including {$thirdPartyList}. These providers only receive the information needed to perform their function.

6. DATA RETENTION
We retain information for as long as necessary to provide the service and comply with legal obligations, after which it is deleted or anonymized.

7. YOUR RIGHTS
Depending on your location, you may have rights to access, correct, delete, or export your personal data. Contact us using the details below to exercise these rights.

8. CHILDREN'S PRIVACY
{$siteName} is not directed at children under 13 (or the minimum age required in your jurisdiction), and we do not knowingly collect data from children.

9. CHANGES TO THIS POLICY
We may update this Privacy Policy from time to time. Material changes will be reflected by an updated "Last updated" date above.

10. CONTACT US
For questions about this Privacy Policy, contact {$contactEmail}.

---
This document was generated from a general template based on the information provided and does not constitute legal advice. Have it reviewed by a qualified lawyer before publishing, to confirm it fits your specific business and the laws that apply to your users (this template does not by itself guarantee compliance with GDPR, Nigeria's NDPR, CCPA, or other data protection laws relevant to {$country}).
DOC;
        }
    }
}

$pageTitle = 'Policy Generator';
$activeNav = 'tools_policy';
require __DIR__ . '/../includes/' . ($loggedIn ? 'header.php' : 'public_header.php');
?>

<?php if ($loggedIn): ?><p><a href="/tools.php" style="color:var(--text-muted);font-size:13px">← Tools</a></p><?php endif; ?>
<?php
$toolTitle = 'Privacy Policy & Terms Generator';
$toolDesc = 'Answer a few questions, get a Privacy Policy or Terms of Service draft for your own website. Free template, not a substitute for legal review.';
require __DIR__ . '/../includes/tool_header.php';
?>
<?php if (!$loggedIn): ?>
    <p style="color:var(--text-muted);font-size:13px">Free to try <strong class="mono"><?= TrialGate::usesLeft('policy') ?></strong> more time(s) without an account.</p>
<?php endif; ?>

<div class="alert" style="background:#fff8c5;border-color:#d4a72c;color:#7d5a00;">
    <strong>Not legal advice.</strong> This generates a general template from your answers. Have a qualified lawyer review it before publishing, especially if your users are in the EU (GDPR), Nigeria (NDPR), California (CCPA), or another jurisdiction with specific data protection requirements.
</div>

<?php if ($generatedDoc): ?>
    <div class="result-panel" style="align-items:flex-start">
        <div class="result-panel-icon"><svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="#1a7f37" stroke-width="2"><path d="M20 6 9 17l-5-5"/></svg></div>
        <div class="result-panel-info">
            <strong><?= htmlspecialchars($docLabel) ?> generated</strong>
            <span>Copy it below or save it as a text file.</span>
        </div>
        <button type="button" class="btn" style="margin:0" onclick="saveGeneratedDoc()">Choose folder &amp; save</button>
    </div>
    <textarea id="generatedDocText" readonly rows="20" style="font-family:'JetBrains Mono',monospace;font-size:12px;margin-bottom:24px"><?= htmlspecialchars($generatedDoc) ?></textarea>
<?php endif; ?>

<form method="POST" class="tool-panel" style="max-width:560px">
    <label style="margin-top:0">Document type</label>
    <select name="doc_type" style="width:100%;padding:8px 12px;border:1px solid var(--border);border-radius:var(--radius);background:var(--bg)">
        <option value="privacy">Privacy Policy</option>
        <option value="terms">Terms of Service</option>
    </select>

    <label>Website / product name</label>
    <input type="text" name="site_name" placeholder="Your App Name" required>

    <label>Website URL</label>
    <input type="text" name="site_url" placeholder="https://yourapp.com" required>

    <label>Contact email</label>
    <input type="email" name="contact_email" placeholder="support@yourapp.com" required>

    <label>Primary country / jurisdiction</label>
    <select name="country" style="width:100%;padding:8px 12px;border:1px solid var(--border);border-radius:var(--radius);background:var(--bg)">
        <option>Nigeria</option>
        <option>United States</option>
        <option>European Union / United Kingdom</option>
        <option>International</option>
    </select>

    <label>What data do you collect?</label>
    <div style="display:flex;flex-wrap:wrap;gap:10px;margin-top:6px">
        <?php foreach (['name'=>'Name','email'=>'Email','ip'=>'IP address','cookies'=>'Cookies','payment'=>'Payment info','files'=>'Uploaded files','location'=>'Location'] as $val => $label): ?>
            <label style="display:flex;align-items:center;gap:6px;font-weight:400;margin:0;font-size:13px">
                <input type="checkbox" name="data_collected[]" value="<?= $val ?>" style="width:auto"> <?= $label ?>
            </label>
        <?php endforeach; ?>
    </div>

    <label>Which third-party services do you use?</label>
    <div style="display:flex;flex-wrap:wrap;gap:10px;margin-top:6px">
        <?php foreach (['analytics'=>'Analytics','payments'=>'Payment processor','email'=>'Email provider','ads'=>'Advertising'] as $val => $label): ?>
            <label style="display:flex;align-items:center;gap:6px;font-weight:400;margin:0;font-size:13px">
                <input type="checkbox" name="third_party[]" value="<?= $val ?>" style="width:auto"> <?= $label ?>
            </label>
        <?php endforeach; ?>
    </div>

    <button type="submit">Generate document</button>
</form>

<script>
async function saveGeneratedDoc() {
    const text = document.getElementById('generatedDocText').value;
    const name = 'policy.txt';
    if (window.showSaveFilePicker) {
        try {
            const handle = await window.showSaveFilePicker({ suggestedName: name });
            const writable = await handle.createWritable();
            await writable.write(text);
            await writable.close();
            return;
        } catch (e) { if (e.name === 'AbortError') return; }
    }
    const blob = new Blob([text], { type: 'text/plain' });
    const url = URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url; a.download = name; a.click();
    URL.revokeObjectURL(url);
}
</script>

<?php require __DIR__ . '/../includes/' . ($loggedIn ? 'footer.php' : 'public_footer.php'); ?>
