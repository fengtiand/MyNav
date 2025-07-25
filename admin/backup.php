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
$page_title = '备份与恢复';
$breadcrumb = [
  ['text' => '首页', 'url' => 'index.php'],
  ['text' => '备份与恢复']
];
$backup_dir = __DIR__ . '/../database/backups/';
if (!is_dir($backup_dir)) {
  mkdir($backup_dir, 0755, true);
}
if (isset($_POST['action']) && $_POST['action'] == 'delete' && verify_csrf_token($_POST['csrf_token'])) {
  $filename = $_POST['filename'];
  $file_path = $backup_dir . $filename;
  if (basename($filename) === $filename && file_exists($file_path) && pathinfo($filename, PATHINFO_EXTENSION) === 'sql') {
    if (unlink($file_path)) {
      $_SESSION['success_message'] = '备份文件删除成功：' . htmlspecialchars($filename);
    } else {
      $_SESSION['error_message'] = '删除备份文件失败';
    }
  } else {
    $_SESSION['error_message'] = '无效的文件名';
  }
  redirect('backup.php');
}
if (isset($_POST['action']) && $_POST['action'] == 'backup' && verify_csrf_token($_POST['csrf_token'])) {
  $conn = db_connect();
  $tables = [];
  $result = $conn->query("SHOW TABLES");

  while ($row = $result->fetch_row()) {
    $tables[] = $row[0];
  }
  $backup_file = $backup_dir . 'backup_' . date('Y-m-d_H-i-s') . '.sql';
  $handle = fopen($backup_file, 'w');
  foreach ($tables as $table) {
    $result = $conn->query("SHOW CREATE TABLE `$table`");
    $row = $result->fetch_row();
    $create_table_sql = $row[1] . ";\n\n";
    fwrite($handle, $create_table_sql);
    $result = $conn->query("SELECT * FROM `$table`");
    $column_count = $result->field_count;

    while ($row = $result->fetch_row()) {
      $sql = "INSERT INTO `$table` VALUES (";

      for ($i = 0; $i < $column_count; $i++) {
        $row[$i] = $row[$i] === null ? 'NULL' : "'" . $conn->real_escape_string($row[$i]) . "'";
        $sql .= ($i > 0) ? ', ' . $row[$i] : $row[$i];
      }

      $sql .= ");\n";
      fwrite($handle, $sql);
    }

    fwrite($handle, "\n\n");
  }

  fclose($handle);
  $conn->close();
  $_SESSION['success_message'] = '数据备份成功，文件保存在：' . basename($backup_file);
  redirect('backup.php');
}
if (isset($_POST['action']) && $_POST['action'] == 'restore' && verify_csrf_token($_POST['csrf_token'])) {
  if (isset($_FILES['backup_file']) && $_FILES['backup_file']['error'] == 0) {
    $tmp_file = $_FILES['backup_file']['tmp_name'];
    $file_content = file_get_contents($tmp_file);
    $conn = db_connect();
    $conn->query("SET FOREIGN_KEY_CHECKS = 0");
    $statements = explode(';', $file_content);

    foreach ($statements as $statement) {
      $statement = trim($statement);

      if (!empty($statement)) {
        $result = $conn->query($statement);

        if (!$result) {
          $_SESSION['error_message'] = '恢复数据失败：' . $conn->error;
          break;
        }
      }
    }
    $conn->query("SET FOREIGN_KEY_CHECKS = 1");
    $conn->close();

    if (!isset($_SESSION['error_message'])) {
      $_SESSION['success_message'] = '数据恢复成功！';
    }
  } else {
    $_SESSION['error_message'] = '请选择有效的备份文件';
  }
  redirect('backup.php');
}
$backup_files = [];
if (is_dir($backup_dir)) {
  $files = glob($backup_dir . '*.sql');

  foreach ($files as $file) {
    $backup_files[] = [
      'name' => basename($file),
      'size' => filesize($file),
      'time' => filectime($file)
    ];
  }
  usort($backup_files, function ($a, $b) {
    return $b['time'] - $a['time'];
  });
}
require_once __DIR__ . '/views/header.php';
?>

<div class="backup-container">
  <div class="backup-overview">
    <div class="backup-info">
      <h3 class="backup-title">
        <i class="fas fa-database"></i>
        数据备份
      </h3>
      <p class="backup-description">
        定期备份数据库可以保护您的重要数据免受意外丢失。备份文件包含所有表结构和数据，可以在需要时完整恢复。
        建议在进行重要操作前创建备份，确保数据安全。
      </p>
    </div>

    <div class="backup-actions">
      <form action="" method="post">
        <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
        <input type="hidden" name="action" value="backup">
        <button type="submit" class="backup-btn primary">
          <i class="fas fa-plus"></i>
          创建备份
        </button>
      </form>
      <div class="backup-stats">
        当前备份文件：<?php echo count($backup_files); ?> 个
      </div>
    </div>
  </div>
  <div class="restore-section">
    <h3 class="restore-title">
      <i class="fas fa-upload"></i>
      数据恢复
    </h3>

    <form action="" method="post" enctype="multipart/form-data">
      <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
      <input type="hidden" name="action" value="restore">

      <div class="file-input-wrapper">
        <input type="file" class="file-input" id="backup_file" name="backup_file" accept=".sql" required>
      </div>

      <div class="warning-box">
        <div class="warning-title">⚠️ 重要警告</div>
        <div class="warning-text">
          恢复数据将完全覆盖当前数据库中的所有数据，此操作不可逆转。请确保您已经备份了当前的重要数据。
        </div>
      </div>

      <button type="submit" class="restore-btn">
        <i class="fas fa-undo"></i>
        恢复数据
      </button>
    </form>
  </div>

  <div class="files-section">
    <div class="files-header">
      <h3 class="files-title">
        <i class="fas fa-folder"></i>
        备份文件列表
        <?php if (!empty($backup_files)): ?>
          <span class="files-count"><?php echo count($backup_files); ?></span>
        <?php endif; ?>
      </h3>
    </div>

    <?php if (empty($backup_files)): ?>
      <div class="empty-state">
        <div class="empty-icon">
          <i class="fas fa-folder-open"></i>
        </div>
        <p>暂无备份文件</p>
        <p style="font-size: 12px; margin-top: 5px;">点击上方"创建备份"按钮开始备份数据</p>
      </div>
    <?php else: ?>
      <table class="files-table">
        <thead>
          <tr>
            <th>文件名</th>
            <th>大小</th>
            <th>创建时间</th>
            <th>操作</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($backup_files as $file): ?>
            <tr>
              <td>
                <div class="file-name"><?php echo htmlspecialchars($file['name']); ?></div>
              </td>
              <td>
                <div class="file-size"><?php echo round($file['size'] / 1024, 2); ?> KB</div>
              </td>
              <td>
                <div class="file-time"><?php echo date('Y-m-d H:i:s', $file['time']); ?></div>
              </td>
              <td>
                <div class="file-actions">
                  <a href="<?php echo '../database/backups/' . urlencode($file['name']); ?>" class="action-btn btn-download"
                    download>
                    <i class="fas fa-download"></i>
                    下载
                  </a>
                  <button type="button" class="action-btn btn-delete"
                    onclick="showDeleteModal('<?php echo htmlspecialchars($file['name']); ?>')">
                    <i class="fas fa-trash"></i>
                    删除
                  </button>
                </div>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    <?php endif; ?>
  </div>
</div>
<div id="deleteModal" class="delete-modal">
  <div class="modal-content">
    <h4 class="modal-title">
      <i class="fas fa-exclamation-triangle" style="color: #ff9500;"></i>
      确认删除
    </h4>
    <p class="modal-text">
      您确定要删除备份文件 "<span id="deleteFileName"></span>" 吗？
      <br><br>
      此操作不可撤销，删除后将无法恢复该备份文件。
    </p>
    <div class="modal-actions">
      <button type="button" class="modal-btn btn-cancel" onclick="hideDeleteModal()">取消</button>
      <button type="button" class="modal-btn btn-confirm" onclick="confirmDelete()">确认删除</button>
    </div>
  </div>
</div>
<form id="deleteForm" action="" method="post" style="display: none;">
  <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
  <input type="hidden" name="action" value="delete">
  <input type="hidden" name="filename" id="deleteFileInput">
</form>

<script>
  let currentDeleteFile = '';

  function showDeleteModal(filename) {
    currentDeleteFile = filename;
    document.getElementById('deleteFileName').textContent = filename;
    document.getElementById('deleteModal').classList.add('show');
  }

  function hideDeleteModal() {
    document.getElementById('deleteModal').classList.remove('show');
    currentDeleteFile = '';
  }

  function confirmDelete() {
    if (currentDeleteFile) {
      document.getElementById('deleteFileInput').value = currentDeleteFile;
      document.getElementById('deleteForm').submit();
    }
  }

  document.getElementById('deleteModal').addEventListener('click', function (e) {
    if (e.target === this) {
      hideDeleteModal();
    }
  });

  document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape') {
      hideDeleteModal();
    }
  });
</script>

<?php
require_once __DIR__ . '/views/footer.php';
?>