<?php
require_once '../include/config.php';

// 如果已登录，重定向到仪表盘
if (isLoggedIn()) {
    header('Location: dashboard.php');
    exit;
}

$error = '';

// 处理登录请求
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    
    if (empty($username) || empty($password)) {
        $error = '请输入用户名和密码';
    } else {
        $conn = connectDB();
        
        // 防止SQL注入
        $stmt = $conn->prepare("SELECT id, username, password FROM users WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();
            
            // 验证密码 (使用password_verify函数验证哈希密码)
            if (password_verify($password, $user['password'])) {
                // 登录成功，设置会话
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                
                // 重定向到仪表盘
                header('Location: dashboard.php');
                exit;
            } else {
                $error = '密码不正确';
            }
        } else {
            $error = '用户名不存在';
        }
        
        $stmt->close();
        $conn->close();
    }
}
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>网站导航管理系统 - 登录</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="assets/css/login.css">
</head>
<body class="login-page">
    <div class="login-container">
        <div class="login-icon">
            <i class="bi bi-shield-lock"></i>
        </div>
        <h2>网站导航管理系统</h2>
        
        <?php if (!empty($error)): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <form method="post" action="">
            <div class="form-group">
                <label for="username" class="form-label">用户名</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-person"></i></span>
                    <input type="text" class="form-control" id="username" name="username" placeholder="请输入用户名" required>
                </div>
            </div>
            
            <div class="form-group">
                <label for="password" class="form-label">密码</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-key"></i></span>
                    <input type="password" class="form-control" id="password" name="password" placeholder="请输入密码" required>
                </div>
            </div>
            
            <button type="submit" class="btn btn-primary">登录系统</button>
        </form>
        
        <div class="text-center mt-3">
            <p class="text-muted">默认账户: admin / password</p>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>