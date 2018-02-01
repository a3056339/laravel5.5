<?php
/**
 * Created by PhpStorm.
 * User: mohong
 * Date: 2017/7/21
 * Time: 16:06
 */

namespace App\Http\Admin;

use App\Support\Common;
use Log;

class PublicController extends AdminController
{


    /**
     * 获取菜单
     * @return mixed
     */
    function menu()
    {

        $common = json_decode(file_get_contents(resource_path('menu/common.json')), true);

        foreach ($common as $key => $val) {

            $menu = [];
            foreach ($val['path'] as $k => $v) {
                $m = json_decode(file_get_contents(resource_path("menu/{$v}.json")), true);

                if($m){
                    array_push($menu, $m);
                }
            }
            $common[$key]["menu"] = $menu;
        }

        $data['menu'] = $common;

        return $this->json($data);
    }


    function upload()
    {
        if ($_FILES) {
            $result = Common::upload($_FILES['name']);
            return $this->json($result);
        }
        Log::info(111);
        return $this->json();

    }
}