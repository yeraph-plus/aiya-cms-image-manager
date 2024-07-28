<?php
if (!defined('ABSPATH')) exit;

use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\GDLibRenderer;

class AYA_QrCode
{
    public function __construct($url = '', $with = 300)
    {
    }
    //检查 Imagick 是否可用
    public static function can_use_imagick()
    {
        if (!extension_loaded('imagick')) return false;
        //if (!extension_loaded('gd')) return false;

        return true;
    }
    //生成二维码
    public function qrcode_generate($url = '', $with = 300, $path = '')
    {
        //验证地址md5
        $url_file = 'qrcode_' . md5($url) . '.png';

        if (file_exists($url_file)) {
            return $url_file;
        }

        //使用驱动
        if (self::can_use_imagick()) {
            self::aya_qrcode_generate_by_imagick($url, $with, $url_file);
        } else {
            self::aya_qrcode_generate_by_gd($url, $with, $url_file);
        }

        return $url_file;
    }
    //生成二维码（GD）
    public function aya_qrcode_generate_by_gd($url = '', $with = 300, $path = '')
    {
        $renderer = new GDLibRenderer($with);
        $writer = new Writer($renderer);
        //写入文件
        $writer->writeFile($url, $path);
    }
    //生成二维码
    public function aya_qrcode_generate_by_imagick($url = '', $with = 300, $path = '')
    {
        $renderer = new ImageRenderer(
            new RendererStyle(400),
            new ImagickImageBackEnd()
        );
        $writer = new Writer($renderer);
        $writer->writeFile('Hello World!', 'qrcode.png');
    }
    //装饰器
    public function aya_qrcode_overlay($text = '')
    {
    }
}
