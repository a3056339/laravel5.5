<?php
/**
 * Created by PhpStorm.
 * User: Li
 * Date: 15/11/4
 * Time: 上午10:38
 */
namespace App\Model;

class User extends Model
{

    public $timestamps = false;

    const LEVEL_ONE    = 1;//普通
    const LEVEL_TWO    = 2;//专家
    const LEVEL_THREE  = 3;//高级
    const STATUS       = 0;//有效性
    const TYPE_TEACHER = 1;//老师
    const TYPE_STUDENT = 0;//学生

    /**
     * 根据access_token查询uid
     *
     * @param $token
     *
     * @return bool (返回uid)
     */
    public static function getUid($token)
    {
        if ($res = self::where('token', $token)->first()) {
            return $res->uid;
        }
        return false;
    }

    /**
     * 获取token对应的用户信息
     *
     * @param $token
     *
     * @return bool
     */
    public static function getUser($token)
    {
        $user = $res = self::where('token', $token)->first();
        if (!$user) {
            return false;
        }
        return $user;
    }

    /**
     * 验证码登录
     *
     * @param $token
     *
     * @return bool
     */
    public static function userLoginCode($phone_st, $phone)
    {
        $user  = self::where(['phone' => $phone_st])->first();
        $token = self::createToken();
        if (!$user) {
            $data['token']      = $token;
            $data['phone']      = $phone_st;
            $data['call_phone'] = $phone;
            $data['type']       = self::TYPE_STUDENT;
            $data['create_at']  = time();
            $result             = self::insertGetId($data);
            if ($result) {
                $uid = self::createUserUid($result);
                self::where(['id' => $result])->update(['uid' => $uid]);
                $user = self::where(['uid' => $uid])->first();
                $user->act =1;
            }
        } else {
            self::where(['id' => $user->id])->update(['token' => $token]);
            $user->token = $token;
            $user->act =0;
        }
        $user->birthday = date("Y-m-d", $user->birthday);
        return $user;
    }

    /**
     * 创建唯一的用户uid
     *
     * @param $token
     *
     * @return bool
     */
    public static function createUserUid($result)
    {
        $uid = rand(1, 8) . str_pad($result, 7, '0', STR_PAD_RIGHT);
        if(env('APP_ENV')=='product'){
            $uid = $result . rand(10, 100) . rand(10, 100) . rand(10, 100);
        }
        if (self::where(['uid' => $uid])->first()) {
            self::createUserUid($result);
        }
        return $uid;
    }

    /**
     * 生成用户唯一token
     *
     * @param $token
     *
     * @return bool
     */
    public static function createToken()
    {
        $token = str_random(32);
        if (self::where(['token' => $token])->first()) {
            self::createToken();
        }
        return $token;
    }

    /**
     * 微信登录
     *
     * @param $token
     *
     * @return bool
     */
    public static function userLoginWeixin($open_id, $name, $avatar)
    {
        $user  = self::where(['open_id' => $open_id])->first();
        $token = self::createToken();
        if (!$user) {
            $data['token']     = $token;
            $data['open_id']   = $open_id;
            $data['create_at'] = time();
            $data['type']      = self::TYPE_STUDENT;
            $data['name']      = $name;
            $data['avatar']    = $avatar;
            $result            = self::insertGetId($data);
            if ($result) {
                $uid = self::createUserUid($result);
                self::where(['id' => $result])->update(['uid' => $uid]);
            }
            $user = self::where(['id' => $result])->first();
            $user->act =1;
        } else {
            self::where(['id' => $user->id])->update(['token' => $token]);
            $user->token = $token;
            $user->act =0;
        }
        $user->birthday = date("Y-m-d", $user->birthday);
        return $user;
    }

    /**
     * 用户token登录
     *
     * @param $token
     *
     * @return bool
     */
    public static function userLoginToken($token)
    {
        $user = self::where(['token' => $token])->first();
        if (!$user) {
            return (object)['status' => -1];
        }
        $user->birthday = date("Y-m-d", $user->birthday);
        return $user;
    }

    /**
     * 修改用户信息
     *
     * @param $token
     *
     * @return bool
     */
    public static function updateUserInfo($id, $longitude, $latitude)
    {
        $data = [
            'longitude' => $longitude,
            'latitude'  => $latitude,
            'login_at'  => time(),
        ];
        self::where(['id' => $id])->update($data);
        return true;
    }


    public static function teacherDetail($data)
    {
        $rs = [];
        if ($data) {
            //多层数据处理
            $rs['uid']         = $data->uid;
            $rs['name']        = $data->name;
            $rs['avatar']      = $data->avatar;
            $rs['title']       = $data->title;
            $rs['tags']        = Tags::getTags($data->tags);
            $rs['work_year']   = $data->work_year;
            $rs['advisory']    = $data->advisory;
            $rs['level']       = $data->level;
            $rs['service']     = $data->service ? self::getService($data->service) : [];
            $rs['intro']       = $data->intro ? $data->intro : '';
            $info              = Price::where(['level_id' => $data->level])->first();
            $rs['video']       = UserVideo::getVideo($data->uid);
            $rs['words_price'] = $info->words_price;
            $rs['phone_price'] = $data->price ? $data->price : $info->phone_price;
        }
        return $rs;
    }

    public static function getUserBy($type, $uid)
    {
        $user = self::where(['uid' => $uid, 'status' => self::STATUS])->first();//取出老师的级别
        switch ($type) {
            case  Order::ORDER_TYPE_ZERO:
                return Price::returnPrice($user->level, 'words_price');
                break;
            case  Order::ORDER_TYPE_ONE:
                if ($user->price > 0) {
                    return $user->price;
                } else {
                    return Price::returnPrice($user->level, 'phone_price');
                }
                break;
            case  Order::ORDER_TYPE_TWO:
                if ($user->year_price > 0) {
                    return $user->year_price;
                } else {
                    return Price::returnPrice($user->level, 'year_price');
                }
                break;
            case  Order::ORDER_TYPE_THREE:
                return Vip::select('price')->value('price');//会员价格待定
                break;
            default:
                return Price::returnPrice($user->level, 'words_price');
                break;
        }
    }

    public static function getService($service)
    {
        $services = explode(',', $service);
        $service  = [];
        foreach ($services as $key => $val) {
            if ($val) {
                $service[] = self::getDay($val);
            }
        }
        $service = UserMajor::ArraySort($service, 'id', false);
        return $service;
    }

    public static function getDay($day)
    {
        switch ($day) {
            case '周-':
                return ['id' => 1, 'title' => $day];
                break;
            case '周二':
                return ['id' => 2, 'title' => $day];
                break;
            case '周三':
                return ['id' => 3, 'title' => $day];
                break;
            case '周四':
                return ['id' => 4, 'title' => $day];
                break;
            case '周五':
                return ['id' => 5, 'title' => $day];
                break;
            case '周六':
                return ['id' => 6, 'title' => $day];
                break;
            case '周日':
                return ['id' => 7, 'title' => $day];
                break;
            default:
                return ['id' => 1, 'title' => $day];
                break;
        }
    }

    public static function getTeacher($where = [], $map = [], $page = 1, $limit = 10,$w=false)
    {
        $mapGroup=[];
        if($w){
            $mapGroup=function($query){
                $query->where('sort','>',0);
            };
        }
        $data = self::select(['uid', 'name', 'avatar', 'tags', 'title', 'work_year', 'advisory', 'level', 'intro','tags_index'])
            ->where($where)
            ->where(['type' => User::TYPE_TEACHER, 'status' => User::STATUS])
            ->where($map)
            ->where($mapGroup)
            ->forPage($page, $limit)
            ->orderBy('sort', 'desc')
            ->get();
        return $data;
    }

    public static function getTeacherCount($where = [], $map = [])
    {
        $count = self::select(['uid'])
            ->where($where)
            ->where(['type' => User::TYPE_TEACHER, 'status' => User::STATUS])
            ->where($map)->count();
        return $count;
    }


}