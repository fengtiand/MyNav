<?php
/**
 * Modern 模板 - 现代简约风格
 */

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
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
  <link rel="stylesheet" href="/templates/modern/assets/css/style.css">
</head>

<body>
  <div class="container">
    <header>
      <div class="header-content">
        <h1><?php echo htmlspecialchars($site_title); ?></h1>
        <p><?php echo htmlspecialchars($site_description); ?></p>

        <div class="search-box">
          <form class="search-form" action="search.php" method="GET">
            <input type="text" name="q" class="search-input" placeholder="搜索你需要的网站..."
              value="<?php echo isset($_GET['q']) ? htmlspecialchars($_GET['q']) : ''; ?>">
            <button type="submit" class="search-button">
              <i class="fas fa-search"></i>
            </button>
          </form>
        </div>
      </div>
    </header>

    <?php if (($personal_settings['show_personal_info'] ?? '0') === '1'): ?>
      <div class="personal-info-card">
        <?php if (!empty($personal_settings['personal_avatar'])): ?>
          <div class="personal-avatar">
            <img src="<?php echo htmlspecialchars($personal_settings['personal_avatar']); ?>"
              alt="<?php echo htmlspecialchars($personal_settings['personal_name'] ?? '头像'); ?>">
          </div>
        <?php endif; ?>

        <div class="personal-content">
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

    <main>
      <?php if (!empty($categories)): ?>
        <?php foreach ($categories as $category): ?>
          <?php if (isset($links[$category['id']]) && !empty($links[$category['id']])): ?>
            <section class="category">
              <div class="category-header">
                <h2 class="category-title">
                  <i class="fas fa-folder-open" style="margin-right: 12px; color: var(--primary-color);"></i>
                  <?php echo htmlspecialchars($category['name']); ?>
                </h2>
              </div>
              <div class="links-grid">
                <?php foreach ($links[$category['id']] as $link): ?>
                  <div class="link-card">
                    <div class="link-actions">
                      <a href="share.php?id=<?php echo $link['id']; ?>" class="link-share" title="分享链接">
                        <i class="fas fa-share-alt"></i>
                      </a>
                    </div>
                    <a href="<?php echo htmlspecialchars($link['url']); ?>" target="_blank" class="link-main">
                      <div class="link-title"><?php echo htmlspecialchars($link['title']); ?></div>
                      <div class="link-url"><?php echo htmlspecialchars($link['url']); ?></div>
                      <?php if (!empty($link['description'])): ?>
                        <div class="link-description"><?php echo htmlspecialchars($link['description']); ?></div>
                      <?php endif; ?>
                    </a>
                  </div>
                <?php endforeach; ?>
              </div>
            </section>
          <?php endif; ?>
        <?php endforeach; ?>
      <?php else: ?>
        <div class="empty-state">
          <i class="fas fa-rocket"></i>
          <h3>开始你的导航之旅</h3>
          <p>还没有添加任何分类和链接，去后台创建你的第一个分类吧！</p>
        </div>
      <?php endif; ?>
    </main>
  </div>

  <!-- 底部信息 -->
  <?php if (($personal_settings['show_footer'] ?? '1') === '1'): ?>
    <footer class="site-footer">
      <div class="footer-content">
        <?php if (!empty($personal_settings['footer_copyright'])): ?>
          <div class="footer-copyright">
            <?php echo htmlspecialchars($personal_settings['footer_copyright']); ?>
          </div>
        <?php endif; ?>

        <div class="footer-links">
          <?php if (!empty($personal_settings['footer_icp'])): ?>
            <span class="footer-link"><?php echo htmlspecialchars($personal_settings['footer_icp']); ?></span>
          <?php endif; ?>

          <?php if (!empty($personal_settings['footer_police'])): ?>
            <span class="footer-link"><?php echo htmlspecialchars($personal_settings['footer_police']); ?></span>
          <?php endif; ?>

          <?php if (!empty($personal_settings['footer_statistics'])): ?>
            <span class="footer-link"><?php echo htmlspecialchars($personal_settings['footer_statistics']); ?></span>
          <?php endif; ?>
        </div>

        <?php if (!empty($personal_settings['footer_custom_html'])): ?>
          <div class="footer-custom">
            <?php echo $personal_settings['footer_custom_html']; ?>
          </div>
        <?php endif; ?>
      </div>
    </footer>
  <?php endif; ?>

  <?php if ($is_admin_logged_in): ?>
    <a href="admin/" class="admin-link" title="管理后台">
      <i class="fas fa-cog"></i>
    </a>

    <a href="tempview.php" class="template-switch-link" title="切换模板">
      <i class="fas fa-palette"></i>
    </a>
  <?php endif; ?>
</body>

</html>