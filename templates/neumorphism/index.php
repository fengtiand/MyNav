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
  <link rel="stylesheet" href="/templates/neumorphism/assets/css/style.css">
</head>

<body>
  <div class="container">
    <header class="header">
      <div class="header-content">
        <h1 class="site-title"><?php echo htmlspecialchars($site_title); ?></h1>
        <p class="site-description"><?php echo htmlspecialchars($site_description); ?></p>
      </div>
    </header>

    <?php if (($personal_settings['show_personal_info'] ?? '0') === '1'): ?>
      <section class="profile-card">
        <div class="profile-content">
          <?php if (!empty($personal_settings['personal_avatar'])): ?>
            <div class="avatar-container">
              <div class="avatar">
                <img src="<?php echo htmlspecialchars($personal_settings['personal_avatar']); ?>"
                  alt="<?php echo htmlspecialchars($personal_settings['personal_name'] ?? '头像'); ?>">
              </div>
            </div>
          <?php endif; ?>

          <div class="profile-info">
            <?php if (!empty($personal_settings['personal_name'])): ?>
              <h2 class="name"><?php echo htmlspecialchars($personal_settings['personal_name']); ?></h2>
            <?php endif; ?>

            <?php if (!empty($personal_settings['personal_title'])): ?>
              <p class="title"><?php echo htmlspecialchars($personal_settings['personal_title']); ?></p>
            <?php endif; ?>

            <?php if (!empty($personal_settings['personal_bio'])): ?>
              <p class="bio"><?php echo htmlspecialchars($personal_settings['personal_bio']); ?></p>
            <?php endif; ?>

            <div class="social-links">
              <?php if (!empty($personal_settings['personal_email'])): ?>
                <a href="mailto:<?php echo htmlspecialchars($personal_settings['personal_email']); ?>" class="social-btn">
                  <i class="fas fa-envelope"></i>
                </a>
              <?php endif; ?>

              <?php if (!empty($personal_settings['personal_github'])): ?>
                <a href="<?php echo htmlspecialchars($personal_settings['personal_github']); ?>" class="social-btn"
                  target="_blank">
                  <i class="fab fa-github"></i>
                </a>
              <?php endif; ?>

              <?php if (!empty($personal_settings['personal_weibo'])): ?>
                <a href="<?php echo htmlspecialchars($personal_settings['personal_weibo']); ?>" class="social-btn"
                  target="_blank">
                  <i class="fab fa-weibo"></i>
                </a>
              <?php endif; ?>

              <?php if (!empty($personal_settings['personal_qq'])): ?>
                <a href="tencent://message/?uin=<?php echo htmlspecialchars($personal_settings['personal_qq']); ?>"
                  class="social-btn">
                  <i class="fab fa-qq"></i>
                </a>
              <?php endif; ?>
            </div>
          </div>
        </div>
      </section>
    <?php endif; ?>

    <main class="main-content">
      <?php if (!empty($categories)): ?>
        <?php foreach ($categories as $category): ?>
          <?php if (isset($links[$category['id']]) && !empty($links[$category['id']])): ?>
            <section class="category-section">
              <div class="category-header">
                <h3 class="category-title"><?php echo htmlspecialchars($category['name']); ?></h3>
              </div>
              <div class="links-grid">
                <?php foreach ($links[$category['id']] as $link): ?>
                  <article class="link-card">
                    <div class="link-content">
                      <div class="link-icon">
                        <i class="fas fa-link"></i>
                      </div>
                      <h4 class="link-title"><?php echo htmlspecialchars($link['title']); ?></h4>
                      <?php if (!empty($link['description'])): ?>
                        <p class="link-description"><?php echo htmlspecialchars($link['description']); ?></p>
                      <?php endif; ?>
                      <div class="link-url"><?php echo htmlspecialchars($link['url']); ?></div>
                    </div>
                    <div class="link-actions">
                      <a href="<?php echo htmlspecialchars($link['url']); ?>" target="_blank" class="action-btn primary">
                        <i class="fas fa-external-link-alt"></i>
                        访问
                      </a>
                      <a href="share.php?id=<?php echo $link['id']; ?>" class="action-btn secondary">
                        <i class="fas fa-share-alt"></i>
                        分享
                      </a>
                    </div>
                  </article>
                <?php endforeach; ?>
              </div>
            </section>
          <?php endif; ?>
        <?php endforeach; ?>
      <?php else: ?>
        <section class="empty-state">
          <div class="empty-card">
            <div class="empty-icon">
              <i class="fas fa-folder-open"></i>
            </div>
            <h3>暂无链接</h3>
            <p>请添加一些分类和链接来开始使用</p>
          </div>
        </section>
      <?php endif; ?>
    </main>

    <?php if (($personal_settings['show_footer'] ?? '1') === '1'): ?>
      <footer class="footer">
        <div class="footer-content">
          <?php if (!empty($personal_settings['footer_copyright'])): ?>
            <p><?php echo htmlspecialchars($personal_settings['footer_copyright']); ?></p>
          <?php endif; ?>

          <?php if (!empty($personal_settings['footer_icp'])): ?>
            <p><?php echo htmlspecialchars($personal_settings['footer_icp']); ?></p>
          <?php endif; ?>

          <?php if (!empty($personal_settings['footer_police'])): ?>
            <p><?php echo htmlspecialchars($personal_settings['footer_police']); ?></p>
          <?php endif; ?>
        </div>
      </footer>
    <?php endif; ?>

    <?php if ($is_admin_logged_in): ?>
      <div class="admin-panel">
        <a href="admin/" class="admin-btn">
          <i class="fas fa-cog"></i>
          <span>管理</span>
        </a>
        <a href="tempview.php" class="admin-btn">
          <i class="fas fa-palette"></i>
          <span>主题</span>
        </a>
      </div>
    <?php endif; ?>
  </div>

  <script>
    document.addEventListener('DOMContentLoaded', function () {
      const cards = document.querySelectorAll('.link-card, .profile-card, .category-header');

      cards.forEach(card => {
        card.addEventListener('mouseenter', function () {
          this.style.transform = 'translateY(-2px)';
        });

        card.addEventListener('mouseleave', function () {
          this.style.transform = 'translateY(0)';
        });
      });

      const buttons = document.querySelectorAll('.action-btn, .social-btn, .admin-btn');

      buttons.forEach(btn => {
        btn.addEventListener('mousedown', function () {
          this.style.transform = 'scale(0.95)';
        });

        btn.addEventListener('mouseup', function () {
          this.style.transform = 'scale(1)';
        });

        btn.addEventListener('mouseleave', function () {
          this.style.transform = 'scale(1)';
        });
      });
    });
  </script>
</body>

</html>