<?php
require __DIR__ . '/../includes/bootstrap.php';

use App\Auth;

Auth::requireLogin();

$generatedHtml = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $purpose = trim($_POST['purpose'] ?? '');
    $tone = trim($_POST['tone'] ?? 'friendly and professional');
    $message = trim($_POST['message'] ?? '');
    $ctaText = trim($_POST['cta_text'] ?? '');
    $ctaUrl = trim($_POST['cta_url'] ?? '');
    $brandColor = trim($_POST['brand_color'] ?? '#0969da');
    $businessName = trim($_POST['business_name'] ?? '');

    $apiKey = $_ENV['GEMINI_API_KEY'] ?? '';

    if (!$apiKey) {
        $error = 'This feature needs a GEMINI_API_KEY set up on the server. Ask the site owner to add one in Railway (it\'s free at aistudio.google.com).';
    } elseif (!$purpose || !$message) {
        $error = 'Tell us what the email is for and what it should say.';
    } else {
        $userPrompt = <<<PROMPT
Write an HTML email template for this business:

Business/sender name: {$businessName}
Purpose of this email: {$purpose}
Tone: {$tone}
Key message / what it should say: {$message}
Call-to-action button text: {$ctaText}
Call-to-action link: {$ctaUrl}
Primary brand color: {$brandColor}

Requirements:
- Output ONLY raw HTML, no markdown code fences, no explanation before or after.
- Use a table-based layout (role="presentation" tables), not divs, for email client compatibility (Outlook desktop specifically).
- Include a full HTML document: <!DOCTYPE html>, <html>, <head> with charset meta tag, <body>.
- Use inline CSS styles on every element, do not rely on a <style> block.
- Include the literal placeholder {{name}} somewhere in the greeting so it can be personalized per recipient.
- Include the literal placeholder {{unsubscribe_link}} as a real link in the footer.
- Use the given brand color for headings and the CTA button.
- Keep it genuinely well designed: clear hierarchy, generous padding, a real CTA button (not just a text link), readable font sizes.
- Max width around 600px, centered.
PROMPT;

        $payload = [
            'contents' => [
                ['parts' => [['text' => $userPrompt]]],
            ],
        ];

        $ch = curl_init('https://generativelanguage.googleapis.com/v1beta/models/gemini-3.5-flash:generateContent');
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_HTTPHEADER => [
                'x-goog-api-key: ' . $apiKey,
                'Content-Type: application/json',
            ],
            CURLOPT_POSTFIELDS => json_encode($payload),
            CURLOPT_TIMEOUT => 40,
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);

        if ($curlError) {
            $error = 'Connection error: ' . $curlError;
        } elseif ($httpCode !== 200) {
            $error = "The AI service returned an error (HTTP $httpCode). Check the GEMINI_API_KEY is valid.";
        } else {
            $data = json_decode($response, true);
            $text = $data['candidates'][0]['content']['parts'][0]['text'] ?? '';
            // Strip accidental markdown code fences if the model adds them anyway
            $text = preg_replace('/^```(?:html)?\s*/i', '', trim($text));
            $text = preg_replace('/```\s*$/', '', $text);
            $generatedHtml = trim($text);
        }
    }
}

$pageTitle = 'AI Template Generator';
$activeNav = 'new_campaign';
require __DIR__ . '/../includes/header.php';
?>

<p><a href="/campaign_create.php" style="color:var(--text-muted);font-size:13px">← Back to campaign</a></p>
<h1>Generate a template</h1>
<p style="color:var(--text-muted)">Answer a few questions, get ready-to-use HTML for your campaign. No coding needed.</p>

<?php if ($error): ?><div class="alert alert-error"><?= htmlspecialchars($error) ?></div><?php endif; ?>

<?php if ($generatedHtml): ?>
    <div class="result-panel" style="align-items:flex-start">
        <div class="result-panel-icon"><svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="#1a7f37" stroke-width="2"><path d="M20 6 9 17l-5-5"/></svg></div>
        <div class="result-panel-info">
            <strong>Template ready</strong>
            <span>Preview below, copy it into your campaign or download it.</span>
        </div>
        <button type="button" class="btn" style="margin:0" onclick="copyTemplate()">Copy to clipboard</button>
    </div>

    <div class="tool-panel" style="padding:0;overflow:hidden;margin-bottom:20px">
        <div style="padding:8px 14px;background:var(--surface);border-bottom:1px solid var(--border);font-size:12px;color:var(--text-muted)">Preview</div>
        <iframe class="preview-frame" style="height:400px" srcdoc="<?= htmlspecialchars(str_replace(['{{name}}', '{{unsubscribe_link}}'], ['Jane', '#'], $generatedHtml)) ?>"></iframe>
    </div>

    <textarea id="generatedTemplate" readonly rows="14" style="font-family:'JetBrains Mono',monospace;font-size:12px;margin-bottom:24px"><?= htmlspecialchars($generatedHtml) ?></textarea>

    <a href="/campaign_create.php" class="btn">Use this template on a campaign</a>
<?php endif; ?>

<form method="POST" class="tool-panel" style="max-width:600px;margin-top:24px">
    <label style="margin-top:0">Your business or sender name</label>
    <input type="text" name="business_name" placeholder="e.g. YomiConnect" value="<?= htmlspecialchars($_POST['business_name'] ?? '') ?>">

    <label>What's this email for?</label>
    <input type="text" name="purpose" required placeholder="e.g. Announcing a new feature, a promo discount, a welcome email" value="<?= htmlspecialchars($_POST['purpose'] ?? '') ?>">

    <label>What should it say?</label>
    <textarea name="message" rows="4" required placeholder="Describe the key message in a sentence or two, in your own words"><?= htmlspecialchars($_POST['message'] ?? '') ?></textarea>

    <label>Tone</label>
    <select name="tone" style="width:100%;padding:8px 12px;border:1px solid var(--border);border-radius:var(--radius);background:var(--bg)">
        <option>friendly and professional</option>
        <option>bold and exciting</option>
        <option>warm and personal</option>
        <option>formal and corporate</option>
        <option>playful and casual</option>
    </select>

    <label>Button text</label>
    <input type="text" name="cta_text" placeholder="e.g. Get Started" value="<?= htmlspecialchars($_POST['cta_text'] ?? '') ?>">

    <label>Button link</label>
    <input type="text" name="cta_url" placeholder="https://yoursite.com" value="<?= htmlspecialchars($_POST['cta_url'] ?? '') ?>">

    <label>Brand color</label>
    <input type="text" name="brand_color" value="<?= htmlspecialchars($_POST['brand_color'] ?? '#0969da') ?>" placeholder="#0969da">

    <button type="submit">Generate template</button>
</form>

<script>
function copyTemplate() {
    const text = document.getElementById('generatedTemplate').value;
    navigator.clipboard.writeText(text);
    event.target.textContent = 'Copied!';
}
</script>

<?php require __DIR__ . '/../includes/footer.php'; ?>
