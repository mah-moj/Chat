
<?php
require_once 'config/database.php';
require_once 'includes/functions.php';
require_once 'includes/auth.php';

$message_id = $_GET['id'] ?? 0;

// دریافت اطلاعات پیام
$stmt = $pdo->prepare("SELECT m.*, u.role FROM messages m JOIN users u ON m.user_id = u.id WHERE m.id = ?");
$stmt->execute([$message_id]);
$message = $stmt->fetch();

if ($message) {
    $can_delete = false;
    
    if (isAdmin()) {
        $can_delete = true;
    } elseif ($message['username'] == $_SESSION['username']) {
        $can_delete = true;
    } elseif ($message['channel_id']) {
        $stmt = $pdo->prepare("SELECT owner_id FROM channels WHERE id = ?");
        $stmt->execute([$message['channel_id']]);
        $channel = $stmt->fetch();
        if ($channel && $channel['owner_id'] == $_SESSION['user_id']) {
            $can_delete = true;
        }
    }
    
    if ($can_delete) {
        // حذف فایل رسانه اگر وجود دارد
        if ($message['media_path']) {
            $file_path = 'assets/uploads/' . $message['media_path'];
            if (file_exists($file_path)) {
                unlink($file_path);
            }
        }
        
        $stmt = $pdo->prepare("DELETE FROM messages WHERE id = ?");
        $stmt->execute([$message_id]);
    }
}

// بازگشت به صفحه قبلی
$referer = $_SERVER['HTTP_REFERER'] ?? 'chat.php';
header('Location: ' . $referer);
exit();
?>
