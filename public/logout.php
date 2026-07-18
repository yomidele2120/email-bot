<?php
require __DIR__ . '/../includes/bootstrap.php';
\App\Auth::logout();
header('Location: /login.php');
exit;
