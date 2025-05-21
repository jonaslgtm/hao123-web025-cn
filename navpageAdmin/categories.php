<?php
require_once '../include/config.php';

// 检查用户是否已登录
requireLogin();

$conn = connectDB();
$message = '';
$error = '';

// 排序功能已移除

// 处理删除请求
if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['id'])) {
    $id = intval($_GET['id']);
    
    // 检查是否是'all'分类（不允许删除）
    $stmt = $conn->prepare("SELECT identifier FROM categories WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $category = $result->fetch_assoc();
    $stmt->close();
    
    if ($category && $category['identifier'] == 'all') {
        $error = "不能删除'全部'分类，它是系统必需的";
    } else {
        // 检查分类是否被使用
        $stmt = $conn->prepare("SELECT COUNT(*) as count FROM websites WHERE category_id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $websiteCount = $result->fetch_assoc()['count'];
        $stmt->close();
        
        if ($websiteCount > 0) {
            $error = "此分类下有{$websiteCount}个网站，请先移除或重新分类这些网站";
        } else {
            $stmt = $conn->prepare("DELETE FROM categories WHERE id = ?");
            $stmt->bind_param("i", $id);
            
            if ($stmt->execute()) {
                $message = "分类已成功删除";
            } else {
                $error = "删除失败: " . $conn->error;
            }
            
            $stmt->close();
        }
    }
}

// 处理添加/编辑请求
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'] ?? '';
    $identifier = $_POST['identifier'] ?? '';
    $sort_order = isset($_POST['sort_order']) ? intval($_POST['sort_order']) : 0;
    
    // 验证输入
    if (empty($name) || empty($identifier)) {
        $error = "请填写所有必填字段";
    } else {
        // 检查标识符是否已存在
        $stmt = $conn->prepare("SELECT id FROM categories WHERE identifier = ? AND id != ?");
        $id = isset($_POST['id']) ? intval($_POST['id']) : 0;
        $stmt->bind_param("si", $identifier, $id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $error = "标识符'{$identifier}'已存在，请使用其他标识符";
        } else {
            // 添加新分类
                if (!isset($_POST['id'])) {
                    $stmt = $conn->prepare("INSERT INTO categories (name, identifier, sort_order) VALUES (?, ?, ?)");
                    $stmt->bind_param("ssi", $name, $identifier, $sort_order);
                    
                    if ($stmt->execute()) {
                        $message = "分类已成功添加";
                        // 重定向到分类列表
                        header("Location: categories.php?success=added");
                        exit;
                    } else {
                        $error = "添加失败: " . $conn->error;
                    }
            } 
            // 更新现有分类
            else {
                $id = intval($_POST['id']);
                
                // 检查是否是'all'分类（不允许修改标识符）
                $stmt = $conn->prepare("SELECT identifier FROM categories WHERE id = ?");
                $stmt->bind_param("i", $id);
                $stmt->execute();
                $result = $stmt->get_result();
                $category = $result->fetch_assoc();
                $stmt->close();
                
                if ($category && $category['identifier'] == 'all' && $identifier != 'all') {
                    $error = "不能修改'全部'分类的标识符，它是系统必需的";
                } else {
                    $stmt = $conn->prepare("UPDATE categories SET name = ?, identifier = ?, sort_order = ? WHERE id = ?");
                    $stmt->bind_param("ssii", $name, $identifier, $sort_order, $id);
                    
                    if ($stmt->execute()) {
                        $message = "分类已成功更新";
                        // 重定向到分类列表
                        header("Location: categories.php?success=updated");
                        exit;
                    } else {
                        $error = "更新失败: " . $conn->error;
                    }
                }
            }
            
            $stmt->close();
        }
    }
}

// 获取要编辑的分类
$editCategory = null;
if (isset($_GET['action']) && $_GET['action'] == 'edit' && isset($_GET['id'])) {
    $id = intval($_GET['id']);
    
    $stmt = $conn->prepare("SELECT * FROM categories WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 1) {
        $editCategory = $result->fetch_assoc();
    }
    
    $stmt->close();
}

// 获取所有分类
$categories = [];
$categoryResult = $conn->query("SELECT c.*, (SELECT COUNT(*) FROM websites WHERE category_id = c.id) as website_count FROM categories c ORDER BY c.sort_order, c.name");
while ($row = $categoryResult->fetch_assoc()) {
    $categories[] = $row;
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>分类管理 - 网站导航管理系统</title>
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
                <div class="page-header">
                    <h1><?php echo isset($_GET['action']) && ($_GET['action'] == 'add' || $_GET['action'] == 'edit') ? ($_GET['action'] == 'add' ? '添加新分类' : '编辑分类') : '分类管理'; ?></h1>
                    <p class="text-muted">管理网站导航的分类信息</p>
                </div>

                <?php if (!empty($message)): ?>
                    <div class="alert alert-success"><?php echo $message; ?></div>
                <?php endif; ?>

                <?php if (!empty($error)): ?>
                    <div class="alert alert-danger"><?php echo $error; ?></div>
                <?php endif; ?>

                <?php if (isset($_GET['success'])): ?>
                    <div class="alert alert-success">
                        <?php echo $_GET['success'] == 'added' ? '分类已成功添加' : '分类已成功更新'; ?>
                    </div>
                <?php endif; ?>

                <?php if (isset($_GET['action']) && ($_GET['action'] == 'add' || $_GET['action'] == 'edit')): ?>
                    <!-- 添加/编辑表单 -->
                    <div class="card">
                        <div class="card-header">
                            <h6 class="m-0 font-weight-bold"><?php echo $_GET['action'] == 'add' ? '添加新分类' : '编辑分类'; ?></h6>
                        </div>
                        <div class="card-body">
                            <form method="post" action="">
                                <?php if ($_GET['action'] == 'edit' && $editCategory): ?>
                                    <input type="hidden" name="id" value="<?php echo $editCategory['id']; ?>">
                                <?php endif; ?>

                                <div class="form-group">
                                    <label for="name" class="form-label">分类名称 <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="name" name="name" required value="<?php echo $editCategory ? htmlspecialchars($editCategory['name']) : ''; ?>">
                                </div>

                                <div class="form-group">
                                    <label for="identifier" class="form-label">分类标识符 <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="identifier" name="identifier" required value="<?php echo $editCategory ? htmlspecialchars($editCategory['identifier']) : ''; ?>" <?php echo $editCategory && $editCategory['identifier'] == 'all' ? 'readonly' : ''; ?>>
                                    <small class="form-text text-muted">用于系统内部识别的唯一标识符，只能包含字母、数字和下划线</small>
                                    <?php if ($editCategory && $editCategory['identifier'] == 'all'): ?>
                                        <small class="form-text text-danger">"all"是系统保留标识符，不能修改</small>
                                    <?php endif; ?>
                                </div>
                                
                                <div class="form-group">
                                    <label for="sort_order" class="form-label">排序顺序</label>
                                    <input type="number" class="form-control" id="sort_order" name="sort_order" value="<?php echo $editCategory ? intval($editCategory['sort_order']) : '0'; ?>">
                                    <small class="form-text text-muted">数字越小排序越靠前，默认为0</small>
                                </div>

                                <div class="form-group">
                                    <button type="submit" class="btn btn-primary"><?php echo $_GET['action'] == 'add' ? '添加分类' : '保存修改'; ?></button>
                                    <a href="categories.php" class="btn btn-secondary">取消</a>
                                </div>
                            </form>
                        </div>
                    </div>
                <?php else: ?>
                    <!-- 分类列表 -->
                    <div class="card">
                        <div class="card-header">
                            <h6 class="m-0 font-weight-bold">分类列表</h6>
                            <a href="categories.php?action=add" class="btn btn-primary btn-sm">
                                <i class="bi bi-plus-circle"></i> 添加新分类
                            </a>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered" width="100%" cellspacing="0">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>名称</th>
                                            <th>标识符</th>
                                            <th>排序顺序</th>
                                            <th>网站数量</th>
                                            <th>操作</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (count($categories) > 0): ?>
                                            <?php foreach ($categories as $category): ?>
                                                <tr class="category-item <?php echo $category['identifier'] == 'all' ? 'category-system' : ''; ?>">
                                                    <td><?php echo $category['id']; ?></td>
                                                    <td><?php echo htmlspecialchars($category['name']); ?></td>
                                                    <td>
                                                        <?php echo htmlspecialchars($category['identifier']); ?>
                                                        <?php if ($category['identifier'] == 'all'): ?>
                                                            <span class="badge bg-info">系统</span>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td><?php echo $category['sort_order']; ?></td>
                                                    <td>
                                                        <span class="category-count"><?php echo $category['website_count']; ?></span>
                                                        <?php if ($category['website_count'] > 0): ?>
                                                            <a href="websites.php?category=<?php echo $category['id']; ?>" class="btn btn-sm btn-outline-primary">查看</a>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td class="action-buttons">
                                                        
                                                            <a href="categories.php?action=edit&id=<?php echo $category['id']; ?>" class="btn btn-warning btn-sm">
                                                                <i class="bi bi-pencil"></i> 编辑
                                                            </a>
                                                            <?php if ($category['identifier'] != 'all'): ?>
                                                                <a href="categories.php?action=delete&id=<?php echo $category['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('确定要删除此分类吗？<?php echo $category['website_count'] > 0 ? '\n注意：此分类下有'.$category['website_count'].'个网站，需要先移除或重新分类这些网站。' : ''; ?>')">
                                                                    <i class="bi bi-trash"></i> 删除
                                                                </a>
                                                            <?php else: ?>
                                                                <button class="btn btn-danger btn-sm" disabled title="系统分类不能删除">
                                                                    <i class="bi bi-trash"></i> 删除
                                                                </button>
                                                            <?php endif; ?>
                                                       
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <tr>
                                                <td colspan="5" class="text-center">暂无分类数据</td>
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
    <style>
        .category-item {
            transition: background-color 0.3s;
        }
        .category-item:hover {
            background-color: #f8f9fa;
        }
        .action-buttons .btn-group {
            display: flex;
        }
    </style>
</body>
</html>