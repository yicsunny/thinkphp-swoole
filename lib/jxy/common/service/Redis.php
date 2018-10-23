<?php
namespace jxy\common\service;

use redis\RedisClient;
use think\facade\Config;

class Redis
{

    /**
     * redis连接池
     *
     * @var array
     */
    private static $pool = [];

    /**
     *
     * @return RedisClient
     * @param int $db 使用第几个数据库
     * @param string $config 使用的配置 参见config下面的redis文件
     */
    public static function getInstance($db = 0, $config = '')
    {
        if (! $config)
            $config = 'main';
        $linkID = static::getLinkID($config, $db);
        // 已经存在直接返回
        if (! is_null(static::getPool($linkID))) {
            return static::getPool($linkID);
        }
        $redis = new RedisClient(self::getConfig($config));
        if ($db > 0) {
            $redis->select($db);
        }
        static::setPool($linkID, $redis);
        return $redis;
    }

    protected static function getConfig($config)
    {
        $config = Config::get('redis.' . $config);
        return [
            'type' => 'direct', // direct: 直连, sentinel: 由sentinel决定host与port
            'password' => 'redispassword', // redis auth 密码
            'master_name' => 'mymaster', // master name
            'use_slave' => false, // 是否使用主从
            'direct' => [
                'masters' => [
                    [
                        'host' => $config['host'],
                        'port' => $config['port']
                    ]
                ]
            ]
        ];
    }

    /**
     *
     * @param $linkID
     * @param RedisClient $redis            
     */
    protected static function setPool($linkID, $redis)
    {
        static::$pool[$linkID] = $redis;
    }

    /**
     *
     * @param
     *            $linkID
     * @return RedisClient
     */
    protected static function getPool($linkID)
    {
        if (! isset(static::$pool[$linkID])) {
            return null;
        }
        return static::$pool[$linkID];
    }

    /**
     * get cache key
     *
     * @param
     *            $config
     * @param
     *            $db
     * @return string
     */
    protected static function getLinkID($config, $db)
    {
        return $config . ':' . $db;
    }

    public static function clear()
    {
        static::$pool = [];
        return true;
    }
}