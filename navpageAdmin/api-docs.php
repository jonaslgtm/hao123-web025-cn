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
    <!-- 顶部导航栏 -->      
    <?php include_once 'include/navbar.php'; ?>

    <div class="container-fluid">
        <div class="row">
            <?php include_once 'include/sidebar.php'; ?>

            <!-- 主内容区域 -->
            <main role="main">
                <div class="page-header">
                    <h1>API接口文档</h1>
                    <p class="text-muted">本页面提供API接口的使用说明，您可以通过这些接口获取网站导航数据以及文章数据。</p>
                </div>

                <div class="card">
                    <div class="card-header">
                        <h6 class="m-0 font-weight-bold">API概述</h6>
                    </div>
                    <div class="card-body">
                        <p>网站导航管理系统提供了RESTful API接口，允许前端应用获取网站导航数据和文章数据。所有API接口都使用GET请求方式，并返回JSON格式的数据。</p>
                        <p>API基础URL：<code><?php echo (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://" . $_SERVER['HTTP_HOST']; ?>/api.php</code></p>
                        <p>所有API响应都遵循以下格式：</p>
                        <pre>{
  "code": 1,  // 1表示成功，0表示失败
  "data": []  // 返回的数据
}</pre>
                    
<p><a href="api-test.php"> API接口测试 </a></p>

</div>
                </div>

                <div class="card mb-4">
                    <div class="card-header">
                        <h3 class="h5 mb-0">API端点</h3>
                    </div>
                    <div class="card-body">
                        <h4 class="h6">1. 获取所有分类</h4>
                        <p><strong>请求:</strong> <code>GET <?php echo $baseUrl; ?>?op=get_categories</code></p>
                        <p><strong>参数:</strong> 无</p>
                        <p><strong>响应示例:</strong></p>
                        <pre><code>{
    "code": 1,
    "data": [
        {
            "id": 1,
            "name": "全部",
            "identifier": "all",
            "description": "所有网站"
        },
        {
            "id": 2,
            "name": "常用",
            "identifier": "common",
            "description": "常用网站"
        }
    ]
}</code></pre>
                        
                        <hr class="my-4">
                        
                        <h4 class="h6">2. 获取网站列表</h4>
                        <p><strong>请求:</strong> <code>GET <?php echo $baseUrl; ?>?op=get_websites</code></p>
                        <p><strong>参数:</strong></p>
                        <ul>
                            <li><code>category</code> (可选) - 分类标识符，如果提供，则只返回该分类下的网站</li>
                        </ul>
                        <p><strong>响应示例:</strong></p>
                        <pre><code>{
    "code": 1,
    "data": [
        {
            "id": 1,
            "name": "百度",
            "url": "https://www.baidu.com",
            "description": "中文搜索引擎",
            "icon": "baidu.png",
            "category_id": 2,
            "display_order": 1
        },
        {
            "id": 2,
            "name": "谷歌",
            "url": "https://www.google.com",
            "description": "全球最大搜索引擎",
            "icon": "google.png",
            "category_id": 2,
            "display_order": 2
        }
    ]
}</code></pre>
                        
                        <hr class="my-4">
                        
                        <h4 class="h6">3. 获取所有文章分类</h4>
                        <p><strong>请求:</strong> <code>GET <?php echo $baseUrl; ?>?op=get_article_categories</code></p>
                        <p><strong>参数:</strong> 无</p>
                        <p><strong>响应示例:</strong></p>
                        <pre><code>{
    "code": 1,
    "data": [
        {
            "id": 1,
            "name": "新闻",
            "identifier": "news",
            "description": "新闻资讯"
        },
        {
            "id": 2,
            "name": "教程",
            "identifier": "tutorials",
            "description": "使用教程"
        }
    ]
}</code></pre>
                        
                        <hr class="my-4">
                        
                        <h4 class="h6">4. 获取文章列表</h4>
                        <p><strong>请求:</strong> <code>GET <?php echo $baseUrl; ?>?op=get_articles</code></p>
                        <p><strong>参数:</strong></p>
                        <ul>
                            <li><code>category_id</code> (可选) - 文章分类ID，如果提供，则只返回该分类下的文章</li>
                            <li><code>tag</code> (可选) - 标签名称，如果提供，则只返回包含该标签的文章</li>
                            <li><code>limit</code> (可选) - 每页显示的文章数量，默认为10</li>
                            <li><code>page</code> (可选) - 页码，默认为1</li>
                            <li><code>featured</code> (可选) - 是否只显示推荐文章，1表示只显示推荐文章，0表示只显示非推荐文章，-1表示不筛选，默认为-1</li>
                        </ul>
                        <p><strong>响应示例:</strong></p>
                        <pre><code>{
    "code": 1,
    "data": {
        "articles": [
            {
                "id": 1,
                "title": "如何使用导航系统",
                "content": "&lt;p&gt;这是文章内容...&lt;/p&gt;",
                "thumbnail": "/uploads/articles/2023/05/thumbnail.jpg",
                "author": "管理员",
                "category": {
                    "id": 2,
                    "name": "教程",
                    "identifier": "tutorials"
                },
                "tags": ["导航", "使用指南"],
                "featured": true,
                "created_at": "2023-05-10 14:30:00",
                "updated_at": "2023-05-10 15:20:00"
            },
            {
                "id": 2,
                "title": "系统更新公告",
                "content": "&lt;p&gt;系统已更新到最新版本...&lt;/p&gt;",
                "thumbnail": "/uploads/articles/2023/05/update.jpg",
                "author": "管理员",
                "category": {
                    "id": 1,
                    "name": "新闻",
                    "identifier": "news"
                },
                "tags": ["更新", "公告"],
                "featured": false,
                "created_at": "2023-05-08 09:15:00",
                "updated_at": "2023-05-08 09:15:00"
            }
        ],
        "pagination": {
            "total": 25,
            "per_page": 10,
            "current_page": 1,
            "last_page": 3
        }
    }
}</code></pre>
                        
                        <hr class="my-4">
                        
                        <h4 class="h6">5. 获取文章详情</h4>
                        <p><strong>请求:</strong> <code>GET <?php echo $baseUrl; ?>?op=get_article&id=1</code></p>
                        <p><strong>参数:</strong></p>
                        <ul>
                            <li><code>id</code> (必填) - 文章ID</li>
                        </ul>
                        <p><strong>响应示例:</strong></p>
                        <pre><code>{
    "code": 1,
    "data": {
        "id": 1,
        "title": "如何使用导航系统",
        "content": "&lt;p&gt;这是文章详细内容...&lt;/p&gt;&lt;p&gt;包含HTML格式的富文本内容&lt;/p&gt;",
        "thumbnail": "/uploads/articles/2023/05/thumbnail.jpg",
        "author": "管理员",
        "category": {
            "id": 2,
            "name": "教程",
            "identifier": "tutorials"
        },
        "tags": ["导航", "使用指南"],
        "featured": true,
        "created_at": "2023-05-10 14:30:00",
        "updated_at": "2023-05-10 15:20:00"
    }
}</code></pre>
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