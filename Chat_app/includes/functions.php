
<?php
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function isAdmin() {
    return isset($_SESSION['role']) && $_SESSION['role'] == 'admin';
}

function getUserIp() {
    if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
        return $_SERVER['HTTP_CLIENT_IP'];
    } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        return $_SERVER['HTTP_X_FORWARDED_FOR'];
    } else {
        return $_SERVER['REMOTE_ADDR'];
    }
}

function checkDuplicateIp($pdo, $ip) {
    $stmt = $pdo->prepare("SELECT id FROM users WHERE ip_address = ?");
    $stmt->execute([$ip]);
    return $stmt->rowCount() > 0;
}

function checkDuplicateUsername($pdo, $username) {
    $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ?");
    $stmt->execute([$username]);
    return $stmt->rowCount() > 0;
}

function timeAgo($timestamp) {
    $time_ago = strtotime($timestamp);
    $current_time = time();
    $time_difference = $current_time - $time_ago;
    $seconds = $time_difference;
    
    $minutes = round($seconds / 60);
    $hours = round($seconds / 3600);
    $days = round($seconds / 86400);
    $weeks = round($seconds / 604800);
    $months = round($seconds / 2629440);
    $years = round($seconds / 31553280);
    
    if ($seconds <= 60) {
        return "لحظاتی پیش";
    } else if ($minutes <= 60) {
        return "$minutes دقیقه پیش";
    } else if ($hours <= 24) {
        return "$hours ساعت پیش";
    } else if ($days <= 7) {
        return "$days روز پیش";
    } else if ($weeks <= 4.3) {
        return "$weeks هفته پیش";
    } else if ($months <= 12) {
        return "$months ماه پیش";
    } else {
        return "$years سال پیش";
    }
}
?>
