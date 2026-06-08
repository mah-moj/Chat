<?php
session_start();

// اگر قبلاً وارد شده، به چت برود
if (isset($_SESSION['user_id'])) {
    header('Location: chat.php');
    exit();
}

// در غیر این صورت به صفحه ورود برود
header('Location: login.php');
exit();
?>
