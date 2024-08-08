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

if (!class_exists('AYA_Imagine_Draws')) {
    class AYA_Imagine_Draws extends AYA_Image_Manager
    {
        //海报生成实例
        public function image_poster_drawing()
        {
            $width = parent::get_config('poster_width');
            $height = parent::get_config('poster_height');

            $image = self::draw_create($width, $height);
            //输出
            $image->show('jpg');

            $saved_quality = parent::image_save_quality();
            $image_save_path = parent::image_save_new_file_path('poster_' . time(), 'poster');
            //保存文件
            $image->save($image_save_path, $saved_quality);

            return $image_save_path;
        }
        //图像生成实例
        public function image_cover_drawing($cover_apply = array())
        {
            $width = parent::get_config('cover_width');
            $height = parent::get_config('cover_height');

            $image = self::draw_create($width, $height);

            //默认图层定位坐标
            $pit_point = self::draw_point(0, 0);

            //分别循环数组中的所有方法

            //背景层
            foreach ($cover_apply as $method => $param) {
                //新建图层
                if ($method == 'bg_by_color') {
                    $bg_image = self::background_color_out($width, $height, $param);
                }
                if ($method == 'bg_material') {
                    $bg_image = self::background_material_out($width, $height, $param, false);
                }
                if ($method == 'bg_material_random') {
                    $bg_image = self::background_material_out($width, $height, $param, true);
                }
                if ($method == 'bg_by_custom_image') {
                    $bg_image = self::background_image_out($width, $height, $param);
                }
                //合并图层
                if (isset($bg_image)) {
                    $image->paste($bg_image, $pit_point);
                }
            }
            //蒙版层
            foreach ($cover_apply as $method => $param) {
                //模糊
                if ($method == 'mask_blur') {
                    $blur = intval($param);
                    $image->effects()->blur($blur);
                }
                //亮度
                if ($method == 'mask_bright') {
                    $brightness = intval($param);
                    $image->effects()->brightness($brightness);
                }
                //调色
                if ($method == 'mask_color') {
                    $color = self::draw_color($param, 50);
                    $image->effects()->colorize($color);
                }
            }

            //创建缓存，用于调整组件颜色
            $cover_cache = parent::image_cache_path('cover_cached_background.jpg');
            $image->save($cover_cache, array('jpeg_quality' => 96));

            $auto_color = parent::get_config('cover_fg_element_color_auto');
            if ($auto_color) {
                //计算主要颜色
                $bg_main_color = parent::image_primary_color($cover_cache);
                //取反
                $use_color = parent::image_color_hex2rgb($bg_main_color, true);
            } else {
                //计算图片亮度
                $cover_bright = parent::image_average_brightness($cover_cache);

                if ($cover_bright > 128) {
                    $use_color = parent::get_config('cover_fg_element_color_light');
                } else {
                    $use_color = parent::get_config('cover_fg_element_color_dark');
                }
            }

            //前置层
            foreach ($cover_apply as $method => $param) {
                if ($method == 'title_center') {
                    $fg_image = self::foreground_content_pos_out($width, $height, $param, $use_color, 'center');
                }
                if ($method == 'title_top') {
                    $fg_image = self::foreground_content_pos_out($width, $height, $param, $use_color, 'top');
                }
                if ($method == 'title_bottom') {
                    $fg_image = self::foreground_content_pos_out($width, $height, $param, $use_color, 'bottom');
                }
                if ($method == 'title_auto') {
                    $fg_image = self::foreground_content_pos_out($width, $height, $param, $use_color, 'auto');
                }
                if ($method == 'thumb_image') {
                    $fg_image = self::foreground_image_out($width, $height, $param, $use_color);
                }
                //合并图层
                if (isset($fg_image)) {
                    $image->paste($fg_image, $pit_point);
                }
            }

            $saved_quality = parent::image_save_quality();
            $image_save_path = parent::image_save_new_file_path('cover_' . time(), 'cover');
            //保存文件
            $image->save($image_save_path, $saved_quality);

            return $image_save_path;
        }
        //创建盒子
        public function draw_box($width, $height)
        {
            $canvas_box = new Box($width, $height);
            return $canvas_box;
        }
        //创建空白画布
        public function draw_create($width, $height)
        {
            $canvas_box = self::draw_box($width, $height);
            $canvas_color = self::draw_color(array(0, 0, 0), 0);

            $canvas_image = $this->manager->create($canvas_box, $canvas_color);
            return $canvas_image;
        }
        //调色板
        public function draw_color($color, $transparent = 100)
        {
            $canvas_palette = new RGB();
            return $canvas_palette->color($color, $transparent);
        }
        //坐标
        public function draw_point($x, $y)
        {
            $canvas_point = new Point($x, $y);
            return $canvas_point;
        }

        //颜色图层
        public function background_color_out($canvas_width, $canvas_height, $color)
        {
            //背景色
            if (empty($color)) {
                $color = parent::get_config('background_default_color');
            }
            $canvas_color = self::draw_color($color, 100);
            //创建画布
            $canvas_box = self::draw_box($canvas_width, $canvas_height);

            $canvas_bg = $this->manager->create($canvas_box, $canvas_color);

            return $canvas_bg;
        }
        //提取内置背景图层文件
        public function background_material_out($canvas_width, $canvas_height, $use_count = 0, $random_mode = false)
        {
            $use_count = intval($use_count);
            //使用内置素材
            $bg_material = (array) parent::get_config('cover_bg_material_in');
            //判断使用图片顺序
            $use_count = ($use_count == 0) ? mt_rand(0, (count($bg_material) - 1)) : ($use_count - 1);

            //打开图像
            $bg_image = parent::image_open($bg_material[$use_count]);
            //获得尺寸
            $bg_org_size = parent::image_size($bg_image);

            $bg_org_box = self::draw_box($bg_org_size['w'], $bg_org_size['h']);
            //获得空画布
            $new_image = self::draw_create($canvas_width, $canvas_height);

            //使用随机效果
            if ($random_mode) {
                //随机比例缩放图像
                $rand_ratio = rand(50, 100) / 100;
                //随机图片放置位置
                $rand_pos_x = rand(0, $canvas_width / 2 / 2);
                $rand_pos_y = rand(0, $canvas_height / 2 / 2);

                $bg_size_box = $bg_org_box->scale($rand_ratio);
                $bg_image->resize($bg_size_box);
                $bg_point = self::draw_point($rand_pos_x, $rand_pos_y);

                //合并图像
                $new_image->paste($bg_image, $bg_point);
            }
            //使用居中缩放
            else {
                //计算缩放比例
                $ratio = parent::image_scale_ratio($canvas_width, $canvas_height, $bg_org_size['w'], $bg_org_size['h'], false);
                //缩放图像
                $bg_size_box = $bg_org_box->scale($ratio);
                $bg_image->resize($bg_size_box);
                //计算缩放后尺寸
                $bg_scale_width = intval($bg_org_size['w'] * $ratio);
                //$bg_scale_height = intval($bg_org_size['h'] * $ratio);
                //计算居中的偏移坐标
                $bg_point = self::draw_point(($canvas_width - $bg_scale_width) / 2, 0);
                //合并图像
                $new_image->paste($bg_image, $bg_point);
            }

            //返回
            return $new_image;
        }
        //图片图层缩放
        public function background_image_out($canvas_width, $canvas_height, $image_file)
        {
            //打开图像
            $bg_image = parent::image_open($image_file);
            //获得尺寸
            $bg_org_size = parent::image_size($bg_image);
            $bg_org_box = self::draw_box($bg_org_size['w'], $bg_org_size['h']);

            //计算缩放比例
            $ratio = parent::image_scale_ratio($canvas_width, $canvas_height, $bg_org_size['w'], $bg_org_size['h'], true);
            //生成缩放图像
            $bg_size_box = $bg_org_box->scale($ratio);
            $bg_image->resize($bg_size_box);

            $bg_scale_width = intval($bg_org_size['w'] * $ratio);
            //如果缩放后宽度大于使用宽度，发生裁剪
            if ($bg_scale_width > $canvas_width) {
                //计算居中的偏移坐标
                $bg_crop_point = self::draw_point(($bg_scale_width - $canvas_width) / 2, 0);
                $bg_crop_box = self::draw_box($canvas_width, $canvas_height);
                //裁剪图像
                $bg_image->crop($bg_crop_point, $bg_crop_box);
            }

            return $bg_image;
        }
        //图层文字
        public function foreground_content_pos_out($canvas_width, $canvas_height, $content, $white_color, $position_type = 'center')
        {
            $font_file = parent::get_config('font_path');
            $font_size = parent::get_config('cover_fg_font_size');
            $font_color = self::draw_color($white_color, 100);
            //设置一个阴影偏移量
            $shadow_offset = 3;

            //获得空画布
            $white_image = self::draw_create($canvas_width, $canvas_height);
            //检查字体文件位置
            if (!file_exists($font_file)) return $white_image;

            if (empty($content)) return $white_image;

            //加载字体
            $white_font = $this->manager->font($font_file, $font_size, $font_color);
            //加载阴影
            $shadow_color = self::draw_color($white_color, 30);
            $shadow_font = $this->manager->font($font_file, $font_size, $shadow_color);
            //获取图像大小和文本尺寸
            $white_box_size = $white_font->box($content);
            $white_box_width = $white_box_size->getWidth();
            //$white_box_height = $white_box_size->getHeight();

            //自动分割文字位置
            if ($position_type == 'auto') {
                //一行的最大字数
                $max_word = intval($canvas_width / parent::get_config('cover_fg_font_width'));
                //是单数，-1
                if ($max_word % 2 != 0) {
                    $max_word -= 1;
                }
                //计算字数
                $content_length = mb_strlen($content, 'UTF-8');
                //如果字数大于最大字数，则进行分割
                if ($content_length > $max_word) {

                    //判断分割两行还是三行
                    if ($content_length > $max_word * 2) {
                        $content_1st = mb_substr($content, 0, $max_word, 'UTF-8');
                        $content_2nd = mb_substr($content, $max_word, $max_word, 'UTF-8');
                        $content_3rd = mb_substr($content, $max_word * 2, $max_word, 'UTF-8');
                    } else {
                        $content_1st = mb_substr($content, 0, $max_word, 'UTF-8');
                        $content_3rd = mb_substr($content, $max_word, $max_word, 'UTF-8');
                    }
                }
            }
            //文字定位模式
            else {
                switch ($position_type) {
                    case 'center':
                        $content_2nd = $content;
                        break;
                    case 'top':
                        $content_1st = $content;
                        break;
                    case 'bottom':
                        $content_3rd = $content;
                        break;
                    default:
                        $content_2nd = $content;
                        break;
                }
            }
            //绘制第一行
            if (isset($content_1st)) {
                //获取文本尺寸
                $box_size = $white_font->box($content_1st);
                $box_width = $box_size->getWidth();
                $box_height = $box_size->getHeight();
                //绘制文字
                $white_position = parent::image_point_coordinate('center-top', $canvas_width, $canvas_height, $box_width, $box_height);
                $point_x = $white_position['pw'];
                $point_y = $white_position['ph'];

                $shadow_point = self::draw_point($point_x + $shadow_offset, $point_y + $shadow_offset);
                $white_point = self::draw_point($point_x, $point_y);

                $white_image->draw()->text($content_1st, $shadow_font, $shadow_point);
                $white_image->draw()->text($content_1st, $white_font, $white_point);
            }
            //绘制第二行
            if (isset($content_2nd)) {
                //获取文本尺寸
                $box_size = $white_font->box($content_2nd);
                $box_width = $box_size->getWidth();
                $box_height = $box_size->getHeight();
                //绘制文字
                $white_position = parent::image_point_coordinate('center-center', $canvas_width, $canvas_height, $box_width, $box_height);
                $point_x = $white_position['pw'];
                $point_y = $white_position['ph'];

                $shadow_point = self::draw_point($point_x + $shadow_offset, $point_y + $shadow_offset);
                $white_point = self::draw_point($point_x, $point_y);

                $white_image->draw()->text($content_2nd, $shadow_font, $shadow_point);
                $white_image->draw()->text($content_2nd, $white_font, $white_point);
            }
            //绘制第三行
            if (isset($content_3rd)) {
                //获取文本尺寸
                $box_size = $white_font->box($content_3rd);
                $box_width = $box_size->getWidth();
                $box_height = $box_size->getHeight();
                //绘制文字
                $white_position = parent::image_point_coordinate('center-bottom', $canvas_width, $canvas_height, $box_width, $box_height);
                $point_x = $white_position['pw'];
                $point_y = $white_position['ph'];

                $shadow_point = self::draw_point($point_x + $shadow_offset, $point_y + $shadow_offset);
                $white_point = self::draw_point($point_x, $point_y);

                $white_image->draw()->text($content_3rd, $shadow_font, $shadow_point);
                $white_image->draw()->text($content_3rd, $white_font, $white_point);
            }

            return $white_image;
        }
        //中心首图
        public function foreground_image_out($canvas_width, $canvas_height, $image_file, $shadow_color = '#ffffff')
        {
            //打开图像
            $image = parent::image_open($image_file);
            //获得尺寸
            $fg_org_size = parent::image_size($image);
            $fg_org_box = self::draw_box($fg_org_size['w'], $fg_org_size['h']);

            //预留缩放边距
            $margin_offset = parent::get_config('cover_fg_thumb_margin');
            $frame_width = parent::get_config('cover_fg_thumb_frame_width');
            $frame_color = parent::get_config('cover_fg_thumb_frame_color');
            $shadow_offset = 5;
            $thumb_width = $canvas_width - ($margin_offset + $frame_width) * 2;
            $thumb_height = $canvas_height - ($margin_offset + $frame_width) * 2;

            //计算缩放比例
            $ratio = parent::image_scale_ratio($thumb_width, $thumb_height, $fg_org_size['w'], $fg_org_size['h'], false);

            //生成缩放图像
            $fg_size_box = $fg_org_box->scale($ratio);
            $image->resize($fg_size_box);

            //获得缩放后的尺寸
            $thumb_size = parent::image_size($image);
            $thumb_width = $thumb_size['w'];
            $thumb_height = $thumb_size['h'];
            //计算居中的偏移坐标
            $thumb_position = parent::image_point_coordinate('center-center', $canvas_width, $canvas_height, $thumb_width, $thumb_height);
            $point_x = $thumb_position['pw'];
            $point_y = $thumb_position['ph'];
            $thumb_cop_point = self::draw_point($point_x, $point_y);

            //获得空画布
            $thumb_image = self::draw_create($canvas_width, $canvas_height);

            //绘制和图像等大的边框图层
            if ($frame_width != 0) {
                $frame_color = self::draw_color($frame_color, 100);

                $frame_box = self::draw_box($thumb_width + $frame_width * 2, $thumb_height + $frame_width * 2);
                $frame_cop_point = self::draw_point($point_x - $frame_width, $point_y - $frame_width);

                $frame_image = $this->manager->create($frame_box, $frame_color);

                $thumb_image->paste($frame_image, $frame_cop_point);
            }

            //绘制阴影
            if ($frame_width != 0) {
                $shadow_color = self::draw_color($shadow_color, 50);

                $shadow_box = $frame_box;
                $shadow_cop_point = self::draw_point($point_x - $frame_width + $shadow_offset, $point_y - $frame_width + $shadow_offset);

                $shadow_image = $this->manager->create($shadow_box, $shadow_color);

                $thumb_image->paste($shadow_image, $shadow_cop_point);
            }

            //合并图像
            $thumb_image->paste($image, $thumb_cop_point);


            return $thumb_image;
        }
    }
}
