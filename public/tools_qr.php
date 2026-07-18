<?php
require __DIR__ . '/../includes/bootstrap.php';

use App\Auth;

Auth::requireLogin();

$pageTitle = 'QR Generator';
$activeNav = 'tools_qr';
require __DIR__ . '/../includes/header.php';
?>

<p><a href="/tools.php" style="color:var(--text-muted);font-size:13px">← Tools</a></p>
<h1>QR Code Generator</h1>
<p style="color:var(--text-muted)">Paste a link, get a QR code you can download.</p>

<div style="max-width:420px;margin-top:24px">
    <label>Link or text</label>
    <input type="text" id="qr-input" placeholder="https://example.com">
    <button type="button" id="qr-generate">Generate QR code</button>

    <div id="qr-result" style="margin-top:24px;display:none;text-align:center">
        <div id="qr-canvas-wrap" style="background:#fff;display:inline-block;padding:16px;border-radius:var(--radius)"></div>
        <div style="margin-top:12px">
            <a href="#" id="qr-download" class="btn btn-secondary" download="qr-code.png">Download PNG</a>
        </div>
    </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
<script>
let qr = null;
document.getElementById('qr-generate').addEventListener('click', () => {
    const value = document.getElementById('qr-input').value.trim();
    if (!value) return;

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

<?php require __DIR__ . '/../includes/footer.php'; ?>
