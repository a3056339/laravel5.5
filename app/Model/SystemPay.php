<?php
/**
 * Created by PhpStorm.
 * User: Li
 * Date: 15/11/4
 * Time: 上午10:38
 */
namespace App\Model;

class SystemPay extends Model
{


    public static function checkPay($appid, $channel, $version)
    {
        $where  = [
            'app_id'  => $appid,
            'channel' => $channel,
            'version' => $version
        ];
        $switch = SystemPay::where($where)->first();
        return $switch ? $switch->switch : 0;
    }

}