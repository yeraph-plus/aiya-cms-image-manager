<?php
if (!defined('ABSPATH')) exit;

/**
 * Plugin Name: AIYA-CMS 图片处理依赖
 * Plugin URI: https://www.yeraph.com/
 * Description: 适配于AIYA-CMS主题的图像处理依赖组件
 * Version: 1.0.0
 * Author: Yeraph Studio
 * Author URI: https://www.yeraph.com/
 * License: GPLv3 or later
 * Requires at least: 6.1
 * Tested up to: 6.5
 * Requires PHP: 7.4
 */

define('AYA_IMAGE_VERSION', '1.0');

//引入图片处理组件
require_once plugin_dir_path(__FILE__) . 'image-manager/setup.php';

/*
//配置
$inst_config = array(
    //基本
    'save_max_width' => 1200,
    'save_upload_path' => 'thumbnail',
    'save_thumb_prefix' => 'thumb_',
    'save_format' => 'jpg', //png webp
    'save_quality' => 96,
    'save_backup_raw_file' => true,
    'thumb_default_width' => 400,
    'thumb_default_height' => 300,
    'font_path' => (__DIR__) . '/image-manager/font/SourceHanSansCN-Bold.otf',
    'offset_x' => 10,
    'offset_y' => 10,
    //水印参数
    'watermark_type' => '', //text image
    'watermark_position' => 'bottom-right',
    'watermark_image' => (__DIR__) . '/image-manager/image/default_watermark.png',
    'watermark_font_path' => (__DIR__) . '/image-manager/font/SourceHanSansCN-Light.otf',
    'watermark_font_text' => 'AIYA-CMS | Yeraph.com - 2024',
    'watermark_font_size' => 24,
    'watermark_font_color' => '#ffffff',
    'watermark_font_opacity' => 80,
    //封面生成器
    'cover_width' => 800,
    'cover_height' => 600,
    'cover_bg_material_in' => array(
        (__DIR__) . '/image-manager/material/circle-paint-1.png',
        (__DIR__) . '/image-manager/material/circle-paint-2.png',
        (__DIR__) . '/image-manager/material/circle-paint-3.png',
        (__DIR__) . '/image-manager/material/circle-paint-4.png',
        (__DIR__) . '/image-manager/material/color-splash-1.png',
        (__DIR__) . '/image-manager/material/color-splash-2.png',
    ),
    'cover_fg_font_size' => 70,
    'cover_fg_font_width' => 100, //估算字体宽度，应根据字体不同
    'cover_fg_element_color_auto' => false,
    'cover_fg_element_color_light' => '#333333',
    'cover_fg_element_color_dark' => '#ffffff',
    'cover_fg_thumb_margin' => 30,
    'cover_fg_thumb_frame_width' => 5, //0则无边框
    'cover_fg_thumb_frame_color' => '#ffffff',
    //海报生成器
    'poster_width' => 900,
    'poster_height' => 1600,
    'background_default_color' => '#FFFAFA',
);
*/
//启动处理器
//AYA_Image_Action::instance($inst_config);

/*
//获取WP正文第一个图片
function get_wp_post_first_image()
{
    $img_url = aya_match_post_first_image(0, false);
    if (empty($img_url)) return false;
    //转换为本地路径
    $img_path = aya_local_path_with_url($img_url, true);

    return $img_path;
}
*/
