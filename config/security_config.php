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
  define('SECURE_ACCESS', true);
}

require_once __DIR__ . '/security.php';

class SecurityConfig
{

  public static function enableStrictMode()
  {
    SecurityManager::setStrictCSP(true);
  }

  public static function disableStrictMode()
  {
    SecurityManager::setStrictCSP(false);
  }

  public static function addCDN($domain)
  {
    if (filter_var($domain, FILTER_VALIDATE_URL)) {
      SecurityManager::addTrustedDomain($domain);
      return true;
    }
    return false;
  }

  public static function removeCDN($domain)
  {
    SecurityManager::removeTrustedDomain($domain);
  }

  public static function setRateLimit($requests_per_minute)
  {
    if (is_numeric($requests_per_minute) && $requests_per_minute > 0) {
      SecurityManager::setMaxRequestsPerMinute($requests_per_minute);
      return true;
    }
    return false;
  }

  public static function blockIP($ip)
  {
    if (filter_var($ip, FILTER_VALIDATE_IP)) {
      SecurityManager::addBlockedIP($ip);
      return true;
    }
    return false;
  }

  public static function unblockIP($ip)
  {
    SecurityManager::removeBlockedIP($ip);
  }

  public static function getSecurityStatus()
  {
    return [
      'trusted_domains' => SecurityManager::getTrustedDomains(),
      'client_ip' => SecurityManager::getClientIP()
    ];
  }

  public static function getCommonCDNs()
  {
    return [
      'BootCDN' => 'https://cdn.bootcdn.net',
      'jsDelivr' => 'https://cdn.jsdelivr.net',
      'CDNJS' => 'https://cdnjs.cloudflare.com',
      'UNPKG' => 'https://unpkg.com',
      'Google Fonts' => 'https://fonts.googleapis.com',
      'Google Fonts Static' => 'https://fonts.gstatic.com',
      'StackPath' => 'https://stackpath.bootstrapcdn.com'
    ];
  }
}

SecurityConfig::disableStrictMode();
?>