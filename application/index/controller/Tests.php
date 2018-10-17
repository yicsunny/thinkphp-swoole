<?php
/**
 * Created by PhpStorm.
 * User: YicSunny
 * Date: 2018/10/9
 * Time: 16:20
 */
namespace app\index\controller;
use app\common\controller\Base;
use jxy\common\logic\Common;
use jxy\test\logic\Test;
use jxy\test\model\Test1;
use think\Exception;

class Tests extends Base
{

    /**
     * @desc 查找test服务，然后调用该服务，返回内容
     */
    public function test($api)
    {
        $jArr = ['code' => 10000, 'msg' => '', 'result' => []];
        try{
            $logic = Test::getInstance();
            $result = $logic->service($api);

            if(isset($result['code'])){
                $jArr['code'] = $result['code'];
                unset($result['code']);
            }
            $jArr['traceID'] = isset($result['traceID']) ? $result['traceID']: '';
            $jArr['serverUrl'] = isset($result['serverUrl']) ? $result['serverUrl']: '';
            $jArr['msg'] = isset($result['msg']) ? $result['msg']: '';
            unset($result['msg']);
            $jArr['result'] = isset($result['result']) ? $result['result']: [];
            $result = '';
            unset($result);
        }catch (Exception $e){
            $jArr['msg'] = $e->getMessage();
            $jArr['code'] = 10001;
        }
        return json_encode($jArr);
    }
}