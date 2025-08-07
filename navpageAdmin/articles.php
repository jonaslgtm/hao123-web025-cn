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
    
    // 删除文章标签关联
    $stmt = $conn->prepare("DELETE FROM article_tag_relations WHERE article_id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();
    
    // 删除文章
    $stmt = $conn->prepare("DELETE FROM articles WHERE id = ?");
    $stmt->bind_param("i", $id);
    
    if ($stmt->execute()) {
        $message = "文章已成功删除";
    } else {
        $error = "删除失败: " . $conn->error;
    }
    
    $stmt->close();
}

// 处理添加/编辑请求
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = $_POST['title'] ?? '';
    $content = $_POST['content'] ?? '';
    $category_id = intval($_POST['category_id'] ?? 0);
    $thumbnail = $_POST['thumbnail'] ?? '';
    $author = $_POST['author'] ?? '';
    $status = $_POST['status'] ?? 'published';
    $featured = isset($_POST['featured']) ? 1 : 0;
    $tags = isset($_POST['tags']) ? explode(',', $_POST['tags']) : [];
    
    // 验证输入
    if (empty($title) || empty($content) || $category_id <= 0) {
        $error = "请填写所有必填字段";
    } else {
        // 开始事务
        $conn->begin_transaction();
        
        try {
            // 添加新文章
            if (!isset($_POST['id'])) {
                $stmt = $conn->prepare("INSERT INTO articles (title, content, category_id, thumbnail, author, status, featured) VALUES (?, ?, ?, ?, ?, ?, ?)");
                $stmt->bind_param("ssisssi", $title, $content, $category_id, $thumbnail, $author, $status, $featured);
                
                if ($stmt->execute()) {
                    $article_id = $conn->insert_id;
                    
                    // 处理标签
                    if (!empty($tags)) {
                        foreach ($tags as $tag) {
                            $tag = trim($tag);
                            if (!empty($tag)) {
                                // 检查标签是否存在
                                $tagStmt = $conn->prepare("SELECT id FROM article_tags WHERE name = ?");
                                $tagStmt->bind_param("s", $tag);
                                $tagStmt->execute();
                                $tagResult = $tagStmt->get_result();
                                
                                if ($tagResult->num_rows > 0) {
                                    $tag_id = $tagResult->fetch_assoc()['id'];
                                } else {
                                    // 创建新标签
                                    $newTagStmt = $conn->prepare("INSERT INTO article_tags (name) VALUES (?)");
                                    $newTagStmt->bind_param("s", $tag);
                                    $newTagStmt->execute();
                                    $tag_id = $conn->insert_id;
                                    $newTagStmt->close();
                                }
                                
                                // 添加文章-标签关联
                                $relStmt = $conn->prepare("INSERT INTO article_tag_relations (article_id, tag_id) VALUES (?, ?)");
                                $relStmt->bind_param("ii", $article_id, $tag_id);
                                $relStmt->execute();
                                $relStmt->close();
                                
                                $tagStmt->close();
                            }
                        }
                    }
                    
                    $message = "文章已成功添加";
                    $conn->commit();
                    // 重定向到文章列表
                    header("Location: articles.php?success=added");
                    exit;
                } else {
                    $error = "添加失败: " . $conn->error;
                    $conn->rollback();
                }
            } 
            // 更新现有文章
            else {
                $id = intval($_POST['id']);
                $stmt = $conn->prepare("UPDATE articles SET title = ?, content = ?, category_id = ?, thumbnail = ?, author = ?, status = ?, featured = ? WHERE id = ?");
                $stmt->bind_param("ssisssii", $title, $content, $category_id, $thumbnail, $author, $status, $featured, $id);
                
                if ($stmt->execute()) {
                    // 删除现有标签关联
                    $delStmt = $conn->prepare("DELETE FROM article_tag_relations WHERE article_id = ?");
                    $delStmt->bind_param("i", $id);
                    $delStmt->execute();
                    $delStmt->close();
                    
                    // 处理标签
                    if (!empty($tags)) {
                        foreach ($tags as $tag) {
                            $tag = trim($tag);
                            if (!empty($tag)) {
                                // 检查标签是否存在
                                $tagStmt = $conn->prepare("SELECT id FROM article_tags WHERE name = ?");
                                $tagStmt->bind_param("s", $tag);
                                $tagStmt->execute();
                                $tagResult = $tagStmt->get_result();
                                
                                if ($tagResult->num_rows > 0) {
                                    $tag_id = $tagResult->fetch_assoc()['id'];
                                } else {
                                    // 创建新标签
                                    $newTagStmt = $conn->prepare("INSERT INTO article_tags (name) VALUES (?)");
                                    $newTagStmt->bind_param("s", $tag);
                                    $newTagStmt->execute();
                                    $tag_id = $conn->insert_id;
                                    $newTagStmt->close();
                                }
                                
                                // 添加文章-标签关联
                                $relStmt = $conn->prepare("INSERT INTO article_tag_relations (article_id, tag_id) VALUES (?, ?)");
                                $relStmt->bind_param("ii", $id, $tag_id);
                                $relStmt->execute();
                                $relStmt->close();
                                
                                $tagStmt->close();
                            }
                        }
                    }
                    
                    $message = "文章已成功更新";
                    $conn->commit();
                    // 重定向到文章列表
                    header("Location: articles.php?success=updated");
                    exit;
                } else {
                    $error = "更新失败: " . $conn->error;
                    $conn->rollback();
                }
            }
            
            $stmt->close();
        } catch (Exception $e) {
            $conn->rollback();
            $error = "操作失败: " . $e->getMessage();
        }
    }
}

// 获取所有文章分类
$categories = [];
$categoryResult = $conn->query("SELECT * FROM article_categories ORDER BY display_order, name");
while ($row = $categoryResult->fetch_assoc()) {
    $categories[] = $row;
}

// 获取要编辑的文章
$editArticle = null;
$articleTags = [];
if (isset($_GET['action']) && $_GET['action'] == 'edit' && isset($_GET['id'])) {
    $id = intval($_GET['id']);
    
    $stmt = $conn->prepare("SELECT * FROM articles WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 1) {
        $editArticle = $result->fetch_assoc();
        
        // 获取文章标签
        $tagStmt = $conn->prepare("SELECT t.name FROM article_tags t JOIN article_tag_relations r ON t.id = r.tag_id WHERE r.article_id = ?");
        $tagStmt->bind_param("i", $id);
        $tagStmt->execute();
        $tagResult = $tagStmt->get_result();
        
        while ($tagRow = $tagResult->fetch_assoc()) {
            $articleTags[] = $tagRow['name'];
        }
        
        $tagStmt->close();
    }
    
    $stmt->close();
}

// 获取所有文章
$articles = [];
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$limit = 10;
$offset = ($page - 1) * $limit;

// 计算总文章数
$totalResult = $conn->query("SELECT COUNT(*) as total FROM articles");
$totalArticles = $totalResult->fetch_assoc()['total'];
$totalPages = ceil($totalArticles / $limit);

// 获取分页文章列表
$articleResult = $conn->query("SELECT a.*, c.name as category_name FROM articles a JOIN article_categories c ON a.category_id = c.id ORDER BY a.created_at DESC LIMIT $offset, $limit");
while ($row = $articleResult->fetch_assoc()) {
    $articles[] = $row;
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($editArticle) ? '编辑文章' : '文章管理'; ?> - 网站导航管理系统</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="assets/css/style.css">
    <!-- TinyMCE 富文本编辑器 -->
    <script src="https://cdn.tiny.cloud/1/ks4r9ymvmha3pgk4zlcbjd3sk7prlnxxb4qbkcqm0goa6kb6/tinymce/6/tinymce.min.js" referrerpolicy="origin"></script>
    <!-- 标签输入插件 -->
    <link href="https://cdn.jsdelivr.net/npm/@yaireo/tagify/dist/tagify.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/@yaireo/tagify/dist/tagify.min.js"></script>
    <style>
        .tox-tinymce {
            border-radius: 0.25rem;
        }
        .article-thumbnail {
            max-width: 100px;
            max-height: 60px;
            object-fit: cover;
        }
        .article-title {
            max-width: 300px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .tagify {
            width: 100%;
            max-width: 100%;
        }
    </style>
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
                    <h1><?php echo isset($editArticle) ? '编辑文章' : '文章管理'; ?></h1>
                    <p class="text-muted">管理网站的文章内容，支持富文本编辑、图片上传和分类管理。</p>
                </div>
                
                <?php if (!empty($message)): ?>
                    <div class="alert alert-success"><?php echo $message; ?></div>
                <?php endif; ?>
                
                <?php if (!empty($error)): ?>
                    <div class="alert alert-danger"><?php echo $error; ?></div>
                <?php endif; ?>
                
                <?php if (isset($_GET['success']) && $_GET['success'] == 'added'): ?>
                    <div class="alert alert-success">文章已成功添加</div>
                <?php endif; ?>
                
                <?php if (isset($_GET['success']) && $_GET['success'] == 'updated'): ?>
                    <div class="alert alert-success">文章已成功更新</div>
                <?php endif; ?>
                
                <!-- 添加/编辑表单 -->
                <?php if (isset($_GET['action']) && ($_GET['action'] == 'add' || $_GET['action'] == 'edit')): ?>
                    <div class="card">
                        <div class="card-header">
                            <h6 class="m-0 font-weight-bold"><?php echo isset($editArticle) ? '编辑文章' : '添加新文章'; ?></h6>
                        </div>
                        <div class="card-body">
                            <form method="post" action="">
                                <?php if (isset($editArticle)): ?>
                                    <input type="hidden" name="id" value="<?php echo $editArticle['id']; ?>">
                                <?php endif; ?>
                                
                                <div class="row">
                                    <div class="col-md-8">
                                        <div class="mb-3">
                                            <label for="title" class="form-label">文章标题 <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control" id="title" name="title" value="<?php echo isset($editArticle) ? htmlspecialchars($editArticle['title']) : ''; ?>" required>
                                        </div>
                                        
                                        <div class="mb-3">
                                            <label for="content" class="form-label">文章内容 <span class="text-danger">*</span></label>
                                            <textarea class="form-control" id="content" name="content" rows="12"><?php echo isset($editArticle) ? $editArticle['content'] : ''; ?></textarea>
                                        </div>
                                    </div>
                                    
                                    <div class="col-md-4">
                                        <div class="mb-3">
                                            <label for="category_id" class="form-label">文章分类 <span class="text-danger">*</span></label>
                                            <select class="form-select" id="category_id" name="category_id" required>
                                                <option value="">选择分类</option>
                                                <?php foreach ($categories as $category): ?>
                                                    <option value="<?php echo $category['id']; ?>" <?php echo (isset($editArticle) && $editArticle['category_id'] == $category['id']) ? 'selected' : ''; ?>>
                                                        <?php echo htmlspecialchars($category['name']); ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                        
                                        <div class="mb-3">
                                            <label for="thumbnail" class="form-label">缩略图URL</label>
                                            <input type="text" class="form-control" id="thumbnail" name="thumbnail" value="<?php echo isset($editArticle) ? htmlspecialchars($editArticle['thumbnail']) : ''; ?>">
                                            <small class="form-text text-muted">输入图片的URL地址</small>
                                        </div>
                                        
                                        <div class="mb-3">
                                            <label for="author" class="form-label">作者</label>
                                            <input type="text" class="form-control" id="author" name="author" value="<?php echo isset($editArticle) ? htmlspecialchars($editArticle['author']) : $_SESSION['username']; ?>">
                                        </div>
                                        
                                        <div class="mb-3">
                                            <label for="tags" class="form-label">标签</label>
                                            <input type="text" class="form-control" id="tags" name="tags" value="<?php echo isset($articleTags) ? implode(',', $articleTags) : ''; ?>">
                                            <small class="form-text text-muted">输入标签，用逗号分隔</small>
                                        </div>
                                        
                                        <div class="mb-3">
                                            <label for="status" class="form-label">状态</label>
                                            <select class="form-select" id="status" name="status">
                                                <option value="published" <?php echo (isset($editArticle) && $editArticle['status'] == 'published') ? 'selected' : ''; ?>>已发布</option>
                                                <option value="draft" <?php echo (isset($editArticle) && $editArticle['status'] == 'draft') ? 'selected' : ''; ?>>草稿</option>
                                            </select>
                                        </div>
                                        
                                        <div class="mb-3 form-check">
                                            <input type="checkbox" class="form-check-input" id="featured" name="featured" <?php echo (isset($editArticle) && $editArticle['featured'] == 1) ? 'checked' : ''; ?>>
                                            <label class="form-check-label" for="featured">推荐文章</label>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="mb-3">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="bi bi-save"></i> <?php echo isset($editArticle) ? '更新文章' : '发布文章'; ?>
                                    </button>
                                    <a href="articles.php" class="btn btn-secondary">
                                        <i class="bi bi-x-circle"></i> 取消
                                    </a>
                                </div>
                            </form>
                        </div>
                    </div>
                <?php else: ?>
                    <!-- 文章列表 -->
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h6 class="m-0 font-weight-bold">文章列表</h6>
                            <a href="articles.php?action=add" class="btn btn-primary btn-sm">
                                <i class="bi bi-plus-circle"></i> 添加新文章
                            </a>
                        </div>
                        <div class="card-body">
                            <?php if (empty($articles)): ?>
                                <div class="alert alert-info">暂无文章，请添加</div>
                            <?php else: ?>
                                <div class="table-responsive">
                                    <table class="table table-bordered table-hover">
                                        <thead>
                                            <tr>
                                                <th>ID</th>
                                                <th>缩略图</th>
                                                <th>标题</th>
                                                <th>分类</th>
                                                <th>作者</th>
                                                <th>状态</th>
                                                <th>推荐</th>
                                                <th>发布时间</th>
                                                <th>操作</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($articles as $article): ?>
                                                <tr>
                                                    <td><?php echo $article['id']; ?></td>
                                                    <td>
                                                        <?php if (!empty($article['thumbnail'])): ?>
                                                            <img src="<?php echo htmlspecialchars($article['thumbnail']); ?>" alt="缩略图" class="article-thumbnail">
                                                        <?php else: ?>
                                                            <span class="text-muted">无缩略图</span>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td class="article-title"><?php echo htmlspecialchars($article['title']); ?></td>
                                                    <td><?php echo htmlspecialchars($article['category_name']); ?></td>
                                                    <td><?php echo htmlspecialchars($article['author']); ?></td>
                                                    <td>
                                                        <?php if ($article['status'] == 'published'): ?>
                                                            <span class="badge bg-success">已发布</span>
                                                        <?php else: ?>
                                                            <span class="badge bg-secondary">草稿</span>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td>
                                                        <?php if ($article['featured'] == 1): ?>
                                                            <span class="badge bg-primary">是</span>
                                                        <?php else: ?>
                                                            <span class="badge bg-light text-dark">否</span>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td><?php echo date('Y-m-d H:i', strtotime($article['created_at'])); ?></td>
                                                    <td>
                                                        <a href="articles.php?action=edit&id=<?php echo $article['id']; ?>" class="btn btn-sm btn-primary">
                                                            <i class="bi bi-pencil"></i> 编辑
                                                        </a>
                                                        <a href="articles.php?action=delete&id=<?php echo $article['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('确定要删除此文章吗？')">
                                                            <i class="bi bi-trash"></i> 删除
                                                        </a>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                                
                                <!-- 分页 -->
                                <?php if ($totalPages > 1): ?>
                                    <nav aria-label="Page navigation">
                                        <ul class="pagination justify-content-center">
                                            <?php if ($page > 1): ?>
                                                <li class="page-item">
                                                    <a class="page-link" href="articles.php?page=<?php echo $page - 1; ?>" aria-label="Previous">
                                                        <span aria-hidden="true">&laquo;</span>
                                                    </a>
                                                </li>
                                            <?php endif; ?>
                                            
                                            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                                                <li class="page-item <?php echo $i == $page ? 'active' : ''; ?>">
                                                    <a class="page-link" href="articles.php?page=<?php echo $i; ?>"><?php echo $i; ?></a>
                                                </li>
                                            <?php endfor; ?>
                                            
                                            <?php if ($page < $totalPages): ?>
                                                <li class="page-item">
                                                    <a class="page-link" href="articles.php?page=<?php echo $page + 1; ?>" aria-label="Next">
                                                        <span aria-hidden="true">&raquo;</span>
                                                    </a>
                                                </li>
                                            <?php endif; ?>
                                        </ul>
                                    </nav>
                                <?php endif; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endif; ?>
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // 初始化TinyMCE编辑器
        tinymce.init({
            selector: '#content',
            apiKey: 'ks4r9ymvmha3pgk4zlcbjd3sk7prlnxxb4qbkcqm0goa6kb6',
            plugins: 'anchor autolink charmap codesample emoticons image link lists media searchreplace table visualblocks wordcount checklist mediaembed casechange export formatpainter pageembed linkchecker a11ychecker tinymcespellchecker permanentpen powerpaste advtable advcode editimage advtemplate ai mentions tinycomments tableofcontents footnotes mergetags autocorrect typography inlinecss',
            toolbar: 'undo redo | blocks fontfamily fontsize | bold italic underline strikethrough | link image media table mergetags | addcomment showcomments | spellcheckdialog a11ycheck typography | align lineheight | checklist numlist bullist indent outdent | emoticons charmap | removeformat',
            tinycomments_mode: 'embedded',
            tinycomments_author: '<?php echo $_SESSION['username']; ?>',
            mergetags_list: [
                { value: 'First.Name', title: 'First Name' },
                { value: 'Email', title: 'Email' },
            ],
            ai_request: (request, respondWith) => respondWith.string(() => Promise.reject("See docs to implement AI Assistant")),
            height: 500,
            language: 'zh_CN',
            images_upload_url: 'upload.php', // 图片上传处理脚本
            automatic_uploads: true,
            file_picker_types: 'image',
            file_picker_callback: function(cb, value, meta) {
                var input = document.createElement('input');
                input.setAttribute('type', 'file');
                input.setAttribute('accept', 'image/*');

                input.onchange = function() {
                    var file = this.files[0];
                    var reader = new FileReader();
                    
                    reader.onload = function() {
                        var id = 'blobid' + (new Date()).getTime();
                        var blobCache = tinymce.activeEditor.editorUpload.blobCache;
                        var base64 = reader.result.split(',')[1];
                        var blobInfo = blobCache.create(id, file, base64);
                        blobCache.add(blobInfo);
                        
                        cb(blobInfo.blobUri(), { title: file.name });
                    };
                    
                    reader.readAsDataURL(file);
                };
                
                input.click();
            }
        });
        
        // 初始化标签输入
        var input = document.querySelector('input[name=tags]');
        new Tagify(input);
    </script>
</body>
</html>