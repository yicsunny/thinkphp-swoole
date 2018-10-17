<?php
namespace app\index\controller;

use app\common\controller\Base;
use app\statistics\model\ServerCount;
use jxy\common\consts\HttpsqsName;
use jxy\common\logic\Common;
use jxy\common\service\Curl;
use jxy\common\service\Httpsqs;
use think\Exception;
use think\paginator\driver\Bootstrap;

class Index extends Base
{
    public $checkFlag = false;

    public function index($name = 'hello')
    {

        $content = Curl::get("http://192.168.11.26:9502/index/tests?userRole=xxx&value=1");
        var_dump($content);
        $result = json_decode($content, true);
        print_r($result);


//        return $name;
    }

    public function url($serverType)
    {
        $logic = Common::getInstance();
        $list = $logic->getRedis()->sMembers($serverType. ':all');
        $result = [];
        foreach ($list as $value){
            $result[] = unserialize($value);
        }
        $this->assign('list', $result);
        $this->assign('serverType', $serverType);
        return $this->fetch();
    }

    public function refresh($serverType)
    {
        //获取所有的集合内容进行挨个获取数据
        $logic = Common::getInstance();
        $list = $logic->getRedis()->sMembers($serverType. ':all');
        $logic->getRedis()->del($serverType. ":all");
        $result = [];
        foreach($list as $value){
            $arr = unserialize($value);
            try{
                $content = Curl::get($arr['url'], [], 2);
                $logic->getRedis()->sRem($serverType, $arr['url']);
                if($content){
                    $logic->getRedis()->sAdd($serverType, $arr['url']);
                    $arr['status'] = '运行中';
                }else{
                    $arr['status'] = "下架";
                }
            }catch (Exception $e){
                $arr['status'] = "下架";
            }
            $result[] = $arr;
            $logic->getRedis()->sAdd($serverType. ":all", serialize($arr));
        }
        exit(json_encode($result));
    }


    public function add($serverType)
    {
        $this->assign('serverType', $serverType);
        return $this->fetch();
    }

    public function doAdd()
    {
        $serverType = request()->param('serverType', '', 'trim');
        $url = request()->param('url', '', 'trim');
        if(!$serverType || !$url){
            $this->error("添加错误");
        }
        $logic = Common::getInstance();
        $arr = ['url' => $url];
        try{
            $content = Curl::get($url, [], 2);
            if(!$content){
                $arr['status'] = '下架';
            }else{
                $arr['status'] = "运行中";
                $logic->getRedis()->sAdd($serverType, $url);
            }
        }catch (Exception $e){
            $arr['status'] = '下架';
        }
        $logic->getRedis()->sAdd($serverType. ":all", serialize($arr));
        $this->success("添加成功", 'index/url/'. $serverType);
    }

    public function del()
    {
        $serverType = request()->param('serverType');
        $keys = request()->param('keys');

        $logic = Common::getInstance();
        $list = $logic->getRedis()->sMembers($serverType. ':all');
        foreach ($list as $key => $value){
            if($key == $keys){
                $logic->getRedis()->sRem($serverType. ':all', $value);
                $logic->getRedis()->sRem($serverType, unserialize($value)['url']);
            }
        }
        $this->success("删除成功", 'index/url/'. $serverType);
    }

    public function change()
    {
        $serverType = request()->param('serverType');
        $keys = request()->param('keys');


        $logic = Common::getInstance();
        $list = $logic->getRedis()->sMembers($serverType. ':all');
        foreach ($list as $key => $value){
            if($key == $keys){
                $logic->getRedis()->sRem($serverType. ':all', $value);
                $value = unserialize($value);
                $content = Curl::get($value['url'], [], 2);
                $logic->getRedis()->sRem($serverType, $value['url']);
                if(!$content){
                    $value['status'] = '下架';
                }else{
                    $value['status'] = "运行中";
                    $logic->getRedis()->sAdd($serverType, $value['url']);
                }
                $logic->getRedis()->sAdd($serverType. ':all', serialize($value));
            }
        }
        $this->success("更新成功", 'index/url/'. $serverType);
    }

    /**
     * @desc 获取api详细列表
     */
    public function api()
    {
        $model = ServerCount::getInstance();
        $list = $model->selectBySearch([], [], 0, 0, 'count(*) as c, api', 'api');
        $this->assign('list', $list);
        return $this->fetch();
    }

    public function details()
    {
        $api = $this->request->param('api', '', 'trim');

        $page = $this->request->param('page', 1, 'intval');
        $limit = 50;
        $offset = (($page < 1? 1: $page) - 1) * $limit;

        $model = ServerCount::getInstance();
        $searchArr = [];
        if($api){
            $searchArr['api'] = $api;
        }
        $list = $model->selectBySearch($searchArr, ['id' => 'desc'], $offset, $limit);
        $this->assign('list', $list);
        $count = $model->countBySearch($searchArr);
        $page = Bootstrap::getCurrentPage();
        $config['path'] = isset($config['path']) ? $config['path'] : Bootstrap::getCurrentPath();
        $config['query'] = request()->param() ? : [];
        $pages = Bootstrap::make($list, $limit, $page, $count, false, $config);
        $this->assign('page', $pages->render());

        return $this->fetch();
    }

}
