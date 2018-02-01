<?php
/**
 * Created by PhpStorm.
 * User: Li
 * Date: 15/11/4
 * Time: 上午10:38
 */
namespace App\Model;

class OrderRelation extends Model
{


    public static function getRealtion($order_id)
    {
        $realtion = self::where(['order_id' => $order_id])->first();
        if (!$realtion) {
            return [];
        }
        $project = explode(',', $realtion->project);
        if (!$project) {
            return [];
        }
        $data = TjPackages::select(['id', 'title', 'tags', 'intro', 'price','img'])->whereIn('id', $project)->get();
        foreach ($data as $k => $val) {
            $info             = Order::where(['relation_order' => $order_id, 'relation_id' => $val->id])->first();
            $data[$k]->status = $info ? $info->status : 0;
            //推荐套餐付款成功时间
            $data[$k]->time = $info? date("Y-m-d H:i:s",strtotime($info->create_at)): '' ;
        }
        return $data;
    }
}