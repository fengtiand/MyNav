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
$page_title = '链接管理';
$breadcrumb = [
  ['text' => '首页', 'url' => 'index.php'],
  ['text' => '链接管理']
];
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
  $link_id = (int) $_GET['id'];
  $csrf_token = isset($_GET['token']) ? $_GET['token'] : '';

  if (!verify_csrf_token($csrf_token)) {
    $message = '安全验证失败，请刷新页面重试';
    $message_type = 'error';
  } else {
    $conn = db_connect();
    $sql = "DELETE FROM links WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $link_id);

    if ($stmt->execute()) {
      $message = '链接已成功删除';
      $message_type = 'success';
    } else {
      $message = '删除链接失败：' . $conn->error;
      $message_type = 'error';
    }

    $stmt->close();
    $conn->close();
  }
}
$conn = db_connect();
$sql = "SELECT * FROM categories WHERE status = 1 ORDER BY sort_order ASC, id ASC";
$result = $conn->query($sql);
$categories = [];
if ($result) {
  while ($row = $result->fetch_assoc()) {
    $categories[] = $row;
  }
}
$page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
$per_page = 10;
$offset = ($page - 1) * $per_page;
$category_id = isset($_GET['category_id']) ? (int) $_GET['category_id'] : 0;
$keyword = isset($_GET['keyword']) ? trim($_GET['keyword']) : '';
$status = isset($_GET['status']) && in_array($_GET['status'], ['0', '1']) ? $_GET['status'] : '';
$where_clauses = [];
$params = [];
$param_types = "";
if ($category_id > 0) {
  $where_clauses[] = "l.category_id = ?";
  $params[] = $category_id;
  $param_types .= "i";
}
if (!empty($keyword)) {
  $where_clauses[] = "(l.title LIKE ? OR l.url LIKE ? OR l.description LIKE ?)";
  $params[] = "%$keyword%";
  $params[] = "%$keyword%";
  $params[] = "%$keyword%";
  $param_types .= "sss";
}
if ($status !== '') {
  $where_clauses[] = "l.status = ?";
  $params[] = $status;
  $param_types .= "i";
}
$where_sql = !empty($where_clauses) ? "WHERE " . implode(" AND ", $where_clauses) : "";
$count_sql = "SELECT COUNT(*) AS total FROM links l $where_sql";

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
$links = [];
$sql = "SELECT l.*, c.name as category_name 
        FROM links l 
        LEFT JOIN categories c ON l.category_id = c.id 
        $where_sql 
        ORDER BY l.sort_order ASC, l.id DESC 
        LIMIT ?, ?";

$params[] = $offset;
$params[] = $per_page;
$param_types .= "ii";
$stmt = $conn->prepare($sql);
$stmt->bind_param($param_types, ...$params);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
  $links[] = $row;
}
$stmt->close();
$conn->close();
$csrf_token = generate_csrf_token();
include_once __DIR__ . '/views/header.php';
?>

<div class="page-header d-flex justify-between align-center">
  <div>
    <h1>链接管理</h1>
    <p>管理网站的导航链接</p>
  </div>
  <div>
    <button type="button" class="btn btn-primary" id="addLinkBtn">
      <i class="fas fa-plus"></i> 添加链接
    </button>
  </div>
</div>

<?php if (isset($message)): ?>
  <div class="alert alert-<?php echo $message_type; ?>">
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
          <label for="category_id">分类</label>
          <select id="category_id" name="category_id" class="form-control">
            <option value="0">全部分类</option>
            <?php foreach ($categories as $category): ?>
              <option value="<?php echo $category['id']; ?>" <?php echo $category_id == $category['id'] ? 'selected' : ''; ?>>
                <?php echo xss_clean($category['name']); ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>

        <div class="filter-item">
          <label for="keyword">关键词</label>
          <input type="text" id="keyword" name="keyword" class="form-control" value="<?php echo xss_clean($keyword); ?>"
            placeholder="标题/URL/描述">
        </div>

        <div class="filter-item">
          <label for="status">状态</label>
          <select id="status" name="status" class="form-control">
            <option value="">全部</option>
            <option value="1" <?php echo $status === '1' ? 'selected' : ''; ?>>启用</option>
            <option value="0" <?php echo $status === '0' ? 'selected' : ''; ?>>禁用</option>
          </select>
        </div>
      </div>

      <div class="filter-actions">
        <button type="submit" class="btn btn-primary">查询</button>
        <a href="links.php" class="btn btn-secondary">重置</a>
      </div>
    </form>
  </div>
</div>

<div class="card">
  <div class="card-header">
    <h3 class="card-title">链接列表</h3>
    <div class="card-tools">
      共 <?php echo $total; ?> 个链接
    </div>
  </div>
  <div class="card-body">
    <?php if (empty($links)): ?>
      <div class="empty-data">暂无链接数据</div>
    <?php else: ?>
      <div class="table-responsive">
        <table class="data-table">
          <thead>
            <tr>
              <th width="60">ID</th>
              <th>标题</th>
              <th>URL</th>
              <th class="mobile-hide">分类</th>
              <th width="80" class="mobile-hide">排序</th>
              <th width="80" class="mobile-hide">状态</th>
              <th width="80" class="mobile-hide">访问量</th>
              <th width="150">操作</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($links as $link): ?>
              <tr>
                <td><?php echo $link['id']; ?></td>
                <td><?php echo xss_clean($link['title']); ?></td>
                <td>
                  <a href="<?php echo xss_clean($link['url']); ?>" target="_blank" class="link-url">
                    <?php echo substr(xss_clean($link['url']), 0, 30) . (strlen($link['url']) > 30 ? '...' : ''); ?>
                  </a>
                </td>
                <td class="mobile-hide"><?php echo xss_clean($link['category_name']); ?></td>
                <td class="mobile-hide"><?php echo $link['sort_order']; ?></td>
                <td class="mobile-hide">
                  <?php if ($link['status'] == 1): ?>
                    <span class="badge success">启用</span>
                  <?php else: ?>
                    <span class="badge error">禁用</span>
                  <?php endif; ?>
                </td>
                <td class="mobile-hide"><?php echo $link['visits']; ?></td>
                <td>
                  <div class="action-buttons">
                    <button type="button" class="btn btn-sm btn-edit edit-link" data-id="<?php echo $link['id']; ?>"
                      data-title="<?php echo xss_clean($link['title']); ?>"
                      data-url="<?php echo xss_clean($link['url']); ?>"
                      data-description="<?php echo xss_clean($link['description']); ?>"
                      data-category="<?php echo $link['category_id']; ?>" data-sort="<?php echo $link['sort_order']; ?>"
                      data-status="<?php echo $link['status']; ?>">
                      <i class="fas fa-edit"></i>
                    </button>
                    <a href="links.php?action=delete&id=<?php echo $link['id']; ?>&token=<?php echo $csrf_token; ?>"
                      class="btn btn-sm btn-delete delete-link" onclick="return confirm('确定要删除此链接吗？');">
                      <i class="fas fa-trash-alt"></i>
                    </a>
                  </div>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>

      <!-- 分页 -->
      <?php if ($total_pages > 1): ?>
        <div class="pagination">
          <?php
          // 构建分页URL参数
          $page_params = [];
          if ($category_id > 0)
            $page_params[] = "category_id=$category_id";
          if (!empty($keyword))
            $page_params[] = "keyword=" . urlencode($keyword);
          if ($status !== '')
            $page_params[] = "status=$status";
          $page_query = !empty($page_params) ? '&' . implode('&', $page_params) : '';
          ?>
          <?php if ($page > 1): ?>
            <a href="?page=1<?php echo $page_query; ?>" class="page-link">&laquo;</a>
            <a href="?page=<?php echo $page - 1; ?><?php echo $page_query; ?>" class="page-link">&lsaquo;</a>
          <?php endif; ?>

          <?php
          $start_page = max(1, $page - 2);
          $end_page = min($total_pages, $page + 2);

          for ($i = $start_page; $i <= $end_page; $i++):
            ?>
            <a href="?page=<?php echo $i; ?><?php echo $page_query; ?>"
              class="page-link <?php echo $i == $page ? 'active' : ''; ?>"><?php echo $i; ?></a>
          <?php endfor; ?>

          <?php if ($page < $total_pages): ?>
            <a href="?page=<?php echo $page + 1; ?><?php echo $page_query; ?>" class="page-link">&rsaquo;</a>
            <a href="?page=<?php echo $total_pages; ?><?php echo $page_query; ?>" class="page-link">&raquo;</a>
          <?php endif; ?>
        </div>
      <?php endif; ?>
    <?php endif; ?>
  </div>
</div>

<!-- 链接表单模态框 -->
<div class="modal" id="linkModal">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h3 class="modal-title" id="modalTitle">添加链接</h3>
        <button type="button" class="close-modal"><i class="fas fa-times"></i></button>
      </div>
      <div class="modal-body">
        <form method="post" action="" id="linkForm">
          <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
          <input type="hidden" name="link_id" id="link_id" value="0">

          <div class="form-group">
            <label for="title">链接标题 <span class="required">*</span></label>
            <input type="text" id="title" name="title" class="form-control" required>
          </div>

          <div class="form-group">
            <label for="url">链接地址 <span class="required">*</span></label>
            <input type="url" id="url" name="url" class="form-control" required>
          </div>

          <div class="form-group">
            <label for="link_category_id">所属分类 <span class="required">*</span></label>
            <select id="link_category_id" name="category_id" class="form-control" required>
              <option value="">请选择分类</option>
              <?php foreach ($categories as $category): ?>
                <option value="<?php echo $category['id']; ?>">
                  <?php echo xss_clean($category['name']); ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>

          <div class="form-group">
            <label for="link_description">链接描述</label>
            <textarea id="link_description" name="description" class="form-control" rows="3"></textarea>
          </div>

          <div class="form-row">
            <div class="form-group col-6">
              <label for="link_sort_order">排序</label>
              <input type="number" id="link_sort_order" name="sort_order" class="form-control" value="0" min="0">
              <div class="form-hint">数字越小排序越靠前</div>
            </div>

            <div class="form-group col-6">
              <label for="link_status">状态</label>
              <select id="link_status" name="status" class="form-control">
                <option value="1">启用</option>
                <option value="0">禁用</option>
              </select>
            </div>
          </div>

          <div class="form-actions text-right">
            <button type="button" class="btn btn-secondary close-modal-btn">取消</button>
            <button type="submit" class="btn btn-primary">保存</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>

<style>
  /* 响应式优化样式 */
  @media (max-width: 768px) {
    .table-responsive {
      overflow-x: auto;
    }

    .data-table th,
    .data-table td {
      padding: 8px 10px;
      font-size: 13px;
    }

    .data-table .mobile-hide {
      display: none;
    }

    .filter-item {
      flex: 100%;
      margin-bottom: 10px;
    }

    .filter-actions {
      margin-top: 15px;
      display: flex;
      width: 100%;
    }

    .filter-actions .btn {
      flex: 1;
      text-align: center;
      margin: 0 5px;
    }

    .pagination {
      flex-wrap: wrap;
    }

    .page-link {
      margin-bottom: 5px;
    }

    .modal-dialog {
      margin: 10px;
      width: auto;
      max-height: 90vh;
    }

    .modal-content {
      max-height: 90vh;
      overflow-y: auto;
    }

    /* 移动端模态框固定样式 */
    body.modal-open {
      overflow: hidden;
    }

    .action-buttons {
      display: flex;
      justify-content: space-around;
    }

    .action-buttons .btn {
      margin: 0 2px;
    }
  }

  @media (max-width: 576px) {
    .page-header {
      flex-direction: column;
      align-items: flex-start;
    }

    .page-header>div:last-child {
      margin-top: 10px;
      width: 100%;
    }

    .page-header>div:last-child .btn {
      width: 100%;
    }

    .card-header {
      flex-wrap: wrap;
    }

    .card-title {
      margin-bottom: 10px;
      width: 100%;
    }

    .card-tools {
      width: 100%;
      text-align: left;
      margin-top: 5px;
    }

    /* 极小屏幕下的表格调整 */
    .data-table th:nth-child(1),
    .data-table td:nth-child(1) {
      display: none;
    }

    .link-url {
      max-width: 120px;
      overflow: hidden;
      text-overflow: ellipsis;
      display: inline-block;
      white-space: nowrap;
    }
  }
</style>

<script>
  document.addEventListener('DOMContentLoaded', function () {
    // 检测是否为移动设备
    const isMobile = window.innerWidth < 768;

    // 筛选区域折叠 - 在移动端默认收起
    const toggleFilterBtn = document.getElementById('toggleFilter');
    const filterBody = document.getElementById('filterBody');

    // 移动端默认收起筛选区域
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

    // 添加链接按钮
    const addLinkBtn = document.getElementById('addLinkBtn');
    const linkModal = document.getElementById('linkModal');
    const modalTitle = document.getElementById('modalTitle');
    const linkForm = document.getElementById('linkForm');
    const linkIdInput = document.getElementById('link_id');
    const titleInput = document.getElementById('title');
    const urlInput = document.getElementById('url');
    const categoryInput = document.getElementById('link_category_id');
    const descriptionInput = document.getElementById('link_description');
    const sortOrderInput = document.getElementById('link_sort_order');
    const statusInput = document.getElementById('link_status');
    const closeModalBtns = document.querySelectorAll('.close-modal, .close-modal-btn');

    // 打开添加链接模态框
    addLinkBtn.addEventListener('click', function () {
      modalTitle.textContent = '添加链接';
      linkIdInput.value = '0';
      titleInput.value = '';
      urlInput.value = '';
      categoryInput.value = '';
      descriptionInput.value = '';
      sortOrderInput.value = '0';
      statusInput.value = '1';

      linkModal.style.display = 'block';
      // 移动端滚动到顶部
      if (isMobile) {
        window.scrollTo(0, 0);
        document.body.classList.add('modal-open');
      }
    });

    // 编辑链接按钮
    const editLinkBtns = document.querySelectorAll('.edit-link');

    editLinkBtns.forEach(function (btn) {
      btn.addEventListener('click', function () {
        const id = this.getAttribute('data-id');
        const title = this.getAttribute('data-title');
        const url = this.getAttribute('data-url');
        const category = this.getAttribute('data-category');
        const description = this.getAttribute('data-description');
        const sort = this.getAttribute('data-sort');
        const status = this.getAttribute('data-status');

        modalTitle.textContent = '编辑链接';
        linkIdInput.value = id;
        titleInput.value = title;
        urlInput.value = url;
        categoryInput.value = category;
        descriptionInput.value = description || '';
        sortOrderInput.value = sort;
        statusInput.value = status;

        linkModal.style.display = 'block';
        // 移动端滚动到顶部
        if (isMobile) {
          window.scrollTo(0, 0);
          document.body.classList.add('modal-open');
        }
      });
    });

    // 关闭模态框
    closeModalBtns.forEach(function (btn) {
      btn.addEventListener('click', function () {
        linkModal.style.display = 'none';
        if (isMobile) {
          document.body.classList.remove('modal-open');
        }
      });
    });

    // 点击模态框外部关闭
    window.addEventListener('click', function (event) {
      if (event.target == linkModal) {
        linkModal.style.display = 'none';
        if (isMobile) {
          document.body.classList.remove('modal-open');
        }
      }
    });

    // 处理链接表单提交
    linkForm.addEventListener('submit', function (e) {
      e.preventDefault();

      const formData = new FormData(this);
      const xhr = new XMLHttpRequest();

      xhr.open('POST', 'link_process.php', true);
      xhr.onload = function () {
        if (xhr.status === 200) {
          try {
            const response = JSON.parse(xhr.responseText);
            if (response.code === 0) {
              // 成功
              window.location.reload();
            } else {
              // 失败
              alert(response.msg);
            }
          } catch (e) {
            alert('操作失败，请重试');
          }
        } else {
          alert('请求失败，请重试');
        }
      };

      xhr.send(formData);
    });

    // 自动隐藏提示消息
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
  });
</script>

<?php
// 引入底部
include_once __DIR__ . '/views/footer.php';
?>