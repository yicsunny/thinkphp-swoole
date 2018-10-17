<?php

namespace redis;

class RedisSentinel {
    private $handle;
    private $nodes;

    public function __construct(){
        $this->handle = false;
        $this->nodes = array();
    }

    //加入多个节点
    public function addnode($host, $port){
        $len = count($this->nodes);
        $this->nodes[$len]['host'] = $host;
        $this->nodes[$len]['port'] = $port;
    }

    //建立连接
    public function connection(){
        if (!$this->handle) {
            for($i=0; $i < count($this->nodes); $i++){
                if (!$sock = fsockopen($this->nodes[$i]['host'], $this->nodes[$i]['port'], $errno, $errstr)) {continue;}
                $this->handle = $sock;
                return $this->handle;
            }
        }
        return false;
    }

    //执行命令
    public function command($commands){
        $this->connection();
        if ( !$this->handle ) {return false;}
        if ( is_array($commands) ){$commands = implode("\r\n", $commands);}
        $command = $commands . "\r\n";
        for ( $written = 0; $written < strlen($command); $written += $fwrite ){
            if ( !$fwrite = fwrite($this->handle, substr($command, $written)) ) {return false;}
        }
        return true;
    }

    //获取子节点
    public function get_slaves($mastername){
        $key = 'slaves '.$mastername;
        if ($this->command("SENTINEL {$key}") == false){
            return false;
        }
        return $this->get_nodeinfo($this->handle);
    }

    //masters,获取主服务
    public function get_masters($mastername){
        $key = 'get-master-addr-by-name '.$mastername;
        if ($this->command("SENTINEL {$key}") == false){
            return false;
        }
        return $this->get_masterinfo($this->handle);
    }

    //ping，检测sentinels是否可用
    public function ping(){
        $this->command("PING");
        if (strpos($this->get_response($this->handle), "PONG") === false){
            return false;
        }
        return true;
    }

    //其他命令返回信息
    public function get_response(){
        if ( !$this->handle ) return false;
        return trim(fgets($this->handle), "\r\n ");
    }

    //获取master信息
    public function get_masterinfo($handle){
        if ( !$handle ) return false;
        $col = intval(substr(trim(fgets($handle)), 1));
        if(feof($handle)) return false;

        for($i=0; $i < $col; $i++){
            $len = intval(substr(trim(fgets($handle)), 1));
            if(feof($handle)) break;

            $value = substr(trim(fgets($handle)), 0, $len);
            $sentinels[$i] = $value;
            if(feof($handle)) break;
        }
        return $sentinels;
    }

    //获取节点信息
    public function get_nodeinfo($handle){
        if ( !$handle ) return false;
        $col = intval(substr(trim(fgets($handle)), 1));
        if(feof($handle)) return false;

        for($i=0; $i < $col; $i++){
            $row = intval(substr(trim(fgets($handle)), 1))/2;
            if(feof($handle)) return false;
            for($j=0; $j < $row; $j++){
                $len = intval(substr(trim(fgets($handle)), 1));
                if(feof($handle)) break;

                $key = substr(trim(fgets($handle)), 0, $len);
                if(feof($handle)) break;

                $len = intval(substr(trim(fgets($handle)), 1));
                if(feof($handle)) break;

                $value = substr(trim(fgets($handle)), 0, $len);
                $sentinels[$i][$key] = $value;
                if(feof($handle)) break;
            }
        }
        return $sentinels;
    }

}
