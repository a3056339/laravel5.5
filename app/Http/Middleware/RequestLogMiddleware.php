<?php

namespace App\Http\Middleware;

use Closure;
use Request;
use App\Model\RequestLog;

class RequestLogMiddleware
{
	public $requestLog = null;

	/**
	 * 请求日志
	 * @param  \Illuminate\Http\Request  $request
	 * @param  \Closure  $next
	 * @return mixed
	 */
	public function handle($request, Closure $next)
	{
		$this->requestLog = new RequestLog();
		$header = $request->header();
		$data = array(
			"header" => json_encode($header,JSON_UNESCAPED_UNICODE),
			"method" => $request->method(),
			"uri"  => $request->getRequestUri(),
			"key" => (null !== $request->header("token")) ? json_encode(array("token"=>$request->header("token")),JSON_UNESCAPED_UNICODE) :$request->input("token"),
			"content" => json_encode($request->input(),JSON_UNESCAPED_UNICODE),
			"app_id" => $request->header("Appid",""),
			"app_version" => $request->header("Version",""),
			"devices" => $request->header("Device",""),
			"channel" => $request->header("Channel_name","")
		);	
		$this->requestLog->writeLog($data);
		return $next($request);
	}
}
