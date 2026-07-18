<?php
require __DIR__ . '/../includes/bootstrap.php';

use App\TrialGate;

header('Content-Type: application/json');

$tool = preg_replace('/[^a-z_]/', '', $_GET['tool'] ?? '');
if (!$tool) {
    echo json_encode(['allowed' => false]);
    exit;
}

echo json_encode(['allowed' => TrialGate::check($tool)]);
