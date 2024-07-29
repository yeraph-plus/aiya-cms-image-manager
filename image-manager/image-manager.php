<?php
if (!defined('ABSPATH')) exit;

//加载库
use Imagine\Imagick\Imagine as ImagickImagine;
use Imagine\Gd\Imagine as GdImagine;

if (!class_exists('AYA_Image_Manager')) {
    class AYA_Image_Manager extends AYA_Image_Action
    {
        public $config;
        public $manager;

        public function __construct()
        {
            //获得配置
            $this->config = parent::self_config();
            //选择处理器
            if (self::can_use_imagick()) {
                $this->manager = new ImagickImagine();
            } else {
                $this->manager = new GdImagine();
            }
        }
        //检查 Imagick 是否可用
        public function can_use_imagick()
        {
            if (!extension_loaded('imagick')) return false;
            //if (!extension_loaded('gd')) return false;

            return true;
        }
        //创建本地文件夹
        public function local_mkdir($dirname)
        {
            //在 wp-content 下创建
            $local_dir = trailingslashit(WP_CONTENT_DIR) . $dirname;
            //判断文件夹是否存在
            if (!is_dir($local_dir)) {
                //创建文件夹
                wp_mkdir_p($local_dir);
            }
            //返回拼接的路径
            return $local_dir;
        }
        //生成缓存文件位置
        public function image_cache_path($cache_name)
        {
            //定义保存路径
            $cache_file_dir = $this->config['save_upload_path'] . '/cache';
            $cache_file_name = $cache_name;

            return self::local_mkdir($cache_file_dir)  . '/' . $cache_file_name;
        }
        //生成新文件位置
        public function image_save_new_file_path($save_name = '', $save_path = '')
        {
            //定义保存路径
            $save_file_dir = $this->config['save_upload_path'] . '/' . $save_path;
            //定义保存格式
            $save_extend = $this->config['save_format'];
            //保存文件名
            if ($save_name == '') {
                $save_name = 'unname_' . time();
            }
            $save_file_name = $save_name . '.' . $save_extend;
            //返回保存参数组
            return self::local_mkdir($save_file_dir)  . '/' . $save_file_name;
        }
        //保存参数
        public function image_save_quality($extend = '')
        {
            if (empty($extend)) {
                $extend = $this->config['save_format'];
            }
            //生成质量参数
            switch ($extend) {
                case 'jpg':
                    $save_quality = array('jpeg_quality' => $this->config['save_quality']);
                    break;
                case 'png':
                    $save_quality = array('png_compression_level' => intval($this->config['save_quality'] / 10));
                    break;
                case 'webp':
                    $save_quality = array('webp_quality' => $this->config['save_quality']);
                    break;
                case 'gif':
                    $save_quality = array('flatten' => false);
                    break;
                default:
                    $save_quality = null;
                    break;
            }
            return $save_quality;
        }
        //打开文件
        public function image_open($image_file = '')
        {
            //检查文件存在
            if (!file_exists($image_file)) return 'ERROR - Image file not found.';

            //返回Object对象
            return $this->manager->open($image_file);
        }
        //尺寸计算
        public function image_size($image_obj)
        {
            if (!is_object($image_obj)) return false;

            $image_size = $image_obj->getSize();

            return array('w' => $image_size->getWidth(), 'h' => $image_size->getHeight());
        }
        //判断是否为URL
        public function image_is_url($file_or_url = '')
        {
            //如果是URL
            if (strpos($file_or_url, 'http://') === 0 || strpos($file_or_url, 'https://') === 0) {
                //转换URL为本地路径
                if (strpos($file_or_url, home_url()) === 0) {
                    $url = esc_url($file_or_url);
                    //获取WP上传目录
                    $wp_content_url = set_url_scheme(WP_CONTENT_URL);
                    $wp_content_dir = WP_CONTENT_DIR; //trailingslashit()

                    //截取URL
                    $url_file = str_replace($wp_content_url, '', $url);
                    //拼接为本地路径
                    $local_file = $wp_content_dir . $url_file;

                    //检查文件存在
                    if (file_exists($local_file)) {
                        return $local_file;
                    }
                    return false;
                }
                return 'is_url';
            }
            //检查文件存在
            if (file_exists($file_or_url)) {
                return $file_or_url;
            }
            return false;
        }
        //打开远程文件
        public function image_load_remote($image_url = '')
        {
            $image_info = getimagesize($image_url);
            //不是图片
            if (!$image_info) return false;

            //允许的图片类型
            $allow_mime = ['image/jpeg', 'image/png', 'image/gif'];

            if (!in_array($image_info['mime'], $allow_mime)) return false;

            //设置超时
            ini_set('default_socket_timeout', 10);

            //读取文件
            $content = file_get_contents($image_url);
            //获取失败
            if ($content === false) return 'ERROR - Unable to load remote image.';

            //返回Object对象
            return $this->manager->load($content);
        }
        //备份原文件
        public function image_backup($origin_file = '', $copy = false)
        {
            //是否创建原文件备份
            if ($this->config['save_backup_raw_file']) {
                $origin_file_dir = dirname($origin_file);
                $origin_file_name = basename($origin_file);
                //备份原文件
                $backup_dir = self::local_mkdir($this->config['save_upload_path'] . '/backup');
                $backup_file = $backup_dir . '/raw_' . $origin_file_name;

                //检查文件存在
                if ($copy) {
                    //复制文件
                    $moved = copy($origin_file, $backup_file);
                    //删除原文件
                    //unlink($origin_file);
                } else {
                    //移动文件
                    $moved = rename($origin_file, $backup_file);
                }
            }
            return $moved;
        }
        //保存位置和保存参数
        public function image_save_param_array($origin_file = '', $save_path = '', $is_url = false)
        {
            //如果是URL
            if ($is_url) {
                //检查保存位置
                $save_path = empty($save_path) ? 'remote' : $save_path;
            }

            //获取保存格式
            $save_extend = $this->config['save_format'];
            //如果是GIF，强制为GIF
            if (strpos($origin_file, '.gif') !== false) {
                $save_extend = 'gif';
            }

            //在原文件位置保存
            if ($save_path == '' || $save_path === false) {
                //检查保存格式和原文件一致
                if (strpos($origin_file, '.' . $save_extend) === false) {
                    //根据保存设置生成新文件格式
                    $origin_file = substr($origin_file, 0, strrpos($origin_file, '.')) . '.' . $save_extend;
                }
                $save_path = $origin_file;
            } else {
                //MD5生成文件名
                $base_name_md5 = md5($origin_file, false);
                $save_dir = $this->config['save_upload_path'] . '/' . $save_path;
                $save_name = $base_name_md5 . '.' . $save_extend;

                //Tips: 输出的位置是/wp-content/thumbnail/{$save_path}/thumb_{md5}.jpg
                $save_path = self::local_mkdir($save_dir)  . '/' . $save_name;
            }

            //返回保存参数组
            return array(
                'save_path' => $save_path,
                'save_quality' => self::image_save_quality($save_extend),
            );
        }
        //坐标计算器
        public function image_point_coordinate($position = '', $width = 0, $height = 0, $sub_width = 0, $sub_height = 0)
        {
            //补偿边距
            $offset_x = $this->config['offset_x'];
            $offset_y = $this->config['offset_y'];

            //计算中心点，并去除层叠占位
            $size_x = intval(($width - $sub_width) / 2);
            $size_y = intval(($height - $sub_height) / 2);

            //基于左上角定位

            //计算位置
            switch ($position) {
                case 'center-center':
                    return array('pw' => $size_x, 'ph' => $size_y);
                case 'center-left':
                    return array('pw' => intval($size_x / 2), 'ph' => $size_y);
                case 'center-right':
                    return array('pw' => $size_x + intval($size_x / 2), 'ph' => $size_y);
                case 'center-top':
                    return array('pw' => $size_x, 'ph' => intval($size_y / 2));
                case 'center-bottom':
                    return array('pw' => $size_x, 'ph' => $size_y + intval($size_y / 2));
                case 'top-left':
                    return array('pw' => $offset_x, 'ph' => $offset_y);
                case 'top-center':
                    return array('pw' => $size_x, 'ph' => $offset_y);
                case 'top-right':
                    return array('pw' => $size_x * 2 - $offset_x, 'ph' => $offset_y);
                case 'bottom-left':
                    return array('pw' => $offset_x, 'ph' => $size_y * 2 - $offset_y);
                case 'bottom-center':
                    return array('pw' => $size_x, 'ph' => $size_y * 2 - $offset_y);
                case 'bottom-right':
                    return array('pw' => $size_x * 2 - $offset_x, 'ph' => $size_y * 2 - $offset_y);
                default:
                    return false;
            }
        }
        //缩放比例计算器
        public function image_scale_ratio($width, $height, $origin_width, $origin_height, $covered = true)
        {
            if ($covered) {
                $ratio = max($width / $origin_width, $height / $origin_height);
            } else {
                $ratio = min($width / $origin_width, $height / $origin_height);
            }
            return $ratio;
        }
        //文字画布尺寸计算器
        public function image_bbox_text_size($font_size, $font_text)
        {
            //检查字体文件位置
            $font_path = $this->config['font_path'];
            if (!file_exists($font_path)) {
                return false;
            }
            //计算字体需要的画布尺寸，GD库方法
            $bbox = imagettfbbox($font_size, 0, $font_path, $font_text);

            $width = $bbox[2] - $bbox[0];
            $height = $bbox[1] - $bbox[7];

            return array('fw' => $width, 'fh' => $height);
        }
        //尺寸计算器
        public function image_get_size($image_file)
        {
            //提取图片宽高
            $image_size = getimagesize($image_file);
            $width = $image_size[0];
            $height = $image_size[1];

            return array(
                'ow' => $width,
                'oh' => $height,
            );
        }
        //亮度计算器
        public function image_average_brightness($image_file)
        {
            //读取文件
            if (is_file($image_file)) {
                $image = imagecreatefromjpeg($image_file);
            } else {
                return false;
            }

            //此处是直接使用GD库方法

            //获取图片宽度和高度
            $width = imagesx($image);
            $height = imagesy($image);

            //抽取图片像素大小
            $grid_size = 100;
            //计数器
            $sum_brightness = 0;
            $grid_count = 0;

            //遍历
            for ($x = 0; $x < $width; $x += $grid_size) {
                for ($y = 0; $y < $height; $y += $grid_size) {
                    //生成图块中心像素的颜色索引
                    $colorIndex = imagecolorat($image, $x + $grid_size / 2, $y + $grid_size / 2);
                    //抽取颜色RGB值
                    $colors = imagecolorsforindex($image, $colorIndex);

                    //加权计算亮度
                    $brightness = 0.299 * $colors['red'] + 0.587 * $colors['green'] + 0.114 * $colors['blue'];

                    //累加
                    $sum_brightness += $brightness;
                    $grid_count++;
                }
            }

            //计算平均值
            $average = $sum_brightness / $grid_count;

            //释放内存
            imagedestroy($image);

            return $average;
            //取值方式
            //$color = (int) $average > 128 ? 0 : 255;
        }
        //占比最大颜色计算器
        public function image_primary_color($image_file)
        {
            $image = imagecreatefromjpeg($image_file);

            //此处是直接使用GD库方法

            //获取图片宽度和高度
            $width = imagesx($image);
            $height = imagesy($image);

            //缩放到5%
            $ratio = 0.05;

            //计算缩小后的尺寸
            $thumb_w = $width * $ratio;
            $thumb_h = $height * $ratio;

            //创建缩放后的图片
            $thumb = imagecreatetruecolor($thumb_w, $thumb_h);

            imagecopyresampled($thumb, $image, 0, 0, 0, 0, $thumb_w, $thumb_h, $width, $height);

            //存放颜色的数组
            $color_array = array();

            //遍历像素，因为需要准确颜色值所以按像素遍历
            for ($x = 0; $x < $thumb_w; $x++) {
                for ($y = 0; $y < $thumb_h; $y++) {
                    //生成图块中心像素的颜色索引
                    $rgb = imagecolorat($thumb, $x, $y);
                    $colors = imagecolorsforindex($thumb, $rgb);

                    //转换RGB到字符串用于存入数组
                    $color = sprintf("#%02x%02x%02x", $colors['red'], $colors['green'], $colors['blue']);

                    //使用数组计数
                    if (isset($color_array[$color])) {
                        $color_array[$color]++;
                    } else {
                        $color_array[$color] = 1;
                    }
                }
            }

            //释放内存
            imagedestroy($image);
            imagedestroy($thumb);


            //去除最大值，可能是背景色
            $max_color = max($color_array);
            //去除太小的样本
            $less_color = 4;

            //清洗数组
            foreach ($color_array as $key => $value) {
                if ($value == $max_color || $value < $less_color) {
                    unset($color_array[$key]);
                }
            }

            $primary_color = array_search(max($color_array), $color_array);

            return $primary_color;
        }
        //十六进制色值反算RGB
        public function image_color_hex2rgb($color_hex, $invert = false)
        {
            //如果包含#号，则去掉
            if (substr($color_hex, 0, 1) == '#') {
                $color_hex = substr($color_hex, 1);
            }
            $r = hexdec(substr($color_hex, 0, 2));
            $g = hexdec(substr($color_hex, 2, 2));
            $b = hexdec(substr($color_hex, 4, 2));

            if ($invert) {
                $r = 255 - $r;
                $g = 255 - $g;
                $b = 255 - $b;
            }
            return array($r, $g, $b);
        }
    }
}
