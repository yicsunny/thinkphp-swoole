<?php
namespace jxy\common\service;

use think\exception\Handle;
use think\exception\HttpException;

/**
 * 异常输出特殊处理
 */
class JxyException extends Handle
{
    public function render(\Exception $e){
        // 请求异常
        if($e instanceof HttpException && request()->isAjax()){
            return response($e->getMessage(), $e->getStatusCode());
        }else{
            ob_end_clean();
            return json(['code' => 50003, 'msg' => $e->getMessage()]);
        }
    }
    
}