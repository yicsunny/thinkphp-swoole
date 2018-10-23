<?php
namespace jxy\common\logic;

use jxy\common\traits\ReturnCode as ReturnCodeTraits;
use think\facade\Request;
use think\facade\App;

class BaseCommon
{
    //当前时间戳
    public $time;
    //用户IP
    public $userIP = '';

    public $redis;
    
    use ReturnCodeTraits;
    
    public function __construct()
    {
        $this->time = time();
        $this->userIP = $this->getUserIp();
    }
    
    private function getUserIp()
    {
        if(defined('APP_PATH') && basename(App::getAppPath()) == 'api'){
            return Request::ip();
        }else{
            return Request::cookie('cookie_userip', 0, 'trim');
        }
    }

    /**
     * 得到调用的逻辑类
     * 
     * @return $this
     */
    public static function getInstance()
    {
        return model(get_called_class(), 'logic');
    }
    
    /**
     * 得到调用的逻辑类(带参数)
     * @return $this
     */
    public static function getInstanceArgs(){
        static $pool = [];
        $args = func_get_args();
        $class = get_called_class();
        $link = md5(var_export(['c' => $class, 'p' => $args], true));
        if(!isset($pool[$link])){
            $new = new \ReflectionClass($class);
            $pool[$link] = $new->newInstanceArgs($args);
        }
        return $pool[$link];
    }
}
