<?php
/**
 * Created by PhpStorm.
 * User: Li
 * Date: 15/11/4
 * Time: 上午10:38
 */
namespace App\Model;

class Notice extends Model
{



    public static function getNotice(){
        return self::orderBy('create_at','desc')->get();
    }

}