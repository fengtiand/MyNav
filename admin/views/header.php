<?php
if (!file_exists('../install.lock')) {
  header('Location: ../install.php');
  exit;
}
$admin = $current_admin;
$admin_name = isset($admin['nickname']) && !empty($admin['nickname']) ? $admin['nickname'] : $admin['username'];
$admin_avatar = isset($admin['avatar']) && !empty($admin['avatar']) ? $admin['avatar'] : '';
if (!empty($admin_avatar) && strpos($admin_avatar, 'uploads/') === 0) {
  $admin_avatar = '../' . $admin_avatar;
}
$admin_first_letter = mb_substr($admin_name, 0, 1, 'UTF-8');
?>
<!DOCTYPE html>
<html lang="zh-CN">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?php echo isset($page_title) ? $page_title . ' - 后台管理系统' : '后台管理系统'; ?></title>
  <link rel="stylesheet" href="assets/css/style.css">
  <link rel="stylesheet" href="https://cdn.bootcdn.net/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>

<body>
  <div class="admin-container">
    <?php include_once __DIR__ . '/sidebar.php'; ?>
    <div class="main-content">
      <header class="header">
        <div class="header-left">
          <button class="header-toggle">
            <i class="fas fa-bars"></i>
          </button>

        </div>
        <div class="header-right">
          <div id="current-datetime"></div>
          <div class="admin-info" style="margin-left: 20px;">
            <div class="admin-avatar">
              <?php if (!empty($admin_avatar)): ?>
                <img src="<?php echo $admin_avatar; ?>" alt="头像">
              <?php else: ?>
                <?php echo $admin_first_letter; ?>
              <?php endif; ?>
            </div>
            <div class="admin-name"><?php echo $admin_name; ?></div>
            <div class="dropdown-toggle">
              <i class="fas fa-chevron-down"></i>
            </div>
            <div class="dropdown-menu">
              <a href="admin_profile.php" class="dropdown-item">
                <i class="fas fa-user"></i>个人资料
              </a>
              <a href="change_password.php" class="dropdown-item">
                <i class="fas fa-key"></i>修改密码
              </a>
              <a href="logout.php" class="dropdown-item">
                <i class="fas fa-sign-out-alt"></i>退出登录
              </a>
            </div>
          </div>
        </div>
      </header>
      <div class="page-content">