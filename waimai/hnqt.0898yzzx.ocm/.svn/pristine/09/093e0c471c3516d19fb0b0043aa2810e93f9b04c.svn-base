<?php

namespace app\common\controller;

class ClientBase extends Base
{
    /*分页*/
    protected $limit = 10; //每页条数
    protected $page = 1;   //页码

    protected function _initialize()
    {
        parent::_initialize();
        if (!isLogin()) {
            if(config('web_config.zg_debug')) {
                //测试模式
                session('user', ['id'=>6,'openid'=>'oG-NJ1JlTUO19tF7h0trqL0EL_1k']);
            }else{
                //线上模式
                // 获取当前网页，授权后跳回
                $path = request()->url();
                session('path', $path);
                //跳转到微信授权
                $this->redirect('client/auth/index');
            }
        }

        $limit = request()->param('limit/d');
        $page = request()->param('page/d');
        if ($limit) {
            $this->limit = $limit;
        }
        if ($page) {
            $this->page = $page;
        }
    }
}