
<?php
$host = 'localhost';
$dbname = 'chat_app_advanced';
$db_user = 'root';
$db_pass = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $db_user, $db_pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    session_start();
} catch(PDOException $e) {
    die("خطا در اتصال: " . $e->getMessage());
}
?>
