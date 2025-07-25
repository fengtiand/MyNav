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
  <link rel="stylesheet" href="/templates/minimal/assets/css/style.css">
</head>

<body>
  <div class="container">
    <header class="header">
      <h1 class="site-title"><?php echo htmlspecialchars($site_title); ?></h1>
      <p class="site-description"><?php echo htmlspecialchars($site_description); ?></p>
    </header>

    <?php if (($personal_settings['show_personal_info'] ?? '0') === '1'): ?>
      <section class="profile">
        <div class="profile-content">
          <?php if (!empty($personal_settings['personal_avatar'])): ?>
            <div class="avatar">
              <img src="<?php echo htmlspecialchars($personal_settings['personal_avatar']); ?>"
                alt="<?php echo htmlspecialchars($personal_settings['personal_name'] ?? '头像'); ?>">
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

            <div class="contacts">
              <?php if (!empty($personal_settings['personal_email'])): ?>
                <a href="mailto:<?php echo htmlspecialchars($personal_settings['personal_email']); ?>"
                  class="contact-link">Email</a>
              <?php endif; ?>

              <?php if (!empty($personal_settings['personal_github'])): ?>
                <a href="<?php echo htmlspecialchars($personal_settings['personal_github']); ?>" class="contact-link"
                  target="_blank">GitHub</a>
              <?php endif; ?>

              <?php if (!empty($personal_settings['personal_weibo'])): ?>
                <a href="<?php echo htmlspecialchars($personal_settings['personal_weibo']); ?>" class="contact-link"
                  target="_blank">Weibo</a>
              <?php endif; ?>

              <?php if (!empty($personal_settings['personal_qq'])): ?>
                <a href="tencent://message/?uin=<?php echo htmlspecialchars($personal_settings['personal_qq']); ?>"
                  class="contact-link">QQ</a>
              <?php endif; ?>
            </div>
          </div>
        </div>
      </section>
    <?php endif; ?>

    <main class="main">
      <?php if (!empty($categories)): ?>
        <?php foreach ($categories as $category): ?>
          <?php if (isset($links[$category['id']]) && !empty($links[$category['id']])): ?>
            <section class="category">
              <h3 class="category-title"><?php echo htmlspecialchars($category['name']); ?></h3>
              <div class="links">
                <?php foreach ($links[$category['id']] as $link): ?>
                  <article class="link">
                    <div class="link-content">
                      <h4 class="link-title">
                        <a href="<?php echo htmlspecialchars($link['url']); ?>" target="_blank">
                          <?php echo htmlspecialchars($link['title']); ?>
                        </a>
                      </h4>
                      <p class="link-url"><?php echo htmlspecialchars($link['url']); ?></p>
                      <?php if (!empty($link['description'])): ?>
                        <p class="link-description"><?php echo htmlspecialchars($link['description']); ?></p>
                      <?php endif; ?>
                    </div>
                    <div class="link-actions">
                      <a href="share.php?id=<?php echo $link['id']; ?>" class="action-link">Share</a>
                    </div>
                  </article>
                <?php endforeach; ?>
              </div>
            </section>
          <?php endif; ?>
        <?php endforeach; ?>
      <?php else: ?>
        <section class="empty">
          <p>No links available.</p>
          <p>Please add some categories and links.</p>
        </section>
      <?php endif; ?>
    </main>

    <?php if (($personal_settings['show_footer'] ?? '1') === '1'): ?>
      <footer class="footer">
        <?php if (!empty($personal_settings['footer_copyright'])): ?>
          <p><?php echo htmlspecialchars($personal_settings['footer_copyright']); ?></p>
        <?php endif; ?>

        <?php if (!empty($personal_settings['footer_icp'])): ?>
          <p><?php echo htmlspecialchars($personal_settings['footer_icp']); ?></p>
        <?php endif; ?>

        <?php if (!empty($personal_settings['footer_police'])): ?>
          <p><?php echo htmlspecialchars($personal_settings['footer_police']); ?></p>
        <?php endif; ?>
      </footer>
    <?php endif; ?>

    <?php if ($is_admin_logged_in): ?>
      <div class="admin-tools">
        <a href="admin/" class="admin-link">Admin</a>
        <a href="tempview.php" class="admin-link">Templates</a>
      </div>
    <?php endif; ?>
  </div>
</body>

</html>