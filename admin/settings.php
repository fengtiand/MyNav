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
$page_title = '系统设置';
$breadcrumb = [
  ['text' => '首页', 'url' => 'index.php'],
  ['text' => '系统设置']
];
$message = '';
$message_type = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $csrf_token = isset($_POST['csrf_token']) ? $_POST['csrf_token'] : '';
  if (!verify_csrf_token($csrf_token)) {
    $message = '安全验证失败，请刷新页面重试';
    $message_type = 'error';
  } else {
    $settings = [];
    if (isset($_POST['form_type']) && $_POST['form_type'] === 'basic') {
      $settings = [
        'site_name' => isset($_POST['site_name']) ? trim($_POST['site_name']) : '',
        'site_description' => isset($_POST['site_description']) ? trim($_POST['site_description']) : '',
        'site_keywords' => isset($_POST['site_keywords']) ? trim($_POST['site_keywords']) : '',
        'show_personal_info' => isset($_POST['show_personal_info']) ? '1' : '0',
      ];
    } elseif (isset($_POST['form_type']) && $_POST['form_type'] === 'footer') {
      $settings = [
        'footer_copyright' => isset($_POST['footer_copyright']) ? trim($_POST['footer_copyright']) : '',
        'footer_icp' => isset($_POST['footer_icp']) ? trim($_POST['footer_icp']) : '',
        'footer_police' => isset($_POST['footer_police']) ? trim($_POST['footer_police']) : '',
        'footer_custom_html' => isset($_POST['footer_custom_html']) ? trim($_POST['footer_custom_html']) : '',
        'show_footer' => isset($_POST['show_footer']) ? '1' : '0',
      ];
    } else {
      $settings = [
        'site_name' => isset($_POST['site_name']) ? trim($_POST['site_name']) : '',
        'site_description' => isset($_POST['site_description']) ? trim($_POST['site_description']) : '',
        'site_keywords' => isset($_POST['site_keywords']) ? trim($_POST['site_keywords']) : '',
        'footer_copyright' => isset($_POST['footer_copyright']) ? trim($_POST['footer_copyright']) : '',
        'footer_icp' => isset($_POST['footer_icp']) ? trim($_POST['footer_icp']) : '',
        'footer_police' => isset($_POST['footer_police']) ? trim($_POST['footer_police']) : '',
        'footer_custom_html' => isset($_POST['footer_custom_html']) ? trim($_POST['footer_custom_html']) : '',
        'show_footer' => isset($_POST['show_footer']) ? '1' : '0',
        'show_personal_info' => isset($_POST['show_personal_info']) ? '1' : '0',
      ];
    }
    if (isset($settings['site_name']) && empty($settings['site_name'])) {
      $message = '网站名称不能为空';
      $message_type = 'error';
    } else {
      $conn = db_connect();
      $success = true;

      foreach ($settings as $key => $value) {
        $sql = "INSERT INTO settings (`key`, value) VALUES (?, ?) ON DUPLICATE KEY UPDATE value = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sss", $key, $value, $value);

        if (!$stmt->execute()) {
          $success = false;
          $message = '保存设置失败：' . $conn->error;
          $message_type = 'error';
          break;
        }

        $stmt->close();
      }

      $conn->close();

      if ($success) {
        if (isset($_POST['form_type']) && $_POST['form_type'] === 'basic') {
          $message = '基本设置已成功保存';
        } elseif (isset($_POST['form_type']) && $_POST['form_type'] === 'footer') {
          $message = '底部设置已成功保存';
        } else {
          $message = '设置已成功保存';
        }
        $message_type = 'success';
      }
    }
  }
}

$conn = db_connect();
$sql = "SELECT * FROM settings";
$result = $conn->query($sql);
$settings = [];

if ($result) {
  while ($row = $result->fetch_assoc()) {
    $settings[$row['key']] = $row['value'];
  }
}

$sql = "SELECT * FROM templates ORDER BY is_default DESC, name ASC";
$result = $conn->query($sql);
$templates = [];
if ($result) {
  while ($row = $result->fetch_assoc()) {
    $templates[] = $row;
  }
}

$conn->close();

$csrf_token = generate_csrf_token();

include_once __DIR__ . '/views/header.php';
?>

<div class="page-header">
  <h1>系统设置</h1>
  <p>配置网站的基本信息和外观设置</p>
</div>

<?php if (!empty($message)): ?>
  <div class="alert alert-<?php echo $message_type; ?>">
    <?php echo xss_clean($message); ?>
  </div>
<?php endif; ?>

<div class="card">
  <div class="card-header">
    <h3 class="card-title">基本设置</h3>
  </div>
  <div class="card-body">
    <form method="post" action="">
      <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
      <input type="hidden" name="form_type" value="basic">

      <div class="form-group">
        <label for="site_name">网站名称</label>
        <input type="text" id="site_name" name="site_name" class="form-control"
          value="<?php echo xss_clean($settings['site_name'] ?? ''); ?>" required>
        <div class="form-hint">显示在浏览器标题栏和首页</div>
      </div>

      <div class="form-group">
        <label for="site_description">网站描述</label>
        <textarea id="site_description" name="site_description" class="form-control"
          rows="2"><?php echo xss_clean($settings['site_description'] ?? ''); ?></textarea>
        <div class="form-hint">用于SEO优化，向搜索引擎描述网站内容</div>
      </div>

      <div class="form-group">
        <label for="site_keywords">网站关键词</label>
        <input type="text" id="site_keywords" name="site_keywords" class="form-control"
          value="<?php echo xss_clean($settings['site_keywords'] ?? ''); ?>">
        <div class="form-hint">用于SEO优化，多个关键词用英文逗号分隔</div>
      </div>

      <div class="form-group">
        <div class="form-check">
          <input type="checkbox" id="show_personal_info" name="show_personal_info" class="form-check-input"
            <?php echo ($settings['show_personal_info'] ?? '0') === '1' ? 'checked' : ''; ?>>
          <label for="show_personal_info" class="form-check-label">显示个人信息</label>
        </div>
        <div class="form-hint">开启后将在首页显示个人信息卡片，<a href="profile.php">点击这里编辑个人信息</a></div>
      </div>

      <div class="form-actions">
        <button type="submit" class="btn btn-primary">保存基本设置</button>
      </div>
    </form>
  </div>
</div>

<div class="card mt-4">
  <div class="card-header">
    <h3 class="card-title">底部信息设置</h3>
  </div>
  <div class="card-body">
    <form method="post" action="">
      <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
      <input type="hidden" name="form_type" value="footer">

      <div class="form-group">
        <div class="form-check">
          <input type="checkbox" id="show_footer" name="show_footer" class="form-check-input"
            <?php echo ($settings['show_footer'] ?? '1') === '1' ? 'checked' : ''; ?>>
          <label for="show_footer" class="form-check-label">显示底部信息</label>
        </div>
        <div class="form-hint">开启后将在网站底部显示底部信息</div>
      </div>

      <div class="form-group">
        <label for="footer_copyright">版权信息</label>
        <input type="text" id="footer_copyright" name="footer_copyright" class="form-control"
          value="<?php echo xss_clean($settings['footer_copyright'] ?? ''); ?>" 
          placeholder="© 2025 MyNav个人导航系统. All rights reserved.">
        <div class="form-hint">显示在网站底部的版权信息</div>
      </div>

      <div class="form-group">
        <label for="footer_icp">ICP备案号</label>
        <input type="text" id="footer_icp" name="footer_icp" class="form-control"
          value="<?php echo xss_clean($settings['footer_icp'] ?? ''); ?>" 
          placeholder="京ICP备12345678号">
        <div class="form-hint">网站ICP备案信息</div>
      </div>

      <div class="form-group">
        <label for="footer_police">公安备案号</label>
        <input type="text" id="footer_police" name="footer_police" class="form-control"
          value="<?php echo xss_clean($settings['footer_police'] ?? ''); ?>" 
          placeholder="京公网安备 11010802012345号">
        <div class="form-hint">网站公安备案信息</div>
      </div>

      <div class="form-group">
        <label for="footer_custom_html">自定义HTML代码</label>
        <textarea id="footer_custom_html" name="footer_custom_html" class="form-control"
          rows="3" placeholder="可以添加统计代码、友情链接等HTML内容"><?php echo xss_clean($settings['footer_custom_html'] ?? ''); ?></textarea>
        <div class="form-hint">可以添加统计代码、友情链接等自定义HTML内容</div>
      </div>

      <div class="form-actions">
        <button type="submit" class="btn btn-primary">保存底部设置</button>
      </div>
    </form>
  </div>
</div>

<div class="card mt-4">
  <div class="card-header">
    <h3 class="card-title">模板设置</h3>
  </div>
  <div class="card-body">
    <div class="row">
      <?php foreach ($templates as $template): ?>
        <div class="col-md-4 col-sm-6 mb-4">
          <div class="template-card <?php echo $template['is_default'] ? 'active' : ''; ?>">
            <div class="template-name"><?php echo htmlspecialchars($template['name']); ?></div>
            <div class="template-description"><?php echo htmlspecialchars($template['description']); ?></div>

            <?php if (!$template['is_default']): ?>
              <form method="post" action="templates.php" class="mt-2">
                <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                <input type="hidden" name="action" value="set_default">
                <input type="hidden" name="template_id" value="<?php echo $template['id']; ?>">
                <button type="submit" class="btn btn-sm btn-outline-primary">设为默认</button>
              </form>
            <?php else: ?>
              <span class="badge badge-success">当前默认</span>
            <?php endif; ?>

            <a href="templates.php?edit=<?php echo $template['id']; ?>" class="btn btn-sm btn-link">编辑</a>
          </div>
        </div>
      <?php endforeach; ?>
    </div>

    <div class="mt-3">
      <a href="templates.php" class="btn btn-secondary">管理全部模板</a>
    </div>
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

    const checkboxes = document.querySelectorAll('.form-check');
    checkboxes.forEach(function(checkContainer) {
      checkContainer.addEventListener('click', function(e) {
        if (e.target.tagName !== 'INPUT') {
          const checkbox = this.querySelector('.form-check-input');
          if (checkbox) {
            checkbox.checked = !checkbox.checked;
          }
        }
      });
    });
  });
</script>

<?php include_once __DIR__ . '/views/footer.php'; ?>