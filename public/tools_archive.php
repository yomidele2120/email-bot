<?php
require __DIR__ . '/../includes/bootstrap.php';

use App\Auth;
use App\TrialGate;

$loggedIn = Auth::check();
$mode = $_GET['mode'] ?? 'zip';
$labels = ['zip' => 'ZIP', 'tar' => 'TAR', 'targz' => 'TAR.GZ', 'jar' => 'JAR'];
$modeLabel = $labels[$mode] ?? 'Archive';

$message = '';
$messageType = 'error';
$downloadUrl = '';
$downloadName = '';
$showPaywall = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_FILES['archive']['tmp_name'])) {
    if (!TrialGate::check('archive')) {
        $showPaywall = true;
    } else {
        $tmpUpload = $_FILES['archive']['tmp_name'];
        $originalName = $_FILES['archive']['name'];
        $workDir = sys_get_temp_dir() . '/archive_' . bin2hex(random_bytes(6));
        mkdir($workDir);

        try {
            $ext = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
            $extractDir = $workDir . '/extracted';
            mkdir($extractDir);

            if ($ext === 'zip' || $ext === 'jar') {
                $zip = new ZipArchive();
                if ($zip->open($tmpUpload) !== true) {
                    throw new Exception('Could not open this file as a ZIP/JAR archive.');
                }
                $zip->extractTo($extractDir);
                $zip->close();
            } else {
                // .tar, .tar.gz, .tgz all handled by PharData
                $localCopy = $workDir . '/' . preg_replace('/[^a-zA-Z0-9._-]/', '_', $originalName);
                move_uploaded_file($tmpUpload, $localCopy);
                $phar = new PharData($localCopy);
                if (preg_match('/\.(tar\.gz|tgz)$/i', $originalName) || $phar->isCompressed(Phar::GZ)) {
                    $phar = $phar->decompress(); // creates a sibling .tar
                }
                $phar->extractTo($extractDir);
            }

            // Repackage the extracted contents as a clean ZIP for download
            $outputName = pathinfo($originalName, PATHINFO_FILENAME) . '-converted.zip';
            $outputPath = $workDir . '/' . $outputName;
            $outZip = new ZipArchive();
            $outZip->open($outputPath, ZipArchive::CREATE);

            $files = new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator($extractDir, RecursiveDirectoryIterator::SKIP_DOTS)
            );
            foreach ($files as $file) {
                if ($file->isDir()) continue;
                $relativePath = substr($file->getPathname(), strlen($extractDir) + 1);
                $outZip->addFile($file->getPathname(), $relativePath);
            }
            $outZip->close();

            // Move to a short-lived public download slot
            $downloadsDir = __DIR__ . '/downloads';
            if (!is_dir($downloadsDir)) mkdir($downloadsDir, 0755, true);
            $token = bin2hex(random_bytes(8));
            $finalPath = $downloadsDir . '/' . $token . '.zip';
            rename($outputPath, $finalPath);

            $_SESSION['archive_downloads'][$token] = ['name' => $outputName, 'expires' => time() + 600];
            $downloadUrl = '/download_archive.php?t=' . $token;
            $downloadName = $outputName;
            $message = 'Converted successfully. Your download is ready.';
            $messageType = 'success';
        } catch (Exception $e) {
            $message = 'Could not process that file: ' . $e->getMessage();
        }
    }
}

$pageTitle = 'Archive Converter';
$activeNav = 'tools_archive';
$allowAds = true;
require __DIR__ . '/../includes/' . ($loggedIn ? 'header.php' : 'public_header.php');
?>

<?php if ($loggedIn): ?><p><a href="/tools.php" style="color:var(--text-muted);font-size:13px">← Tools</a></p><?php endif; ?>
<?php
$toolTitle = 'Archive Converter';
$toolDesc = 'Upload a ZIP, TAR, TAR.GZ, or JAR file and get a clean ZIP back. Format: <strong>' . htmlspecialchars($modeLabel) . '</strong>.';
require __DIR__ . '/../includes/tool_header.php';
?>
<?php if (!$loggedIn): ?>
    <p style="color:var(--text-muted);font-size:13px">Free to try <strong class="mono"><?= TrialGate::usesLeft('archive') ?></strong> more time(s) without an account.</p>
<?php endif; ?>

<?php if ($message): ?>
    <div class="alert alert-<?= $messageType ?>">
        <?= htmlspecialchars($message) ?>
    </div>
<?php endif; ?>

<?php if ($downloadUrl): ?>
    <div class="result-panel">
        <div class="result-panel-icon">
            <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="#1a7f37" stroke-width="2"><path d="M20 6 9 17l-5-5"/></svg>
        </div>
        <div class="result-panel-info">
            <strong><?= htmlspecialchars($downloadName) ?></strong>
            <span>Ready to save</span>
        </div>
        <button type="button" class="btn" style="margin:0" onclick="downloadWithPicker('<?= htmlspecialchars($downloadUrl) ?>', '<?= htmlspecialchars($downloadName) ?>')">Choose folder &amp; save</button>
    </div>
<?php endif; ?>

<form method="POST" enctype="multipart/form-data" id="archiveForm">
    <label class="dropzone" for="archiveInput" id="archiveDropzone">
        <input type="file" name="archive" id="archiveInput" accept=".zip,.tar,.tar.gz,.tgz,.jar" required hidden>
        <div class="dropzone-icon">
            <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><path d="M17 8l-5-5-5 5"/><path d="M12 3v12"/></svg>
        </div>
        <div class="dropzone-text">
            <strong id="dropzoneLabel">Select a file to convert</strong>
            <span>or drag and drop it here — ZIP, TAR, TAR.GZ, JAR</span>
        </div>
    </label>
    <button type="submit" class="btn" style="width:100%">Convert now</button>
</form>

<p style="margin-top:24px;font-size:12px;color:var(--text-muted)">RAR, 7Z, and other formats aren't supported yet, those need extra system tools we haven't wired up. This handles ZIP, TAR, TAR.GZ, and JAR.</p>

<script>
const dz = document.getElementById('archiveDropzone');
const input = document.getElementById('archiveInput');
const label = document.getElementById('dropzoneLabel');

input.addEventListener('change', () => {
    if (input.files[0]) label.textContent = input.files[0].name;
});

['dragover', 'dragenter'].forEach(evt => dz.addEventListener(evt, (e) => {
    e.preventDefault();
    dz.classList.add('dropzone-active');
}));
['dragleave', 'drop'].forEach(evt => dz.addEventListener(evt, (e) => {
    e.preventDefault();
    dz.classList.remove('dropzone-active');
}));
dz.addEventListener('drop', (e) => {
    if (e.dataTransfer.files[0]) {
        input.files = e.dataTransfer.files;
        label.textContent = e.dataTransfer.files[0].name;
    }
});

// Lets the browser prompt "choose folder / save as" instead of a silent auto-download,
// in browsers that support the File System Access API (Chrome, Edge). Falls back to a
// normal download elsewhere.
async function downloadWithPicker(url, suggestedName) {
    if (window.showSaveFilePicker) {
        try {
            const handle = await window.showSaveFilePicker({ suggestedName });
            const writable = await handle.createWritable();
            const resp = await fetch(url);
            await writable.write(await resp.blob());
            await writable.close();
            return;
        } catch (e) {
            if (e.name === 'AbortError') return;
        }
    }
    window.location.href = url;
}
</script>

<?php require __DIR__ . '/../includes/' . ($loggedIn ? 'footer.php' : 'public_footer.php'); ?>
