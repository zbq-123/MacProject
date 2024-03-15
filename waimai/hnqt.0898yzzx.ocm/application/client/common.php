<?php

use app\lib\event\PushEvent;

/**
 * 是否登录
 * @return boolean
 */
function isLogin()
{
    return session('user') == null ? false : true;
}
/**
 * 退出登录
 *
 * @return boolean
 */
function logout()
{
    session('user', null);
    return true;
}

/*
 * 推送订单消息给后台
 * */
function push_orders_msg($store_user_id,$order_number)

{
    $string = $order_number;//推送的消息，实际开发中换成根据uid查的业务值(订单号)
    $push = new PushEvent();
    $push->setUser($store_user_id)->setContent($string)->push();
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
