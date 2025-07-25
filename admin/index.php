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
$page_title = '控制台';
$breadcrumb = [
  ['text' => '首页', 'url' => 'index.php']
];
$conn = db_connect();
$sql = "SELECT COUNT(*) as total FROM categories";
$result = $conn->query($sql);
$categories_count = $result->fetch_assoc()['total'] ?? 0;
$sql = "SELECT COUNT(*) as total FROM links";
$result = $conn->query($sql);
$links_count = $result->fetch_assoc()['total'] ?? 0;
$sql = "SELECT SUM(visits) as total FROM visits WHERE visit_date >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)";
$result = $conn->query($sql);
$week_visits = $result->fetch_assoc()['total'] ?? 0;
$sql = "SELECT COUNT(*) as total FROM admin_users";
$result = $conn->query($sql);
$users_count = $result->fetch_assoc()['total'] ?? 0;
$sql = "SELECT l.*, c.name as category_name 
        FROM links l 
        LEFT JOIN categories c ON l.category_id = c.id 
        ORDER BY l.created_at DESC 
        LIMIT 5";
$recent_links = [];
$result = $conn->query($sql);
if ($result) {
  while ($row = $result->fetch_assoc()) {
    $recent_links[] = $row;
  }
}
$sql = "SELECT l.*, a.username, a.nickname 
        FROM admin_login_logs l 
        LEFT JOIN admin_users a ON l.admin_id = a.id 
        ORDER BY l.login_time DESC 
        LIMIT 5";
$recent_logs = [];
$result = $conn->query($sql);
if ($result) {
  while ($row = $result->fetch_assoc()) {
    $recent_logs[] = $row;
  }
}
$conn->close();
include_once __DIR__ . '/views/header.php';
?>

<div class="dashboard">
  <div class="row">
    <div class="stat-card">
      <div class="stat-card-icon" style="background-color: rgba(110, 142, 251, 0.2);">
        <i class="fas fa-list"></i>
      </div>
      <div class="stat-card-info">
        <div class="stat-card-title">分类数量</div>
        <div class="stat-card-value"><?php echo $categories_count; ?></div>
      </div>
    </div>

    <div class="stat-card">
      <div class="stat-card-icon" style="background-color: rgba(167, 119, 227, 0.2);">
        <i class="fas fa-link"></i>
      </div>
      <div class="stat-card-info">
        <div class="stat-card-title">链接数量</div>
        <div class="stat-card-value"><?php echo $links_count; ?></div>
      </div>
    </div>

    <div class="stat-card">
      <div class="stat-card-icon" style="background-color: rgba(52, 199, 89, 0.2);">
        <i class="fas fa-eye"></i>
      </div>
      <div class="stat-card-info">
        <div class="stat-card-title">近7天访问量</div>
        <div class="stat-card-value"><?php echo $week_visits; ?></div>
      </div>
    </div>

    <div class="stat-card">
      <div class="stat-card-icon" style="background-color: rgba(255, 149, 0, 0.2);">
        <i class="fas fa-user"></i>
      </div>
      <div class="stat-card-info">
        <div class="stat-card-title">用户数量</div>
        <div class="stat-card-value"><?php echo $users_count; ?></div>
      </div>
    </div>
  </div>

  <div class="row mt-20">
    <div class="card" style="flex: 1; margin-right: 20px;">
      <div class="card-header">
        <h3 class="card-title">最近添加的链接</h3>
        <a href="links.php" class="more-link">查看更多</a>
      </div>
      <div class="card-body">
        <?php if (empty($recent_links)): ?>
          <div class="empty-data">暂无数据</div>
        <?php else: ?>
          <table class="data-table">
            <thead>
              <tr>
                <th>名称</th>
                <th>分类</th>
                <th>URL</th>
                <th>添加时间</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($recent_links as $link): ?>
                <tr>
                  <td><?php echo xss_clean($link['title']); ?></td>
                  <td><?php echo xss_clean($link['category_name']); ?></td>
                  <td>
                    <a href="<?php echo xss_clean($link['url']); ?>" target="_blank" class="link-url">
                      <?php echo substr(xss_clean($link['url']), 0, 30) . (strlen($link['url']) > 30 ? '...' : ''); ?>
                    </a>
                  </td>
                  <td><?php echo date('Y-m-d H:i', strtotime($link['created_at'])); ?></td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        <?php endif; ?>
      </div>
    </div>
    <div class="card" style="flex: 1;">
      <div class="card-header">
        <h3 class="card-title">最近登录记录</h3>
        <a href="logs.php" class="more-link">查看更多</a>
      </div>
      <div class="card-body">
        <?php if (empty($recent_logs)): ?>
          <div class="empty-data">暂无数据</div>
        <?php else: ?>
          <table class="data-table">
            <thead>
              <tr>
                <th>用户</th>
                <th>登录IP</th>
                <th>登录时间</th>
                <th>状态</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($recent_logs as $log): ?>
                <?php
                $status_class = $log['login_status'] ? 'success' : 'error';
                $status_text = $log['login_status'] ? '成功' : '失败';
                ?>
                <tr>
                  <td><?php echo xss_clean(!empty($log['nickname']) ? $log['nickname'] : $log['username']); ?></td>
                  <td><?php echo xss_clean($log['login_ip']); ?></td>
                  <td><?php echo date('Y-m-d H:i:s', strtotime($log['login_time'])); ?></td>
                  <td><span class="badge <?php echo $status_class; ?>"><?php echo $status_text; ?></span></td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        <?php endif; ?>
      </div>
    </div>
  </div>
  <?php if (date('Y-m-d') >= '2025-08-30'): ?>
  <div class="row mt-20">
    <div class="card" style="flex: 1; margin-right: 20px;">
      <div class="card-header">
        <h3 class="card-title">
          <i class="fas fa-bullhorn me-2"></i>系统公告
        </h3>
      </div>
      <div class="card-body">
        <div id="announcements-container">
          <div class="text-center py-3">
            <span class="text-muted fs-sm">
              <i class="fa fa-spinner fa-spin opacity-50 me-1"></i>加载中...
            </span>
          </div>
        </div>
      </div>
    </div>
    
    <div class="card" style="flex: 1;">
      <div class="card-header">
        <h3 class="card-title">
          <i class="fas fa-ad me-2"></i>推广信息
        </h3>
      </div>
      <div class="card-body">
        <div id="advertisements-container">
          <div class="text-center py-3">
            <span class="text-muted fs-sm">
              <i class="fa fa-spinner fa-spin opacity-50 me-1"></i>加载中...
            </span>
          </div>
        </div>
      </div>
    </div>
  </div>
  <?php endif; ?>
  
  <div class="card mt-20">
    <div class="card-header">
      <h3 class="card-title">欢迎使用</h3>
    </div>
    <div class="card-body">
      <div class="welcome-message">
        <p>您好，<?php echo $admin_name; ?>！欢迎使用MyNav个人导航系统后台管理。</p>
        <p>当前系统版本：2.0.0</p>
        <p>如有问题或建议，请联系系统开发者（奉天）。</p>
      </div>
    </div>
  </div>
</div>


<?php
include_once __DIR__ . '/views/footer.php';
?>