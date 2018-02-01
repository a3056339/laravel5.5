<?php
/**
 * Created by PhpStorm.
 * User: Li
 * Date: 15/11/4
 * Time: 下午8:31
 */
namespace App\Model;


use Request;
use DB;
use Log;

class Inform extends Model
{

    public static function getReason($type)
    {
        $arr = [
            0 => '没人接电话',
            1 => '电话被挂断',
            2 => '咨询回答不满意',
            3 => '老师回答不认真',
            4 => '其他原因'
        ];
        return isset($arr[$type]) ? $arr[$type] : '其他原因';
    }


}