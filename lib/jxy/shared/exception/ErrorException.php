<?php

namespace jxy\shared\exception;

use Think\Exception;


define("SUCCESS", 10000);
//公共错误码(参数、用户、api和app、系统)
define("ERROR_", 10013);
define("ERROR_PARAM_IS_EMPTY", 10001);
define("ERROR_PARAM_NOT_EXIST", 10002);
define("ERROR_PARAM_INVALID_SIGN", 10003);
define("ERROR_PARAM_FLOOD_REQUEST", 10004);
define("ERROR_PARAM_INVALID_FORMAT", 10005);
define("ERROR_PARAM_NOT_SMALL_ZERO", 10006);
define("ERROR_PARAM_KEY_EXISTS", 10009);
define("ERROR_PARAM_REQUEST_FORMAT", 10010);
define('ERROR_PARAM_LENGTH_LONG', 10011);
define('ERROR_PARAM_LENGTH_SHORT', 10012);
define("ERROR_TIME_TOO_MUCH", 10014);

/**
 * 异常基类
 */
class ErrorException extends Exception
{
    public function __construct($errno, $args = [])
    {
        $ERROR_LIST = [
            // 基础
            ERROR_                     => "%s",
            ERROR_PARAM_IS_EMPTY       => "%s参数不能为空!",
            ERROR_PARAM_NOT_EXIST      => "%s参数不存在",
            ERROR_PARAM_EXIST          => "%s已存在",
            ERROR_PARAM_INVALID_SIGN   => "签名校验失败",
            ERROR_PARAM_FLOOD_REQUEST  => "不能重复请求",
            ERROR_PARAM_INVALID_FORMAT => "%s参数格式错误",
            ERROR_PARAM_NOT_SMALL_ZERO => "%s参数不能小于0",
            ERROR_PARAM_REQUEST_FORMAT => '请求格式错误!',
            ERROR_PARAM_LENGTH_LONG    => '%s长度过长',
            ERROR_PARAM_LENGTH_SHORT   => '%s长度过短',
            ERROR_TIME_TOO_MUCH        => '%s错误次数已超过%s',

            // 模型
            ERROR_ADD                  => '%s写入失败',
            ERROR_UPDATE               => '%s修改失败',
            ERROR_DEL                  => '%s删除失败',
            ERROR_SEL                  => '%s查询失败',

            // 验证
            ERROR_PARAM_VERIFY         => '验证码错误',
            ERROR_PARAM_PHONE_NUMBER   => '手机号格式错误',
            ERROR_PARAM_EMAIL          => '邮箱格式错误',

            // 用户
            USER_IS_NULL               => "用户不存在",
            USER_PARAM_REPEAT          => "用户%s已注册",
            USER_SET_USER_VALUE_ERROR  => '发奖失败',
            USER_REGISTER_IS_EMPTY     => '%s不能为空',
            USER_PASSWORD_ERROR        => '用户密码错误',

            // 渠道
            CHANNEL_NON_USER           => '非本渠道用户!',
        ];

        $args = is_array($args) ? $args : [$args];

        if (is_array($errno)) {
            $errno = $errno['0'];
            $error = $args['0'];
        } else {
            $error = empty($args) ? $ERROR_LIST[$errno] : vsprintf($ERROR_LIST[$errno], $args);
        }

        throw new Exception($error, $errno);
    }
}
