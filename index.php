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

if (!file_exists('install.lock')) {
  header('Location: install.php');
  exit;
}

if (!defined('SECURE_ACCESS')) {
  define('SECURE_ACCESS', true);
}

require_once __DIR__ . '/config/security_config.php';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/admin/includes/template.php';

SecurityManager::init();

$_GET = SecurityManager::filterInput($_GET);
$_POST = SecurityManager::filterInput($_POST);

$template_input = isset($_GET['template']) ? $_GET['template'] : '';
if (!empty($template_input) && !SecurityManager::validateTemplate($template_input)) {
  $template_input = '';
}

$current_template = !empty($template_input) ? $template_input : get_current_template();

$available_templates = get_available_templates();
if (!isset($available_templates[$current_template])) {
  $current_template = get_default_template();
}

$conn = db_connect();

$site_title = "个人导航";
$site_description = "我的个人网址导航";

try {
  $stmt = $conn->prepare("SELECT `key`, value FROM settings WHERE `key` IN (?, ?)");
  $site_name = 'site_name';
  $site_desc = 'site_description';
  $stmt->bind_param("ss", $site_name, $site_desc);
  $stmt->execute();
  $result = $stmt->get_result();

  if ($result) {
    while ($row = $result->fetch_assoc()) {
      switch ($row['key']) {
        case 'site_name':
          if (!empty($row['value'])) {
            $site_title = $row['value'];
          }
          break;
        case 'site_description':
          if (!empty($row['value'])) {
            $site_description = $row['value'];
          }
          break;
      }
    }
  }
  $stmt->close();
} catch (Exception $e) {

}

$categories = [];
try {
  $stmt = $conn->prepare("SELECT * FROM categories WHERE status = ? ORDER BY sort_order ASC, id ASC");
  $status = 1;
  $stmt->bind_param("i", $status);
  $stmt->execute();
  $result = $stmt->get_result();

  if ($result) {
    while ($row = $result->fetch_assoc()) {
      $categories[$row['id']] = $row;
    }
  }
  $stmt->close();
} catch (Exception $e) {

}

$links = [];
try {
  $stmt = $conn->prepare("SELECT * FROM links WHERE status = ? ORDER BY sort_order ASC, id ASC");
  $status = 1;
  $stmt->bind_param("i", $status);
  $stmt->execute();
  $result = $stmt->get_result();

  if ($result) {
    while ($row = $result->fetch_assoc()) {
      if (!isset($links[$row['category_id']])) {
        $links[$row['category_id']] = [];
      }
      $links[$row['category_id']][] = $row;
    }
  }
  $stmt->close();
} catch (Exception $e) {

}

$conn->close();

render_template($current_template, $categories, $links, $site_title, $site_description);
?>