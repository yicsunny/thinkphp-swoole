<?php
/**
 * Created by PhpStorm.
 * User: YicSunny
 * Date: 2018/10/12
 * Time: 21:19
 */
namespace app\statistics\logic;
use app\statistics\model\ServerCount;
use jxy\common\consts\HttpsqsName;
use jxy\common\logic\Common;
use jxy\common\service\Httpsqs;

class UrlLogic extends Common
{
    public function deal()
    {
//        $result = Httpsqs::get(HttpsqsName::HTTPSQS_NAMEOF_LOG_REQUEST_URL);
//        if(!$result)exit("no error /n");
//
//        $result = json_decode($result, true);
//        if(!$result){
//            exit("error /n");
//        }

        $result = [];
        $result['api'] = "asdf";
        $result['url'] = "hg";
        $result['host'] = 1;
        $result['time'] = $this->time;

        //写入统计信息库
        $addField = [];
        $addField['api'] = $result['api'];
        $addField['url'] = $result['url'];
        $addField['host'] = $result['host'];
        $addField['cDate'] = $result['time'];
        $addField['hour'] = date("H");

        $model = ServerCount::getInstance();
        return $model->addFields($addField);
    }
}