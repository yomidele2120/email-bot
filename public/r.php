<?php
require __DIR__ . '/../includes/bootstrap.php';

use App\ShortLink;

$slug = $_GET['s'] ?? '';
$target = $slug ? ShortLink::resolveAndTrack($slug) : null;

if ($target) {
    header('Location: ' . $target, true, 302);
    exit;
}

http_response_code(404);
echo "This short link doesn't exist or has expired.";
