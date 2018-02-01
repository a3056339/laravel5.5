<?php
namespace App\Model;

/**
 * 请求日志
 * @author ZYF
 */
class RequestLog extends Model
{
	/**
	 * 写日志
	 */
	public function writeLog($data)
	{
		foreach ($data as $k => $v) {
			$this->$k = $v;
		}
		return $this->save();
	}
    
}