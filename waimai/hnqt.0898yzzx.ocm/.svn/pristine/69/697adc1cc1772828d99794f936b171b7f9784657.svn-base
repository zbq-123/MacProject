<?php
/**
 * Created by PhpStorm.
 * User: wenyi
 * Date: 2020/7/9
 * Time: 15:16
 */

namespace app\store\controller;


use app\common\controller\StoreBase;
use app\admin\model\Admin;
use think\Controller;

class Login extends Controller
{
    public function index(Admin $admin){
        if(isLogin()){
            $this->redirect('index/index');
        }
        if(request()->isPost()){
            $username = request()->post('username');
            $password = request()->post('password');
            if(empty($username) || empty($password)){
                $this->error('用户名密码不能为空');
            }

            $result = $admin->login($username, $password);
            if ($result['is_root'] == 1){
                $this->error('非商家账号，无法登陆！');
            }
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