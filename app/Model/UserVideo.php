<?php
/**
 * Created by PhpStorm.
 * User: Li
 * Date: 15/11/4
 * Time: 上午10:38
 */
namespace App\Model;

class UserVideo extends Model
{
    public static function getVideo($uid)
    {
        $data = self::select('video','img','length','status')->where(['uid'=>$uid])->first();
        return $data ? $data : (object)[];
    }
   


}