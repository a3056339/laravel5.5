<?php
/**
 * Created by PhpStorm.
 * User: Li
 * Date: 15/11/4
 * Time: 上午10:38
 */
namespace App\Model;

use App\Libraries\Alipaypay\aop\AopClient;
use App\Libraries\Alipaypay\aop\request\AlipayTradeWapPayRequest;
use Config;
use Log;
use DB;
use PDOException;

class Order extends Model
{
    const SALE             = 0.8;//会员打8折 待定
    const STATUS_CLOSE     = -1;//关闭
    const STATUS_ZERO      = 0;//代付款
    const STATUS_ONE       = 1;//待服务
    const STATUS_TWO       = 2;//待评价
    const STATUS_THREE     = 3;//投诉
    const STATUS_FIVE      = 5;//退款
    const STATUS_FOUR      = 4;//已评价
    const ORDER_TYPE_ZERO  = 0;//文字咨询
    const ORDER_TYPE_ONE   = 1;//电话咨询
    const ORDER_TYPE_TWO   = 2;//年度管家
    const ORDER_TYPE_THREE = 3;//升级会员
    const ORDER_TYPE_FOUR  = 4;//推荐套餐

    //生成唯一订单编号
    public static function makePaySn($uid)
    {
        return mt_rand(100, 999)
        . sprintf('%010d', time() - 946656000)
        . sprintf('%03d', (float)microtime() * 1000)
        . sprintf('%03d', (int)$uid % 1000);
    }


    public static function changeOrder($order_id, $uid, $to_uid, $is_vip, $type = 0, $pay = false)
    {
        $vipqy = UserVipQy::where(['uid' => $uid])->first();
        $major = UserMajor::where(['from_uid' => $uid, 'to_uid' => $to_uid, 'status' => 0])->first();//是否是年度心里管家
        if ($pay) {
            self::updateOrder($order_id, $uid, $type, $to_uid);
            return true;
        }
        if ($major) {
            self::updateOrder($order_id, $uid, 2, $to_uid);
            return true;
        }
        if ($is_vip != Vip::VIP_STATUS) {
            return false;
        }
        if (!$vipqy) {
            return false;
        }
        if ($is_vip != Vip::VIP_STATUS && $type == self::ORDER_TYPE_ONE) {//电话咨询必须是会员
            return false;
        }
        if ($type == self::ORDER_TYPE_ZERO && $vipqy->word_num >= 1) {
            self::updateOrder($order_id, $uid, $type, $to_uid);
            return true;
        } elseif ($type == self::ORDER_TYPE_ONE && $vipqy->phone_num >= 1) {
            self::updateOrder($order_id, $uid, $type, $to_uid);
            return true;
        }
        return false;
    }

    public static function updateOrder($order_id, $uid, $type, $to_uid)
    {
        $order = [
            'pay_code'    => '',
            'pay_time'    => time(),
            'finnshed_at' => time(),
            'status'      => self::STATUS_ONE,
            'update_at'   => time(),
        ];
        if ($type == self::ORDER_TYPE_ONE) {
            $vip_data            = Vip::select(['w_num', 'p_num', 'length', 'sale'])->first();
            $order['timelength'] = $vip_data ? $vip_data->length * 60 : 30 * 60;
        }
        DB::beginTransaction();
        $info = Order::where(['order_id' => $order_id])->first();
        try {
            if ($type == self::ORDER_TYPE_ZERO) {
                $order['is_free'] = 1;
                UserVipQy::where(['uid' => $uid])->where('word_num', '>', 0)->decrement('word_num', 1);
                UserTalk::sendMssage(false, $info->to_uid, 1012, false, $type);
                $res  = User::where(['uid' => $info->from_uid])->first();
                $name = $res->name ? $res->name : substr($res->phone, 8, 5);
                Notice::insert([
                    'intro'     => $name . ' 发布了快问',
                    'type'      => 2,
                    'create_at' => time()
                ]);
            } elseif ($type == self::ORDER_TYPE_ONE) {
                $order['is_free'] = 1;
                UserVipQy::where(['uid' => $uid])->where('phone_num', '>', 0)->decrement('phone_num', 1);
                UserTalk::sendMssage(false, $info->to_uid, 1014, false, $type);
            } else {
                $order['is_free'] = 2;
                if ($info->type == self::ORDER_TYPE_ZERO) {
                    UserTalk::sendMssage(false, $info->to_uid, 1012, false, $info->type);
                }
                if ($info->type == self::ORDER_TYPE_ONE) {
                    UserTalk::sendMssage(false, $info->to_uid, 1014, false, $info->type);
                }
            }
            self::where(['order_id' => $order_id])->update($order);
            if (UserAdvisory::where(['from_uid' => $uid, 'to_uid' => $to_uid])->first()) {
                UserAdvisory::where(['from_uid' => $uid, 'to_uid' => $to_uid])
                    ->update(['update_at' => time()]);
            } else {
                UserAdvisory::insert([
                    'from_uid'  => $uid,
                    'to_uid'    => $to_uid,
                    'create_at' => time()
                ]);
            }
            DB::commit();
        } catch (PDOException $ex) {
            DB::rollback();    //失败，回滚事务
            echo 'error';
        }

    }

    /**
     * 支付宝wap支付
     */
    public static function create_charge($_order = [], $sub_title)
    {

        $request              = new AlipayTradeWapPayRequest();
        $configs              = Config::get('app.alipaypay');
        $body                 = [];
        $body["out_trade_no"] = $_order['order_no'];
        $body["subject"]      = $sub_title;
        $body["seller_id"]    = "";
        $body["total_amount"] = self::getRealamount($_order['amount']);
        $request->setBizContent(json_encode($body));
        $request->setNotifyUrl($configs['notice_url']);
        $request->setReturnUrl($configs['return_url']);

        $aop                     = new AopClient ();
        $aop->appId              = $configs['appid'];
        $aop->rsaPrivateKey      = $configs['private_key'];
        $aop->alipayrsaPublicKey = $configs['public_key'];
        $aop->apiVersion         = "1.0";
        $aop->signType           = 'RSA2';
        // 开启页面信息输出
        $aop->debugInfo = true;

        $result = $aop->pageExecute($request, "post");
        return $result;


    }

    public static function order_user_info($order_id)
    {
        $data = self::where(['order_id' => $order_id])
            ->leftJoin('user', 'order.from_uid', '=', 'user.uid')
            ->select('order_id', 'user.name', 'user.avatar', 'content')
            ->first();
        if ($data) {
            $data->content = $data->content ? $data->content : '';
            return $data;
        } else {
            return (object)[];
        }
    }

    public static function getOrderId($order_id, $id)
    {
        if ($id > 0) {
            $order = self::where(['relation_id' => $id, 'type' => self::ORDER_TYPE_FOUR, 'relation_order' => $order_id])
                ->first();
            return $order ? $order->order_id : '';
        }
        return $order_id;
    }

    public static function userAmount($amount, $uid, $order_id)
    {
        UserAmountLog::insert([
            'uid'       => $uid,
            'amount'    => $amount,//待定
            'order_id'  => $order_id,
            'create_at' => time()
        ]);
        UserAmount::saveAmount($uid, $amount);
    }

    public static function getAmount($amount)
    {
        return self::getRealamount($amount * 0.7);
    }

    /**
     * 格式化金额
     *
     * @param     $str     需要格式化的字符串
     * @param int $ws      保留几位小数
     *
     * @return string  返回格式化的数据
     */
    public static function getRealamount($str, $ws = 2)
    {
        return sprintf("%.{$ws}f", round($str, $ws));
    }


}