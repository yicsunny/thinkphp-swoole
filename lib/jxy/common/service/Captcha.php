<?php
namespace jxy\common\service;

use think\captcha\Captcha as CaptchaObj;
use think\facade\Config;

class Captcha{
    
    private static $instance;
    
    /**
     * @return \think\captcha\Captcha
     */
    private static function getInstance(){
        
        if(!self::$instance) self::$instance = new CaptchaObj(Config::pull('captcha'));
        
        return self::$instance;
    }
    
    public static function entry($id = ''){
        
        return self::getInstance()->entry($id); 
    }
    
    public static function check($code, $id = ''){
        
        return self::getInstance()->check($code, $id);
    }
    
}