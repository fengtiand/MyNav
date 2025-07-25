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
$conn = db_connect();
$sql = "SELECT value FROM settings WHERE `key` = 'personal_avatar'";
$result = $conn->query($sql);
$current_avatar = '';
if ($result && $row = $result->fetch_assoc()) {
  $current_avatar = $row['value'];
}

if (!empty($current_avatar) && strpos($current_avatar, 'uploads/avatars/') === 0) {
  $file_path = '../' . $current_avatar;
  if (file_exists($file_path) && is_file($file_path)) {
    $filename = basename($current_avatar);
    if (strpos($filename, 'avatar_') === 0) {
      if (unlink($file_path)) {
        $sql = "UPDATE settings SET value = '' WHERE `key` = 'personal_avatar'";
        if ($conn->query($sql)) {
          $conn->close();
          echo json_encode(['success' => true, 'message' => '头像已删除']);
          exit;
        } else {
          $conn->close();
          echo json_encode(['success' => false, 'message' => '数据库更新失败']);
          exit;
        }
      } else {
        $conn->close();
        echo json_encode(['success' => false, 'message' => '文件删除失败']);
        exit;
      }
    } else {
      $conn->close();
      echo json_encode(['success' => false, 'message' => '无法删除系统头像']);
      exit;
    }
  } else {
    $sql = "UPDATE settings SET value = '' WHERE `key` = 'personal_avatar'";
    if ($conn->query($sql)) {
      $conn->close();
      echo json_encode(['success' => true, 'message' => '头像记录已清除']);
      exit;
    } else {
      $conn->close();
      echo json_encode(['success' => false, 'message' => '数据库更新失败']);
      exit;
    }
  }
} else {
  $conn->close();
  echo json_encode(['success' => false, 'message' => '没有可删除的头像']);
  exit;
}
?>