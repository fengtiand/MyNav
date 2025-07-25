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
require_once __DIR__ . '/config/database.php';
$link_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($link_id <= 0) {
  header('Location: index.php');
  exit;
}
$conn = db_connect();
$sql = "SELECT l.*, c.name as category_name 
        FROM links l 
        LEFT JOIN categories c ON l.category_id = c.id 
        WHERE l.id = ? AND l.status = 1";
$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $link_id);
$stmt->execute();
$result = $stmt->get_result();
$link = $result->fetch_assoc();
$stmt->close();
if (!$link) {
  header('Location: index.php');
  exit;
}
$sql = "UPDATE links SET visits = visits + 1 WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $link_id);
$stmt->execute();
$stmt->close();
$today = date('Y-m-d');
$sql = "INSERT INTO visits (link_id, visits, visit_date) 
        VALUES (?, 1, ?) 
        ON DUPLICATE KEY UPDATE visits = visits + 1";
$stmt = $conn->prepare($sql);
$stmt->bind_param('is', $link_id, $today);
$stmt->execute();
$stmt->close();
$conn->close();
$current_url = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
$site_title = $link['title'] . " - 链接分享";
$site_description = $link['description'] ?: $link['title'];
?>
<!DOCTYPE html>
<html lang="zh-CN">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?php echo htmlspecialchars($site_title); ?></title>
  <meta name="description" content="<?php echo htmlspecialchars($site_description); ?>">
  <meta property="og:title" content="<?php echo htmlspecialchars($link['title']); ?>">
  <meta property="og:description" content="<?php echo htmlspecialchars($site_description); ?>">
  <meta property="og:url" content="<?php echo htmlspecialchars($current_url); ?>">
  <link rel="stylesheet" href="https://cdn.bootcdn.net/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link rel="stylesheet" href="assets/css/share.css">
</head>

<body>
  <div class="container">
    <header>
      <h1>链接分享</h1>
      <p class="header-subtitle">发现并分享优质链接</p>
    </header>
    <main>
      <div class="link-card">
        <div class="link-header">
          <div class="link-icon">
            <i class="fas fa-link"></i>
          </div>
          <div class="link-info">
            <div class="link-title"><?php echo htmlspecialchars($link['title']); ?></div>
            <div class="link-url"><?php echo htmlspecialchars($link['url']); ?></div>
          </div>
        </div>
        <div class="link-meta">
          <div class="link-meta-item">
            <i class="fas fa-folder"></i>
            <span><?php echo htmlspecialchars($link['category_name']); ?></span>
          </div>
          <div class="link-meta-item">
            <i class="fas fa-eye"></i>
            <span><?php echo $link['visits']; ?> 次浏览</span>
          </div>
          <div class="link-meta-item">
            <i class="fas fa-clock"></i>
            <span><?php echo date('Y-m-d', strtotime($link['created_at'] ?? 'now')); ?></span>
          </div>
        </div>
        <?php if (!empty($link['description'])): ?>
          <div class="link-description">
            <i class="fas fa-quote-left" style="opacity: 0.5; margin-right: 8px;"></i>
            <?php echo htmlspecialchars($link['description']); ?>
          </div>
        <?php endif; ?>
        <div class="action-buttons">
          <a href="<?php echo htmlspecialchars($link['url']); ?>" class="visit-btn" target="_blank">
            <i class="fas fa-external-link-alt"></i>
            <span>访问链接</span>
          </a>
          <button onclick="copyLink()" class="copy-btn">
            <i class="fas fa-copy"></i>
            <span>复制链接</span>
          </button>
        </div>
        <div class="share-section">
          <div class="share-title">
            <i class="fas fa-share-alt" style="margin-right: 8px;"></i>
            分享到社交平台
          </div>
          <div class="share-buttons">
            <a href="https://connect.qq.com/widget/shareqq/index.html?url=<?php echo urlencode($current_url); ?>&title=<?php echo urlencode($link['title']); ?>&desc=<?php echo urlencode($site_description); ?>"
              class="share-button qq" target="_blank" title="分享到QQ">
              <i class="fab fa-qq"></i>
            </a>
            <a href="https://service.weibo.com/share/share.php?url=<?php echo urlencode($current_url); ?>&title=<?php echo urlencode($link['title'] . ' - ' . $site_description); ?>"
              class="share-button weibo" target="_blank" title="分享到微博">
              <i class="fab fa-weibo"></i>
            </a>
            <a href="javascript:void(0);" onclick="shareToWechat()" class="share-button wechat" title="微信分享">
              <i class="fab fa-weixin"></i>
            </a>
            <a href="javascript:void(0);" onclick="copyShareText()" class="share-button" title="复制分享文本">
              <i class="fas fa-clipboard"></i>
            </a>
          </div>
        </div>
      </div>
      <div class="back-section">
        <a href="index.php" class="back-link">
          <i class="fas fa-arrow-left"></i>
          <span>返回首页</span>
        </a>
      </div>
    </main>
  </div>
  <div id="toast" class="toast"></div>

  <footer>
    <div class="container">
      <p>Copyright © <?php echo date('Y'); ?> MyNav All Rights Reserved.</p>
    </div>
  </footer>

  <script>
    function showToast(message, type = 'success') {
      const toast = document.getElementById('toast');
      toast.textContent = message;
      toast.className = `toast ${type}`;
      toast.classList.add('show');
      setTimeout(() => {
        toast.classList.remove('show');
      }, 3000);
    }

    async function copyLink() {
      const linkToCopy = window.location.href;
      try {
        if (navigator.clipboard && window.isSecureContext) {
          await navigator.clipboard.writeText(linkToCopy);
        } else {
          const tempInput = document.createElement('input');
          tempInput.value = linkToCopy;
          document.body.appendChild(tempInput);
          tempInput.select();
          document.execCommand('copy');
          document.body.removeChild(tempInput);
        }
        showToast('链接已复制到剪贴板！');
      } catch (err) {
        showToast('复制失败，请手动复制', 'error');
        console.error('复制失败:', err);
      }
    }

    async function copyShareText() {
      const title = '<?php echo addslashes($link['title']); ?>';
      const url = window.location.href;
      const description = '<?php echo addslashes($link['description'] ?? ''); ?>';

      const shareText = `${title}\n${description ? description + '\n' : ''}${url}`;
      try {
        if (navigator.clipboard && window.isSecureContext) {
          await navigator.clipboard.writeText(shareText);
        } else {
          const tempInput = document.createElement('textarea');
          tempInput.value = shareText;
          document.body.appendChild(tempInput);
          tempInput.select();
          document.execCommand('copy');
          document.body.removeChild(tempInput);
        }
        showToast('分享文本已复制！');
      } catch (err) {
        showToast('复制失败，请手动复制', 'error');
      }
    }

    function shareToWechat() {
      const url = window.location.href;
      const qrUrl = `https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=${encodeURIComponent(url)}`;

      const modal = document.createElement('div');
      modal.style.cssText = `
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(0, 0, 0, 0.8);
        display: flex;
        align-items: center;
        justify-content: center;
        z-index: 10000;
        backdrop-filter: blur(10px);
      `;

      const content = document.createElement('div');
      content.style.cssText = `
        background: white;
        padding: 30px;
        border-radius: 16px;
        text-align: center;
        max-width: 300px;
        box-shadow: 0 20px 40px rgba(0, 0, 0, 0.3);
      `;

      content.innerHTML = `
        <h3 style="margin-bottom: 20px; color: #333;">微信扫码分享</h3>
        <img src="${qrUrl}" alt="二维码" style="width: 200px; height: 200px; border-radius: 8px;">
        <p style="margin: 15px 0; color: #666; font-size: 14px;">使用微信扫描二维码分享</p>
        <button onclick="this.closest('.modal').remove()" style="
          background: #07c160;
          color: white;
          border: none;
          padding: 10px 20px;
          border-radius: 8px;
          cursor: pointer;
          font-size: 14px;
        ">关闭</button>
      `;

      modal.className = 'modal';
      modal.appendChild(content);
      document.body.appendChild(modal);

      modal.addEventListener('click', (e) => {
        if (e.target === modal) {
          modal.remove();
        }
      });
    }

    document.addEventListener('DOMContentLoaded', function () {
      document.addEventListener('keydown', function (e) {
        if ((e.ctrlKey || e.metaKey) && e.key === 'c' && !e.target.matches('input, textarea')) {
          e.preventDefault();
          copyLink();
        }

        if (e.key === 'Escape') {
          const modal = document.querySelector('.modal');
          if (modal) {
            modal.remove();
          }
        }
      });

      const buttons = document.querySelectorAll('.visit-btn, .copy-btn, .share-button, .back-link');
      buttons.forEach(button => {
        button.addEventListener('touchstart', function () {
          this.style.transform = 'scale(0.95)';
        });

        button.addEventListener('touchend', function () {
          setTimeout(() => {
            this.style.transform = '';
          }, 150);
        });
      });

      const qrUrl = `https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=${encodeURIComponent(window.location.href)}`;
      const img = new Image();
      img.src = qrUrl;
    });

    document.addEventListener('visibilitychange', function () {
      if (document.visibilityState === 'visible') {
        console.log('页面重新可见');
      }
    });
  </script>
</body>

</html>