<?php
require_once 'config/database.php';
require_once 'includes/functions.php';
require_once 'includes/auth.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $message = $_POST['message'] ?? '';
    $reply_to = $_POST['reply_to'] ?? null;
    $channel_id = $_POST['channel_id'] ?? null;
    
    $media_path = null;
    $media_type = null;
    
    // آپلود فایل
    if (isset($_FILES['media']) && $_FILES['media']['error'] == 0) {
        $allowed = ['jpg', 'jpeg', 'png', 'gif', 'mp4', 'mp3', 'pdf', 'doc', 'docx'];
        $filename = $_FILES['media']['name'];
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        
        if (in_array($ext, $allowed)) {
            $new_filename = time() . '_' . rand(1000, 9999) . '.' . $ext;
            $upload_path = 'assets/uploads/' . $new_filename;
            
            if (move_uploaded_file($_FILES['media']['tmp_name'], $upload_path)) {
                $media_path = $new_filename;
                $media_type = in_array($ext, ['jpg', 'jpeg', 'png', 'gif']) ? 'image' : 'file';
            }
        }
    }
    
    // ذخیره پیام
    $stmt = $pdo->prepare("INSERT INTO messages (user_id, username, message, reply_to, channel_id, media_path, media_type) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([$_SESSION['user_id'], $_SESSION['username'], $message, $reply_to, $channel_id, $media_path, $media_type]);
    
    // بازگشت به صفحه قبلی
    if ($channel_id) {
        $stmt = $pdo->prepare("SELECT channel_link FROM channels WHERE id = ?");
        $stmt->execute([$channel_id]);
        $channel = $stmt->fetch();
        header('Location: channel.php?link=' . $channel['channel_link']);
    } else {
        header('Location: chat.php');
    }
    exit();
}
?>
