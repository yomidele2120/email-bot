<?php
require __DIR__ . '/../includes/bootstrap.php';

use App\Auth;
use App\Contact;
use App\Sequence;
use App\PlanGate;

Auth::requireLogin();
$userId = Auth::id();

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $intervalDays = max(1, (int)($_POST['interval_days'] ?? 1));
    $selectedIds = array_map('intval', $_POST['selected_contacts'] ?? []);

    // A CSV uploaded here works exactly like on the campaign page: it's added
    // to your saved contacts, and automatically included in this sequence's audience.
    if (!empty($_FILES['contacts_csv']['tmp_name'])) {
        $result = Contact::importFromCsv($_FILES['contacts_csv']['tmp_name'], $userId);
        $message .= "Imported {$result['imported']} contacts, skipped {$result['skipped']} invalid rows. ";
        $importedIds = Contact::idsForEmails($userId, $result['emails']);
        $selectedIds = array_merge($selectedIds, $importedIds);
    }
    $selectedIds = array_unique($selectedIds);

    $subjects = $_POST['step_subject'] ?? [];
    $templates = $_POST['step_template'] ?? [];
    $templateFiles = $_FILES['step_template_file']['tmp_name'] ?? [];

    $steps = [];
    foreach ($subjects as $i => $subj) {
        $subj = trim($subj);
        $html = trim($templates[$i] ?? '');

        // A per-step uploaded HTML file takes priority over pasted text, same
        // pattern as the main campaign builder.
        if (!empty($templateFiles[$i]) && is_uploaded_file($templateFiles[$i])) {
            $uploadedHtml = file_get_contents($templateFiles[$i]);
            if ($uploadedHtml) {
                $html = $uploadedHtml;
            }
        }

        if ($subj && $html) {
            $steps[] = ['subject' => $subj, 'template_html' => $html];
        }
    }

    if (!$name) {
        $message .= 'Give this sequence a name.';
    } elseif (empty($selectedIds)) {
        $message .= 'Select at least one contact, or upload a CSV.';
    } elseif (count($steps) < 2) {
        $message .= 'Add at least 2 different messages, that\'s the whole point of a rotation.';
    } elseif (!PlanGate::canCreateSequence($userId)) {
        $message .= 'Your plan only allows ' . PlanGate::limits($userId)['sequences'] . ' active sequence(s). Upgrade on the Billing page to create more.';
    } else {
        $sequenceId = Sequence::create($userId, $name, $intervalDays, $selectedIds, $steps);
        header('Location: /sequences.php?created=1');
        exit;
    }
}

$existingContacts = Contact::allActive($userId);

$pageTitle = 'New Sequence';
$activeNav = 'sequences';
require __DIR__ . '/../includes/header.php';
?>

<p><a href="/sequences.php" style="color:var(--text-muted);font-size:13px">← Sequences</a></p>
<h1>New sequence</h1>
<p style="color:var(--text-muted)">Add a few different messages, pick an audience, and set how often it repeats. It rotates through each message in order, looping back to the first once it reaches the end, until you pause or stop it.</p>

<?php if ($message): ?><div class="alert alert-error"><?= htmlspecialchars($message) ?></div><?php endif; ?>

<form method="POST" enctype="multipart/form-data">
    <div class="builder-card" style="margin-bottom:20px">
        <div class="builder-card-header">
            <span class="builder-step">1</span>
            <div><h2>Sequence details</h2><p>Name it, and set the rhythm.</p></div>
        </div>
        <div class="builder-card-body">
            <label style="margin-top:0">Sequence name</label>
            <input type="text" name="name" required placeholder="e.g. New lead nurture">

            <label>Repeat every</label>
            <select name="interval_days" style="width:100%;padding:8px 12px;border:1px solid var(--border);border-radius:var(--radius);background:var(--bg)">
                <option value="1">Day (a new message goes out daily)</option>
                <option value="2">2 days</option>
                <option value="3">3 days</option>
                <option value="7">Week</option>
            </select>
        </div>
    </div>

    <div class="builder-card" style="margin-bottom:20px">
        <div class="builder-card-header">
            <span class="builder-step">2</span>
            <div><h2>Audience</h2><p>Everyone in the rotation gets each message as it comes up.</p></div>
        </div>
        <div class="builder-card-body">
            <?php if (!empty($existingContacts)): ?>
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
                                    <span style="display:block;font-size:13px"><?= htmlspecialchars($c['email']) ?></span>
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
    </div>

    <div class="builder-card" style="margin-bottom:20px">
        <div class="builder-card-header">
            <span class="builder-step">3</span>
            <div><h2>Messages in rotation</h2><p>Add at least 2. They send in this order, then loop back to the first.</p></div>
        </div>
        <div class="builder-card-body">
            <div id="stepsContainer"></div>
            <button type="button" id="addStepBtn" class="btn btn-secondary" style="margin-top:8px">+ Add another message</button>
        </div>
    </div>

    <button type="submit">Create sequence</button>
</form>

<template id="stepTemplate">
    <div class="sequence-step">
        <div class="sequence-step-header">
            <strong class="step-number"></strong>
            <button type="button" class="icon-btn remove-step" title="Remove this message">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 6 6 18M6 6l12 12"/></svg>
            </button>
        </div>
        <label style="margin-top:12px">Subject</label>
        <input type="text" name="step_subject[]" required placeholder="e.g. Still thinking it over?">

        <label class="dropzone step-dropzone" style="margin-top:10px">
            <input type="file" name="step_template_file[]" accept=".html" class="step-file-input" hidden>
            <div class="dropzone-icon" style="width:36px;height:36px">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><path d="M14 2v6h6"/></svg>
            </div>
            <div class="dropzone-text">
                <strong class="step-file-label" style="font-size:13px">Upload HTML file for this message</strong>
                <span>optional if pasting below</span>
            </div>
        </label>

        <label>Or paste template HTML <span style="color:var(--text-muted);font-weight:400">(use {{name}}, {{email}}, {{unsubscribe_link}})</span></label>
        <textarea name="step_template[]" class="step-template-textarea" rows="8" placeholder="<p>Hi {{name}}, ...</p>"></textarea>
        <p style="font-size:12px;margin-top:6px"><a href="/tools_ai_template.php" target="_blank">Don't know HTML? Generate a template →</a> (opens in a new tab, copy the result back here)</p>
    </div>
</template>

<script>
// Contact selection
const selectAll = document.getElementById('selectAll');
const checkboxes = document.querySelectorAll('.contact-checkbox');
const counter = document.getElementById('selectedCount');
function updateCounter() {
    if (counter) counter.textContent = document.querySelectorAll('.contact-checkbox:checked').length + ' selected';
}
if (selectAll) {
    selectAll.addEventListener('change', () => { checkboxes.forEach(cb => cb.checked = selectAll.checked); updateCounter(); });
    checkboxes.forEach(cb => cb.addEventListener('change', () => { if (!cb.checked) selectAll.checked = false; updateCounter(); }));
}

// CSV dropzone
const csvDz = document.getElementById('csvDropzone');
const csvInput = document.getElementById('csvInput');
const csvLabel = document.getElementById('csvLabel');
csvInput.addEventListener('change', () => { if (csvInput.files[0]) csvLabel.textContent = csvInput.files[0].name; });
['dragover', 'dragenter'].forEach(evt => csvDz.addEventListener(evt, (e) => { e.preventDefault(); csvDz.classList.add('dropzone-active'); }));
['dragleave', 'drop'].forEach(evt => csvDz.addEventListener(evt, (e) => { e.preventDefault(); csvDz.classList.remove('dropzone-active'); }));
csvDz.addEventListener('drop', (e) => { if (e.dataTransfer.files[0]) { csvInput.files = e.dataTransfer.files; csvLabel.textContent = e.dataTransfer.files[0].name; } });

// Dynamic message steps
const stepsContainer = document.getElementById('stepsContainer');
const stepTemplate = document.getElementById('stepTemplate');
let stepCount = 0;

function addStep() {
    stepCount++;
    const clone = stepTemplate.content.cloneNode(true);
    clone.querySelector('.step-number').textContent = 'Message ' + stepCount;

    const fileInput = clone.querySelector('.step-file-input');
    const fileLabel = clone.querySelector('.step-file-label');
    const dz = clone.querySelector('.step-dropzone');

    fileInput.addEventListener('change', () => { if (fileInput.files[0]) fileLabel.textContent = fileInput.files[0].name; });
    ['dragover', 'dragenter'].forEach(evt => dz.addEventListener(evt, (e) => { e.preventDefault(); dz.classList.add('dropzone-active'); }));
    ['dragleave', 'drop'].forEach(evt => dz.addEventListener(evt, (e) => { e.preventDefault(); dz.classList.remove('dropzone-active'); }));
    dz.addEventListener('drop', (e) => { if (e.dataTransfer.files[0]) { fileInput.files = e.dataTransfer.files; fileLabel.textContent = e.dataTransfer.files[0].name; } });

    clone.querySelector('.remove-step').addEventListener('click', function() {
        this.closest('.sequence-step').remove();
        renumberSteps();
    });
    stepsContainer.appendChild(clone);
}

function renumberSteps() {
    const steps = stepsContainer.querySelectorAll('.sequence-step');
    steps.forEach((step, i) => { step.querySelector('.step-number').textContent = 'Message ' + (i + 1); });
    stepCount = steps.length;
}

document.getElementById('addStepBtn').addEventListener('click', addStep);
addStep();
addStep(); // Start with 2, since a rotation needs at least 2
</script>

<?php require __DIR__ . '/../includes/footer.php'; ?>
