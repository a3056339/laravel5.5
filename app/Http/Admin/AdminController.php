<?php
/**
 * Created by PhpStorm.
 * API基类
 * User: Li
 * Date: 15/7/3
 * Time: 下午2:40
 */

namespace App\Http\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    protected $_user;       //当前用户信息
    protected $_args;
    protected $page = 1;
    protected $limit = 10;
    protected $filter = [];
    protected $uid = 0;

    public function __construct(Request $request)
    {
        parent::__construct();

        //所有请求参数
        $filter = $request->input('filter');
        if ($filter) {
            $filter = json_decode($filter, true);
            foreach ($filter as $key =>$val){
                if ($val=== 'null' ){
                    unset($filter[$key]);
                }
            }
            $this->filter = $filter;
        }
        $this->page = $request->input('page', 1);
        $this->limit = $request->input('num', 10);
        $this->uid = $request->header('uid');
        $this->_args =$request->all();
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
     * //返回json数据
     * @param array $items
     * @param int $err
     * @param string $msg
     * @return array
     */
    function json($items = [], $err = 0, $msg = "")
    {

        $data = [
            'status' => $err,
            'msg' => $msg,
            'data' => $items,
            'server_time' => date("Y-m-d H:i:s")

        ];
        return $data;
    }

    /**
     * 快捷返回错误码
     * @param $err
     * @return array
     */
    function err($err)
    {
        return $this->json([], $err);
    }

    /**
     * 返回错误快捷信息
     * @param $err
     * @param $msg
     * @return array
     */
    function errMsg($err, $msg)
    {
        return $this->json([], $err, $msg);
    }

    /**
     * 快捷返回数据信息
     * @param $items
     * @return array
     */
    function items($items)
    {
        return $this->json($items);
    }

    /**
     * 快捷返回成功信息
     * @param $msg
     * @return array
     */
    function successMsg($msg)
    {
        return $this->json([], 0, $msg);
    }

    /**
     * 列表需要的数据格式
     * @param $list
     * @param $count
     * @param int $page
     * @param int $limit
     * @param array|bool $title 表头显示内容，name 显示名称，value显示值
     * [['name'=>'','value'=>'']] 例如 [['name'=>'订单总数','value'=>100]] ,页面显示为 订单总数：100
     * @param array|bool $select 动态指定字段类型为select 的值  field 为字段值
     * ['field'=>['val1','val2']]  例如 字段为status 状态，结果为 ['status'=>['-1'=>'禁用',1=>'可用']]
     * @param array|bool $filter
     * @return array
     */
    function returnTable($list, $count, $page = 1, $limit = 10, $title = false, $select = false, $filter = false, $cols = null)
    {
        $data['list'] = $list;
        $data['count'] = $count;
        $data['page'] = $page;
        $data['num'] = $limit;
        $data['title'] = $title;
        $data['select'] = $select;
        $data['filter'] = $filter;
        $data['cols'] = $cols;
        return $this->json($data);
    }

}
