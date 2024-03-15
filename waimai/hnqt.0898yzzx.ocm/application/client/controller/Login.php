<?php
namespace app\client\controller;

use app\common\controller\ClientBase;
use app\common\model\SysExpertLibrary;
use app\common\model\SysUser;

class Login extends ClientBase
{
    public function repairman_login()
    {
        if(request()->isPost()){
            $username = request()->post('username');
            $password = request()->post('password');
            if(empty($password) || empty($password)){
                $this->error('用户名密码不能为空');
            }
            $repairman_model = new SysExpertLibrary();
            $result = $repairman_model->login($username, $password);
            if(empty($result)){
                $this->error($repairman_model->getError());
            }else{
                $this->success('登录成功','index/repair_index', null ,1);
            }
        }
        return $this->fetch();
    }
    //管理端登录
    public function adminman_login()
    {
        if(request()->isPost()){
            $username = request()->post('username');
            $password = request()->post('password');
            if(empty($password) || empty($password)){
                $this->error('用户名密码不能为空');
            }
            $repairman_model = new SysUser();
            $result = $repairman_model->logins($username, $password);
            if(empty($result)){
                $this->error($repairman_model->getError());
            }else{
                $this->success('登录成功','repair/admin_repair', null ,1);
            }
        }
        return $this->fetch();
    }

    //管理端登录
    public function work_login()
    {
        if(request()->isPost()){
            $username = request()->post('username');
            $password = request()->post('password');
            if(empty($password) || empty($password)){
                $this->error('用户名密码不能为空');
            }
            $repairman_model = new SysUser();
            $result = $repairman_model->logins($username, $password);
            if(empty($result)){
                $this->error($repairman_model->getError());
            }else{
                $this->success('登录成功','project/index', null ,1);
            }
        }
        return $this->fetch();
    }

    public function logout()
    {
        $type = 0;
        $type = request()->get('type');
        if($type == 1){
            $repairman_model = new SysExpertLibrary();
            $users = $repairman_model->where('contract_weixinID',session('user_openid'))->find();
            $result = $users->isUpdate(true)->save(['contract_weixinID'=>'']);
        }else if($type == 2){
            $repair_model = new SysUser();
            $user = $repair_model->where('weixin_id',session('user_openid'))->find();
            $result = $user->isUpdate(true)->save(['weixin_id'=>'']);
        }else{
            $result = 0;
        }
        if($result){
            $this->success('退出成功');
        }else{
            $this->error('退出失败');
        }
    }

}
