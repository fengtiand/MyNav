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
$page_title = '个人信息';
$breadcrumb = [
  ['text' => '首页', 'url' => 'index.php'],
  ['text' => '个人信息']
];
$message = '';
$message_type = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $csrf_token = isset($_POST['csrf_token']) ? $_POST['csrf_token'] : '';

  if (!verify_csrf_token($csrf_token)) {
    $message = '安全验证失败，请刷新页面重试';
    $message_type = 'error';
  } else {
    $personal_info = [];
    if (isset($_POST['form_type']) && $_POST['form_type'] === 'basic') {
      $conn_temp = db_connect();
      $sql_temp = "SELECT value FROM settings WHERE `key` = 'personal_avatar'";
      $result_temp = $conn_temp->query($sql_temp);
      $old_avatar = '';

      if ($result_temp && $row_temp = $result_temp->fetch_assoc()) {
        $old_avatar = $row_temp['value'];
      }
      $conn_temp->close();
      $new_avatar = isset($_POST['personal_avatar']) ? trim($_POST['personal_avatar']) : '';
      if (!empty($old_avatar) && $old_avatar !== $new_avatar && strpos($old_avatar, 'uploads/avatars/') === 0) {
        $old_file_path = '../' . $old_avatar;
        if (file_exists($old_file_path) && is_file($old_file_path)) {
          $old_filename = basename($old_avatar);
          if (strpos($old_filename, 'avatar_') === 0) {
            unlink($old_file_path);
          }
        }
      }

      $personal_info = [
        'personal_name' => isset($_POST['personal_name']) ? trim($_POST['personal_name']) : '',
        'personal_title' => isset($_POST['personal_title']) ? trim($_POST['personal_title']) : '',
        'personal_bio' => isset($_POST['personal_bio']) ? trim($_POST['personal_bio']) : '',
        'personal_avatar' => $new_avatar,
      ];
    } elseif (isset($_POST['form_type']) && $_POST['form_type'] === 'contact') {
      $personal_info = [
        'personal_email' => isset($_POST['personal_email']) ? trim($_POST['personal_email']) : '',
        'personal_github' => isset($_POST['personal_github']) ? trim($_POST['personal_github']) : '',
        'personal_weibo' => isset($_POST['personal_weibo']) ? trim($_POST['personal_weibo']) : '',
        'personal_qq' => isset($_POST['personal_qq']) ? trim($_POST['personal_qq']) : '',
      ];
    } else {
      $personal_info = [
        'personal_name' => isset($_POST['personal_name']) ? trim($_POST['personal_name']) : '',
        'personal_title' => isset($_POST['personal_title']) ? trim($_POST['personal_title']) : '',
        'personal_bio' => isset($_POST['personal_bio']) ? trim($_POST['personal_bio']) : '',
        'personal_avatar' => isset($_POST['personal_avatar']) ? trim($_POST['personal_avatar']) : '',
        'personal_email' => isset($_POST['personal_email']) ? trim($_POST['personal_email']) : '',
        'personal_github' => isset($_POST['personal_github']) ? trim($_POST['personal_github']) : '',
        'personal_weibo' => isset($_POST['personal_weibo']) ? trim($_POST['personal_weibo']) : '',
        'personal_qq' => isset($_POST['personal_qq']) ? trim($_POST['personal_qq']) : '',
      ];
    }

    if (isset($personal_info['personal_name']) && empty($personal_info['personal_name'])) {
      $message = '姓名不能为空';
      $message_type = 'error';
    } else {
      $conn = db_connect();
      $success = true;

      foreach ($personal_info as $key => $value) {
        $sql = "INSERT INTO settings (`key`, value) VALUES (?, ?) ON DUPLICATE KEY UPDATE value = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sss", $key, $value, $value);

        if (!$stmt->execute()) {
          $success = false;
          $message = '保存个人信息失败：' . $conn->error;
          $message_type = 'error';
          break;
        }

        $stmt->close();
      }

      $conn->close();

      if ($success) {
        if (isset($_POST['form_type']) && $_POST['form_type'] === 'basic') {
          $message = '基本信息已成功保存';
        } elseif (isset($_POST['form_type']) && $_POST['form_type'] === 'contact') {
          $message = '联系方式已成功保存';
        } else {
          $message = '个人信息已成功保存';
        }
        $message_type = 'success';
      }
    }
  }
}

$conn = db_connect();
$sql = "SELECT `key`, value FROM settings WHERE `key` LIKE 'personal_%' OR `key` = 'show_personal_info'";
$result = $conn->query($sql);
$personal_settings = [];

if ($result) {
  while ($row = $result->fetch_assoc()) {
    $personal_settings[$row['key']] = $row['value'];
  }
}
$conn->close();
$csrf_token = generate_csrf_token();
include_once __DIR__ . '/views/header.php';
?>

<div class="page-header d-flex justify-between align-center">
  <div>
    <h1>个人信息</h1>
    <p>管理在首页显示的个人信息内容</p>
  </div>
  <div>
    <a href="settings.php" class="btn btn-secondary">
      <i class="fas fa-cog"></i> 系统设置
    </a>
  </div>
</div>

<?php if (!empty($message)): ?>
  <div class="alert alert-<?php echo $message_type; ?>">
    <?php echo xss_clean($message); ?>
  </div>
<?php endif; ?>

<?php if (($personal_settings['show_personal_info'] ?? '0') !== '1'): ?>
  <div class="alert alert-warning">
    <i class="fas fa-info-circle"></i>
    个人信息显示功能已关闭，请到 <a href="settings.php">系统设置</a> 中开启"显示个人信息"选项
  </div>
<?php endif; ?>

<div class="card">
  <div class="card-header">
    <h3 class="card-title">基本信息</h3>
  </div>
  <div class="card-body">
    <form method="post" action="">
      <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
      <input type="hidden" name="form_type" value="basic">

      <div class="row">
        <div class="col-md-6">
          <div class="form-group">
            <label for="personal_name">姓名 *</label>
            <input type="text" id="personal_name" name="personal_name" class="form-control"
              value="<?php echo xss_clean($personal_settings['personal_name'] ?? ''); ?>" required>
            <div class="form-hint">显示的姓名或昵称</div>
          </div>
        </div>
        <div class="col-md-6">
          <div class="form-group">
            <label for="personal_title">职位/标题</label>
            <input type="text" id="personal_title" name="personal_title" class="form-control"
              value="<?php echo xss_clean($personal_settings['personal_title'] ?? ''); ?>">
            <div class="form-hint">例如：前端开发工程师、设计师等</div>
          </div>
        </div>
      </div>

      <div class="form-group">
        <label for="personal_bio">个人简介</label>
        <textarea id="personal_bio" name="personal_bio" class="form-control"
          rows="4"><?php echo xss_clean($personal_settings['personal_bio'] ?? ''); ?></textarea>
        <div class="form-hint">简短的个人介绍，建议控制在100字以内</div>
      </div>

      <div class="form-group">
        <label for="personal_avatar">头像</label>
        <div class="avatar-upload-container">
          <div class="avatar-upload-preview">
            <?php if (!empty($personal_settings['personal_avatar'])): ?>
              <?php
              $avatar_src = $personal_settings['personal_avatar'];
              if (strpos($avatar_src, 'uploads/') === 0) {
                $avatar_src = '../' . $avatar_src;
              }
              ?>
              <img id="avatar-preview" src="<?php echo xss_clean($avatar_src); ?>" alt="头像预览">
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
          <div class="avatar-controls">
            <input type="file" id="avatar-file" name="avatar_file" accept="image/*" style="display: none;">
            <input type="text" id="personal_avatar" name="personal_avatar" class="form-control"
              value="<?php echo xss_clean($personal_settings['personal_avatar'] ?? ''); ?>"
              placeholder="输入头像链接地址或相对路径（如：uploads/avatars/xxx.png）">
            <?php if (!empty($personal_settings['personal_avatar'])): ?>
              <button type="button" id="delete-avatar-btn" class="btn btn-sm btn-danger mt-2">
                <i class="fas fa-trash"></i> 删除头像
              </button>
            <?php endif; ?>
          </div>
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
        <button type="submit" class="btn btn-primary">保存基本信息</button>
      </div>
    </form>
  </div>
</div>

<div class="card mt-4">
  <div class="card-header">
    <h3 class="card-title">联系方式</h3>
  </div>
  <div class="card-body">
    <form method="post" action="">
      <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
      <input type="hidden" name="form_type" value="contact">

      <div class="row">
        <div class="col-md-6">
          <div class="form-group">
            <label for="personal_email">邮箱</label>
            <input type="email" id="personal_email" name="personal_email" class="form-control"
              value="<?php echo xss_clean($personal_settings['personal_email'] ?? ''); ?>">
            <div class="form-hint">联系邮箱地址</div>
          </div>
        </div>
        <div class="col-md-6">
          <div class="form-group">
            <label for="personal_qq">QQ</label>
            <input type="text" id="personal_qq" name="personal_qq" class="form-control"
              value="<?php echo xss_clean($personal_settings['personal_qq'] ?? ''); ?>">
            <div class="form-hint">QQ号码</div>
          </div>
        </div>
      </div>

      <div class="row">
        <div class="col-md-6">
          <div class="form-group">
            <label for="personal_github">GitHub</label>
            <input type="url" id="personal_github" name="personal_github" class="form-control"
              value="<?php echo xss_clean($personal_settings['personal_github'] ?? ''); ?>">
            <div class="form-hint">GitHub主页链接</div>
          </div>
        </div>
        <div class="col-md-6">
          <div class="form-group">
            <label for="personal_weibo">微博</label>
            <input type="url" id="personal_weibo" name="personal_weibo" class="form-control"
              value="<?php echo xss_clean($personal_settings['personal_weibo'] ?? ''); ?>">
            <div class="form-hint">微博主页链接</div>
          </div>
        </div>
      </div>

      <div class="form-actions">
        <button type="submit" class="btn btn-primary">保存联系方式</button>
      </div>
    </form>
  </div>
</div>

<?php if (($personal_settings['show_personal_info'] ?? '0') === '1'): ?>
  <div class="card mt-4">
    <div class="card-header">
      <h3 class="card-title">预览效果</h3>
    </div>
    <div class="card-body">
      <div class="preview-container">
        <div class="personal-info-preview">
          <?php if (!empty($personal_settings['personal_avatar'])): ?>
            <div class="preview-avatar">
              <?php
              $preview_avatar_src = $personal_settings['personal_avatar'];
              if (strpos($preview_avatar_src, 'uploads/') === 0) {
                $preview_avatar_src = '../' . $preview_avatar_src;
              }
              ?>
              <img src="<?php echo xss_clean($preview_avatar_src); ?>"
                alt="<?php echo xss_clean($personal_settings['personal_name'] ?? '头像'); ?>">
            </div>
          <?php endif; ?>

          <div class="preview-content">
            <?php if (!empty($personal_settings['personal_name'])): ?>
              <h4 class="preview-name"><?php echo xss_clean($personal_settings['personal_name']); ?></h4>
            <?php endif; ?>

            <?php if (!empty($personal_settings['personal_title'])): ?>
              <p class="preview-title"><?php echo xss_clean($personal_settings['personal_title']); ?></p>
            <?php endif; ?>

            <?php if (!empty($personal_settings['personal_bio'])): ?>
              <p class="preview-bio"><?php echo xss_clean($personal_settings['personal_bio']); ?></p>
            <?php endif; ?>

            <div class="preview-links">
              <?php if (!empty($personal_settings['personal_email'])): ?>
                <span class="preview-link" title="邮箱">
                  <i class="fas fa-envelope"></i>
                </span>
              <?php endif; ?>

              <?php if (!empty($personal_settings['personal_github'])): ?>
                <span class="preview-link" title="GitHub">
                  <i class="fab fa-github"></i>
                </span>
              <?php endif; ?>

              <?php if (!empty($personal_settings['personal_weibo'])): ?>
                <span class="preview-link" title="微博">
                  <i class="fab fa-weibo"></i>
                </span>
              <?php endif; ?>

              <?php if (!empty($personal_settings['personal_qq'])): ?>
                <span class="preview-link" title="QQ">
                  <i class="fab fa-qq"></i>
                </span>
              <?php endif; ?>
            </div>
          </div>
        </div>
        <div class="preview-hint">
          <i class="fas fa-info-circle"></i> 这是个人信息在首页的显示效果预览
        </div>
      </div>
    </div>
  </div>
<?php endif; ?>


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
    const avatarPreview = document.getElementById('avatar-preview');
    const avatarPlaceholder = document.getElementById('avatar-placeholder');
    const avatarFile = document.getElementById('avatar-file');
    const avatarInput = document.getElementById('personal_avatar');
    const uploadProgress = document.getElementById('upload-progress');
    const avatarUploadPreview = document.querySelector('.avatar-upload-preview');

    if (avatarUploadPreview) {
      avatarUploadPreview.addEventListener('click', function () {
        avatarFile.click();
      });
    }

    if (avatarFile) {
      avatarFile.addEventListener('change', function (e) {
        const file = e.target.files[0];
        if (file) {
          const allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
          if (!allowedTypes.includes(file.type)) {
            alert('请选择有效的图片文件（JPG、PNG、GIF、WebP）');
            return;
          }
          if (file.size > 4 * 1024 * 1024) {
            alert('文件大小不能超过4MB');
            return;
          }

          uploadProgress.style.display = 'block';
          const progressBar = uploadProgress.querySelector('.progress-bar');
          const progressText = uploadProgress.querySelector('.progress-text');
          let progress = 0;
          const interval = setInterval(function () {
            progress += Math.random() * 30;
            if (progress > 90) progress = 90;
            progressBar.style.width = progress + '%';
          }, 100);

          const formData = new FormData();
          formData.append('avatar', file);
          formData.append('csrf_token', '<?php echo $csrf_token; ?>');

          fetch('upload_avatar.php', {
            method: 'POST',
            body: formData
          })
            .then(response => response.json())
            .then(data => {
              clearInterval(interval);
              progressBar.style.width = '100%';
              progressText.textContent = '上传完成';

              setTimeout(function () {
                uploadProgress.style.display = 'none';
                progressBar.style.width = '0%';
                progressText.textContent = '上传中...';
              }, 1000);

              if (data.success) {
                示
                avatarInput.value = data.path;

                if (avatarPreview) {
                  avatarPreview.src = data.url;
                  avatarPreview.style.display = 'block';
                }

                if (avatarPlaceholder) {
                  avatarPlaceholder.style.display = 'none';
                }
              } else {
                alert('上传失败：' + (data.message || '未知错误'));
              }
            })
            .catch(error => {
              clearInterval(interval);
              uploadProgress.style.display = 'none';
              progressBar.style.width = '0%';
              progressText.textContent = '上传中...';
              alert('上传失败：' + error.message);
            });
        }
      });
    }

    if (avatarInput) {
      avatarInput.addEventListener('input', function () {
        const value = this.value.trim();
        const deleteBtn = document.getElementById('delete-avatar-btn');

        if (value) {
          if (avatarPreview) {

            let src = value;
            if (value.startsWith('uploads/')) {
              src = '../' + value;
            }
            avatarPreview.src = src;
            avatarPreview.style.display = 'block';
          }
          if (avatarPlaceholder) {
            avatarPlaceholder.style.display = 'none';
          }

          if (deleteBtn) {
            deleteBtn.style.display = 'block';
          }
        } else {
          if (avatarPreview) {
            avatarPreview.style.display = 'none';
          }
          if (avatarPlaceholder) {
            avatarPlaceholder.style.display = 'flex';
          }

          if (deleteBtn) {
            deleteBtn.style.display = 'none';
          }
        }
      });
    }

    const deleteAvatarBtn = document.getElementById('delete-avatar-btn');
    if (deleteAvatarBtn) {
      deleteAvatarBtn.addEventListener('click', function () {
        if (confirm('确定要删除当前头像吗？')) {
          const formData = new FormData();
          formData.append('csrf_token', '<?php echo $csrf_token; ?>');

          fetch('delete_avatar.php', {
            method: 'POST',
            body: formData
          })
            .then(response => response.json())
            .then(data => {
              if (data.success) {
                if (avatarPreview) {
                  avatarPreview.style.display = 'none';
                }
                if (avatarPlaceholder) {
                  avatarPlaceholder.style.display = 'flex';
                }
                if (avatarInput) {
                  avatarInput.value = '';
                } 钮
                deleteAvatarBtn.style.display = 'none';

                alert('头像删除成功');
              } else {
                alert('删除失败：' + (data.message || '未知错误'));
              }
            })
            .catch(error => {
              alert('删除失败：' + error.message);
            });
        }
      });
    }
  });
</script>

<?php include_once __DIR__ . '/views/footer.php'; ?>