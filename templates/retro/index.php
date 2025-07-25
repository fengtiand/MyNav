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
  <link rel="stylesheet" href="/templates/retro/assets/css/style.css">
</head>

<body>
  <div class="desktop">
    <div class="taskbar">
      <div class="start-button" onclick="toggleStartMenu()"> <span class="start-icon">⊞</span> 开始 </div>
      <div class="taskbar-time" id="taskbar-time"></div>
    </div> <!-- 开始菜单 -->
    <div class="start-menu" id="start-menu">
      <div class="start-menu-header"> <span class="start-menu-icon">👤</span> 系统信息 </div>
      <?php if (($personal_settings['show_personal_info'] ?? '0') === '1'): ?>
        <div class="start-menu-content"> <?php if (!empty($personal_settings['personal_avatar'])): ?>
            <div class="start-user-avatar"> <img
                src="<?php echo htmlspecialchars($personal_settings['personal_avatar']); ?>"
                alt="<?php echo htmlspecialchars($personal_settings['personal_name'] ?? '头像'); ?>"> </div> <?php endif; ?>
          <div class="start-user-details"> <?php if (!empty($personal_settings['personal_name'])): ?>
              <div class="start-info-line"> <span class="start-label">姓名:</span> <span
                  class="start-value"><?php echo htmlspecialchars($personal_settings['personal_name']); ?></span> </div>
            <?php endif; ?>   <?php if (!empty($personal_settings['personal_title'])): ?>
              <div class="start-info-line"> <span class="start-label">职位:</span> <span
                  class="start-value"><?php echo htmlspecialchars($personal_settings['personal_title']); ?></span> </div>
            <?php endif; ?>   <?php if (!empty($personal_settings['personal_bio'])): ?>
              <div class="start-info-line"> <span class="start-label">简介:</span> <span
                  class="start-value"><?php echo htmlspecialchars($personal_settings['personal_bio']); ?></span> </div>
            <?php endif; ?>
            <div class="start-contact-buttons"> <?php if (!empty($personal_settings['personal_email'])): ?> <a
                  href="mailto:<?php echo htmlspecialchars($personal_settings['personal_email']); ?>" class="start-btn"> 📧
                  邮箱 </a> <?php endif; ?>   <?php if (!empty($personal_settings['personal_github'])): ?> <a
                  href="<?php echo htmlspecialchars($personal_settings['personal_github']); ?>" class="start-btn"
                  target="_blank"> 💻 GitHub </a> <?php endif; ?>
              <?php if (!empty($personal_settings['personal_weibo'])): ?> <a
                  href="<?php echo htmlspecialchars($personal_settings['personal_weibo']); ?>" class="start-btn"
                  target="_blank"> 📱 微博 </a> <?php endif; ?>   <?php if (!empty($personal_settings['personal_qq'])): ?> <a
                  href="tencent://message/?uin=<?php echo htmlspecialchars($personal_settings['personal_qq']); ?>"
                  class="start-btn"> 💬 QQ </a> <?php endif; ?>
            </div>
          </div> <?php if (($personal_settings['show_footer'] ?? '1') === '1'): ?>
            <div class="start-footer-info">
              <div class="start-footer-header">📋 系统信息</div> <?php if (!empty($personal_settings['footer_copyright'])): ?>
                <div class="start-footer-line"><?php echo htmlspecialchars($personal_settings['footer_copyright']); ?></div>
              <?php endif; ?>     <?php if (!empty($personal_settings['footer_icp'])): ?>
                <div class="start-footer-line"><?php echo htmlspecialchars($personal_settings['footer_icp']); ?></div>
              <?php endif; ?>     <?php if (!empty($personal_settings['footer_police'])): ?>
                <div class="start-footer-line"><?php echo htmlspecialchars($personal_settings['footer_police']); ?></div>
              <?php endif; ?>
            </div> <?php endif; ?>
        </div> <?php else: ?>
        <div class="start-menu-content">
          <div class="start-empty">
            <p>系统信息未配置</p>
            <p>请在后台设置个人信息</p>
          </div> <?php if (($personal_settings['show_footer'] ?? '1') === '1'): ?>
            <div class="start-footer-info">
              <div class="start-footer-header">📋 系统信息</div> <?php if (!empty($personal_settings['footer_copyright'])): ?>
                <div class="start-footer-line"><?php echo htmlspecialchars($personal_settings['footer_copyright']); ?></div>
              <?php endif; ?>     <?php if (!empty($personal_settings['footer_icp'])): ?>
                <div class="start-footer-line"><?php echo htmlspecialchars($personal_settings['footer_icp']); ?></div>
              <?php endif; ?>     <?php if (!empty($personal_settings['footer_police'])): ?>
                <div class="start-footer-line"><?php echo htmlspecialchars($personal_settings['footer_police']); ?></div>
              <?php endif; ?>
            </div> <?php endif; ?>
        </div> <?php endif; ?>
    </div>

    <div class="window main-window">
      <div class="window-header">
        <div class="window-title">
          <span class="window-icon">🌐</span>
          <?php echo htmlspecialchars($site_title); ?>
        </div>
        <div class="window-controls">
          <button class="control-btn minimize">_</button>
          <button class="control-btn maximize">□</button>
          <button class="control-btn close">×</button>
        </div>
      </div>

      <div class="window-content">
        <div class="menu-bar">
          <span class="menu-item">文件(F)</span>
          <span class="menu-item">编辑(E)</span>
          <span class="menu-item">查看(V)</span>
          <span class="menu-item">帮助(H)</span>
        </div>

        <div class="content-area">
          <div class="welcome-section">
            <h1 class="site-title">
              <span class="blinking">█</span> <?php echo htmlspecialchars($site_title); ?>
            </h1>
            <p class="site-desc"><?php echo htmlspecialchars($site_description); ?></p>
          </div>

          <?php if (($personal_settings['show_personal_info'] ?? '0') === '1'): ?>
            <div class="user-info-window desktop-only">
              <div class="info-header"> <span class="info-icon">👤</span> 用户信息 </div>
              <div class="info-content"> <?php if (!empty($personal_settings['personal_avatar'])): ?>
                  <div class="user-avatar"> <img
                      src="<?php echo htmlspecialchars($personal_settings['personal_avatar']); ?>"
                      alt="<?php echo htmlspecialchars($personal_settings['personal_name'] ?? '头像'); ?>"> </div>
                <?php endif; ?>
                <div class="user-details"> <?php if (!empty($personal_settings['personal_name'])): ?>
                    <div class="info-line"> <span class="label">姓名:</span> <span
                        class="value"><?php echo htmlspecialchars($personal_settings['personal_name']); ?></span> </div>
                  <?php endif; ?>   <?php if (!empty($personal_settings['personal_title'])): ?>
                    <div class="info-line"> <span class="label">职位:</span> <span
                        class="value"><?php echo htmlspecialchars($personal_settings['personal_title']); ?></span> </div>
                  <?php endif; ?>   <?php if (!empty($personal_settings['personal_bio'])): ?>
                    <div class="info-line"> <span class="label">简介:</span> <span
                        class="value"><?php echo htmlspecialchars($personal_settings['personal_bio']); ?></span> </div>
                  <?php endif; ?>
                  <div class="contact-buttons"> <?php if (!empty($personal_settings['personal_email'])): ?> <a
                        href="mailto:<?php echo htmlspecialchars($personal_settings['personal_email']); ?>"
                        class="retro-btn"> 📧 邮箱 </a> <?php endif; ?>
                    <?php if (!empty($personal_settings['personal_github'])): ?> <a
                        href="<?php echo htmlspecialchars($personal_settings['personal_github']); ?>" class="retro-btn"
                        target="_blank"> 💻 GitHub </a> <?php endif; ?>
                    <?php if (!empty($personal_settings['personal_weibo'])): ?> <a
                        href="<?php echo htmlspecialchars($personal_settings['personal_weibo']); ?>" class="retro-btn"
                        target="_blank"> 📱 微博 </a> <?php endif; ?>   <?php if (!empty($personal_settings['personal_qq'])): ?>
                      <a href="tencent://message/?uin=<?php echo htmlspecialchars($personal_settings['personal_qq']); ?>"
                        class="retro-btn"> 💬 QQ </a> <?php endif; ?>
                  </div>
                </div>
              </div>
            </div> <?php endif; ?>

          <div class="links-section">
            <?php if (!empty($categories)): ?>
              <?php foreach ($categories as $category): ?>
                <?php if (isset($links[$category['id']]) && !empty($links[$category['id']])): ?>
                  <div class="folder-window">
                    <div class="folder-header">
                      <span class="folder-icon">📁</span>
                      <?php echo htmlspecialchars($category['name']); ?>
                    </div>
                    <div class="folder-content">
                      <?php foreach ($links[$category['id']] as $link): ?>
                        <div class="file-item">
                          <div class="file-icon">🌐</div>
                          <div class="file-info">
                            <div class="file-name"><?php echo htmlspecialchars($link['title']); ?></div>
                            <div class="file-path"><?php echo htmlspecialchars($link['url']); ?></div>
                            <?php if (!empty($link['description'])): ?>
                              <div class="file-desc"><?php echo htmlspecialchars($link['description']); ?></div>
                            <?php endif; ?>
                          </div>
                          <div class="file-actions">
                            <a href="<?php echo htmlspecialchars($link['url']); ?>" target="_blank" class="retro-btn small">
                              打开
                            </a>
                            <a href="share.php?id=<?php echo $link['id']; ?>" class="retro-btn small">
                              分享
                            </a>
                          </div>
                        </div>
                      <?php endforeach; ?>
                    </div>
                  </div>
                <?php endif; ?>
              <?php endforeach; ?>
            <?php else: ?>
              <div class="empty-folder">
                <div class="empty-icon">📂</div>
                <div class="empty-text">
                  <p>文件夹为空</p>
                  <p>请添加一些链接文件</p>
                </div>
              </div>
            <?php endif; ?>
          </div>
        </div>

        <div class="status-bar">
          <span class="status-text">就绪</span>
          <span class="status-info">
            共 <?php echo count($categories ?? []); ?> 个文件夹，
            <?php
            $total_links = 0;
            if (!empty($links)) {
              foreach ($links as $category_links) {
                $total_links += count($category_links);
              }
            }
            echo $total_links;
            ?> 个链接
          </span>
        </div>
      </div>
    </div>

    <?php if (($personal_settings['show_footer'] ?? '1') === '1'): ?>
      <div class="footer-window desktop-only">
        <div class="window-header">
          <div class="window-title"> <span class="window-icon">ℹ️</span> 系统信息 </div>
        </div>
        <div class="window-content"> <?php if (!empty($personal_settings['footer_copyright'])): ?>
            <div class="footer-line"><?php echo htmlspecialchars($personal_settings['footer_copyright']); ?></div>
          <?php endif; ?>   <?php if (!empty($personal_settings['footer_icp'])): ?>
            <div class="footer-line"><?php echo htmlspecialchars($personal_settings['footer_icp']); ?></div> <?php endif; ?>
          <?php if (!empty($personal_settings['footer_police'])): ?>
            <div class="footer-line"><?php echo htmlspecialchars($personal_settings['footer_police']); ?></div>
          <?php endif; ?>
        </div>
      </div> <?php endif; ?>

    <?php if ($is_admin_logged_in): ?>
      <div class="desktop-icons">
        <div class="desktop-icon">
          <a href="admin/" class="icon-link">
            <div class="icon-image">⚙️</div>
            <div class="icon-label">控制面板</div>
          </a>
        </div>
        <div class="desktop-icon">
          <a href="tempview.php" class="icon-link">
            <div class="icon-image">🎨</div>
            <div class="icon-label">主题切换</div>
          </a>
        </div>
      </div>
    <?php endif; ?>
  </div>

  <script>    function updateTime() { const now = new Date(); const timeString = now.toLocaleTimeString('zh-CN', { hour12: false, hour: '2-digit', minute: '2-digit' }); document.getElementById('taskbar-time').textContent = timeString; } function toggleStartMenu() { const startMenu = document.getElementById('start-menu'); const startButton = document.querySelector('.start-button'); if (startMenu.classList.contains('show')) { startMenu.classList.remove('show'); startButton.style.border = '1px outset var(--retro-gray)'; } else { startMenu.classList.add('show'); startButton.style.border = '1px inset var(--retro-gray)'; } }    // 点击其他地方关闭开始菜单    document.addEventListener('click', function(e) {      const startMenu = document.getElementById('start-menu');      const startButton = document.querySelector('.start-button');            if (!startButton.contains(e.target) && !startMenu.contains(e.target)) {        startMenu.classList.remove('show');        startButton.style.border = '1px outset var(--retro-gray)';      }    });    updateTime();    setInterval(updateTime, 1000);    document.querySelectorAll('.control-btn').forEach(btn => {      btn.addEventListener('click', function (e) {        e.preventDefault();        if (this.classList.contains('close')) {          alert('无法关闭主窗口！');        } else if (this.classList.contains('minimize')) {          alert('最小化功能暂不可用');        } else if (this.classList.contains('maximize')) {          alert('窗口已最大化');        }      });    });  </script>
</body>

</html>