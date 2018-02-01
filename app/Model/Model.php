<?php
namespace App\Model;

use Illuminate\Database\Eloquent\Model as BaseModel;
use Validator;

class Model extends BaseModel
{
    const CREATED_AT = "create_at";
    const UPDATED_AT = "update_at";

    function __construct()
    {
        parent::__construct();
        $this->table = snake_case(class_basename(get_called_class()));
    }

    /**
     * 添加或者更新
     *
     * @param array $data
     *
     * @return boolean
     */
    public function addOrUpdate($data)
    {
        foreach ($data as $k => $v) {
            $this->$k = $v;
        }
        return $this->save();
    }

    public function freshTimestamp()
    {
        return time();
    }

    public function fromDateTime($value)
    {
        return $value;
    }


    /**
     * 快速保存
     *
     * @param array  $input
     * @param array  $field
     * @param string $pk
     * @param array  $roles
     *
     * @return array
     */
    public function singleSave($input = [], $field = [], $pk = "id", $roles = [])
    {
        if (isset($input[$pk]) && $input[$pk]) {
            $exist = $this->where($pk, $input[$pk])->first();
            if ($exist) {
                $this->exists = true;
            } else {
                $result['err'] = true;
                $result['msg'] = '记录不存在';
                return $result;
            }
        }

        $validator = Validator::make($input, $roles);
        $result    = ['err' => false];

        if ($validator->fails()) {
            $result['err'] = true;
            $result['msg'] = $validator->errors()->first();
            return $result;
        }

        foreach ($input as $key => $val) {
            if (in_array($key, $field)) {
                if ($val != 'null') {
                    $this->$key = $val;
                } 
            }
        }

        $save = $this->save();
        if (!$save) {
            $result['err'] = true;
            $result['msg'] = "保存失败";
        }
        return $result;
    }

    public function getPhonePriceAttribute($value)
    {
        if (substr($value, -2) > 0) {
            return $value;
        }
        return (int)$value;
    }

    public function getWordsPriceAttribute($value)
    {
        if (substr($value, -2) > 0) {
            return $value;
        }
        return (int)$value;
    }

    public function getPriceAttribute($value)
    {
        if (substr($value, -2) > 0) {
            return $value;
        }
        return (int)$value;
    }

    public function getYearPriceAttribute($value)
    {
        if (substr($value, -2) > 0) {
            return $value;
        }
        return (int)$value;
    }

    public function getAmountAttribute($value)
    {
        if (substr($value, -2) > 0) {
            return $value;
        }
        return (int)$value;
    }

    public function getUnamountAttribute($value)
    {
        if (substr($value, -2) > 0) {
            return $value;
        }
        return (int)$value;
    }

    public function getFreezeAttribute($value)
    {
        if (substr($value, -2) > 0) {
            return $value;
        }
        return (int)$value;
    }

    public function getMoneyAttribute($value)
    {
        if (substr($value, -2) > 0) {
            return $value;
        }
        return (int)$value;
    }

}
