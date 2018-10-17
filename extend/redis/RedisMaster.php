<?php

namespace redis;

use Redis;

/**
 * redis master
 */
class RedisMaster {

    /* 配置项 */
    private $configs = [];

    private $handler = false;

    /**
     * 构造
     */
    public function __construct($config){
        $defaultConfig = [
            'host' => '',
            'port' => '',
            'password' => ''
        ];
        $this->setConfigs($defaultConfig);
        $this->setConfigs($config);

        $this->connect();

        if('' !== $this->configs['password']){
            $this->auth();
        }
    }

    /**
     * 设置配置
     */
    public function setConfigs($configs){
        $this->configs = array_merge($this->configs, $configs);
    }

    /**
     * 连接
     */
    public function connect(){
        $this->handler = new Redis();
        $this->handler->connect($this->configs['host'], $this->configs['port']);
    }

    /**
     * 验证
     */
    public function auth(){
        $this->handler->auth($this->configs['password']);
    }

    /**
     * 获取连接
     */
    public function getHandler(){
        return $this->handler;
    }

}
