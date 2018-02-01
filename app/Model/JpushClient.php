<?php
namespace App\Model;

use JPush\Client as JPush;
use Config;

class JpushClient
{
    private static $jpush;

    private static $instance;

    public static function getInstance()
    {
        if (!(self::$jpush instanceof JPush)) {
            self::$jpush = new JPush(Config::get('app.jpush.app_key'), Config::get('app.jpush.master_secret'));
        }
        if (!(self::$instance instanceof self)) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    //极光推送
    public function push($user, $msg ,$extras = [],$title='心诊所', $production = false)
    {
        foreach ($user as $k => $v) {
            $user[$k] = 'user' . $v;
        }
        $production = env('APP_ENV') == 'product' ? false : true;
        try {
            $response = self::$jpush->push()
                ->setPlatform(['ios', 'android'])
                ->addAlias($user)
                ->setNotificationAlert($msg)
                ->iosNotification($msg, [
                    'sound'             => $msg,
                    'badge'             => +1,
                    'content-available' => true,
                    'category'          => 'jiguang',
                    'extras'            => $extras,
                ])
                ->androidNotification($msg, [
                    'title'  => $title,
                    'extras' => $extras,
                ])
                ->message($msg, [
                    'title'        => $title,
                    'content_type' => 'text',
                    'extras'       => $extras,
                ])
                ->options([
                    'apns_production' => $production,
                ])
                ->send();
            //return $response;
        } catch (\JPush\Exceptions\APIConnectionException $e) {
            // try something here
           // print $e;
        } catch (\JPush\Exceptions\APIRequestException $e) {
            // try something here
            //print $e;
        }
    }


}
