<?php
/**
 * Created by PhpStorm.
 * User: Li
 * Date: 15/11/4
 * Time: 上午10:38
 */
namespace App\Model;

class UserDevice extends Model
{
    /**
     * 修改用户信息
     *
     * @param $token
     *
     * @return bool
     */
    public static function updateUserDevice($uid, $version, $appid, $device_app, $channel, $device)
    {
        $data = [
            'device_name' => $device_app,
            'channel'     => $channel,
            'verison'     => $version,
            'app_id'      => $appid,
            'device'      => $device
        ];
        if (self::where(['uid' => $uid])->first()) {
            self::where(['uid' => $uid])->update($data);
        } else {
            $data['uid']       = $uid;
            $data['create_at'] = time();
            self::insert($data);
        }
        return true;
    }


}