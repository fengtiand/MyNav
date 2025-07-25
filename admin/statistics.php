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
$page_title = '访问统计';
$breadcrumb = [
  ['text' => '首页', 'url' => 'index.php'],
  ['text' => '访问统计']
];
$conn = db_connect();
$range = isset($_GET['range']) ? intval($_GET['range']) : 7;
$valid_ranges = [7, 30, 90, 365];
if (!in_array($range, $valid_ranges)) {
  $range = 7;
}
$end_date = date('Y-m-d');
$start_date = date('Y-m-d', strtotime("-{$range} days"));
$sql = "SELECT l.id, l.title, l.url, l.visits, c.name as category_name 
        FROM links l 
        LEFT JOIN categories c ON l.category_id = c.id 
        WHERE l.status = 1 
        ORDER BY l.visits DESC 
        LIMIT 10";
$popular_links = db_fetch_all($sql);
$sql = "SELECT v.visit_date, SUM(v.visits) as daily_visits 
        FROM visits v 
        WHERE v.visit_date BETWEEN ? AND ? 
        GROUP BY v.visit_date 
        ORDER BY v.visit_date";
$trend_data = db_fetch_all($sql, [$start_date, $end_date]);
$sql = "SELECT c.name, COUNT(l.id) as link_count, SUM(l.visits) as total_visits 
        FROM categories c 
        LEFT JOIN links l ON c.id = l.category_id AND l.status = 1 
        WHERE c.status = 1 
        GROUP BY c.id 
        ORDER BY total_visits DESC";
$category_stats = db_fetch_all($sql);
$total_visits = array_sum(array_column($trend_data, 'daily_visits'));
$dates = [];
$visits = [];
$current_date = $start_date;
while (strtotime($current_date) <= strtotime($end_date)) {
  $dates[] = date('m/d', strtotime($current_date));
  $visits[$current_date] = 0;
  $current_date = date('Y-m-d', strtotime('+1 day', strtotime($current_date)));
}
foreach ($trend_data as $day) {
  $visits[$day['visit_date']] = (int) $day['daily_visits'];
}
$chart_dates = json_encode(array_values($dates));
$chart_visits = json_encode(array_values($visits));
require_once __DIR__ . '/views/header.php';
?>

<div class="stats-container">
  <div class="stats-overview">
    <div class="overview-card">
      <div class="overview-icon" style="background-color: rgba(110, 142, 251, 0.2);">
        <i class="fas fa-chart-line"></i>
      </div>
      <div class="overview-value"><?php echo $total_visits; ?></div>
      <div class="overview-label"><?php echo $range; ?>天总访问量</div>
    </div>

    <div class="overview-card">
      <div class="overview-icon" style="background-color: rgba(167, 119, 227, 0.2);">
        <i class="fas fa-link"></i>
      </div>
      <div class="overview-value"><?php echo count($popular_links); ?></div>
      <div class="overview-label">热门链接</div>
    </div>

    <div class="overview-card">
      <div class="overview-icon" style="background-color: rgba(52, 199, 89, 0.2);">
        <i class="fas fa-list"></i>
      </div>
      <div class="overview-value"><?php echo count($category_stats); ?></div>
      <div class="overview-label">活跃分类</div>
    </div>

    <div class="overview-card">
      <div class="overview-icon" style="background-color: rgba(255, 149, 0, 0.2);">
        <i class="fas fa-calendar-day"></i>
      </div>
      <div class="overview-value"><?php echo round($total_visits / max(1, $range), 1); ?></div>
      <div class="overview-label">日均访问量</div>
    </div>
  </div>

  <div class="chart-section">
    <div class="chart-card">
      <div class="chart-header">
        <h3 class="chart-title">访问趋势</h3>
        <div class="time-filters">
          <a href="?range=7" class="time-filter <?php echo $range == 7 ? 'active' : ''; ?>">7天</a>
          <a href="?range=30" class="time-filter <?php echo $range == 30 ? 'active' : ''; ?>">30天</a>
          <a href="?range=90" class="time-filter <?php echo $range == 90 ? 'active' : ''; ?>">90天</a>
          <a href="?range=365" class="time-filter <?php echo $range == 365 ? 'active' : ''; ?>">1年</a>
        </div>
      </div>
      <div class="chart-body">
        <canvas id="visitsChart" class="chart-canvas"></canvas>
      </div>
    </div>
  </div>

  <div class="data-section">
    <div class="data-card">
      <div class="data-header">
        <h3 class="data-title">热门链接</h3>
      </div>
      <div class="data-body">
        <?php if (empty($popular_links)): ?>
          <div class="empty-state">
            <i class="fas fa-chart-bar" style="font-size: 48px; margin-bottom: 15px; opacity: 0.3;"></i>
            <p>暂无访问数据</p>
          </div>
        <?php else: ?>
          <table class="links-table">
            <thead>
              <tr>
                <th>链接标题</th>
                <th>分类</th>
                <th>访问次数</th>
                <th>操作</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($popular_links as $link): ?>
                <tr>
                  <td>
                    <div class="link-title" title="<?php echo htmlspecialchars($link['title']); ?>">
                      <?php echo htmlspecialchars($link['title']); ?>
                    </div>
                  </td>
                  <td><?php echo htmlspecialchars($link['category_name']); ?></td>
                  <td class="visit-count"><?php echo $link['visits']; ?></td>
                  <td class="link-action">
                    <a href="../share.php?id=<?php echo $link['id']; ?>" class="action-btn" target="_blank">
                      <i class="fas fa-external-link-alt"></i>
                    </a>
                  </td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        <?php endif; ?>
      </div>
    </div>

    <div class="data-card">
      <div class="data-header">
        <h3 class="data-title">分类统计</h3>
      </div>
      <div class="data-body">
        <div class="category-chart-container">
          <?php if (empty($category_stats) || array_sum(array_column($category_stats, 'total_visits')) == 0): ?>
            <div class="empty-state">
              <i class="fas fa-chart-pie" style="font-size: 48px; margin-bottom: 15px; opacity: 0.3;"></i>
              <p>暂无分类数据</p>
            </div>
          <?php else: ?>
            <canvas id="categoryChart" class="chart-canvas"></canvas>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net.cn/npm/chart.js"></script>
<script>
  const ctx = document.getElementById('visitsChart').getContext('2d');
  const visitsChart = new Chart(ctx, {
    type: 'line',
    data: {
      labels: <?php echo $chart_dates; ?>,
      datasets: [{
        label: '访问次数',
        data: <?php echo $chart_visits; ?>,
        backgroundColor: 'rgba(110, 142, 251, 0.2)',
        borderColor: 'rgba(110, 142, 251, 1)',
        borderWidth: 3,
        pointBackgroundColor: 'rgba(110, 142, 251, 1)',
        pointBorderColor: '#fff',
        pointBorderWidth: 2,
        pointRadius: 5,
        pointHoverRadius: 7,
        tension: 0.4,
        fill: true
      }]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      interaction: {
        intersect: false,
        mode: 'index'
      },
      scales: {
        x: {
          grid: {
            color: 'rgba(255, 255, 255, 0.1)',
            borderColor: 'rgba(255, 255, 255, 0.2)'
          },
          ticks: {
            color: 'rgba(255, 255, 255, 0.7)',
            font: {
              size: 11
            }
          }
        },
        y: {
          beginAtZero: true,
          grid: {
            color: 'rgba(255, 255, 255, 0.1)',
            borderColor: 'rgba(255, 255, 255, 0.2)'
          },
          ticks: {
            color: 'rgba(255, 255, 255, 0.7)',
            precision: 0,
            font: {
              size: 11
            }
          }
        }
      },
      plugins: {
        legend: {
          display: false
        },
        tooltip: {
          backgroundColor: 'rgba(0, 0, 0, 0.8)',
          titleColor: '#fff',
          bodyColor: '#fff',
          borderColor: 'rgba(255, 255, 255, 0.2)',
          borderWidth: 1,
          cornerRadius: 8,
          displayColors: false
        }
      }
    }
  });

  <?php if (!empty($category_stats) && array_sum(array_column($category_stats, 'total_visits')) > 0): ?>
    const categoryCtx = document.getElementById('categoryChart').getContext('2d');
    const categoryChart = new Chart(categoryCtx, {
      type: 'doughnut',
      data: {
        labels: [<?php echo implode(',', array_map(function ($item) {
          return "'" . addslashes($item['name']) . "'";
        }, $category_stats)); ?>],
        datasets: [{
          data: [<?php echo implode(',', array_map(function ($item) {
            return $item['total_visits'];
          }, $category_stats)); ?>],
          backgroundColor: [
            'rgba(110, 142, 251, 0.8)',
            'rgba(167, 119, 227, 0.8)',
            'rgba(52, 199, 89, 0.8)',
            'rgba(255, 149, 0, 0.8)',
            'rgba(255, 59, 48, 0.8)',
            'rgba(90, 200, 250, 0.8)',
            'rgba(255, 204, 0, 0.8)',
            'rgba(175, 82, 222, 0.8)',
            'rgba(255, 45, 85, 0.8)',
            'rgba(48, 209, 88, 0.8)'
          ],
          borderWidth: 0,
          hoverBorderWidth: 2,
          hoverBorderColor: '#fff'
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        cutout: '60%',
        plugins: {
          legend: {
            position: 'bottom',
            labels: {
              color: 'rgba(255, 255, 255, 0.8)',
              font: {
                size: 11
              },
              padding: 15,
              usePointStyle: true,
              pointStyle: 'circle'
            }
          },
          tooltip: {
            backgroundColor: 'rgba(0, 0, 0, 0.8)',
            titleColor: '#fff',
            bodyColor: '#fff',
            borderColor: 'rgba(255, 255, 255, 0.2)',
            borderWidth: 1,
            cornerRadius: 8,
            callbacks: {
              label: function (context) {
                const label = context.label || '';
                const value = context.raw || 0;
                const total = context.dataset.data.reduce((a, b) => a + b, 0);
                const percentage = ((value / total) * 100).toFixed(1);
                return label + ': ' + value + ' 次 (' + percentage + '%)';
              }
            }
          }
        }
      }
    });
  <?php endif; ?>
</script>

<?php
require_once __DIR__ . '/views/footer.php';
?>