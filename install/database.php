<?php
class DatabaseInstaller
{
  private $host;
  private $port;
  private $username;
  private $password;
  private $database;
  private $connection;

  public function __construct($host, $port, $username, $password, $database)
  {
    $this->host = $host;
    $this->port = $port;
    $this->username = $username;
    $this->password = $password;
    $this->database = $database;
  }

  public function testConnection()
  {
    try {
      $this->connection = new mysqli($this->host, $this->username, $this->password, '', $this->port);
      if ($this->connection->connect_error) {
        throw new Exception('连接失败: ' . $this->connection->connect_error);
      }
      return true;
    } catch (Exception $e) {
      throw new Exception('数据库连接失败: ' . $e->getMessage());
    }
  }

  public function createDatabase()
  {
    try {
      $sql = "CREATE DATABASE IF NOT EXISTS `{$this->database}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci";
      if (!$this->connection->query($sql)) {
        throw new Exception('创建数据库失败: ' . $this->connection->error);
      }

      $this->connection->select_db($this->database);
      return true;
    } catch (Exception $e) {
      throw new Exception('创建数据库失败: ' . $e->getMessage());
    }
  }

  public function importSQL($sqlFile)
  {
    try {
      if (!file_exists($sqlFile)) {
        throw new Exception('SQL文件不存在: ' . $sqlFile);
      }

      $sql = file_get_contents($sqlFile);

      $sql = preg_replace('/\/\*.*?\*\//s', '', $sql);
      $sql = preg_replace('/--.*$/m', '', $sql);
      $sql = preg_replace('/^\s*$/m', '', $sql);

      $queries = explode(';', $sql);

      foreach ($queries as $query) {
        $query = trim($query);
        if (empty($query))
          continue;

        if (preg_match('/^(SET|\/\*!|ALTER TABLE.*AUTO_INCREMENT)/i', $query)) {
          continue;
        }

        if (!$this->connection->query($query)) {
          if ($this->connection->errno != 1050) {
            throw new Exception('执行SQL失败: ' . $this->connection->error . ' SQL: ' . substr($query, 0, 100));
          }
        }
      }

      return true;
    } catch (Exception $e) {
      throw new Exception('导入SQL失败: ' . $e->getMessage());
    }
  }

  public function createTables()
  {
    $tables = [
      'admin_users' => "
                CREATE TABLE IF NOT EXISTS `admin_users` (
                    `id` int(11) NOT NULL AUTO_INCREMENT,
                    `username` varchar(50) NOT NULL COMMENT '用户名',
                    `password` varchar(255) NOT NULL COMMENT '密码',
                    `nickname` varchar(50) DEFAULT NULL COMMENT '昵称',
                    `email` varchar(100) DEFAULT NULL COMMENT '邮箱',
                    `avatar` varchar(255) DEFAULT NULL COMMENT '头像',
                    `status` tinyint(1) NOT NULL DEFAULT '1' COMMENT '状态 0:禁用 1:启用',
                    `last_login_ip` varchar(50) DEFAULT NULL COMMENT '最后登录IP',
                    `last_login_time` datetime DEFAULT NULL COMMENT '最后登录时间',
                    `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
                    `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
                    PRIMARY KEY (`id`),
                    UNIQUE KEY `username` (`username`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='管理员表'
            ",
      'categories' => "
                CREATE TABLE IF NOT EXISTS `categories` (
                    `id` int(11) NOT NULL AUTO_INCREMENT,
                    `name` varchar(50) NOT NULL COMMENT '分类名称',
                    `description` varchar(255) DEFAULT NULL COMMENT '分类描述',
                    `sort_order` int(11) NOT NULL DEFAULT '0' COMMENT '排序',
                    `status` tinyint(1) NOT NULL DEFAULT '1' COMMENT '状态 0:禁用 1:启用',
                    `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
                    `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
                    PRIMARY KEY (`id`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='分类表'
            ",
      'links' => "
                CREATE TABLE IF NOT EXISTS `links` (
                    `id` int(11) NOT NULL AUTO_INCREMENT,
                    `category_id` int(11) NOT NULL COMMENT '分类ID',
                    `title` varchar(100) NOT NULL COMMENT '标题',
                    `url` varchar(255) NOT NULL COMMENT '链接地址',
                    `description` varchar(255) DEFAULT NULL COMMENT '描述',
                    `icon` varchar(255) DEFAULT NULL COMMENT '图标',
                    `sort_order` int(11) NOT NULL DEFAULT '0' COMMENT '排序',
                    `status` tinyint(1) NOT NULL DEFAULT '1' COMMENT '状态 0:禁用 1:启用',
                    `visits` int(11) NOT NULL DEFAULT '0' COMMENT '访问次数',
                    `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
                    `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
                    PRIMARY KEY (`id`),
                    KEY `category_id` (`category_id`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='链接表'
            ",
      'settings' => "
                CREATE TABLE IF NOT EXISTS `settings` (
                    `id` int(11) NOT NULL AUTO_INCREMENT,
                    `key` varchar(50) NOT NULL COMMENT '键名',
                    `value` text COMMENT '键值',
                    `description` varchar(255) DEFAULT NULL COMMENT '描述',
                    `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
                    `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
                    PRIMARY KEY (`id`),
                    UNIQUE KEY `key` (`key`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='系统设置表'
            ",
      'admin_login_logs' => "
                CREATE TABLE IF NOT EXISTS `admin_login_logs` (
                    `id` int(11) NOT NULL AUTO_INCREMENT,
                    `admin_id` int(11) NOT NULL COMMENT '管理员ID',
                    `login_ip` varchar(50) NOT NULL COMMENT '登录IP',
                    `login_time` datetime NOT NULL COMMENT '登录时间',
                    `login_status` tinyint(1) NOT NULL COMMENT '登录状态 0:失败 1:成功',
                    `login_message` varchar(255) DEFAULT NULL COMMENT '登录信息',
                    PRIMARY KEY (`id`),
                    KEY `admin_id` (`admin_id`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='管理员登录日志'
            ",
      'visits' => "
                CREATE TABLE IF NOT EXISTS `visits` (
                    `id` int(11) NOT NULL AUTO_INCREMENT,
                    `link_id` int(11) DEFAULT NULL COMMENT '链接ID',
                    `visits` int(11) NOT NULL DEFAULT '1' COMMENT '访问次数',
                    `visit_date` date NOT NULL COMMENT '访问日期',
                    `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
                    `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
                    PRIMARY KEY (`id`),
                    UNIQUE KEY `link_id_visit_date` (`link_id`,`visit_date`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='访问统计表'
            "
    ];

    foreach ($tables as $tableName => $sql) {
      if (!$this->connection->query($sql)) {
        throw new Exception("创建表 {$tableName} 失败: " . $this->connection->error);
      }
    }

    return true;
  }

  public function insertDefaultData($adminUser, $adminPass, $adminEmail, $siteName, $siteDesc)
  {
    try {
      $passwordHash = password_hash($adminPass, PASSWORD_DEFAULT);
      $stmt = $this->connection->prepare("INSERT INTO admin_users (username, password, nickname, email, status) VALUES (?, ?, ?, ?, 1)");
      $nickname = '管理员';
      $stmt->bind_param('ssss', $adminUser, $passwordHash, $nickname, $adminEmail);
      $stmt->execute();
      $stmt->close();

      $settings = [
        ['site_name', $siteName, '网站名称'],
        ['site_description', $siteDesc, '网站描述'],
        ['site_keywords', '导航,网址导航,个人导航', '网站关键词'],
        ['site_footer', '© 2025 个人导航系统', '网站底部信息'],
        ['current_template', 'glass', '当前使用的模板'],
        ['show_personal_info', '0', '显示个人信息'],
        ['show_footer', '1', '显示底部信息']
      ];

      foreach ($settings as $setting) {
        $stmt = $this->connection->prepare("INSERT INTO settings (`key`, value, description) VALUES (?, ?, ?)");
        $stmt->bind_param('sss', $setting[0], $setting[1], $setting[2]);
        $stmt->execute();
        $stmt->close();
      }

      $categories = [
        ['常用工具', '日常使用的在线工具', 1],
        ['开发资源', '编程开发相关资源', 2],
        ['学习教程', '各类学习资料和教程', 3],
        ['娱乐休闲', '放松娱乐的网站', 4]
      ];

      foreach ($categories as $category) {
        $stmt = $this->connection->prepare("INSERT INTO categories (name, description, sort_order, status) VALUES (?, ?, ?, 1)");
        $stmt->bind_param('ssi', $category[0], $category[1], $category[2]);
        $stmt->execute();
        $stmt->close();
      }

      $links = [
        [1, '百度', 'https://www.baidu.com', '全球最大的中文搜索引擎', 1],
        [1, '谷歌', 'https://www.google.com', '全球最大的搜索引擎', 2],
        [2, 'GitHub', 'https://github.com', '全球最大的代码托管平台', 1],
        [2, 'Stack Overflow', 'https://stackoverflow.com', '全球最大的程序员问答社区', 2],
        [3, '菜鸟教程', 'https://www.runoob.com', '提供各种编程语言和开发技术的中文教程', 1],
        [4, 'Bilibili', 'https://www.bilibili.com', '国内知名的视频弹幕网站', 1]
      ];

      foreach ($links as $link) {
        $stmt = $this->connection->prepare("INSERT INTO links (category_id, title, url, description, sort_order, status) VALUES (?, ?, ?, ?, ?, 1)");
        $stmt->bind_param('isssi', $link[0], $link[1], $link[2], $link[3], $link[4]);
        $stmt->execute();
        $stmt->close();
      }

      return true;
    } catch (Exception $e) {
      throw new Exception('插入默认数据失败: ' . $e->getMessage());
    }
  }

  public function close()
  {
    if ($this->connection) {
      $this->connection->close();
    }
  }
}
?>