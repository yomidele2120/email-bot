<?php
require __DIR__ . '/../includes/bootstrap.php';

use App\Auth;
use App\TrialGate;

$loggedIn = Auth::check();

$pageTitle = 'QR Generator';
$activeNav = 'tools_qr';
require __DIR__ . '/../includes/' . ($loggedIn ? 'header.php' : 'public_header.php');
?>

<?php if ($loggedIn): ?><p><a href="/tools.php" style="color:var(--text-muted);font-size:13px">← Tools</a></p><?php endif; ?>
<h1>QR Code Generator</h1>
<p style="color:var(--text-muted)">Paste a link, get a QR code you can download.</p>
<?php if (!$loggedIn): ?>
    <p style="color:var(--text-muted);font-size:13px">Free to try <strong class="mono"><?= TrialGate::usesLeft('qr') ?></strong> more time(s) without an account.</p>
<?php endif; ?>

<div style="max-width:420px;margin-top:24px">
    <label>Link or text</label>
    <input type="text" id="qr-input" placeholder="https://example.com">
    <button type="button" id="qr-generate">Generate QR code</button>

    <div id="qr-result" style="margin-top:24px;display:none;text-align:center">
        <div id="qr-canvas-wrap" style="background:#fff;display:inline-block;padding:16px;border-radius:var(--radius);border:1px solid var(--border)"></div>
        <div style="margin-top:12px">
            <a href="#" id="qr-download" class="btn btn-secondary" download="qr-code.png">Download PNG</a>
        </div>
    </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
<script>
let qr = null;
document.getElementById('qr-generate').addEventListener('click', async () => {
    const value = document.getElementById('qr-input').value.trim();
    if (!value) return;

    const res = await fetch('/trial_check.php?tool=qr');
    const data = await res.json();
    if (!data.allowed) { openPaywall(); return; }

    const wrap = document.getElementById('qr-canvas-wrap');
    wrap.innerHTML = '';
    qr = new QRCode(wrap, { text: value, width: 220, height: 220, colorDark: '#14120F', colorLight: '#ffffff' });

    document.getElementById('qr-result').style.display = 'block';

    setTimeout(() => {
        const canvas = wrap.querySelector('canvas');
        if (canvas) {
            document.getElementById('qr-download').href = canvas.toDataURL('image/png');
        }
    }, 150);
});

document.getElementById('qr-input').addEventListener('keydown', (e) => {
    if (e.key === 'Enter') document.getElementById('qr-generate').click();
});
</script>

<?php require __DIR__ . '/../includes/' . ($loggedIn ? 'footer.php' : 'public_footer.php'); ?>
