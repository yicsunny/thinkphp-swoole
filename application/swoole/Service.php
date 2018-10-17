<?php
/**
 * Created by PhpStorm.
 * User: YicSunny
 * Date: 2018/10/13
 * Time: 15:55
 */
namespace app\swoole;
use app\statistics\model\ServerCount;
use think\Exception;
use think\facade\Env;
use think\swoole\Server;

class Service extends Server
{
    protected $host = '0.0.0.0';

    //投递任务端口从9401开始
    protected $port = 9401;
    protected $serverType = 'socket';
    protected $option = [
        'worker_num'=> 1,
        'task_worker_num' => 4,
        'daemonize'	=> true,
        'backlog'	=> 128,
    ];

    public function init()
    {
        $this->option['pid_file'] = Env::get('runtime_path') . 'swoole_server.pid';
        $this->option['log_file'] = Env::get('runtime_path') . 'swoole_server.pid';
        $this->swoole->set($this->option);
    }

    public function onRequest($request, $response)
    {
        $this->swoole->task($request->post);
        $response->end('show');
    }

    public function onReceive($server, $data)
    {
        $server->send($data->fd, $data);
    }

    public function onMessage($server, $frame)
    {
        $server->push($frame->fd, "this is server");
        return $frame->fd;
    }


    public function onTask($server, $taskID, $workID, $data)
    {
        try{
            $model = ServerCount::getInstance();
            $result = $model->addInfo($data);
        }catch (Exception $e){
            //MYSQL gone away的话重新来一次。。。
            //MYSQL 不会断线重连啊 - -。。。
            $model = new ServerCount();
            $result = $model->addInfo($data);
        }
        return $result;
    }

    /**
     * 监听连接Finish事件
     * @param $serv
     * @param $task_id
     * @param $data
     */
    public function onFinish($serv, $task_id, $data) {
        echo "Task {$task_id} finish\n\n";
        return true;
    }

}