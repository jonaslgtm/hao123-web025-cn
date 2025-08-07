<?php
require_once '../include/config.php';

// 检查用户是否已登录
requireLogin();

// 获取统计数据
$conn = connectDB();
$websiteCount = $conn->query("SELECT COUNT(*) as count FROM websites")->fetch_assoc()['count'];
$categoryCount = $conn->query("SELECT COUNT(*) as count FROM categories WHERE identifier != 'all'")->fetch_assoc()['count'];

// 获取文章统计数据
$articleCount = 0;
$articleCategoryCount = 0;

// 检查文章表是否存在
$tableExists = $conn->query("SHOW TABLES LIKE 'articles'")->num_rows > 0;
if ($tableExists) {
    $articleCount = $conn->query("SELECT COUNT(*) as count FROM articles")->fetch_assoc()['count'];
}

// 检查文章分类表是否存在
$tableExists = $conn->query("SHOW TABLES LIKE 'article_categories'")->num_rows > 0;
if ($tableExists) {
    $articleCategoryCount = $conn->query("SELECT COUNT(*) as count FROM article_categories")->fetch_assoc()['count'];
}

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
    <?php include_once 'include/navbar.php'; ?>

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
                    <a href="articles.php?action=add" class="btn btn-info">
                        <i class="bi bi-plus-circle"></i> 添加新文章
                    </a>
                    <a href="article-categories.php?action=add" class="btn btn-secondary">
                        <i class="bi bi-plus-circle"></i> 添加文章分类
                    </a>
                </div>

                <!-- 统计卡片 -->
                <div class="row">
                    <div class="col-xl-3 col-md-6">
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

                    <div class="col-xl-3 col-md-6">
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
                    
                    <div class="col-xl-3 col-md-6">
                        <div class="card stat-card" style="background-color: #17a2b8; color: white;">
                            <div class="card-body">
                                <div class="row align-items-center">
                                    <div class="col">
                                        <div class="title text-uppercase mb-1">文章总数</div>
                                        <div class="count"><?php echo $articleCount; ?></div>
                                    </div>
                                    <i class="bi bi-file-text icon"></i>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-3 col-md-6">
                        <div class="card stat-card" style="background-color: #6c757d; color: white;">
                            <div class="card-body">
                                <div class="row align-items-center">
                                    <div class="col">
                                        <div class="title text-uppercase mb-1">文章分类</div>
                                        <div class="count"><?php echo $articleCategoryCount; ?></div>
                                    </div>
                                    <i class="bi bi-folder icon"></i>
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
                        <p>本系统用于管理网站导航页面和文章内容的数据，您可以通过以下功能进行操作：</p>
                        <ul>
                            <li><strong>网站管理</strong>：添加、编辑、删除导航网站</li>
                            <li><strong>分类管理</strong>：管理网站分类</li>
                            <li><strong>文章管理</strong>：发布、编辑、删除文章内容，支持富文本编辑</li>
                            <li><strong>文章分类</strong>：管理文章分类</li>
                            <li><strong>API接口</strong>：为前端提供数据接口</li>
                        </ul>
                        <p>前端页面将通过API获取最新的导航数据和文章内容，确保内容实时更新。</p>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>