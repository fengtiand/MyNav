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
require_once __DIR__ . '/includes/template.php';
$page_title = '模板管理';
$breadcrumb = [
  ['text' => '首页', 'url' => 'index.php'],
  ['text' => '模板管理']
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

  $csrf_token = isset($_POST['csrf_token']) ? $_POST['csrf_token'] : '';

  if (!verify_csrf_token($csrf_token)) {
    $message = '安全验证失败，请刷新页面重试';
    $message_type = 'error';
  } else {
    $action = isset($_POST['action']) ? $_POST['action'] : '';
    if ($action === 'set_current') {
      $template_folder = isset($_POST['template_folder']) ? $_POST['template_folder'] : '';

      if (!empty($template_folder)) {
        if (set_current_template($template_folder)) {
          $message = '模板切换成功';
          $message_type = 'success';
        } else {
          $message = '模板切换失败，请重试';
          $message_type = 'error';
        }
      }
    } elseif ($action === 'sync_templates') {
      if (sync_templates_to_database()) {
        $message = '模板同步成功';
        $message_type = 'success';
      } else {
        $message = '模板同步失败，请重试';
        $message_type = 'error';
      }
    }
  }
}

$current_template = get_current_template();
$available_templates = get_available_templates();
$csrf_token = generate_csrf_token();
include_once __DIR__ . '/views/header.php';
?>

<div class="page-header d-flex justify-between align-center">
  <div>
    <h1>模板管理</h1>
    <p>管理前台首页的显示模板</p>
  </div>
  <div>
    <form method="post" style="display: inline;">
      <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
      <input type="hidden" name="action" value="sync_templates">

    </form>
    <a href="../tempview.php" class="btn btn-primary" target="_blank">
      <i class="fas fa-eye"></i> 预览模板
    </a>
  </div>
</div>

<?php if (isset($message)): ?>
  <div class="alert alert-<?php echo $message_type; ?>">
    <?php echo xss_clean($message); ?>
  </div>
<?php endif; ?>
<div class="card">
  <div class="card-header">
    <h3 class="card-title">可用模板</h3>
    <p class="card-subtitle">当前共有 <?php echo count($available_templates); ?> 个模板</p>
  </div>
  <div class="card-body">
    <?php if (empty($available_templates)): ?>
      <div class="empty-data">
        <i class="fas fa-palette" style="font-size: 48px; margin-bottom: 20px; opacity: 0.3;"></i>
        <p>暂无可用模板</p>
        <p style="font-size: 14px; opacity: 0.7; margin-top: 10px;">请确保templates目录下有正确的模板文件</p>
      </div>
    <?php else: ?>
      <div class="templates-grid">
        <?php foreach ($available_templates as $folder => $template): ?>
          <div class="template-card <?php echo $folder === $current_template ? 'current' : ''; ?>">
            <div class="template-preview">
              <?php
              $preview_file = $template['preview'] ?? 'preview.jpg';
              $preview_path = "../templates/{$folder}/{$preview_file}";
              $file_check_path = __DIR__ . "/../templates/{$folder}/{$preview_file}";

              if (file_exists($file_check_path)):
                ?>
                <img src="<?php echo $preview_path; ?>" alt="<?php echo xss_clean($template['name']); ?> 预览图"
                  style="width: 100%; height: 100%; object-fit: cover; object-position: center;"
                  onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                <div class="preview-fallback" style="display: none;">
                  <i class="fas fa-palette"></i>
                </div>
              <?php else: ?>
                <i class="fas fa-palette"></i>
              <?php endif; ?>
            </div>
            <div class="template-info">
              <div class="template-header">
                <h4 class="template-name"><?php echo xss_clean($template['name']); ?></h4>
                <?php if ($folder === $current_template): ?>
                  <span class="current-badge">当前使用</span>
                <?php endif; ?>
              </div>

              <div class="template-meta">
                <div class="meta-item">
                  <i class="fas fa-user"></i>
                  <span><?php echo xss_clean($template['author']); ?></span>
                </div>
                <div class="meta-item">
                  <i class="fas fa-tag"></i>
                  <span>v<?php echo xss_clean($template['version']); ?></span>
                </div>
                <div class="meta-item">
                  <i class="fas fa-folder"></i>
                  <span><?php echo xss_clean($folder); ?></span>
                </div>
              </div>

              <div class="template-description">
                <?php echo xss_clean($template['description']); ?>
              </div>

              <div class="template-actions">
                <a href="../index.php?template=<?php echo $folder; ?>" class="btn btn-sm btn-outline" target="_blank">
                  <i class="fas fa-eye"></i> 预览
                </a>
                <?php if ($folder !== $current_template): ?>
                  <form method="post" style="display: inline;">
                    <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                    <input type="hidden" name="action" value="set_current">
                    <input type="hidden" name="template_folder" value="<?php echo $folder; ?>">
                    <button type="submit" class="btn btn-sm btn-primary">
                      <i class="fas fa-check"></i> 使用
                    </button>
                  </form>
                <?php endif; ?>
              </div>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </div>
</div>

<div class="card">
  <div class="card-header">
    <h3 class="card-title">模板开发说明</h3>
  </div>
  <div class="card-body">
    <div class="dev-guide">
      <h4>如何创建新模板？</h4>
      <ol>
        <li>在 <code>templates</code> 目录下创建新的文件夹，如 <code>my-template</code></li>
        <li>在文件夹中创建 <code>index.php</code> 文件作为模板主文件</li>
        <li>创建 <code>template.json</code> 配置文件，包含以下信息：
          <pre><code>{
  "name": "模板名称",
  "author": "作者名称", 
  "description": "模板描述",
  "version": "1.0.0",
  "preview": "preview.jpg",
  "created_at": "2024-01-01",
  "updated_at": "2024-01-01"
}</code></pre>
        </li>
        <li>点击"同步模板"按钮将新模板加载到系统中</li>
      </ol>

      <h4>模板文件结构</h4>
      <ul>
        <li><code>index.php</code> - 模板主文件，包含HTML和CSS</li>
        <li><code>template.json</code> - 模板配置信息</li>
        <li><code>preview.jpg</code> - 模板预览图（可选）</li>
      </ul>

      <h4>可用变量</h4>
      <p>在模板的 <code>index.php</code> 文件中，可以使用以下PHP变量：</p>
      <ul>
        <li><code>$site_title</code> - 网站标题</li>
        <li><code>$site_description</code> - 网站描述</li>
        <li><code>$categories</code> - 分类数组</li>
        <li><code>$links</code> - 链接数组（按分类ID分组）</li>
      </ul>
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
    const switchForms = document.querySelectorAll('form[action=""] input[name="action"][value="set_current"]');
    switchForms.forEach(function (input) {
      const form = input.closest('form');
      form.addEventListener('submit', function (e) {
        const templateName = form.querySelector('input[name="template_folder"]').value;
        if (!confirm('确定要切换到 "' + templateName + '" 模板吗？')) {
          e.preventDefault();
        }
      });
    });
  });
</script>

<?php
include_once __DIR__ . '/views/footer.php';
?>