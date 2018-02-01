<?php
namespace App\Libraries\Sms;
abstract class ASms
{
    #请求路径
    protected $_baseUrl ;

    #用户名
    protected $_userName ;

    #密码
    protected $_password ;

    #私钥
    protected $_privateKey ;

    #时间戳
    protected $_timeStamp ;

    #短信批次号，每次发送短信客户端自定义字符串号，用于与运营商和对短信
    protected $_batchNum ;

    #运营商数据返回
    protected $_return ;

    function __construct($baseUrl,$userName,$password,$extArr)
    {

        $this->_baseUrl    = $baseUrl ;
        $this->_userName   = $userName ;
        $this->_password   = $password ;
        $this->_privateKey = $extArr['privateKey'] ;


        $this->_timeStamp = date("YmdHis") ;
        $this->_batchNum  = empty($this->_batchNum) ? $this->_userName.rand(100000,999999).time() : $this->_batchNum ;
    }

    #取得批次号
    function getSmsId()
    {
        return $this->_batchNum ;
    }

    #短信发送和接收数据
    abstract function getRequestReturnCollect() ;

    /**
     * @desc
     * @access
     * @param array $mobileArr   手机号码
     * @param string $messageStr 短信内容
     * @return
     * @example
     */
    abstract function sentSms($mobileArr,$messageStr) ;

    abstract function balance() ;
}
