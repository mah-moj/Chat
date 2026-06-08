<?php
require_once 'config/database.php';
require_once 'includes/functions.php';

if (isLoggedIn()) {
    header('Location: chat.php');
    exit();
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $fullname = trim($_POST['fullname']);
    $username = trim($_POST['username']);
    $password = md5($_POST['password']);
    $ip = getUserIp();
    
    // اعتبارسنجی
    if (empty($fullname) || empty($username) || empty($_POST['password'])) {
        $error = 'تمامی فیلدها الزامی است';
    } elseif (checkDuplicateIp($pdo, $ip)) {
        $error = 'از این دستگاه قبلاً حسابی ساخته شده است';
    } elseif (checkDuplicateUsername($pdo, $username)) {
        $error = 'این نام کاربری قبلاً ثبت شده است';
    } elseif (strlen($_POST['password']) < 4) {
        $error = 'رمز عبور باید حداقل ۴ کاراکتر باشد';
    } else {
        try {
            $stmt = $pdo->prepare("INSERT INTO users (fullname, username, password, ip_address) VALUES (?, ?, ?, ?)");
            $stmt->execute([$fullname, $username, $password, $ip]);
            
            $_SESSION['user_id'] = $pdo->lastInsertId();
            $_SESSION['username'] = $username;
            $_SESSION['role'] = 'user';
            
            header('Location: chat.php');
            exit();
        } catch(PDOException $e) {
            $error = 'خطا در ثبت نام: ' . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>ثبت نام - پیامرسان حرفه‌ای</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Vazir&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <div class="container">
        <div class="form-box">
            <div class="form-header">
                <i class="fas fa-user-plus"></i>
                <h2>ثبت نام در پیامرسان</h2>
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
                    <input type="text" name="fullname" placeholder="نام و نام خانوادگی" required>
                </div>
                
                <div class="input-group">
                    <i class="fas fa-at"></i>
                    <input type="text" name="username" placeholder="نام کاربری" required>
                </div>
                
                <div class="input-group">
                    <i class="fas fa-lock"></i>
                    <input type="password" name="password" placeholder="رمز عبور" required>
                </div>
                
                <button type="submit" class="btn-primary">
                    <i class="fas fa-arrow-right"></i>
                    ثبت نام
                </button>
            </form>
            
            <div class="form-footer">
                <p>قبلاً ثبت نام کردید؟ <a href="login.php">ورود به حساب</a></p>
            </div>
        </div>
    </div>
</body>
</html>
