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
    <title>API测试 - 网站导航管理系统</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/api-docs.css">

</head>
<body>
    <nav class="navbar navbar-light fixed-top flex-md-nowrap p-0 shadow">
        <a class="navbar-brand col-md-3 col-lg-2 mr-0 px-3" href="#">网站导航管理系统</a>
        <div class="navbar-user ml-auto px-3">
            <span class="user-name">欢迎，<?php echo htmlspecialchars($_SESSION['username']); ?></span>
            <a href="logout.php" class="btn btn-sm btn-outline-secondary">退出</a>
        </div>
    </nav>

    <div class="container-fluid">
        <div class="row">
            <?php include_once 'include/sidebar.php'; ?>

            <main role="main" class="col-md-9 ml-sm-auto col-lg-10 px-md-4">
                <div class="page-header">
                    <h1>API测试工具</h1>
                    <p class="text-muted">使用此工具测试API接口的响应结果</p>
                </div>

                <div class="card">
                    <div class="card-header">
                        <h6 class="m-0 font-weight-bold">API请求</h6>
                    </div>
                    <div class="card-body">
                        <form id="apiForm" class="api-form">
                            <div class="mb-3">
                                <label for="apiEndpoint" class="form-label">API端点</label>
                                <select class="form-control" id="apiEndpoint" name="endpoint">
                                    <option value="get_categories">获取所有分类</option>
                                    <option value="get_websites">获取网站列表</option>
                                    <option value="get_all_data">获取所有数据</option>
                                </select>
                            </div>
                            
                            <div class="mb-3" id="categoryParam" style="display: none;">
                                <label for="category" class="form-label">分类标识符</label>
                                <input type="text" class="form-control" id="category" name="category" placeholder="留空表示获取所有分类的网站">
                                <div class="form-text">输入分类标识符以筛选特定分类的网站</div>
                            </div>
                            
                            <button type="submit" class="btn btn-primary">发送请求</button>
                        </form>
                    </div>
                </div>
                
                <div class="card">
                    <div class="card-header">
                        <h6 class="m-0 font-weight-bold">API响应</h6>
                    </div>
                    <div class="card-body">
                        <div id="response">// 响应结果将显示在这里</div>
                    </div>
                </div>
                
                <div class="card">
                    <div class="card-header">
                        <h6 class="m-0 font-weight-bold">请求URL</h6>
                    </div>
                    <div class="card-body">
                        <div id="requestUrl" class="mb-3">// 请求URL将显示在这里</div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const apiForm = document.getElementById('apiForm');
            const apiEndpoint = document.getElementById('apiEndpoint');
            const categoryParam = document.getElementById('categoryParam');
            const responseDiv = document.getElementById('response');
            const requestUrlDiv = document.getElementById('requestUrl');
            
            // 根据选择的API端点显示/隐藏参数
            apiEndpoint.addEventListener('change', function() {
                if (this.value === 'get_websites') {
                    categoryParam.style.display = 'block';
                } else {
                    categoryParam.style.display = 'none';
                }
            });
            
            // 处理表单提交
            apiForm.addEventListener('submit', function(e) {
                e.preventDefault();
                
                const endpoint = apiEndpoint.value;
                let url = `/api.php?action=${endpoint}`;
                
                // 如果是获取网站列表且指定了分类
                if (endpoint === 'get_websites' && document.getElementById('category').value) {
                    url += `&category=${document.getElementById('category').value}`;
                }
                
                // 显示请求URL
                requestUrlDiv.textContent = url;
                
                // 发送API请求
                fetch(url)
                    .then(response => response.json())
                    .then(data => {
                        responseDiv.textContent = JSON.stringify(data, null, 2);
                    })
                    .catch(error => {
                        responseDiv.textContent = `请求出错: ${error.message}`;
                    });
            });
        });
    </script>
</body>
</html>