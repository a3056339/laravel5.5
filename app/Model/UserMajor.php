<?php
/**
 * Created by PhpStorm.
 * User: Li
 * Date: 15/11/4
 * Time: 上午10:38
 */
namespace App\Model;

class UserMajor extends Model
{


    public static function getLaveAt($from_uid, $to_uid)
    {
        $info = self::where(['from_uid' => $from_uid, 'to_uid' => $to_uid, 'status' => 0])->first();
        $day  = 0;
        if (!$info) {
            return $day . '天';
        }
        $day    = $info->end_at - time() > 0 ? intval(($info->end_at - time()) / (24 * 3600)) : 0;
        $hour   = $info->end_at - time() > 0 ? intval((($info->end_at - time()) % 86400) / 3600) : 0;
        $minute = $info->end_at - time() > 0 ? intval(((($info->end_at - time()) % 86400)%3600) / 60) : 0;
        return $day . '天' . $hour . '小时' . $minute . '分';
    }

    //$array 要排序的数组
//$row  排序依据列
//$type 排序类型[asc or desc]
//return 排好序的数组
    public static function ArraySort($array, $field, $desc)
    {

        $fieldArr = [];
        foreach ($array as $k => $v) {
            $fieldArr[$k] = $v[$field];
        }
        $sort = $desc == false ? SORT_ASC : SORT_DESC;
        array_multisort($fieldArr, $sort, $array);
        return $array;
    }

    public static function upMajor($from_uid, $to_uid)
    {
        if (self::where(['from_uid' => $from_uid, 'to_uid' => $to_uid])->first()) {
            return ['status' => 1, 'msg' => ''];
        }
        $price = User::getUserBy(Order::ORDER_TYPE_TWO, $to_uid);
        return ['status' => 0, 'msg' => '全年无限次免费咨询 ￥' . $price . '/年'];
    }

}