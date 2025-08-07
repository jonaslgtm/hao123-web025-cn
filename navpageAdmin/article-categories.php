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
    
    // 检查分类是否被使用
    $stmt = $conn->prepare("SELECT COUNT(*) as count FROM articles WHERE category_id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $articleCount = $result->fetch_assoc()['count'];
    $stmt->close();
    
    if ($articleCount > 0) {
        $error = "此分类下有{$articleCount}篇文章，请先移除或重新分类这些文章";
    } else {
        $stmt = $conn->prepare("DELETE FROM article_categories WHERE id = ?");
        $stmt->bind_param("i", $id);
        
        if ($stmt->execute()) {
            $message = "文章分类已成功删除";
        } else {
            $error = "删除失败: " . $conn->error;
        }
        
        $stmt->close();
    }
}

// 处理添加/编辑请求
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'] ?? '';
    $identifier = $_POST['identifier'] ?? '';
    $parent_id = isset($_POST['parent_id']) ? intval($_POST['parent_id']) : 0;
    $display_order = isset($_POST['display_order']) ? intval($_POST['display_order']) : 0;
    
    // 验证输入
    if (empty($name) || empty($identifier)) {
        $error = "请填写所有必填字段";
    } else {
        // 检查标识符是否已存在
        $stmt = $conn->prepare("SELECT id FROM article_categories WHERE identifier = ? AND id != ?");
        $id = isset($_POST['id']) ? intval($_POST['id']) : 0;
        $stmt->bind_param("si", $identifier, $id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $error = "标识符'{$identifier}'已存在，请使用其他标识符";
        } else {
            // 添加新分类
            if (!isset($_POST['id'])) {
                $stmt = $conn->prepare("INSERT INTO article_categories (name, identifier, parent_id, display_order) VALUES (?, ?, ?, ?)");
                $stmt->bind_param("ssii", $name, $identifier, $parent_id, $display_order);
                
                if ($stmt->execute()) {
                    $message = "文章分类已成功添加";
                    // 重定向到分类列表
                    header("Location: article-categories.php?success=added");
                    exit;
                } else {
                    $error = "添加失败: " . $conn->error;
                }
            } 
            // 更新现有分类
            else {
                $id = intval($_POST['id']);
                $stmt = $conn->prepare("UPDATE article_categories SET name = ?, identifier = ?, parent_id = ?, display_order = ? WHERE id = ?");
                $stmt->bind_param("ssiii", $name, $identifier, $parent_id, $display_order, $id);
                
                if ($stmt->execute()) {
                    $message = "文章分类已成功更新";
                    // 重定向到分类列表
                    header("Location: article-categories.php?success=updated");
                    exit;
                } else {
                    $error = "更新失败: " . $conn->error;
                }
            }
            
            $stmt->close();
        }
    }
}

// 获取所有分类
$categories = [];
$categoryResult = $conn->query("SELECT id, name, identifier, parent_id, display_order, created_at FROM article_categories ORDER BY display_order, name");
while ($row = $categoryResult->fetch_assoc()) {
    $categories[] = $row;
}

// 获取要编辑的分类
$editCategory = null;
if (isset($_GET['action']) && $_GET['action'] == 'edit' && isset($_GET['id'])) {
    $id = intval($_GET['id']);
    
    $stmt = $conn->prepare("SELECT * FROM article_categories WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 1) {
        $editCategory = $result->fetch_assoc();
    }
    
    $stmt->close();
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>文章分类管理 - 网站导航管理系统</title>
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
                <div class="page-header">
                    <h1><?php echo isset($editCategory) ? '编辑文章分类' : '文章分类管理'; ?></h1>
                    <p class="text-muted">管理网站的文章分类，用于组织和分类文章内容。</p>
                </div>
                
                <?php if (!empty($message)): ?>
                    <div class="alert alert-success"><?php echo $message; ?></div>
                <?php endif; ?>
                
                <?php if (!empty($error)): ?>
                    <div class="alert alert-danger"><?php echo $error; ?></div>
                <?php endif; ?>
                
                <?php if (isset($_GET['success']) && $_GET['success'] == 'added'): ?>
                    <div class="alert alert-success">文章分类已成功添加</div>
                <?php endif; ?>
                
                <?php if (isset($_GET['success']) && $_GET['success'] == 'updated'): ?>
                    <div class="alert alert-success">文章分类已成功更新</div>
                <?php endif; ?>
                
                <!-- 添加/编辑表单 -->
                <?php if (isset($_GET['action']) && ($_GET['action'] == 'add' || $_GET['action'] == 'edit')): ?>
                    <div class="card">
                        <div class="card-header">
                            <h6 class="m-0 font-weight-bold"><?php echo isset($editCategory) ? '编辑文章分类' : '添加新文章分类'; ?></h6>
                        </div>
                        <div class="card-body">
                            <form method="post" action="">
                                <?php if (isset($editCategory)): ?>
                                    <input type="hidden" name="id" value="<?php echo $editCategory['id']; ?>">
                                <?php endif; ?>
                                
                                <div class="mb-3">
                                    <label for="name" class="form-label">分类名称 <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="name" name="name" value="<?php echo isset($editCategory) ? htmlspecialchars($editCategory['name']) : ''; ?>" required>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="identifier" class="form-label">分类标识符 <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="identifier" name="identifier" value="<?php echo isset($editCategory) ? htmlspecialchars($editCategory['identifier']) : ''; ?>" required>
                                    <small class="form-text text-muted">标识符只能包含字母、数字和连字符，用于URL和API中标识此分类</small>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="parent_id" class="form-label">父级分类</label>
                                    <select class="form-select" id="parent_id" name="parent_id">
                                        <option value="0">无 (顶级分类)</option>
                                        <?php foreach ($categories as $category): ?>
                                            <?php if (!isset($editCategory) || $category['id'] != $editCategory['id']): ?>
                                                <option value="<?php echo $category['id']; ?>" <?php echo (isset($editCategory) && $editCategory['parent_id'] == $category['id']) ? 'selected' : ''; ?>>
                                                    <?php echo htmlspecialchars($category['name']); ?>
                                                </option>
                                            <?php endif; ?>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="display_order" class="form-label">显示顺序</label>
                                    <input type="number" class="form-control" id="display_order" name="display_order" value="<?php echo isset($editCategory) ? intval($editCategory['display_order']) : 0; ?>">
                                    <small class="form-text text-muted">数字越小，排序越靠前</small>
                                </div>
                                
                                <div class="mb-3">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="bi bi-save"></i> <?php echo isset($editCategory) ? '更新分类' : '添加分类'; ?>
                                    </button>
                                    <a href="article-categories.php" class="btn btn-secondary">
                                        <i class="bi bi-x-circle"></i> 取消
                                    </a>
                                </div>
                            </form>
                        </div>
                    </div>
                <?php else: ?>
                    <!-- 分类列表 -->
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h6 class="m-0 font-weight-bold">文章分类列表</h6>
                            <a href="article-categories.php?action=add" class="btn btn-primary btn-sm">
                                <i class="bi bi-plus-circle"></i> 添加新分类
                            </a>
                        </div>
                        <div class="card-body">
                            <?php if (empty($categories)): ?>
                                <div class="alert alert-info">暂无文章分类，请添加</div>
                            <?php else: ?>
                                <div class="table-responsive">
                                    <table class="table table-bordered table-hover">
                                        <thead>
                                            <tr>
                                                <th>ID</th>
                                                <th>分类名称</th>
                                                <th>标识符</th>
                                                <th>父级分类</th>
                                                <th>显示顺序</th>
                                                <th>创建时间</th>
                                                <th>操作</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($categories as $category): ?>
                                                <tr>
                                                    <td><?php echo $category['id']; ?></td>
                                                    <td><?php echo htmlspecialchars($category['name']); ?></td>
                                                    <td><code><?php echo htmlspecialchars($category['identifier']); ?></code></td>
                                                    <td>
                                                        <?php 
                                                        if ($category['parent_id'] > 0) {
                                                            foreach ($categories as $parentCategory) {
                                                                if ($parentCategory['id'] == $category['parent_id']) {
                                                                    echo htmlspecialchars($parentCategory['name']);
                                                                    break;
                                                                }
                                                            }
                                                        } else {
                                                            echo '无 (顶级分类)';
                                                        }
                                                        ?>
                                                    </td>
                                                    <td><?php echo $category['display_order']; ?></td>
                                                    <td><?php echo $category['created_at']; ?></td>
                                                    <td>
                                                        <a href="article-categories.php?action=edit&id=<?php echo $category['id']; ?>" class="btn btn-sm btn-primary">
                                                            <i class="bi bi-pencil"></i> 编辑
                                                        </a>
                                                        <a href="article-categories.php?action=delete&id=<?php echo $category['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('确定要删除此分类吗？')">
                                                            <i class="bi bi-trash"></i> 删除
                                                        </a>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endif; ?>
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>