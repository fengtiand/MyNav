<?php
/*
 * MyNav个人导航系统 2.0.0
 * 作者：奉天
 * 官网：www.ococn.cn
 * 
 * 版权声明：
 * 本程序为开源软件，仅供学习和个人使用
 * 禁止使用本程序进行任何形式的商业盈利活动
 * 如需商业使用，请联系作者获得授权
 * 
 * Copyright (c) 2025 星涵网络 All rights reserved.
 */

session_start();
require_once __DIR__ . '/includes/auth.php';
header('Content-Type: application/json; charset=utf-8');
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  echo json_encode(['success' => false, 'message' => '请求方法错误']);
  exit;
}
$csrf_token = isset($_POST['csrf_token']) ? $_POST['csrf_token'] : '';
if (!verify_csrf_token($csrf_token)) {
  echo json_encode(['success' => false, 'message' => '安全验证失败']);
  exit;
}
if (!isset($_FILES['avatar']) || $_FILES['avatar']['error'] !== UPLOAD_ERR_OK) {
  $error_messages = [
    UPLOAD_ERR_INI_SIZE => '文件大小超过系统限制',
    UPLOAD_ERR_FORM_SIZE => '文件大小超过表单限制',
    UPLOAD_ERR_PARTIAL => '文件只有部分被上传',
    UPLOAD_ERR_NO_FILE => '没有文件被上传',
    UPLOAD_ERR_NO_TMP_DIR => '找不到临时文件夹',
    UPLOAD_ERR_CANT_WRITE => '文件写入失败',
    UPLOAD_ERR_EXTENSION => '文件上传被扩展程序阻止'
  ];
  $error = $_FILES['avatar']['error'] ?? UPLOAD_ERR_NO_FILE;
  $message = $error_messages[$error] ?? '未知上传错误';

  echo json_encode(['success' => false, 'message' => $message]);
  exit;
}
$file = $_FILES['avatar'];
$allowed_types = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
$file_type = $file['type'];
$file_info = getimagesize($file['tmp_name']);

if (!in_array($file_type, $allowed_types) || !$file_info) {
  echo json_encode(['success' => false, 'message' => '请上传有效的图片文件（JPG、PNG、GIF、WebP）']);
  exit;
}

if ($file['size'] > 4 * 1024 * 1024) {
  echo json_encode(['success' => false, 'message' => '文件大小不能超过4MB']);
  exit;
}
$upload_dir = '../uploads/avatars/';
if (!is_dir($upload_dir)) {
  if (!mkdir($upload_dir, 0755, true)) {
    echo json_encode(['success' => false, 'message' => '无法创建上传目录']);
    exit;
  }
}

$conn = db_connect();
$sql = "SELECT value FROM settings WHERE `key` = 'personal_avatar'";
$result = $conn->query($sql);
$old_avatar = '';

if ($result && $row = $result->fetch_assoc()) {
  $old_avatar = $row['value'];
}
$file_extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
if ($file_extension === 'jpeg') {
  $file_extension = 'jpg';
}
$new_filename = 'avatar_' . time() . '_' . uniqid() . '.' . $file_extension;
$upload_path = $upload_dir . $new_filename;
$relative_path = 'uploads/avatars/' . $new_filename;
if (!move_uploaded_file($file['tmp_name'], $upload_path)) {
  echo json_encode(['success' => false, 'message' => '文件上传失败']);
  exit;
}

if (!empty($old_avatar) && strpos($old_avatar, 'uploads/avatars/') === 0) {
  $old_file_path = '../' . $old_avatar;
  if (file_exists($old_file_path) && is_file($old_file_path)) {

    $old_filename = basename($old_avatar);
    if (strpos($old_filename, 'avatar_') === 0) {
      unlink($old_file_path);
    }
  }
}

$sql = "INSERT INTO settings (`key`, value) VALUES ('personal_avatar', ?) ON DUPLICATE KEY UPDATE value = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ss", $relative_path, $relative_path);

if (!$stmt->execute()) {
  if (file_exists($upload_path)) {
    unlink($upload_path);
  }

  $stmt->close();
  $conn->close();

  echo json_encode(['success' => false, 'message' => '数据库更新失败']);
  exit;
}

$stmt->close();
$conn->close();
echo json_encode([
  'success' => true,
  'message' => '头像上传成功',
  'path' => $relative_path,
  'url' => '../' . $relative_path
]);
?>