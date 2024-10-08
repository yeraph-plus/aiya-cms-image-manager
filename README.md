## AIYA-CMS 主题图像处理依赖

![截图](https://github.com/yeraph-plus/aiya-cms-image-manager/blob/main/screenshot/2024-07-30%20040126.png)

---

这是一个基于 php-imagine 库和 GD 驱动的图像生成器。

设计用途是给 AIYA-CMS 主题提供一些额外的图片处理功能来替代 timthumb.php ，可以提供一些简单的图片处理：缩放、裁剪、水印、缩略图生成，以及生成海报图片和封面图片。

虽然这个项目是作为 WordPress 插件打包的，但本身只是一个流式处理图片的类封装，项目里没用到 WP 的功能，也没给 WP 添加功能，项目内和 WP 有关的地方只是通过 WP 的常量和 `home_url()` 用于定位 wp-content 目录，所以如需用作其他程序请自行调整，AIYA-CMS 是在主题中对 WP 媒体库操作的。

UPDATE：额外提供了一个创建在 WP 后台的简易图床程序，这个本来是开发时用来调试功能顺手写的，稍微完善了一下，如果直接使用也是可以的。（PS：图片上传并不通过WP媒体库，所以也不会存入数据表，长期使用如果需要删除文件之类的就需要在服务器上操作了）

PS：创建 Composer 的 PHP 版本为 8.1 ，需要更低版本的可以自行按照 composer.json 重新安装。

---

#### 使用方式

一些字体、尺寸等其他的设置和参数，默认是在 setup.php 里定义的。

更详细的说明之后有时间再写吧，这里先列出用法。

图片处理：

返回的是图片文件的路径，输出前需要先截取成相对路径或转换成URL。
```
$image = new AYA_Imagine_Trans();
$image_file = $image->image_generate($image_file, 'convert', true);
$image_file = $image->image_generate($image_file, 'auto_scale');
$image_file = $image->image_generate($image_file, 'watermark');
return $image_file;
```

生成缩略图：

返回的是缩略图文件的绝对路径，输出前需要先截取成相对路径或转换成URL。
```
$thumb = new AYA_Imagine_Trans();
return $thumb->image_thumb_generate($image_file, 200, 200);
```

封面图生成：

按照数组定义的顺序生成图层，可自由排列组合，结构为背景、蒙版、标题。返回的文件的绝对路径。
```
$cover = array(
    //'bg_by_color' => '',
    //'bg_material' => 6,
    'bg_by_custom_image' => $image_file,
    //'bg_material_random' => 5,
    'mask_blur' => 1,
    //'mask_bright' => -10,
    //'mask_color' => '#66ccff',
    //'title_center' => '测试文字',
    //'title_top' => '测试文字',
    //'title_bottom' => '测试文字',
    'title_auto' => '恶魔妹妹卖卖萌恶魔妹妹卖卖萌',
    //'thumb_image' => $image_file,
);
$cover_draw = new AYA_Imagine_Draws();
return $cover_draw->image_cover_drawing($cover);
```
