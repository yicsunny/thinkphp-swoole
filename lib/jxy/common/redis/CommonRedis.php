<?php
/**
 * Created by PhpStorm.
 * User: YicSunny
 * Date: 2018/4/9
 * Time: 9:43
 */

namespace jxy\common\redis;
use Redis\RedisClient;
use think\facade\Env;

class CommonRedis {
    /**
     * @var $instance RedisClient
     */
    protected static $instance = [];

    public static function getInstance($db = 0)
    {
        if(isset(self::$instance[$db])){
            return self::$instance[$db];
        }
        self::$instance[$db] = new RedisClient(self::getConfig());
        if($db > 0){
            self::$instance->select($db);
        }
        return self::$instance[$db];
    }

    public static function getConfig()
    {
       return [
            'type' => 'direct', // direct: 直连, sentinel: 由sentinel决定host与port
            'password' => 'redispassword', // redis auth 密码
            'master_name' => 'mymaster', // master name
            'direct' => [
                'masters' => [
                    ['host' => Env::get('REDIS.HOST', '192.168.11.60'), 'port' => Env::get('REDIS.post', '6379')]
                ],
                'slaves' => [
                    ['host' => Env::get('REDIS.HOST', '192.168.11.60'), 'port' => Env::get('REDIS.post', '6379')],
                    ['host' => Env::get('REDIS.HOST', '192.168.11.60'), 'port' => Env::get('REDIS.post', '6379')]
                ],
            ],
            'sentinel' => [
                'sentinels' => [
                    ['host' => Env::get('REDIS.HOST', '192.168.11.60'), 'port' => Env::get('REDIS.post', '5000')],
                    ['host' => Env::get('REDIS.HOST', '192.168.11.60'), 'port' => Env::get('REDIS.post', '5001')]
                ]
            ]
        ];
    }
}