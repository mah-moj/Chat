<?php
require_once 'config/database.php';
require_once 'includes/functions.php';
require_once 'includes/auth.php';

// بررسی اینکه کاربر قبلاً درخواست نداده باشد
$stmt = $pdo->prepare("SELECT * FROM verify_requests WHERE user_id = ? AND status != 'rejected'");
$stmt->execute([$_SESSION['user_id']]);
$existing_request = $stmt->fetch();

$error = '';
$success = '';

if ($existing_request) {
    if ($existing_request['status'] == 'pending') {
        $error = 'درخواست شما قبلاً ثبت شده و در حال بررسی است';
    } elseif ($existing_request['status'] == 'approved') {
        $success = 'شما قبلاً تایید شده‌اید و تیک آبی دریافت کرده‌اید';
    }
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && !$existing_request) {
    $email = trim($_POST['email']);
    $national_code = trim($_POST['national_code']);
    $phone = trim($_POST['phone']);
    
    if (empty($email) || empty($national_code) || empty($phone)) {
        $error = 'تمامی فیلدها الزامی است';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'ایمیل معتبر نیست';
    } elseif (!preg_match('/^[0-9]{10}$/', $national_code)) {
        $error = 'کد ملی باید ۱۰ رقم باشد';
    } elseif (!preg_match('/^09[0-9]{9}$/', $phone)) {
        $error = 'شماره تماس معتبر نیست';
    } else {
        $stmt = $pdo->prepare("INSERT INTO verify_requests (user_id, email, national_code, phone) VALUES (?, ?, ?, ?)");
        if ($stmt->execute([$_SESSION['user_id'], $email, $national_code, $phone])) {
            $success = 'درخواست شما ثبت شد. پس از تایید ادمین، تیک آبی دریافت خواهید کرد';
        } else {
            $error = 'خطا در ثبت درخواست';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>احراز هویت و دریافت تیک آبی</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Vazir&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <div class="container">
        <div class="form-box">
            <div class="form-header">
                <i class="fas fa-check-circle"></i>
                <h2>دریافت تیک آبی</h2>
                <p class="subtitle">با احراز هویت، تیک آبی رسمی دریافت کنید</p>
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
            
            <?php if(!$existing_request || $existing_request['status'] == 'rejected'): ?>
                <form method="POST">
                    <div class="input-group">
                        <i class="fas fa-envelope"></i>
                        <input type="email" name="email" placeholder="ایمیل" required>
                    </div>
                    
                    <div class="input-group">
                        <i class="fas fa-id-card"></i>
                        <input type="text" name="national_code" placeholder="کد ملی (۱۰ رقم)" maxlength="10" required>
                    </div>
                    
                    <div class="input-group">
                        <i class="fas fa-phone"></i>
                        <input type="tel" name="phone" placeholder="شماره تماس (مثال: 09123456789)" required>
                    </div>
                    
                    <button type="submit" class="btn-primary">
                        <i class="fas fa-paper-plane"></i>
                        ارسال درخواست
                    </button>
                </form>
            <?php endif; ?>
            
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
