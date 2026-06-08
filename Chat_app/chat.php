<?php
require_once 'config/database.php';
require_once 'includes/functions.php';
require_once 'includes/auth.php';

// ارسال پیام جدید
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['message'])) {
    $message = trim($_POST['message']);
    $reply_to = $_POST['reply_to'] ?? null;
    $channel_id = $_POST['channel_id'] ?? null;
    
    if (!empty($message)) {
        $stmt = $pdo->prepare("INSERT INTO messages (user_id, username, message, reply_to, channel_id) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$_SESSION['user_id'], $_SESSION['username'], $message, $reply_to, $channel_id]);
    }
    header('Location: chat.php');
    exit();
}

// دریافت پیام‌های عمومی
$stmt = $pdo->query("
    SELECT m.*, u.has_blue_tick, u.role 
    FROM messages m 
    JOIN users u ON m.user_id = u.id 
    WHERE m.channel_id IS NULL 
    ORDER BY m.created_at ASC
");
$messages = $stmt->fetchAll();

// دریافت کانال‌های کاربر
$stmt = $pdo->prepare("SELECT * FROM channels WHERE owner_id = ?");
$stmt->execute([$_SESSION['user_id']]);
$myChannels = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>پیامرسان حرفه‌ای</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Vazir&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <div class="app-container">
        <!-- نوار کناری -->
        <div class="sidebar">
            <div class="sidebar-header">
                <h3><i class="fas fa-comment-dots"></i> پیامرسان</h3>
            </div>
            
            <div class="sidebar-menu">
                <a href="chat.php" class="menu-item active">
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
                <?php if(isAdmin()): ?>
                <a href="admin_panel.php" class="menu-item">
                    <i class="fas fa-shield-alt"></i>
                    <span>پنل ادمین</span>
                </a>
                <?php endif; ?>
                <a href="edit_profile.php" class="menu-item">
                    <i class="fas fa-edit"></i>
                    <span>ویرایش اطلاعات</span>
                </a>
                <a href="logout.php" class="menu-item">
                    <i class="fas fa-sign-out-alt"></i>
                    <span>خروج</span>
                </a>
            </div>
            
            <div class="sidebar-footer">
                <div class="user-info-sidebar">
                    <i class="fas fa-user"></i>
                    <div>
                        <div class="username"><?php echo htmlspecialchars($_SESSION['username']); ?></div>
                        <div class="user-role"><?php echo isAdmin() ? 'ادمین' : 'کاربر عادی'; ?></div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- محتوای اصلی -->
        <div class="main-content">
            <div class="chat-header">
                <div class="chat-title">
                    <i class="fas fa-comments"></i>
                    <h2>گفتگوی عمومی</h2>
                </div>
                <div class="header-actions">
                    <a href="profile.php?user=<?php echo $_SESSION['username']; ?>" class="profile-btn">
                        <i class="fas fa-user"></i>
                    </a>
                </div>
            </div>
            
            <div class="messages-area" id="messagesArea">
                <?php foreach ($messages as $msg): ?>
                    <div class="message-wrapper" data-msg-id="<?php echo $msg['id']; ?>">
                        <div class="message-avatar">
                            <i class="fas fa-user-circle"></i>
                            <?php if($msg['has_blue_tick']): ?>
                                <i class="fas fa-check-circle blue-tick"></i>
                            <?php endif; ?>
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
                                    پاسخ به <strong><?php echo htmlspecialchars($reply['username']); ?></strong>:
                                    <?php echo htmlspecialchars(mb_substr($reply['message'], 0, 50)); ?>
                                </div>
                            <?php endif; ?>
                            
                            <?php if($msg['media_path']): ?>
                                <div class="message-media">
                                    <?php if($msg['media_type'] == 'image'): ?>
                                        <img src="assets/uploads/<?php echo $msg['media_path']; ?>" alt="تصویر">
                                    <?php else: ?>
                                        <a href="assets/uploads/<?php echo $msg['media_path']; ?>" download>
                                            <i class="fas fa-paperclip"></i> دانلود فایل
                                        </a>
                                    <?php endif; ?>
                                </div>
                            <?php endif; ?>
                            
                            <div class="message-text"><?php echo nl2br(htmlspecialchars($msg['message'])); ?></div>
                        </div>
                        <div class="message-actions">
                            <button onclick="replyTo(<?php echo $msg['id']; ?>, '<?php echo htmlspecialchars($msg['username']); ?>')" class="action-btn reply-action">
                                <i class="fas fa-reply"></i>
                            </button>
                            <?php if ($_SESSION['username'] == $msg['username'] || isAdmin()): ?>
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
                
                <form method="POST" id="messageForm" enctype="multipart/form-data" action="upload.php">
                    <input type="hidden" name="reply_to" id="replyToId" value="">
                    <input type="hidden" name="channel_id" value="">
                    
                    <div class="input-wrapper">
                        <label for="fileInput" class="attach-btn">
                            <i class="fas fa-paperclip"></i>
                        </label>
                        <input type="file" name="media" id="fileInput" style="display:none" accept="image/*,video/*,audio/*,application/pdf">
                        
                        <textarea name="message" id="messageInput" placeholder="پیام خود را بنویسید..." rows="1"></textarea>
                        
                        <button type="submit" class="send-btn">
                            <i class="fas fa-paper-plane"></i>
                        </button>
                    </div>
                </form>
            </div>
        </div>
        
        <!-- نوار کناری کانال‌ها -->
        <div class="channels-sidebar">
            <div class="channels-header">
                <h4><i class="fas fa-broadcast-tower"></i> کانال‌های من</h4>
            </div>
            <div class="channels-list">
                <?php foreach($myChannels as $channel): ?>
                    <a href="channel.php?link=<?php echo $channel['channel_link']; ?>" class="channel-item">
                        <i class="fas fa-hashtag"></i>
                        <span><?php echo htmlspecialchars($channel['channel_name']); ?></span>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
    
    <script src="assets/js/script.js"></script>
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
        
        document.getElementById('fileInput').addEventListener('change', function() {
            if(this.files.length > 0) {
                document.getElementById('messageForm').submit();
            }
        });
        
        document.getElementById('messageInput').addEventListener('keydown', function(e) {
            if(e.key === 'Enter' && !e.shiftKey) {
                e.preventDefault();
                document.getElementById('messageForm').submit();
            }
        });
        
        function scrollToBottom() {
            const messagesArea = document.getElementById('messagesArea');
            messagesArea.scrollTop = messagesArea.scrollHeight;
        }
        scrollToBottom();
    </script>
</body>
</html>
