<?php
/**
 * Created by PhpStorm.
 * User: Li
 * Date: 15/11/4
 * Time: 上午10:38
 */
namespace App\Model;

class Tags extends Model
{
    public static function getTags($tags,$limit=10000)
    {
        $tag = [];
        if ($tags) {
            $tags_id = explode(',', $tags);
            $tag     = Tags::select(['id', 'title'])->whereIn('id', $tags_id)->take($limit)->get();
        }
        return $tag;
    }

    public static function getRecommendTeacher($where)
    {
        $data = User::from('user as a')->select([
            'a.uid',
            'a.name',
            'a.avatar',
            'a.title',
            'a.level',
            'a.work_year',
            'b.words_price',
            'b.phone_price'
        ])->where(['type' => User::TYPE_TEACHER, 'status' => 0])
            ->leftJoin('price as b', 'a.level', '=', 'b.level_id')
            ->where($where)
            ->orderByRaw('RAND()')
            ->get();
        return $data;
    }

}