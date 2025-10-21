<?php

/* 引入json相关辅助函数 */
require_once "json.php";

/* 引入对象相关辅助函数 */
require_once "object.php";

/* 引入字符串相关辅助函数 */
require_once "string.php";

/* 引入数组相关辅助函数 */
require_once "array.php";

/* 引入数组相关辅助函数 */
require_once "logger.php";


function getRealIp()
{
    $ip = false;
    if (getenv("HTTP_CLIENT_IP") && strcasecmp(getenv("HTTP_CLIENT_IP"), "unknown")) {
        $ip = getenv("HTTP_CLIENT_IP");
    } else if (getenv("HTTP_X_FORWARDED_FOR") && strcasecmp(getenv("HTTP_X_FORWARDED_FOR"), "unknown")) {
        $ips = explode(", ", getenv("HTTP_X_FORWARDED_FOR"));
        if ($ip) {
            array_unshift($ips, $ip);
            $ip = false;
        }
        $ipscount = count($ips);
        for ($i = 0; $i < $ipscount; $i++) {
            if (!preg_match("/^(10|172\.16|192\.168)\./i", $ips[$i])) {
                $ip = $ips[$i];
                break;
            }
        }
    } else if (getenv("REMOTE_ADDR") && strcasecmp(getenv("REMOTE_ADDR"), "unknown")) {
        $ip = getenv("REMOTE_ADDR");
    } else if (isset($_SERVER['REMOTE_ADDR']) && $_SERVER['REMOTE_ADDR'] && strcasecmp($_SERVER['REMOTE_ADDR'], "unknown")) {
        $ip = $_SERVER['REMOTE_ADDR'];
    } else {
        $ip = "unknown";
    }
    return isIp($ip) ? $ip : "unknown";
}

/**
 * 检查是否是合法的IP
 */
function isIp($ip)
{
    if (preg_match('/^((\d|[1-9]\d|2[0-4]\d|25[0-5]|1\d\d)(?:\.(\d|[1-9]\d|2[0-4]\d|25[0-5]|1\d\d)){3})$/', $ip)) {
        return true;
    } else {
        return false;
    }
}

/**
 * 验证手机号
 */
function checkMobile($mobile)
{
    return preg_match('/^((13[0-9])|(14[5,7])|(15[0-3,5-9])|(17[0,3,5-8])|(18[0-9])|166|198|199|(147))\d{8}$/i', $mobile);
}
