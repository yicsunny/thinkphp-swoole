<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006~2018 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------

Route::get('think', function () {
    return 'hello,ThinkPHP5!';
});

Route::group("index", function (){
    Route::get("url/:serverType", "index/Index/url");
    Route::get("refresh/:serverType", "index/Index/refresh");
    Route::get("add/:serverType", "index/Index/add");

    Route::post("index/do-add", "index/Index/doAdd");
    Route::get("index/del", "index/Index/del");
    Route::get("index/change", "index/Index/change");
    Route::get("index/details", "index/Index/details");

    Route::get("test/:api", "index/Tests/test");
    Route::get("list", "index/Index/api");
    Route::get("index", "index/Index/index");
});