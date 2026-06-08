
<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/functions.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isLoggedIn() && basename($_SERVER['PHP_SELF']) != 'login.php' && 
    basename($_SERVER['PHP_SELF']) != 'register.php') {
    header('Location: login.php');
    exit();
}
?>
