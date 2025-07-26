<?php
/*
 * MyNav Docker 健康检查脚本
 * 用于检查应用程序是否正常运行
 */

// 设置响应头
header('Content-Type: application/json');
header('Cache-Control: no-cache, no-store, must-revalidate');

$health = [
    'status' => 'healthy',
    'timestamp' => date('Y-m-d H:i:s'),
    'checks' => []
];

// 检查PHP运行状态
$health['checks']['php'] = [
    'status' => 'ok',
    'version' => PHP_VERSION
];

// 检查必要的PHP扩展
$required_extensions = ['mysqli', 'session', 'json'];
foreach ($required_extensions as $ext) {
    $health['checks']['extensions'][$ext] = extension_loaded($ext) ? 'ok' : 'error';
    if (!extension_loaded($ext)) {
        $health['status'] = 'unhealthy';
    }
}

// 检查文件权限
$writable_dirs = [
    '/var/www/html/config',
    '/var/www/html/database/backups',
    '/var/www/html/admin/assets'
];

foreach ($writable_dirs as $dir) {
    if (is_dir($dir)) {
        $health['checks']['permissions'][$dir] = is_writable($dir) ? 'ok' : 'warning';
    } else {
        $health['checks']['permissions'][$dir] = 'missing';
    }
}

// 检查数据库连接（如果配置文件存在）
if (file_exists('/var/www/html/config/database.php')) {
    try {
        require_once '/var/www/html/config/database.php';
        
        if (defined('DB_HOST') && defined('DB_USER') && defined('DB_NAME')) {
            $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME, DB_PORT);
            
            if ($conn->connect_error) {
                $health['checks']['database'] = [
                    'status' => 'error',
                    'message' => 'Connection failed: ' . $conn->connect_error
                ];
                $health['status'] = 'unhealthy';
            } else {
                $health['checks']['database'] = [
                    'status' => 'ok',
                    'host' => DB_HOST,
                    'database' => DB_NAME
                ];
                $conn->close();
            }
        } else {
            $health['checks']['database'] = [
                'status' => 'warning',
                'message' => 'Database not configured'
            ];
        }
    } catch (Exception $e) {
        $health['checks']['database'] = [
            'status' => 'error',
            'message' => $e->getMessage()
        ];
        $health['status'] = 'unhealthy';
    }
} else {
    $health['checks']['database'] = [
        'status' => 'warning',
        'message' => 'Database config file not found'
    ];
}

// 检查安装状态
if (file_exists('/var/www/html/install.lock')) {
    $health['checks']['installation'] = [
        'status' => 'ok',
        'message' => 'Application installed'
    ];
} else {
    $health['checks']['installation'] = [
        'status' => 'warning',
        'message' => 'Application not installed yet'
    ];
}

// 设置HTTP状态码
if ($health['status'] === 'unhealthy') {
    http_response_code(503);
} elseif ($health['status'] === 'degraded') {
    http_response_code(200);
} else {
    http_response_code(200);
}

// 输出健康检查结果
echo json_encode($health, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
?>