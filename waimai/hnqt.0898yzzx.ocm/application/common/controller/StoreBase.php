<?php
namespace app\common\controller;

class StoreBase extends Base
{
    /*分页*/
    protected $limit = 10; //每页条数
    protected $page = 1;   //页码
    protected $islogin = false;

    protected function _initialize()
    {
        parent::_initialize();
        //登录验证
        if(!isLogin()){
            $this->redirect('Login/index');
        }

        $limit = request()->param('limit/d');
        $page = request()->param('page/d');
        if ($limit){
            $this->limit = $limit;
        }
        if($page){
            $this->page = $page;
        }
    }

}