<?php
require __DIR__ . '/../includes/bootstrap.php';

$token = preg_replace('/[^a-f0-9]/', '', $_GET['t'] ?? '');
$entry = $_SESSION['archive_downloads'][$token] ?? null;

$path = __DIR__ . '/downloads/' . $token . '.zip';

if (!$entry || !file_exists($path) || $entry['expires'] < time()) {
    http_response_code(404);
    echo "This download link has expired. Convert the file again to get a fresh one.";
    exit;
}

header('Content-Type: application/zip');
header('Content-Disposition: attachment; filename="' . basename($entry['name']) . '"');
header('Content-Length: ' . filesize($path));
readfile($path);

// Clean up after serving
unset($_SESSION['archive_downloads'][$token]);
@unlink($path);
