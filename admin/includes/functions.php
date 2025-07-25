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

/**
 * 获取客户端IP地址
 * @return string
 */
function get_client_ip()
{
  $ip = '';

  if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
    $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
  } elseif (isset($_SERVER['HTTP_CLIENT_IP'])) {
    $ip = $_SERVER['HTTP_CLIENT_IP'];
  } elseif (isset($_SERVER['REMOTE_ADDR'])) {
    $ip = $_SERVER['REMOTE_ADDR'];
  }

  // 处理多个IP的情况，取第一个
  if (strpos($ip, ',') !== false) {
    $ip = explode(',', $ip)[0];
  }

  return $ip;
}

/**
 * 记录管理员登录日志
 * @param int $admin_id 管理员ID
 * @param int $status 登录状态 0:失败 1:成功
 * @param string $message 登录信息
 * @return array
 */
function log_admin_login($admin_id, $status, $message = '')
{
  $ip = get_client_ip();
  $time = date('Y-m-d H:i:s');

  $sql = "INSERT INTO admin_login_logs (admin_id, login_ip, login_time, login_status, login_message) 
            VALUES (?, ?, ?, ?, ?)";

  return db_execute($sql, [$admin_id, $ip, $time, $status, $message]);
}

/**
 * 更新管理员最后登录信息
 * @param int $admin_id 管理员ID
 * @return array
 */
function update_admin_login_info($admin_id)
{
  $ip = get_client_ip();
  $time = date('Y-m-d H:i:s');

  $sql = "UPDATE admin_users SET last_login_ip = ?, last_login_time = ? WHERE id = ?";

  return db_execute($sql, [$ip, $time, $admin_id]);
}

/**
 * 防止XSS攻击
 * @param string $str 需要过滤的字符串
 * @return string
 */
function xss_clean($str)
{
  return htmlspecialchars($str, ENT_QUOTES, 'UTF-8');
}

/**
 * 获取随机字符串
 * @param int $length 长度
 * @return string
 */
function random_string($length = 8)
{
  $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
  $str = '';

  for ($i = 0; $i < $length; $i++) {
    $str .= $chars[mt_rand(0, strlen($chars) - 1)];
  }

  return $str;
}

/**
 * 生成CSRF Token
 * @return string
 */
function generate_csrf_token()
{
  if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
  }

  return $_SESSION['csrf_token'];
}

/**
 * 验证CSRF Token
 * @param string $token 待验证的token
 * @return bool
 */
function verify_csrf_token($token)
{
  if (!isset($_SESSION['csrf_token']) || !$token) {
    return false;
  }

  return hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * 重定向
 * @param string $url 目标URL
 */
function redirect($url)
{
  header("Location: $url");
  exit;
}

/**
 * 判断是否为AJAX请求
 * @return bool
 */
function is_ajax_request()
{
  return isset($_SERVER['HTTP_X_REQUESTED_WITH']) &&
    strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
}

/**
 * 输出JSON数据
 * @param array $data 数据
 * @param int $code 状态码
 * @param string $msg 消息
 */
function json_output($data = [], $code = 0, $msg = 'success')
{
  $result = [
    'code' => $code,
    'msg' => $msg,
    'data' => $data
  ];

  header('Content-Type: application/json');
  echo json_encode($result, JSON_UNESCAPED_UNICODE);
  exit;
}

/**
 * 检查管理员是否已登录
 * @return bool
 */
function is_admin_logged_in()
{
  return isset($_SESSION['admin_id']) && $_SESSION['admin_id'] > 0;
}

/**
 * 获取当前登录的管理员信息
 * @return array|null
 */
function get_current_admin()
{
  if (!is_admin_logged_in()) {
    return null;
  }

  $admin_id = $_SESSION['admin_id'];
  $sql = "SELECT * FROM admin_users WHERE id = ? AND status = 1";

  return db_fetch_one($sql, [$admin_id]);
}

/**
 * 验证管理员登录
 * @param string $username 用户名
 * @param string $password 密码
 * @return array|bool 成功返回管理员信息，失败返回false
 */
function verify_admin_login($username, $password)
{
  $sql = "SELECT * FROM admin_users WHERE username = ? AND status = 1";
  $admin = db_fetch_one($sql, [$username]);

  if (!$admin) {
    return false;
  }

  if (password_verify($password, $admin['password'])) {
    // 更新登录信息
    update_admin_login_info($admin['id']);

    // 记录登录日志
    log_admin_login($admin['id'], 1, '登录成功');

    // 设置session
    $_SESSION['admin_id'] = $admin['id'];
    $_SESSION['admin_username'] = $admin['username'];
    $_SESSION['admin_nickname'] = $admin['nickname'];
    $_SESSION['admin'] = $admin;

    return $admin;
  }

  // 记录登录失败日志
  log_admin_login($admin['id'], 0, '密码错误');

  return false;
}

/**
 * 管理员退出登录
 */
function admin_logout()
{
  if (isset($_SESSION['admin_id'])) {
    unset($_SESSION['admin_id']);
    unset($_SESSION['admin_username']);
    unset($_SESSION['admin_nickname']);
  }

  session_destroy();
}