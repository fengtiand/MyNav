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
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $csrf_token = isset($_POST['csrf_token']) ? $_POST['csrf_token'] : '';

  if (!verify_csrf_token($csrf_token)) {
    json_output([], 1, '安全验证失败，请刷新页面重试');
  }
  $link_id = isset($_POST['link_id']) ? (int) $_POST['link_id'] : 0;
  $title = isset($_POST['title']) ? trim($_POST['title']) : '';
  $url = isset($_POST['url']) ? trim($_POST['url']) : '';
  $category_id = isset($_POST['category_id']) ? (int) $_POST['category_id'] : 0;
  $description = isset($_POST['description']) ? trim($_POST['description']) : '';
  $sort_order = isset($_POST['sort_order']) ? (int) $_POST['sort_order'] : 0;
  $status = isset($_POST['status']) ? (int) $_POST['status'] : 1;
  if (empty($title)) {
    json_output([], 1, '链接标题不能为空');
  } elseif (empty($url)) {
    json_output([], 1, '链接地址不能为空');
  } elseif ($category_id <= 0) {
    json_output([], 1, '请选择所属分类');
  } else {
    $conn = db_connect();
    if ($link_id > 0) {
      $sql = "UPDATE links SET title = ?, url = ?, category_id = ?, description = ?, sort_order = ?, status = ? WHERE id = ?";
      $stmt = $conn->prepare($sql);
      $stmt->bind_param("ssissii", $title, $url, $category_id, $description, $sort_order, $status, $link_id);

      if ($stmt->execute()) {
        json_output(['id' => $link_id], 0, '链接已成功更新');
      } else {
        json_output([], 1, '更新链接失败：' . $conn->error);
      }
    } else {
      $sql = "INSERT INTO links (title, url, category_id, description, sort_order, status) VALUES (?, ?, ?, ?, ?, ?)";
      $stmt = $conn->prepare($sql);
      $stmt->bind_param("ssissi", $title, $url, $category_id, $description, $sort_order, $status);
      if ($stmt->execute()) {
        $new_id = $stmt->insert_id;
        json_output(['id' => $new_id], 0, '链接已成功添加');
      } else {
        json_output([], 1, '添加链接失败：' . $conn->error);
      }
    }

    $stmt->close();
    $conn->close();
  }
} else {
  json_output([], 1, '非法请求');
}