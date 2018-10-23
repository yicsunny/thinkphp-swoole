<?php
/**
 * Created by PhpStorm.
 * User: YicSunny
 * Date: 2018/3/27
 * Time: 10:23
 */
namespace jxy\common\model;

use think\Model;
use think\Db;

class Base extends Model  {
    
    public function setTable($table)
    {
        $this->table = $table;
        return true;
    }

    public function findBySearch($searchArr, $orderArr = [], $fieldArr = [],$join = false){

        $this->_join($join);
        if($fieldArr){
            $t = $this->field($fieldArr)->where($this->bulidWhere($searchArr));
        }else{
            $t = $this->where($this->bulidWhere($searchArr));
        }
        if($orderArr){
            $t->order($orderArr);
        }else{
            $t->order($orderArr);
        }
        $result = $t->find();
        if($result){
            return $result->toArray();
        }else{
            return $result;
        }
    }

    /**
     * 构建用于5.1查询的数组
     * @param mixed $searchArr
     * @return mixed
     */
    protected function bulidWhere($searchArr){
        if(is_array($searchArr)){//如果是数组传值，需要自己构建
            if(isset($searchArr[0])) return $searchArr;//如果是框架定义的查询方式，直接返回
            $where = [];
            foreach($searchArr as $key => $val){
                if(is_array($val) && isset($val[0]) && isset($val[1])){//如果是数组的条件表达式
                    if($val[0] == 'exp'){//exp 需要特殊对待
                        $where[] = [$key, 'exp', Db::raw($val[1])];
                    }else{
                        $where[] = [$key, $val[0], $val[1]];
                    }
                }else{
                    $where[] = [$key, '=', $val];
                }
            }
            return $where;
        }else{//如果不是数组，直接返回
            return $searchArr;
        }
    }

    public function selectBySearch($searchArr, $orderArr = [], $limit = 0, $offset = 0, $fieldArr = [], $groupArr = '', $join = false){

        $this -> _join($join);
        if($fieldArr){
            $t = $this->field($fieldArr)->where($this->bulidWhere($searchArr));
        }else{
            $t = $this->where($this->bulidWhere($searchArr));
        }
        if($orderArr){
            $t->order($orderArr);
        }
        if($limit && $offset){
            $t->limit($limit, $offset);
        }elseif($offset){
            $t->limit($offset);
        }
        if($groupArr){
            $t->group($groupArr);
        }

        $result = $t->select();
        if($result){
            return $result->toArray();
        }else{
            return $result;
        }
    }

    public function countBySearch($array, $field = ''){

        if($field){
            $result = $this->where($this->bulidWhere($array))->count($field);
        }else{
            $result = $this->where($this->bulidWhere($array))->count();
        }

        return $result;
    }

    public function updateBySearch($searchArr, $fields)
    {
        $result = $this->where($this->bulidWhere($searchArr))->update($fields);
        return $result;
    }

    public function sumBySearch($array, $field){
        $result = $this->where($this->bulidWhere($array))->sum($field);
        return $result;
    }

    public function deleteBySearch($searchArr)
    {
        return $this->where($this->bulidWhere($searchArr))->delete();
    }

    public function addFields($fields){

        $result = $this->insertGetId($fields);
        return $result;
    }

    public function _join($join)
    {
        if ($join) {
            if (is_string($join))
                $this->join($join);
            elseif (is_array($join)) {
                foreach ($join as $j) {
                    $this->join($j);
                }
            }
        }
    }

    /**
     * 得到调用的模型
     * @return $this
     */
    public static function getInstance(){
        return \model(get_called_class());
    }

    /**
     * 分表参数
     * @param int $value
     * @param int $num
     */
    public function getTableID($value,$num=10){
        if(empty($value))	return 0;
        $__v = intval($value)%$num;
        return $__v;
    }

   public function getLastSql(){
       return parent::getLastSql();
   }
}
