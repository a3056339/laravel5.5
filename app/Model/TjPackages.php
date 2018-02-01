<?php
/**
 * Created by PhpStorm.
 * User: Li
 * Date: 15/11/4
 * Time: 上午10:38
 */
namespace App\Model;

class TjPackages extends Model
{
    //根据标签获取方案
    public static function getTj($tags,$page, $limit)
    {
        $where = function ($query) use ($tags) {
            foreach ($tags as $k => $val) {
                if ($val) {
                    $query->orWhere('tags', 'like', "%{$val}%");
                }
            }
        };
        $data['project'] = self::where($where)->forPage($page, $limit)->get();
        $data['count'] = self::where($where)->count();
        return $data;
    }
   



}