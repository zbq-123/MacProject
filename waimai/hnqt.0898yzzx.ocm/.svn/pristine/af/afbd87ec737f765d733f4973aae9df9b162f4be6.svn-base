<?php

/**
 * 是否登录
 * @return boolean
 */
function isLogin()
{
    return session('admin') == null ? false : true;
}

/**
 * 退出登录
 *
 * @return boolean
 */
function logout()
{
    session('user', null);
    cookie('keeplogin', null);
    return true;
}

/**
 * 检查用户是否已注册
 * @param $phone
 * @return bool
 */
function existUser($phone)
{
    $user = User::get(['login_name' => $phone]);
    if($user){
        return true;
    }else{
        return false;
    }
}

/**
 * @Author: Wanglixian
 * @Date: 2020/7/25 9:55
 * @Description: 钱--分转换元
 * @param $fen
 */
function fen_change_yuan($fen){
    $yuan = $fen/100;

    return $yuan;
}

function yuan_change_fen($yuan){
    $fen = $yuan*100;

    return $fen;
}
