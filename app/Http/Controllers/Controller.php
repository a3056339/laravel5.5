<?php
/*
 |--------------------------------------------------------------------------
 |  API基类
 |--------------------------------------------------------------------------
 | 只有通过以下两种方式才能访问本API：
 |    1、在参数中或头部传递app_key以及sign字段(sign的创建方式参考API文档)
 |    2、在参数中传递access_token或在头部传递Access_Token
 */

namespace App\Http\Controllers;

use Illuminate\Routing\Controller as BaseController;

class Controller extends BaseController
{

    public function __construct()
    {
    }
}