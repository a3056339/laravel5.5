<?php
/**
 * Created by PhpStorm.
 * User: Li
 * Date: 15/11/4
 * Time: 上午10:38
 */
namespace App\Model;

use App\Libraries\Alipaypay\aop\AopClient;
use App\Libraries\Alipaypay\aop\request\AlipayFundTransToaccountTransferRequest;
use Config;
use Log;

class UserWithdraw extends Model
{


    public static function userWithdraw($id,$uid)
    {
        $info = self::where(['id' => $id, 'status' => 1])->first();
        if (!$info) {
            return ['status' => -1, 'msg' => '该提现申请不存在'];//避免重复提现
        }

        $detail_data             = $info->id . "^" . $info->account . "^" . $info->name . "^" . $info->amount . "^心诊所提现{$info->amount}元" . "";
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
        $request                 = new AlipayFundTransToaccountTransferRequest ();
        $data                    = [
            'out_biz_no'    => $info->batch_no,
            'payee_type'    => 'ALIPAY_LOGONID',
            'payee_account' => $info->account,
            'amount'        => Order::getRealamount($info->amount),
            'remark'        => $detail_data
        ];
        $data                    = json_encode($data);
        $request->setBizContent($data);
        $result = $aop->execute($request);

        $responseNode = str_replace(".", "_", $request->getApiMethodName()) . "_response";
        $resultCode   = $result->$responseNode->code;
        if (!empty($resultCode) && $resultCode == 10000) {
            self::where(['id' => $id, 'status' => 1])->update([
                'status'      => 2,
                'update_at'   => time(),
                'finished_at' => time(),
                'caiwu_id'=>$uid
            ]);
            UserAmount::where(['uid' => $info->uid])->increment('unamount', $info->amount);
            UserAmount::where(['uid' => $info->uid])->decrement('freeze', $info->amount);
            UserAmount::where(['uid' => $info->uid])->decrement('amount', $info->amount);
            UserTalk::sendMssage(false, $info->uid, 1009, false);
            return ['status' => 0];
        } else {
            return ['status' => -1];
        }
    }

    public static function refund($id)
    {
        $info = self::where(['id' => $id])->first();
        UserAmount::where(['uid' => $info->uid])->increment('money', $info->amount);
        UserAmount::where(['uid' => $info->uid])->decrement('freeze', $info->amount);
    }


}