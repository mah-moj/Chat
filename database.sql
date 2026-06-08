CREATE DATABASE chat_app_advanced;
USE chat_app_advanced;

-- جدول کاربران
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    fullname VARCHAR(100) NOT NULL,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    email VARCHAR(100) DEFAULT NULL,
    national_code VARCHAR(10) DEFAULT NULL,
    phone VARCHAR(15) DEFAULT NULL,
    is_verified BOOLEAN DEFAULT FALSE,
    has_blue_tick BOOLEAN DEFAULT FALSE,
    role ENUM('user', 'admin') DEFAULT 'user',
    ip_address VARCHAR(45) NOT NULL,
    avatar VARCHAR(255) DEFAULT 'default.png',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- جدول کانال‌ها
CREATE TABLE channels (
    id INT AUTO_INCREMENT PRIMARY KEY,
    channel_name VARCHAR(100) NOT NULL,
    channel_link VARCHAR(100) UNIQUE NOT NULL,
    owner_id INT NOT NULL,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (owner_id) REFERENCES users(id) ON DELETE CASCADE
);

-- جدول پیام‌ها (با پشتیبانی از رسانه)
CREATE TABLE messages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    username VARCHAR(50) NOT NULL,
    channel_id INT DEFAULT NULL,
    message TEXT,
    media_path VARCHAR(255) DEFAULT NULL,
    media_type ENUM('image','file',NULL) DEFAULT NULL,
    reply_to INT DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (channel_id) REFERENCES channels(id) ON DELETE CASCADE,
    FOREIGN KEY (reply_to) REFERENCES messages(id) ON DELETE SET NULL
);

-- جدول درخواست‌های احراز هویت
CREATE TABLE verify_requests (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    email VARCHAR(100),
    national_code VARCHAR(10),
    phone VARCHAR(15),
    status ENUM('pending','approved','rejected') DEFAULT 'pending',
    requested_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- درج ادمین
INSERT INTO users (fullname, username, password, role, ip_address, is_verified, has_blue_tick) VALUES 
('مدیر سایت', 'admin', MD5('admin123'), 'admin', '127.0.0.1', TRUE, TRUE);
