<?php
/**
 * Created by PhpStorm.
 * API基类
 * User: Li
 * Date: 15/7/3
 * Time: 下午2:40
 */
namespace App\Http\Api;

use App\Http\Controllers\Controller;
use App\Model\User;
use Cache;
use Request;

class ApiController extends Controller
{
    protected $_user;       //当前用户信息
    protected $_args;
    protected $_token;
    protected $appid;
    protected $version;
    protected $channel;
    protected $longitude;
    protected $latitude;
    protected $device;
    protected $device_app;

    public function __construct()
    {
        parent::__construct();

        //所有请求参数
        $this->_args  = Request::all();
        $this->_token = Request::input('token') ?: $this->header('Token');
        //根据access_token获取用户信息
        $this->_user      = $this->_getUser();
        $this->appid      = $this->header('Appid');
        $this->version    = $this->header('Version', '1.0');
        $this->channel    = $this->header('Channel_name', 'develop');
        $this->longitude  = $this->header('Longitude', '');
        $this->latitude   = $this->header('Latitude', '');
        $this->device_app = $this->header('Device', '');
        $this->device     = $this->getRealDevice($this->device_app);
    }

    /**
     * 封装Request方法(取get,post,put值)
     *
     * @param $key
     * @param $defaultValue
     *
     * @return bool
     */
    protected function input($key, $defaultValue = false)
    {
        return isset($this->_args[$key]) ? $this->_args[$key] : $defaultValue;
    }

    protected function header($key)
    {
        return Request::header($key);
    }

    protected function file($key)
    {
        return Request::file($key);
    }

    /**
     * 根据访问的access_token读取对应的用户信息
     */
    public function _getUser()
    {
        $token = $this->_token;
        if (!$token) {
            return false;
        }
        return User::getUser($token);
    }

    /**
     * //返回json数据
     *
     * @param array  $items
     * @param int    $err
     * @param string $msg
     *
     * @return array
     */
    function json($item, $s = 0)
    {

        $data = [
            'code'        => isset($item['code']) ? $item['code'] : 0,
            'msg'         => isset($item['msg']) ? $item['msg'] : '',
            'data'        => isset($item['data']) && $item['data'] ? $item['data'] : (object)[],
            'server_time' => time()

        ];
        return $data;
    }

    function getRealDevice($device)
    {
        if (substr($device, 0, 7) == 'android') {
            return 2;
        } elseif (substr($device, 0, 3) == 'ios') {
            return 1;
        } else {
            return 3;
        }
    }

}
