<?php
require_once '../include/config.php';

// 检查用户是否已登录
requireLogin();

$conn = connectDB();
$message = '';
$error = '';

// 处理删除请求
if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['id'])) {
    $id = intval($_GET['id']);
    
    $stmt = $conn->prepare("DELETE FROM websites WHERE id = ?");
    $stmt->bind_param("i", $id);
    
    if ($stmt->execute()) {
        $message = "网站已成功删除";
    } else {
        $error = "删除失败: " . $conn->error;
    }
    
    $stmt->close();
}

// 处理添加/编辑请求
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'] ?? '';
    $url = $_POST['url'] ?? '';
    $category_id = intval($_POST['category_id'] ?? 0);
    $img_url = $_POST['img_url'] ?? '';
    $display_order = intval($_POST['display_order'] ?? 0);
    
    // 验证输入
    if (empty($name) || empty($url) || empty($img_url) || $category_id <= 0) {
        $error = "请填写所有必填字段";
    } else {
        // 添加新网站
        if (!isset($_POST['id'])) {
            $stmt = $conn->prepare("INSERT INTO websites (name, url, category_id, img_url, display_order) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param("ssisi", $name, $url, $category_id, $img_url, $display_order);
            
            if ($stmt->execute()) {
                $message = "网站已成功添加";
                // 重定向到网站列表
                header("Location: websites.php?success=added");
                exit;
            } else {
                $error = "添加失败: " . $conn->error;
            }
        } 
        // 更新现有网站
        else {
            $id = intval($_POST['id']);
            $stmt = $conn->prepare("UPDATE websites SET name = ?, url = ?, category_id = ?, img_url = ?, display_order = ? WHERE id = ?");
            $stmt->bind_param("ssisii", $name, $url, $category_id, $img_url, $display_order, $id);
            
            if ($stmt->execute()) {
                $message = "网站已成功更新";
                // 重定向到网站列表
                header("Location: websites.php?success=updated");
                exit;
            } else {
                $error = "更新失败: " . $conn->error;
            }
        }
        
        $stmt->close();
    }
}

// 获取所有分类
$categories = [];
$categoryResult = $conn->query("SELECT id, name FROM categories WHERE identifier != 'all' ORDER BY name");
while ($row = $categoryResult->fetch_assoc()) {
    $categories[] = $row;
}

// 获取要编辑的网站
$editWebsite = null;
if (isset($_GET['action']) && $_GET['action'] == 'edit' && isset($_GET['id'])) {
    $id = intval($_GET['id']);
    
    $stmt = $conn->prepare("SELECT * FROM websites WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 1) {
        $editWebsite = $result->fetch_assoc();
    }
    
    $stmt->close();
}

// 获取所有网站
$websites = [];
$websiteResult = $conn->query("SELECT w.*, c.name as category_name FROM websites w JOIN categories c ON w.category_id = c.id ORDER BY w.display_order, w.name");
while ($row = $websiteResult->fetch_assoc()) {
    $websites[] = $row;
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>网站管理 - 网站导航管理系统</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="assets/css/style.css">
     <link rel="stylesheet" href="assets/css/websites.css">
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
                <div class="page-header">
                    <h1><?php echo isset($_GET['action']) && ($_GET['action'] == 'add' || $_GET['action'] == 'edit') ? ($_GET['action'] == 'add' ? '添加新网站' : '编辑网站') : '网站管理'; ?></h1>
                </div>

                <?php if (!empty($message)): ?>
                    <div class="alert alert-success"><?php echo $message; ?></div>
                <?php endif; ?>

                <?php if (!empty($error)): ?>
                    <div class="alert alert-danger"><?php echo $error; ?></div>
                <?php endif; ?>

                <?php if (isset($_GET['success'])): ?>
                    <div class="alert alert-success">
                        <?php echo $_GET['success'] == 'added' ? '网站已成功添加' : '网站已成功更新'; ?>
                    </div>
                <?php endif; ?>

                <?php if (isset($_GET['action']) && ($_GET['action'] == 'add' || $_GET['action'] == 'edit')): ?>
                    <!-- 添加/编辑表单 -->
                    <div class="card">
                        <div class="card-header">
                            <h6 class="m-0 font-weight-bold"><?php echo $_GET['action'] == 'add' ? '添加新网站' : '编辑网站'; ?></h6>
                        </div>
                        <div class="card-body">
                            <form method="post" action="">
                                <?php if ($_GET['action'] == 'edit' && $editWebsite): ?>
                                    <input type="hidden" name="id" value="<?php echo $editWebsite['id']; ?>">
                                <?php endif; ?>

                                <div class="form-group">
                                    <label for="name">网站名称 <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="name" name="name" required value="<?php echo $editWebsite ? htmlspecialchars($editWebsite['name']) : ''; ?>">
                                </div>

                                <div class="form-group">
                                    <label for="url">网站URL <span class="text-danger">*</span></label>
                                    <input type="url" class="form-control" id="url" name="url" required value="<?php echo $editWebsite ? htmlspecialchars($editWebsite['url']) : ''; ?>">
                                </div>

                                <div class="form-group">
                                    <label for="category_id">分类 <span class="text-danger">*</span></label>
                                    <select class="form-control" id="category_id" name="category_id" required>
                                        <option value="">选择分类</option>
                                        <?php foreach ($categories as $category): ?>
                                            <option value="<?php echo $category['id']; ?>" <?php echo $editWebsite && $editWebsite['category_id'] == $category['id'] ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($category['name']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>

                                <div class="form-group">
                                    <label for="img_url">图标URL <span class="text-danger">*</span></label>
                                    <input type="url" class="form-control" id="img_url" name="img_url" required value="<?php echo $editWebsite ? htmlspecialchars($editWebsite['img_url']) : ''; ?>">
                                    <small class="form-text text-muted">输入网站图标的URL地址</small>
                                </div>

                                <div class="form-group">
                                    <label for="display_order">显示顺序</label>
                                    <input type="number" class="form-control" id="display_order" name="display_order" value="<?php echo $editWebsite ? intval($editWebsite['display_order']) : 0; ?>">
                                    <small class="form-text text-muted">数字越小排序越靠前，默认为0</small>
                                </div>

                                <div class="form-group">
                                    <button type="submit" class="btn btn-primary"><?php echo $_GET['action'] == 'add' ? '添加网站' : '保存修改'; ?></button>
                                    <a href="websites.php" class="btn btn-secondary">取消</a>
                                </div>
                            </form>
                        </div>
                    </div>
                <?php else: ?>
                    <!-- 网站列表 -->
                    <div class="card">
                        <div class="card-header">
                            <h6 class="m-0 font-weight-bold">网站列表</h6>
                            <a href="websites.php?action=add" class="btn btn-primary btn-sm">
                                <i class="bi bi-plus-circle"></i> 添加新网站
                            </a>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered" width="100%" cellspacing="0">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>图标</th>
                                            <th>名称</th>
                                            <th>URL</th>
                                            <th>分类</th>
                                            <th>显示顺序</th>
                                            <th>操作</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (count($websites) > 0): ?>
                                            <?php foreach ($websites as $website): ?>
                                                <tr>
                                                    <td><?php echo $website['id']; ?></td>
                                                    <td>
                                                        <img src="<?php echo htmlspecialchars($website['img_url']); ?>" alt="<?php echo htmlspecialchars($website['name']); ?>" class="website-img">
                                                    </td>
                                                    <td><?php echo htmlspecialchars($website['name']); ?></td>
                                                    <td>
                                                        <a href="<?php echo htmlspecialchars($website['url']); ?>" target="_blank">
                                                            <?php echo htmlspecialchars($website['url']); ?>
                                                        </a>
                                                    </td>
                                                    <td><?php echo htmlspecialchars($website['category_name']); ?></td>
                                                    <td><?php echo $website['display_order']; ?></td>
                                                    <td class="action-buttons">
                                                        <a href="websites.php?action=edit&id=<?php echo $website['id']; ?>" class="btn btn-warning btn-sm">
                                                            <i class="bi bi-pencil"></i> 编辑
                                                        </a>
                                                        <a href="websites.php?action=delete&id=<?php echo $website['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('确定要删除此网站吗？')">
                                                            <i class="bi bi-trash"></i> 删除
                                                        </a>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <tr>
                                                <td colspan="7" class="text-center">暂无网站数据</td>
                                            </tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>