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
require_once __DIR__ . '/admin/includes/template.php';
session_start();
$is_admin_logged_in = isset($_SESSION['admin_id']) && $_SESSION['admin_id'] > 0;
if (isset($_POST['switch_template']) && $is_admin_logged_in) {
  $template_name = $_POST['template'];
  if (set_current_template($template_name)) {
    $success_message = "模板切换成功！";
  } else {
    $error_message = "模板切换失败！";
  }
}
$current_template = get_current_template();
$available_templates = get_available_templates();
?>
<!DOCTYPE html>
<html lang="zh-CN">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>模板预览 - 个人导航</title>
  <link rel="stylesheet" href="https://cdn.bootcdn.net/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link rel="stylesheet" href="assets/css/temp.css">
</head>

<body>
  <a href="index.php" class="back-link" title="返回首页">
    <i class="fas fa-arrow-left"></i>
  </a>

  <div class="container">
    <header>
      <h1>模板预览</h1>
      <p>选择你喜欢的模板风格</p>
      <?php if (!$is_admin_logged_in): ?>
        <div class="message"
          style="background: rgba(255, 193, 7, 0.2); border: 1px solid rgba(255, 193, 7, 0.3); color: rgba(255, 193, 7, 1);">
          <i class="fas fa-info-circle"></i> 需要管理员登录才能切换模板
        </div>
      <?php endif; ?>
    </header>

    <?php if (isset($success_message)): ?>
      <div class="message success"><?php echo $success_message; ?></div>
    <?php endif; ?>

    <?php if (isset($error_message)): ?>
      <div class="message error"><?php echo $error_message; ?></div>
    <?php endif; ?>

    <div class="templates-grid">
      <?php foreach ($available_templates as $folder => $template): ?>
        <div class="template-card <?php echo $folder === $current_template ? 'current' : ''; ?>">
          <div class="template-preview">
            <?php
            $preview_path = "templates/{$folder}/" . ($template['preview'] ?? 'preview.jpg');
            if (file_exists($preview_path)):
              ?>
              <img src="<?php echo $preview_path; ?>" alt="<?php echo htmlspecialchars($template['name']); ?> 预览图"
                style="width: 100%; height: 100%; object-fit: cover; object-position: center;"
                onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
              <div class="preview-fallback" style="display: none;">
                <i class="fas fa-palette"></i>
              </div>
            <?php else: ?>
              <i class="fas fa-palette"></i>
            <?php endif; ?>
          </div>
          <div class="template-info">
            <div class="template-name">
              <?php echo htmlspecialchars($template['name']); ?>
              <?php if ($folder === $current_template): ?>
                <span class="current-badge">当前</span>
              <?php endif; ?>
            </div>
            <div class="template-author">
              <i class="fas fa-user"></i> <?php echo htmlspecialchars($template['author']); ?>
            </div>
            <div class="template-version">
              <i class="fas fa-tag"></i> v<?php echo htmlspecialchars($template['version']); ?>
            </div>
            <div class="template-description">
              <?php echo htmlspecialchars($template['description']); ?>
            </div>
            <div class="template-actions">
              <a href="index.php?template=<?php echo $folder; ?>" class="btn btn-outline" target="_blank">
                <i class="fas fa-eye"></i> 预览
              </a>
              <?php if ($is_admin_logged_in && $folder !== $current_template): ?>
                <form method="post" style="display: inline;">
                  <input type="hidden" name="template" value="<?php echo $folder; ?>">
                  <button type="submit" name="switch_template" class="btn btn-primary">
                    <i class="fas fa-check"></i> 使用
                  </button>
                </form>
              <?php elseif (!$is_admin_logged_in): ?>
                <span class="btn btn-outline" style="opacity: 0.5; cursor: not-allowed;" title="需要管理员登录">
                  <i class="fas fa-lock"></i> 需要登录
                </span>
              <?php endif; ?>
            </div>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</body>

</html>