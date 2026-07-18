<?php
require __DIR__ . '/../includes/bootstrap.php';

use App\Auth;
use App\Contact;
use App\Campaign;

Auth::requireLogin();
$userId = Auth::id();

$message = '';
$messageType = 'success';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!empty($_FILES['contacts_csv']['tmp_name'])) {
        $result = Contact::importFromCsv($_FILES['contacts_csv']['tmp_name'], $userId);
        $message .= "Imported {$result['imported']} contacts, skipped {$result['skipped']} invalid rows. ";
    }

    $templateHtml = '';
    if (!empty($_FILES['template_file']['tmp_name'])) {
        $templateHtml = file_get_contents($_FILES['template_file']['tmp_name']);
    } elseif (!empty($_POST['template_html'])) {
        $templateHtml = $_POST['template_html'];
    }

    $subject = trim($_POST['subject'] ?? '');

    if ($templateHtml && $subject) {
        $campaignId = Campaign::create($userId, $subject, $templateHtml);
        $count = Campaign::queueAll($campaignId, $userId);
        $message .= "Campaign created and queued for $count contacts. It'll go out in batches shortly.";
    } else {
        $message .= 'A subject and a template are both required.';
        $messageType = 'error';
    }
}

$pageTitle = 'New Campaign';
$activeNav = 'new_campaign';
require __DIR__ . '/../includes/header.php';
?>

<h1>New campaign</h1>
<p style="color:var(--text-muted)">Upload contacts, write your email, send it out.</p>

<?php if ($message): ?>
    <div class="alert alert-<?= $messageType ?>"><?= htmlspecialchars($message) ?></div>
<?php endif; ?>

<form method="POST" enctype="multipart/form-data" style="max-width:560px">
    <label>Contacts CSV <span style="color:var(--text-muted);font-weight:400">(columns: email, name, ...custom fields)</span></label>
    <input type="file" name="contacts_csv" accept=".csv">

    <label>Subject line</label>
    <input type="text" name="subject" required placeholder="e.g. Your July update from us">

    <label>Template HTML file <span style="color:var(--text-muted);font-weight:400">(optional if pasting below)</span></label>
    <input type="file" name="template_file" accept=".html">

    <label>Or paste template HTML <span style="color:var(--text-muted);font-weight:400">(use {{name}}, {{email}}, {{unsubscribe_link}})</span></label>
    <textarea name="template_html" rows="12" placeholder="<p>Hi {{name}}, ...</p>"></textarea>

    <button type="submit">Create &amp; queue campaign</button>
</form>

<?php require __DIR__ . '/../includes/footer.php'; ?>
