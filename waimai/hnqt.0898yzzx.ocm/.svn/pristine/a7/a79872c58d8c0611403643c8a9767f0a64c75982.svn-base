<?php
namespace util;

/**
 * 返回前端数据类
 */
class Ret
{
    public $code = 200;
    public $message = '';
    public $data;
    public function __construct($data = null, $code = 200, $msg = '')
    {
        $this->data = $data === null ? '' : $data;
        $this->code = empty($code) ? 200 : $code;
        $this->setMsg($code);
        if(!empty($msg)){
            $this->message = $msg;
        }
    }
    public function setMsg($code)
    {
        $msg = '';
        switch ($code){
            case 200: break;
            case 10001: $msg = '未登录，请求失败';break;
            case 10002: $msg = '自动登录失败，请重新登录';break;
            case 10003: $msg = '请求失败';break;//具体看错误原因
            default: $msg = '请求错误'; break;
        }
        $this->message = $msg;
    }
}