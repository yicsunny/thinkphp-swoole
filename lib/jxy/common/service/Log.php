<?php
namespace jxy\common\service;

use jxy\common\model\CommonLog;
/**
 * 通用日志服务
 */
class Log
{
    /**
     * 记录通用日志
     * @param string $type 日志类型
     * @param string $id 区分标志
     * @param mixed $param 记录参数
     */
    static public function add($type, $id, $param)
    {
        if (!$type) return false;
        $data = [
            'type' => $type,
            'uid' => $id ?: 0,
            'param' => var_export($param, true),
            'cDate' => time(),
        ];
        return CommonLog::getInstance()->insertGetId($data);
    }            
}