<?php
/**
 * Created by PhpStorm.
 * User: Li
 * Date: 15/11/4
 * Time: 上午10:38
 */
namespace App\Model;

use App\Libraries\Alipaypay\aop\AopClient;
use App\Libraries\Alipaypay\aop\request\AlipayTradeRefundRequest;
use Config;
use Illuminate\Foundation\Auth\User;
use Log;

class UserRefund extends Model
{

    public static function reFund($status, $amount, $order_id)
    {
        $order = Order::where(['order_id' => $order_id, 'status' => Order::STATUS_THREE])->first();
        if (!$order) {
            return ['err' => true, 'msg' => '投诉订单不存在或已退款'];
        }
        $out_no = date("YmdHis") . rand(100, 999);
        if ($status == 1) {
            $amount = $order->amount;
            $out_no = '';
        }
        $alipay_config           = Config::get('app.alipaypay');
        $aop                     = new AopClient ();
        $aop->gatewayUrl         = 'https://openapi.alipay.com/gateway.do';
        $aop->appId              = $alipay_config['appid'];
        $aop->rsaPrivateKey      = $alipay_config['private_key'];
        $aop->alipayrsaPublicKey = $alipay_config['public_key'];
        $aop->apiVersion         = '1.0';
        $aop->signType           = 'RSA2';
        $aop->postCharset        = 'UTF-8';
        $aop->format             = 'json';
        $request                 = new AlipayTradeRefundRequest ();
        $data                    = [
            'out_trade_no'   => $order->order_no,
            'refund_amount'  => Order::getRealamount($amount),
            'refund_reason'  => '心诊所订单退款',
            'out_request_no' => $out_no
        ];
        $request->setBizContent(json_encode($data));
        $result       = $aop->execute($request);
        $responseNode = str_replace(".", "_", $request->getApiMethodName()) . "_response";
        $resultCode   = $result->$responseNode->code;
        if (!empty($resultCode) && $resultCode == 10000) {
            Order::where(['order_id' => $order_id])
                ->update([
                    'refund_status' => $status,
                    'refund_amount' => $amount,
                    'status'        => Order::STATUS_FIVE,
                    'refund_rason'  => '用户投诉后退款'
                ]);
            if ($status == 0&&$order->type!=Order::ORDER_TYPE_FOUR) {
                $amounts = Order::getAmount($order->amount);
                $price  = Order::getAmount($order->amount - $amount);
                UserAmount::where(['uid' => $order->to_uid])->where('freeze', '>', 0)->decrement('freeze', $amounts);
                UserAmount::where(['uid' => $order->to_uid])->increment('money', $price);
                UserAmount::where(['uid' => $order->to_uid])->decrement('amount', $amounts-$price);
            } else {
                if ($status == 1&&$order->type!=Order::ORDER_TYPE_FOUR) {
                    $amount = Order::getAmount($amount);
                    UserAmount::where(['uid' => $order->to_uid])->where('freeze', '>', 0)->decrement('freeze', $amount);
                    UserAmount::where(['uid' => $order->to_uid])->decrement('amount', $amount);
                    UserTalk::sendMssage(false, $order->from_uid, 1010, false);
                } else {
                    UserTalk::sendMssage(false, $order->from_uid, 1010, false);
                }
            }
        } else {
            return ['err' => true, 'msg' => '退款失败'];
        }
    }

    public static function reFundOrder($order_id)
    {
        $order                   = Order::where(['order_id' => $order_id, 'status' => Order::STATUS_ONE])->first();
        $alipay_config           = Config::get('app.alipaypay');
        $aop                     = new AopClient ();
        $aop->gatewayUrl         = 'https://openapi.alipay.com/gateway.do';
        $aop->appId              = $alipay_config['appid'];
        $aop->rsaPrivateKey      = $alipay_config['private_key'];
        $aop->alipayrsaPublicKey = $alipay_config['public_key'];
        $aop->apiVersion         = '1.0';
        $aop->signType           = 'RSA2';
        $aop->postCharset        = 'UTF-8';
        $aop->format             = 'json';
        $request                 = new AlipayTradeRefundRequest ();
        $data                    = [
            'out_trade_no'    => $order->order_no,
            'refund_amount'   => Order::getRealamount($order->amount),
            'refund_currency' => 'USD',
            'refund_reason'   => '心诊所订单退款',
        ];
        $request->setBizContent(json_encode($data));
        $result       = $aop->execute($request);
        $responseNode = str_replace(".", "_", $request->getApiMethodName()) . "_response";
        $resultCode   = $result->$responseNode->code;
        if (!empty($resultCode) && $resultCode == 10000) {
            Order::where(['order_id' => $order_id])
                ->update([
                    'refund_status' => 1,
                    'refund_amount' => $order->amount,
                    'status'        => Order::STATUS_FIVE,
                    'refund_rason'  => '超过24小时直接退款'
                ]);
            return ['err' => false, 'msg' => '退款成功'];
        } else {
            return ['err' => true, 'msg' => '退款失败'];
        }
    }


}