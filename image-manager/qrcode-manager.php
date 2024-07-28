<?php
if (!defined('ABSPATH')) exit;

use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\GDLibRenderer;
use BaconQrCode\Writer;

use BaconQrCode\Encoder\QrCode;
use BaconQrCode\Common\Eye;
use BaconQrCode\Common\Mode;
use BaconQrCode\Renderer\Image\Png;
use BaconQrCode\Renderer\Image\ImagickImageBackEnd;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;

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
    public function aya_qrcode_generate($url = '', $with = 300, $path = '')
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
        // 要编码的信息
        $text = 'https://www.example.com';

        // 创建一个PNG图像渲染器
        $renderer = new Png(300);

        // 设置二维码的尺寸
        $renderer->setHeight();
        $renderer->setWidth(300);

        // 创建一个二维码生成器并设置渲染器
        $writer = new Writer($renderer);

        // 图标文件路径
        $logoPath = 'path/to/your/logo.png';

        // 创建一个Overlay装饰器并设置Logo
        $overlay = new Overlay(
            new ImageBackEnd(),
            $logoPath
        );

        // 设置Logo的大小（可选）
        $overlay->setGetSize(100); // 设置Logo的大小为100x100像素

        // 将装饰器添加到渲染器
        $renderer->addResource($overlay);

        // 生成的二维码文件名
        $filename = 'qrcode_with_logo.png';

        // 将二维码写入文件
        $writer->writeFile($text, $filename);

        echo "带图标的二维码已生成：$filename";
    }
}
