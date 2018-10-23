<?php
namespace jxy\common\service;

use think\facade\Request;

/**
 * 错误日志保存接管
 */
class ErrorLog
{

    const HTTPSQS_NAMEOF_ERROR_LOG = 'jxy3_log_error_list';

    /**
     * 保存日志信息
     * 
     * @param array $log 日志信息
     */
    public function save(array $log = [])
    {
        $res = [];
        foreach ($log as $level => $list) {
            if ($level == 'info')
                continue;
            foreach ($list as $node) {
                $data = [
                    'level' => $level,
                    'module' => Request::module() ?: '',
                    'controller' => Request::controller() ?: '',
                    'action' => Request::action() ?: '',
                    'func' => isset($node['func']) ? $node['func'] : '',
                    'info' => isset($node['info']) ? $node['info'] : ($node ?: ''),
//                    'url' => urldecode(isset($node['url']) ? $node['url'] : (isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : $_SERVER['SERVER_NAME']) . '/' . trim($_SERVER['REQUEST_URI'], '/')),
                    'url' => urldecode(isset($node['url']) ? $node['url'] : (isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : (isset($_SERVER['SERVER_NAME']) ? $_SERVER['SERVER_NAME'] : '') . '/' . trim((isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI']: ''), '/'))),

                    'userID' => Request::cookie('cookie_userid') ?: 0,
                    'clientIP' => Request::cookie('cookie_userip') ?: Request::ip(),
                    'ip' => isset($_SERVER['SERVER_ADDR']) ? $_SERVER['SERVER_ADDR'] : '',
                    'param' => $_REQUEST,
                    'time' => microtime(true),
                    'cDate' => date('Y-m-d H:i:s')
                ];
                // 这里特殊处理一下，如果是图片不存在，不记录
                if ($data['url'] && in_array(strtolower(pathinfo($data['url'], PATHINFO_EXTENSION)), [
                    'png',
                    'jpg',
                    'jpeg',
                    'gif',
                    'ico',
                    'js',
                    'css',
                    'txt'
                ]))
                    continue;
                // 公用资源不记录
                if (in_array($data['module'], [
                    'Public',
                    'Favicon'
                ]) && $data['controller'] == '' && $data['action'] == '')
                    continue;
                $res[] = $data;
            }
        }

//        print_r($res);
        
//        return $res ? Httpsqs::put(self::HTTPSQS_NAMEOF_ERROR_LOG, base64_encode(urlencode(json_encode($res)))) : true;
    }

    /**
     * 错误日志落地
     * 
     * @param number $num 一次插入多少条
     */
    public function insert($num = 10)
    {
        $insert = [];
        for ($count = 0; $count < $num; $count ++) {
            $res = Httpsqs::get(self::HTTPSQS_NAMEOF_ERROR_LOG);
            if ($res) {
                $res = json_decode(urldecode(base64_decode($res)), true);
                foreach ($res as $node) {
                    $insert[] = [
                        'level' => $node['level'] ?: 'error',
                        'module' => $node['module'] ?: '',
                        'controller' => $node['controller'] ?: '',
                        'action' => $node['action'] ?: '',
                        'func' => $node['func'] ?: '',
                        'info' => $node['info'] ?: '',
                        'url' => $node['url'] ?: '',
                        'userID' => $node['userID'] ?: 0,
                        'clientIP' => $node['clientIP'] ?: '',
                        'ip' => $node['ip'] ?: '',
                        'param' => $node['param'] ? json_encode($node['param']) : '',
                        'time' => $node['time'] ?: 0,
                        'cDate' => $node['cDate'] ?: '0000-00-00 00:00:00'
                    ];
                }
            } else {
                break;
            }
        }
        if (! $insert)
            return 0;
        return db('jxyLog', 'db_sts', false)->insertAll($insert);
    }
}