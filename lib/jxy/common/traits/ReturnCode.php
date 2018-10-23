<?php
namespace jxy\common\traits;

use think\facade\Request;

trait ReturnCode{

    /**
     * 返回成功状态码
     */
    public $return_code_success = 10000;

    /**
     * 设置错误信息
     * 
     * @param number $iCode 错误标识
     * @param string $sMsg 错误信息
     * @param array $aExtraInfo 额外的信息
     */
    public function setError($iCode = 0, $sMsg = '', $aExtraInfo = [])
    {
        $aRes = [
            'code' => $iCode ? $iCode : 0,
            'msg' => $sMsg
        ];
        if ($aExtraInfo)
            $aRes = array_merge($aRes, $aExtraInfo);
        return $aRes;
    }

    /**
     * 设置成功信息
     *
     * @param array $aExtraInfo 额外信息
     */
    public function setSuccess($aExtraInfo = array())
    {
        $aRes = [
            'code' => $this->return_code_success
        ];
        if ($aExtraInfo)
            $aRes = array_merge($aRes, $aExtraInfo);
        return $aRes;
    }
    
    /**
     * 判断返回结果是否正确
     * @param array $aRes
     * @return boolean
     */
    public function isSuccess($aRes)
    {
        return (isset($aRes['code']) && $aRes['code'] == $this->return_code_success) ? true : false;
    }

    /**
     * 直接打印
     *
     * @param string $msg 信息
     * @param bool $exit 是否输出后退出
     */
    public function output($msg, $exit = false)
    {
        if (! Request::isCli()) {
            echo $msg . '<br/>';
        } else {
            echo $msg . "\n";
        }
        if ($exit) {
            exit();
        }
    }
}