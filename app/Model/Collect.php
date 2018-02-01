<?php
/**
 * Created by PhpStorm.
 * User: Li
 * Date: 17/12/7
 * Time: 上午11:38
 */
namespace App\Model;

class Collect extends Model
{
    //判断是否收藏该医生
    public static  function isCollect($f_uid,$t_uid)
    {
        //收藏返回1
        if($data = Collect::where(['from_uid'=>$f_uid,'to_uid'=>$t_uid,'status'=>0])->first()){
            return 1;
        }else{
            return 0;
        }
    }

    //收藏医生的信息
     public static function collectInfo($f_uid,$page,$limit)
     {
         $rs = [];
         //字段
         $fieldRaw = 't_user.uid,t_user.name,t_user.avatar,t_user.title,t_user.intro,t_user.status';
         //排序规则
         $orderbyRaw = 't_collect.update_at DESC,t_user.status DESC';
         //老师信息
         $t_data = Collect::where(['collect.from_uid'=>$f_uid,'collect.status'=>0])
             ->leftJoin('user','to_uid','=','user.uid')
             ->selectRaw($fieldRaw)
             ->orderByRaw($orderbyRaw)
             ->forPage($page, $limit)
             ->get();
         //统计总数
         $count = Collect::where(['collect.from_uid'=>$f_uid,'collect.status'=>0])
             ->leftJoin('user','to_uid','=','user.uid')
             ->count();
         //数据返回
         if($t_data){
             $rs['teacher'] = $t_data;
             $rs['count'] = $count;
         }
         return $rs;
     }
    
}