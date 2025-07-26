<?php
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
  <link rel="stylesheet" href="/templates/blue/assets/css/style.css">
</head>

<body>
  <div class="container">
    <header class="header">
      <h1 class="site-title"><?php echo htmlspecialchars($site_title); ?></h1>
      <p class="site-desc"><?php echo htmlspecialchars($site_description); ?></p>

      <div class="search-container">
        <form class="search-form" action="search.php" method="GET"> <input type="text" name="q" class="search-input"
            placeholder="搜索网站..." value="<?php echo isset($_GET['q']) ? htmlspecialchars($_GET['q']) : ''; ?>"> <button
            type="submit" class="search-btn"> <i class="fas fa-search"></i> </button> </form>
      </div>
    </header> <?php if (($personal_settings['show_personal_info'] ?? '0') === '1'): ?>
      <div class="personal-card">
        <div class="personal-header"> <?php if (!empty($personal_settings['personal_avatar'])): ?>
            <div class="personal-avatar"> <img src="<?php echo htmlspecialchars($personal_settings['personal_avatar']); ?>"
                alt="<?php echo htmlspecialchars($personal_settings['personal_name'] ?? '头像'); ?>"> </div> <?php endif; ?>
          <div class="personal-info"> <?php if (!empty($personal_settings['personal_name'])): ?>
              <h3 class="personal-name"><?php echo htmlspecialchars($personal_settings['personal_name']); ?></h3>
            <?php endif; ?>   <?php if (!empty($personal_settings['personal_title'])): ?>
              <p class="personal-title"><?php echo htmlspecialchars($personal_settings['personal_title']); ?></p>
            <?php endif; ?>
          </div>
        </div> <?php if (!empty($personal_settings['personal_bio'])): ?>
          <div class="personal-bio">
            <p><?php echo htmlspecialchars($personal_settings['personal_bio']); ?></p>
          </div> <?php endif; ?>
        <div class="personal-social"> <?php if (!empty($personal_settings['personal_email'])): ?> <a
              href="mailto:<?php echo htmlspecialchars($personal_settings['personal_email']); ?>" class="social-link"
              title="邮箱"> <i class="fas fa-envelope"></i> </a> <?php endif; ?>
          <?php if (!empty($personal_settings['personal_github'])): ?> <a
              href="<?php echo htmlspecialchars($personal_settings['personal_github']); ?>" class="social-link"
              title="GitHub" target="_blank"> <i class="fab fa-github"></i> </a> <?php endif; ?>
          <?php if (!empty($personal_settings['personal_weibo'])): ?> <a
              href="<?php echo htmlspecialchars($personal_settings['personal_weibo']); ?>" class="social-link" title="微博"
              target="_blank"> <i class="fab fa-weibo"></i> </a> <?php endif; ?>
          <?php if (!empty($personal_settings['personal_qq'])): ?> <a
              href="tencent://message/?uin=<?php echo htmlspecialchars($personal_settings['personal_qq']); ?>"
              class="social-link" title="QQ"> <i class="fab fa-qq"></i> </a> <?php endif; ?>
        </div>
      </div> <?php endif; ?>
    <div class="main-content">
      <?php if (!empty($categories)): ?>
        <?php foreach ($categories as $category): ?>
          <?php if (isset($links[$category['id']]) && !empty($links[$category['id']])): ?>
            <div class="category-section">
              <h2 class="category-title">
                <i class="fas fa-folder"></i>
                <?php echo htmlspecialchars($category['name']); ?>
              </h2>
              <div class="links-grid">
                <?php foreach ($links[$category['id']] as $link): ?>
                  <div class="link-card">
                    <div class="link-header">
                      <div class="link-icon">
                        <i class="fas fa-link"></i>
                      </div>
                      <div class="link-actions">
                        <a href="share.php?id=<?php echo $link['id']; ?>" class="action-btn" title="分享">
                          <i class="fas fa-share-alt"></i>
                        </a>
                      </div>
                    </div>
                    <div class="link-body">
                      <h3 class="link-title"><?php echo htmlspecialchars($link['title']); ?></h3>
                      <p class="link-url"><?php echo htmlspecialchars($link['url']); ?></p>
                      <?php if (!empty($link['description'])): ?>
                        <p class="link-desc"><?php echo htmlspecialchars($link['description']); ?></p>
                      <?php endif; ?>
                    </div>
                    <div class="link-footer">
                      <a href="<?php echo htmlspecialchars($link['url']); ?>" target="_blank" class="visit-link">
                        访问网站 <i class="fas fa-external-link-alt"></i>
                      </a>
                    </div>
                  </div>
                <?php endforeach; ?>
              </div>
            </div>
          <?php endif; ?>
        <?php endforeach; ?>
      <?php else: ?>
        <div class="empty-state">
          <div class="empty-icon">
            <i class="fas fa-inbox"></i>
          </div>
          <h3>暂无内容</h3>
          <p>还没有添加任何分类和链接</p>
          <a href="admin/" class="add-btn">去添加</a>
        </div>
      <?php endif; ?>
    </div>

    <?php if (($personal_settings['show_footer'] ?? '1') === '1'): ?>
      <footer class="footer">
        <div class="footer-content">
          <?php if (!empty($personal_settings['footer_copyright'])): ?>
            <p class="copyright"><?php echo htmlspecialchars($personal_settings['footer_copyright']); ?></p>
          <?php endif; ?>

          <div class="footer-info">
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
    <div class="floating-actions">
      <a href="admin/" class="float-btn admin-btn" title="管理后台">
        <i class="fas fa-cog"></i>
      </a>
      <a href="tempview.php" class="float-btn template-btn" title="切换模板">
        <i class="fas fa-palette"></i>
      </a>
    </div>
  <?php endif; ?>
</body>

</html>