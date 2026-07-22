<?php
require __DIR__ . '/../includes/bootstrap.php';

use App\Auth;
use App\TrialGate;
use App\PlanGate;
use App\Plan;

$loggedIn = Auth::check();
$showBranding = true;
if ($loggedIn) {
    $showBranding = Plan::config(PlanGate::currentPlan(Auth::id()))['branding'];
}

$pageTitle = 'QR Generator';
$activeNav = 'tools_qr';
require __DIR__ . '/../includes/' . ($loggedIn ? 'header.php' : 'public_header.php');
?>

<?php if ($loggedIn): ?><p><a href="/tools.php" style="color:var(--text-muted);font-size:13px">← Tools</a></p><?php endif; ?>
<?php
$toolTitle = 'QR Code Generator';
$toolDesc = 'Paste a link, get a QR code you can download.';
require __DIR__ . '/../includes/tool_header.php';
?>
<?php if (!$loggedIn): ?>
    <p style="color:var(--text-muted);font-size:13px">Free to try <strong class="mono"><?= TrialGate::usesLeft('qr') ?></strong> more time(s) without an account.</p>
<?php endif; ?>

<div class="tool-panel" style="max-width:420px;margin-top:24px">
    <label>Link or text</label>
    <input type="text" id="qr-input" placeholder="https://example.com">
    <button type="button" id="qr-generate" class="btn" style="width:100%">Generate QR code</button>

    <div id="qr-result" style="margin-top:24px;display:none;text-align:center">
        <div id="qr-canvas-wrap" style="background:#fff;display:inline-block;padding:16px;border-radius:var(--radius);border:1px solid var(--border)"></div>
        <div id="qr-caption" style="margin-top:6px;font-size:11px;color:var(--text-muted)"></div>
        <div style="margin-top:12px">
            <a href="#" id="qr-download" class="btn btn-secondary" download="qr-code.png">Download PNG</a>
        </div>
    </div>
</div>

<?php if ($loggedIn && $showBranding): ?>
    <p style="color:var(--text-muted);font-size:13px;max-width:420px">Downloaded QR codes include a small "Powered by" watermark on the free plan. <a href="/billing.php">Upgrade</a> to remove it.</p>
<?php endif; ?>

<script src="https://cdnjs.cloudflare.com/ajax/libs/qrcode-generator/1.4.4/qrcode.min.js"></script>
<script>
const QR_SHOW_BRANDING = <?= $showBranding ? 'true' : 'false' ?>;

document.getElementById('qr-generate').addEventListener('click', async () => {
    const value = document.getElementById('qr-input').value.trim();
    if (!value) return;

    const res = await fetch('/trial_check.php?tool=qr');
    const data = await res.json();
    if (!data.allowed) { openPaywall(); return; }

    // typeNumber 0 = auto-detect the smallest QR version that fits the data.
    // Error correction level 'M' (15% recovery) is the standard default for scannability.
    const qr = qrcode(0, 'M');
    qr.addData(value);
    qr.make();

    // cellSize 6, margin 16 -> a proper quiet zone around the code, which scanners rely on
    const dataUrl = qr.createDataURL(6, 16);

    const wrap = document.getElementById('qr-canvas-wrap');
    wrap.innerHTML = '<img src="' + dataUrl + '" width="220" height="220" alt="QR code">';

    const caption = document.getElementById('qr-caption');
    caption.textContent = QR_SHOW_BRANDING ? 'Powered by Email Bot' : '';

    if (QR_SHOW_BRANDING) {
        // Stamp the watermark into the downloaded PNG itself, not just the on-screen caption,
        // so the branding travels with the file once it's downloaded.
        const canvas = document.createElement('canvas');
        const img = new Image();
        img.onload = () => {
            const pad = 22;
            canvas.width = img.width;
            canvas.height = img.height + pad;
            const ctx = canvas.getContext('2d');
            ctx.fillStyle = '#ffffff';
            ctx.fillRect(0, 0, canvas.width, canvas.height);
            ctx.drawImage(img, 0, 0);
            ctx.fillStyle = '#656d76';
            ctx.font = '12px Inter, sans-serif';
            ctx.textAlign = 'center';
            ctx.fillText('Powered by Email Bot', canvas.width / 2, canvas.height - 6);
            document.getElementById('qr-download').href = canvas.toDataURL('image/png');
        };
        img.src = dataUrl;
    } else {
        document.getElementById('qr-download').href = dataUrl;
    }

    document.getElementById('qr-result').style.display = 'block';
});

document.getElementById('qr-input').addEventListener('keydown', (e) => {
    if (e.key === 'Enter') document.getElementById('qr-generate').click();
});
</script>

<?php require __DIR__ . '/../includes/' . ($loggedIn ? 'footer.php' : 'public_footer.php'); ?>
