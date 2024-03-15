<?php
namespace util;

/**
 * 返回后台管理分页数据类
 */
class listData
{
    public $code = 200;
    public $message = '';
    public $data;
    public $count;
    public $count_arr;
    public function __construct($data = '', $count = 0, $code = 200, $msg = '', $count_arr = '')
    {
        $this->data = empty($data) ? '' : $data;
        $this->code = empty($code) ? 200 : $code;
        $this->count = $count;
        if(!empty($count_arr)){
            $this->count_arr = $count_arr;
        }
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
            case 600: $msg = '登录失败';break;  //具体错误看返回结果
            case 601: $msg = '请求参数错误'; break;
            case 602: $msg = '用户已存在'; break;
            case 603: $msg = '验证码请求失败'; break;
            case 604: $msg = '请求太频繁，请稍后再尝试'; break;
            case 605: $msg = '请求失败'; break; //详情看返回结果
            case 606: $msg = '请先登录'; break;
            case 607: $msg = '用户不存在'; break;
            default: $msg = '请求错误'; break;
        }
        $this->message = $msg;
    }
}