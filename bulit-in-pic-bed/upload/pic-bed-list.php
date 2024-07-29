<?php
if (!defined('ABSPATH')) exit;

if (!current_user_can('upload_files')) {
    echo '没有上传权限！';
    return;
}
?>
<div class="pic-bed-warp">
    <h3>已上传的文件</h3>
    <div class="pic-bed-view-list">
        <?php AYA_Shortcode_Pic_Bed::handle_image_view(); ?>
    </div>
</div>