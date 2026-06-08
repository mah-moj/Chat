
<?php
require_once 'config/database.php';
require_once 'includes/functions.php';
require_once 'includes/auth.php';

$user_id = $_SESSION['user_id'];
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $fullname = trim($_POST['fullname']);
    $username = trim($_POST['username']);
    $password = !empty($_POST['password']) ? md5($_POST['password']) : $user['password'];
    
    if (empty($fullname) || empty($username)) {
        $error = 'نام و نام کاربری الزامی است';
    } elseif ($username != $user['username'] && checkDuplicateUsername($pdo, $username)) {
        $error = 'این نام کاربری قبلاً ثبت شده است';
    } else {
        $stmt = $pdo->prepare("UPDATE users SET fullname = ?, username = ?, password = ? WHERE id = ?");
        if ($stmt->execute([$fullname, $username, $password, $user_id])) {
            $_SESSION['username'] = $username;
            $success = 'اطلاعات با موفقیت به‌روزرسانی شد';
        } else {
            $error = 'خطا در به‌روزرسانی';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>ویرایش پروفایل</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Vazir&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <div class="container">
        <div class="form-box">
            <div class="form-header">
                <i class="fas fa-user-edit"></i>
                <h2>ویرایش پروفایل</h2>
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
                    <i class="fas fa-user"></i>
                    <input type="text" name="fullname" value="<?php echo htmlspecialchars($user['fullname']); ?>" required>
                </div>
                
                <div class="input-group">
                    <i class="fas fa-at"></i>
                    <input type="text" name="username" value="<?php echo htmlspecialchars($user['username']); ?>" required>
                </div>
                
                <div class="input-group">
                    <i class="fas fa-lock"></i>
                    <input type="password" name="password" placeholder="رمز عبور جدید (در صورت تمایل)">
                </div>
                
                <button type="submit" class="btn-primary">
                    <i class="fas fa-save"></i>
                    ذخیره تغییرات
                </button>
            </form>
            
            <div class="form-footer">
                <a href="verify_identity.php" class="back-link">
                    <i class="fas fa-check-circle"></i>
                    دریافت تیک آبی
                </a>
                <br>
                <a href="chat.php" class="back-link">
                    <i class="fas fa-arrow-right"></i>
                    بازگشت به صفحه اصلی
                </a>
            </div>
        </div>
    </div>
</body>
</html>
