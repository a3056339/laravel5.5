<?php
namespace App\Model;

/**
 * 后台群组规则
 */
class Menu extends Model
{
	
	/**
	 * 通过规则ID获取规则信息
	 * @param string $rule
	 */
	public static function menus($rules)
	{
        $where=[];
        if($rules!='all'){
            $arrayRules = explode(",", $rules);
            $where=function($query)use($arrayRules){
                $query->whereIn('id',$arrayRules);
            };
        }

		return self::where($where)->where(['status'=>1])->where('level','<=',2)->orderBy('sort','asc')->get();
	}

    public static function getRoleList(){
        return self::where(['status'=>1])->get();
    }
}
?>