<?php
/**
 * Created by PhpStorm.
 * User: Li
 * Date: 15/11/4
 * Time: 上午10:38
 */
namespace App\Model;

class UserAmount extends Model
{
    public static function teacherAmount($uid)
    {

        if (!$data = self::where(['uid' => $uid])->first()) {
            $data           = new UserAmount();
            $data->uid      = $uid;
            $data->amount   = 0;
            $data->freeze   = 0;
            $data->unamount = 0;
            $data->money    = 0;
            if (!$data->save()) {
                return '保存失败';
            }
        }
        return $data;
    }

    public static function saveAmount($uid, $amount)
    {
        if (!$data = self::where(['uid' => $uid])->first()) {
            self::insert([
                'uid'       => $uid,
                'amount'    => $amount,
                'freeze'    => $amount,
                'create_at' => time()
            ]);
        } else {
            self::where(['uid' => $uid])->increment('freeze', $amount);
            self::where(['uid' => $uid])->increment('amount', $amount);
        }
        return true;
    }


}