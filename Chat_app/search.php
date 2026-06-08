<?php
require_once 'config/database.php';
require_once 'includes/functions.php';
require_once 'includes/auth.php';

$search = $_GET['q'] ?? '';
$results = [];

if (!empty($search)) {
    // جستجو در پیام‌ها
    $stmt = $pdo->prepare("
        SELECT m.*, u.has_blue_tick, u.role 
        FROM messages m 
        JOIN users u ON m.user_id = u.id 
        WHERE m.message LIKE ? 
        ORDER BY m.created_at DESC
        LIMIT 50
    ");
    $stmt->execute(["%$search%"]);
    $results = $stmt->fetchAll();
}
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>جستجو در پیام‌ها</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Vazir&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <div class="app-container">
        <div class="sidebar">
            <div class="sidebar-header">
                <h3><i class="fas fa-search"></i> جستجو</h3>
            </div>
            <div class="sidebar-menu">
                <a href="chat.php" class="menu-item">
                    <i class="fas fa-home"></i>
                    <span>صفحه اصلی</span>
                </a>
                <a href="create_channel.php" class="menu-item">
                    <i class="fas fa-plus-circle"></i>
                    <span>ساخت کانال</span>
                </a>
                <a href="logout.php" class="menu-item">
                    <i class="fas fa-sign-out-alt"></i>
                    <span>خروج</span>
                </a>
            </div>
        </div>
        
        <div class="main-content" style="margin-right: 260px;">
            <div class="chat-header">
                <div class="chat-title">
                    <i class="fas fa-search"></i>
                    <h2>جستجوی پیام‌ها</h2>
                </div>
            </div>
            
            <div style="padding: 20px;">
                <form method="GET" style="margin-bottom: 20px;">
                    <div class="input-wrapper">
                        <input type="text" name="q" value="<?php echo htmlspecialchars($search); ?>" placeholder="عبارت مورد نظر را وارد کنید..." style="flex:1; padding:12px; border:1px solid #e0e0e0; border-radius:25px;">
                        <button type="submit" class="send-btn" style="width:auto; padding:0 20px; border-radius:25px;">
                            <i class="fas fa-search"></i> جستجو
                        </button>
                    </div>
                </form>
                
                <?php if(!empty($search)): ?>
                    <h4>نتایج جستجو برای: "<?php echo htmlspecialchars($search); ?>"</h4>
                    
                    <?php if(count($results) > 0): ?>
                        <div class="messages-area" style="height: auto; max-height: 500px;">
                            <?php foreach($results as $msg): ?>
                                <div class="message-wrapper">
                                    <div class="message-content">
                                        <div class="message-info">
                                            <strong class="username">
                                                <?php echo htmlspecialchars($msg['username']); ?>
                                                <?php if($msg['has_blue_tick']): ?>
                                                    <i class="fas fa-check-circle blue-tick-small"></i>
                                                <?php endif; ?>
                                            </strong>
                                            <small class="time"><?php echo timeAgo($msg['created_at']); ?></small>
                                        </div>
                                        <div class="message-text">
                                            <?php echo nl2br(htmlspecialchars($msg['message'])); ?>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <p style="color:#999; text-align:center; padding:50px;">نتیجه‌ای یافت نشد</p>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>
