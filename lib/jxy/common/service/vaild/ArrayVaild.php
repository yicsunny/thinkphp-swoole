<?php
namespace jxy\common\service\vaild;
/**
 * 数组的验证服务
 */
class ArrayVaild
{
    /**
     * 数组是否设置了key，且不为空
     * @param array $array 数组
     * @param mixed $key 要判断的键值，可传数组
     */
    public static function getKey($array, $key){
        if(is_array($key)){
            foreach ($key as $keyName){
                if(!isset($array[$key]) || !$array[$key]) return false; 
            }
            return true;
        }else{
            return isset($array[$key]) && $array[$key] ? true : false;
        }
    }    
    
}