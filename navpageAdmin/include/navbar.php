<?php
// 检查是否已定义 SESSION
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 禁用浏览器缓存
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
?>
<!-- 顶部导航栏 -->
<nav class="navbar navbar-light fixed-top flex-md-nowrap shadow">
    <a class="navbar-brand" href="dashboard.php">网站导航管理系统</a>    
    <div class="navbar-user">        
        <a href="../index.html" class="btn btn-link" target="_blank" title="在新标签页打开首页"><i class="bi bi-house"></i> 访问首页</a>
        <span class="user-name">欢迎，<i class="bi bi-person-circle me-1"></i><?php echo htmlspecialchars($_SESSION['username']); ?></span>
        <a href="logout.php" class="btn btn-outline-secondary">退出 <i class="bi bi-box-arrow-right"></i></a>
    </div>
</nav>
