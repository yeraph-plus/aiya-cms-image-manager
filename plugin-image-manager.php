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
define('AYA_IMAGE_PATH', plugin_dir_path(__FILE__));

//引入设置框架
require_once AYA_IMAGE_PATH . 'image-manager/setup.php';