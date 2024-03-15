<?php
namespace util\exception;

use Exception;
use think\exception\Handle;
use think\exception\HttpException;
use util\Ret;

/**
 * 自定义http异常处理类
 */
class HttpHandle extends Handle
{
    public function render(Exception $e)
    {
        if($e instanceof HttpException){
            $code = $e->getStatusCode();
            $msg = $e->getMessage();

            if($code >= 600){
                return json(new Ret("", $code, $msg));
            }
        }
        return parent::render($e);
    }
}