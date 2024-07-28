<?php
if (!defined('ABSPATH')) exit;

//加载 Composer 依赖
require_once (__DIR__) . '/composer/vendor/autoload.php';

//功能配置
class AYA_Image_Action
{
    private static $instance;
    //实例化
    public static function instance()
    {
        if (is_null(self::$instance)) new self();
    }
    //初始化
    public function __construct()
    {
        self::include_self();
    }
    //载入项目文件
    public function include_self()
    {
        require_once (__DIR__) . '/image-manager.php';
        require_once (__DIR__) . '/image-trans.php';
        require_once (__DIR__) . '/image-draws.php';
        //require_once (__DIR__) . '/image-create-captcha.php';
    }
    //图像转换默认的配置参数
    public function self_config()
    {
        $config = array(
            //基本
            'save_max_width' => '1200',
            'save_upload_path' => 'thumbnail',
            'save_thumb_prefix' => 'thumb_',
            'save_format' => 'jpg', //png webp
            'save_quality' => 86,
            'thumb_default_width' => 400,
            'thumb_default_height' => 300,
            'font_path' => (__DIR__) . '/font/GenJyuuGothicL-Bold.ttf',
            'offset_x' => 10,
            'offset_y' => 10,
            //水印参数
            'watermark_type' => 'text', //text image
            'watermark_position' => 'center-center',
            'watermark_image' => (__DIR__) . '/image/default_watermark.png',
            'watermark_font_text' => 'AIYA-CMS | Yeraph.com [2024]',
            'watermark_font_path' => (__DIR__) . '/font/GenJyuuGothicL-Light.ttf',
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
            'cover_bg_default_color' => '#FFFAFA',
            'cover_fg_font_size' => 80,
            'cover_fg_font_width' => 105, //估算字体宽度，应根据字体不同
            'cover_fg_element_color_auto' => false,
            'cover_fg_element_color_light' => '#333333',
            'cover_fg_element_color_dark' => '#ffffff',
            'cover_fg_thumb_margin' => 30,
            'cover_fg_thumb_frame_width' => 5, //0则无边框
            'cover_fg_thumb_frame_color' => '#ffffff',
            //海报生成器
            'poster_width' => 900,
            'poster_height' => 1600,
        );

        return $config;
    }
}

//启动
AYA_Image_Action::instance();

//获取WP正文第一个图片
function get_wp_post_first_image()
{
    $img_url = aya_match_post_first_image(0, false);
    if (empty($img_url)) return false;
    //转换为本地路径
    $img_path = aya_local_path_with_url($img_url, true);

    return $img_path;
}
