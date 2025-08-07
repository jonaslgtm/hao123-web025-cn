<?php
require_once '../include/config.php';

// 检查用户是否已登录
requireLogin();

// 设置上传目录
$upload_dir = '../uploads/articles/';
$year_month = date('Y/m');
$target_dir = $upload_dir . $year_month . '/';

// 如果目录不存在，则创建
if (!file_exists($target_dir)) {
    mkdir($target_dir, 0755, true);
}

// 响应数据
$response = array();

// 检查是否有文件上传
if (isset($_FILES['file']) || isset($_FILES['image'])) {
    // 兼容不同的上传字段名
    $file = isset($_FILES['file']) ? $_FILES['file'] : $_FILES['image'];
    
    // 检查上传错误
    if ($file['error'] !== UPLOAD_ERR_OK) {
        $error_message = '上传失败: ';
        switch ($file['error']) {
            case UPLOAD_ERR_INI_SIZE:
            case UPLOAD_ERR_FORM_SIZE:
                $error_message .= '文件大小超过限制';
                break;
            case UPLOAD_ERR_PARTIAL:
                $error_message .= '文件上传不完整';
                break;
            case UPLOAD_ERR_NO_FILE:
                $error_message .= '没有文件被上传';
                break;
            case UPLOAD_ERR_NO_TMP_DIR:
                $error_message .= '临时文件夹不存在';
                break;
            case UPLOAD_ERR_CANT_WRITE:
                $error_message .= '无法写入磁盘';
                break;
            case UPLOAD_ERR_EXTENSION:
                $error_message .= '上传被扩展程序中断';
                break;
            default:
                $error_message .= '未知错误';
        }
        $response['error'] = $error_message;
    } else {
        // 获取文件信息
        $file_name = $file['name'];
        $file_tmp = $file['tmp_name'];
        $file_size = $file['size'];
        $file_type = $file['type'];
        
        // 检查文件类型
        $allowed_types = array('image/jpeg', 'image/png', 'image/gif', 'image/webp');
        if (!in_array($file_type, $allowed_types)) {
            $response['error'] = '不支持的文件类型，仅支持JPG、PNG、GIF和WEBP格式';
        }
        // 检查文件大小（限制为5MB）
        elseif ($file_size > 5 * 1024 * 1024) {
            $response['error'] = '文件大小不能超过5MB';
        } else {
            // 生成唯一文件名
            $file_extension = pathinfo($file_name, PATHINFO_EXTENSION);
            $new_file_name = uniqid() . '_' . time() . '.' . $file_extension;
            $target_file = $target_dir . $new_file_name;
            
            // 移动上传的文件
            if (move_uploaded_file($file_tmp, $target_file)) {
                // 构建文件URL
                $file_url = str_replace('../', '', $target_file);
                $site_url = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://" . $_SERVER['HTTP_HOST'];
                $base_path = rtrim(dirname(dirname($_SERVER['PHP_SELF'])), '/') . '/';
                $file_url = $site_url . $base_path . $file_url;
                
                // 返回成功响应
                $response['location'] = $file_url; // TinyMCE需要location字段
                
                // 兼容其他编辑器可能需要的字段
                $response['success'] = true;
                $response['file'] = array(
                    'url' => $file_url,
                    'name' => $new_file_name,
                    'size' => $file_size,
                    'type' => $file_type
                );
            } else {
                $response['error'] = '文件上传失败，请检查目录权限';
            }
        }
    }
} else {
    $response['error'] = '没有文件被上传';
}

// 返回JSON响应
header('Content-Type: application/json');
echo json_encode($response);