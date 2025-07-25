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
if (!defined('SECURE_ACCESS')) {
  die('直接访问被拒绝');
}

class SecurityManager
{
  private static $blocked_ips = [];
  private static $max_requests_per_minute = 100;
  private static $session_timeout = 3600;
  private static $strict_csp = false;

  private static $trusted_domains = [
    'https://cdn.bootcdn.net',
    'https://cdnjs.cloudflare.com',
    'https://unpkg.com',
    'https://fonts.googleapis.com',
    'https://fonts.gstatic.com',
    'https://cdn.jsdelivr.net',
    'https://stackpath.bootstrapcdn.com'
  ];

  public static function init()
  {
    self::setSecurityHeaders();
    self::validateRequest();
    self::startSecureSession();
  }

  private static function setSecurityHeaders()
  {
    header('X-Content-Type-Options: nosniff');
    header('X-Frame-Options: DENY');
    header('X-XSS-Protection: 1; mode=block');
    header('Referrer-Policy: strict-origin-when-cross-origin');

    $trusted_domains_str = implode(' ', self::$trusted_domains);

    if (self::$strict_csp) {
      header("Content-Security-Policy: default-src 'self'; script-src 'self' 'unsafe-inline'; style-src 'self' 'unsafe-inline'; img-src 'self' data: https:; font-src 'self' data:; connect-src 'self'");
    } else {
      header("Content-Security-Policy: default-src 'self'; script-src 'self' 'unsafe-inline' {$trusted_domains_str}; style-src 'self' 'unsafe-inline' {$trusted_domains_str}; img-src 'self' data: https:; font-src 'self' data: {$trusted_domains_str}; connect-src 'self' https:");
    }

    header('Strict-Transport-Security: max-age=31536000; includeSubDomains');
  }

  private static function validateRequest()
  {
    if (!in_array($_SERVER['REQUEST_METHOD'], ['GET', 'POST'])) {
      http_response_code(405);
      die('方法不被允许');
    }

    $client_ip = self::getClientIP();
    if (in_array($client_ip, self::$blocked_ips)) {
      http_response_code(403);
      die('访问被拒绝');
    }

    self::checkRateLimit($client_ip);
  }

  private static function startSecureSession()
  {
    ini_set('session.cookie_httponly', 1);
    ini_set('session.cookie_secure', isset($_SERVER['HTTPS']));
    ini_set('session.use_strict_mode', 1);
    session_start();

    if (
      isset($_SESSION['last_activity']) &&
      (time() - $_SESSION['last_activity'] > self::$session_timeout)
    ) {
      session_unset();
      session_destroy();
      session_start();
    }
    $_SESSION['last_activity'] = time();
  }

  public static function getClientIP()
  {
    return $_SERVER['HTTP_X_FORWARDED_FOR'] ??
      $_SERVER['HTTP_X_REAL_IP'] ??
      $_SERVER['REMOTE_ADDR'] ?? '';
  }

  private static function checkRateLimit($ip)
  {
    $current_time = time();
    $limit_file = sys_get_temp_dir() . '/nav_rate_' . md5($ip);

    if (file_exists($limit_file)) {
      $data = json_decode(file_get_contents($limit_file), true);
      if ($data && $current_time - $data['time'] < 60) {
        if ($data['count'] >= self::$max_requests_per_minute) {
          http_response_code(429);
          die('请求过于频繁，请稍后再试');
        }
        $data['count']++;
      } else {
        $data = ['time' => $current_time, 'count' => 1];
      }
    } else {
      $data = ['time' => $current_time, 'count' => 1];
    }

    file_put_contents($limit_file, json_encode($data));
  }

  public static function filterInput($input)
  {
    if (is_array($input)) {
      return array_map([self::class, 'filterInput'], $input);
    }
    $input = trim($input);
    $input = stripslashes($input);
    $input = htmlspecialchars($input, ENT_QUOTES, 'UTF-8');
    return $input;
  }

  public static function validateTemplate($template)
  {
    if (empty($template))
      return false;
    if (strlen($template) > 50)
      return false;
    if (!preg_match('/^[a-zA-Z0-9_-]+$/', $template))
      return false;
    return true;
  }



  public static function setStrictCSP($strict = true)
  {
    self::$strict_csp = $strict;
  }

  public static function addTrustedDomain($domain)
  {
    if (!in_array($domain, self::$trusted_domains)) {
      self::$trusted_domains[] = $domain;
    }
  }

  public static function removeTrustedDomain($domain)
  {
    $key = array_search($domain, self::$trusted_domains);
    if ($key !== false) {
      unset(self::$trusted_domains[$key]);
      self::$trusted_domains = array_values(self::$trusted_domains);
    }
  }

  public static function getTrustedDomains()
  {
    return self::$trusted_domains;
  }

  public static function setMaxRequestsPerMinute($limit)
  {
    self::$max_requests_per_minute = $limit;
  }

  public static function addBlockedIP($ip)
  {
    if (!in_array($ip, self::$blocked_ips)) {
      self::$blocked_ips[] = $ip;
    }
  }

  public static function removeBlockedIP($ip)
  {
    $key = array_search($ip, self::$blocked_ips);
    if ($key !== false) {
      unset(self::$blocked_ips[$key]);
      self::$blocked_ips = array_values(self::$blocked_ips);
    }
  }
}
?>