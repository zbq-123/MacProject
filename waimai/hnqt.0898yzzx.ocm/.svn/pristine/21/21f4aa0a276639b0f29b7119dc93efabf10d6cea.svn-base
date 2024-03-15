<?php
namespace app\common\controller;

use util\Auth;

class AdminBase extends Base
{
    protected $page = 1;
    protected $limit = 10;
    protected $auth;    //权限验证对象
    protected $path;    //当前请求path
    protected $adminId;     //管理员id
    protected $nocheck = []; //不检查权限的操作数组
    /**
     * 初始化
     */
    protected function _initialize()
    {
        parent::_initialize();
        //登录验证
        if(!isLogin()){
            $this->redirect('Login/index');
        }
        $this->adminId = adminId();

        //权限验证
        $this->path = strtolower(request()->module().'/'.request()->controller().'/'.request()->action());
        $this->auth = new Auth();
        //dump($auth->check($path, 1, 1, '!url'));
        if (false == in_array(request()->action(), $this->nocheck)) {
            if(session('admin.is_root') != 1){
                if(false == $this->auth->check($this->path, $this->adminId)){
                    abort(608, '没有权限');
                }
            }
        }

        //加载渲染公共布局
        //$this->view->engine->layout('layout/default');
        //生成menu
        if(empty(session('admin.menus'))){
            $menus = getMenus(config('menu'));
            session('admin.menus', $menus);
        }else{
            $menus = session('admin.menus');
        }
        $this->assign('menus', $menus);
        //面包屑导航
        $breadcrumb = getBreadcrumb(config('menu'));
        $this->assign('breadcrumb', $breadcrumb);

        $limit = request()->param('limit/d');
        $page = request()->param('page/d');
        if ($limit){
            $this->limit = $limit;
        }
        if($page){
            $this->page = $page;
        }
    }

    /**
     * 验证权限方法
     * @param $path 请求操作地址
     * @return bool
     */
    protected function check($path)
    {
        if (isRoot()) {
            return true;
        } else {
            return  $this->auth->check($path, $this->adminId);
        }
    }
}