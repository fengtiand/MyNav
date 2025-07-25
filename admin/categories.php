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
$page_title = '分类管理';
$breadcrumb = [
  ['text' => '首页', 'url' => 'index.php'],
  ['text' => '分类管理']
];
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
  $category_id = (int) $_GET['id'];
  $csrf_token = isset($_GET['token']) ? $_GET['token'] : '';

  if (!verify_csrf_token($csrf_token)) {
    $message = '安全验证失败，请刷新页面重试';
    $message_type = 'error';
  } else {
    $conn = db_connect();
    $check_sql = "SELECT COUNT(*) as count FROM links WHERE category_id = ?";
    $stmt = $conn->prepare($check_sql);
    $stmt->bind_param("i", $category_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $links_count = $result->fetch_assoc()['count'];
    $stmt->close();

    if ($links_count > 0) {
      $message = '无法删除，该分类下有 ' . $links_count . ' 个链接';
      $message_type = 'error';
    } else {
      $sql = "DELETE FROM categories WHERE id = ?";
      $stmt = $conn->prepare($sql);
      $stmt->bind_param("i", $category_id);

      if ($stmt->execute()) {
        $message = '分类已成功删除';
        $message_type = 'success';
      } else {
        $message = '删除分类失败：' . $conn->error;
        $message_type = 'error';
      }

      $stmt->close();
    }

    $conn->close();
  }
}
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $csrf_token = isset($_POST['csrf_token']) ? $_POST['csrf_token'] : '';

  if (!verify_csrf_token($csrf_token)) {
    $message = '安全验证失败，请刷新页面重试';
    $message_type = 'error';
  } else {
    $conn = db_connect();
    $category_id = isset($_POST['category_id']) ? (int) $_POST['category_id'] : 0;
    $name = isset($_POST['name']) ? trim($_POST['name']) : '';
    $description = isset($_POST['description']) ? trim($_POST['description']) : '';
    $sort_order = isset($_POST['sort_order']) ? (int) $_POST['sort_order'] : 0;
    $status = isset($_POST['status']) ? (int) $_POST['status'] : 1;
    if (empty($name)) {
      $message = '分类名称不能为空';
      $message_type = 'error';
    } else {
      if ($category_id > 0) {
        $sql = "UPDATE categories SET name = ?, description = ?, sort_order = ?, status = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssiii", $name, $description, $sort_order, $status, $category_id);

        if ($stmt->execute()) {
          $message = '分类已成功更新';
          $message_type = 'success';
        } else {
          $message = '更新分类失败：' . $conn->error;
          $message_type = 'error';
        }
      } else {
        $sql = "INSERT INTO categories (name, description, sort_order, status) VALUES (?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssii", $name, $description, $sort_order, $status);

        if ($stmt->execute()) {
          $message = '分类已成功添加';
          $message_type = 'success';
        } else {
          $message = '添加分类失败：' . $conn->error;
          $message_type = 'error';
        }
      }

      $stmt->close();
    }

    $conn->close();
  }
}
$conn = db_connect();
$sql = "SELECT c.*, (SELECT COUNT(*) FROM links WHERE category_id = c.id) as links_count 
        FROM categories c 
        ORDER BY c.sort_order ASC, c.id ASC";
$result = $conn->query($sql);
$categories = [];

if ($result) {
  while ($row = $result->fetch_assoc()) {
    $categories[] = $row;
  }
}
$conn->close();
$csrf_token = generate_csrf_token();
include_once __DIR__ . '/views/header.php';
?>

<div class="page-header d-flex justify-between align-center">
  <div>
    <h1><i class="iconfont icon-category"></i> 分类管理</h1>
    <p>管理网站的导航分类，组织您的链接内容</p>
  </div>
  <div>
    <button type="button" class="btn btn-primary" id="addCategoryBtn">
      <i class="fas fa-plus"></i> 添加分类
    </button>
  </div>
</div>

<?php if (isset($message)): ?>
  <div class="alert alert-<?php echo $message_type; ?>">
    <i class="fas fa-<?php echo $message_type === 'success' ? 'check-circle' : 'exclamation-circle'; ?>"></i>
    <?php echo xss_clean($message); ?>
  </div>
<?php endif; ?>

<div class="card">
  <div class="card-header">
    <h3 class="card-title">分类列表</h3>
    <div class="card-tools">
      <div class="d-flex align-center">
        <span style="margin-right: 15px;">共 <?php echo count($categories); ?> 个分类</span>
        <div class="search-box" style="position: relative;">
          <i class="iconfont icon-search"
            style="position: absolute; left: 8px; top: 50%; transform: translateY(-50%); color: rgba(255,255,255,0.5);"></i>
          <input type="text" placeholder="搜索分类..." id="searchInput" class="form-control"
            style="padding-left: 30px; width: 200px;">
        </div>
      </div>
    </div>
  </div>
  <div class="card-body">
    <?php if (empty($categories)): ?>
      <div class="empty-data">
        <i class="iconfont icon-category" style="font-size: 48px; margin-bottom: 15px; opacity: 0.5;"></i>
        <div>暂无分类数据</div>
        <button type="button" class="btn btn-primary" style="margin-top: 15px;"
          onclick="document.getElementById('addCategoryBtn').click()">
          <i class="fas fa-plus"></i> 立即添加
        </button>
      </div>
    <?php else: ?>
      <div class="table-responsive">
        <table class="data-table">
          <thead>
            <tr>
              <th width="60">ID</th>
              <th>名称</th>
              <th>描述</th>
              <th width="80">排序</th>
              <th width="80">状态</th>
              <th width="80">链接数</th>
              <th width="150">操作</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($categories as $category): ?>
              <tr class="table-row" data-name="<?php echo strtolower(xss_clean($category['name'])); ?>">
                <td><?php echo $category['id']; ?></td>
                <td>
                  <?php echo xss_clean($category['name']); ?>
                </td>
                <td>
                  <?php echo xss_clean($category['description']) ?: '<span style="opacity: 0.5; font-style: italic;">暂无描述</span>'; ?>
                </td>
                <td><?php echo $category['sort_order']; ?></td>
                <td>
                  <?php if ($category['status'] == 1): ?>
                    <span class="badge success">启用</span>
                  <?php else: ?>
                    <span class="badge error">禁用</span>
                  <?php endif; ?>
                </td>
                <td><?php echo $category['links_count']; ?></td>
                <td>
                  <div class="action-buttons">
                    <button type="button" class="btn btn-sm btn-edit edit-category" data-id="<?php echo $category['id']; ?>"
                      data-name="<?php echo xss_clean($category['name']); ?>"
                      data-description="<?php echo xss_clean($category['description']); ?>"
                      data-sort="<?php echo $category['sort_order']; ?>" data-status="<?php echo $category['status']; ?>"
                      title="编辑分类">
                      <i class="fas fa-edit"></i>
                    </button>
                    <?php if ($category['links_count'] == 0): ?>
                      <button type="button" class="btn btn-sm btn-delete delete-category"
                        data-id="<?php echo $category['id']; ?>" data-name="<?php echo xss_clean($category['name']); ?>"
                        title="删除分类">
                        <i class="fas fa-trash-alt"></i>
                      </button>
                    <?php else: ?>
                      <button type="button" class="btn btn-sm btn-delete disabled"
                        title="该分类下有 <?php echo $category['links_count']; ?> 个链接，无法删除">
                        <i class="fas fa-trash-alt"></i>
                      </button>
                    <?php endif; ?>
                  </div>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    <?php endif; ?>
  </div>
</div>

<div class="modal" id="categoryModal">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h3 class="modal-title" id="modalTitle">
          <i class="fas fa-plus"></i> 添加分类
        </h3>
        <button type="button" class="close-modal"><i class="fas fa-times"></i></button>
      </div>
      <div class="modal-body">
        <form method="post" action="" id="categoryForm">
          <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
          <input type="hidden" name="category_id" id="category_id" value="0">

          <div class="form-group">
            <label for="name">
              分类名称
              <span class="required">*</span>
            </label>
            <input type="text" id="name" name="name" class="form-control" placeholder="请输入分类名称" required>
          </div>

          <div class="form-group">
            <label for="description">
              分类描述
            </label>
            <textarea id="description" name="description" class="form-control" rows="3"
              placeholder="请输入分类描述（可选）"></textarea>
          </div>

          <div class="form-row">
            <div class="form-group col-6">
              <label for="sort_order">
                排序
              </label>
              <input type="number" id="sort_order" name="sort_order" class="form-control" value="0" min="0"
                placeholder="0">
              <div class="form-hint">数字越小排序越靠前</div>
            </div>

            <div class="form-group col-6">
              <label for="status">
                状态
              </label>
              <select id="status" name="status" class="form-control">
                <option value="1">启用</option>
                <option value="0">禁用</option>
              </select>
            </div>
          </div>

          <div class="form-actions text-right">
            <button type="button" class="btn btn-secondary close-modal-btn">
              <i class="fas fa-times"></i> 取消
            </button>
            <button type="submit" class="btn btn-primary">
              <i class="fas fa-save"></i> 保存
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>

<div class="modal" id="confirmModal">
  <div class="modal-dialog" style="max-width: 400px;">
    <div class="modal-content">
      <div class="modal-header">
        <h3 class="modal-title">
          <i class="fas fa-exclamation-triangle" style="color: #ff3b30;"></i> 确认删除
        </h3>
        <button type="button" class="close-modal"><i class="fas fa-times"></i></button>
      </div>
      <div class="modal-body">
        <p>确定要删除分类 "<span id="deleteCategoryName" style="color: #ff3b30; font-weight: bold;"></span>" 吗？</p>
        <p style="opacity: 0.7; font-size: 14px;">此操作不可恢复，请谨慎操作。</p>
      </div>
      <div class="form-actions text-right">
        <button type="button" class="btn btn-secondary" id="cancelDelete">
          <i class="fas fa-times"></i> 取消
        </button>
        <button type="button" class="btn btn-delete" id="confirmDelete">
          <i class="fas fa-trash-alt"></i> 确认删除
        </button>
      </div>
    </div>
  </div>
</div>

<script>
  document.addEventListener('DOMContentLoaded', function () {
    const addCategoryBtn = document.getElementById('addCategoryBtn');
    const categoryModal = document.getElementById('categoryModal');
    const confirmModal = document.getElementById('confirmModal');
    const modalTitle = document.getElementById('modalTitle');
    const categoryForm = document.getElementById('categoryForm');
    const categoryIdInput = document.getElementById('category_id');
    const nameInput = document.getElementById('name');
    const descriptionInput = document.getElementById('description');
    const sortOrderInput = document.getElementById('sort_order');
    const statusInput = document.getElementById('status');
    const searchInput = document.getElementById('searchInput');

    function showModal(modal) {
      modal.style.display = 'block';
      document.body.style.overflow = 'hidden';
    }

    function hideModal(modal) {
      modal.style.display = 'none';
      document.body.style.overflow = '';
    }

    addCategoryBtn.addEventListener('click', function () {
      modalTitle.innerHTML = '<i class="fas fa-plus"></i> 添加分类';
      categoryIdInput.value = '0';
      nameInput.value = '';
      descriptionInput.value = '';
      sortOrderInput.value = '0';
      statusInput.value = '1';
      showModal(categoryModal);
      setTimeout(() => nameInput.focus(), 100);
    });

    document.querySelectorAll('.edit-category').forEach(function (btn) {
      btn.addEventListener('click', function () {
        const id = this.getAttribute('data-id');
        const name = this.getAttribute('data-name');
        const description = this.getAttribute('data-description');
        const sort = this.getAttribute('data-sort');
        const status = this.getAttribute('data-status');

        modalTitle.innerHTML = '<i class="fas fa-edit"></i> 编辑分类';
        categoryIdInput.value = id;
        nameInput.value = name;
        descriptionInput.value = description;
        sortOrderInput.value = sort;
        statusInput.value = status;

        showModal(categoryModal);
        setTimeout(() => nameInput.focus(), 100);
      });
    });

    document.querySelectorAll('.delete-category').forEach(function (btn) {
      btn.addEventListener('click', function () {
        if (this.classList.contains('disabled')) return;

        const id = this.getAttribute('data-id');
        const name = this.getAttribute('data-name');

        document.getElementById('deleteCategoryName').textContent = name;
        showModal(confirmModal);

        document.getElementById('confirmDelete').onclick = function () {
          window.location.href = `categories.php?action=delete&id=${id}&token=<?php echo $csrf_token; ?>`;
        };
      });
    });

    document.getElementById('cancelDelete').addEventListener('click', function () {
      hideModal(confirmModal);
    });

    document.querySelectorAll('.close-modal, .close-modal-btn').forEach(function (btn) {
      btn.addEventListener('click', function () {
        hideModal(categoryModal);
        hideModal(confirmModal);
      });
    });

    window.addEventListener('click', function (event) {
      if (event.target == categoryModal) {
        hideModal(categoryModal);
      }
      if (event.target == confirmModal) {
        hideModal(confirmModal);
      }
    });

    if (searchInput) {
      searchInput.addEventListener('input', function () {
        const searchTerm = this.value.toLowerCase();
        const rows = document.querySelectorAll('.table-row');

        rows.forEach(function (row) {
          const name = row.getAttribute('data-name');
          if (name.includes(searchTerm)) {
            row.style.display = '';
          } else {
            row.style.display = 'none';
          }
        });
      });
    }

    document.addEventListener('keydown', function (e) {
      if (e.key === 'Escape') {
        hideModal(categoryModal);
        hideModal(confirmModal);
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
  });
</script>

<?php include_once __DIR__ . '/views/footer.php'; ?>