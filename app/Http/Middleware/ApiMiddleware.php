<?php
/**
 * Created by PhpStorm.
 * User: Li
 * Date: 15/9/14
 * Time: 上午10:05
 */
namespace App\Http\Middleware;

use App\Model\User;
use Closure;
use Request;
use Cache;

class ApiMiddleware
{
    protected $request;

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Closure                 $next
     *
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $this->request = $request;

        /** 验证access_token */
        $token = Request::input('token') ?: Request::header('Token');
        if (!$token) {
            return response(['code' => 3100, 'msg' => '登录已失效','data'=>(object)[]]);
        }
        //验证access_token是否有效
        if (!$this->checkToken($token)) {
            return response(['code' => 3100, 'msg' => '登录已失效','data'=>(object)[]]);
        }

        //记录日志
        $this->accessLog();

        return $next($request);
    }

    /**
     * 记录API访问日志 (后期需放入队列中) 只记录put,post,delete
     */
    protected function accessLog()
    {
        if (Request::method() == 'GET') {
            return;
        }
        $token = Request::input('access_token') ?: Request::header('Access-Token');
        $uid   = User::getUid($token);

    }

    /** 验证sign */
    protected function checkSign()
    {
        //todo
        return true;
    }

    /**
     * 验证access_token
     *
     * @param $token
     *
     * @return bool
     */
    protected function checkToken($token)
    {
        if ($token == 'im-a-visit') {
            return true;
        }
        $uid = User::getUid($token);
        if (!$uid) {
            return false;
        }
        return true;
    }

}