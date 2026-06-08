<?php
require_once 'config/database.php';
require_once 'includes/functions.php';
require_once 'includes/auth.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $channel_name = trim($_POST['channel_name']);
    $description = trim($_POST['description']);
    $channel_link = str_replace(' ', '_', strtolower($channel_name)) . '_' . rand(1000, 9999);
    
    // بررسی تکراری نبودن لینک
    $stmt = $pdo->prepare("SELECT id FROM channels WHERE channel_link = ?");
    $stmt->execute([$channel_link]);
    
    if ($stmt->rowCount() > 0) {
        $channel_link = $channel_link . '_' . rand(10, 99);
    }
    
    if (empty($channel_name)) {
        $error = 'نام کانال الزامی است';
    } else {
        $stmt = $pdo->prepare("INSERT INTO channels (channel_name, channel_link, owner_id, description) VALUES (?, ?, ?, ?)");
        if ($stmt->execute([$channel_name, $channel_link, $_SESSION['user_id'], $description])) {
            $success = 'کانال با موفقیت ساخته شد';
            header("refresh:2;url=channel.php?link=$channel_link");
        } else {
            $error = 'خطا در ساخت کانال';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>ساخت کانال جدید</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Vazir&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <div class="container">
        <div class="form-box">
            <div class="form-header">
                <i class="fas fa-broadcast-tower"></i>
                <h2>ساخت کانال جدید</h2>
            </div>
            
            <?php if($error): ?>
                <div class="alert alert-error">
                    <i class="fas fa-exclamation-circle"></i>
                    <?php echo $error; ?>
                </div>
            <?php endif; ?>
            
            <?php if($success): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i>
                    <?php echo $success; ?>
                </div>
            <?php endif; ?>
            
            <form method="POST">
                <div class="input-group">
                    <i class="fas fa-hashtag"></i>
                    <input type="text" name="channel_name" placeholder="نام کانال" required>
                </div>
                
                <div class="input-group">
                    <i class="fas fa-align-left"></i>
                    <textarea name="description" placeholder="توضیحات کانال (اختیاری)" rows="4"></textarea>
                </div>
                
                <button type="submit" class="btn-primary">
                    <i class="fas fa-plus-circle"></i>
                    ساخت کانال
                </button>
            </form>
            
            <div class="form-footer">
                <a href="chat.php" class="back-link">
                    <i class="fas fa-arrow-right"></i>
                    بازگشت به صفحه اصلی
                </a>
            </div>
        </div>
    </div>
</body>
</html>
