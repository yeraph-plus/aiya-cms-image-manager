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

if (!class_exists('AYA_Imagine_Trans')) {
    class AYA_Imagine_Trans extends AYA_Image_Manager
    {
        //入口方法
        public function image_generate($file_or_url = '', $func_type = '', $backup_type = false)
        {
            //验证文件
            $this_image = parent::image_is_url($file_or_url);
            //文件不存在
            if ($this_image == false) return false;

            //打开图片
            if ($this_image == 'is_url') {
                $image = parent::image_load_remote($file_or_url);
                $is_url = true;
            } else {
                $image = parent::image_open($this_image);
                $is_url = false;
            }

            //打开失败
            if (!is_object($image)) return $image;

            $save_param = parent::image_save_param_array($this_image, '', $is_url);
            //进行备份
            if ($backup_type && $this_image != 'is_url') {
                $image_backup = parent::image_backup($this_image, false);
            }


            //选择需要的生成器
            switch ($func_type) {
                case 'convert':
                    $func = self::image_only_convert_generate($image, $save_param['save_path'], $save_param['save_quality']);
                    break;
                case 'auto_scale':
                    $func = self::image_auto_scale_generate($image, $save_param['save_path'], $save_param['save_quality']);
                    break;
                case 'watermark':
                    $func = self::image_watermark_generate($image, $save_param['save_path'], $save_param['save_quality']);
                    break;
                default:
                    $func = false;
            }
            //返回
            return ($func) ? $save_param['save_path'] : 'ERROR - Input parameter generation failure.';
        }
        //图像裁剪缩放实例
        public function image_thumb_generate($image_local_url, $width = 0, $height = 0)
        {
            //如果使用转换缩略图参数
            if (strpos($width, 'x') !== false) {
                //分割参数
                list($width, $height) = explode('x', $width);
            }
            //如果宽高均未设置parent::get_config
            if ($width === 0 && $height === 0) {
                $width = parent::get_config('thumb_default_width');
                $height = parent::get_config('thumb_default_height');
            }

            //保存位置
            $thumb_name = parent::get_config('save_thumb_prefix') . md5($image_local_url, false) . parent::get_config('save_format');
            $thumb_dir = parent::local_mkdir(parent::get_config('save_upload_path') . '/' . $width . 'x' . $height);
            $thumb_saved_path = $thumb_dir . '/' . $thumb_name;
            //已生成，直接返回
            if (file_exists($thumb_saved_path)) {
                return $thumb_saved_path;
            }

            //验证文件
            $this_image = parent::image_is_url($image_local_url);
            //不允许直接处理远程图片
            if ($this_image == false || $this_image == 'is_url') return false;

            //打开图片
            $image = parent::image_open($this_image);

            if (!is_object($image)) return false;


            //获得尺寸
            $image_size = parent::image_size($image);
            $size = new Box($image_size['w'], $image_size['h']);

            //计算缩放尺寸
            $ratio = parent::image_scale_ratio($width, $height, $image_size['w'], $image_size['h'], true);

            $size = $size->scale($ratio);

            //缩放图像
            $image->resize($size);

            //如果宽高任意不为零，则裁剪
            if ($width !== 0 && $height !== 0) {
                //计算裁剪的起始点
                $crop_width = ($size->getWidth() - $width) / 2;
                $crop_height = ($size->getHeight() - $height) / 2;
                $crop_point = new Point($crop_width, $crop_height);
                $crop_size = new Box($width, $height);
                //裁剪图像
                $image->crop($crop_point, $crop_size);
            }

            //保存图像
            $save_quality = parent::image_save_quality(parent::get_config('save_format'));
            $image->save($thumb_saved_path, $save_quality);

            return $thumb_saved_path;
        }
        //图片转换实例
        public function image_only_convert_generate($image, $save_path, $save_quality)
        {
            if (!is_object($image)) return false;

            //保存图像
            $image->save($save_path, $save_quality);

            return true;
        }
        //超宽图片自动缩放实例
        public function image_auto_scale_generate($image, $save_path, $save_quality)
        {
            if (!is_object($image)) return false;

            $max_width = parent::get_config('save_max_width');

            //如果设置为0则不执行
            if ($max_width == 0) return true;

            //检查尺寸
            $image_size = $image->getSize();
            $origin_width = $image_size->getWidth();
            $origin_height = $image_size->getHeight();
            //$size = new Box($origin_width, $origin_height);

            //如果尺寸大于最大值，执行缩放
            if ($origin_width > $max_width) {
                //计算缩放比例
                $size = $image_size->scale($max_width / $origin_width);
                //缩放图像
                $image->resize($size);
                //保存图像
                $image->save($save_path, $save_quality);
            }

            return true;
        }
        //图片水印实例
        public function image_watermark_generate($image, $save_path, $save_quality)
        {
            if (!is_object($image)) return false;


            $watermark_type = parent::get_config('watermark_type');

            //如果设置为0则不执行
            if ($watermark_type == '') return true;

            //获得尺寸
            $image_size = parent::image_size($image);

            //检查图片水印还是文字水印
            if ($watermark_type == 'image') {
                //图片水印
                $mark_image = parent::image_open(parent::get_config('watermark_image'));
                //获得尺寸
                $mark_size = parent::image_size($mark_image);
            } else {
                //文字水印
                $watermark_text = parent::get_config('watermark_font_text');
                $watermark_text_md5 = md5($watermark_text, false);

                $watermark_file = parent::image_cache_path('watermark_' . $watermark_text_md5 . '.png');

                //不存在水印文件则创建
                if (!file_exists($watermark_file)) {
                    //获取字体
                    $watermark_font_file = parent::get_config('watermark_font_path');
                    $watermark_size = parent::get_config('watermark_font_size');
                    $watermark_color = parent::get_config('watermark_font_color');
                    $watermark_opacity = parent::get_config('watermark_font_opacity');
                    //检查字体文件位置
                    if (!file_exists($watermark_font_file)) return false;

                    //生成水印图片
                    $new_manager = new GdImagine();
                    $new_palette = new RGB();

                    //加载字体
                    $white_point = new Point(0, 0);
                    $white_color = $new_palette->color($watermark_color, $watermark_opacity);
                    $white_background = $new_palette->color(array(0, 0, 0), 0);

                    $white_font = $new_manager->font($watermark_font_file, $watermark_size, $white_color);

                    $white_size = $white_font->box($watermark_text);
                    $white_width = $white_size->getWidth();
                    $white_height = $white_size->getHeight();

                    $white_box = new Box($white_width, $white_height);

                    //绘制文字
                    $watermark_canvas = $new_manager->create($white_box, $white_background);
                    $watermark_canvas->draw()->text($watermark_text, $white_font, $white_point);
                    //保存水印图片
                    $watermark_canvas->save($watermark_file, array('png_compression_level' => 9));
                }
                //打开水印
                $mark_image = parent::image_open($watermark_file);
                //获得尺寸
                $mark_size = parent::image_size($mark_image);
            }

            //计算水印位置
            $position = parent::image_point_coordinate(parent::get_config('watermark_position'), $image_size['w'], $image_size['h'], $mark_size['w'], $mark_size['h']);

            $water_mark_point = new Point($position['pw'], $position['ph']);

            //合并图层
            $image->paste($mark_image, $water_mark_point);

            //保存图像
            $image->save($save_path, $save_quality);

            return true;
        }
    }
}
