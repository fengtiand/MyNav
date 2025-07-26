# MyNav Docker 部署指南

本文档介绍如何使用 Docker 部署 MyNav 个人导航系统。

## 🐳 快速开始

### 方法一：使用 Docker Compose（推荐）

1. **克隆项目**
```bash
git clone https://github.com/fengtiand/MyNav.git
cd MyNav
```

2. **启动服务**
```bash
docker-compose up -d
```

3. **访问应用**
- 前台：http://localhost:8080
- 后台：http://localhost:8080/admin

### 方法二：使用预构建镜像

```bash
# 拉取镜像
docker pull ghcr.io/fengtiand/mynav:latest

# 运行容器
docker run -d \
  --name mynav \
  -p 8080:80 \
  -v $(pwd)/config:/var/www/html/config \
  -v $(pwd)/database/backups:/var/www/html/database/backups \
  ghcr.io/fengtiand/mynav:latest
```

### 方法三：本地构建

```bash
# 构建镜像
docker build -t mynav:local .

# 运行容器
docker run -d \
  --name mynav \
  -p 8080:80 \
  mynav:local
```

## 📋 环境变量

| 变量名 | 默认值 | 说明 |
|--------|--------|------|
| `MYSQL_ROOT_PASSWORD` | `mynav_root_2024` | MySQL root 密码 |
| `MYSQL_DATABASE` | `mynav` | 数据库名称 |
| `MYSQL_USER` | `mynav` | 数据库用户名 |
| `MYSQL_PASSWORD` | `mynav_password_2024` | 数据库密码 |

## 📁 数据持久化

重要的数据目录已通过 volumes 进行持久化：

- `./config` - 配置文件
- `./database/backups` - 数据库备份
- `./admin/assets/uploads` - 上传文件
- `mysql_data` - MySQL 数据

## 🔧 配置说明

### 数据库配置

容器启动后，需要修改 `config/database.php` 中的数据库连接信息：

```php
define('DB_HOST', 'mysql');  // 容器服务名
define('DB_USER', 'mynav');
define('DB_PASS', 'mynav_password_2024');
define('DB_NAME', 'mynav');
define('DB_PORT', '3306');
```

### 首次安装

1. 访问 http://localhost:8080/install.php
2. 按照安装向导完成配置
3. 数据库信息使用上述配置

## 🚀 生产环境部署

### 1. 修改默认密码

编辑 `docker-compose.yml`，修改数据库密码：

```yaml
environment:
  MYSQL_ROOT_PASSWORD: your_secure_root_password
  MYSQL_PASSWORD: your_secure_password
```

### 2. 使用外部数据库

如果使用外部 MySQL 服务，可以移除 `docker-compose.yml` 中的 mysql 服务，并修改数据库连接配置。

### 3. 反向代理

推荐使用 Nginx 作为反向代理：

```nginx
server {
    listen 80;
    server_name your-domain.com;
    
    location / {
        proxy_pass http://localhost:8080;
        proxy_set_header Host $host;
        proxy_set_header X-Real-IP $remote_addr;
        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
        proxy_set_header X-Forwarded-Proto $scheme;
    }
}
```

### 4. HTTPS 配置

使用 Let's Encrypt 获取 SSL 证书：

```bash
# 安装 certbot
sudo apt install certbot python3-certbot-nginx

# 获取证书
sudo certbot --nginx -d your-domain.com
```

## 🛠️ 常用命令

```bash
# 查看日志
docker-compose logs -f web

# 重启服务
docker-compose restart

# 停止服务
docker-compose down

# 更新镜像
docker-compose pull
docker-compose up -d

# 进入容器
docker exec -it mynav-web bash

# 数据库备份
docker exec mynav-mysql mysqldump -u root -p mynav > backup.sql

# 数据库恢复
docker exec -i mynav-mysql mysql -u root -p mynav < backup.sql
```

## 🔍 故障排除

### 1. 端口冲突

如果 8080 端口被占用，修改 `docker-compose.yml` 中的端口映射：

```yaml
ports:
  - "8081:80"  # 改为其他端口
```

### 2. 权限问题

确保挂载的目录有正确的权限：

```bash
sudo chown -R www-data:www-data ./config
sudo chmod -R 755 ./config
```

### 3. 数据库连接失败

检查数据库服务是否正常启动：

```bash
docker-compose logs mysql
```

## 📝 注意事项

1. 首次启动可能需要等待数据库初始化完成
2. 生产环境请务必修改默认密码
3. 定期备份重要数据
4. 建议使用 HTTPS 访问

## 🆕 更新升级

```bash
# 拉取最新镜像
docker-compose pull

# 重新创建容器
docker-compose up -d

# 清理旧镜像
docker image prune
```

---

如有问题，请查看项目文档或提交 Issue。