<?php
/**
 * Created by PhpStorm.
 * User: WangZiyong
 * Date: 2019/10/1
 * Time: 5:54
 */

namespace app\command;

use think\console\Command;
use think\console\Input;
use think\console\Output;
use think\Db;
use think\Log;

class Auto extends Command
{


    protected function configure()
    {
        $this->setName('take-orders')->setDescription('每日定时执行轮询自动完成订单已确认订单一天后的订单');
    }

    protected function execute(Input $input, Output $output)
    {
        $autoOrderList = [];
        Log::write(date('Y-m-d H:i:s',time()).'自动收货开始执行'.PHP_EOL);
        $autoOrderList = Db::name('orders')
            ->where('deleted',0)
            ->where('pay_status',2)
            ->where('status','in','3,5')
            ->where('update_time','<',date("Y-m-d 01:00:00",time()))
            ->field('id,store_id,order_number')
            ->select();
            if(!empty($autoOrderList)){
                foreach ($autoOrderList as $autoOrder){
                    $result = Db::name('orders')
                        ->where('id',$autoOrder['id'])
                        ->where('deleted',0)
                        ->where('pay_status',2)
                        ->where('status','in','3,5')
                        ->where('update_time','<',date("Y-m-d 01:00:00",time()))
                        ->update(['status'=>7,'status7_time'=>date('Y-m-d H:i:s',time()),'update_time'=>date('Y-m-d H:i:s',time())]);
                    if($result){
                        //订单完成，将订单收入写入店铺总额与余额
                        $store_id = $autoOrder['store_id'];
                        $store = Db::name('store')->where('id',$store_id)->where('deleted',0)->find();
                        $orders_data = Db::name('orders')->where('id',$autoOrder['id'])->where('deleted',0)->find();
                        $inset_data = [];
                        $inset_data['balance'] = $store['balance']+$orders_data['store_price'];
                        $inset_data['revenue'] = $store['revenue']+$orders_data['store_price'];
                        $inset_data['update_time'] = date('Y-m-d H:i:s',time());
                        $store_result =  Db::name('store')->where('id',$store_id)->update($inset_data);

                        //订单完成，将订单收入写入店铺金额明细表中
                        $store_amount_data = [];
                        $store_amount_data['store_id'] = $store_id;
                        $store_amount_data['money'] = $orders_data['store_price'];
                        $store_amount_data['old_balance'] = $store['balance'];
                        $store_amount_data['now_balance'] = $store['balance']+$orders_data['store_price'];
                        $store_amount_data['status'] = 1;
                        $store_amount_data['admin_id'] = session('admin.id');
                        $store_amount_data['notes'] = '商品出售';
                        $store_amount_records = Db::name('store_amount_records')->insert($store_amount_data);

                        //存入订单状态改变时间表
                        $orders_time_data = [];
                        $orders_time_data['orders_id'] = $store_id;
                        $orders_time_data['status'] = 7;
                        $orders_time_data['status_time'] = date('Y-m-d H:i:s',time());
                        $orders_time_data['admin_id'] = session('admin.id');
                        $orders_time = Db::name('orders_times')->insert($orders_time_data);

                        /********将订单中的全部商品信息获取********/
                        $orders_goods_sale = Db::name('orders')
                            ->where('id',$autoOrder['id'])
                            ->where('deleted',0)
                            ->find();
                        $order_goods_detail =  explode('--onelist--', $orders_goods_sale['goods_detail']);//获取订单中的全部商品并以一商品为一数组
                        for ($i=1;$i<count($order_goods_detail);$i++){
                            $all_goods = explode('--twolist--', $order_goods_detail[$i]);//将订单中的每样商品信息组成数组
                            $goods_id = '';
                            $sale = 0;
                            for ($j=0;$j<count($all_goods);$j++){  //统计每样商品数量
                                switch ($j) {
                                    case 0: $goods_id = $all_goods[$j];break;//商品数量
                                    case 3:$sale = $all_goods[$j];break;//商品数量
                                }
                            }
                            $goods = Db::name('goods')->where('id',$goods_id)->find();
                            $sale = $sale+$goods['sale'];
                            $goods = Db::name('goods')
                                ->where('id',$goods_id)
                                ->update(['sale'=>$sale,'update_time'=>date('Y-m-d H:i:s',time())]);
                        }
                        /********将订单中的全部商品信息获取********/

                        $log = '自动完成订单->订单编号:'.$autoOrder['order_number']." 于 ".date('Y-m-d H:i:s',time())." 自动完成订单成功".PHP_EOL;
                        Log::write($log);
                    }else{
                        $log = '自动完成订单->订单编号:'.$autoOrder['order_number']+" 于 ".date('Y-m-d H:i:s',time())." 自动完成订单失败".PHP_EOL;
                        Log::write($log);
                    }
                }
            }
        
        Log::write(date('Y-m-d H:i:s',time()).'自动收货执行完毕'.PHP_EOL);


    }
}