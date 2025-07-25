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

define('DB_HOST', 'localhost');
define('DB_USER', 'mynav');
define('DB_PASS', 'NfAnpG6J6nEf3eYa');
define('DB_NAME', 'mynav');
define('DB_CHARSET', 'utf8mb4');
define('DB_PORT', '3306');

function db_connect()
{
  $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME, DB_PORT);


  if ($conn->connect_error) {
    die("数据库连接失败: " . $conn->connect_error);
  }


  $conn->set_charset(DB_CHARSET);

  return $conn;
}

function db_query($sql, $params = [])
{
  $conn = db_connect();
  $stmt = $conn->prepare($sql);

  if (!empty($params)) {
    $types = '';
    $values = [];

    foreach ($params as $param) {
      if (is_int($param)) {
        $types .= 'i';
      } elseif (is_float($param)) {
        $types .= 'd';
      } elseif (is_string($param)) {
        $types .= 's';
      } else {
        $types .= 'b';
      }
      $values[] = $param;
    }

    $stmt->bind_param($types, ...$values);
  }

  $stmt->execute();
  $result = $stmt->get_result();
  $stmt->close();
  $conn->close();

  return $result;
}

function db_fetch_one($sql, $params = [])
{
  $result = db_query($sql, $params);
  return $result->fetch_assoc();
}

function db_fetch_all($sql, $params = [])
{
  $result = db_query($sql, $params);
  return $result->fetch_all(MYSQLI_ASSOC);
}
function db_execute($sql, $params = [])
{
  $conn = db_connect();
  $stmt = $conn->prepare($sql);

  if (!empty($params)) {
    $types = '';
    $values = [];

    foreach ($params as $param) {
      if (is_int($param)) {
        $types .= 'i';
      } elseif (is_float($param)) {
        $types .= 'd';
      } elseif (is_string($param)) {
        $types .= 's';
      } else {
        $types .= 'b';
      }
      $values[] = $param;
    }

    $stmt->bind_param($types, ...$values);
  }

  $stmt->execute();
  $affected_rows = $stmt->affected_rows;
  $insert_id = $stmt->insert_id;
  $stmt->close();
  $conn->close();

  return [
    'affected_rows' => $affected_rows,
    'insert_id' => $insert_id
  ];
}