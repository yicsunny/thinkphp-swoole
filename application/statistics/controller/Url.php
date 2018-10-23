<?php
/**
 * Created by PhpStorm.
 * User: YicSunny
 * Date: 2018/10/12
 * Time: 21:16
 */
namespace app\statistics\controller;

use app\statistics\logic\UrlLogic;
use think\Controller;

class url extends Controller
{

    public function index()
    {
        $logic = UrlLogic::getInstance();
        $result = $logic->deal();

        echo "完成一次{$result}". date("Y-m-d H:i:s"). "\r\n";
        exit;
    }
}