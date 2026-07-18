<?php
// router.php - used only when the app is started with:
// php -S 0.0.0.0:$PORT -t public router.php
// Lets short links resolve as clean paths like /promo instead of /r.php?s=promo,
// while every other request still goes to its normal file in public/.

$uri = urldecode(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));

if ($uri === '/') {
    require __DIR__ . '/public/index.php';
    return true;
}

$file = __DIR__ . '/public' . $uri;

// Real file (a .php page, the stylesheet, etc.) -> let the built-in server serve it normally.
if (file_exists($file) && !is_dir($file)) {
    return false;
}

// A single path segment that isn't a real file -> treat it as a short link slug.
if (preg_match('#^/([a-zA-Z0-9\-_]{3,20})$#', $uri, $m)) {
    $_GET['s'] = $m[1];
    require __DIR__ . '/public/r.php';
    return true;
}

http_response_code(404);
echo "Not found";
return true;
