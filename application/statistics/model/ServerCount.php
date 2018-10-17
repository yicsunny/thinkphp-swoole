<?php
/**
 * Created by PhpStorm.
 * User: YicSunny
 * Date: 2018/10/12
 * Time: 21:30
 */
namespace app\statistics\model;
use jxy\common\model\Base;

class ServerCount extends Base
{
    protected $connection = 'db_sts';

    public function addInfo($arr)
    {
        //写入统计信息库
        $addField = [];
        $addField['api'] = $arr['api'];
        $addField['url'] = urldecode($arr['url']);
        $addField['host'] = $arr['host'];
        $addField['cDate'] = $arr['time'];
        $addField['hour'] = date("H");

        return $this->addFields($addField);
    }

}