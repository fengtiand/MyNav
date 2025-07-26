#!/bin/bash

# MyNav Docker 构建脚本
# 作者：奉天
# 版本：2.0.0

set -e

# 颜色定义
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# 打印带颜色的消息
print_message() {
    echo -e "${2}[$(date +'%Y-%m-%d %H:%M:%S')] $1${NC}"
}

print_info() {
    print_message "$1" "$BLUE"
}

print_success() {
    print_message "$1" "$GREEN"
}

print_warning() {
    print_message "$1" "$YELLOW"
}

print_error() {
    print_message "$1" "$RED"
}

# 检查Docker是否安装
check_docker() {
    if ! command -v docker &> /dev/null; then
        print_error "Docker 未安装，请先安装 Docker"
        exit 1
    fi
    
    if ! command -v docker-compose &> /dev/null; then
        print_error "Docker Compose 未安装，请先安装 Docker Compose"
        exit 1
    fi
}

# 构建镜像
build_image() {
    print_info "开始构建 MyNav Docker 镜像..."
    
    # 获取版本信息
    VERSION=$(grep -o '"version": "[^"]*"' templates/glass/template.json | cut -d'"' -f4 || echo "latest")
    IMAGE_NAME="mynav:${VERSION}"
    
    print_info "构建镜像: $IMAGE_NAME"
    
    docker build -t "$IMAGE_NAME" -t "mynav:latest" .
    
    if [ $? -eq 0 ]; then
        print_success "镜像构建成功: $IMAGE_NAME"
    else
        print_error "镜像构建失败"
        exit 1
    fi
}

# 启动服务
start_services() {
    print_info "启动 MyNav 服务..."
    
    # 创建必要的目录
    mkdir -p database/backups
    mkdir -p admin/assets/uploads
    
    # 设置权限
    chmod -R 755 config database admin/assets 2>/dev/null || true
    
    docker-compose up -d
    
    if [ $? -eq 0 ]; then
        print_success "服务启动成功"
        print_info "前台访问: http://localhost:8080"
        print_info "后台访问: http://localhost:8080/admin"
        print_info "健康检查: http://localhost:8080/healthcheck.php"
    else
        print_error "服务启动失败"
        exit 1
    fi
}

# 停止服务
stop_services() {
    print_info "停止 MyNav 服务..."
    docker-compose down
    print_success "服务已停止"
}

# 查看日志
view_logs() {
    print_info "查看服务日志..."
    docker-compose logs -f
}

# 清理资源
cleanup() {
    print_info "清理 Docker 资源..."
    docker-compose down -v
    docker image prune -f
    print_success "清理完成"
}

# 显示帮助信息
show_help() {
    echo "MyNav Docker 构建脚本"
    echo ""
    echo "用法: $0 [选项]"
    echo ""
    echo "选项:"
    echo "  build     构建 Docker 镜像"
    echo "  start     启动服务"
    echo "  stop      停止服务"
    echo "  restart   重启服务"
    echo "  logs      查看日志"
    echo "  cleanup   清理资源"
    echo "  all       构建镜像并启动服务"
    echo "  help      显示帮助信息"
    echo ""
    echo "示例:"
    echo "  $0 build     # 只构建镜像"
    echo "  $0 start     # 只启动服务"
    echo "  $0 all       # 构建并启动"
}

# 主函数
main() {
    case "$1" in
        "build")
            check_docker
            build_image
            ;;
        "start")
            check_docker
            start_services
            ;;
        "stop")
            check_docker
            stop_services
            ;;
        "restart")
            check_docker
            stop_services
            start_services
            ;;
        "logs")
            check_docker
            view_logs
            ;;
        "cleanup")
            check_docker
            cleanup
            ;;
        "all")
            check_docker
            build_image
            start_services
            ;;
        "help"|"--help"|"-h")
            show_help
            ;;
        "")
            print_warning "请指定操作，使用 '$0 help' 查看帮助"
            show_help
            ;;
        *)
            print_error "未知选项: $1"
            show_help
            exit 1
            ;;
    esac
}

# 执行主函数
main "$@"