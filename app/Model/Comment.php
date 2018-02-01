<?php
/**
 * Created by PhpStorm.
 * User: Li
 * Date: 17/12/7
 * Time: 上午10:38
 */
namespace App\Model;

use App\Model\User;

class Comment extends Model
{
    public static function teacherComment($uid,$page = 1,$limit = 10)
    {
        $rs = [];
        //取出的数据
        $data = self::where(['to_uid' => $uid])
            ->forPage($page, $limit)
            ->orderBy('create_at', 'DESC')
            ->get();

        //数据处理
        if ($data) {
            $rs = $data;
            //评论人的信息
            foreach ($data as $k => $v) {
                $f_name                  = User::where(['uid' => $v->from_uid, 'status' => 0])->value('name');
                $data[$k]['from_name']   = $f_name ? $f_name : '';
                $data[$k]['create_time'] = date("Y.m.d", strtotime($v->create_at));
                unset($data[$k]['create_at']);
            }
        }
        return $rs;
    }

    public static function commentNum($uid)
    {
        $conditionRaw = 'to_uid =' . $uid;
        $data         = self::whereRaw($conditionRaw)->count();
        return $data ? $data : 0;
    }


}