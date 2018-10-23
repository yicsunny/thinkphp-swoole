<?php
namespace jxy\common\service;

use think\facade\Config;
use jxy\common\service\Curl;

class Httpsqs
{

    public static function put($queuename, $data)
    {
        $config = Config::get('httpsqs.');
        if($config['close']) return false;
        if(is_array($data)) $data = json_encode($data);
        $httpsqs_url = "http://" . $config['host'] . ":" . $config['port'] . "/?name=" . $queuename . "&auth=" . $config['auth'] . "&opt=put&charset=utf-8&data=" . $data;
        $res = Curl::putQueue($httpsqs_url);
        if ($res === 'HTTPSQS_PUT_OK') {
            return true;
        } else {
            return false;
        }
    }

    public static function get($queuename)
    {
        $config = Config::get('httpsqs.');
        if($config['close']) return false;
        $httpsqs_url = "http://" . $config['host'] . ":" . $config['port'] . "/?name=" . $queuename . "&auth=" . $config['auth'] . "&opt=get&charset=utf-8";
        $rst = Curl::get($httpsqs_url);
        if ($rst != 'HTTPSQS_GET_END') {
            return $rst;
        } else {
            return false;
        }
    }

    public static function status($queuename)
    {
        $config = Config::get('httpsqs.');
        $httpsqs_url = "http://" . $config['host'] . ":" . $config['port'] . "/?name=" . $queuename . "&auth=" . $config['auth'] . "&opt=status&charset=utf-8";
        $rst = Curl::get($httpsqs_url);
        return $rst;
    }

    public static function view($queuename, $pos)
    {
        $config = Config::get('httpsqs.');
        $httpsqs_url = "http://" . $config['host'] . ":" . $config['port'] . "/?name=" . $queuename . "&auth=" . $config['auth'] . "&opt=view&charset=utf-8&pos=" . $pos;
        $rst = Curl::get($httpsqs_url);
        return $rst;
    }

    public static function reset($queuename)
    {
        $config = Config::get('httpsqs.');
        $httpsqs_url = "http://" . $config['host'] . ":" . $config['port'] . "/?name=" . $queuename . "&auth=" . $config['auth'] . "&opt=reset&charset=utf-8";
        $rst = Curl::get($httpsqs_url);
        if ($rst === 'HTTPSQS_RESET_OK') {
            return true;
        } else {
            return false;
        }
    }
}
