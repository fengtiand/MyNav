<?php
session_start();

if (file_exists('install.lock')) {
  die('系统已安装，如需重新安装请删除 install.lock 文件');
}

$step = isset($_GET['step']) ? (int) $_GET['step'] : 1;
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  switch ($step) {
    case 1:
      $step = 2;
      break;
    case 2:
      $db_host = $_POST['db_host'] ?? '';
      $db_port = $_POST['db_port'] ?? '3306';
      $db_name = $_POST['db_name'] ?? '';
      $db_user = $_POST['db_user'] ?? '';
      $db_pass = $_POST['db_pass'] ?? '';

      if (empty($db_host) || empty($db_name) || empty($db_user)) {
        $error = '请填写完整的数据库信息';
      } else {
        try {
          $conn = new mysqli($db_host, $db_user, $db_pass, '', $db_port);
          if ($conn->connect_error) {
            throw new Exception('数据库连接失败: ' . $conn->connect_error);
          }

          $result = $conn->query("CREATE DATABASE IF NOT EXISTS `$db_name` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
          if (!$result) {
            throw new Exception('创建数据库失败: ' . $conn->error);
          }

          $_SESSION['db_config'] = [
            'host' => $db_host,
            'port' => $db_port,
            'name' => $db_name,
            'user' => $db_user,
            'pass' => $db_pass
          ];

          $conn->close();
          $step = 3;
          $success = '数据库连接成功！数据库已创建，请继续下一步。';
        } catch (Exception $e) {
          $error = $e->getMessage();
        }
      }
      break;
    case 3:
      $admin_user = $_POST['admin_user'] ?? '';
      $admin_pass = $_POST['admin_pass'] ?? '';
      $admin_email = $_POST['admin_email'] ?? '';
      $site_name = $_POST['site_name'] ?? 'MyNav个人导航系统';
      $site_desc = $_POST['site_desc'] ?? '收集和管理您的常用网站链接';

      if (empty($admin_user) || empty($admin_pass)) {
        $error = '请填写管理员账号和密码';
      } elseif (strlen($admin_pass) < 6) {
        $error = '密码长度不能少于6位';
      } else {
        try {
          require_once 'install/database.php';
          $db_config = $_SESSION['db_config'];

          $installer = new DatabaseInstaller(
            $db_config['host'],
            $db_config['port'],
            $db_config['user'],
            $db_config['pass'],
            $db_config['name']
          );

          $installer->testConnection();
          $installer->createDatabase();
          $installer->createTables();
          $installer->insertDefaultData($admin_user, $admin_pass, $admin_email, $site_name, $site_desc);
          $installer->close();

          $config_content = "<?php\n";
          $config_content .= "/*\n";
          $config_content .= " * MyNav个人导航系统 2.0.0\n";
          $config_content .= " * 作者：奉天\n";
          $config_content .= " * 官网：www.ococn.cn\n";
          $config_content .= " * \n";
          $config_content .= " * 版权声明：\n";
          $config_content .= " * 本程序为开源软件，仅供学习和个人使用\n";
          $config_content .= " * 禁止使用本程序进行任何形式的商业盈利活动\n";
          $config_content .= " * 如需商业使用，请联系作者获得授权\n";
          $config_content .= " * \n";
          $config_content .= " * Copyright (c) 2025 星涵网络 All rights reserved.\n";
          $config_content .= " */\n\n";
          $config_content .= "define('DB_HOST', '{$db_config['host']}');\n";
          $config_content .= "define('DB_USER', '{$db_config['user']}');\n";
          $config_content .= "define('DB_PASS', '{$db_config['pass']}');\n";
          $config_content .= "define('DB_NAME', '{$db_config['name']}');\n";
          $config_content .= "define('DB_CHARSET', 'utf8mb4');\n";
          $config_content .= "define('DB_PORT', '{$db_config['port']}');\n\n";

          $original_content = file_get_contents('config/database.php');
          $function_start = strpos($original_content, 'function db_connect()');
          if ($function_start !== false) {
            $config_content .= substr($original_content, $function_start);
          }

          file_put_contents('config/database.php', $config_content);
          file_put_contents('install.lock', date('Y-m-d H:i:s'));

          $step = 4;
          $success = '安装完成！';

        } catch (Exception $e) {
          $error = '安装失败: ' . $e->getMessage();
        }
      }
      break;
  }
}
?>
<!DOCTYPE html>
<html lang="zh-CN">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>MyNav 安装向导</title>
  <style>
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }

    body {
      font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      min-height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
    }

    .install-container {
      background: rgba(255, 255, 255, 0.95);
      backdrop-filter: blur(10px);
      border-radius: 20px;
      padding: 40px;
      width: 100%;
      max-width: 600px;
      box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
    }

    .install-header {
      text-align: center;
      margin-bottom: 30px;
    }

    .install-header h1 {
      color: #333;
      font-size: 28px;
      margin-bottom: 10px;
    }

    .install-header p {
      color: #666;
      font-size: 16px;
    }

    .step-indicator {
      display: flex;
      justify-content: center;
      margin-bottom: 30px;
    }

    .step {
      width: 40px;
      height: 40px;
      border-radius: 50%;
      background: #e0e0e0;
      display: flex;
      align-items: center;
      justify-content: center;
      margin: 0 10px;
      font-weight: bold;
      color: #999;
      position: relative;
    }

    .step.active {
      background: #667eea;
      color: white;
    }

    .step.completed {
      background: #4caf50;
      color: white;
    }

    .step:not(:last-child)::after {
      content: '';
      position: absolute;
      right: -20px;
      width: 20px;
      height: 2px;
      background: #e0e0e0;
      top: 50%;
      transform: translateY(-50%);
    }

    .form-group {
      margin-bottom: 20px;
    }

    .form-group label {
      display: block;
      margin-bottom: 8px;
      color: #333;
      font-weight: 500;
    }

    .form-group input {
      width: 100%;
      padding: 12px 16px;
      border: 2px solid #e0e0e0;
      border-radius: 10px;
      font-size: 16px;
      transition: border-color 0.3s;
    }

    .form-group input:focus {
      outline: none;
      border-color: #667eea;
    }

    .form-row {
      display: flex;
      gap: 15px;
    }

    .form-row .form-group {
      flex: 1;
    }

    .btn {
      background: #667eea;
      color: white;
      border: none;
      padding: 12px 30px;
      border-radius: 10px;
      font-size: 16px;
      cursor: pointer;
      transition: background 0.3s;
      text-decoration: none;
      display: inline-block;
      text-align: center;
    }

    .btn:hover {
      background: #5a6fd8;
    }

    .btn-block {
      width: 100%;
    }

    .alert {
      padding: 15px;
      border-radius: 10px;
      margin-bottom: 20px;
    }

    .alert-error {
      background: #ffebee;
      color: #c62828;
      border: 1px solid #ffcdd2;
    }

    .alert-success {
      background: #e8f5e8;
      color: #2e7d32;
      border: 1px solid #c8e6c9;
    }

    .step-content {
      text-align: center;
    }

    .step-content h2 {
      color: #333;
      margin-bottom: 20px;
    }

    .step-content p {
      color: #666;
      margin-bottom: 20px;
      line-height: 1.6;
    }

    .requirements {
      text-align: left;
      background: #f8f9fa;
      padding: 20px;
      border-radius: 10px;
      margin: 20px 0;
    }

    .requirements ul {
      list-style: none;
    }

    .requirements li {
      padding: 5px 0;
      color: #333;
    }

    .requirements li::before {
      content: '✓';
      color: #4caf50;
      font-weight: bold;
      margin-right: 10px;
    }

    .success-icon {
      font-size: 60px;
      color: #4caf50;
      margin-bottom: 20px;
    }

    .btn-group {
      display: flex;
      gap: 15px;
      justify-content: center;
      margin-top: 20px;
    }

    .continue-btn {
      background: #4caf50;
      margin-top: 15px;
    }

    .continue-btn:hover {
      background: #45a049;
    }
  </style>
</head>

<body>
  <div class="install-container">
    <div class="install-header">
      <h1>MyNav 安装向导</h1>
      <p>欢迎使用 MyNav 个人导航系统</p>
    </div>

    <div class="step-indicator">
      <div class="step <?php echo $step >= 1 ? ($step > 1 ? 'completed' : 'active') : ''; ?>">1</div>
      <div class="step <?php echo $step >= 2 ? ($step > 2 ? 'completed' : 'active') : ''; ?>">2</div>
      <div class="step <?php echo $step >= 3 ? ($step > 3 ? 'completed' : 'active') : ''; ?>">3</div>
      <div class="step <?php echo $step >= 4 ? 'active' : ''; ?>">4</div>
    </div>

    <?php if ($error): ?>
      <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <?php if ($success): ?>
      <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
    <?php endif; ?>

    <?php if ($step == 1): ?>
      <div class="step-content">
        <h2>环境检测</h2>
        <p>在开始安装之前，请确保您的服务器环境满足以下要求：</p>

        <div class="requirements">
          <ul>
            <li>PHP 版本 >= 7.0 (当前: <?php echo PHP_VERSION; ?>)</li>
            <li>MySQL 版本 >= 5.6</li>
            <li>MySQLi 扩展 (<?php echo extension_loaded('mysqli') ? '已安装' : '未安装'; ?>)</li>
            <li>文件写入权限 (<?php echo is_writable('.') ? '可写' : '不可写'; ?>)</li>
            <li>Session 功能 (<?php echo function_exists('session_start') ? '支持' : '不支持'; ?>)</li>
          </ul>
        </div>

        <form method="post" action="install.php?step=1">
          <button type="submit" class="btn btn-block">开始安装</button>
        </form>
      </div>
    <?php elseif ($step == 2): ?>
      <div class="step-content">
        <h2>数据库配置</h2>
        <p>请填写您的数据库连接信息：</p>

        <form method="post" action="install.php?step=2">
          <div class="form-row">
            <div class="form-group">
              <label>数据库主机</label>
              <input type="text" name="db_host"
                value="<?php echo isset($_SESSION['db_config']['host']) ? htmlspecialchars($_SESSION['db_config']['host']) : 'localhost'; ?>"
                required>
            </div>
            <div class="form-group">
              <label>端口</label>
              <input type="text" name="db_port"
                value="<?php echo isset($_SESSION['db_config']['port']) ? htmlspecialchars($_SESSION['db_config']['port']) : '3306'; ?>"
                required>
            </div>
          </div>

          <div class="form-group">
            <label>数据库名称</label>
            <input type="text" name="db_name"
              value="<?php echo isset($_SESSION['db_config']['name']) ? htmlspecialchars($_SESSION['db_config']['name']) : ''; ?>"
              placeholder="请输入数据库名称" required>
          </div>

          <div class="form-group">
            <label>数据库用户名</label>
            <input type="text" name="db_user"
              value="<?php echo isset($_SESSION['db_config']['user']) ? htmlspecialchars($_SESSION['db_config']['user']) : ''; ?>"
              placeholder="请输入数据库用户名" required>
          </div>

          <div class="form-group">
            <label>数据库密码</label>
            <input type="password" name="db_pass"
              value="<?php echo isset($_SESSION['db_config']['pass']) ? htmlspecialchars($_SESSION['db_config']['pass']) : ''; ?>"
              placeholder="请输入数据库密码">
          </div>

          <button type="submit" class="btn btn-block">测试连接</button>
        </form>

        <div style="margin-top: 15px;">
          <a href="http://doc.xhus.cn/web/#/612172001/256951061" target="_blank">数据库安装教程</a>
        </div>

        <?php if (isset($_SESSION['db_config']) && $success): ?>
          <a href="install.php?step=3" class="btn btn-block continue-btn">继续下一步</a>
        <?php endif; ?>
      </div>
    <?php elseif ($step == 3): ?>
      <div class="step-content">
        <h2>系统配置</h2>
        <p>请设置管理员账号和网站基本信息：</p>

        <form method="post" action="install.php?step=3">
          <div class="form-group">
            <label>管理员用户名</label>
            <input type="text" name="admin_user" placeholder="请输入管理员用户名" required>
          </div>

          <div class="form-group">
            <label>管理员密码</label>
            <input type="password" name="admin_pass" placeholder="请输入管理员密码（至少6位）" required>
          </div>

          <div class="form-group">
            <label>管理员邮箱</label>
            <input type="email" name="admin_email" placeholder="请输入管理员邮箱">
          </div>

          <div class="form-group">
            <label>网站名称</label>
            <input type="text" name="site_name" value="MyNav个人导航系统" required>
          </div>

          <div class="form-group">
            <label>网站描述</label>
            <input type="text" name="site_desc" value="收集和管理您的常用网站链接" required>
          </div>

          <button type="submit" class="btn btn-block">开始安装</button>
        </form>
      </div>
    <?php elseif ($step == 4): ?>
      <div class="step-content">
        <div class="success-icon">🎉</div>
        <h2>安装完成</h2>
        <p>恭喜！MyNav 个人导航系统已成功安装。</p>

        <div class="requirements">
          <ul>
            <li>前台地址：<a href="index.php" target="_blank">访问前台</a></li>
            <li>后台地址：<a href="admin/login.php" target="_blank">进入后台</a></li>
            <li>安装时间：<?php echo date('Y-m-d H:i:s'); ?></li>
          </ul>
        </div>

        <div class="btn-group">
          <a href="/" class="btn">访问前台</a>
          <a href="/admin" class="btn">进入后台</a>
        </div>

        <p style="margin-top: 20px; color: #666; font-size: 14px;">
          为了安全，建议删除 install.php 文件
        </p>
      </div>
    <?php endif; ?>
  </div>
</body>

</html>
