<?php
/**
 * Created by PhpStorm.
 * User: wenyi
 * Date: 2020/7/13
 * Time: 13:24
 */

namespace app\store\controller;


use app\common\controller\StoreBase;
use think\Db;
use util\Ret;
use app\store\common\AppPrint;
use app\store\common\AppFePrint;

use app\admin\model\User;
use app\admin\model\UserAddress;

class Orders extends StoreBase
{
    /**
     * @Author: Wanglixian
     * @Date: 2020/7/31 16:23
     * @Description: 订单列表
     * @return mixed
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function orders(){
        $admin = session('admin');
        $store_id = $admin['store_id'];

        $store = Db::name('store')->where('id',$store_id)->find();
        $this->assign('store',$store);

        return $this->fetch();
    }

    /**
     * @Author: Wanglixian
     * @Date: 2020/8/5 11:21
     * @Description: 获取订单数据
     * @return Ret
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function get_orders(){
        $admin = session('admin');
        $store_id = $admin['store_id'];
        $page = request()->param('page');
        $status = request()->param('status');
        $create_time = request()->param('create_time');
        $todayStart = $create_time.' 00:00:00'; 
        $todayEnd = $create_time.' 23:59:59';  
        $today_number = request()->param('today_number');
        $where = [];
        if($today_number){
            $where = ['today_number'=>$today_number];
        }
        
        if (!$status){
            $status = '2,3,5,7,15';
        }
        $orders = Db::name('orders')
            ->where('store_id',$store_id)
            ->where('status','in',$status)
            ->where('deleted',0)
            ->where('create_time','between', $todayStart.','.$todayEnd)
            ->where($where)
            ->limit($this->limit)
            ->page($page)
            ->order('create_time desc')
            ->select();
            // echo Db::name('orders')->getLastSql();exit;
        $orders_count = Db::name('orders')
            ->where('store_id',$store_id)
            ->where('status','in',$status)
            ->where('deleted',0)
            ->where('create_time','between',$todayStart.','.$todayEnd)
            ->where($where)
            ->order('create_time desc')
            ->select();

        foreach ($orders as &$item){
            $item['total_price'] = fen_change_yuan($item['total_price']);
            $item['order_status']  = $this->order_status($item['status']);
            $user_order_count = Db::name('orders')
                ->where('store_id',$store_id)
                ->where('user_id',$item['user_id'])
                ->where('deleted',0)
//                ->where('create_time','<=',$item['create_time'])
                ->count();
            $item['user_order_count'] =$user_order_count;
        }

        $data['data'] = $orders;
        $data['pagenum'] = count($orders_count)/10;

        return new Ret($data);
    }

    /**
     * @Author: Wanglixian
     * @Date: 2020/7/31 16:23
     * @Description: 订单详情
     * @return mixed
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function orders_details(){
        $admin = session('admin');
        $store_id = $admin['store_id'];
        $order_id = request()->param('id');
        $orders = Db::name('orders')
            ->where('id',$order_id)
            ->where('deleted',0)
            ->find();
        $orders['total_price'] = fen_change_yuan($orders['total_price']);//订单总价
        $orders['store_price'] = fen_change_yuan($orders['store_price']);//店铺实际获得金额
        $orders['convey_price'] = fen_change_yuan($orders['convey_price']);//跑腿费
        $orders['box_price'] = fen_change_yuan($orders['box_price']);//餐盒费

        $orders['all_goods'] = [];//订单中全部商品信息
        $orders['all_count'] = 0;//订单中商品总数
        $orders['pay_type_text'] = '';//订单支付方式
        $orders['user_order_count'] = '';//订单支付方式
        $orders['order_status'] = '';//订单状态

        /********将订单中的全部商品信息获取********/
        $order_goods_detail =  explode('--onelist--', $orders['goods_detail']);//获取订单中的全部商品并以一商品为一数组
        for ($i=1;$i<count($order_goods_detail);$i++){
            $all_goods = explode('--twolist--', $order_goods_detail[$i]);//将订单中的每样商品信息组成数组
            for ($j=2;$j<count($all_goods);$j++){  //将订单中的每一种商品与对应商品信息并入 $orders[all_goods] 变量中
                switch ($j) {
                    case 2: $orders['all_goods'][$i-1]['name'] = $all_goods[$j];break;//商品名称
                    case 3: $orders['all_goods'][$i-1]['count'] = $all_goods[$j];break;//商品数量
                    case 4:
                        $orders['all_goods'][$i-1]['price'] = fen_change_yuan($all_goods[$j]);//商品单价
                        $orders['all_goods'][$i-1]['all_price'] = $orders['all_goods'][$i-1]['price']*$orders['all_goods'][$i-1]['count'];//商品小计
                        break;
                    case 5: $orders['all_goods'][$i-1]['unit'] = $all_goods[$j];break;//商品单位
                }
            }
            $orders['all_count'] = $orders['all_count']+$orders['all_goods'][$i-1]['count'];//订单中每样商品数量相加
        }
        /********将订单中的全部商品信息获取********/

        /********订单的支付方式********/
        switch ($orders['pay_type']) {
            case 1: $orders['pay_type_text'] = '微信支付';break;
            case 2: $orders['pay_type_text'] = '支付宝支付';break;
        }
        /********订单的支付方式********/

        /********客户下单次数********/
        $user_order_count = Db::name('orders')
            ->where('store_id',$store_id)
            ->where('user_id',$orders['user_id'])
            ->where('deleted',0)
            ->where('create_time','<=',$orders['create_time'])
            ->count();
        $orders['user_order_count'] =$user_order_count;
        /********客户下单次数********/

        /********订单状态********/
        $orders['order_status']  = $this->order_status($orders['status']);
        /********订单状态********/

        $this->assign('order',$orders);

        return $this->fetch();
    }

    /**
     * @Author: Wanglixian
     * @Date: 2020/8/3 10:25
     * @Description: 退款订单列表
     * @return mixed
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function refunds_orders(){
        $admin = session('admin');
        $store_id = $admin['store_id'];

        $store = Db::name('store')->where('id',$store_id)->find();
        $this->assign('store',$store);

        return $this->fetch();
    }

    /**
     * @Author: Wanglixian
     * @Date: 2020/8/5 15:26
     * @Description: 获取已处理完成的退款订单
     * @return Ret
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function get_refunds_orders(){
        $admin = session('admin');
        $store_id = $admin['store_id'];
        $page = request()->param('page');
        $create_time = request()->param('create_time');
        $todayStart = $create_time.' 00:00:00'; 
        $todayEnd = $create_time.' 23:59:59';  
        $today_number = request()->param('today_number');
        $where = [];
        if($today_number){
            $where = ['today_number'=>$today_number];
        }
        $orders = Db::name('orders')
            ->where('store_id',$store_id)
            ->where('status','in','8,9,10,12,13')
            ->where('create_time','between', $todayStart.','.$todayEnd)
            ->where($where)
            ->where('deleted',0)
            ->limit($this->limit)
            ->page($page)
            ->order('create_time desc')
            ->select();
        $orders_count = Db::name('orders')
            ->where('store_id',$store_id)
            ->where('status','in','8,9,10,12,13')
            ->where('create_time','between', $todayStart.','.$todayEnd)
            ->where($where)
            ->where('deleted',0)
            ->order('create_time desc')
            ->select();

        foreach ($orders as &$item){
            $item['total_price'] = fen_change_yuan($item['total_price']);
            $item['order_status']  = $this->order_status($item['status']);
            $user_order_count = Db::name('orders')
                ->where('store_id',$store_id)
                ->where('user_id',$item['user_id'])
                ->where('deleted',0)
                ->where('create_time','<=',$item['create_time'])
                ->count();
            $item['user_order_count'] =$user_order_count;
        }

        $data['data'] = $orders;
        $data['pagenum'] = count($orders_count)/10;

        return new Ret($data);
    }

    /**
     * @Author: Wanglixian
     * @Date: 2020/8/3 10:32
     * @Description: 订单退款申请列表
     * @return mixed
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function orders_refunds_request(){
        $admin = session('admin');
        $store_id = $admin['store_id'];
        $orders = Db::name('orders')
            ->where('store_id',$store_id)
            ->where('status','in','8,11')
            ->where('deleted',0)
            ->order('create_time desc')
            ->select();

        foreach ($orders as &$item){
            $item['total_price'] = fen_change_yuan($item['total_price']);
            $item['order_status']  = $this->order_status($item['status']);
            $user_order_count = Db::name('orders')
                ->where('store_id',$store_id)
                ->where('user_id',$item['user_id'])
                ->where('deleted',0)
                ->where('create_time','<=',$item['create_time'])
                ->count();
            $item['user_order_count'] =$user_order_count;
        }
        $this->assign('orders',$orders);
        $store = Db::name('store')->where('id',$store_id)->find();
        $this->assign('store',$store);

        return $this->fetch();
    }

    /**
     * @Author: Wanglixian
     * @Date: 2020/8/5 15:25
     * @Description:获取退款申请订单
     * @return Ret
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function get_orders_refunds_request(){
        $admin = session('admin');
        $store_id = $admin['store_id'];
        $page = request()->param('page');
        $create_time = request()->param('create_time');
        $todayStart = $create_time.' 00:00:00'; 
        $todayEnd = $create_time.' 23:59:59';  
        $today_number = request()->param('today_number');
        $where = [];
        if($today_number){
            $where = ['today_number'=>$today_number];
        }
        $orders = Db::name('orders')
            ->where('store_id',$store_id)
            ->where('status','in','8,13')
            ->where('create_time','between', $todayStart.','.$todayEnd)
            ->where($where)
            ->where('deleted',0)
            ->limit($this->limit)
            ->page($page)
            ->order('update_time desc')
            ->select();
        $orders_count = Db::name('orders')
            ->where('store_id',$store_id)
            ->where('status','in','8,13')
            ->where('create_time','between', $todayStart.','.$todayEnd)
            ->where($where)
            ->where('deleted',0)
            ->order('create_time desc')
            ->select();

        foreach ($orders as &$item){
            $item['total_price'] = fen_change_yuan($item['total_price']);
            $item['order_status']  = $this->order_status($item['status']);
            $user_order_count = Db::name('orders')
                ->where('store_id',$store_id)
                ->where('user_id',$item['user_id'])
                ->where('deleted',0)
                ->where('create_time','<=',$item['create_time'])
                ->count();
            $item['user_order_count'] =$user_order_count;
        }

        $data['data'] = $orders;
        $data['pagenum'] = count($orders_count)/10;

        return new Ret($data);
    }

    /**
     * @Author: Wanglixian
     * @Date: 2020/8/3 16:21
     * @Description: 接单
     * @return Ret
     * @throws \think\Exception
     * @throws \think\exception\PDOException
     */
    public function take_orders(){
        $admin = session('admin');
        $store_id = $admin['store_id'];
        $order_id = request()->post('id');
        $store = Db::name('store')->where('id',$store_id)->find();
        $cancel_time = date("Y-m-d H:i:s", strtotime(date("Y-m-d H:i:s") . " +".$store['order_cancel_time']." seconds"));
        $orders = Db::name('orders')
            ->where('id',$order_id)
            ->where('deleted',0)
            ->update(['status'=>3,'cancel_time'=>$cancel_time,'update_time'=>date('Y-m-d H:i:s',time())]);

        if ($orders){
            $orders = Db::name('orders')->where('id',$order_id)->where('deleted',0)->find();
            if($orders['pay_type'] == 1){
                //给用户发送模板消息 商家已接单
                $user = Db::name('user')
                    ->where('id',$orders['user_id'])
                    ->where('deleted',0)
                    ->where('disabled',0)
                    ->field('id,gz_openid')
                    ->find();
                if($user){
                    send_orders_status($user['gz_openid'],$orders['order_number'],$orders['total_price'],'微信支付','已接单');
                }
            }

            //存入订单状态改变时间表
            $orders_time_data = [];
            $orders_time_data['orders_id'] = $order_id;
            $orders_time_data['status'] = 3;
            $orders_time_data['status_time'] = date('Y-m-d H:i:s',time());
            $orders_time_data['admin_id'] = session('admin.id');
            $orders_time = Db::name('orders_times')->insert($orders_time_data);
        }

        return new Ret($orders);
    }

    /**
     * @Author: Wanglixian
     * @Date: 2020/8/3 16:21
     * @Description: 拒单
     * @return Ret
     * @throws \think\Exception
     * @throws \think\exception\PDOException
     */
    public function reject_orders(){
        $order_id = request()->post('id');

        $orders = Db::name('orders')->where('id',$order_id)->where('deleted',0)->find();
        $user = Db::name('user')->where('id',$orders['user_id'])->find();

        if (!$orders){
            $ret_data['type'] = 801;
            $ret_data['content'] = '该订单不支持退款！';
            return new Ret($ret_data);
        }

        $update_time = date("Y-m-d H:i:s");

        $update_data = [
            'status' => 15,
            'update_time' => $update_time,
            'refund_order_code' => build_order_refund_no(),
        ];

        //调用微信取消订单接口
        if($orders['pay_type'] == 1){
            $wx_refund_res = wx_refund($orders['wx_order_code'],$orders['order_number'],$orders['total_price'],$update_data['refund_order_code'],$update_data['status']);
            if($wx_refund_res){
                //给用户发送模板消息 退款已完成
                $user = Db::name('user')
                    ->where('id',$orders['user_id'])
                    ->where('deleted',0)
                    ->where('disabled',0)
                    ->field('id,gz_openid')
                    ->find();
                if($user){
                    send_refund_status($user['gz_openid'],$orders['order_number'],$orders['total_price'],$orders['create_time'],$update_data['status']);
                }

                //存入订单状态改变时间表
                $orders_time_data = [];
                $orders_time_data['orders_id'] = $order_id;
                $orders_time_data['status'] = 15;
                $orders_time_data['status_time'] = date('Y-m-d H:i:s',time());
                $orders_time_data['admin_id'] = session('admin.id');
                $orders_time = Db::name('orders_times')->insert($orders_time_data);

                //更新订单状态
                $orders = Db::name('orders')
                    ->where('id',$order_id)
                    ->where('deleted',0)
                    ->update(['status'=>15,'update_time'=>date('Y-m-d H:i:s',time())]);

                $ret_data['type'] = 800;
                $ret_data['content'] = '拒单给用户退款成功！';

                return new Ret($ret_data);
            }else{
                $ret_data['type'] = 801;
                $ret_data['content'] = '拒单给用户退款失败！';

                return new Ret($ret_data);
            }
        }else{
            $ret_data['type'] = 802;
            $ret_data['content'] = '该订单非微信支付，暂不支持退款！';

            return new Ret($ret_data);
        }
    }

    /**
     * @Author: Wanglixian
     * @Date: 2020/8/19 11:57
     * @Description: 订单打印
     * @return Ret
     */
    public function print_orders(){
        $order_id = request()->post('id');
        $admin = session('admin');
        $store_id = $admin['store_id'];

        // 查找打印机
        $print = Db::name('prints')->where('store_id',$store_id)->where('deleted',0)->field('type')->find();

        if($print['type'] == 1){
            $AppPrint = new AppPrint();
        }else{ //飞鹅
            $AppPrint = new AppFePrint();
        }
       
        
        $print_result = $AppPrint->get_store_print($order_id);

        return new Ret($print_result);

    }

    /**
     * @Author: Wanglixian
     * @Date: 2020/8/22 14:41
     * @Description: 订单完成
     * @return Ret
     * @throws \think\Exception
     * @throws \think\exception\PDOException
     */
    public function success_orders(){
        $admin = session('admin');
        $store_id = $admin['store_id'];
        $order_id = request()->post('id');
        $orders = Db::name('orders')
            ->where('id',$order_id)
            ->where('deleted',0)
            ->update(['status'=>7,'status7_time'=>date('Y-m-d H:i:s',time()),'update_time'=>date('Y-m-d H:i:s',time())]);

        if ($orders){
            $orders = Db::name('orders')->where('id',$order_id)->where('deleted',0)->find();
            // if($orders['pay_type'] == 1){
            //     //给用户发送模板消息 订单已完成
            //     $user = Db::name('user')
            //         ->where('id',$orders['user_id'])
            //         ->where('deleted',0)
            //         ->where('disabled',0)
            //         ->field('id,gz_openid')
            //         ->find();
            //     if($user){
            //         send_orders_status($user['gz_openid'],$orders['order_number'],$orders['total_price'],'微信支付','订单已完成');
            //     }
            // }

            //订单完成，将订单收入写入店铺总额与余额
            $store = Db::name('store')->where('id',$store_id)->where('deleted',0)->find();
            $orders_data = Db::name('orders')->where('id',$order_id)->where('deleted',0)->find();
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
            $store_amount_data['order_number'] = $orders_data['order_number'];
            $store_amount_records = Db::name('store_amount_records')->insert($store_amount_data);

            //存入订单状态改变时间表
            $orders_time_data = [];
            $orders_time_data['orders_id'] = $order_id;
            $orders_time_data['status'] = 7;
            $orders_time_data['status_time'] = date('Y-m-d H:i:s',time());
            $orders_time_data['admin_id'] = session('admin.id');
            $orders_time = Db::name('orders_times')->insert($orders_time_data);

            /********将订单中的全部商品信息获取********/
            $orders_goods_sale = Db::name('orders')
                ->where('id',$order_id)
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
        }
        $ret_data['type'] = 700;
        $ret_data['content'] = '订单完成！';

        return new Ret($ret_data);
    }

    /**
     * @Author: rice
     * @Date: 2021/4/15 14:41
     * @Description: 一键订单完成
     * @return Ret
     * @throws \think\Exception
     * @throws \think\exception\PDOException
     */
    public function auto_success_orders(){
        $admin = session('admin');
        $store_id = $admin['store_id'];

        $autoOrderList = Db::name('orders')
            ->where('deleted',0)
            ->where('pay_status',2)
            ->where('status','in','3,5')
            ->where('store_id',$store_id)
            ->select();
        if($autoOrderList){
            foreach ($autoOrderList as $autoOrder){
                $result = Db::name('orders')
                    ->where('id',$autoOrder['id'])
                    ->where('deleted',0)
                    ->where('pay_status',2)
                    ->where('status','in','3,5')
                    ->update(['status'=>7,'status7_time'=>date('Y-m-d H:i:s',time()),'update_time'=>date('Y-m-d H:i:s',time())]);
                if($result){
                    //订单完成，将订单收入写入店铺总额与余额
                    $store = Db::name('store')->where('id',$autoOrder['store_id'])->where('deleted',0)->find();
                    $orders_data = Db::name('orders')->where('id',$autoOrder['id'])->where('deleted',0)->find();
                    $inset_data = [];
                    $inset_data['balance'] = $store['balance']+$orders_data['store_price'];
                    $inset_data['revenue'] = $store['revenue']+$orders_data['store_price'];
                    $inset_data['update_time'] = date('Y-m-d H:i:s',time());
                    $store_result =  Db::name('store')->where('id',$autoOrder['store_id'])->update($inset_data);

                    //订单完成，将订单收入写入店铺金额明细表中
                    $store_amount_data = [];
                    $store_amount_data['store_id'] = $autoOrder['store_id'];
                    $store_amount_data['money'] = $orders_data['store_price'];
                    $store_amount_data['old_balance'] = $store['balance'];
                    $store_amount_data['now_balance'] = $store['balance']+$orders_data['store_price'];
                    $store_amount_data['status'] = 1;
                    $store_amount_data['admin_id'] = session('admin.id');
                    $store_amount_data['notes'] = '商品出售';
                    $store_amount_records = Db::name('store_amount_records')->insert($store_amount_data);

                    //存入订单状态改变时间表
                    $orders_time_data = [];
                    $orders_time_data['orders_id'] = $autoOrder['store_id'];
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
                }
            }
        }
        
        
        $ret_data['type'] = 700;
        $ret_data['content'] = '订单完成！';

        return new Ret($ret_data);
    }

    /**
     * @Author: Wanglixian
     * @Date: 2020/8/22 17:49
     * @Description: 商家申请退款
     * @return Ret
     * @throws \think\Exception
     * @throws \think\exception\PDOException
     */
    public function store_refund_orders(){
        $order_id = request()->post('id');
        $orders = Db::name('orders')->where('id',$order_id)->where('deleted',0)->find();
        $user = Db::name('user')->where('id',$orders['user_id'])->find();

        if (!$orders){
            $ret_data['type'] = 801;
            $ret_data['content'] = '该订单不支持退款！';
            return new Ret($ret_data);
        }

        //更新订单状态
        $orders_update = Db::name('orders')->where('id',$order_id)->where('deleted',0)->update(['status'=>11,'update_time'=>date('Y-m-d H:i:s',time())]);

        if ($orders_update){
            //存入订单状态改变时间表
            $orders_time_data = [];
            $orders_time_data['orders_id'] = $order_id;
            $orders_time_data['status'] = 11;
            $orders_time_data['status_time'] = date('Y-m-d H:i:s',time());
            $orders_time_data['admin_id'] = session('admin.id');
            $orders_time = Db::name('orders_times')->insert($orders_time_data);

            $update_time = date("Y-m-d H:i:s");

            $update_data = [
                'status' => 11,
                'update_time' => $update_time,
                'refund_order_code' => build_order_refund_no(),
            ];

            //给用户发送模板消息 商家发起申请退款
            $user = Db::name('user')
                ->where('id',$orders['user_id'])
                ->where('deleted',0)
                ->where('disabled',0)
                ->field('id,gz_openid')
                ->find();
            if($user){
                send_refund_status($user['gz_openid'],$orders['order_number'],$orders['total_price'],$orders['create_time'],$update_data['status']);
            }
        }


        //调用微信取消订单接口
        if($orders['pay_type'] == 1){
            $wx_refund_res = wx_refund($orders['wx_order_code'],$orders['order_number'],$orders['total_price'],$update_data['refund_order_code'],$update_data['status']);
            if($wx_refund_res){
                //存入订单状态改变时间表
                $orders_time_data = [];
                $orders_time_data['orders_id'] = $order_id;
                $orders_time_data['status'] = 12;
                $orders_time_data['status_time'] = date('Y-m-d H:i:s',time());
                $orders_time_data['admin_id'] = session('admin.id');
                $orders_time = Db::name('orders_times')->insert($orders_time_data);

                //更新订单状态
                $orders = Db::name('orders')->where('id',$order_id)->where('deleted',0)->update(['status'=>12,'update_time'=>date('Y-m-d H:i:s',time())]);

                //给用户发送模板消息 商家发起申请退款已退还金额
                $user = Db::name('user')
                    ->where('id',$orders['user_id'])
                    ->where('deleted',0)
                    ->where('disabled',0)
                    ->field('id,gz_openid')
                    ->find();
                if($user){
                    send_refund_status($user['gz_openid'],$orders['order_number'],$orders['total_price'],$orders['create_time'],12);
                }


                $ret_data['type'] = 800;
                $ret_data['content'] = '商家申请退款成功！';

                return new Ret($ret_data);
            }else{

                $update_refund_time = date("Y-m-d H:i:s");

                $update_refund_res = Db::name('orders')
                    ->where('id',$orders['id'])
                    ->where('pay_status',2)
                    ->where('deleted',0)
                    ->update([
                        'status' => 13,
                        'update_time' => $update_refund_time,
                    ]);

                if($update_refund_res){
                    //存入订单状态改变时间表
                    $orders_time_data = [];
                    $orders_time_data['orders_id'] = $order_id;
                    $orders_time_data['status'] = 13;
                    $orders_time_data['status_time'] = date('Y-m-d H:i:s',time());
                    $orders_time_data['admin_id'] = session('admin.id');
                    $orders_time = Db::name('orders_times')->insert($orders_time_data);
                }

                //给用户发送模板消息 商家发起退款申请失败
                $user = Db::name('user')
                    ->where('id',$orders['user_id'])
                    ->where('deleted',0)
                    ->where('disabled',0)
                    ->field('id,gz_openid')
                    ->find();
                if($user){
                    send_refund_status($user['gz_openid'],$orders['order_number'],$orders['total_price'],$orders['create_time'],13);
                }

                $ret_data['type'] = 801;
                $ret_data['content'] = '商家申请退款失败！';

                return new Ret($ret_data);
            }
        }else{
            $ret_data['type'] = 802;
            $ret_data['content'] = '该订单非微信支付，暂不支持退款！';

            return new Ret($ret_data);
        }

    }

    /**
     * @Author: Wanglixian
     * @Date: 2020/8/30 18:50
     * @Description: 商家同意客户申请退款
     * @return Ret
     * @throws \think\Exception
     * @throws \think\exception\PDOException
     */
    public function agree_refunds_request(){
        $order_id = request()->post('id');

        $orders = Db::name('orders')->where('id',$order_id)->where('deleted',0)->find();
        $user = Db::name('user')->where('id',$orders['user_id'])->find();

        if (!$orders){
            $ret_data['type'] = 801;
            $ret_data['content'] = '该订单不支持退款！';
            return new Ret($ret_data);
        }

        $update_time = date("Y-m-d H:i:s");

        $update_data = [
            'status' => 8,
            'update_time' => $update_time,
            'refund_order_code' => build_order_refund_no(),
        ];

        //给用户发送模板消息 退款申请
        $user = Db::name('user')
            ->where('id',$orders['user_id'])
            ->where('deleted',0)
            ->where('disabled',0)
            ->field('id,gz_openid')
            ->find();
        if($user){
            send_refund_status($user['gz_openid'],$orders['order_number'],$orders['total_price'],$orders['create_time'],$update_data['status']);
        }

        //调用微信取消订单接口
        if($orders['pay_type'] == 1){
            $wx_refund_res = wx_refund($orders['wx_order_code'],$orders['order_number'],$orders['total_price'],$update_data['refund_order_code'],$update_data['status']);

            if($wx_refund_res){
                //给用户发送模板消息 同意退款申请
                $user = Db::name('user')
                    ->where('id',$orders['user_id'])
                    ->where('deleted',0)
                    ->where('disabled',0)
                    ->field('id,gz_openid')
                    ->find();
                if($user){
                    send_refund_status($user['gz_openid'],$orders['order_number'],$orders['total_price'],$orders['create_time'],9);
                }

                $update_refund_time = date("Y-m-d H:i:s");
                $update_refund_res = Db::name('orders')
                    ->where('id',$orders['id'])
                    ->where('pay_status',2)
                    ->where('deleted',0)
                    ->update([
                        'status' => 9,
                        'update_time' => $update_refund_time,
                    ]);

                //存入订单状态改变时间表
                $orders_time_data = [];
                $orders_time_data['orders_id'] = $order_id;
                $orders_time_data['status'] = 9;
                $orders_time_data['status_time'] = date('Y-m-d H:i:s',time());
                $orders_time_data['admin_id'] = session('admin.id');
                $orders_time = Db::name('orders_times')->insert($orders_time_data);

                $ret_data['type'] = 800;
                $ret_data['content'] = '客户退款成功！';

                return new Ret($ret_data);
            }else{

                $update_refund_time = date("Y-m-d H:i:s");

                $update_refund_res = Db::name('orders')
                    ->where('id',$orders['id'])
                    ->where('pay_status',2)
                    ->where('deleted',0)
                    ->update([
                        'status' => 3,
                        'update_time' => $update_refund_time,
                    ]);

                if($update_refund_res){
                    //存入订单状态改变时间表
                    $orders_time_data = [];
                    $orders_time_data['orders_id'] = $order_id;
                    $orders_time_data['status'] = 10;
                    $orders_time_data['status_time'] = date('Y-m-d H:i:s',time());
                    $orders_time_data['admin_id'] = session('admin.id');
                    $orders_time = Db::name('orders_times')->insert($orders_time_data);
                    $orders_time_data['status'] = 3;
                    $orders_time = Db::name('orders_times')->insert($orders_time_data);
                }

                //给用户发送模板消息 退款申请失败
                $user = Db::name('user')
                    ->where('id',$orders['user_id'])
                    ->where('deleted',0)
                    ->where('disabled',0)
                    ->field('id,gz_openid')
                    ->find();
                if($user){
                    send_refund_status($user['gz_openid'],$orders['order_number'],$orders['total_price'],$orders['create_time'],10);
                }

                $ret_data['type'] = 801;
                $ret_data['content'] = '客户退款成功！';

                return new Ret($ret_data);
            }
        }else{
            $ret_data['type'] = 802;
            $ret_data['content'] = '该订单非微信支付，暂不支持退款！';

            return new Ret($ret_data);
        }
    }

    /**
     * @Author: Wanglixian
     * @Date: 2020/8/30 18:50
     * @Description: 商家拒绝客户申请退款
     * @return Ret
     * @throws \think\Exception
     * @throws \think\exception\PDOException
     */
    public function refuse_refunds_request(){
        $order_id = request()->post('id');
        $orders = Db::name('orders')
            ->where('id',$order_id)
            ->where('deleted',0)
            ->update(['status'=>3,'update_time'=>date('Y-m-d H:i:s',time())]);

        if ($orders){
            $orders = Db::name('orders')->where('id',$order_id)->where('deleted',0)->find();
            //给用户发送模板消息 商家拒绝退款申请
            $user = Db::name('user')
                ->where('id',$orders['user_id'])
                ->where('deleted',0)
                ->where('disabled',0)
                ->field('id,gz_openid')
                ->find();
            if($user){
                send_refund_status($user['gz_openid'],$orders['order_number'],$orders['total_price'],$orders['create_time'],10);
            }

            //存入订单状态改变时间表
            $orders_time_data = [];
            $orders_time_data['orders_id'] = $order_id;
            $orders_time_data['status'] = 10;
            $orders_time_data['status_time'] = date('Y-m-d H:i:s',time());
            $orders_time_data['admin_id'] = session('admin.id');
            $orders_time = Db::name('orders_times')->insert($orders_time_data);

            $orders_time_data['status'] = 3;
            $orders_time = Db::name('orders_times')->insert($orders_time_data);

            $ret_data['type'] = 200;
            $ret_data['content'] = '操作成功！';
        }else{
            $ret_data['type'] = 201;
            $ret_data['content'] = '操作失败！';
        }


        return new Ret($ret_data);
    }

    /**
     * @Author: Wanglixian
     * @Date: 2020/8/31 7:08
     * @Description: 商家重复申请退款
     * @return Ret
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @throws \think\exception\PDOException
     */
    public function agin_fefunds_request(){
        $order_id = request()->post('id');

        $orders = Db::name('orders')->where('id',$order_id)->where('deleted',0)->find();
        $user = Db::name('user')->where('id',$orders['user_id'])->find();

        if (!$orders){
            $ret_data['type'] = 801;
            $ret_data['content'] = '该订单不支持退款！';
            return new Ret($ret_data);
        }

        $update_time = date("Y-m-d H:i:s");

        $update_data = [
            'status' => 11,
            'update_time' => $update_time,
            'refund_order_code' => build_order_refund_no(),
        ];

        //存入订单状态改变时间表
        $orders_time_data = [];
        $orders_time_data['orders_id'] = $order_id;
        $orders_time_data['status'] = 11;
        $orders_time_data['status_time'] = date('Y-m-d H:i:s',time());
        $orders_time_data['admin_id'] = session('admin.id');
        $orders_time = Db::name('orders_times')->insert($orders_time_data);

        //给用户发送模板消息 退款申请
        $user = Db::name('user')
            ->where('id',$orders['user_id'])
            ->where('deleted',0)
            ->where('disabled',0)
            ->field('id,gz_openid')
            ->find();
        if($user){
            send_refund_status($user['gz_openid'],$orders['order_number'],$orders['total_price'],$orders['create_time'],$update_data['status']);
        }

        //调用微信取消订单接口
        if($orders['pay_type'] == 1){
            $wx_refund_res = wx_refund($orders['wx_order_code'],$orders['order_number'],$orders['total_price'],$update_data['refund_order_code'],$update_data['status']);

            if($wx_refund_res){
                //给用户发送模板消息 退款申请
                $user = Db::name('user')
                    ->where('id',$orders['user_id'])
                    ->where('deleted',0)
                    ->where('disabled',0)
                    ->field('id,gz_openid')
                    ->find();
                if($user){
                    send_refund_status($user['gz_openid'],$orders['order_number'],$orders['total_price'],$orders['create_time'],9);
                }

                $update_refund_time = date("Y-m-d H:i:s");
                $update_refund_res = Db::name('orders')
                    ->where('id',$orders['id'])
                    ->where('pay_status',2)
                    ->where('deleted',0)
                    ->update([
                        'status' => 12,
                        'update_time' => $update_refund_time,
                    ]);

                //存入订单状态改变时间表
                $orders_time_data = [];
                $orders_time_data['orders_id'] = $order_id;
                $orders_time_data['status'] = 12;
                $orders_time_data['status_time'] = date('Y-m-d H:i:s',time());
                $orders_time_data['admin_id'] = session('admin.id');
                $orders_time = Db::name('orders_times')->insert($orders_time_data);

                $ret_data['type'] = 800;
                $ret_data['content'] = '商家申请退款成功！';

                return new Ret($ret_data);
            }else{

                $update_refund_time = date("Y-m-d H:i:s");

                $update_refund_res = Db::name('orders')
                    ->where('id',$orders['id'])
                    ->where('pay_status',2)
                    ->where('deleted',0)
                    ->update([
                        'status' => 13,
                        'update_time' => $update_refund_time,
                    ]);

                if($update_refund_res){
                    //存入订单状态改变时间表
                    $orders_time_data = [];
                    $orders_time_data['orders_id'] = $order_id;
                    $orders_time_data['status'] = 13;
                    $orders_time_data['status_time'] = date('Y-m-d H:i:s',time());
                    $orders_time_data['admin_id'] = session('admin.id');
                    $orders_time = Db::name('orders_times')->insert($orders_time_data);
                }

                //给用户发送模板消息 退款申请失败
                $user = Db::name('user')
                    ->where('id',$orders['user_id'])
                    ->where('deleted',0)
                    ->where('disabled',0)
                    ->field('id,gz_openid')
                    ->find();
                if($user){
                    send_refund_status($user['gz_openid'],$orders['order_number'],$orders['total_price'],$orders['create_time'],13);
                }

                $ret_data['type'] = 801;
                $ret_data['content'] = '商家申请退款失败！';

                return new Ret($ret_data);
            }
        }else{
            $ret_data['type'] = 802;
            $ret_data['content'] = '该订单非微信支付，暂不支持退款！';

            return new Ret($ret_data);
        }
    }

    /**
     * @Author: Wanglixian
     * @Date: 2020/8/3 16:21
     * @Description: 删除订单
     * @return Ret
     * @throws \think\Exception
     * @throws \think\exception\PDOException
     */
    public function deleted_orders(){
        $order_id = request()->post('id');
        $orders = Db::name('orders')
            ->where('id',$order_id)
            ->where('deleted',0)
            ->update(['deleted'=>1,'update_time'=>date('Y-m-d H:i:s',time())]);

        return new Ret($orders);
    }

    /**
     * @Author: Wanglixian
     * @Date: 2020/8/3 10:53
     * @Description: 订单状态方法
     * @param $status
     * @return array
     */
    public function order_status($status){
        $order_status = [];
        switch ($status) {
            case 2:$order_status['text'] = '待接单';$order_status['style'] = 'color:#ff813d;';break;
            case 3:$order_status['text'] = '已接单';$order_status['style'] = 'color:#ff813d;';break;
//            case 4:$order_status['text'] = '配送中';$order_status['style'] = 'color:#ff813d;';break;
//            case 6:$order_status['text'] = '配送失败';$order_status['style'] = 'color: red;';break;
            case 7:$order_status['text'] = '已完成';$order_status['style'] = 'color:#009900;';break;
            case 15:$order_status['text'] = '拒接单';$order_status['style'] = 'color: red;';break;
            case 8:$order_status['text'] = '客户申请退款';$order_status['style'] = 'color:#ff813d;';break;
            case 9:$order_status['text'] = '客户退款成功';$order_status['style'] = 'color:#009900;';break;
            case 10:$order_status['text'] = '客户退款失败';$order_status['style'] = 'color: red;';break;
            case 11:$order_status['text'] = '商家申请退款';$order_status['style'] = 'color:#ff813d;';break;
            case 12:$order_status['text'] = '商家申请退款成功';$order_status['style'] = 'color:#009900;';break;
            case 13:$order_status['text'] = '商家申请退款失败';$order_status['style'] = 'color: red;';break;
            default:
                $order_status['text'] = '未知状态';$order_status['style'] = 'color: #ff813d;';break;
        }

        return $order_status;
    }

    /**
     * 统计客户在本店的消费次数
     * @return
     */
    public function order_user_stastistic()
    {
        $admin = session('admin');
        $store_id = $admin['store_id'];
        $orderSum = Db::name('orders')->where('store_id',$store_id)
            ->where('status',7)
            ->field('user_id, count(user_id) AS num')->group('user_id')
            ->select();
        // if (empty($orderSum)) {
        //     $orderSum = [
        //         ['user_id' => 1, 'num'=> 11],
        //         ['user_id' => 2, 'num'=> 4],
        //     ];
        // }

        $uModel = new User();
        $uaddModel = new UserAddress();        
        foreach ($orderSum as $key => $value) {
            $user = $uModel->find($value['user_id']);
            $useraddress = $uaddModel->find(['user_id'=>$value['user_id'],'is_default'=>0]);
            $orderSum[$key]['user_name'] = empty($user)? '???' : $user['nickname'];
            $orderSum[$key]['phone'] = empty($useraddress)? '???' : $useraddress['delivery_phone'];
        }

        $this->assign('orderSum',$orderSum);
        return $this->fetch();
    }

    //订单状态改变
    public function edit_status()
    {
        $statuss = request()->get('status');
        $id = request()->get('id');
        $admin = session('admin');
        $store_id = $admin['store_id'];

        $data['admin_id'] = $store_id;
        $column = new Orders();

        if($statuss == 9){
            // $result = $this->agree_refunds_request($id);
        }else{
            // $result = $this->refuse_refunds_request($id);
        }

        return new Ret($result);
    }

    
}