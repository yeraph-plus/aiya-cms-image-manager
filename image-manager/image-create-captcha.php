<?php
if (!defined('ABSPATH')) exit;

//加载库
use Imagine\Imagick\Imagine as ImagickImagine;
use Imagine\Gd\Imagine as GdImagine;

use Imagine\Image\Box;
use Imagine\Image\Point;
use Imagine\Image\Point\Center;
use Imagine\Image\Color;
use Imagine\Image\Font;
use Imagine\Image\Palette\RGB;
use Imagine\Image\ImageInterface;
use Imagine\Image\FontInterface;

class AYA_Imagine_Captcha
{
    private $captcha_config;

    public function __construct()
    {
        $this->captcha_config = array(
            'captcha_possible' => 'abcdefghijklmnopqrstuvwxyz0123456789',
            'captcha_background' => '#ffffff',
            'captcha_color' => '#000000',
            'captcha_offset_x' => 30,
            'captcha_offset_y' => 20,
            'captcha_font' => (__DIR__) . '/font/imageCaptchaFont.ttf',
            'captcha_font_size' => 14,
        );
    }
    //验证码会话
    function captcha_code($length = 4)
    {
        //生成随机字符串
        $possible = $this->captcha_config['captcha_possible'];
        $code = '';

        $i = 0;
        while ($i < $length) {
            $code .= substr($possible, mt_rand(0, strlen($possible) - 1), 1);
            $i++;
        }

        return $code;
    }
    //验证码生成器实例
    public function captcha_generate($captcha)
    {
        $manager = new GdImagine();
        $cap_bg = $this->captcha_config['captcha_background'];
        $cap_color = $this->captcha_config['captcha_color'];
        $cap_off_x = $this->captcha_config['captcha_offset_x'];
        $cap_off_y = $this->captcha_config['captcha_offset_y'];
        $font_size = $this->captcha_config['captcha_font_size'];
        $font_file = $this->captcha_config['captcha_font'];

        //计算图片尺寸
        $bbox = imagettfbbox($font_size, 0, $font_file, $captcha);
        $size_width = $bbox[2] - $bbox[0] + ($cap_off_x * 2);
        $size_height = $bbox[1] - $bbox[7] + ($cap_off_y * 2);

        //创建图片
        $palette = new RGB();
        $box = new Box($size_width, $size_height);
        $background = $palette->color($cap_bg, 90);
        $image = $manager->create($box, $background);

        //将验证码绘制到图像上
        $white = $palette->color($cap_color, 80);

        $font = $manager->font($font_file, $font_size, $white);

        //先roll随机正负
        $roll = (mt_rand(0, 1) == 1) ? 1 : -1;
        $cup_width = rand(0, $cap_off_x) * $roll;
        $cup_height = rand(0, $cap_off_y) * $roll;

        //计算文字位置 预留一点边距
        $center = new Center($box);
        $center_width = $center->getX() - $cup_width;
        $center_height = $center->getY() - $cup_height;
        //生成坐标
        $point = new Point($center_width, $center_height);

        //绘制
        $image->draw()->text($captcha, $font, $point);

        //绘制干扰线
        $line = 5;
        for ($i = 0; $i < $line; $i++) {
            $line_point_x = new Point(rand($cap_off_x, $size_width), rand($cap_off_y, $size_height));
            $line_point_y = new Point(rand($cap_off_y, $size_height), rand($cap_off_x, $size_width));

            $image->draw()->line($line_point_x, $line_point_y, $white);
        }

        //输出到浏览器
        return $image->show('png');
    }
}
