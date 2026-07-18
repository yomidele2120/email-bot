<?php
require __DIR__ . '/../includes/bootstrap.php';

use App\Auth;

header('Location: ' . (Auth::check() ? '/dashboard.php' : '/login.php'));
exit;
