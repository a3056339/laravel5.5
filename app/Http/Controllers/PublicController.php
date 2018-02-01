<?php
/**
 * Created by PhpStorm.
 * User: Li
 * Date: 15/7/8
 * Time: 下午1:43
 */
namespace App\Http\Controllers;


use Symfony\Component\HttpFoundation\Session\Session;
use App\Model\User;
use DB;
use Request;
use Config;


class PublicController extends Controller
{


    public function login()
    {
        $session = new Session;
        $session->remove('token');
        return view('login');
    }

    public function auth()
    {
        $username = Request::input('uername');
        $password = Request::input('password');
        if (!$username) {
            return response(['msg' => '用户名不能为空', 'code' => 403]);
        }
        if (!$password) {
            return response(['msg' => '密码不能为空', 'code' => 403]);
        }
        $system = Config::get('app.system_user');
        if ($username != $system['uid']) {
            return response(['msg' => '用户名错误', 'code' => 403]);
        }
        if ($password != 'xingzhengsuo888') {
            return response(['msg' => '密码错误', 'code' => 403]);
        }
        $session = new Session;
        $session->set("token", str_random(32));
        return response(['code' => 0, 'msg' => 'ok', 'redirect_url' => '/chat']);
    }

    public function chat()
    {
        $session = new Session;
        $token   = $session->get("token");
        if (!$token) {
            return redirect()->to('/login');
        }
        $system = Config::get('app.system_user');
        $user   = User::select(['name', 'avatar'])->where(['uid' => $system['uid']])->first();
        return view('chat', $user);
    }

}
