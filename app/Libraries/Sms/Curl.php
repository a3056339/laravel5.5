<?php
namespace App\Libraries\Sms;
class Curl
{
    static function curlXml($url,$xmlData)
    {
        //初始一个curl会话
        $curl = curl_init() ;
        //设置url
        curl_setopt($curl, CURLOPT_URL,$url) ;
        //设置发送方式：
        curl_setopt($curl, CURLOPT_POST, true) ;
        //设置发送数据
        curl_setopt($curl, CURLOPT_POSTFIELDS, $xmlData) ;
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true ) ;
        curl_setopt($curl, CURLOPT_TIMEOUT,3);//超时时间
        //抓取URL并把它传递给浏览器
        $return = curl_exec($curl) ;
        //关闭cURL资源，并且释放系统资源
        curl_close($curl) ;
        return $return ;
    }
    static function curlSpider($url)
    {
        $ch  =  curl_init () ;

        // 设置URL和相应的选项
        curl_setopt ( $ch ,  CURLOPT_URL ,  $url ) ;
        curl_setopt ( $ch ,  CURLOPT_HEADER ,  0 ) ;
        curl_setopt ( $ch ,  CURLOPT_RETURNTRANSFER ,  true ) ;

        // 抓取URL并把它传递给浏览器
        $result = curl_exec ( $ch ) ;

        // 关闭cURL资源，并且释放系统资源
        curl_close ( $ch ) ;
        return $result ;
    }
}