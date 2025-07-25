<?php
function checkEnvironment()
{
  $checks = [];

  $checks['php_version'] = [
    'name' => 'PHP 版本',
    'required' => '7.0+',
    'current' => PHP_VERSION,
    'status' => version_compare(PHP_VERSION, '7.0.0', '>=')
  ];

  $checks['mysqli'] = [
    'name' => 'MySQLi 扩展',
    'required' => '必需',
    'current' => extension_loaded('mysqli') ? '已安装' : '未安装',
    'status' => extension_loaded('mysqli')
  ];

  $checks['session'] = [
    'name' => 'Session 支持',
    'required' => '必需',
    'current' => function_exists('session_start') ? '支持' : '不支持',
    'status' => function_exists('session_start')
  ];

  $checks['json'] = [
    'name' => 'JSON 扩展',
    'required' => '必需',
    'current' => extension_loaded('json') ? '已安装' : '未安装',
    'status' => extension_loaded('json')
  ];

  $checks['config_writable'] = [
    'name' => 'config/ 目录写入权限',
    'required' => '可写',
    'current' => is_writable('../config') ? '可写' : '不可写',
    'status' => is_writable('../config')
  ];

  $checks['root_writable'] = [
    'name' => '根目录写入权限',
    'required' => '可写',
    'current' => is_writable('../') ? '可写' : '不可写',
    'status' => is_writable('../')
  ];

  return $checks;
}

function getSystemInfo()
{
  return [
    'php_version' => PHP_VERSION,
    'server_software' => $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown',
    'document_root' => $_SERVER['DOCUMENT_ROOT'] ?? 'Unknown',
    'max_execution_time' => ini_get('max_execution_time'),
    'memory_limit' => ini_get('memory_limit'),
    'upload_max_filesize' => ini_get('upload_max_filesize'),
    'post_max_size' => ini_get('post_max_size')
  ];
}

if (isset($_GET['ajax'])) {
  header('Content-Type: application/json');
  echo json_encode([
    'checks' => checkEnvironment(),
    'system' => getSystemInfo()
  ]);
  exit;
}
?>