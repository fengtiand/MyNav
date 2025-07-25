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
session_start();

require_once __DIR__ . '/includes/auth.php';

$page_title = '关于程序';

$breadcrumb = [
  ['text' => '首页', 'url' => 'index.php'],
  ['text' => '关于程序', 'url' => '']
];

include_once __DIR__ . '/views/header.php';
?>

<div class="about-page">
  <div class="card">
    <div class="card-header">
      <h3 class="card-title">
        <i class="fas fa-info-circle"></i>
        关于程序
      </h3>
    </div>
    <div class="card-body">
      <div class="about-content">
        <div class="program-info">
          <div class="program-logo">
            <i class="fas fa-compass"></i>
          </div>
          <h2 class="program-name">MyNav个人导航系统</h2>
          <p class="program-version">版本 2.0.0</p>
        </div>

        <div class="info-section">
          <h3><i class="fas fa-file-alt"></i> 程序介绍</h3>
          <p>这是一个基于PHP+MySQL开发的个人导航系统，旨在为用户提供一个简洁、美观、功能完善的个人网址导航平台。系统支持多种主题模板，包括玻璃态、复古90年代、极简主义等多种风格，满足不同用户的审美需求。</p>

          <h4>主要功能特性：</h4>
          <ul class="feature-list">
            <li><i class="fas fa-check"></i> 多主题模板支持（Glass、Retro、Minimal、Neumorphism等）</li>
            <li><i class="fas fa-check"></i> 响应式设计，完美适配PC和移动端</li>
            <li><i class="fas fa-check"></i> 分类管理，支持无限层级分类</li>
            <li><i class="fas fa-check"></i> 链接管理，支持图标、描述、访问统计</li>
            <li><i class="fas fa-check"></i> 个人信息展示，支持头像、社交链接</li>
            <li><i class="fas fa-check"></i> 访问统计分析，实时监控网站数据</li>
            <li><i class="fas fa-check"></i> 数据备份恢复，保障数据安全</li>
            <li><i class="fas fa-check"></i> 系统日志记录，便于问题排查</li>
          </ul>
        </div>

        <div class="info-section">
          <h3><i class="fas fa-user"></i> 作者信息</h3>
          <div class="author-info">
            <div class="author-details">
              <p><strong>作者：</strong>奉天</p>
              <p><strong>工作室：</strong>星涵网络工作室</p>
              <p><strong>官网：</strong><a href="https://www.ococn.cn" target="_blank">www.ococn.cn</a></p>
              <p><strong>开发时间：</strong>2025年5月</p>
            </div>
          </div>
        </div>

        <div class="info-section">
          <h3><i class="fas fa-code-branch"></i> 开源说明</h3>
          <div class="license-info">
            <div class="license-box">
              <h4><i class="fas fa-balance-scale"></i> 使用许可</h4>
              <p>本程序采用开源许可协议，允许个人和非营利组织免费使用、修改和分发。</p>

              <h4><i class="fas fa-exclamation-triangle"></i> 重要声明</h4>
              <div class="warning-box">
                <p><strong>禁止商业盈利：</strong>严禁使用本程序进行任何形式的商业盈利活动，包括但不限于：</p>
                <ul>
                  <li>销售本程序或基于本程序的衍生产品</li>
                  <li>提供基于本程序的付费服务</li>
                  <li>在本程序中植入广告进行盈利</li>
                  <li>其他任何形式的商业化运营</li>
                </ul>
                <p class="legal-notice"><strong>违者必究：</strong>如发现违反上述规定的行为，作者保留追究法律责任的权利。</p>
              </div>

              <h4><i class="fas fa-heart"></i> 支持作者</h4>
              <p>如果您觉得这个程序对您有帮助，欢迎：</p>
              <ul style="margin-left: 20px;">
                <li>为项目点赞和分享</li>
                <li>提交Bug报告和功能建议</li>
                <li>参与代码贡献和文档完善</li>
                <li>访问我们的官网了解更多作品</li>
              </ul>
            </div>
          </div>
        </div>

        <div class="info-section">
          <h3><i class="fas fa-cogs"></i> 技术栈</h3>
          <div class="tech-stack">
            <div class="tech-item">
              <i class="fab fa-php"></i>
              <span>PHP 7.4+</span>
            </div>
            <div class="tech-item">
              <i class="fas fa-database"></i>
              <span>MySQL 5.7+</span>
            </div>
            <div class="tech-item">
              <i class="fab fa-html5"></i>
              <span>HTML5</span>
            </div>
            <div class="tech-item">
              <i class="fab fa-css3-alt"></i>
              <span>CSS3</span>
            </div>
            <div class="tech-item">
              <i class="fab fa-js"></i>
              <span>JavaScript</span>
            </div>
          </div>
        </div>

        <div class="info-section">
          <h3><i class="fas fa-envelope"></i> 联系方式</h3>
          <p>如有问题或建议，请通过以下方式联系：</p>
          <ul class="contact-list">
            <li><i class="fas fa-globe"></i> 官网：<a href="https://www.ococn.cn" target="_blank">www.ococn.cn</a></li>
            <li><i class="fas fa-envelope"></i> 邮箱：<a href="mailto:admin@ococn.cn" target="_blank">admin@ococn.cn</a>
            </li>
          </ul>
        </div>
      </div>
    </div>
  </div>
</div>


<?php include_once __DIR__ . '/views/footer.php'; ?>