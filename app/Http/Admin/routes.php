<?php
/**
 * Created by PhpStorm.
 * User: Li
 * Date: 15/11/2
 * Time: 下午4:05
 */

/*
|-------------------------------------------------------------------------
|  管理后台接口组
|-------------------------------------------------------------------------

 */
//上传
$app->post('/menu', 'PublicController@menu');
$app->post('/upload', 'PublicController@upload');

//配置相关
$app->group(['prefix' => 'configs'], function ($app) {

});
