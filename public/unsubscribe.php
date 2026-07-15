<?php
// public/unsubscribe.php
require __DIR__ . '/../vendor/autoload.php';

use App\Database;
use App\Contact;

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();

$token = $_GET['token'] ?? '';
$pdo = Database::connect();

$stmt = $pdo->prepare("SELECT contact_id FROM unsubscribe_tokens WHERE token = :t");
$stmt->execute([':t' => $token]);
$contactId = $stmt->fetchColumn();

if ($contactId) {
    Contact::unsubscribe((int)$contactId);
    echo "You've been unsubscribed and won't receive further emails.";
} else {
    http_response_code(404);
    echo "Invalid or expired unsubscribe link.";
}
