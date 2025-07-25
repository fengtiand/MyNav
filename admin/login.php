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
require_once __DIR__ . '/includes/functions.php';
if (!function_exists('need_captcha')) {
  require_once __DIR__ . '/includes/captcha_functions.php';
}

if (is_admin_logged_in()) {
  redirect('index.php');
}
$error = '';
$show_captcha = need_captcha();
$lock_info = check_login_lock();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

  if ($lock_info['locked']) {
    $minutes = ceil($lock_info['remaining'] / 60);
    $error = "登录失败次数过多，请等待 {$minutes} 分钟后再试";
  } else {

    $username = isset($_POST['username']) ? trim($_POST['username']) : '';
    $password = isset($_POST['password']) ? $_POST['password'] : '';
    $captcha = isset($_POST['captcha']) ? trim($_POST['captcha']) : '';
    $remember = isset($_POST['remember']) ? (bool) $_POST['remember'] : false;
    $csrf_token = isset($_POST['csrf_token']) ? $_POST['csrf_token'] : '';

    if (!verify_csrf_token($csrf_token)) {
      $error = '安全验证失败，请刷新页面重试';
    } elseif (empty($username)) {
      $error = '请输入用户名';
    } elseif (empty($password)) {
      $error = '请输入密码';
    } elseif ($show_captcha && !verify_captcha($captcha)) {
      increment_login_attempts();
      $error = '验证码错误';
      $show_captcha = true;
    } else {

      $result = verify_admin_login($username, $password);

      if ($result) {
        reset_login_attempts();
        if ($remember) {

          $expire = time() + 7 * 24 * 60 * 60;
          setcookie('admin_username', $username, $expire, '/');
        }
        redirect('index.php');
      } else {

        increment_login_attempts();
        $error = '用户名或密码错误';
        $show_captcha = need_captcha();
      }
    }
  }
}

$lock_info = check_login_lock();
$show_captcha = need_captcha();
$csrf_token = generate_csrf_token();
?>
<!DOCTYPE html>
<html lang="zh-CN">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>后台管理系统 - 登录</title>
  <link rel="stylesheet" href="assets/css/login.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>

<body>
  <div class="bg-shapes">
    <div></div>
    <div></div>
    <div></div>
    <div></div>
    <div></div>
  </div>

  <div class="login-container">
    <h1 class="login-title">管理员登录</h1>

    <?php if (!empty($error)): ?>
      <div class="error-message show"><?php echo xss_clean($error); ?></div>
    <?php else: ?>
      <div class="error-message" id="errorMessage"></div>
    <?php endif; ?>

    <form id="loginForm" class="login-form" method="post" action="">
      <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">

      <div class="form-group">
        <input type="text" id="username" name="username" required>
        <label for="username">用户名</label>
      </div>

      <div class="form-group">
        <input type="password" id="password" name="password" required>
        <label for="password">密码</label>
      </div>

      <?php if ($show_captcha): ?>
        <div class="form-group captcha-group">
          <div class="captcha-container">
            <input type="text" id="captcha" name="captcha" required maxlength="4" placeholder="验证码">
            <div class="captcha-image">
              <img src="captcha.php" alt="验证码" id="captcha-img">
              <button type="button" class="refresh-captcha" onclick="refreshCaptcha()" title="刷新验证码">
                <i class="fas fa-sync-alt"></i>
              </button>
            </div>
          </div>
          <div class="captcha-hint">点击图片刷新验证码</div>
        </div>
      <?php endif; ?>

      <div class="remember-me">
        <input type="checkbox" id="remember" name="remember" value="1">
        <label for="remember">记住我</label>
      </div>

      <button type="submit" class="login-btn">登 录</button>
    </form>
  </div>

  <script src="assets/js/login.js"></script>

  <?php if ($lock_info['locked']): ?>
    <script>
      document.addEventListener('DOMContentLoaded', function () {
        startCountdown(<?php echo $lock_info['remaining']; ?>);
      });
    </script>
  <?php endif; ?>
</body>

</html>