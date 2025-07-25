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
$page_title = '个人资料';
$breadcrumb = [
  ['text' => '首页', 'url' => 'index.php'],
  ['text' => '个人资料']
];
$message = '';
$message_type = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $csrf_token = isset($_POST['csrf_token']) ? $_POST['csrf_token'] : '';

  if (!verify_csrf_token($csrf_token)) {
    $message = '安全验证失败，请刷新页面重试';
    $message_type = 'error';
  } else {
    $nickname = isset($_POST['nickname']) ? trim($_POST['nickname']) : '';
    $avatar = isset($_POST['avatar']) ? trim($_POST['avatar']) : '';
    if (empty($nickname)) {
      $message = '昵称不能为空';
      $message_type = 'error';
    } else {
      $admin_id = $current_admin['id'];
      $sql = "UPDATE admin_users SET nickname = ?, avatar = ? WHERE id = ?";
      $result = db_execute($sql, [$nickname, $avatar, $admin_id]);

      if ($result['affected_rows'] > 0) {
        $message = '个人资料更新成功';
        $message_type = 'success';
        $_SESSION['admin']['nickname'] = $nickname;
        $_SESSION['admin']['avatar'] = $avatar;
        $current_admin['nickname'] = $nickname;
        $current_admin['avatar'] = $avatar;
      } else {
        $message = '个人资料更新失败，请重试';
        $message_type = 'error';
      }
    }
  }
}
$csrf_token = generate_csrf_token();
include_once __DIR__ . '/views/header.php';
?>

<div class="page-header">
  <h1>个人资料</h1>
  <p>管理您的个人信息和头像</p>
</div>

<?php if (!empty($message)): ?>
  <div class="alert alert-<?php echo $message_type; ?>">
    <?php echo xss_clean($message); ?>
  </div>
<?php endif; ?>

<div class="card">
  <div class="card-header">
    <h3 class="card-title">基本信息</h3>
  </div>
  <div class="card-body">
    <form method="post" action="">
      <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">

      <div class="form-group">
        <label for="username">用户名</label>
        <input type="text" id="username" class="form-control"
          value="<?php echo xss_clean($current_admin['username']); ?>" readonly>
        <div class="form-hint">用户名不可修改</div>
      </div>

      <div class="form-group">
        <label for="nickname">昵称</label>
        <input type="text" id="nickname" name="nickname" class="form-control"
          value="<?php echo xss_clean($current_admin['nickname'] ?? ''); ?>" required>
        <div class="form-hint">显示在后台界面的昵称</div>
      </div>

      <div class="form-group">
        <label for="avatar">头像</label>
        <div class="avatar-upload-container">
          <div class="avatar-upload-preview">
            <?php if (!empty($current_admin['avatar'])): ?>
              <?php
              $admin_avatar_src = $current_admin['avatar'];
              if (strpos($admin_avatar_src, 'uploads/') === 0) {
                $admin_avatar_src = '../' . $admin_avatar_src;
              }
              ?>
              <img id="avatar-preview" src="<?php echo xss_clean($admin_avatar_src); ?>" alt="头像预览">
            <?php else: ?>
              <div id="avatar-placeholder" class="avatar-placeholder">
                <i class="fas fa-user"></i>
                <span>点击上传头像</span>
              </div>
            <?php endif; ?>
            <div class="avatar-upload-overlay">
              <i class="fas fa-camera"></i>
              <span>更换头像</span>
            </div>
          </div>
          <input type="file" id="avatar-file" name="avatar_file" accept="image/*" style="display: none;">
          <input type="text" id="avatar" name="avatar" class="form-control mt-2"
            value="<?php echo xss_clean($current_admin['avatar'] ?? ''); ?>"
            placeholder="输入头像链接地址或相对路径（如：uploads/avatars/xxx.png）">
        </div>
        <div class="form-hint">
          支持 JPG、PNG、GIF、WebP 格式，文件大小不超过2MB，建议使用正方形图片。也可以直接输入相对路径或完整URL链接
        </div>
        <div id="upload-progress" class="upload-progress" style="display: none;">
          <div class="progress-bar"></div>
          <span class="progress-text">上传中...</span>
        </div>
      </div>

      <div class="form-actions">
        <button type="submit" class="btn btn-primary">保存修改</button>
        <a href="change_password.php" class="btn btn-secondary">修改密码</a>
      </div>
    </form>
  </div>
</div>

<script>
  document.addEventListener('DOMContentLoaded', function () {
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
    const avatarPreview = document.querySelector('.avatar-upload-preview');
    const avatarFile = document.getElementById('avatar-file');
    const avatarInput = document.getElementById('avatar');
    const avatarImg = document.getElementById('avatar-preview');
    const avatarPlaceholder = document.getElementById('avatar-placeholder');
    const uploadProgress = document.getElementById('upload-progress');
    const progressBar = document.querySelector('.progress-bar');
    const progressText = document.querySelector('.progress-text');
    if (avatarPreview) {
      avatarPreview.addEventListener('click', function () {
        avatarFile.click();
      });
    }
    if (avatarFile) {
      avatarFile.addEventListener('change', function (e) {
        const file = e.target.files[0];
        if (file) {
          const allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
          if (!allowedTypes.includes(file.type)) {
            alert('只支持 JPG、PNG、GIF、WebP 格式的图片');
            return;
          }

          const maxSize = 4 * 1024 * 1024;
          if (file.size > maxSize) {
            alert('文件大小不能超过4MB');
            return;
          }
          const reader = new FileReader();
          reader.onload = function (e) {
            updateAvatarPreview(e.target.result);
          };
          reader.readAsDataURL(file);
          uploadAvatar(file);
        }
      });
    }

    if (avatarInput) {
      avatarInput.addEventListener('input', function () {
        const url = this.value.trim();
        if (url) {
          updateAvatarPreview(url);
        }
      });

      avatarInput.addEventListener('blur', function () {
        const url = this.value.trim();
        if (url && !isValidAvatarUrl(url)) {
          showMessage('请输入有效的图片链接或相对路径', 'error');
        }
      });
    }

    function isValidAvatarUrl(url) {
      if (url.startsWith('uploads/')) {
        return true;
      }

      try {
        const urlObj = new URL(url);
        return urlObj.protocol === 'http:' || urlObj.protocol === 'https:';
      } catch (e) {
        return false;
      }
    }

    function updateAvatarPreview(src) {
      let imageSrc = src;
      if (src && src.startsWith('uploads/')) {
        imageSrc = '../' + src;
      }

      if (avatarImg) {
        avatarImg.src = imageSrc;
        avatarImg.style.display = 'block';
      } else {
        const img = document.createElement('img');
        img.id = 'avatar-preview';
        img.src = imageSrc;
        img.alt = '头像预览';
        avatarPreview.insertBefore(img, avatarPreview.firstChild);
      }

      if (avatarPlaceholder) {
        avatarPlaceholder.style.display = 'none';
      }
    }
    function uploadAvatar(file) {
      const formData = new FormData();
      formData.append('avatar', file);
      formData.append('csrf_token', document.querySelector('input[name="csrf_token"]').value);
      uploadProgress.style.display = 'block';
      progressBar.style.width = '0%';
      progressText.textContent = '上传中...';
      let progress = 0;
      const progressInterval = setInterval(function () {
        progress += Math.random() * 30;
        if (progress > 90) progress = 90;
        progressBar.style.width = progress + '%';
      }, 200);
      fetch('upload_avatar.php', {
        method: 'POST',
        body: formData
      })
        .then(response => response.json())
        .then(data => {
          clearInterval(progressInterval);
          progressBar.style.width = '100%';

          if (data.success) {
            progressText.textContent = '上传成功！';
            avatarInput.value = data.url;
            avatarInput.dispatchEvent(new Event('input'));
            autoSaveAvatar(data.url);

            setTimeout(function () {
              uploadProgress.style.display = 'none';
            }, 1000);

            showMessage('头像上传成功，正在保存...', 'success');
          } else {
            progressText.textContent = '上传失败：' + data.message;
            showMessage('上传失败：' + data.message, 'error');

            setTimeout(function () {
              uploadProgress.style.display = 'none';
            }, 3000);
          }
        })
        .catch(error => {
          clearInterval(progressInterval);
          progressText.textContent = '上传失败，请重试';
          showMessage('上传失败，请重试', 'error');

          setTimeout(function () {
            uploadProgress.style.display = 'none';
          }, 3000);
        });
    }

    function autoSaveAvatar(avatarUrl) {

      const formData = new FormData();
      formData.append('csrf_token', document.querySelector('input[name="csrf_token"]').value);
      formData.append('nickname', document.getElementById('nickname').value);
      formData.append('avatar', avatarUrl);

      fetch('admin_profile.php', {
        method: 'POST',
        body: formData
      })
        .then(response => response.text())
        .then(html => {
          if (html.includes('个人资料更新成功') || html.includes('alert-success')) {
            showMessage('头像上传并保存成功！', 'success');

            const headerAvatar = document.querySelector('.admin-avatar img');
            if (headerAvatar) {
              const headerImageSrc = avatarUrl.startsWith('uploads/') ? '../' + avatarUrl : avatarUrl;
              headerAvatar.src = headerImageSrc;
            }
          } else if (html.includes('alert-error')) {
            showMessage('头像上传成功，但保存失败，请手动保存', 'error');
          } else {
            showMessage('头像上传成功，请手动保存', 'info');
          }
        })
        .catch(error => {
          showMessage('头像上传成功，但自动保存失败，请手动保存', 'error');
        });
    }

    function showMessage(message, type) {
      const existingAlert = document.querySelector('.alert');
      if (existingAlert) {
        existingAlert.remove();
      }
      const alert = document.createElement('div');
      alert.className = 'alert alert-' + type;
      alert.textContent = message;

      const pageHeader = document.querySelector('.page-header');
      pageHeader.parentNode.insertBefore(alert, pageHeader.nextSibling);

      setTimeout(function () {
        alert.style.opacity = '0';
        alert.style.transition = 'opacity 0.5s';
        setTimeout(function () {
          alert.remove();
        }, 500);
      }, 3000);
    }
  });
</script>

<?php

include_once __DIR__ . '/views/footer.php';
?>