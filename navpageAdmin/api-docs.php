<?php
require_once '../include/config.php';

// 检查用户是否已登录
requireLogin();
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>API接口 - 网站导航管理系统</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/api-docs.css">
</head>
<body>
    <nav class="navbar navbar-light fixed-top flex-md-nowrap shadow">
        <a class="navbar-brand" href="dashboard.php">网站导航管理系统</a>
        <div class="navbar-user">
            <span class="user-name">欢迎，<?php echo htmlspecialchars($_SESSION['username']); ?></span>
            <a href="logout.php" class="btn btn-sm btn-outline-secondary">退出</a>
        </div>
    </nav>

    <div class="container-fluid">
        <div class="row">
            <?php include_once 'include/sidebar.php'; ?>

            <!-- 主内容区域 -->
            <main role="main">
                <div class="page-header">
                    <h1>API接口文档</h1>
                    <p class="text-muted">本页面提供API接口的使用说明，您可以通过这些接口获取网站导航数据。</p>
                </div>

                <div class="card">
                    <div class="card-header">
                        <h6 class="m-0 font-weight-bold">API概述</h6>
                    </div>
                    <div class="card-body">
                        <p>网站导航管理系统提供了RESTful API接口，允许前端应用获取网站导航数据。所有API接口都使用GET请求方式，并返回JSON格式的数据。</p>
                        <p>API基础URL：<code><?php echo (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://" . $_SERVER['HTTP_HOST']; ?>/api.php</code></p>
                        <p>所有API响应都遵循以下格式：</p>
                        <pre>{
  "code": 1,  // 1表示成功，0表示失败
  "data": []  // 返回的数据
}</pre>
                    
<p><a href="api-test.php"> API接口测试 </a></p>

</div>
                </div>

                <div class="card">
                    <div class="card-header">
                        <h6 class="m-0 font-weight-bold">API端点</h6>
                    </div>
                    <div class="card-body">
                        <!-- 获取所有分类 -->
                        <div class="api-endpoint">
                            <h5>获取所有分类</h5>
                            <div class="description">获取系统中所有可用的分类信息。</div>
                            <div class="url">GET /api.php?action=get_categories</div>
                            <div class="response">
                                <strong>响应示例：</strong>
                                <pre>{
  "code": 1,
  "data": [
    {
      "id": 1,
      "name": "全部",
      "identifier": "all"
    },
    {
      "id": 2,
      "name": "资讯",
      "identifier": "news"
    },
    {
      "id": 3,
      "name": "科技",
      "identifier": "tech"
    }
    // 更多分类...
  ]
}</pre>
                            </div>
                        </div>

                        <!-- 获取网站列表 -->
                        <div class="api-endpoint">
                            <h5>获取网站列表</h5>
                            <div class="description">获取系统中的网站列表，可以按分类筛选。</div>
                            <div class="url">GET /api.php?action=get_websites</div>
                            <div class="params">
                                <strong>可选参数：</strong>
                                <div class="param"><code>category</code> - 分类标识符，用于筛选特定分类的网站。如果不提供，则返回所有网站。</div>
                                <div class="example">示例：<code>/api.php?action=get_websites&category=tech</code></div>
                            </div>
                            <div class="response">
                                <strong>响应示例：</strong>
                                <pre>{
  "code": 1,
  "data": [
    {
      "id": 1,
      "name": "GitHub",
      "url": "https://github.com/",
      "category": "tech",
      "img": "https://github.githubassets.com/assets/GitHub-Mark-ea2971cee799.png",
      "display_order": 1
    },
    {
      "id": 2,
      "name": "Apple",
      "url": "https://www.apple.com/cn/",
      "category": "tech",
      "img": "https://www.apple.com/ac/globalnav/7/zh_CN/images/be15095f-5a20-57d0-ad14-cf4c638e223a/globalnav_apple_image__b5er5ngrzxqq_large.svg",
      "display_order": 2
    }
    // 更多网站...
  ]
}</pre>
                            </div>
                        </div>

                        <!-- 获取所有数据 -->
                        <div class="api-endpoint">
                            <h5>获取所有数据</h5>
                            <div class="description">一次性获取所有分类和网站数据。</div>
                            <div class="url">GET /api.php?action=get_all_data</div>
                            <div class="response">
                                <strong>响应示例：</strong>
                                <pre>{
  "code": 1,
  "data": {
    "categories": [
      {
        "id": 1,
        "name": "全部",
        "identifier": "all"
      },
      // 更多分类...
    ],
    "websites": [
      {
        "name": "GitHub",
        "url": "https://github.com/",
        "category": "tech",
        "img": "https://github.githubassets.com/assets/GitHub-Mark-ea2971cee799.png"
      },
      // 更多网站...
    ]
  }
}</pre>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card integration-example">
                    <div class="card-header">
                        <h6 class="m-0 font-weight-bold">Vue集成示例</h6>
                    </div>
                    <div class="card-body">
                        <p>以下是将API与Vue前端组件集成的示例代码：</p>
                        <pre>// 在Vue组件中获取数据
import { ref, computed, onMounted } from 'vue';

// API URL（根据您的实际部署情况修改）
const API_URL = '<?php echo (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://" . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']); ?>/api.php';

// 网站数据和分类数据
const websites = ref([]);
const categories = ref([]);
const activeCategory = ref('all');

// 获取所有数据
async function fetchData() {
  try {
    const response = await fetch(`${API_URL}?action=get_all_data`);
    if (!response.ok) {
      throw new Error('API请求失败');
    }
    const data = await response.json();
    if (data.code === 1 && data.data) {
      websites.value = data.data.websites;
      
      // 转换分类数据格式
      categories.value = [
        { id: 'all', name: '全部' },
        ...data.data.categories.filter(cat => cat.identifier !== 'all')
          .map(cat => ({ id: cat.identifier, name: cat.name }))
      ];
    }
  } catch (error) {
    console.error('获取数据失败:', error);
  }
}

// 根据当前分类过滤网站
const filteredWebsites = computed(() => {
  if (activeCategory.value === 'all') {
    return websites.value;
  }
  return websites.value.filter(site => site.category === activeCategory.value);
});

onMounted(() => {
  fetchData();
});</pre>
                        <p>完整的集成示例可以在 <a href="vue-integration-example.js" target="_blank">vue-integration-example.js</a> 文件中找到。</p>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header">
                        <h6 class="m-0 font-weight-bold">跨域问题处理</h6>
                    </div>
                    <div class="card-body">
                        <p>API已配置为允许跨域请求（CORS），但如果您在生产环境中遇到跨域问题，可以考虑以下解决方案：</p>
                        <ol>
                            <li>确保前端和API部署在同一域名下</li>
                            <li>使用代理服务器转发请求</li>
                            <li>在服务器配置中添加更多CORS头信息</li>
                        </ol>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>