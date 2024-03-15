<?php
namespace app\admin\model;

use think\Model;

class Admin extends Model
{
    protected $updateTime = 'last_login_time';

    public function group()
    {
        return $this->belongsToMany('AuthGroup', 'AuthGroupAccess','group_id', 'uid');
    }

    /**
     * 登录
     * @param $username
     * @param $password
     * @return array|bool
     */
    public function login($username, $password)
    {
        $admin = $this->where([
            'login_name'    =>  $username,
            'disabled'  =>  '0'
        ])->find();
        if(false == $admin){
            $this->error = '用户不存在或被禁用';
            return false;
        }
        if(md5($password) == $admin->password){
            $res = $admin->hidden(['password'])->toArray();
            $admin->last_login_time = date('y-m-d h:i:s',time());
            $admin->save();
            return $res;
        }else{
            $this->error = '用户名\密码错误';
            return false;
        }
    }
}