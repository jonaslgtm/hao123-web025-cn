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
        
    default:
        // 未知操作
        http_response_code(400); // Bad Request
        echo json_encode(['error' => '未知操作类型']);
        break;
}

$conn->close();