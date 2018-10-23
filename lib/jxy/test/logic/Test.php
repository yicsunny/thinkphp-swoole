<?php
namespace jxy\test\logic;

use jxy\common\logic\Common;
use jxy\common\service\Curl;
use think\Exception;
use think\facade\Config;
use think\facade\Request;

class Test extends Common //需要继承BaseCommon 这个logic
{

    /**
     * @desc 调用服务，返回内容
     * @param $serverType
     * @param $api
     * @param string $method
     * @return string
     * @throws Exception
     */
    public function service($api)
    {
        $header = [];
        $header['traceID'] = md5(uniqid(mt_rand(), true));
        $header['token']   = Curl::desEcbEncrypt($header['traceID']);

        $configUrlList = Config::get('api.');
        if(!isset($configUrlList[$api])) throw new Exception('地址不存在');
        $urlArr = $configUrlList[$api];

        try{
            $serviceUrl = $this->getRedis()->sRandMember($urlArr['serverType']);
        }catch (\RedisException $e){
            $this->redisInstance = null;
            $serviceUrl = $this->getRedis()->sRandMember($urlArr['serverType']);
        }

//        echo $serviceUrl. '/'. $urlArr['url']. '?'. Request::server('QUERY_STRING');

        $result = Curl::get($serviceUrl. '/'. $urlArr['url']. '?'. Request::server('QUERY_STRING'), $header);
        $result = json_decode($result, true);
        $result['traceID'] = $header['traceID'];
        $result['serverUrl'] = $serviceUrl;
        return $result;
    }
}