<?php
/**
 * Created by PhpStorm.
 * User: Li
 * Date: 15/7/8
 * Time: 下午1:43
 */
namespace App\Http\Controllers;


use DB;
use Request;
use Crypt;

class GitController extends Controller
{

    /**
     * 钩子
     */
    public function Git()
    {
        error_reporting(E_ALL);
        exec("cd /data1/heart/;git reset --hard origin/master;git pull", $output);
        print_r($output);
    }


}
