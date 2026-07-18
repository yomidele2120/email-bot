<?php
require __DIR__ . '/../includes/bootstrap.php';

use App\Auth;

header('Location: ' . (Auth::check() ? '/dashboard.php' : '/landing.php'));
exit;
