<?php
require __DIR__ . '/../includes/bootstrap.php';

header('Content-Type: application/xml; charset=utf-8');

$base = rtrim($_ENV['APP_URL'] ?? '', '/');

$pages = [
    ['loc' => '/', 'priority' => '1.0'],
    ['loc' => '/tools.php', 'priority' => '0.9'],
    ['loc' => '/tools_archive.php', 'priority' => '0.7'],
    ['loc' => '/tools_share.php', 'priority' => '0.7'],
    ['loc' => '/tools_qr.php', 'priority' => '0.7'],
    ['loc' => '/tools_shortener.php', 'priority' => '0.7'],
    ['loc' => '/tools_email_verify.php', 'priority' => '0.7'],
    ['loc' => '/tools_policy_generator.php', 'priority' => '0.7'],
    ['loc' => '/privacy.php', 'priority' => '0.3'],
    ['loc' => '/terms.php', 'priority' => '0.3'],
    ['loc' => '/login.php', 'priority' => '0.3'],
    ['loc' => '/register.php', 'priority' => '0.5'],
];

echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";
foreach ($pages as $page) {
    echo "  <url>\n";
    echo "    <loc>" . htmlspecialchars($base . $page['loc']) . "</loc>\n";
    echo "    <priority>" . $page['priority'] . "</priority>\n";
    echo "  </url>\n";
}
echo '</urlset>';
