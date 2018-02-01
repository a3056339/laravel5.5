<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It is a breeze. Simply tell Lumen the URIs it should respond to
| and give it the Closure to call when that URI is requested.
|
*/

//***登录相关***
$app->group([], function ($app) {
    $app->any('/git', ['uses' => 'GitController@Git']);
    $app->any('/notice_url', ['uses' => 'AlipayController@alipay_notice']);
    $app->any('/return_url', ['uses' => 'AlipayController@alipay_return']);
    $app->any('/phone_notice', ['uses' => 'AliyunPhoneController@notice']);

});


$app->get('/', function () {
    return view('welcome');
});
$app->any('/login', ['uses' => 'PublicController@login']);
$app->any('/chat', ['uses' => 'PublicController@chat']);
$app->any('/auth', ['uses' => 'PublicController@auth']);

$app->get('/send_msg', function () {
    $res=  App\Libraries\AliyunDypls\PlsPhone::queryRecordFileDownloadUrl('435a582e6a059450',date("Y-m-d H:i:s",1515757290));
    print_r($res);;
});



