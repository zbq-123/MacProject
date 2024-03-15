<?php
namespace app\admin\controller;

use app\admin\model\Admin;
use think\Controller;

class Login extends Controller
{
    /**
     * 登录
     */
    public function index(Admin $admin)
    {
        if(isLogin()){
            $this->redirect('index/index');
        }
        if(request()->isPost()){
            $username = request()->post('username');
            $password = request()->post('password');
            if(empty($password) || empty($password)){
                $this->error('用户名密码不能为空');
            }
            if (!captcha_check(input('post.captcha'))) {
                $this->error("验证码错误");
            }
            $result = $admin->login($username, $password);
            if(empty($result)){
                $this->error($admin->getError());
            }else{
                session('admin', $result);
                $this->success('登录成功','index/index', null ,1);
            }
        }
        return $this->fetch();
    }

    /**
     * 注销登录
     */
    public function logout()
    {
        session('admin', null);
        $this->redirect('login/index');
    }
}
