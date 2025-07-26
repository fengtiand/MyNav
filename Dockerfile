FROM php:8.0-apache

# 设置国内镜像源
RUN sed -i 's/deb.debian.org/mirrors.ustc.edu.cn/g' /etc/apt/sources.list \
    && sed -i 's/security.debian.org/mirrors.ustc.edu.cn/g' /etc/apt/sources.list

# 安装必要的PHP扩展和系统依赖
RUN apt-get update && apt-get install -y \
    libzip-dev \
    zip \
    unzip \
    && docker-php-ext-install mysqli pdo pdo_mysql zip \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# 启用Apache mod_rewrite
RUN a2enmod rewrite

# 设置工作目录
WORKDIR /var/www/html

# 复制项目文件到容器
COPY . /var/www/html/

# 设置文件权限
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html \
    && chmod -R 777 /var/www/html/config \
    && chmod -R 777 /var/www/html/admin/assets \
    && chmod -R 777 /var/www/html/assets

# 创建必要的目录
RUN mkdir -p /var/www/html/database/backups \
    && chown -R www-data:www-data /var/www/html/database \
    && chmod -R 777 /var/www/html/database

# 配置Apache虚拟主机
COPY docker/apache-config.conf /etc/apache2/sites-available/000-default.conf

# 复制健康检查脚本
COPY docker/healthcheck.php /var/www/html/healthcheck.php

# 添加健康检查
HEALTHCHECK --interval=30s --timeout=10s --start-period=60s --retries=3 \
    CMD curl -f http://localhost/healthcheck.php || exit 1

# 安装curl用于健康检查
RUN sed -i 's/deb.debian.org/mirrors.ustc.edu.cn/g' /etc/apt/sources.list \
    && sed -i 's/security.debian.org/mirrors.ustc.edu.cn/g' /etc/apt/sources.list \
    && apt-get update && apt-get install -y curl && rm -rf /var/lib/apt/lists/*

# 暴露端口
EXPOSE 80

# 启动Apache
CMD ["apache2-foreground"]