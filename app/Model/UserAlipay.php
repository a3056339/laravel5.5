<?php
/**
 * Created by PhpStorm.
 * User: Li
 * Date: 15/11/4
 * Time: 上午10:38
 */
namespace App\Model;

class UserAlipay extends Model
{
    //添加或修改支付宝账号
    public static function teacherAlipay($uid,$alipay,$name)
    {
        if(!$data = self::where(['uid'=>$uid])->first()){
            //增加账号
            $data = new UserAlipay();
            $data->uid = $uid;
            $data->alipay = $alipay;
            $data->name = $name;
        }else{
            //修改账号
            $data->alipay = $alipay;
            $data->name = $name;
            $data->update_at = time();
        }
        //储存账号
        if($data->save())
            return true;
            return false;
    }



}