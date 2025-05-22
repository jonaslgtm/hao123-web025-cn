<?php
// 检查是否已定义 SESSION
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<!-- 顶部导航栏 -->
<nav class="navbar navbar-light fixed-top flex-md-nowrap shadow">
    <a class="navbar-brand" href="dashboard.php">网站导航管理系统</a>
    <div class="navbar-user">
        <span class="user-name"><i class="bi bi-person-circle me-1"></i><?php echo htmlspecialchars($_SESSION['username']); ?></span>
        <a href="logout.php" class="btn btn-outline-secondary">退出 <i class="bi bi-box-arrow-right"></i></a>
    </div>
</nav>
