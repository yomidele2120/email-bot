<?php
require __DIR__ . '/../includes/bootstrap.php';

use App\Database;

$token = preg_replace('/[^a-f0-9]/', '', $_GET['t'] ?? '');
$pdo = Database::connect();

$stmt = $pdo->prepare("SELECT * FROM shared_files WHERE token = :t AND expires_at > NOW()");
$stmt->execute([':t' => $token]);
$file = $stmt->fetch();

$path = $file ? __DIR__ . '/uploads/shared/' . $file['stored_name'] : null;

if (!$file || !file_exists($path)) {
    http_response_code(404);
    echo "This link has expired or doesn't exist.";
    exit;
}

$pdo->prepare("UPDATE shared_files SET downloads = downloads + 1 WHERE id = :id")->execute([':id' => $file['id']]);

header('Content-Type: application/octet-stream');
header('Content-Disposition: attachment; filename="' . basename($file['original_name']) . '"');
header('Content-Length: ' . filesize($path));
readfile($path);
