<?php
require_once 'config/database.php';
require_once 'includes/functions.php';
require_once 'includes/auth.php';

if (!isAdmin()) {
    header('Location: chat.php');
    exit();
}

// تایید یا رد درخواست تیک آبی
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['verify_action'])) {
    $request_id = $_POST['request_id'];
    $action = $_POST['verify_action'];
    
    if ($action == 'approve') {
        // دریافت user_id از درخواست
        $stmt = $pdo->prepare("SELECT user_id FROM verify_requests WHERE id = ?");
        $stmt->execute([$request_id]);
        $user_id = $stmt->fetchColumn();
        
        // اعطای تیک آبی
        $stmt = $pdo->prepare("UPDATE users SET has_blue_tick = 1 WHERE id = ?");
        $stmt->execute([$user_id]);
        
        // به‌روزرسانی وضعیت درخواست
        $stmt = $pdo->prepare("UPDATE verify_requests SET status = 'approved' WHERE id = ?");
        $stmt->execute([$request_id]);
    } elseif ($action == 'reject') {
        $stmt = $pdo->prepare("UPDATE verify_requests SET status = 'rejected' WHERE id = ?");
        $stmt->execute([$request_id]);
    }
}

// حذف کاربر
if (isset($_GET['delete_user'])) {
    $user_id = $_GET['delete_user'];
    $stmt = $pdo->prepare("DELETE FROM users WHERE id = ? AND role != 'admin'");
    $stmt->execute([$user_id]);
    header('Location: admin_panel.php');
    exit();
}

// دریافت درخواست‌های احراز هویت
$stmt = $pdo->query("
    SELECT vr.*, u.fullname, u.username 
    FROM verify_requests vr 
    JOIN users u ON vr.user_id = u.id 
    WHERE vr.status = 'pending'
    ORDER BY vr.requested_at DESC
");
$verify_requests = $stmt->fetchAll();

// دریافت همه کاربران
$stmt = $pdo->query("SELECT * FROM users WHERE role != 'admin' ORDER BY created_at DESC");
$users = $stmt->fetchAll();

// دریافت همه کانال‌ها
$stmt = $pdo->query("
    SELECT c.*, u.username as owner_name 
    FROM channels c 
    JOIN users u ON c.owner_id = u.id 
    ORDER BY c.created_at DESC
");
$channels = $stmt->fetchAll();

// آمار
$stmt = $pdo->query("SELECT COUNT(*) as total FROM users");
$total_users = $stmt->fetchColumn();
$stmt = $pdo->query("SELECT COUNT(*) as total FROM messages");
$total_messages = $stmt->fetchColumn();
$stmt = $pdo->query("SELECT COUNT(*) as total FROM channels");
$total_channels = $stmt->fetchColumn();
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>پنل مدیریت</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Vazir&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .admin-stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            padding: 20px;
            margin-bottom: 30px;
        }
        .stat-card {
            background: white;
            padding: 20px;
            border-radius: 12px;
            text-align: center;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .stat-card i {
            font-size: 40px;
            color: #0088cc;
            margin-bottom: 10px;
        }
        .stat-card h3 {
            font-size: 28px;
            margin: 10px 0;
            color: #333;
        }
        .stat-card p {
            color: #666;
            font-size: 14px;
        }
        .admin-section {
            background: white;
            margin: 20px;
            padding: 20px;
            border-radius: 12px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .admin-section h3 {
            color: #333;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid #0088cc;
        }
        .requests-table, .users-table, .channels-table {
            width: 100%;
            overflow-x: auto;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            padding: 12px;
            text-align: right;
            border-bottom: 1px solid #eee;
        }
        th {
            background: #f5f5f5;
            color: #333;
            font-weight: bold;
        }
        .btn-approve {
            background: #4caf50;
            color: white;
            border: none;
            padding: 6px 12px;
            border-radius: 6px;
            cursor: pointer;
            margin: 0 5px;
        }
        .btn-reject {
            background: #f44336;
            color: white;
            border: none;
            padding: 6px 12px;
            border-radius: 6px;
            cursor: pointer;
        }
        .btn-delete {
            background: #ff9800;
            color: white;
            border: none;
            padding: 6px 12px;
            border-radius: 6px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            font-size: 12px;
        }
        .badge-blue {
            background: #0088cc;
            color: white;
            padding: 3px 8px;
            border-radius: 12px;
            font-size: 11px;
        }
    </style>
</head>
<body>
    <div class="app-container">
        <div class="sidebar">
            <div class="sidebar-header">
                <h3><i class="fas fa-shield-alt"></i> پنل ادمین</h3>
            </div>
            <div class="sidebar-menu">
                <a href="chat.php" class="menu-item">
                    <i class="fas fa-home"></i>
                    <span>صفحه اصلی</span>
                </a>
                <a href="admin_panel.php" class="menu-item active">
                    <i class="fas fa-tachometer-alt"></i>
                    <span>داشبورد</span>
                </a>
                <a href="logout.php" class="menu-item">
                    <i class="fas fa-sign-out-alt"></i>
                    <span>خروج</span>
                </a>
            </div>
        </div>
        
        <div class="main-content" style="margin-right: 250px;">
            <div class="chat-header">
                <h2><i class="fas fa-tachometer-alt"></i> داشبورد مدیریت</h2>
            </div>
            
            <div class="admin-stats">
                <div class="stat-card">
                    <i class="fas fa-users"></i>
                    <h3><?php echo $total_users; ?></h3>
                    <p>کاربران کل</p>
                </div>
                <div class="stat-card">
                    <i class="fas fa-comments"></i>
                    <h3><?php echo $total_messages; ?></h3>
                    <p>پیام‌های ارسال شده</p>
                </div>
                <div class="stat-card">
                    <i class="fas fa-broadcast-tower"></i>
                    <h3><?php echo $total_channels; ?></h3>
                    <p>کانال‌های ساخته شده</p>
                </div>
            </div>
            
            <!-- درخواست‌های تیک آبی -->
            <div class="admin-section">
                <h3><i class="fas fa-check-circle"></i> درخواست‌های تیک آبی</h3>
                <?php if(count($verify_requests) > 0): ?>
                    <div class="requests-table">
                        <table>
                            <thead>
                                <tr><th>نام کاربری</th><th>نام کامل</th><th>ایمیل</th><th>کد ملی</th><th>شماره تماس</th><th>عملیات</th></tr>
                            </thead>
                            <tbody>
                                <?php foreach($verify_requests as $req): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($req['username']); ?></td>
                                    <td><?php echo htmlspecialchars($req['fullname']); ?></td>
                                    <td><?php echo htmlspecialchars($req['email']); ?></td>
                                    <td><?php echo htmlspecialchars($req['national_code']); ?></td>
                                    <td><?php echo htmlspecialchars($req['phone']); ?></td>
                                    <td>
                                        <form method="POST" style="display:inline">
                                            <input type="hidden" name="request_id" value="<?php echo $req['id']; ?>">
                                            <button type="submit" name="verify_action" value="approve" class="btn-approve">تایید</button>
                                            <button type="submit" name="verify_action" value="reject" class="btn-reject">رد</button>
                                        </form>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <p style="color:#999; text-align:center">هیچ درخواستی وجود ندارد</p>
                <?php endif; ?>
            </div>
            
            <!-- لیست کاربران -->
            <div class="admin-section">
                <h3><i class="fas fa-users"></i> مدیریت کاربران</h3>
                <div class="users-table">
                    <table>
                        <thead>
                            <tr><th>نام کاربری</th><th>نام کامل</th><th>وضعیت تیک</th><th>تاریخ عضویت</th><th>عملیات</th></tr>
                        </thead>
                        <tbody>
                            <?php foreach($users as $user): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($user['username']); ?></td>
                                <td><?php echo htmlspecialchars($user['fullname']); ?></td>
                                <td><?php echo $user['has_blue_tick'] ? '<span class="badge-blue">دارای تیک</span>' : 'ندارد'; ?></td>
                                <td><?php echo timeAgo($user['created_at']); ?></td>
                                <td>
                                    <a href="?delete_user=<?php echo $user['id']; ?>" class="btn-delete" onclick="return confirm('حذف کاربر؟')">حذف کاربر</a>
                                </td>
                                <td>
    <a href="delete_account.php?id=<?php echo $user['id']; ?>" class="btn-delete" onclick="return confirm('حذف کامل این کاربر و تمام پیام‌ها و کانال‌هایش؟')">
        <i class="fas fa-trash"></i> حذف کاربر
    </a>
</td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            
            <!-- لیست کانال‌ها -->
            <div class="admin-section">
                <h3><i class="fas fa-broadcast-tower"></i> مدیریت کانال‌ها</h3>
                <div class="channels-table">
                    <table>
                        <thead>
                            <tr><th>نام کانال</th><th>مالک</th><th>تاریخ ساخت</th></tr>
                        </thead>
                        <tbody>
                            <?php foreach($channels as $channel): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($channel['channel_name']); ?></td>
                                <td><?php echo htmlspecialchars($channel['owner_name']); ?></td>
                                <td><?php echo timeAgo($channel['created_at']); ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
