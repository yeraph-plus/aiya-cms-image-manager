<?php
if (!defined('ABSPATH')) exit;

if (!class_exists('AYA_Image_Action')) {
    //加载 Composer 依赖
    require_once (__DIR__) . '/composer/vendor/autoload.php';

    //功能配置
    class AYA_Image_Action
    {
        private static $instance;

        public static $default_config = array(
            //基本
            'save_max_width' => '1200',
            'save_upload_path' => 'thumbnail',
            'save_thumb_prefix' => 'thumb_',
            'save_format' => 'jpg', //png webp
            'save_quality' => 96,
            'save_backup_raw_file' => true,
            'thumb_default_width' => 400,
            'thumb_default_height' => 300,
            'font_path' => (__DIR__) . '/font/SourceHanSansCN-Bold.otf',
            'offset_x' => 10,
            'offset_y' => 10,
            //水印参数
            'watermark_type' => '', //text image
            'watermark_position' => 'bottom-right',
            'watermark_image' => (__DIR__) . '/image/default_watermark.png',
            'watermark_font_path' => (__DIR__) . '/font/SourceHanSansCN-Light.otf',
            'watermark_font_text' => 'AIYA-CMS',
            'watermark_font_size' => 24,
            'watermark_font_color' => '#ffffff',
            'watermark_font_opacity' => 80,
            //封面生成器
            'cover_width' => 800,
            'cover_height' => 600,
            'cover_bg_material_in' => array(
                (__DIR__) . '/material/circle-paint-1.png',
                (__DIR__) . '/material/circle-paint-2.png',
                (__DIR__) . '/material/circle-paint-3.png',
                (__DIR__) . '/material/circle-paint-4.png',
                (__DIR__) . '/material/color-splash-1.png',
                (__DIR__) . '/material/color-splash-2.png',
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

        public static $custom_config;

        //实例化
        public static function instance($config)
        {
            if (is_null(self::$instance)) new self();

            self::$custom_config = $config;
        }
        //载入项目文件
        public function __construct()
        {
            self::include_self();
        }
        public function include_self()
        {
            require_once (__DIR__) . '/image-manager.php';
            require_once (__DIR__) . '/image-trans.php';
            require_once (__DIR__) . '/image-draws.php';
            //require_once (__DIR__) . '/image-create-captcha.php';
        }
    }
}
