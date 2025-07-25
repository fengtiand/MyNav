
SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


CREATE TABLE IF NOT EXISTS `admin_login_logs` (
  `id` int(11) NOT NULL,
  `admin_id` int(11) NOT NULL COMMENT '管理员ID',
  `login_ip` varchar(50) NOT NULL COMMENT '登录IP',
  `login_time` datetime NOT NULL COMMENT '登录时间',
  `login_status` tinyint(1) NOT NULL COMMENT '登录状态 0:失败 1:成功',
  `login_message` varchar(255) DEFAULT NULL COMMENT '登录信息'
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COMMENT='管理员登录日志';



CREATE TABLE IF NOT EXISTS `admin_users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL COMMENT '用户名',
  `password` varchar(255) NOT NULL COMMENT '密码',
  `nickname` varchar(50) DEFAULT NULL COMMENT '昵称',
  `email` varchar(100) DEFAULT NULL COMMENT '邮箱',
  `avatar` varchar(255) DEFAULT NULL COMMENT '头像',
  `status` tinyint(1) NOT NULL DEFAULT '1' COMMENT '状态 0:禁用 1:启用',
  `last_login_ip` varchar(50) DEFAULT NULL COMMENT '最后登录IP',
  `last_login_time` datetime DEFAULT NULL COMMENT '最后登录时间',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间'
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COMMENT='管理员表';

--
-- 转存表中的数据 `admin_users`
--

INSERT INTO `admin_users` (`id`, `username`, `password`, `nickname`, `email`, `avatar`, `status`, `last_login_ip`, `last_login_time`, `created_at`, `updated_at`) VALUES
(1, 'admin', '$2y$10$9pEV9KYV8NozeA3JxtMKeOCEIdmHqMu4Nd80KteUzAeF5vc4u9tLq', '管理员', 'admin@example.com', '', 1, NULL, NULL, '2025-05-21 23:40:57', '2025-05-23 22:39:42');

-- --------------------------------------------------------

--
-- 表的结构 `categories`
--

CREATE TABLE IF NOT EXISTS `categories` (
  `id` int(11) NOT NULL,
  `name` varchar(50) NOT NULL COMMENT '分类名称',
  `description` varchar(255) DEFAULT NULL COMMENT '分类描述',
  `sort_order` int(11) NOT NULL DEFAULT '0' COMMENT '排序',
  `status` tinyint(1) NOT NULL DEFAULT '1' COMMENT '状态 0:禁用 1:启用',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间'
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COMMENT='分类表';

--
-- 转存表中的数据 `categories`
--

INSERT INTO `categories` (`id`, `name`, `description`, `sort_order`, `status`, `created_at`, `updated_at`) VALUES
(1, '常用工具', '日常使用的在线工具', 1, 1, '2025-05-21 23:47:49', '2025-05-22 00:23:59'),
(2, '开发资源', '编程开发相关资源', 2, 1, '2025-05-21 23:47:49', '2025-05-21 23:47:49'),
(3, '学习教程', '各类学习资料和教程', 3, 1, '2025-05-21 23:47:49', '2025-05-21 23:47:49'),
(4, '娱乐休闲', '放松娱乐的网站', 4, 1, '2025-05-21 23:47:49', '2025-05-21 23:47:49');

-- --------------------------------------------------------

--
-- 表的结构 `links`
--

CREATE TABLE IF NOT EXISTS `links` (
  `id` int(11) NOT NULL,
  `category_id` int(11) NOT NULL COMMENT '分类ID',
  `title` varchar(100) NOT NULL COMMENT '标题',
  `url` varchar(255) NOT NULL COMMENT '链接地址',
  `description` varchar(255) DEFAULT NULL COMMENT '描述',
  `icon` varchar(255) DEFAULT NULL COMMENT '图标',
  `sort_order` int(11) NOT NULL DEFAULT '0' COMMENT '排序',
  `status` tinyint(1) NOT NULL DEFAULT '1' COMMENT '状态 0:禁用 1:启用',
  `visits` int(11) NOT NULL DEFAULT '0' COMMENT '访问次数',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间'
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COMMENT='链接表';

--
-- 转存表中的数据 `links`
--

INSERT INTO `links` (`id`, `category_id`, `title`, `url`, `description`, `icon`, `sort_order`, `status`, `visits`, `created_at`, `updated_at`) VALUES
(1, 1, '百度', 'https://www.baidu.com', '全球最大的中文搜索引擎', NULL, 1, 1, 0, '2025-05-21 23:47:49', '2025-05-23 22:40:10'),
(2, 1, '谷歌', 'https://www.google.com', '全球最大的搜索引擎', NULL, 2, 1, 0, '2025-05-21 23:47:49', '2025-05-23 22:40:13'),
(3, 2, 'GitHub', 'https://github.com', '全球最大的代码托管平台', NULL, 1, 1, 0, '2025-05-21 23:47:49', '2025-05-21 23:47:49'),
(4, 2, 'Stack Overflow', 'https://stackoverflow.com', '全球最大的程序员问答社区', NULL, 2, 1, 0, '2025-05-21 23:47:49', '2025-05-23 22:40:16'),
(5, 3, '菜鸟教程', 'https://www.runoob.com', '提供各种编程语言和开发技术的中文教程', NULL, 1, 1, 0, '2025-05-21 23:47:49', '2025-05-23 22:40:18'),
(6, 4, 'Bilibili', 'https://www.bilibili.com', '国内知名的视频弹幕网站', NULL, 1, 1, 0, '2025-05-21 23:47:49', '2025-05-21 23:47:49');

-- --------------------------------------------------------

--
-- 表的结构 `settings`
--

CREATE TABLE IF NOT EXISTS `settings` (
  `id` int(11) NOT NULL,
  `key` varchar(50) NOT NULL COMMENT '键名',
  `value` text COMMENT '键值',
  `description` varchar(255) DEFAULT NULL COMMENT '描述',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间'
) ENGINE=InnoDB AUTO_INCREMENT=253 DEFAULT CHARSET=utf8mb4 COMMENT='系统设置表';

--
-- 转存表中的数据 `settings`
--

INSERT INTO `settings` (`id`, `key`, `value`, `description`, `created_at`, `updated_at`) VALUES
(1, 'site_name', 'MyNav个人导航系统', '网站名称', '2025-05-21 23:47:49', '2025-05-23 20:46:50'),
(2, 'site_description', '收集和管理您的常用网站链接', '网站描述', '2025-05-21 23:47:49', '2025-05-21 23:47:49'),
(3, 'site_keywords', '导航,网址导航,个人导航', '网站关键词', '2025-05-21 23:47:49', '2025-05-21 23:47:49'),
(4, 'site_footer', '© 2025 个人导航系统', '网站底部信息', '2025-05-21 23:47:49', '2025-05-21 23:47:49'),
(6, 'current_template', 'blue', '当前使用的模板', '2025-05-23 13:38:10', '2025-05-23 21:46:44'),
(24, 'show_personal_info', '0', NULL, '2025-05-23 18:20:52', '2025-05-23 20:53:21'),
(25, 'personal_name', '奉天', NULL, '2025-05-23 18:22:00', '2025-05-23 18:22:00'),
(26, 'personal_title', '全栈工程师', NULL, '2025-05-23 18:22:00', '2025-05-23 20:45:08'),
(27, 'personal_bio', '我说没有你信吗？', NULL, '2025-05-23 18:22:00', '2025-05-23 20:45:08'),
(28, 'personal_avatar', 'https://q1.qlogo.cn/g?b=qq&nk=3345554910&s=640', NULL, '2025-05-23 18:22:00', '2025-05-23 20:59:48'),
(29, 'personal_email', 'admin@ococn.cn', NULL, '2025-05-23 18:22:00', '2025-05-23 20:58:44'),
(30, 'personal_github', 'https://www.ococn.cn/', NULL, '2025-05-23 18:22:00', '2025-05-23 20:58:44'),
(31, 'personal_weibo', 'https://www.ococn.cn/', NULL, '2025-05-23 18:22:00', '2025-05-23 20:58:44'),
(32, 'personal_qq', '3345554910', NULL, '2025-05-23 18:22:00', '2025-05-23 20:58:44'),
(86, 'footer_copyright', 'Copyright © 2019 - 2025 星涵网络工作室 All Rights Reserved.', NULL, '2025-05-23 19:06:37', '2025-05-23 20:52:48'),
(87, 'footer_icp', 'ICP备2025654321号', NULL, '2025-05-23 19:06:37', '2025-05-23 20:53:18'),
(88, 'footer_police', '公网安备123456654321号', NULL, '2025-05-23 19:06:37', '2025-05-23 20:53:18'),
(89, 'footer_statistics', '', NULL, '2025-05-23 19:06:37', '2025-05-23 19:06:37'),
(90, 'footer_custom_html', '', NULL, '2025-05-23 19:06:37', '2025-05-23 19:06:37'),
(91, 'show_footer', '1', NULL, '2025-05-23 19:06:38', '2025-05-23 21:55:43');

-- --------------------------------------------------------

--
-- 表的结构 `visits`
--

CREATE TABLE IF NOT EXISTS `visits` (
  `id` int(11) NOT NULL,
  `link_id` int(11) DEFAULT NULL COMMENT '链接ID',
  `visits` int(11) NOT NULL DEFAULT '1' COMMENT '访问次数',
  `visit_date` date NOT NULL COMMENT '访问日期',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间'
) ENGINE=InnoDB AUTO_INCREMENT=28 DEFAULT CHARSET=utf8mb4 COMMENT='访问统计表';

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admin_login_logs`
--
ALTER TABLE `admin_login_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `admin_id` (`admin_id`);

--
-- Indexes for table `admin_users`
--
ALTER TABLE `admin_users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `links`
--
ALTER TABLE `links`
  ADD PRIMARY KEY (`id`),
  ADD KEY `category_id` (`category_id`);

--
-- Indexes for table `settings`
--
ALTER TABLE `settings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `key` (`key`);

--
-- Indexes for table `visits`
--
ALTER TABLE `visits`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `link_id_visit_date` (`link_id`,`visit_date`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admin_login_logs`
--
ALTER TABLE `admin_login_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=12;
--
-- AUTO_INCREMENT for table `admin_users`
--
ALTER TABLE `admin_users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=2;
--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=5;
--
-- AUTO_INCREMENT for table `links`
--
ALTER TABLE `links`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=7;
--
-- AUTO_INCREMENT for table `settings`
--
ALTER TABLE `settings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=253;
--
-- AUTO_INCREMENT for table `visits`
--
ALTER TABLE `visits`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=28;
