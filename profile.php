
<?php
require_once 'config/database.php';
require_once 'includes/functions.php';
require_once 'includes/auth.php';

$username = $_GET['user'] ?? '';
$stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
$stmt->execute([$username]);
$profile_user = $stmt->fetch();

if (!$profile_user) {
    header('Location: chat.php');
    exit();
}

// دریافت کانال‌های کاربر
$stmt = $pdo->prepare("SELECT * FROM channels WHERE owner_id = ?");
$stmt->execute([$profile_user['id']]);
$user_channels = $stmt->fetchAll();

// تعداد پیام‌های کاربر
$stmt = $pdo->prepare("SELECT COUNT(*) FROM messages WHERE user_id = ?");
$stmt->execute([$profile_user['id']]);
$message_count = $stmt->fetchColumn();
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>پروفایل <?php echo htmlspecialchars($profile_user['username']); ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Vazir&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .profile-container {
            max-width: 600px;
            margin: 50px auto;
            background: white;
            border-radius: 20px;
            padding: 40px;
            text-align: center;
        }
        .profile-avatar {
            font-size: 100px;
            color: #0088cc;
            margin-bottom: 20px;
        }
        .profile-name {
            font-size: 24px;
            color: #333;
            margin-bottom: 10px;
        }
        .profile-username {
            color: #666;
            margin-bottom: 20px;
        }
        .profile-stats {
            display: flex;
            justify-content: center;
            gap: 30px;
            margin: 30px 0;
            padding: 20px;
            background: #f5f5f5;
            border-radius: 12px;
        }
        .stat-item {
            text-align: center;
        }
        .stat-number {
            font-size: 28px;
            font-weight: bold;
            color: #0088cc;
        }
        .stat-label {
            font-size: 12px;
            color: #666;
        }
        .channels-list-profile {
            text-align: right;
            margin-top: 20px;
        }
        .channel-item-profile {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 10px;
            background: #f5f5f5;
            border-radius: 8px;
            margin: 10px 0;
            text-decoration: none;
            color: #333;
        }
        .back-btn-profile {
            display: inline-block;
            margin-top: 20px;
            padding: 10px 20px;
            background: #0088cc;
            color: white;
            text-decoration: none;
            border-radius: 8px;
        }
    </style>
</head>
<body>
    <div class="profile-container">
        <div class="profile-avatar">
            <i class="fas fa-user-circle"></i>
            <?php if($profile_user['has_blue_tick']): ?>
                <i class="fas fa-check-circle blue-tick" style="font-size: 24px; margin-right: -25px;"></i>
            <?php endif; ?>
        </div>
        
        <div class="profile-name">
            <?php echo htmlspecialchars($profile_user['fullname']); ?>
        </div>
        
        <div class="profile-username">
            @<?php echo htmlspecialchars($profile_user['username']); ?>
            <?php if($profile_user['role'] == 'admin'): ?>
                <span style="background:#0088cc; color:white; padding:2px 8px; border-radius:12px; font-size:11px; margin-right:8px;">ادمین</span>
            <?php endif; ?>
        </div>
        
        <div class="profile-stats">
            <div class="stat-item">
                <div class="stat-number"><?php echo $message_count; ?></div>
                <div class="stat-label">پیام</div>
            </div>
            <div class="stat-item">
                <div class="stat-number"><?php echo count($user_channels); ?></div>
                <div class="stat-label">کانال</div>
            </div>
        </div>
        
        <?php if(count($user_channels) > 0): ?>
            <div class="channels-list-profile">
                <h4>کانال‌های این کاربر:</h4>
                <?php foreach($user_channels as $channel): ?>
                    <a href="channel.php?link=<?php echo $channel['channel_link']; ?>" class="channel-item-profile">
                        <i class="fas fa-hashtag"></i>
                        <span><?php echo htmlspecialchars($channel['channel_name']); ?></span>
                    </a>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
        
        <a href="chat.php" class="back-btn-profile">
            <i class="fas fa-arrow-right"></i>
            بازگشت به چت
        </a>
    </div>
</body>
</html>
