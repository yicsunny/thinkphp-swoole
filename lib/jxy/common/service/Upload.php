<?php
namespace jxy\common\service;

use think\facade\Env;

class Upload{
    
    const IMG_MAX_SIZE = 2048000; //上传图片最大的尺寸
    
    const FILE_MAX_SIZE = 4096000; //上传文件最大的尺寸
    
    const IMG_EXT = ['jpg', 'jpeg', 'png', 'gif']; //可上传图片的扩展
    
    const FILE_EXT = ['xls', 'xlsx', 'doc', 'docx']; //可上传文件的扩展
    
    /**
     * 文件上传保存
     * @param string $inputName 文件input的名字
     * @param string $path 保存文件的目录
     * @param int $size 文件上传最大限制
     * @param array $ext 文件上传扩展名
     * @param mixed $saveName 如果true图片名自动生成， 如果false保持原名，如果字符串，则以该字符串命名
     * @param string $rule 名字命名规则方法(需要$saveName设置为true) 如：date， md5,sha1,uniqid,
     */
    public static function save($inputName, $path = '', $size = 0, $ext = [], $saveName = true, $rule = ''){
        
        $file = request()->file($inputName);
        
        if(!$file) return false;
        
        $path = Env::get('ROOT_PATH') . 'public/upload/' . rtrim($path, '/') . '/';
        
        $vaild = [];
        if($size) $vaild['size'] = $size;
        if($ext) $vaild['ext'] = implode(',', $ext);
        
        if(!is_object($file)){//如果是多文件上传
            $res = [];
            foreach($file as $one){
                $info = $one->validate($vaild)->rule($rule)->move($path, $saveName);
                if(!$info){
                    return [
                        'error' => 1,
                        'msg' => $one->getError(),
                    ];
                }else{
                    $res[] = [
                        'fileName' => $info->getFilename(),
                        'saveName' => $info->getSaveName(),
                    ];
                }
            }
            return [
                'error' => 0,
                'list' => $res,
            ];
        }else{
            $info = $file->validate($vaild)->rule($rule)->move($path, $saveName); 
            if($info){
                return [
                    'error' => 0,
                    'fileName' => $info->getFilename(),
                    'saveName' => $info->getSaveName(),
                ];
            }else{
                return [
                    'error' => 1,
                    'msg' => $file->getError(),
                ];
            }
        }
    }
    
    /**
     * 文件上传保存
     * @param string $inputName 文件input的名字
     * @param string $path 保存文件的目录
     * @param int $size 文件上传最大限制
     * @param array $ext 文件上传扩展名
     * @param mixed $saveName 如果true图片名自动生成， 如果false保持原名，如果字符串，则以该字符串命名
     * @param string $rule 名字命名规则方法(需要$saveName设置为true) 如：date， md5,sha1,uniqid,
     */
    public static function image($inputName, $path = '', $size = 0, $ext = [], $saveName = true, $rule = ''){
        
        if(!$size) $size = self::IMG_MAX_SIZE;
        if(!$ext) $ext = self::IMG_EXT;
        
        return self::save($inputName, $path, $size, $ext, $saveName, $rule);    
    }
    
    
    /**
     * 文件上传保存
     * @param string $inputName 文件input的名字
     * @param string $path 保存文件的目录
     * @param int $size 文件上传最大限制
     * @param array $ext 文件上传扩展名
     * @param mixed $saveName 如果true图片名自动生成， 如果false保持原名，如果字符串，则以该字符串命名
     * @param string $rule 名字命名规则方法(需要$saveName设置为true) 如：date， md5,sha1,uniqid,
     */
    public static function file($inputName, $path = '', $size = 0, $ext = [], $saveName = true, $rule = ''){
        
        if(!$size) $size = self::FILE_MAX_SIZE;
        if(!$ext) $ext = self::FILE_EXT;
        
        return self::save($inputName, $path, $size, $ext, $saveName, $rule);
    }
}