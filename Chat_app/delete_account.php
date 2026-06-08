
<?php
require_once 'config/database.php';
require_once 'includes/functions.php';
require_once 'includes/auth.php';

// فقط ادمین می‌تواند حذف کند
if (!isAdmin()) {
    header('Location: chat.php');
    exit();
}

$user_id = $_GET['id'] ?? 0;

if ($user_id) {
    // بررسی نکردن حذف ادمین اصلی
    $stmt = $pdo->prepare("SELECT role, username FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch();
    
    if ($user && $user['role'] != 'admin') {
        // حذف تمام پیام‌های کاربر
        $stmt = $pdo->prepare("DELETE FROM messages WHERE user_id = ?");
        $stmt->execute([$user_id]);
        
        // حذف تمام کانال‌های کاربر
        $stmt = $pdo->prepare("SELECT id FROM channels WHERE owner_id = ?");
        $stmt->execute([$user_id]);
        $channels = $stmt->fetchAll();
        
        foreach ($channels as $channel) {
            // حذف پیام‌های کانال‌ها
            $stmt = $pdo->prepare("DELETE FROM messages WHERE channel_id = ?");
            $stmt->execute([$channel['id']]);
        }
        
        // حذف کانال‌ها
        $stmt = $pdo->prepare("DELETE FROM channels WHERE owner_id = ?");
        $stmt->execute([$user_id]);
        
        // حذف درخواست‌های احراز هویت
        $stmt = $pdo->prepare("DELETE FROM verify_requests WHERE user_id = ?");
        $stmt->execute([$user_id]);
        
        // حذف خود کاربر
        $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
        $stmt->execute([$user_id]);
        
        $_SESSION['admin_message'] = "کاربر " . htmlspecialchars($user['username']) . " با موفقیت حذف شد";
    } else {
        $_SESSION['admin_error'] = "امکان حذف ادمین وجود ندارد";
    }
}

header('Location: admin_panel.php');
exit();
?>
