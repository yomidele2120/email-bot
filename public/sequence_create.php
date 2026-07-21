<?php
require __DIR__ . '/../includes/bootstrap.php';

use App\Auth;
use App\Contact;
use App\Sequence;

Auth::requireLogin();
$userId = Auth::id();

$message = '';
$messageType = 'error';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $intervalDays = max(1, (int)($_POST['interval_days'] ?? 1));
    $contactIds = array_map('intval', $_POST['selected_contacts'] ?? []);
    $subjects = $_POST['step_subject'] ?? [];
    $templates = $_POST['step_template'] ?? [];

    $steps = [];
    foreach ($subjects as $i => $subj) {
        $subj = trim($subj);
        $html = trim($templates[$i] ?? '');
        if ($subj && $html) {
            $steps[] = ['subject' => $subj, 'template_html' => $html];
        }
    }

    if (!$name) {
        $message = 'Give this sequence a name.';
    } elseif (empty($contactIds)) {
        $message = 'Select at least one contact.';
    } elseif (count($steps) < 2) {
        $message = 'Add at least 2 different messages, that\'s the whole point of a rotation.';
    } else {
        $sequenceId = Sequence::create($userId, $name, $intervalDays, $contactIds, $steps);
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
<p style="color:var(--text-muted)">Add a few different messages, pick an audience, and set how often it repeats. It'll rotate through each message in order, looping back to the first once it reaches the end, until you pause or stop it.</p>

<?php if ($message): ?><div class="alert alert-error"><?= htmlspecialchars($message) ?></div><?php endif; ?>

<form method="POST">
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
            <?php if (empty($existingContacts)): ?>
                <div class="empty-state" style="padding:24px">
                    <p style="margin:0">You don't have any contacts yet. <a href="/campaign_create.php">Upload some first</a>, then come back here.</p>
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
                                    <span style="display:block;font-size:13px"><?= htmlspecialchars($c['email']) ?></span>
                                    <span style="display:block;font-size:12px;color:var(--text-muted)"><?= htmlspecialchars($c['name'] ?: 'No name') ?></span>
                                </span>
                            </label>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>
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
        <label>Template HTML</label>
        <textarea name="step_template[]" rows="8" required placeholder="<p>Hi {{name}}, ...</p>"></textarea>
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

// Dynamic message steps
const stepsContainer = document.getElementById('stepsContainer');
const stepTemplate = document.getElementById('stepTemplate');
let stepCount = 0;

function addStep() {
    stepCount++;
    const clone = stepTemplate.content.cloneNode(true);
    clone.querySelector('.step-number').textContent = 'Message ' + stepCount;
    clone.querySelector('.remove-step').addEventListener('click', function() {
        this.closest('.sequence-step').remove();
        renumberSteps();
    });
    stepsContainer.appendChild(clone);
}

function renumberSteps() {
    const steps = stepsContainer.querySelectorAll('.sequence-step');
    steps.forEach((step, i) => {
        step.querySelector('.step-number').textContent = 'Message ' + (i + 1);
    });
    stepCount = steps.length;
}

document.getElementById('addStepBtn').addEventListener('click', addStep);
addStep();
addStep(); // Start with 2, since a rotation needs at least 2
</script>

<?php require __DIR__ . '/../includes/footer.php'; ?>
