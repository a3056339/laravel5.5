<?php
/**
 * Created by PhpStorm.
 * User: Li
 * Date: 15/11/4
 * Time: 上午10:38
 */
namespace App\Model;

use Request;
use Config;
use Log;
use Illuminate\Support\Facades\Redis;
class UserTalk extends Model
{
    const TALK_KEY = 'xingzhengsuo_talk';

    public static function saveMSG($to_uid, $type, $msg)
    {
        $system = Config::get('app.system_user');
        $from_uid=$system['uid'];
        self::insert([
            'from_uid'  => $system['uid'],
            'to_uid'    => $to_uid,
            'type'      => $type,
            'msg'       => $msg,
            'create_at' => time()
        ]);
        $key      = UserTalk::TALK_KEY . '-' . $to_uid;
        Redis::del($key);
        return true;
    }

    public static function sendMsg($to_uid, $content, $user)
    {
        $url = Config::get('app.url');
        // 推送的url地址，上线时改成自己的服务器地址
        $push_api_url = $url . ":2121/";
        $post_data    = [
            'type'    => 'publish',
            'content' => ['content' => $content, 'user' => $user],
            'to'      => $to_uid,
        ];
        $ch           = curl_init();
        curl_setopt($ch, CURLOPT_URL, $push_api_url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($post_data));
        $return = curl_exec($ch);
        curl_close($ch);
        return $return;
    }

    public static $system = [
        1001 => ['msg' => '您已成功购买了XX治疗方案，老师会在24小时内给您电话回复，请保持手机畅通。', 'sms' => false, 'notice' => true,'jpush'=>true],
        1002 => ['msg' => '您的治疗方案服务已完成，继续咨询可升级VIP用户', 'sms' => false, 'notice' => true,'jpush'=>true],
        1003 => ['msg' => '您已成功购买了XX老师的年度心灵管家服务，未来一年内可无限次电话咨询，享受一对一贴心管家式服务。', 'sms' => false, 'notice' => true,'jpush'=>true],
        1004 => ['msg' => '您的（XX老师）的年度心灵管家服务3天后将到期。为避免影响您的问题咨询，请及时续费哦。', 'sms' => false, 'notice' => true,'jpush'=>true],
        1005 => ['msg' => '您的（XX老师）的年度心灵管家服务已到期。为方便咨询请及时续费哦。', 'sms' => false, 'notice' => true,'jpush'=>true],
        1006 => ['msg' => '恭喜您，成为年度VIP用户，所有咨询服务您都能享受8折优惠。更多权益可前往会员中心查看。', 'sms' => false, 'notice' => true,'jpush'=>true],
        1007 => ['msg' => '您的年度VIP服务已到期，为方便咨询，请及时前往会员中心续费哦。', 'sms' => false, 'notice' => true,'jpush'=>true],
        1008 => ['msg' => '您的订单因导师超过24小时未接单而被关闭了，相关费用已退还到您的账户，您可以选择继续咨询或向其他导师咨询', 'sms' => false, 'notice' => true,'jpush'=>true],
        1009 => ['msg' => '您已提现成功', 'sms' => false, 'notice' => true,'jpush'=>true],
        1010 => ['msg' => '您的退款申请已通过，相关费用已退还到您的账户中，请注意查收。', 'sms' => false, 'notice' => true,'jpush'=>true],
        1011 => ['msg' => '您申请的退款，被拒绝。如有问题，请联系客服。', 'sms' => false, 'notice' => true,'jpush'=>true],
        1012 => ['msg' => '您有一个新订单，请立即前往咨询室“文字咨询订单”页面查看，并与客户联系，超过24小时未确认接单就会取消订单并退款。', 'sms' => true, 'notice' => true,'jpush'=>true],
        1013 => ['msg' => 'XX客户购买了您推荐的治疗方案##，请在24小时内与客户联系沟通。', 'sms' => false, 'notice' => true,'jpush'=>true],
        1014 => ['msg' => '您有一个新订单，请在咨询室“电话咨询订单”页面查看，并与客户联系，超过24小时未确认接单就会取消订单并退款。', 'sms' => true, 'notice' => true,'jpush'=>true],
        1015 => ['msg' => '恭喜您，XX用户购买了您的年度“心灵管家”服务，您的信任度再次升级，请保持电话畅通，以便用户及时联系您。', 'sms' => false, 'notice' => true,'jpush'=>true],
        1016 => ['msg' => '你有一个订单将在1小时后关闭，请您及时处理，为客户提供服务。立即查看。', 'sms' => true, 'notice' => true,'jpush'=>true],
        1017 => ['msg' => '您有一个订单被关闭，因用户付款后超过24小时您还未确认接单。为给用户提供及时的服务，请一定及时为客户提供服务。', 'sms' => true, 'notice' => true,'jpush'=>true],
        1018 => ['msg' => '您的订单导师超过24小时未接单，已关闭并退回免费次数，您可以选择其他导师的服务。', 'sms' => false, 'notice' => true,'jpush'=>true],
        1019 => ['msg' => '欢迎您来到心诊所，我们有美式权威的少儿心理学专家，竭诚为您服务！', 'sms' => false, 'notice' => true,'jpush'=>false],
        1020 => ['msg' => '欢迎您来到心诊所，我们有海量的儿童问题想向您咨询哦！', 'sms' => false, 'notice' => true,'jpush'=>false],
    ];

    public static function sendMssage($uid = false, $to_uid, $code, $id = false, $type = 3)
    {
        $info  = self::$system[$code];
        $msg   = $info['msg'];
        $title = '';
        if ($id) {
            $title = TjPackages::where(['id' => $id])->value('title');
        }
        if ($uid) {
            $user  = User::select(['name', 'phone'])->where(['uid' => $uid])->first();
            $msg   = str_replace('XX', $user->name, $msg);
            $msg   = str_replace('##', $title, $msg);
        }
        $phone =User::where(['uid'=>$to_uid])->value('phone');
        if($phone){
           $phone = substr($phone,2,11);
        }
        $drivce =UserDevice::where(['uid'=>$to_uid])->first();
        $where=[
          'app_id'=>$drivce->app_id,
          'version'=>$drivce->version,
          'channel'=>$drivce->channel
        ];
        $switch  = SystemPay::where($where)->first();
        $is_open = 0;
        if ($switch) {
            $is_open = $switch->switch;
        }
        if($is_open!=1){
            if ($info['sms']) {
                if ($phone) {
                    self::sendSms($phone, $msg);
                }
            }
            if ($info['notice']) {
                self::saveMSG($to_uid, $type, $msg);
            }
            if($info['jpush']){
                $extras = ['type' => 'room', 'content' => $msg];
                JpushClient::getInstance()->push([$to_uid], $msg, $extras);
            }
        }
    }

    public static function sendSms($phone, $msg)
    {
        $post_data            = [];
        $post_data['sign']    = 'ab1ea1439870a9ef8bd7f252e90c7fec';
        $post_data['content'] = $msg;//$sign . $this->content;
        $post_data['mobile']  = $phone;
        $url                  = 'http://sms.xiake99.com/notice';
        $o                    = '';
        foreach ($post_data as $k => $v) {
            $o .= "$k=" . urlencode($v) . '&';
        }
//$post_data = substr($o, 0, -1);
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); //如果需要将结果直接返回到变量里，那加上这句。
        $result = curl_exec($ch);
        curl_close($ch);
        $result = json_decode($result, true);
    }

    public static function clearMessage($uid, $type)
    {
        self::where(['to_uid' => $uid, 'type' => $type])->update(['status' => 1, 'update_at' => time()]);
    }
}