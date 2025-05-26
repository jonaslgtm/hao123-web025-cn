<?php
// 数据库连接配置
define('DB_HOST', 'localhost');
define('DB_USER', 'root'); // 请修改为您的数据库用户名
define('DB_PASS', 'mysql123456'); // 请修改为您的数据库密码
define('DB_NAME', 'navpage_db');

// 建立数据库连接
function connectDB() {
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    
    // 检查连接
    if ($conn->connect_error) {
        die("连接失败: " . $conn->connect_error);
    }
    
    // 设置字符集
    $conn->set_charset("utf8mb4");
    
    return $conn;
}

// 开启会话
session_start();

// 检查用户是否已登录
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

// 如果用户未登录且不是登录页面，则重定向到登录页面
function requireLogin() {
    if (!isLoggedIn()) {
        $currentPage = basename($_SERVER['PHP_SELF']);
        if ($currentPage !== 'login.php' && $currentPage !== 'index.php') {
            header('Location: login.php');
            exit;
        }
    }
}