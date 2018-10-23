<?php
/**
 * Created by PhpStorm.
 * User: YicSunny
 * Date: 2018/3/26
 * Time: 17:39
 */
namespace jxy\common\logic;
use jxy\common\service\Redis;
use jxy\common\traits\redis\CommonRedis;

class Common extends BaseCommon{

    use CommonRedis;
    
    public function __get($key){
        if($key == 'redis'){
            $this->redis = Redis::getInstance();
            return $this->redis;
        }
    }
}