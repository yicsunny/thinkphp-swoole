<?php
namespace app\common\controller;

use jxy\common\consts\HttpsqsName;
use jxy\common\service\Curl;
use jxy\common\service\Httpsqs;
use think\Controller;
use jxy\common\traits\ReturnCode;
use think\Exception;
use think\facade\Env;

class Base extends Controller{


    /**
     * 当前时间戳
     * @var integer
     */
    public $time = 0;

    public $checkFlag = true;

    use ReturnCode {ReturnCode::setError as pSetError; ReturnCode::setSuccess as pSetSuccess;}

    protected function initialize()
    {
        $this->checkHeader($this->checkFlag);
    }

    public function checkHeader($checkFlag)
    {
        if(!$checkFlag){
            return true;
        }
        //获取头部信息进行解析
        $header = request()->header();
        $server = request()->server();

//        print_r(request()->param());
//        print_r(request()->header());
//        print_r(request()->server());
//        exit;

        //发送队列写入请求信息
//        Httpsqs::put(HttpsqsName::HTTPSQS_NAMEOF_LOG_REQUEST_URL, ['api' => request()->param('api'), 'url' => urlencode($server["REQUEST_URI"]), 'host' => $server['REMOTE_ADDR'], 'time' => time()]);
//        //TODO 也别发送队列了，直接投递任务吧。
        $result = Curl::post("127.0.0.1:9401", ['api' => request()->param('api'), 'url' => urlencode($server["REQUEST_URI"]), 'host' => $server['REMOTE_ADDR'], 'time' => time()]);
//        var_dump($result);
        if(!isset($header['token']) || !request()->param('api')){
            exception('非法请求', 10001);
        }

        //头部信息解密，验证身份真实性
        $token = get_decode_token_key($header['token']);
        if($token != $header['realip']){
            exception('token错误', 10002);
        }
        return true;
    }

    
    /**
     * 设置错误信息(json格式输出)
     * 
     * @param number $iCode 错误标识
     * @param string $sMsg 错误信息
     * @param array $aExtraInfo 额外的信息
     */
    public function setError($iCode = 0, $sMsg = '', $aExtraInfo = [])
    {
        return json($this->pSetError($iCode, $sMsg, $aExtraInfo));
    }
    
    /**
     * 设置成功信息(json格式输出)
     *
     * @param array $aExtraInfo 额外信息
     */
    public function setSuccess($aExtraInfo = array())
    {
        return json($this->pSetSuccess($aExtraInfo));
    }

}

