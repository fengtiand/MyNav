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

require_once __DIR__ . '/../../config/database.php';

function scan_available_templates()
{
  $templates = [];
  $templates_dir = __DIR__ . '/../../templates';

  if (!is_dir($templates_dir)) {
    return $templates;
  }

  $dirs = scandir($templates_dir);
  foreach ($dirs as $dir) {
    if ($dir === '.' || $dir === '..') {
      continue;
    }

    $template_path = $templates_dir . '/' . $dir;
    if (!is_dir($template_path)) {
      continue;
    }
    $index_file = $template_path . '/index.php';
    $config_file = $template_path . '/template.json';

    if (file_exists($index_file) && file_exists($config_file)) {
      $config = json_decode(file_get_contents($config_file), true);
      if ($config) {
        $templates[$dir] = array_merge($config, [
          'folder' => $dir,
          'path' => 'templates/' . $dir . '/index.php'
        ]);
      }
    }
  }

  return $templates;
}
function get_current_template()
{
  $conn = db_connect();

  try {
    $sql = "SELECT value FROM settings WHERE `key` = 'current_template'";
    $result = $conn->query($sql);

    if ($result && $row = $result->fetch_assoc()) {
      $template = $row['value'];
    } else {
      $template = 'glass';
    }
  } catch (Exception $e) {
    $template = 'glass';
  }

  $conn->close();
  return $template;
}

function set_current_template($template_name)
{
  $conn = db_connect();

  try {
    $sql = "INSERT INTO settings (`key`, value) VALUES ('current_template', ?) 
                ON DUPLICATE KEY UPDATE value = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('ss', $template_name, $template_name);
    $result = $stmt->execute();
    $stmt->close();
  } catch (Exception $e) {
    $result = false;
  }

  $conn->close();
  return $result;
}

function get_default_template()
{
  return 'glass';
}

function get_available_templates()
{
  return scan_available_templates();
}

function render_template($template_name, $categories, $links, $site_title, $site_description)
{
  $available_templates = get_available_templates();

  if (!isset($available_templates[$template_name])) {
    $template_name = get_default_template();
  }

  if (!isset($available_templates[$template_name])) {
    echo "错误：找不到可用的模板";
    return;
  }

  $template_path = $available_templates[$template_name]['path'];

  if (file_exists($template_path)) {
    include $template_path;
  } else {
    echo "模板文件不存在: " . htmlspecialchars($template_path);
  }
}
function sync_templates_to_database()
{
  $templates = scan_available_templates();
  $conn = db_connect();

  try {
    $sql = "DELETE FROM templates";
    $conn->query($sql);
    foreach ($templates as $folder => $template) {
      $sql = "INSERT INTO templates (folder, name, author, description, version, preview, created_at, updated_at, status) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, 1)";
      $stmt = $conn->prepare($sql);
      $stmt->bind_param(
        'ssssssss',
        $folder,
        $template['name'],
        $template['author'],
        $template['description'],
        $template['version'],
        $template['preview'],
        $template['created_at'],
        $template['updated_at']
      );
      $stmt->execute();
      $stmt->close();
    }

    $result = true;
  } catch (Exception $e) {
    $result = false;
  }

  $conn->close();
  return $result;
}

function get_template_info($template_name)
{
  $available_templates = get_available_templates();
  return isset($available_templates[$template_name]) ? $available_templates[$template_name] : null;
}