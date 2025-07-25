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
if (!extension_loaded('gd')) {
    header('Location: captcha_svg.php');
    exit;
}
header('Content-Type: image/png');
header('Cache-Control: no-cache, no-store, must-revalidate');
header('Pragma: no-cache');
header('Expires: 0');
$width = 120;
$height = 40;
$font_size = 16;
$char_count = 4;
$chars = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';
$char_len = strlen($chars);
$captcha_code = '';
for ($i = 0; $i < $char_count; $i++) {
    $captcha_code .= $chars[rand(0, $char_len - 1)];
}
$_SESSION['captcha_code'] = $captcha_code;
$_SESSION['captcha_time'] = time();
$image = imagecreate($width, $height);
$bg_color = imagecolorallocate($image, 240, 245, 255);
$text_colors = [
    imagecolorallocate($image, 50, 80, 150),
    imagecolorallocate($image, 80, 50, 150),
    imagecolorallocate($image, 150, 50, 80),
    imagecolorallocate($image, 50, 150, 80),
    imagecolorallocate($image, 150, 80, 50)
];
$line_color = imagecolorallocate($image, 200, 200, 200);
$noise_color = imagecolorallocate($image, 180, 180, 180);
imagefill($image, 0, 0, $bg_color);
for ($i = 0; $i < 5; $i++) {
    $x1 = rand(0, $width);
    $y1 = rand(0, $height);
    $x2 = rand(0, $width);
    $y2 = rand(0, $height);
    imageline($image, $x1, $y1, $x2, $y2, $line_color);
}
for ($i = 0; $i < 50; $i++) {
    $x = rand(0, $width);
    $y = rand(0, $height);
    imagesetpixel($image, $x, $y, $noise_color);
}
$char_width = $width / $char_count;
for ($i = 0; $i < $char_count; $i++) {
    $char = $captcha_code[$i];
    $color = $text_colors[array_rand($text_colors)];
    $x = $char_width * $i + rand(5, 15);
    $y = rand($height / 2, $height - 5);
    $angle = rand(-15, 15);
    if (function_exists('imagettftext')) {
        $font_paths = [
            __DIR__ . '/assets/fonts/arial.ttf',
            '/System/Library/Fonts/Arial.ttf',
            '/Windows/Fonts/arial.ttf',
            '/usr/share/fonts/truetype/dejavu/DejaVuSans.ttf'
        ];

        $font_used = false;
        foreach ($font_paths as $font_path) {
            if (file_exists($font_path)) {
                imagettftext($image, $font_size, $angle, $x, $y, $color, $font_path, $char);
                $font_used = true;
                break;
            }
        }
        if (!$font_used) {
            imagestring($image, 5, $x, $y - 15, $char, $color);
        }
    } else {
        imagestring($image, 5, $x, $y - 15, $char, $color);
    }
}

imagepng($image);

imagedestroy($image);
?>