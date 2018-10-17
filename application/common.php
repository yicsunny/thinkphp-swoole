<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2016 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 流年 <liu21st@gmail.com>
// +----------------------------------------------------------------------

// 应用公共文件
if(!function_exists('get_decode_token_key')){
    function get_decode_token_key($data){
        return openssl_decrypt ($data, 'des-ecb', \think\facade\Env::get('JXY.interface_auth_key', 'ah21jT#agDi7%df2pWd#l9'));
    }
}