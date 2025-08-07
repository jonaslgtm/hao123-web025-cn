<?php
// 包含数据库配置
require_once 'include/config.php';

// 连接数据库
$conn = connectDB();

// 检查article_categories表是否存在parent_id列
$result = $conn->query("SHOW COLUMNS FROM article_categories LIKE 'parent_id'");

if ($result->num_rows == 0) {
    // 如果parent_id列不存在，添加它
    $sql = "ALTER TABLE article_categories ADD COLUMN parent_id INT NOT NULL DEFAULT 0 COMMENT '父级分类ID' AFTER description";
    
    if ($conn->query($sql) === TRUE) {
        echo "<p>成功添加parent_id列到article_categories表</p>";
    } else {
        echo "<p>添加列失败: " . $conn->error . "</p>";
    }
} else {
    echo "<p>parent_id列已存在，无需更新</p>";
}

// 关闭数据库连接
$conn->close();

echo "<p>数据库更新完成。<a href='navpageAdmin/article-categories.php'>返回文章分类管理</a></p>";
?>