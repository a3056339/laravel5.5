<?php
/**
 * Created by PhpStorm.
 * User: Li
 * Date: 15/11/4
 * Time: 上午10:38
 */
namespace App\Model;

class Price extends Model
{


    public static function returnPrice($id, $str = 'words_price')
    {
        return self::where(['level_id' => $id])->value($str);
    }


}