<?php
/**
 * Created by PhpStorm.
 * User: YicSunny
 * Date: 2018/3/29
 * Time: 13:37
 */
namespace jxy\shared\logic;
use jxy\common\logic\Common;

class WebTask extends Common {

//    public function getWebTaskQueue()
//    {
//        $jobData = get_queue(HTTPSQS_NAMEOF_WEBSWOOLESHELL);
//        if (!$jobData)
//            exit("no error /n");
//
//        $jobData = json_decode($jobData, true);
//
//        //暂时本地处理
//
//
//
//
//
//    }

    public function t1()
    {
        $this->test($this->getRedisUrl('game'). '/hello');
    }


    public function test($url)
    {
        //发送给服务处理内容，获取服务地址
        echo $url;
        return 'aaa';

    }

}