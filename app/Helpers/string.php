<?php

if ( ! function_exists('xss_filter')) {
    //php防注入和XSS攻击过滤.
    function xss_filter(&$arr,$strict=false) {
        $ra = Array('/([\x00-\x08,\x0b-\x0c,\x0e-\x19])/', '/script/', '/javascript/', '/vbscript/', '/expression/', '/applet/', '/meta/', '/xml/', '/blink/', '/link/', '/style/', '/embed/', '/object/',
            '/frame/', '/layer/', '/title/', '/bgsound/', '/base/', '/onload/', '/onunload/', '/onchange/', '/onsubmit/', '/onreset/', '/onselect/', '/onblur/', '/onfocus/', '/onabort/',
            '/onkeydown/', '/onkeypress/', '/onkeyup/', '/onclick/', '/ondblclick/', '/onmousedown/', '/onmousemove/', '/onmouseout/', '/onmouseover/', '/onmouseup/', '/onunload/');
        if (is_array($arr)) {
            foreach ($arr as $key => $value) {
                if (!is_array($value)) {
                    if (is_string($value)) {
//                        if (!get_magic_quotes_gpc()) { //不对magic_quotes_gpc转义过的字符使用addslashes(),避免双重转义。
//                            $value = addslashes($value); //给单引号（'）、双引号（"）、反斜线（\）与 NUL（NULL 字符）加上反斜线转义
//                        }
                        if ($strict) { //严格模式过滤所有的标签
                            $value = preg_replace($ra, '', $value); //删除非打印字符，粗暴式过滤xss可疑字符串
                            $value = strip_tags($value);//函数剥去字符串中的 HTML、XML 以及 PHP 的标签。
                        }

//                    $old = htmlspecialchars_decode($value);//避免双重转义。
//                    $arr[$key] = htmlspecialchars($old); //去除 HTML 和 PHP 标记并转换为 HTML 实体
//                    $arr[$key] = htmlspecialchars($value,ENT_COMPAT,'UTF-8',false); //去除 HTML 和 PHP 标记并转换为 HTML 实体
                        $arr[$key] = e($value, false); //去除 HTML 和 PHP 标记并转换为 HTML 实体
                    }
                } else {
                    xss_filter($arr[$key]);
                }
            }
        }
    }
}

if ( ! function_exists('d_sql_injection')) {
    //防止sql注入
    function d_sql_injection($keyword){
        $keyword=addslashes($keyword);
        return $keyword = preg_replace('/&((#(\d{3,5}|x[a-fA-F0-9]{4}));)/', '&\\1', $keyword);
    }
}

if(! function_exists('get_vcode')){
    function get_vcode(&$captcha_code,$num = 4, $fontsize = 24, $width = 100, $height = 40) {
        $image = imagecreatetruecolor($width, $height);    //1>设置验证码图片大小的函数
        //5>设置验证码颜色 imagecolorallocate(int im, int red, int green, int blue);
        $bgcolor = imagecolorallocate($image,255,255,255); //#ffffff
        //6>专业填充 int imagefill(int im, int x, int y, int col) (x,y) 所在的专业着色,col 表示欲涂上的颜色
        imagefill($image, 0, 0, $bgcolor);
        //10>设置变量
        //$captcha_code = "";
        //7>生成随机的字母和数字
        for($i=0;$i<$num;$i++){
            //设置字体大小
            //设置字体颜色，随机颜色
            $fontcolor = imagecolorallocate($image, rand(0,120),rand(0,120), rand(0,120));      //0-120深颜色
            //设置需要随机取的值,去掉容易出错的值如0和o
            $data ='abcdefghigkmnpqrstuvwxy3456789';
            //取出值，字符串截取方法  strlen获取字符串长度
            $fontcontent = substr($data, rand(0,strlen($data)),1);
            //10>.=连续定义变量
            $captcha_code .= $fontcontent;
            //设置坐标
            $x = ($i*$width/$num)+rand(5,10);
            $y = rand(5,10);
            imagestring($image,14,$x,$y,$fontcontent,$fontcolor);
        }
        //10>存到session
//        \Session::put('VerifyCode',$captcha_code);
        //8>增加干扰元素，设置雪花点
        for($i=0;$i<200;$i++){
            //设置点的颜色，50-200颜色比数字浅，不干扰阅读
            $pointcolor = imagecolorallocate($image,rand(50,200), rand(50,200), rand(50,200));
            //imagesetpixel — 画一个单一像素
            imagesetpixel($image, rand(1,99), rand(1,29), $pointcolor);
        }
        //9>增加干扰元素，设置横线
        for($i=0;$i<4;$i++){
            //设置线的颜色
            $linecolor = imagecolorallocate($image,rand(80,220), rand(80,220),rand(80,220));
            //设置线，两点一线
            imageline($image,rand(1,99), rand(1,29),rand(1,99), rand(1,29),$linecolor);
        }

        //2>设置头部，image/png
        header('Content-Type: image/png');
        //3>imagepng() 建立png图形函数
        imagepng($image);
        //4>imagedestroy() 结束图形函数 销毁$image
        imagedestroy($image);
    }
}


if ( ! function_exists('is_tel')){

    function is_tel($str) {
        if (!preg_match("/^1[3456789]\d{9}$/", $str)) {
            return false;
        }
        return true;
    }
}
