<?php
require_once '../include/config.php';

// 检查用户是否已登录
requireLogin();

// 获取统计数据
$conn = connectDB();
$websiteCount = $conn->query("SELECT COUNT(*) as count FROM websites")->fetch_assoc()['count'];
$categoryCount = $conn->query("SELECT COUNT(*) as count FROM categories WHERE identifier != 'all'")->fetch_assoc()['count'];
$conn->close();
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>仪表盘 - 网站导航管理系统</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <!-- 顶部导航栏 -->
    <nav class="navbar navbar-light fixed-top flex-md-nowrap shadow">
        <a class="navbar-brand" href="dashboard.php">网站导航管理系统</a>
        <div class="navbar-user">
            <span class="user-name"><i class="bi bi-person-circle me-1"></i><?php echo htmlspecialchars($_SESSION['username']); ?></span>
            <a href="logout.php" class="btn btn-outline-secondary">退出 <i class="bi bi-box-arrow-right"></i></a>
        </div>
    </nav>

    <div class="container-fluid">
        <div class="row">
            <?php include_once 'include/sidebar.php'; ?>

            <!-- 主内容区域 -->
            <main role="main">
                <div class="welcome-message">
                    <h1>仪表盘</h1>
                    <p class="text-muted">欢迎使用网站导航管理系统，您可以在这里管理您的网站导航数据。</p>
                </div>

                <div class="action-buttons">
                    <a href="websites.php?action=add" class="btn btn-primary">
                        <i class="bi bi-plus-circle"></i> 添加新网站
                    </a>
                    <a href="categories.php?action=add" class="btn btn-success">
                        <i class="bi bi-plus-circle"></i> 添加新分类
                    </a>
                </div>

                <!-- 统计卡片 -->
                <div class="row">
                    <div class="col-xl-6 col-md-6">
                        <div class="card stat-card stat-websites">
                            <div class="card-body">
                                <div class="row align-items-center">
                                    <div class="col">
                                        <div class="title text-uppercase mb-1">网站总数</div>
                                        <div class="count"><?php echo $websiteCount; ?></div>
                                    </div>
                                    <i class="bi bi-globe icon"></i>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-6 col-md-6">
                        <div class="card stat-card stat-categories">
                            <div class="card-body">
                                <div class="row align-items-center">
                                    <div class="col">
                                        <div class="title text-uppercase mb-1">分类总数</div>
                                        <div class="count"><?php echo $categoryCount; ?></div>
                                    </div>
                                    <i class="bi bi-tags icon"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 系统信息卡片 -->
                <div class="card system-info-card">
                    <div class="card-header">
                        <h6 class="m-0">系统信息</h6>
                        <span class="badge bg-primary">v1.0</span>
                    </div>
                    <div class="card-body">
                        <p>本系统用于管理网站导航页面的数据，您可以通过以下功能进行操作：</p>
                        <ul>
                            <li><strong>网站管理</strong>：添加、编辑、删除导航网站</li>
                            <li><strong>分类管理</strong>：管理网站分类</li>
                            <li><strong>API接口</strong>：为前端提供数据接口</li>
                        </ul>
                        <p>前端页面将通过API获取最新的导航数据，确保内容实时更新。</p>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>