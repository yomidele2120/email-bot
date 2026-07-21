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
    $selectedIds = array_map('intval', $_POST['selected_contacts'] ?? []);

    if (!empty($_FILES['contacts_csv']['tmp_name'])) {
        $result = Contact::importFromCsv($_FILES['contacts_csv']['tmp_name'], $userId);
        $message .= "Imported {$result['imported']} contacts, skipped {$result['skipped']} invalid rows. ";
        $importedIds = Contact::idsForEmails($userId, $result['emails']);
        $selectedIds = array_merge($selectedIds, $importedIds);
    }

    $templateHtml = '';
    if (!empty($_FILES['template_file']['tmp_name'])) {
        $templateHtml = file_get_contents($_FILES['template_file']['tmp_name']);
    } elseif (!empty($_POST['template_html'])) {
        $templateHtml = $_POST['template_html'];
    }

    $subject = trim($_POST['subject'] ?? '');
    $selectedIds = array_unique($selectedIds);

    if (empty($selectedIds)) {
        $message .= 'Select at least one contact from your list, or upload a new CSV.';
        $messageType = 'error';
    } elseif (!$templateHtml || !$subject) {
        $message .= 'A subject and a template are both required.';
        $messageType = 'error';
    } else {
        $campaignId = Campaign::create($userId, $subject, $templateHtml);
        $count = Campaign::queueSelected($campaignId, $selectedIds);
        $message .= "Campaign created and queued for $count contact(s). It'll go out in batches shortly.";
    }
}

$existingContacts = Contact::allActive($userId);

$pageTitle = 'New Campaign';
$activeNav = 'new_campaign';
require __DIR__ . '/../includes/header.php';
?>

<h1>New campaign</h1>
<p style="color:var(--text-muted)">Pick who this goes to, write your email, send it out.</p>

<?php if ($message): ?>
    <div class="alert alert-<?= $messageType ?>"><?= htmlspecialchars($message) ?></div>
<?php endif; ?>

<form method="POST" enctype="multipart/form-data" id="campaignForm">
<div class="builder-grid">
  <div class="builder-main">

    <!-- Section 1: Audience -->
    <section class="builder-card">
        <div class="builder-card-header">
            <span class="builder-step">1</span>
            <div>
                <h2>Audience</h2>
                <p>Choose who receives this campaign.</p>
            </div>
        </div>
        <div class="builder-card-body">
            <?php if (empty($existingContacts)): ?>
                <div class="empty-state" style="padding:24px">
                    <p style="margin:0">You don't have any contacts yet. Upload a CSV below, they'll be included in this campaign automatically.</p>
                </div>
            <?php else: ?>
                <div class="contact-picker">
                    <div class="contact-picker-header">
                        <label style="display:flex;align-items:center;gap:8px;margin:0;font-weight:500;font-size:13px">
                            <input type="checkbox" id="selectAll" style="width:auto"> Select all (<?= count($existingContacts) ?>)
                        </label>
                        <span id="selectedCount" class="mono" style="font-size:12px;color:var(--text-muted)">0 selected</span>
                    </div>
                    <div class="contact-picker-list">
                        <?php foreach ($existingContacts as $c): ?>
                            <label class="contact-picker-row">
                                <input type="checkbox" name="selected_contacts[]" value="<?= $c['id'] ?>" class="contact-checkbox" style="width:auto">
                                <span class="contact-picker-avatar"><?= strtoupper(substr($c['name'] ?: $c['email'], 0, 1)) ?></span>
                                <span style="min-width:0">
                                    <span style="display:block;font-size:13px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap"><?= htmlspecialchars($c['email']) ?></span>
                                    <span style="display:block;font-size:12px;color:var(--text-muted)"><?= htmlspecialchars($c['name'] ?: 'No name') ?></span>
                                </span>
                            </label>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>

            <label class="dropzone" for="csvInput" id="csvDropzone" style="margin-top:16px">
                <input type="file" name="contacts_csv" id="csvInput" accept=".csv" hidden>
                <div class="dropzone-icon">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><path d="M17 8l-5-5-5 5"/><path d="M12 3v12"/></svg>
                </div>
                <div class="dropzone-text">
                    <strong id="csvLabel">Or upload a new contact list</strong>
                    <span>CSV with email, name columns — added to your contacts too</span>
                </div>
            </label>
        </div>
    </section>

    <!-- Section 2: Content -->
    <section class="builder-card">
        <div class="builder-card-header">
            <span class="builder-step">2</span>
            <div>
                <h2>Email content</h2>
                <p>Subject line and the message itself.</p>
            </div>
        </div>
        <div class="builder-card-body">
            <label style="margin-top:0">Subject line</label>
            <input type="text" name="subject" id="subjectInput" required placeholder="e.g. Your July update from us">

            <label class="dropzone" for="templateFileInput" id="templateDropzone" style="margin-top:16px">
                <input type="file" name="template_file" id="templateFileInput" accept=".html" hidden>
                <div class="dropzone-icon">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><path d="M14 2v6h6"/></svg>
                </div>
                <div class="dropzone-text">
                    <strong id="templateLabel">Upload a template HTML file</strong>
                    <span>optional if pasting below</span>
                </div>
            </label>

            <label>Or paste template HTML <span style="color:var(--text-muted);font-weight:400">(use {{name}}, {{email}}, {{unsubscribe_link}})</span></label>
            <textarea name="template_html" id="templateInput" rows="12" placeholder="<p>Hi {{name}}, ...</p>"></textarea>
            <p style="font-size:13px;margin-top:8px">Don't know HTML? <a href="/tools_ai_template.php">Generate a template instead →</a></p>
        </div>
    </section>

    <!-- Section 3: Preview -->
    <section class="builder-card">
        <div class="builder-card-header">
            <span class="builder-step">3</span>
            <div>
                <h2>Preview</h2>
                <p>Roughly how this will render in an inbox.</p>
            </div>
        </div>
        <div class="builder-card-body" style="padding:0">
            <iframe id="previewFrame" class="preview-frame" title="Email preview"></iframe>
        </div>
    </section>

  </div>

  <!-- Sticky summary sidebar -->
  <aside class="builder-sidebar">
    <div class="builder-summary">
        <h3 style="margin-top:0">Ready to send?</h3>
        <div class="builder-summary-row">
            <span>Recipients</span>
            <strong id="summaryCount" class="mono">0</strong>
        </div>
        <div class="builder-summary-row">
            <span>Subject</span>
            <strong id="summarySubject" style="max-width:160px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;text-align:right">—</strong>
        </div>
        <button type="submit" form="campaignForm" class="btn" style="width:100%">Create &amp; queue campaign</button>
        <p style="font-size:12px;color:var(--text-muted);margin-top:12px">Sends go out in small batches, not all at once.</p>
    </div>
  </aside>
</div>
</form>

<script>
// Contact selection
const selectAll = document.getElementById('selectAll');
const checkboxes = document.querySelectorAll('.contact-checkbox');
const counter = document.getElementById('selectedCount');
const summaryCount = document.getElementById('summaryCount');

function updateCounter() {
    const checked = document.querySelectorAll('.contact-checkbox:checked').length;
    if (counter) counter.textContent = checked + ' selected';
    summaryCount.textContent = checked;
}

if (selectAll) {
    selectAll.addEventListener('change', () => {
        checkboxes.forEach(cb => cb.checked = selectAll.checked);
        updateCounter();
    });
    checkboxes.forEach(cb => cb.addEventListener('change', () => {
        if (!cb.checked) selectAll.checked = false;
        updateCounter();
    }));
}

// Subject summary
const subjectInput = document.getElementById('subjectInput');
const summarySubject = document.getElementById('summarySubject');
subjectInput.addEventListener('input', () => {
    summarySubject.textContent = subjectInput.value || '—';
});

// Live preview
const templateInput = document.getElementById('templateInput');
const previewFrame = document.getElementById('previewFrame');
function updatePreview() {
    const html = templateInput.value || '<p style="color:#999;font-family:sans-serif;padding:20px">Your email preview will appear here as you type.</p>';
    previewFrame.srcdoc = html.replace(/\{\{name\}\}/g, 'Jane').replace(/\{\{email\}\}/g, 'jane@example.com').replace(/\{\{unsubscribe_link\}\}/g, '#');
}
templateInput.addEventListener('input', updatePreview);
updatePreview();

// Dropzone helpers (CSV + template file)
function wireDropzone(dzId, inputId, labelId) {
    const dz = document.getElementById(dzId);
    const input = document.getElementById(inputId);
    const label = document.getElementById(labelId);
    input.addEventListener('change', () => { if (input.files[0]) label.textContent = input.files[0].name; });
    ['dragover', 'dragenter'].forEach(evt => dz.addEventListener(evt, (e) => { e.preventDefault(); dz.classList.add('dropzone-active'); }));
    ['dragleave', 'drop'].forEach(evt => dz.addEventListener(evt, (e) => { e.preventDefault(); dz.classList.remove('dropzone-active'); }));
    dz.addEventListener('drop', (e) => {
        if (e.dataTransfer.files[0]) { input.files = e.dataTransfer.files; label.textContent = e.dataTransfer.files[0].name; }
    });
}
wireDropzone('csvDropzone', 'csvInput', 'csvLabel');
wireDropzone('templateDropzone', 'templateFileInput', 'templateLabel');
</script>

<?php require __DIR__ . '/../includes/footer.php'; ?>
