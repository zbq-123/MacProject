<?php
namespace app\client\controller;

use app\common\controller\ClientBase;
use think\Request;
use util\Ret;

/**
 * 文件上传
 */
class Upload extends ClientBase
{
    protected $_config = array(
        'rule'   =>  [
            'userhead'  =>  ['size'=>3*1024*1024,'ext'=>'jpg,png,gif,jpeg,bmp,heic'],
            'comment'  =>  ['size'=>3*1024*1024,'ext'=>'jpg,png,gif,jpeg,bmp,heic'],
            'feedback'  =>  ['size'=>3*1024*1024,'ext'=>'jpg,png,gif,jpeg,bmp,heic'],
        ],
        'path' =>  [
            'userhead'  =>  ROOT_PATH.'public/uploads/userhead',
            'comment'  =>  ROOT_PATH.'public/uploads/comment',
            'feedback'  =>  ROOT_PATH.'public/uploads/feedback',
        ]
    );

    protected function _initialize()
    {
        if ($this->islogin) {
            return new Ret('',10001);
        }
    }
    //保存压缩处理上传图片
    protected function saveImage($name,$width = 0,$height = 0,$thumbType = 1)
    {
        $request = Request::instance();
        $file = $request->file('file');
        $info = $file->validate($this->_config["rule"]["{$name}"])->move($this->_config['path']["{$name}"]);
        if ($info) {
            if ($width && $height) {
                $image = \think\Image::open($info);
                $image->thumb($width, $height, $thumbType)->save($this->_config['path']["{$name}"].'/'.$info->getSaveName());
                return config('web_config.upload_host')."{$name}/".$info->getSaveName();
            }else{
                return config('web_config.upload_host')."{$name}/".$info->getSaveName();
            }
        }else{
            return new Ret('',10003, '上传失败');
        }
    }
    //用户头像
    public function userHead()
    {
        $result = $this->saveImage("userhead",500,500);
        $result = str_replace("\\","/",$result);
        return new Ret($result);
    }

    public function comment()
    {
        $result = $this->saveImage("comment");
        $result = str_replace("\\","/",$result);
        return new Ret($result);
    }

    public function feedback()
    {
        $result = $this->saveImage("feedback");
        $result = str_replace("\\","/",$result);
        return new Ret($result);
    }

}