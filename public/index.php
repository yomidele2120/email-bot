<?php
// public/index.php
require __DIR__ . '/../vendor/autoload.php';

use App\Contact;
use App\Campaign;

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->safeLoad(); // won't error if .env is missing (e.g. on Railway, where vars come from the environment directly)

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 1. Import contacts CSV
    if (!empty($_FILES['contacts_csv']['tmp_name'])) {
        $result = Contact::importFromCsv($_FILES['contacts_csv']['tmp_name']);
        $message .= "Imported {$result['imported']} contacts, skipped {$result['skipped']} invalid rows. ";
    }

    // 2. Get template: either uploaded HTML file or pasted text
    $templateHtml = '';
    if (!empty($_FILES['template_file']['tmp_name'])) {
        $templateHtml = file_get_contents($_FILES['template_file']['tmp_name']);
    } elseif (!empty($_POST['template_html'])) {
        $templateHtml = $_POST['template_html'];
    }

    $subject = trim($_POST['subject'] ?? '');

    if ($templateHtml && $subject) {
        $campaignId = Campaign::create($subject, $templateHtml);
        $count = Campaign::queueAll($campaignId);
        $message .= "Campaign #$campaignId created and queued for $count contacts. The cron worker will send it in batches.";
    } else {
        $message .= "Please provide a subject and a template.";
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Email Bot</title>
    <style>
        body { font-family: sans-serif; max-width: 600px; margin: 40px auto; padding: 0 20px; }
        label { display: block; margin-top: 16px; font-weight: bold; }
        input, textarea { width: 100%; padding: 8px; margin-top: 4px; box-sizing: border-box; }
        button { margin-top: 20px; padding: 10px 20px; background: #222; color: #fff; border: none; cursor: pointer; }
        .msg { background: #eef; padding: 12px; margin-bottom: 16px; }
    </style>
</head>
<body>
    <h1>Email Campaign Bot</h1>
    <?php if ($message): ?><div class="msg"><?= htmlspecialchars($message) ?></div><?php endif; ?>

    <form method="POST" enctype="multipart/form-data">
        <label>Contacts CSV (columns: email, name, ...custom fields)</label>
        <input type="file" name="contacts_csv" accept=".csv">

        <label>Email Subject</label>
        <input type="text" name="subject" required>

        <label>Template HTML file (optional if pasting below)</label>
        <input type="file" name="template_file" accept=".html">

        <label>Or paste template HTML (use {{name}}, {{email}}, {{unsubscribe_link}})</label>
        <textarea name="template_html" rows="10"></textarea>

        <button type="submit">Create & Queue Campaign</button>
    </form>
</body>
</html>
