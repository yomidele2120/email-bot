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
            $message = 'Converted successfully. Your download is ready.';
            $messageType = 'success';
        } catch (Exception $e) {
            $message = 'Could not process that file: ' . $e->getMessage();
        }
    }
}

$pageTitle = 'Archive Converter';
$activeNav = 'tools_archive';
require __DIR__ . '/../includes/' . ($loggedIn ? 'header.php' : 'public_header.php');
?>

<?php if ($loggedIn): ?><p><a href="/tools.php" style="color:var(--text-muted);font-size:13px">← Tools</a></p><?php endif; ?>
<h1>Archive Converter</h1>
<p style="color:var(--text-muted)">Upload a ZIP, TAR, TAR.GZ, or JAR file and get a clean ZIP back. Handles the format you selected: <strong><?= htmlspecialchars($modeLabel) ?></strong>.</p>
<?php if (!$loggedIn): ?>
    <p style="color:var(--text-muted);font-size:13px">Free to try <strong class="mono"><?= TrialGate::usesLeft('archive') ?></strong> more time(s) without an account.</p>
<?php endif; ?>

<?php if ($message): ?>
    <div class="alert alert-<?= $messageType ?>">
        <?= htmlspecialchars($message) ?>
        <?php if ($downloadUrl): ?> <a href="<?= htmlspecialchars($downloadUrl) ?>">Download ZIP</a><?php endif; ?>
    </div>
<?php endif; ?>

<form method="POST" enctype="multipart/form-data" style="max-width:440px">
    <label style="margin-top:0">Archive file</label>
    <input type="file" name="archive" accept=".zip,.tar,.tar.gz,.tgz,.jar" required>
    <button type="submit">Convert</button>
</form>

<p style="margin-top:24px;font-size:12px;color:var(--text-muted)">RAR, 7Z, and other formats aren't supported yet, those need extra system tools we haven't wired up. This handles ZIP, TAR, TAR.GZ, and JAR.</p>

<?php require __DIR__ . '/../includes/' . ($loggedIn ? 'footer.php' : 'public_footer.php'); ?>
