<?php

namespace App\Services;

use Mrgoon\AliSms\AliSms;

/**
 * 阿里云短信类
 */
class AliyunSms
{
    //验证码
    const VERIFICATION_CODE = 'SMS_195723569';
    //信息
    const MESSAGE_CODE = 'SMS_195723569';

    //模板CODE
    public static $templateCodes = [
        self::VERIFICATION_CODE => 'SMS_195723569',
        self::MESSAGE_CODE => 'SMS_195723569'
    ];

    /**
     * 发送短信
     */
    public static function sendSms($mobile, $scene, $params = [])
    {
        if (empty($mobile)) {
            throw new \Exception('手机号不能为空');
        }

        if (empty($scene)) {
            throw new \Exception('场景不能为空');
        }

        if (!isset(self::$templateCodes[$scene])) {
            throw new \Exception('请配置场景的模板CODE');
        }

        $template_code = self::$templateCodes[$scene];

        try {
            $ali_sms = new AliSms();
            $response = $ali_sms->sendSms($mobile, $template_code, $params);

            if ($response->Code == 'OK') {
                return true;
            }

            throw new \Exception($response->Message);
        } catch (\Throwable $e) {
            throw new \Exception($e->getMessage());
        }
    }
}
