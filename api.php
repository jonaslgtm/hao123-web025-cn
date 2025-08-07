<?php
require_once './include/config.php';

// 设置响应头为JSON
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *'); // 允许跨域请求
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// 如果是OPTIONS请求（预检请求），直接返回200
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// 只允许GET请求
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405); // Method Not Allowed
    echo json_encode(['error' => '只允许GET请求']);
    exit;
}

$conn = connectDB();

// 获取请求的操作类型
$action = isset($_GET['action']) ? $_GET['action'] : '';

// 根据操作类型执行相应的操作
switch ($action) {
    case 'get_categories':
        // 获取所有分类
        $categories = [];
        $result = $conn->query("SELECT id, name, identifier FROM categories ORDER BY name");
        
        while ($row = $result->fetch_assoc()) {
            $categories[] = [
                'id' => $row['id'],
                'name' => $row['name'],
                'identifier' => $row['identifier']
            ];
        }
        
        echo json_encode(['code' => 1, 'data' => $categories]);
        break;
        
    case 'get_websites':
        // 获取网站列表，可以按分类筛选
        $category = isset($_GET['category']) ? $_GET['category'] : 'all';
        
        $websites = [];
        
        if ($category === 'all') {
            $result = $conn->query("SELECT * FROM websites ORDER BY display_order, name");
        } else {
            $stmt = $conn->prepare("SELECT w.* FROM websites w JOIN categories c ON w.category_id = c.id WHERE c.identifier = ? ORDER BY w.display_order, w.name");
            $stmt->bind_param("s", $category);
            $stmt->execute();
            $result = $stmt->get_result();
        }
        
        while ($row = $result->fetch_assoc()) {
            // 获取分类信息
            $categoryStmt = $conn->prepare("SELECT identifier FROM categories WHERE id = ?");
            $categoryStmt->bind_param("i", $row['category_id']);
            $categoryStmt->execute();
            $categoryResult = $categoryStmt->get_result();
            $categoryData = $categoryResult->fetch_assoc();
            $categoryStmt->close();
            
            $websites[] = [
                'id' => $row['id'],
                'name' => $row['name'],
                'url' => $row['url'],
                'category' => $categoryData['identifier'],
                'img' => $row['img_url'],
                'display_order' => $row['display_order']
            ];
        }
        
        echo json_encode(['code' => 1, 'data' => $websites]);
        break;
        
    case 'get_all_data':
        // 获取所有数据（分类和网站）
        $data = [];
        
        // 获取分类
        $categories = [];
        $result = $conn->query("SELECT id, name, identifier FROM categories ORDER BY name");
        
        while ($row = $result->fetch_assoc()) {
            $categories[] = [
                'id' => $row['id'],
                'name' => $row['name'],
                'identifier' => $row['identifier']
            ];
        }
        
        // 获取网站
        $websites = [];
        $result = $conn->query("SELECT w.*, c.identifier as category FROM websites w JOIN categories c ON w.category_id = c.id ORDER BY w.display_order, w.name");
        
        while ($row = $result->fetch_assoc()) {
            $websites[] = [
                'name' => $row['name'],
                'url' => $row['url'],
                'category' => $row['category'],
                'img' => $row['img_url']
            ];
        }
        
        $data['categories'] = $categories;
        $data['websites'] = $websites;
        
        echo json_encode(['code' => 1, 'data' => $data]);
        break;
        
    case 'get_article_categories':
        // 获取所有文章分类
        $categories = [];
        
        // 检查文章分类表是否存在
        $tableExists = $conn->query("SHOW TABLES LIKE 'article_categories'")->num_rows > 0;
        if ($tableExists) {
            $result = $conn->query("SELECT id, name, identifier, description FROM article_categories ORDER BY display_order, name");
            
            while ($row = $result->fetch_assoc()) {
                $categories[] = [
                    'id' => $row['id'],
                    'name' => $row['name'],
                    'identifier' => $row['identifier'],
                    'description' => $row['description']
                ];
            }
            
            echo json_encode(['code' => 1, 'data' => $categories]);
        } else {
            echo json_encode(['code' => 0, 'message' => '文章分类功能未启用']);
        }
        break;
        
    case 'get_articles':
        // 获取文章列表，可以按分类筛选
        $category_id = isset($_GET['category_id']) ? intval($_GET['category_id']) : 0;
        $tag = isset($_GET['tag']) ? $_GET['tag'] : '';
        $limit = isset($_GET['limit']) ? intval($_GET['limit']) : 10;
        $page = isset($_GET['page']) ? intval($_GET['page']) : 1;
        $featured = isset($_GET['featured']) ? intval($_GET['featured']) : -1; // -1表示不筛选
        
        // 检查文章表是否存在
        $tableExists = $conn->query("SHOW TABLES LIKE 'articles'")->num_rows > 0;
        if (!$tableExists) {
            echo json_encode(['code' => 0, 'message' => '文章功能未启用']);
            break;
        }
        
        $offset = ($page - 1) * $limit;
        $articles = [];
        $whereConditions = [];
        $params = [];
        $types = '';
        
        // 构建查询条件
        if ($category_id > 0) {
            $whereConditions[] = "a.category_id = ?";
            $params[] = $category_id;
            $types .= 'i';
        }
        
        if ($featured >= 0) {
            $whereConditions[] = "a.featured = ?";
            $params[] = $featured;
            $types .= 'i';
        }
        
        // 只获取已发布的文章
        $whereConditions[] = "a.status = 'published'";
        
        $whereClause = count($whereConditions) > 0 ? "WHERE " . implode(" AND ", $whereConditions) : "";
        
        // 计算总文章数
        $countQuery = "SELECT COUNT(*) as total FROM articles a $whereClause";
        
        if (!empty($types)) {
            $countStmt = $conn->prepare($countQuery);
            $countStmt->bind_param($types, ...$params);
            $countStmt->execute();
            $totalResult = $countStmt->get_result();
            $countStmt->close();
        } else {
            $totalResult = $conn->query($countQuery);
        }
        
        $totalArticles = $totalResult->fetch_assoc()['total'];
        $totalPages = ceil($totalArticles / $limit);
        
        // 获取文章列表
        $query = "SELECT a.*, c.name as category_name, c.identifier as category_identifier 
                 FROM articles a 
                 JOIN article_categories c ON a.category_id = c.id 
                 $whereClause 
                 ORDER BY a.created_at DESC 
                 LIMIT ?, ?";
        
        $params[] = $offset;
        $params[] = $limit;
        $types .= 'ii';
        
        $stmt = $conn->prepare($query);
        $stmt->bind_param($types, ...$params);
        $stmt->execute();
        $result = $stmt->get_result();
        
        while ($row = $result->fetch_assoc()) {
            // 获取文章标签
            $tags = [];
            $tagQuery = "SELECT t.name FROM article_tags t 
                        JOIN article_tag_relations r ON t.id = r.tag_id 
                        WHERE r.article_id = ?";
            $tagStmt = $conn->prepare($tagQuery);
            $tagStmt->bind_param("i", $row['id']);
            $tagStmt->execute();
            $tagResult = $tagStmt->get_result();
            
            while ($tagRow = $tagResult->fetch_assoc()) {
                $tags[] = $tagRow['name'];
            }
            
            $tagStmt->close();
            
            // 构建文章数据
            $article = [
                'id' => $row['id'],
                'title' => $row['title'],
                'content' => $row['content'],
                'thumbnail' => $row['thumbnail'],
                'author' => $row['author'],
                'category' => [
                    'id' => $row['category_id'],
                    'name' => $row['category_name'],
                    'identifier' => $row['category_identifier']
                ],
                'tags' => $tags,
                'featured' => (bool)$row['featured'],
                'created_at' => $row['created_at'],
                'updated_at' => $row['updated_at']
            ];
            
            $articles[] = $article;
        }
        
        $stmt->close();
        
        echo json_encode([
            'code' => 1, 
            'data' => [
                'articles' => $articles,
                'pagination' => [
                    'total' => $totalArticles,
                    'per_page' => $limit,
                    'current_page' => $page,
                    'last_page' => $totalPages
                ]
            ]
        ]);
        break;
        
    case 'get_article':
        // 获取单篇文章详情
        $id = isset($_GET['id']) ? intval($_GET['id']) : 0;
        
        if ($id <= 0) {
            echo json_encode(['code' => 0, 'message' => '无效的文章ID']);
            break;
        }
        
        // 检查文章表是否存在
        $tableExists = $conn->query("SHOW TABLES LIKE 'articles'")->num_rows > 0;
        if (!$tableExists) {
            echo json_encode(['code' => 0, 'message' => '文章功能未启用']);
            break;
        }
        
        $stmt = $conn->prepare("SELECT a.*, c.name as category_name, c.identifier as category_identifier 
                              FROM articles a 
                              JOIN article_categories c ON a.category_id = c.id 
                              WHERE a.id = ? AND a.status = 'published'");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 0) {
            echo json_encode(['code' => 0, 'message' => '文章不存在或未发布']);
            break;
        }
        
        $row = $result->fetch_assoc();
        $stmt->close();
        
        // 获取文章标签
        $tags = [];
        $tagQuery = "SELECT t.name FROM article_tags t 
                    JOIN article_tag_relations r ON t.id = r.tag_id 
                    WHERE r.article_id = ?";
        $tagStmt = $conn->prepare($tagQuery);
        $tagStmt->bind_param("i", $id);
        $tagStmt->execute();
        $tagResult = $tagStmt->get_result();
        
        while ($tagRow = $tagResult->fetch_assoc()) {
            $tags[] = $tagRow['name'];
        }
        
        $tagStmt->close();
        
        // 构建文章数据
        $article = [
            'id' => $row['id'],
            'title' => $row['title'],
            'content' => $row['content'],
            'thumbnail' => $row['thumbnail'],
            'author' => $row['author'],
            'category' => [
                'id' => $row['category_id'],
                'name' => $row['category_name'],
                'identifier' => $row['category_identifier']
            ],
            'tags' => $tags,
            'featured' => (bool)$row['featured'],
            'created_at' => $row['created_at'],
            'updated_at' => $row['updated_at']
        ];
        
        echo json_encode(['code' => 1, 'data' => $article]);
        break;
        
    default:
        // 未知操作
        http_response_code(400); // Bad Request
        echo json_encode(['error' => '未知操作类型']);
        break;
}

$conn->close();