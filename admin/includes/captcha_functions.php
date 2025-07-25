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
if (session_status() == PHP_SESSION_NONE) {
  session_start();
}

/**
 * 验证验证码
 * @param string $input_code 用户输入的验证码
 * @return bool 验证是否成功
 */
function verify_captcha($input_code)
{

  if (!isset($_SESSION['captcha_code']) || !isset($_SESSION['captcha_time'])) {
    return false;
  }

  if (time() - $_SESSION['captcha_time'] > 300) {
    unset($_SESSION['captcha_code']);
    unset($_SESSION['captcha_time']);
    return false;
  }
  $result = strtoupper(trim($input_code)) === strtoupper($_SESSION['captcha_code']);
  if ($result) {
    unset($_SESSION['captcha_code']);
    unset($_SESSION['captcha_time']);
  }

  return $result;
}

/**
 * 清除验证码
 */
function clear_captcha()
{
  unset($_SESSION['captcha_code']);
  unset($_SESSION['captcha_time']);
}

/**
 * 检查登录失败次数
 * @return int 失败次数
 */
function get_login_attempts()
{
  return isset($_SESSION['login_attempts']) ? $_SESSION['login_attempts'] : 0;
}

/**
 * 增加登录失败次数
 */
function increment_login_attempts()
{
  if (!isset($_SESSION['login_attempts'])) {
    $_SESSION['login_attempts'] = 0;
  }
  $_SESSION['login_attempts']++;
  $_SESSION['last_attempt_time'] = time();
}

/**
 * 重置登录失败次数
 */
function reset_login_attempts()
{
  unset($_SESSION['login_attempts']);
  unset($_SESSION['last_attempt_time']);
}

/**
 * 检查是否需要验证码
 * @return bool 是否需要验证码
 */
function need_captcha()
{
  return get_login_attempts() >= 3;
}

/**
 * 检查是否被临时锁定
 * @return array 锁定信息 ['locked' => bool, 'remaining' => int]
 */
function check_login_lock()
{
  $attempts = get_login_attempts();
  if ($attempts >= 5) {
    $last_attempt = isset($_SESSION['last_attempt_time']) ? $_SESSION['last_attempt_time'] : 0;
    $lock_time = 15 * 60;
    $remaining = $lock_time - (time() - $last_attempt);

    if ($remaining > 0) {
      return ['locked' => true, 'remaining' => $remaining];
    } else {
      reset_login_attempts();
    }
  }

  return ['locked' => false, 'remaining' => 0];
}
?>