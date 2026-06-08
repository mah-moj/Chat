<?php
require_once 'config/database.php';
require_once 'includes/functions.php';
require_once 'includes/auth.php';

$channel_link = $_GET['link'] ?? '';
$stmt = $pdo->prepare("SELECT * FROM channels WHERE channel_link = ?");
$stmt->execute([$channel_link]);
$channel = $stmt->fetch();

if (!$channel) {
    header('Location: chat.php');
    exit();
}

// دریافت پیام‌های کانال
$stmt = $pdo->prepare("
    SELECT m.*, u.has_blue_tick, u.role 
    FROM messages m 
    JOIN users u ON m.user_id = u.id 
    WHERE m.channel_id = ? 
    ORDER BY m.created_at ASC
");
$stmt->execute([$channel['id']]);
$messages = $stmt->fetchAll();

$is_owner = ($channel['owner_id'] == $_SESSION['user_id']);
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title><?php echo htmlspecialchars($channel['channel_name']); ?> - کانال</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Vazir&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <div class="app-container">
        <div class="sidebar">
            <div class="sidebar-header">
                <h3><i class="fas fa-comment-dots"></i> پیامرسان</h3>
            </div>
            <div class="sidebar-menu">
                <a href="chat.php" class="menu-item">
                    <i class="fas fa-home"></i>
                    <span>صفحه اصلی</span>
                </a>
                <a href="create_channel.php" class="menu-item">
                    <i class="fas fa-plus-circle"></i>
                    <span>ساخت کانال جدید</span>
                </a>
                <a href="profile.php?user=<?php echo $_SESSION['username']; ?>" class="menu-item">
                    <i class="fas fa-user-circle"></i>
                    <span>پروفایل من</span>
                </a>
                <a href="logout.php" class="menu-item">
                    <i class="fas fa-sign-out-alt"></i>
                    <span>خروج</span>
                </a>
            </div>
        </div>
        
        <div class="main-content">
            <div class="chat-header">
                <div class="chat-title">
                    <i class="fas fa-broadcast-tower"></i>
                    <h2><?php echo htmlspecialchars($channel['channel_name']); ?></h2>
                    <span class="channel-link">@<?php echo $channel['channel_link']; ?></span>
                </div>
                <div class="header-actions">
                    <button onclick="copyLink()" class="profile-btn">
                        <i class="fas fa-link"></i>
                    </button>
                </div>
            </div>
            
            <?php if($channel['description']): ?>
                <div class="channel-description">
                    <i class="fas fa-info-circle"></i>
                    <?php echo htmlspecialchars($channel['description']); ?>
                </div>
            <?php endif; ?>
            
            <div class="messages-area" id="messagesArea">
                <?php foreach ($messages as $msg): ?>
                    <div class="message-wrapper" data-msg-id="<?php echo $msg['id']; ?>">
                        <div class="message-avatar">
                            <i class="fas fa-user-circle"></i>
                        </div>
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
                            
                            <?php if ($msg['reply_to']): 
                                $reply_stmt = $pdo->prepare("SELECT username, message FROM messages WHERE id = ?");
                                $reply_stmt->execute([$msg['reply_to']]);
                                $reply = $reply_stmt->fetch();
                            ?>
                                <div class="reply-preview">
                                    <i class="fas fa-reply"></i>
                                    پاسخ به <strong><?php echo htmlspecialchars($reply['username']); ?></strong>
                                </div>
                            <?php endif; ?>
                            
                            <div class="message-text"><?php echo nl2br(htmlspecialchars($msg['message'])); ?></div>
                        </div>
                        <div class="message-actions">
                            <button onclick="replyTo(<?php echo $msg['id']; ?>, '<?php echo htmlspecialchars($msg['username']); ?>')" class="action-btn reply-action">
                                <i class="fas fa-reply"></i>
                            </button>
                            <?php if ($_SESSION['username'] == $msg['username'] || isAdmin() || $is_owner): ?>
                                <a href="delete_message.php?id=<?php echo $msg['id']; ?>" class="action-btn delete-action" onclick="return confirm('حذف شود؟')">
                                    <i class="fas fa-trash"></i>
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            
            <div class="message-input-container">
                <div id="replyIndicator" class="reply-indicator" style="display:none;">
                    <div class="reply-info">
                        <i class="fas fa-reply"></i>
                        در حال پاسخ به <span id="replyToUser"></span>
                    </div>
                    <button onclick="cancelReply()" class="cancel-reply">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                
                <form method="POST" action="chat.php" id="messageForm">
                    <input type="hidden" name="reply_to" id="replyToId" value="">
                    <input type="hidden" name="channel_id" value="<?php echo $channel['id']; ?>">
                    
                    <div class="input-wrapper">
                        <textarea name="message" id="messageInput" placeholder="پیام خود را بنویسید..." rows="1"></textarea>
                        <button type="submit" class="send-btn">
                            <i class="fas fa-paper-plane"></i>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <script>
        function replyTo(msgId, username) {
            document.getElementById('replyToId').value = msgId;
            document.getElementById('replyToUser').innerText = username;
            document.getElementById('replyIndicator').style.display = 'flex';
            document.getElementById('messageInput').focus();
        }
        
        function cancelReply() {
            document.getElementById('replyToId').value = '';
            document.getElementById('replyIndicator').style.display = 'none';
        }
        
        function copyLink() {
            const link = window.location.href;
            navigator.clipboard.writeText(link);
            alert('لینک کانال کپی شد');
        }
        
        function scrollToBottom() {
            const messagesArea = document.getElementById('messagesArea');
            if(messagesArea) messagesArea.scrollTop = messagesArea.scrollHeight;
        }
        scrollToBottom();
    </script>
</body>
</html>
