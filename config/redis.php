<?php
//redis 相关配置
return [
    'main' => [
        'host' => \think\facade\Env::get('Redis_Main.host', ''),
        'port' => \think\facade\Env::get('Redis_Main.port', ''),
    ],
];
