<?php
/**
 * Created by PhpStorm.
 * User: Li
 * Date: 15/11/4
 * Time: 上午10:38
 */
namespace App\Model;

class Vip extends Model
{
    const VIP_STATUS = 1;//会员

    public static function getAmount($amount)
    {
        $vip    = self::select('sale')->value('sale');
        $amount = (($vip * 10) / 100) * $amount;
        return $amount;
    }

    public static function getNum()
    {
        return self::select('w_num', 'p_num')->first();
    }

    public static function getVipMessage($user, $type)
    {
        $msg = '';
        $vip = Vip::select(['w_num', 'p_num', 'sale'])->first();
        if (!$user && $type == Order::ORDER_TYPE_ZERO) {
            $msg = "升级为VIP送{$vip->w_num}次免费问答";
        } elseif (!$user && $type == Order::ORDER_TYPE_ONE) {
            $msg = "升级为VIP送{$vip->p_num}次电话咨询";
        } elseif (($qy = UserVipQy::where(['uid' => $user->uid])
                ->first()) && $user->is_vip == self::VIP_STATUS
        ) {
            if ($qy->word_num > 0 && $type == Order::ORDER_TYPE_ZERO) {
                $msg = "您是VIP会员，还可以免费提{$qy->word_num}个问题";
            }
            elseif ($qy->phone_num > 0 && $type == Order::ORDER_TYPE_ONE) {
                $msg = "您是VIP会员，可以免费电话咨询{$qy->phone_num}次";
            }elseif($type == Order::ORDER_TYPE_ZERO){
                $msg = "您是VIP可享受快问{$vip->sale}折优惠";
            }else{
                $msg = "您是VIP可享受电话咨询{$vip->sale}折优惠";
            }
        } elseif ($user->is_vip == self::VIP_STATUS && $type == Order::ORDER_TYPE_ZERO ) {
            $msg = "您是VIP可享受快问{$vip->sale}折优惠";
        }elseif ($user->is_vip == self::VIP_STATUS && $type == Order::ORDER_TYPE_ONE ) {
            $msg = "您是VIP可享受电话咨询{$vip->sale}折优惠";
        }
        elseif ($user->is_vip != self::VIP_STATUS && $type == Order::ORDER_TYPE_ZERO) {
            $msg = "升级为VIP送{$vip->w_num}次免费问答";
        } elseif ($user->is_vip != self::VIP_STATUS && $type == Order::ORDER_TYPE_ONE) {
            $msg = "升级为VIP送{$vip->p_num}次电话咨询";
        }

        return $msg;
    }
}