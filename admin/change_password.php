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
$page_title = '修改密码';
$breadcrumb = [
  ['text' => '首页', 'url' => 'index.php'],
  ['text' => '修改密码']
];
$message = '';
$message_type = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $csrf_token = isset($_POST['csrf_token']) ? $_POST['csrf_token'] : '';

  if (!verify_csrf_token($csrf_token)) {
    $message = '安全验证失败，请刷新页面重试';
    $message_type = 'error';
  } else {
    $current_password = isset($_POST['current_password']) ? trim($_POST['current_password']) : '';
    $new_password = isset($_POST['new_password']) ? trim($_POST['new_password']) : '';
    $confirm_password = isset($_POST['confirm_password']) ? trim($_POST['confirm_password']) : '';

    if (empty($current_password)) {
      $message = '请输入当前密码';
      $message_type = 'error';
    } elseif (empty($new_password)) {
      $message = '请输入新密码';
      $message_type = 'error';
    } elseif (strlen($new_password) < 6) {
      $message = '新密码长度不能少于6个字符';
      $message_type = 'error';
    } elseif ($new_password !== $confirm_password) {
      $message = '两次输入的新密码不一致';
      $message_type = 'error';
    } else {

      $admin_id = $current_admin['id'];
      $sql = "SELECT password FROM admin_users WHERE id = ?";
      $admin = db_fetch_one($sql, [$admin_id]);

      if (!$admin || !password_verify($current_password, $admin['password'])) {
        $message = '当前密码不正确';
        $message_type = 'error';
      } else {
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
        $sql = "UPDATE admin_users SET password = ? WHERE id = ?";
        $result = db_execute($sql, [$hashed_password, $admin_id]);

        if ($result['affected_rows'] > 0) {
          $message = '密码修改成功，下次登录请使用新密码';
          $message_type = 'success';
        } else {
          $message = '密码修改失败，请重试';
          $message_type = 'error';
        }
      }
    }
  }
}

$csrf_token = generate_csrf_token();
include_once __DIR__ . '/views/header.php';
?>

<div class="page-header">
  <h1>修改密码</h1>
  <p>修改您的账户登录密码</p>
</div>

<?php if (!empty($message)): ?>
  <div class="alert alert-<?php echo $message_type; ?>">
    <?php echo xss_clean($message); ?>
  </div>
<?php endif; ?>

<div class="card">
  <div class="card-header">
    <h3 class="card-title">修改密码</h3>
  </div>
  <div class="card-body">
    <form method="post" action="" id="passwordForm">
      <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">

      <div class="form-group">
        <label for="current_password">当前密码 <span class="required">*</span></label>
        <input type="password" id="current_password" name="current_password" class="form-control" required>
      </div>

      <div class="form-group">
        <label for="new_password">新密码 <span class="required">*</span></label>
        <input type="password" id="new_password" name="new_password" class="form-control" required minlength="6">
        <div class="form-hint">密码长度不少于6个字符</div>
      </div>

      <div class="form-group">
        <label for="confirm_password">确认新密码 <span class="required">*</span></label>
        <input type="password" id="confirm_password" name="confirm_password" class="form-control" required
          minlength="6">
      </div>

      <div class="form-actions">
        <button type="submit" class="btn btn-primary">保存修改</button>
      </div>
    </form>
  </div>
</div>

<script>
  document.addEventListener('DOMContentLoaded', function () {
    const passwordForm = document.getElementById('passwordForm');
    const newPasswordInput = document.getElementById('new_password');
    const confirmPasswordInput = document.getElementById('confirm_password');

    confirmPasswordInput.addEventListener('input', function () {
      if (newPasswordInput.value !== confirmPasswordInput.value) {
        confirmPasswordInput.setCustomValidity('两次输入的密码不一致');
      } else {
        confirmPasswordInput.setCustomValidity('');
      }
    });

    newPasswordInput.addEventListener('input', function () {
      if (confirmPasswordInput.value && newPasswordInput.value !== confirmPasswordInput.value) {
        confirmPasswordInput.setCustomValidity('两次输入的密码不一致');
      } else {
        confirmPasswordInput.setCustomValidity('');
      }
    });

    const alertElement = document.querySelector('.alert');
    if (alertElement) {
      setTimeout(function () {
        alertElement.style.opacity = '0';
        alertElement.style.transition = 'opacity 0.5s';

        setTimeout(function () {
          alertElement.style.display = 'none';
        }, 500);
      }, 3000);
    }
  });
</script>

<?php

include_once __DIR__ . '/views/footer.php';
?>