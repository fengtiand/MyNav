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
$page_title = '系统日志';
$breadcrumb = [
  ['text' => '首页', 'url' => 'index.php'],
  ['text' => '系统日志']
];
if (isset($_GET['action']) && $_GET['action'] === 'clear' && isset($_GET['type'])) {
  $csrf_token = isset($_GET['token']) ? $_GET['token'] : '';
  $clear_type = $_GET['type'];

  if (!verify_csrf_token($csrf_token)) {
    $message = '安全验证失败，请刷新页面重试';
    $message_type = 'error';
  } else {
    $conn = db_connect();

    if ($clear_type === 'all') {
      $sql = "DELETE FROM admin_login_logs";
      $stmt = $conn->prepare($sql);

      if ($stmt->execute()) {
        $message = '所有日志已成功清除';
        $message_type = 'success';
      } else {
        $message = '清除日志失败：' . $conn->error;
        $message_type = 'error';
      }

      $stmt->close();
    } elseif ($clear_type === 'old') {
      $sql = "DELETE FROM admin_login_logs WHERE login_time < DATE_SUB(NOW(), INTERVAL 30 DAY)";
      $stmt = $conn->prepare($sql);

      if ($stmt->execute()) {
        $affected_rows = $stmt->affected_rows;
        $message = "已清除 {$affected_rows} 条30天前的日志";
        $message_type = 'success';
      } else {
        $message = '清除日志失败：' . $conn->error;
        $message_type = 'error';
      }

      $stmt->close();
    } elseif ($clear_type === 'failed') {
      $sql = "DELETE FROM admin_login_logs WHERE login_status = 0";
      $stmt = $conn->prepare($sql);

      if ($stmt->execute()) {
        $affected_rows = $stmt->affected_rows;
        $message = "已清除 {$affected_rows} 条失败登录日志";
        $message_type = 'success';
      } else {
        $message = '清除日志失败：' . $conn->error;
        $message_type = 'error';
      }

      $stmt->close();
    }

    $conn->close();
  }
}
$page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
$per_page = 15;
$offset = ($page - 1) * $per_page;
$status = isset($_GET['status']) && in_array($_GET['status'], ['0', '1']) ? $_GET['status'] : '';
$username = isset($_GET['username']) ? trim($_GET['username']) : '';
$date_from = isset($_GET['date_from']) ? trim($_GET['date_from']) : '';
$date_to = isset($_GET['date_to']) ? trim($_GET['date_to']) : '';
$where_clauses = [];
$params = [];
$param_types = "";
if ($status !== '') {
  $where_clauses[] = "l.login_status = ?";
  $params[] = $status;
  $param_types .= "i";
}
if (!empty($username)) {
  $where_clauses[] = "(a.username LIKE ? OR a.nickname LIKE ?)";
  $params[] = "%$username%";
  $params[] = "%$username%";
  $param_types .= "ss";
}
if (!empty($date_from)) {
  $where_clauses[] = "l.login_time >= ?";
  $params[] = $date_from . " 00:00:00";
  $param_types .= "s";
}
if (!empty($date_to)) {
  $where_clauses[] = "l.login_time <= ?";
  $params[] = $date_to . " 23:59:59";
  $param_types .= "s";
}
$where_sql = !empty($where_clauses) ? "WHERE " . implode(" AND ", $where_clauses) : "";
$conn = db_connect();
$count_sql = "SELECT COUNT(*) AS total FROM admin_login_logs l 
              LEFT JOIN admin_users a ON l.admin_id = a.id 
              $where_sql";

if (!empty($params)) {
  $stmt = $conn->prepare($count_sql);
  $stmt->bind_param($param_types, ...$params);
  $stmt->execute();
  $result = $stmt->get_result();
  $total = $result->fetch_assoc()['total'];
  $stmt->close();
} else {
  $result = $conn->query($count_sql);
  $total = $result->fetch_assoc()['total'];
}
$total_pages = ceil($total / $per_page);
if ($page > $total_pages && $total_pages > 0) {
  $page = $total_pages;
  $offset = ($page - 1) * $per_page;
}
$logs = [];
$sql = "SELECT l.*, a.username, a.nickname 
        FROM admin_login_logs l 
        LEFT JOIN admin_users a ON l.admin_id = a.id 
        $where_sql 
        ORDER BY l.login_time DESC 
        LIMIT ?, ?";

$params[] = $offset;
$params[] = $per_page;
$param_types .= "ii";
$stmt = $conn->prepare($sql);
$stmt->bind_param($param_types, ...$params);
$stmt->execute();
$result = $stmt->get_result();

while ($row = $result->fetch_assoc()) {
  $logs[] = $row;
}
$stmt->close();
$conn->close();
$csrf_token = generate_csrf_token();
include_once __DIR__ . '/views/header.php';
?>

<div class="page-header d-flex justify-between align-center">
  <div>
    <h1>系统日志</h1>
    <p>查看管理员登录记录</p>
  </div>
  <div>
    <div class="btn-group">
      <button type="button" class="btn btn-secondary dropdown-toggle" id="clearLogsBtn">
        <i class="fas fa-trash-alt"></i> 清除日志
      </button>
      <div class="dropdown-menu" id="clearDropdown" style="display: none;">
        <a href="javascript:void(0)" class="dropdown-item" onclick="confirmClear('old')">
          <i class="fas fa-calendar-minus"></i> 清除30天前日志
        </a>
        <a href="javascript:void(0)" class="dropdown-item" onclick="confirmClear('failed')">
          <i class="fas fa-times-circle"></i> 清除失败日志
        </a>
        <div class="dropdown-divider"></div>
        <a href="javascript:void(0)" class="dropdown-item text-danger" onclick="confirmClear('all')">
          <i class="fas fa-trash"></i> 清除所有日志
        </a>
      </div>
    </div>
  </div>
</div>

<?php if (isset($message)): ?>
  <div class="alert alert-<?php echo $message_type; ?>">
    <i class="fas fa-<?php echo $message_type === 'success' ? 'check-circle' : 'exclamation-circle'; ?>"></i>
    <?php echo xss_clean($message); ?>
  </div>
<?php endif; ?>

<div class="card mb-20">
  <div class="card-header">
    <h3 class="card-title">筛选条件</h3>
    <button type="button" class="btn-collapse" id="toggleFilter">
      <i class="fas fa-chevron-down"></i>
    </button>
  </div>
  <div class="card-body" id="filterBody">
    <form method="get" action="" class="filter-form">
      <div class="filter-row">
        <div class="filter-item">
          <label for="username">用户</label>
          <input type="text" id="username" name="username" class="form-control"
            value="<?php echo xss_clean($username); ?>" placeholder="用户名/昵称">
        </div>

        <div class="filter-item">
          <label for="status">状态</label>
          <select id="status" name="status" class="form-control">
            <option value="">全部</option>
            <option value="1" <?php echo $status === '1' ? 'selected' : ''; ?>>成功</option>
            <option value="0" <?php echo $status === '0' ? 'selected' : ''; ?>>失败</option>
          </select>
        </div>

        <div class="filter-item">
          <label for="date_from">开始日期</label>
          <input type="date" id="date_from" name="date_from" class="form-control"
            value="<?php echo xss_clean($date_from); ?>">
        </div>

        <div class="filter-item">
          <label for="date_to">结束日期</label>
          <input type="date" id="date_to" name="date_to" class="form-control"
            value="<?php echo xss_clean($date_to); ?>">
        </div>
      </div>

      <div class="filter-actions">
        <button type="submit" class="btn btn-primary">查询</button>
        <a href="logs.php" class="btn btn-secondary">重置</a>
      </div>
    </form>
  </div>
</div>
<div class="card">
  <div class="card-header">
    <h3 class="card-title">登录日志列表</h3>
    <div class="card-tools">
      共 <?php echo $total; ?> 条记录
    </div>
  </div>
  <div class="card-body">
    <?php if (empty($logs)): ?>
      <div class="empty-data">暂无日志记录</div>
    <?php else: ?>
      <div class="table-responsive">
        <table class="data-table">
          <thead>
            <tr>
              <th>ID</th>
              <th>用户</th>
              <th>登录IP</th>
              <th>登录时间</th>
              <th>状态</th>
              <th>信息</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($logs as $log): ?>
              <?php
              $status_class = $log['login_status'] ? 'success' : 'error';
              $status_text = $log['login_status'] ? '成功' : '失败';
              $display_name = !empty($log['nickname']) ? $log['nickname'] : $log['username'];
              ?>
              <tr>
                <td><?php echo $log['id']; ?></td>
                <td><?php echo xss_clean($display_name); ?></td>
                <td><?php echo xss_clean($log['login_ip']); ?></td>
                <td><?php echo date('Y-m-d H:i:s', strtotime($log['login_time'])); ?></td>
                <td><span class="badge <?php echo $status_class; ?>"><?php echo $status_text; ?></span></td>
                <td><?php echo xss_clean($log['login_message']); ?></td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
      <?php if ($total_pages > 1): ?>
        <div class="pagination">
          <?php if ($page > 1): ?>
            <a href="?page=1<?php echo !empty($status) ? '&status=' . $status : ''; ?><?php echo !empty($username) ? '&username=' . urlencode($username) : ''; ?><?php echo !empty($date_from) ? '&date_from=' . urlencode($date_from) : ''; ?><?php echo !empty($date_to) ? '&date_to=' . urlencode($date_to) : ''; ?>"
              class="page-link">&laquo;</a>
            <a href="?page=<?php echo $page - 1; ?><?php echo !empty($status) ? '&status=' . $status : ''; ?><?php echo !empty($username) ? '&username=' . urlencode($username) : ''; ?><?php echo !empty($date_from) ? '&date_from=' . urlencode($date_from) : ''; ?><?php echo !empty($date_to) ? '&date_to=' . urlencode($date_to) : ''; ?>"
              class="page-link">&lsaquo;</a>
          <?php endif; ?>

          <?php
          $start_page = max(1, $page - 2);
          $end_page = min($total_pages, $page + 2);

          for ($i = $start_page; $i <= $end_page; $i++):
            ?>
            <a href="?page=<?php echo $i; ?><?php echo !empty($status) ? '&status=' . $status : ''; ?><?php echo !empty($username) ? '&username=' . urlencode($username) : ''; ?><?php echo !empty($date_from) ? '&date_from=' . urlencode($date_from) : ''; ?><?php echo !empty($date_to) ? '&date_to=' . urlencode($date_to) : ''; ?>"
              class="page-link <?php echo $i == $page ? 'active' : ''; ?>"><?php echo $i; ?></a>
          <?php endfor; ?>

          <?php if ($page < $total_pages): ?>
            <a href="?page=<?php echo $page + 1; ?><?php echo !empty($status) ? '&status=' . $status : ''; ?><?php echo !empty($username) ? '&username=' . urlencode($username) : ''; ?><?php echo !empty($date_from) ? '&date_from=' . urlencode($date_from) : ''; ?><?php echo !empty($date_to) ? '&date_to=' . urlencode($date_to) : ''; ?>"
              class="page-link">&rsaquo;</a>
            <a href="?page=<?php echo $total_pages; ?><?php echo !empty($status) ? '&status=' . $status : ''; ?><?php echo !empty($username) ? '&username=' . urlencode($username) : ''; ?><?php echo !empty($date_from) ? '&date_from=' . urlencode($date_from) : ''; ?><?php echo !empty($date_to) ? '&date_to=' . urlencode($date_to) : ''; ?>"
              class="page-link">&raquo;</a>
          <?php endif; ?>
        </div>
      <?php endif; ?>
    <?php endif; ?>
  </div>
</div>

<div class="modal" id="confirmClearModal">
  <div class="modal-dialog" style="max-width: 400px;">
    <div class="modal-content">
      <div class="modal-header">
        <h3 class="modal-title">
          <i class="fas fa-exclamation-triangle" style="color: #ff3b30;"></i> 确认清除
        </h3>
        <button type="button" class="close-modal"><i class="fas fa-times"></i></button>
      </div>
      <div class="modal-body">
        <p id="clearConfirmText">确定要执行此操作吗？</p>
        <p style="opacity: 0.7; font-size: 14px;">此操作不可恢复，请谨慎操作。</p>
      </div>
      <div class="form-actions text-right">
        <button type="button" class="btn btn-secondary" id="cancelClear">
          <i class="fas fa-times"></i> 取消
        </button>
        <button type="button" class="btn btn-delete" id="confirmClearAction">
          <i class="fas fa-trash-alt"></i> 确认清除
        </button>
      </div>
    </div>
  </div>
</div>

<style>

</style>

<script>
  document.addEventListener('DOMContentLoaded', function () {
    const isMobile = window.innerWidth < 768;
    const toggleFilterBtn = document.getElementById('toggleFilter');
    const filterBody = document.getElementById('filterBody');
    if (isMobile) {
      filterBody.style.display = 'none';
      toggleFilterBtn.classList.add('collapsed');
    }

    toggleFilterBtn.addEventListener('click', function () {
      if (filterBody.style.display === 'none') {
        filterBody.style.display = 'block';
        toggleFilterBtn.classList.remove('collapsed');
      } else {
        filterBody.style.display = 'none';
        toggleFilterBtn.classList.add('collapsed');
      }
    });
    const clearLogsBtn = document.getElementById('clearLogsBtn');
    const clearDropdown = document.getElementById('clearDropdown');

    clearLogsBtn.addEventListener('click', function (e) {
      e.stopPropagation();
      if (clearDropdown.style.display === 'none') {
        clearDropdown.style.display = 'block';
      } else {
        clearDropdown.style.display = 'none';
      }
    });
    document.addEventListener('click', function () {
      clearDropdown.style.display = 'none';
    });
    clearDropdown.addEventListener('click', function (e) {
      e.stopPropagation();
    });

    const confirmClearModal = document.getElementById('confirmClearModal');
    const clearConfirmText = document.getElementById('clearConfirmText');
    const confirmClearAction = document.getElementById('confirmClearAction');
    const cancelClear = document.getElementById('cancelClear');
    const closeModal = document.querySelector('#confirmClearModal .close-modal');

    let currentClearType = '';
    window.confirmClear = function (type) {
      currentClearType = type;
      let confirmText = '';

      switch (type) {
        case 'old':
          confirmText = '确定要清除30天前的日志吗？';
          break;
        case 'failed':
          confirmText = '确定要清除所有失败的登录日志吗？';
          break;
        case 'all':
          confirmText = '确定要清除所有日志吗？这将删除所有登录记录！';
          break;
      }

      clearConfirmText.textContent = confirmText;
      confirmClearModal.style.display = 'block';
      clearDropdown.style.display = 'none';

      if (isMobile) {
        document.body.style.overflow = 'hidden';
      }
    };
    confirmClearAction.addEventListener('click', function () {
      if (currentClearType) {
        window.location.href = `logs.php?action=clear&type=${currentClearType}&token=<?php echo $csrf_token; ?>`;
      }
    });
    function closeClearModal() {
      confirmClearModal.style.display = 'none';
      if (isMobile) {
        document.body.style.overflow = '';
      }
    }
    cancelClear.addEventListener('click', closeClearModal);
    closeModal.addEventListener('click', closeClearModal);
    window.addEventListener('click', function (event) {
      if (event.target == confirmClearModal) {
        closeClearModal();
      }
    });

    document.addEventListener('keydown', function (e) {
      if (e.key === 'Escape') {
        closeClearModal();
      }
    });

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

    const dateFrom = document.getElementById('date_from');
    const dateTo = document.getElementById('date_to');

    function formatDate(date) {
      const year = date.getFullYear();
      const month = String(date.getMonth() + 1).padStart(2, '0');
      const day = String(date.getDate()).padStart(2, '0');
      return `${year}-${month}-${day}`;
    }
  });
</script>

<?php
include_once __DIR__ . '/views/footer.php';
?>