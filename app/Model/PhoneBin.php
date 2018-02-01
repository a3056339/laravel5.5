<?php
/**
 * Created by PhpStorm.
 * User: Li
 * Date: 15/11/4
 * Time: 上午10:38
 */
namespace App\Model;

use App\Libraries\AliyunDypls\PlsPhone;
use DB;

class PhoneBin extends Model
{

    //随机给一个中间号
    public static function getPhoneX($from_uid, $to_uid)
    {
        $bin = self::where(function ($query) use ($from_uid, $to_uid) {
            $query->Where(['from_uid' => $from_uid, 'to_uid' => $to_uid])
                ->orWhere(function ($query) use ($from_uid, $to_uid) {
                    $query->Where(['to_uid' => $from_uid, 'from_uid' => $to_uid]);
                });
        })->first();
        if (!$bin) {
            $phone = AliyunPhone::where(['status' => 0])->orderBy(DB::raw('rand()'))->value('phone');
            if ($phone) {
                return $phone;
            }
            return '';
        }
        $phone = AliyunPhone::where(['status' => 0])
            ->where('phone', '!=', $bin->phone)
            ->orderBy(DB::raw('rand()'))
            ->value('phone');
        if ($phone) {
            return $phone;
        }
        return '';
    }

    //检查a->b或者 b->a是否绑定过避免重复绑定
    public static function checkPhoneBin($from_uid, $to_uid)
    {
        $bin = self::where(function ($query) use ($from_uid, $to_uid) {
            $query->Where(['from_uid' => $from_uid, 'to_uid' => $to_uid])
            ->orWhere(function ($query) use ($from_uid, $to_uid) {
                $query->Where(['to_uid' => $from_uid, 'from_uid' => $to_uid]);
            });
        })->first();
        if ($bin) {
            $axbResponse = PlsPhone::unbindSubscription($bin->subsId,$bin->secretNo);
            if($axbResponse->Code == 'OK'){
                self::unbindPhonX($from_uid, $to_uid);
                AliyunPhone::where(['phone'=>$bin->phone])->update(['status'=>0,'update_at'=>time()]);
            }
        }
        return true;
    }

    public static function saveBin($from_uid, $to_uid, $phone, $axbSubsId, $axbSecretNo)
    {
        self::insert([
            'from_uid'  => $from_uid,
            'to_uid'    => $to_uid,
            'phone'     => $phone,
            'subsId'    => $axbSubsId,
            'secretNo'  => $axbSecretNo,
            'create_at' => time()
        ]);
        AliyunPhone::where(['phone'=>$phone])->update(['status'=>1,'update_at'=>time()]);
        return true;
    }

    public static function unbindPhonX($from_uid, $to_uid)
    {
        self::where(function ($query) use ($from_uid, $to_uid) {
            $query->Where(['from_uid' => $from_uid, 'to_uid' => $to_uid])
                ->orWhere(function ($query) use ($from_uid, $to_uid) {
                    $query->Where(['to_uid' => $from_uid, 'from_uid' => $to_uid]);
                });
        })->delete();
    }

}