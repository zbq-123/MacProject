<?php
/**
 * Created by PhpStorm.
 * User: wenyi
 * Date: 2020/7/9
 * Time: 15:16
 */

namespace app\store\controller;
use think\Db;
use util\Ret;
use app\common\controller\StoreBase;

class Index extends StoreBase
{
    public function index(){

    	return $this->fetch();
    }

    /**
     * 获取最新退款订单记录
     * @return
     */
    public function get_refunds_count()
    {
        $admin = session('admin');
        $store_id = $admin['store_id'];
        $max_id = request()->param('id');

        $orders_count = Db::name('orders')
        ->where('store_id',$store_id)
        ->where('status','in','9')
        ->where('id','>',$max_id)
        ->where('deleted',0)
        ->order('id desc')
        ->select();
      
        $data = [];
        if($orders_count){
           $data['count'] = sizeof($orders_count); 
           $data['max_id'] = $orders_count[0]['id'];
        }
        
        return new Ret($data);
    }

}