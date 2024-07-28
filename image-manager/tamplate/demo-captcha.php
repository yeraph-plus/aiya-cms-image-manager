<?php

$captcha = new AYA_Imagine_Captcha();

//新建验证
session_start();
$_SESSION['captcha'] = $captcha->captcha_code(4);

//显示验证
header('Content-Type: image/png');
$captcha->captcha_generate($_SESSION['captcha']);

//验证验证
session_start();

if (strtolower($user_captcha) == strtolower($_SESSION['captcha'])) {
    //或者 strcasecmp() 忽略大小写比较
    echo 'Success!';
} else {
    echo 'Fail';
}

//销毁验证
unset($_SESSION['captcha']);
