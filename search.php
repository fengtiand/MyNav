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
$keyword = isset($_GET['q']) ? trim($_GET['q']) : '';
$site_title = "搜索结果";
$site_description = "搜索结果：" . htmlspecialchars($keyword);
$conn = db_connect();
$search_results = [];

if (!empty($keyword)) {
  $search_param = '%' . $conn->real_escape_string($keyword) . '%';

  $sql = "SELECT l.*, c.name as category_name 
          FROM links l 
          LEFT JOIN categories c ON l.category_id = c.id 
          WHERE l.status = 1 AND (
            l.title LIKE ? OR 
            l.url LIKE ? OR 
            l.description LIKE ?
          )
          ORDER BY c.sort_order ASC, l.sort_order ASC";

  $stmt = $conn->prepare($sql);
  $stmt->bind_param('sss', $search_param, $search_param, $search_param);
  $stmt->execute();
  $result = $stmt->get_result();

  while ($row = $result->fetch_assoc()) {
    $search_results[] = $row;
  }

  $stmt->close();
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
  <link rel="stylesheet" href="assets/css/style.css">
</head>

<body>
  <div class="container">
    <header>
      <h1>搜索结果</h1>
      <?php if (!empty($keyword)): ?>
        <p>关键词: "<?php echo htmlspecialchars($keyword); ?>"</p>
      <?php endif; ?>
    </header>

    <div class="search-box">
      <form action="search.php" method="get" class="search-form">
        <input type="text" name="q" value="<?php echo htmlspecialchars($keyword); ?>" class="search-input"
          placeholder="搜索链接..." required>
        <button type="submit" class="search-button">
          <i class="fas fa-search"></i>
        </button>
      </form>
    </div>

    <main>
      <?php if (!empty($keyword)): ?>
        <?php if (count($search_results) > 0): ?>
          <div class="search-results">
            <?php foreach ($search_results as $result): ?>
              <div class="result-card">
                <a href="<?php echo htmlspecialchars($result['url']); ?>" target="_blank">
                  <div class="result-title"><?php echo htmlspecialchars($result['title']); ?></div>
                  <div class="result-category">分类: <?php echo htmlspecialchars($result['category_name']); ?></div>
                  <div class="result-url"><?php echo htmlspecialchars($result['url']); ?></div>
                  <?php if (!empty($result['description'])): ?>
                    <div class="result-description"><?php echo htmlspecialchars($result['description']); ?></div>
                  <?php endif; ?>
                </a>
              </div>
            <?php endforeach; ?>
          </div>
        <?php else: ?>
          <div class="no-results">
            <p>没有找到与 "<?php echo htmlspecialchars($keyword); ?>" 相关的链接</p>
            <a href="index.php" class="back-link">返回首页</a>
          </div>
        <?php endif; ?>
      <?php else: ?>
        <div class="no-results">
          <p>请输入搜索关键词</p>
          <a href="index.php" class="back-link">返回首页</a>
        </div>
      <?php endif; ?>
    </main>

    <footer>
      <p>Copyright © <?php echo date('Y'); ?> MyNav All Rights Reserved.</p>
    </footer>
  </div>
</body>

</html>