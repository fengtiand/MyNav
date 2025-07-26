# 本地开发环境配置指南

由于网络连接问题无法下载Docker镜像，建议使用以下本地环境配置：

## 方案一：使用XAMPP（推荐）

1. 下载并安装XAMPP：https://www.apachefriends.org/zh_cn/index.html
2. 启动Apache和MySQL服务
3. 将项目文件复制到 `C:\xampp\htdocs\MyNav\`
4. 访问 `http://localhost/MyNav`

## 方案二：使用WampServer

1. 下载并安装WampServer：https://www.wampserver.com/
2. 启动所有服务
3. 将项目文件复制到 `C:\wamp64\www\MyNav\`
4. 访问 `http://localhost/MyNav`

## 数据库配置

1. 打开phpMyAdmin（通常在 http://localhost/phpmyadmin）
2. 创建数据库 `mynav`
3. 修改项目中的数据库连接配置

## 环境要求

- PHP 8.0+
- MySQL 5.7+
- Apache 2.4+
- 启用以下PHP扩展：
  - mysqli
  - pdo_mysql
  - curl
  - json
  - mbstring
  - xml
  - zip
  - gd

## 注意事项

- 确保文件权限正确
- 检查Apache配置中的DocumentRoot设置
- 如需使用.htaccess，确保启用mod_rewrite模块