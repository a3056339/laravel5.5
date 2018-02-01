<?php
/**
 * Created by PhpStorm.
 * User: Li
 * Date: 15/11/4
 * Time: 上午10:38
 */
namespace App\Model;

use Symfony\Component\HttpFoundation\Request;
use Illuminate\Support\Facades\Redis;

class SmsCode extends Model
{

    public $timestamps = false;
    const EXPRE = 600;//过期时间


    public static function chcekSign($sign, $phone)
    {
        $key      = Redis::get('key');
        $sign_str = Md5($key . $phone);
        if ($sign != $sign_str) {
            return false;
        }
        return true;
    }

    public static function verifyCode($phone, $code)
    {
        $sms = SmsCode::where(['phone' => $phone, 'code' => $code])->first();
        if ($sms) {
            if ($sms->create_at+ $sms->expire<time()) {
                return false;
            }
            return true;
        }
        return false;
    }

    public static function send($country_code, $phone, $ip)
    {

        if ($country_code == '86') {
            ////////////////////////////////////示远科技短信通道
            $post_data           = [];
            $post_data['sign']   = 'ab1ea1439870a9ef8bd7f252e90c7fec';
            $post_data['ip']     = $ip;
            $post_data['mobile'] = $phone;
            $url                 = 'http://sms.xiake99.com/send';
            $o                   = '';
            foreach ($post_data as $k => $v) {
                $o .= "$k=" . urlencode($v) . '&';
            }
            //$post_data = substr($o, 0, -1);
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_HEADER, 0);
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); //如果需要将结果直接返回到变量里，那加上这句。
            $result = curl_exec($ch);
            curl_close($ch);
            $result = json_decode($result, true);
            if ($result['Code'] != 0) {
                return ['msg' => $result['Msg'], 'code' => 2004];
            }
            /////添加短信日志
            if ($result['Code'] == 0) {

                $data['phone']     = $country_code . $phone;
                $data['code']      = $result['Msg'];
                $data['create_at'] = time();
                $data['expire']    = self::EXPRE;//60*10
                $data['ip']        = $ip;
                if (self::where(['phone' => $country_code . $phone])->first()) {
                    self::where(['phone' => $country_code . $phone])->update($data);
                } else {
                    self::insert($data);
                }
                return ['msg' => '获取验证码成功', 'code' => 0];
            } else {
                return ['msg' => '获取验证码失败', 'code' => 2004];
            }
        }
        return ['msg' => '获取验证码失败', 'code' => 2004];
    }

}