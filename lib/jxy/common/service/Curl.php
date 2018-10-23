<?php
namespace jxy\common\service;

class Curl{
    
    /**
     * get 数据
     * @param string $url
     * @param array $header 设置header
     * @param int $timeout 超时时间
     * @param string $save_cookie 存储cookie的文件名
     */
    public static function get($url, $header = [], $timeout = 60, $use_cookie = '', $save_cookie = '')
    {
        $ch = curl_init($url);
        $default_header = [
            "User-Agent" => "Mozilla/5.0 (Windows NT 6.1; Win64; x64; rv:59.0) Gecko/20100101 Firefox/59.0",
            "Accept-Language" => "zh-CN,zh;q=0.8,zh-TW;q=0.7,zh-HK;q=0.5,en-US;q=0.3,en;q=0.2",
        ];
        $header = array_merge($default_header, $header);
        $headerSend = [];
        foreach($header as $key => $val){
            $headerSend[] = $key . ': '. $val;
        }
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headerSend);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); 
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_FAILONERROR, true);
        if($timeout) curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
        if(isset($header['Referer'])){
            curl_setopt($ch, CURLOPT_REFERER, $header['Referer']);
        }
        if(isset($header['User-Agent'])){
            curl_setopt($ch, CURLOPT_USERAGENT, $header['User-Agent']);
        }
        curl_setopt($ch, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_AUTOREFERER, 1);
        if ($save_cookie) {
            curl_setopt($ch, CURLOPT_COOKIEJAR, $save_cookie);
        }
        if($use_cookie){
            curl_setopt($ch, CURLOPT_COOKIEFILE, $use_cookie);
        }
        $result = curl_exec($ch);
        if (curl_errno($ch)){
            trace(['info' => curl_error($ch), 'func' => __METHOD__, 'url' => $url], 'error');
        }
        curl_close($ch);
        return $result;
    }
    
    /**
     * post 数据
     * @param string $url
     * @param array $header 设置header
     * @param int $timeout 超时时间
     * @param string $save_cookie 存储cookie的文件名
     */
    public static function post($url, $data = [], $header = [], $timeout = 60, $use_cookie = '', $save_cookie = ''){
        $ch = curl_init($url);
        $default_header = [
            "User-Agent" => "Mozilla/5.0 (Windows NT 6.1; Win64; x64; rv:59.0) Gecko/20100101 Firefox/59.0",
            "Accept-Language" => "zh-CN,zh;q=0.8,zh-TW;q=0.7,zh-HK;q=0.5,en-US;q=0.3,en;q=0.2",
        ];
        $header = array_merge($default_header, $header);
        $headerSend = [];
        foreach($header as $key => $val){
            $headerSend[] = $key . ': '. $val;
        }
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headerSend);
        if($data){
            $data = http_build_query($data);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        }
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_FAILONERROR, true);
        if($timeout) curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
        if(isset($header['Referer'])){
            curl_setopt($ch, CURLOPT_REFERER, $header['Referer']);
        }
        if(isset($header['User-Agent'])){
            curl_setopt($ch, CURLOPT_USERAGENT, $header['User-Agent']);
        }
        curl_setopt($ch, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_AUTOREFERER, 1);
        if ($save_cookie) {
            curl_setopt($ch, CURLOPT_COOKIEJAR, $save_cookie);
        }
        if($use_cookie){
            curl_setopt($ch, CURLOPT_COOKIEFILE, $use_cookie);
        }
        $result = curl_exec($ch);
        if (curl_errno($ch)){
            trace(['info' => curl_error($ch), 'func' => __METHOD__, 'url' => $url], 'error');
        }
        curl_close($ch);
        return $result;
    }

    public static function putQueue($httpsqsUrl)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $httpsqsUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        $rst = curl_exec($ch);
        if (curl_errno($ch)){
            trace(['info' => curl_error($ch), 'func' => __METHOD__, 'url' => $httpsqsUrl], 'error');
        }
        curl_close($ch);
        if ($rst === 'HTTPSQS_PUT_OK') {
            return true;
        } else {
            return false;
        }
    }

    public static function postNoHeader($url, $array, $timeout = 0, $use_cookie = '', $save_cookie = ''){
        $ch = curl_init ();

        // set the url, number of POST vars, POST data
        curl_setopt ( $ch, CURLOPT_URL, $url );
        curl_setopt ( $ch, CURLOPT_HEADER, 0);
        curl_setopt ( $ch, CURLOPT_POST, 1 );
        curl_setopt ( $ch, CURLOPT_HTTPHEADER, array('Expect:'));
        curl_setopt ( $ch, CURLOPT_POSTFIELDS, $array ); // POST数据
        curl_setopt ( $ch, CURLOPT_RETURNTRANSFER, 1 ); // 不直接输出，返回到变量
//        curl_setopt ( $ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt ( $ch, CURLOPT_SSL_VERIFYPEER, FALSE); //不验证SSL证书
        curl_setopt ( $ch, CURLOPT_SSL_VERIFYHOST, false);
        if($timeout) curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
        if ($save_cookie) curl_setopt($ch, CURLOPT_COOKIEJAR, $save_cookie);
        if($use_cookie) curl_setopt($ch, CURLOPT_COOKIEFILE, $use_cookie);

        // execute post而
        $result = curl_exec ( $ch );
        curl_close ( $ch );
        return $result;
    }


    /**
     * @todo
     */
    public static function getSsl(){
        
    }
    /**
     * @todo
     */
    public static function postSsl(){
    }

    public static function desEcbEncrypt($data)
    {
        return openssl_encrypt ($data, 'des-ecb', \think\facade\Env::get('JXY.interface_auth_key', 'ah21jT#agDi7%df2pWd#l9'));
    }

}