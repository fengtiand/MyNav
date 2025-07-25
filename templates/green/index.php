<?php
session_start();
$is_admin_logged_in = isset($_SESSION['admin_id']) && $_SESSION['admin_id'] > 0;

require_once __DIR__ . '/../../config/database.php';
$conn = db_connect();
$personal_settings = [];

$sql = "SELECT `key`, value FROM settings WHERE `key` LIKE 'personal_%' OR `key` = 'show_personal_info' OR `key` LIKE 'footer_%' OR `key` = 'show_footer'";
$result = $conn->query($sql);

if ($result) {
  while ($row = $result->fetch_assoc()) {
    $personal_settings[$row['key']] = $row['value'];
  }
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="zh-CN">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?php echo $site_title; ?></title>
  <link rel="stylesheet" href="https://cdn.bootcdn.net/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link rel="stylesheet" href="/templates/green/assets/css/style.css">
</head>

<body>
  <div class="container">
    <header class="header">
      <h1 class="site-title"><?php echo htmlspecialchars($site_title); ?></h1>
      <p class="site-desc"><?php echo htmlspecialchars($site_description); ?></p>
    </header>

    <?php if (($personal_settings['show_personal_info'] ?? '0') === '1'): ?>
      <div class="personal-info">
        <?php if (!empty($personal_settings['personal_avatar'])): ?>
          <div class="personal-avatar">
            <img src="<?php echo htmlspecialchars($personal_settings['personal_avatar']); ?>"
              alt="<?php echo htmlspecialchars($personal_settings['personal_name'] ?? '头像'); ?>">
          </div>
        <?php endif; ?>

        <div class="personal-details">
          <?php if (!empty($personal_settings['personal_name'])): ?>
            <h3 class="personal-name"><?php echo htmlspecialchars($personal_settings['personal_name']); ?></h3>
          <?php endif; ?>

          <?php if (!empty($personal_settings['personal_title'])): ?>
            <p class="personal-title"><?php echo htmlspecialchars($personal_settings['personal_title']); ?></p>
          <?php endif; ?>

          <?php if (!empty($personal_settings['personal_bio'])): ?>
            <p class="personal-bio"><?php echo htmlspecialchars($personal_settings['personal_bio']); ?></p>
          <?php endif; ?>

          <div class="personal-links">
            <?php if (!empty($personal_settings['personal_email'])): ?>
              <a href="mailto:<?php echo htmlspecialchars($personal_settings['personal_email']); ?>" class="personal-link"
                title="邮箱">
                <i class="fas fa-envelope"></i>
              </a>
            <?php endif; ?>

            <?php if (!empty($personal_settings['personal_github'])): ?>
              <a href="<?php echo htmlspecialchars($personal_settings['personal_github']); ?>" class="personal-link"
                title="GitHub" target="_blank">
                <i class="fab fa-github"></i>
              </a>
            <?php endif; ?>

            <?php if (!empty($personal_settings['personal_weibo'])): ?>
              <a href="<?php echo htmlspecialchars($personal_settings['personal_weibo']); ?>" class="personal-link"
                title="微博" target="_blank">
                <i class="fab fa-weibo"></i>
              </a>
            <?php endif; ?>

            <?php if (!empty($personal_settings['personal_qq'])): ?>
              <a href="tencent://message/?uin=<?php echo htmlspecialchars($personal_settings['personal_qq']); ?>"
                class="personal-link" title="QQ">
                <i class="fab fa-qq"></i>
              </a>
            <?php endif; ?>
          </div>
        </div>
      </div>
    <?php endif; ?>

    <div class="main-content">
      <?php if (!empty($categories)): ?>
        <?php foreach ($categories as $category): ?>
          <?php if (isset($links[$category['id']]) && !empty($links[$category['id']])): ?>
            <div class="category-section">
              <h2 class="category-title"><?php echo htmlspecialchars($category['name']); ?></h2>
              <div class="links-container">
                <?php foreach ($links[$category['id']] as $link): ?>
                  <div class="link-item">
                    <div class="link-content">
                      <div class="link-info">
                        <div class="link-title"><?php echo htmlspecialchars($link['title']); ?></div>
                        <div class="link-url"><?php echo htmlspecialchars($link['url']); ?></div>
                      </div>
                      <div class="link-actions">
                        <a href="<?php echo htmlspecialchars($link['url']); ?>" target="_blank" class="visit-btn">点击进入</a>
                      </div>
                    </div>
                  </div>
                <?php endforeach; ?>
              </div>
            </div>
          <?php endif; ?>
        <?php endforeach; ?>
      <?php else: ?>
        <div class="empty-state">
          <i class="fas fa-inbox"></i>
          <p>暂无分类和链接</p>
          <span>请先到后台添加分类和链接</span>
        </div>
      <?php endif; ?>
    </div>

    <?php if (($personal_settings['show_footer'] ?? '1') === '1'): ?>
      <footer class="footer">
        <div class="footer-content">
          <?php if (!empty($personal_settings['footer_copyright'])): ?>
            <div class="footer-copyright">
              <?php echo htmlspecialchars($personal_settings['footer_copyright']); ?>
            </div>
          <?php endif; ?>

          <div class="footer-links">
            <?php if (!empty($personal_settings['footer_icp'])): ?>
              <span><?php echo htmlspecialchars($personal_settings['footer_icp']); ?></span>
            <?php endif; ?>

            <?php if (!empty($personal_settings['footer_police'])): ?>
              <span><?php echo htmlspecialchars($personal_settings['footer_police']); ?></span>
            <?php endif; ?>
          </div>
        </div>
      </footer>
    <?php endif; ?>
  </div>

  <?php if ($is_admin_logged_in): ?>
    <a href="admin/" class="admin-link">
      <i class="fas fa-cog"></i>
    </a>
    <a href="tempview.php" class="template-switch-link">
      <i class="fas fa-palette"></i>
    </a>
  <?php endif; ?>
</body>

</html>