<!-- 侧边栏导航 -->
<nav id="sidebarMenu" class="sidebar">
    <div class="sidebar-sticky">
        <ul class="nav flex-column">
            <li class="nav-item">
                <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'active' : ''; ?>" href="dashboard.php">
                    <i class="bi bi-speedometer2"></i> 仪表盘
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'websites.php' ? 'active' : ''; ?>" href="websites.php">
                    <i class="bi bi-globe"></i> 网站管理
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'categories.php' ? 'active' : ''; ?>" href="categories.php">
                    <i class="bi bi-tags"></i> 分类管理
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'articles.php' ? 'active' : ''; ?>" href="articles.php">
                    <i class="bi bi-file-text"></i> 文章管理
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'article-categories.php' ? 'active' : ''; ?>" href="article-categories.php">
                    <i class="bi bi-folder"></i> 文章分类
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'api-docs.php' ? 'active' : ''; ?>" href="api-docs.php">
                    <i class="bi bi-code-slash"></i> API接口
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'api-test.php' ? 'active' : ''; ?>" href="api-test.php">
                    <i class="bi bi-lightning"></i> API测试
                </a>
            </li>
        </ul>
    </div>
</nav>