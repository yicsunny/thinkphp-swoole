<?php
//队列 相关配置
return [
    'host' => \think\facade\Env::get('HTTPSQS.host', ''),
    'port' => \think\facade\Env::get('HTTPSQS.port', ''),
    'auth' => \think\facade\Env::get('HTTPSQS.auth', ''),
    'close' => \think\facade\Env::get('HTTPSQS.close', 0), //是否关闭写入
];