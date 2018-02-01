<?php
namespace App\Libraries\Sms;

use App\Libraries\Sms\ASms;
use App\Libraries\Sms\Curl;


class YunFengNew extends ASms
{
    protected $_smsType = 'advert' ;//营销类型//验证码verify_code

    /**
     * @desc
     * @param string $baseUrl  请求地址   对应文档 APIURL
     * @param string $userName 用户名       对应文档nonce_str
     * @param string $password 密码            对应文档app_secret
     * @param string $extArr   扩展参数  当前仅仅包含privateKey 对应文档 app_key
     * @return
     * @example
     * @date 2016年7月5日
     * @author yunfeng
     */
    function __construct($baseUrl,$userName,$password,$extArr)
    {
        $this->_baseUrl    = $baseUrl ;
        $this->_userName   = $userName ;
        $this->_password   = $password ;
        $this->_privateKey = $extArr['privateKey'] ;

        $this->_timeStamp = date("YmdHis") ;
        $this->_batchNum  = empty($this->_batchNum) ? $this->_userName
            .rand(100000,999999).time() : $this->_batchNum ;
    }

    function getSmsId()
    {
        return $this->_batchNum ;
    }

    /**
     * @desc 公钥算法:1、参数名ASCII码从小到大排序（字典序 ;
     *               2、参数名区分大小写；
     *               3、多个dest_id和mission_num没有先后顺序
     *               4、拼接app_secret进行md5
     * @param unknowtype
     * @return
     * @example
     * @date 2016年7月5日
     * @author yunfeng
     */
    private function __createSendSmsKey($mobileArr,$messageStr)
    {
        $string = "app_key=".$this->_privateKey."&".
            "batch_num=".$this->_batchNum."&".
            "content=".$messageStr."&" ;

        foreach ($mobileArr as $k=>$v)
        {
            $string = $string."dest_id=".$v."&" ;
        }
        foreach ($mobileArr as $k => $v)
        {
            $k++;
            $string = $string."mission_num=".$k."&" ;
        }
        $string = $string."nonce_str=".$this->_userName."&".
            "sms_type=".$this->_smsType."&".
            "time_stamp=".$this->_timeStamp."&" ;
        $string = $string."app_secret=".$this->_password ;

        $sign = md5($string) ;
        $this->_sign = $sign ;
        return $sign ;
    }

    private function __createPublicKey($paramsArr = array())
    {
        $headArr['app_key']    = $this->_privateKey ;
        $headArr['time_stamp'] = $this->_timeStamp ;
        $headArr['nonce_str']  = $this->_userName ;

        $paramsArr = array_merge($headArr,$paramsArr) ;
        ksort($paramsArr) ;
        $queryStr  = http_build_query ( $paramsArr) ;

        $this->_sign = md5($queryStr."&"."app_secret=".$this->_password) ;
        return $this->_sign ;
    }

    private function __createSentSmsRaw($mobileArr,$messageStr)
    {
        if(empty($this->_batchNum))
        {
            throw new \Exception("请先进行setUserName操作!") ;
        }

        $raw  = "<?xml version='1.0' encoding='UTF-8'?>" ;
        $raw .= "<xml>" ;
        $raw .= "<head>" ;
        $raw .= "<app_key>{$this->_privateKey}</app_key>" ;
        $raw .= "<time_stamp>".$this->_timeStamp."</time_stamp>" ;
        $raw .= "<nonce_str>{$this->_userName}</nonce_str>" ;
        $raw .= "<sign>".$this->__createSendSmsKey($mobileArr, $messageStr)."</sign>" ;
        $raw .= "</head>" ;
        $raw .= "<body>" ;
        $raw .= "<dests>" ;
        foreach ($mobileArr as $k => $v)
        {
            $k++;
            $raw .= "<dest>" ;
            $raw .= "<mission_num>".$k."</mission_num>" ;
            $raw .= "<dest_id>{$v}</dest_id>" ;
            $raw .= "</dest>" ;
        }
        $raw .= "</dests>" ;
        $raw .= "<batch_num>{$this->_batchNum}</batch_num>" ;
        $raw .= "<sms_type>{$this->_smsType}</sms_type>" ;
        $raw .= "<content>{$messageStr}</content>" ;
        $raw .= "</body>" ;
        $raw .= "</xml>" ;

        return $raw ;
    }

    /**
     * @desc
     * @param array $mobileArr
     * @param string $messageStr ansi编码字符串
     * @return
     * @example
     * @date 2016年7月5日
     * @author yunfeng
     */
    function sentSms($mobileArr,$messageStr)
    {

        $this->_raw = $this->__createSentSmsRaw($mobileArr,$messageStr) ;
        $this->_return = Curl::curlXml($this->_baseUrl, $this->_raw) ;
        return $this->_return ;
    }

    #请求数据和接收数据大集合
    function getRequestReturnCollect()
    {
        return array('url'=>$this->_baseUrl,'raw'=>$this->_raw,'return'=>$this->_return,'batchNum'=>$this->_batchNum) ;
    }

    private function __createBalanceRaw()
    {
        $raw  = "<?xml version='1.0' encoding='UTF-8'?>" ;
        $raw .= '<xml>' ;
        $raw .= "    <head>" ;
        $raw .= "        <app_key>{$this->_privateKey}</app_key>" ;
        $raw .= "        <time_stamp>".$this->_timeStamp."</time_stamp>" ;
        $raw .= "        <nonce_str>{$this->_userName}</nonce_str>" ;
        $raw .= "        <sign>".$this->__createPublicKey()."</sign>" ;
        $raw .= "    </head>" ;
        $raw .= "</xml>" ;
        return $raw ;
    }

    #取得余额
    function balance()
    {
        $url = "http://".parse_url($this->_baseUrl."/getSmsCount",PHP_URL_HOST )
            ."/stardy/balance_jy.jsp?"."&usr=".$this->_userName."&pwd=".$this->_password ;
        $this->_raw = $this->__createBalanceRaw() ;
        $this->_return = Curl::curlXml($this->_baseUrl."/getSmsCount", $this->_raw) ;
        return $this->_return ;
    }
}