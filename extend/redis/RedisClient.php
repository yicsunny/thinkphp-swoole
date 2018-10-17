<?php

namespace redis;

/**
 * redis操作类
 */
class RedisClient {

    private $defaultConfig = [
        'type' => 'direct', // direct: 直连, sentinel: 由sentinel决定host与port
        'password' => 'redispassword', // redis auth 密码
        'master_name' => 'mymaster', // master name
        'use_slave' => false,// 是否使用主从
        'direct' => [
            'masters' => [
                ['host' => '127.0.0.1', 'port' => '6379']
            ],
            'slaves' => [
                ['host' => '127.0.0.1', 'port' => '6381'],
                ['host' => '127.0.0.1', 'port' => '6382']
            ],
        ],
        'sentinel' => [
            'sentinels' => [
                ['host' => '127.0.0.1', 'port' => '5000'],
                ['host' => '127.0.0.1', 'port' => '5001']
            ]
        ]
    ];

    /* 配置项 */
    private $configs = [];

    /* 用于执行redis操作的池 */
    private $pool;

    /* 哨兵对象 */
    private $sentinel;

    /* 要操作的master名称 */
    private $masterName;

    /**
     * 构造函数
     */
    public function __construct($config = []){
        $this->setConfig($this->defaultConfig);
        $this->setConfig($config);
        $this->masterName = $this->configs['master_name'];
        if('sentinel' === $this->configs['type']){ // sentinel方式
            $this->sentinel = new RedisSentinel(); //创建sentinel

            /* 根据配置添加sentinel */
            foreach ($this->configs['sentinel']['sentinels'] as $s) {
                $this->sentinel->addnode($s['host'], $s['port']);
            }
            return;
        }
        $this->pool['master'] = new RedisMaster($this->getMasterConfigs());
        if($this->configs['use_slave']){
            $this->pool['slave'] = new RedisSlave($this->getSlaveConfigs());
        }
    }

    /**
     * 获取master配置
     */
    public function getMasterConfigs(){
        if('sentinel' === $this->configs['type']){
            return $this->getMasterConfigsBySentinel();
        }
        $randomMaster = rand(0, (count($this->configs['direct']['masters']) - 1)); // 随机取一个master的配置
        $config = [
            'host' => $this->configs['direct']['masters'][$randomMaster]['host'],
            'port' => $this->configs['direct']['masters'][$randomMaster]['port'],
            'password' => $this->configs['password']
        ];
        return $config;
    }

    /**
     * 获取slave配置
     */
    public function getSlaveConfigs(){
        if('sentinel' === $this->configs['type']){
            return $this->getSlaveConfigsBySentinel();
        }
        if(0 === count($this->configs['direct']['slaves'])){ // 没有slave则取master
            return $this->getMasterConfigs();
        }
        $randomSlave = rand(0, (count($this->configs['direct']['slaves']) - 1)); // 随机取一个slave的配置
        $config = [
            'host' => $this->configs['direct']['slaves'][$randomSlave]['host'],
            'port' => $this->configs['direct']['slaves'][$randomSlave]['port'],
            'password' => $this->configs['password']
        ];
        return $config;
    }

    /**
     * 通过sentinel获取master配置
     */
    public function getMasterConfigsBySentinel(){
        $masters = $this->sentinel->get_masters($this->masterName);
        $config = [
            'host' => $masters[0],
            'port' => $masters[1],
            'password' => $this->configs['password']
        ];
        return $config;
    }

    /**
     * 通过sentinel获取slave配置
     */
    public function getSlaveConfigsBySentinel(){
        $slaves = $this->sentinel->get_slaves($this->masterName);
        if(0 === count($slaves)){ // 没有slave则取master
            return $this->getMasterConfigsBySentinel();
        }
        $random = rand(0, (count($slaves) - 1)); // 随机取一个slave的配置
        $config = [
            'host' => $slaves[$random]['ip'],
            'port' => $slaves[$random]['port'],
            'password' => $this->configs['password']
        ];
        return $config;
    }

    /**
     * 设置配置
     */
    private function setConfig($config) {
        $this->configs = array_merge($this->configs, $config);
    }

    /**
     * 判断只读还是读写
     */
    private function judge($command) {
        $masterOrSlave = 'master';
        if($this->configs['use_slave']){
            $readOnlyCommands = [
                'get',
                'hGet',
                'hMGet',
                'hGetAll',
                'sMembers',
                'zRange',
                'exists'
            ]; //只读的操作
            if (in_array($command, $readOnlyCommands)) {
                $masterOrSlave = 'slave';
            }
       }
       return $masterOrSlave;
    }

    /**
     * 获取连接
     *
     * @param string $masterOrSlave [master / slave]
     * @return
     */
    private function getHandler($masterOrSlave) {
       $handler = $this->pool[$masterOrSlave]->getHandler();
       return $handler;
    }

    /**
     * 切换database
     *
     * @param int $index db索引
     */
    public function select($index = 0) {
        $this->pool['master']->getHandler()->select($index);
        if($this->configs['use_slave']){
            $this->pool['slave']->getHandler()->select($index);
        }
    }

    /**
     * 执行lua脚本
     *
     * eval是PHP关键字PHP7以下不能作为方法名
     *
     * @param string $script 脚本代码
     * @param array $args 传给脚本的KEYS, ARGV组成的索引数组（不是key-value对应，是先KEYS再ARGV的索引数组，KEYS, ARGV数量可以不同） 例：['key1', 'key2', 'argv1', 'argv2', 'argv3']
     * @param int $quantity 传给脚本的KEY数量
     * @return
     */
    public function evaluate ($script, $args, $quantity) {
        $result = $this->getHandler($this->judge(__FUNCTION__))->eval($script, $args, $quantity);
        return $result;
    }

    /**
     * 获取key对应的值
     *
     * @param string $key key
     * @return
     */
    public function get($key){
        $result = $this->getHandler($this->judge(__FUNCTION__))->get($key);
        return $result;
    }

    /**
     * 设置key - value
     *
     * set('key', 'value');
     * set('key', 'value', ['nx']);
     * set('key', 'value', ['xx']);
     * set('key', 'value', ['ex' => 10]);
     * set('key', 'value', ['px' => 1000]);
     * set('key', 'value', ['nx', 'ex' => 10]);
     * set('key', 'value', ['nx', 'px' => 1000]);
     * set('key', 'value', ['xx', 'ex' => 10]);
     * set('key', 'value', ['xx', 'px' => 1000]);
     *
     * @param string $key key
     * @param string $value value
     * @param array $opt 可选参数  可选参数可以自由组合 nx: key不存在时有效, xx: key存在时有效, ex: ttl[单位：s], px: ttl[单位：ms]
     * @return
     */
    public function set($key, $value, $opt = null){
        $result = $this->getHandler($this->judge(__FUNCTION__))->set($key, $value, $opt);
        return $result;
    }

    /**
     * 设置key - value同时设置剩余有效期
     *
     * @param string $key key
     * @param int $seconds 剩余有效期 （单位：s / 秒）
     * @param string $value
     * @return
     */
    public function setEx($key, $seconds, $value){
        $result = $this->getHandler($this->judge(__FUNCTION__))->setEx($key, $seconds, $value);
        return $result;
    }

    /**
     * 设置key - value （仅在当前key不存在时有效）
     *
     * @param string $key key
     * @param string $value value
     * @return
     */
    public function setNx($key, $value){
        $result = $this->getHandler($this->judge(__FUNCTION__))->setNx($key, $value);
        return $result;
    }

    /**
     * 获取hash一个指定字段的值
     *
     * @param string $key key
     * @param string $field 字段
     * @return
     */
    public function hGet($key, $field){
        $result = $this->getHandler($this->judge(__FUNCTION__))->hGet($key, $field);
        return $result;
    }


    /**
     * 返回hash里面key是否存在的标志
     *
     * @param string $key key
     * @param string $field 字段
     * @return
     */
    public function hExists($key, $field){
        $result = $this->getHandler($this->judge(__FUNCTION__))->hExists($key, $field);
        return $result;
    }


    /**
     * O(N) N是被删除的字段数量。
     *
     * @param string $key key
     * @param string $field 字段
     * @return
     */
    public function hDel($key, $field){
        $result = $this->getHandler($this->judge(__FUNCTION__))->hDel($key, $field);
        return $result;
    }

    /**
     * 获取hash多个指定字段的值
     *
     * @param string $key key
     * @param array $array 字段索引数组
     * @return
     */
    public function hMGet($key, $array){
        $result = $this->getHandler($this->judge(__FUNCTION__))->hMGet($key, $array);
        return $result;
    }

    /**
     * 获取整个hash的值
     *
     * @param string $key key
     * @return
     */
    public function hGetAll($key){
        $result = $this->getHandler($this->judge(__FUNCTION__))->hGetAll($key);
        return $result;
    }

    /**
     * 返回 key 指定的哈希集中所有字段的名字。
     *
     * 哈希集中的字段列表，当 key 指定的哈希集不存在时返回空列表。
     *
     * @param string $key key
     * @return
     */
    public function hKeys($key){
        $result = $this->getHandler($this->judge(__FUNCTION__))->hKeys($key);
        return $result;
    }

    /**
     * 只在 key 指定的哈希集中不存在指定的字段时，设置字段的值。
     * 如果 key 指定的哈希集不存在，会创建一个新的哈希集并与 key 关联。
     * 如果字段已存在，该操作无效果
     *
     * 返回值意义:
     * 1：如果字段是个新的字段，并成功赋值
     * 0：如果哈希集中已存在该字段，没有操作被执行
     *
     * @param string $key key
     * @param string $field 字段
     * @param string $value 值
     * @return
     */
    public function hSetNx($key, $field, $value){
        $result = $this->getHandler($this->judge(__FUNCTION__))->hSetNx($key, $field, $value);
        return $result;
    }

    /**
     * 设置hash一个字段
     *
     * @param string $key key
     * @param string $field 字段
     * @param string $value 值
     * @return
     */
    public function hSet($key, $field, $value){
        $result = $this->getHandler($this->judge(__FUNCTION__))->hSet($key, $field, $value);
        return $result;
    }

    /**
     * 设置hash多个字段
     *
     * @param string $key key
     * @param array $array 要设置的hash字段 例：['field1' => 'value1', 'field2' => 'value2']
     * @return
     */
    public function hMSet($key, $array){
        $result = $this->getHandler($this->judge(__FUNCTION__))->hMSet($key, $array);
        return $result;
    }

    /**
     *
     * 增加 key 指定的哈希集中指定字段的数值。如果 key 不存在，会创建一个新的哈希集并与 key 关联。如果字段不存在，则字段的值在该操作执行前被设置为 0
     * HINCRBY 支持的值的范围限定在 64位 有符号整数
     *
     * 返回执行后的值
     * @param $key
     * @param $field
     * @param $value
     * @return mixed
     */
    public function hInCrBy($key, $field, $value){
        $result = $this->getHandler($this->judge(__FUNCTION__))->hInCrBy($key, $field, $value);
        return $result;
    }

    /**
     * 获取集合所有成员
     *
     * @param string $key key
     * @return
     */
    public function sPop($key){
        $result = $this->getHandler($this->judge(__FUNCTION__))->sPop($key);
        return $result;
    }

    /**
     * 往set集合添加成员
     *
     * @param string $key key
     * @param string $member 成员
     * @return
     */
    public function sAdd($key, $member){
        $result = $this->getHandler($this->judge(__FUNCTION__))->sAdd($key, $member);
        return $result;
    }

    public function sRandMember($key)
    {
        return $this->getHandler($this->judge(__FUNCTION__))->sRandMember($key);
    }

    /**
     * 获取集合所有成员
     *
     * @param string $key key
     * @return
     */
    public function sMembers($key){
        $result = $this->getHandler($this->judge(__FUNCTION__))->sMembers($key);
        return $result;
    }

    /**
     * 返回成员 member 是否是存储的集合 key的成员
     *
     * @param string $key key
     * @param string $member 成员
     * @return
     */
    public function sIsMember($key, $member){
        $result = $this->getHandler($this->judge(__FUNCTION__))->sIsMember($key, $member);
        return $result;
    }

    /**
     * 从集合中移除指定的成员
     *
     * @param string $key key
     * @param string $member 成员
     * @return
     */
    public function sRem($key, $member){
        $result = $this->getHandler($this->judge(__FUNCTION__))->sRem($key, $member);
        return $result;
    }

    /**
     * 往有序集合添加成员
     *
     * @param string $key key
     * @param int $score score
     * @param string $value value
     * @return
     */
    public function zAdd($key, $score, $value){
        $result = $this->getHandler($this->judge(__FUNCTION__))->zAdd($key, $score, $value);
        return $result;
    }

    /**
     * @desc 返回有序集中Key的Score
     * @param $key
     * @param $member
     * @return mixed
     */
    public function zScore($key, $member) {
        $result = $this->getHandler($this->judge(__FUNCTION__))->zScore($key, $member);
        return $result;
    }

    /**
     * 从有序集合获取指定范围内的成员
     *
     * @param string $key key
     * @param int $start 起始值
     * @param int $stop 截止值
     * @param bool $isWithScore 是否包含score值
     * @return
     */
    public function zRange($key, $start, $stop, $isWithScore = false){
        $result = $this->getHandler($this->judge(__FUNCTION__))->zRange($key, $start, $stop, $isWithScore);
        return $result;
    }

    /**
     * @desc 在指定的范围由高到低返回存储在键的排序元素集合
     * @param $key
     * @param $min
     * @param $isWithScore
     * @param $max
     * @return mixed
     */
    public function zRevRange($key, $min, $max, $isWithScore = false) {
        $result = $this->getHandler($this->judge(__FUNCTION__))->zRevRange($key, $min, $max, $isWithScore);
        return $result;
    }

    /**
     * @desc 在指定的范围由高到低返回存储在键的排序元素与值的集合
     * @param $key
     * @param $min
     * @param $isWithScore array('withscores' => TRUE, 'limit' => array(1, 1)
     * @param $max
     * @return mixed
     */
    public function zRangeByScore($key, $min, $max, $isWithScore = false) {
        $result = $this->getHandler($this->judge(__FUNCTION__))->zRangeByScore($key, $min, $max, $isWithScore);
        return $result;
    }

    /**
     * @desc 在指定的范围由高到低返回存储在键的排序元素与值的集合
     * @param $key
     * @param $min
     * @param $isWithScore array('withscores' => TRUE, 'limit' => array(1, 1)
     * @param $max
     * @return mixed
     */
    public function zRevRangeByScore($key, $min, $max, $isWithScore = array('withscores' => TRUE, 'limit' => array(1, 1))) {
        $result = $this->getHandler($this->judge(__FUNCTION__))->zRevRangeByScore($key, $min, $max, $isWithScore);
        return $result;
    }

    /**
     * @desc 获得成员按score值递增(从小到大)排列的排名
     * @param $key
     * @param $member
     * @return mixed
     */
    public function zRank($key, $member) {
        $result = $this->getHandler($this->judge(__FUNCTION__))->zRank($key, $member);
        return $result;
    }

    /**
     * @desc 获得成员按score值递增(从小到大)排列的排名
     * @param $key
     * @param $member
     * @return mixed
     */
    public function zRevRank($key, $member) {
        $result = $this->getHandler($this->judge(__FUNCTION__))->zRevRank($key, $member);
        return $result;
    }

    /**
     * 从有序集合移除指定的成员
     * @param string $key key
     * @param string $member 成员
     * @return
     */
    public function zRem($key, $member){
        $result = $this->getHandler($this->judge(__FUNCTION__))->zRem($key, $member);
        return $result;
    }

    /**
     * 从有序集合中移除指定排名范围内的成员
     *
     * @param string $key key
     * @param int $start 起始排名 （包含） 从0开始
     * @param int $stop 截止排名 （包含） 从0开始
     * @return
     */
    public function zRemRangeByRank($key, $start, $stop){
        $result = $this->getHandler($this->judge(__FUNCTION__))->zRemRangeByRank($key, $start, $stop);
        return $result;
    }

    /**
     * 从有序集合中移除指定score范围内的成员
     *
     * @param string $key key
     * @param int $min 起始score （包含）
     * @param int $max 截止score （包含）
     * @return
     */
    public function zRemRangeByScore($key, $min, $max){
        $result = $this->getHandler($this->judge(__FUNCTION__))->zRemRangeByScore($key, $min, $max);
        return $result;
    }


    /**
     * 获取有序集合的成员数
     *
     * @param string $key key
     * @return
     */
    public function zCard($key){
        $result = $this->getHandler($this->judge(__FUNCTION__))->zCard($key);
        return $result;
    }


    /**
     * 设置剩余有效时长
     *
     * @param string $key key
     * @param int $exp 剩余时长 （单位：秒）
     * @return
     */
    public function expire($key, $exp){
        $result = $this->getHandler($this->judge(__FUNCTION__))->expire($key, $exp);
        return $result;
    }

    /**
     * 设置剩余有效时长
     *
     * @param string $key key
     * @return
     */
    public function ttl($key){
        $result = $this->getHandler($this->judge(__FUNCTION__))->ttl($key);
        return $result;
    }

    /**
     * @desc 毫秒级
     * @param $key
     * @param $exp
     * @return mixed
     */
    public function pExpire($key, $exp){
        $result = $this->getHandler($this->judge(__FUNCTION__))->pExpire($key, $exp);
        return $result;
    }

    /**
     * 删除key
     *
     * @param string $key key
     * @return
     */
    public function del($key){
        $result = $this->getHandler($this->judge(__FUNCTION__))->del($key);
        return $result;
    }

    /**
     * 判断key是否存在
     *
     * @param $key
     * @return
     */
    public function exists($key){
        $result = $this->getHandler($this->judge(__FUNCTION__))->exists($key);
        return $result;
    }

    /**
     * 发布消息到指定频道
     * @param string $channel 频道
     * @param string $message 消息内容
     * @return
     */
    public function publish($channel, $message){
        $result = $this->getHandler($this->judge(__FUNCTION__))->publish($channel, $message);
        return $result;
    }

    /**
     * 自增 - 增幅为1
     *
     * @param string $key key
     * @return
     */
    public function incr($key) {
        $result = $this->getHandler($this->judge(__FUNCTION__))->incr($key);
        return $result;
    }

    /**
     * 自增 - 增幅为指定值
     *
     * @param string $key key
     * @param int $amount 增加的数值
     * @return
     */
    public function incrBy($key, $amount) {
        $result = $this->getHandler($this->judge(__FUNCTION__))->incrBy($key, $amount);
        return $result;
    }

    /**
     * 自减 - 减幅为指定值
     *
     * @param string $key key
     * @param int $amount 减加的数值
     * @return
     */
    public function decrBy($key, $amount) {
        $result = $this->getHandler($this->judge(__FUNCTION__))->decrBy($key, $amount);
        return $result;
    }

    /**
     * 添加到队列头
     *
     * @param string $key key
     * @param string $value value
     * @return
     */
    public function lPush($key, $value) {
        $result = $this->getHandler($this->judge(__FUNCTION__))->lPush($key, $value);
        return $result;
    }

    /**
     * 列尾弹出一个元素
     *
     * 元素从队列删除
     *
     * @param string $key key
     * @return
     */
    public function lPop($key) {
        $result = $this->getHandler($this->judge(__FUNCTION__))->lPop($key);
        return $result;
    }

    /**
     * 添加到队列尾
     *
     * @param string $key key
     * @param string $value value
     * @return
     */
    public function rPush($key, $value) {
        $result = $this->getHandler($this->judge(__FUNCTION__))->rPush($key, $value);
        return $result;
    }

    /**
     * 列尾弹出一个元素
     *
     * 元素从队列删除
     *
     * @param string $key key
     * @return
     */
    public function rPop($key) {
        $result = $this->getHandler($this->judge(__FUNCTION__))->rPop($key);
        return $result;
    }

    /**
     * lists总长度
     * @param string $key key
     * @return
     */
    public function lLen($key) {
        $result = $this->getHandler($this->judge(__FUNCTION__))->lLen($key);
        return $result;
    }

    /**
     * 从队列中取数据
     *
     * @param string $key
     * @param int $start 起始
     * @param int $stop 截止
     */
    public function lRange($key, $start, $stop) {
        $result = $this->getHandler($this->judge(__FUNCTION__))->lRange($key, $start, $stop);
        return $result;
    }

    /**
     * 对列表进行修剪
     *
     * @param string $key
     * @param int $start 起始
     * @param int $stop 截止
     */
    public function lTrim($key, $start, $stop) {
        $result = $this->getHandler($this->judge(__FUNCTION__))->lTrim($key, $start, $stop);
        return $result;
    }

    /**
     * 增加有序集合score值
     *
     * @param string $key key
     * @param int $amount 增长的数值
     * @param string $value value值
     * @return
     */
    public function zIncrBy($key, $amount, $value) {
        $result = $this->getHandler($this->judge(__FUNCTION__))->zIncrBy($key, $amount, $value);
        return $result;
    }

    /**
     * 重命名
     *
     * @param $source
     * @param $destination
     * @return mixed
     */
    public function rename($source, $destination) {
        $result = $this->getHandler($this->judge(__FUNCTION__))->rename($source, $destination);
        return $result;
    }

    /**
     * 标记一个事务块的开始
     * @param $mode
     * @return mixed
     */
    public function multi( $mode = Redis::MULTI ) {
        $result = $this->getHandler($this->judge(__FUNCTION__))->multi($mode);
        return $result;
    }

    /**
     * 执行事物
     * @see multi()
     * @link    http://redis.io/commands/exec
     */
    public function exec( ) {
        $result = $this->getHandler($this->judge(__FUNCTION__))->exec();
        return $result;
    }
}



