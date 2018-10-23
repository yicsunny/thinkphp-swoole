<?php
namespace jxy\common\traits\redis;

use jxy\common\service\Curl;
use jxy\common\service\Redis;
/**
 * 用户Redis公用方法
 * <li>这里可以进行redis相关的操作，如key值的获取等等，方便管理</li>
 */
trait CommonRedis{
    
    protected $redisInstance = null;
    
    /**
     * @return \redis\RedisClient
     */
    public function getRedis(){
        if(is_null($this->redisInstance)){
            Redis::clear();
            $this->redisInstance = Redis::getInstance();
        }
        return $this->redisInstance;
    }

    public function getRedisUrl($key)
    {
        return $this->getRedis()->sRandMember($key);
    }
}