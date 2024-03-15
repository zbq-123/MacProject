<?php
/**
 * Created by PhpStorm.
 * User: weitrun
 * Date: 2020/7/9
 * Time: 9:17
 */
namespace app\admin\model;

use think\Db;
use think\Model;
use util\listData;

class Orders extends Model
{
    public function base($query)
    {
        return $query->where('deleted', 0);
    }
    public function admin(){
        return $this->belongsTo('Admin','admin_id')->field('id,login_name,real_name');
    }
    public function campus(){
        return $this->belongsTo('Campus','campus_id')->field('id,name');
    }
    //关联商品表
    public function goods(){
        return $this->belongsTo('Goods','goods_ids')->field('id,name');
    }
    public function store(){
        return $this->belongsTo('Store','store_id')->field('id,name');
    }
    //用户表关联
    public function user(){
        return $this->belongsTo('User','user_id')->field('id,nickname');
    }
    //用户地址表
    public function address(){
        return $this->belongsTo('User','user_id')->field('id,delivery_name,delivery_address,');
    }


    //后台查询范围
    protected function scopeOrdersList($query){
        return $query->field('id,status7_time,order_number,store_id,goods_ids,user_id,store_name,address_id,count,box_type,box_price,box_name,manage_price,manage_ratio,
        pay_manage_ratio,store_price,convey_price,convey_name,total_price,campus_id,campus_name,delivery_name,delivery_phone,delivery_address,pay_status,status,
        goods_detail,order_source,pay_type,order_name,order_describe,wx_order_code,refund_order_code,wx_refund_order_code,cancel_time,remake,admin_id,create_time,
        update_time,deleted,today_number')
            ->order('update_time desc');
    }
    //获取列表数据
    public function getListData($maps, $searchFields, $limit, $page){

        if (!empty($searchFields['field_name']) && !empty($searchFields['field_content'])) {
            $key = $searchFields['field_name'];
            $value = $searchFields['field_content'];
            $maps[$key] = ['like', '%' . $value . '%'];
        }

        if(!empty($searchFields['store_id'])){
            $maps['store_id'] = $searchFields['store_id'];
        }

        if(!empty($searchFields['campus_id'])){
            $maps['campus_id'] = $searchFields['campus_id'];
        }

        if (session('admin.store_id') != null && session('admin.is_root') == 0) {
            $maps['store_id'] = session('admin.store_id');
        }
        if(session('admin.id')==48){//琼台超级管理员
            $maps['campus_id'] = 4;
        }
        $result = $this::scope('OrdersList')->with('admin,campus,goods')->where($maps)->limit($limit)->page($page)->select();
        $count = $this->where($maps)->count();

        /*foreach ($result as &$item){
            $user_order_count = Db::name('orders')
                ->where('store_id',session('admin.store_id'))
                ->where('user_id',$item['user_id'])
                ->where('deleted',0)
                ->count();
            $item['user_order_count'] =$user_order_count;
        }*/

        return new listData($result, $count);
    }
    //获取详情
    public function getTimeDetail($start,$end,$id){


        $result = $this

            ->where('deleted',0)->where('status',7)
            ->where('store_id',$id)
            ->where('status7_time','>',$start)->where('status7_time','<',$end)
            ->field('IFNULL(sum(total_price),0)/100 as total_price,IFNULL(sum(store_price),0)/100 as store_price,sum(manage_price)/100 as manage_price,sum(develop_price)/100 as develop_price,sum(pay_manage_price)/100 as pay_manage_price')
            ->group('store_id')
            ->find();

      



        return $result;
    }

    public function editStore($data){

        $data['admin_id'] = session('admin.id');
        $data['logo'] = $data['logo'];

             if (session('admin.store_id') !== null || session('admin.store_id') !== '') {
                    $data['store_id'] = session('admin.store_id');}

        if (!isset($data['store_id']) || empty($data['store_id'])) {
            $result = $this->validate(true)->allowField(true)->data($data)->save();
            if($result) {
                return true;
            }else {
                return false;
            }
        }else {
            $id = $data['store_id'];

            $portal = $this->where('id', $id)->find();

            $result = $portal->validate(true)->allowField(true)->except('id')->data($data)->save();
            if ($result) {
                return true;
            }else {
                return false;
            }
        }
    }
    public function editOrder($data){

        $data['admin_id'] = session('admin.id');
        $data['type'] = 2;
        $data['status'] = 7;
        $data['pay_status'] = 2;
        $data['total_price'] = $data['total_price']*100;
        $data['store_price'] = $data['store_price']*100;
        $data['box_price'] = $data['box_price']*100;
        $data['convey_price'] = $data['convey_price']*100;
        $data['pay_manage_price'] = $data['pay_manage_price']*100;
        $data['manage_price'] = $data['manage_price']*100;
        $data['develop_price'] = $data['develop_price']*100;

        if (!isset($data['orders_id']) || empty($data['orders_id'])) {
            $result = $this->validate(true)->allowField(true)->data($data)->save();
            if($result) {
                return true;
            }else {
                return false;
            }
        }else {
            $id = $data['orders_id'];

            $portal = $this->where('id', $id)->find();

            $result = $portal->validate(true)->allowField(true)->except('id')->data($data)->save();
            if ($result) {
                return true;
            }else {
                return false;
            }
        }
    }

    public function editStatus($id, $status)
    {
        if ($status == 3){ //    接单
            $result = $this->take_orders($id);
        }
        if ($status == 15){ //    拒单
            $result = $this->reject_orders($id);
        }
        if ($status == 7){ //    订单完成
            $result = $this->success_orders($id);
        }
        if ($status == 11){//    商家申请退款
            $result = $this->store_refund_orders($id);
        }
        if ($status == 9){//    商家同意客户申请退款
            $result = $this->agree_refunds_request($id);
        }
        if ($status == 10){//    商家拒绝客户申请退款
            $result = $this->refuse_refunds_request($id);
        }
        if ($status == 13){ //    商家重复申请退款
            $result = $this->agin_fefunds_request($id);
        }

        if ($result) {
            return true;
        } else {
            return false;
        }
    }


    //    接单
    public function take_orders($order_id){
        $admin = session('admin');
        $store_id = $admin['store_id'];
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

            return true;
        }

        return false;
    }

    //    拒单
    public function reject_orders($order_id){
        $orders = Db::name('orders')->where('id',$order_id)->where('deleted',0)->find();

        if (!$orders){
            return false;
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

                return true;
            }else{

                return false;
            }
        }else{

            return false;
        }
    }

    //    订单完成
    public function success_orders($order_id){
        
        if(empty($order_id)){
            return false;
            exit;
        }

        $admin = session('admin');
        // $store_id = $admin['store_id'];
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
            $store_id = $orders['store_id'];
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

            return true;
        }

        return false;
    }

    //    商家申请退款
    public function store_refund_orders($order_id){
        $orders = Db::name('orders')->where('id',$order_id)->where('deleted',0)->find();

        if (!$orders){

            return false;
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


                return true;
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

                return false;
            }
        }else{

            return false;
        }
    }

    //    商家同意客户申请退款
    public function agree_refunds_request($order_id){
        $orders = Db::name('orders')->where('id',$order_id)->where('deleted',0)->find();

        if (!$orders){

            return  false;
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
                 // 增加月卡次数
                if($orders['use_month_card']){
                    // Db::name('pay_test')->insert(['msg'=>'开始追加次数：']);
                    $card_mod = new MonthCard();
                    // 验证月卡有效性
                    $card_id = $orders['use_month_card'];
                    $card_info = $card_mod->getMonthCard(['id'=>$card_id],1);
                    if(!empty($card_info)){
                         // Db::name('pay_test')->insert(['msg'=>'查到订单：']);
                        // 增加
                        $card_res = $card_mod->addCount($card_id);
                        if($card_res){
                            // Db::name('pay_test')->insert(['msg'=>'追加成功：']);
                            // 增加日志
                            // $card_log = [
                            //     'card_id' => $card_id,
                            //     'status' => 2,
                            //     'order_id' => $orders['id'],
                            // ];
                            $card_mod->addLog($card_id,2,$orders['id']);
                        }else{
                            $is_true = false;
                            // Db::name('pay_test')->insert(['msg'=>'追加失败：']);
                        }
                    }
                }
                //存入订单状态改变时间表
                $orders_time_data = [];
                $orders_time_data['orders_id'] = $order_id;
                $orders_time_data['status'] = 9;
                $orders_time_data['status_time'] = date('Y-m-d H:i:s',time());
                $orders_time_data['admin_id'] = session('admin.id');
                $orders_time = Db::name('orders_times')->insert($orders_time_data);

                return  true;
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

                return  false;
            }
        }else{

            return  false;
        }
    }

    //    商家拒绝客户申请退款
    public function refuse_refunds_request($order_id){
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

            return true;
        }

        return false;
    }

    //    商家重复申请退款
    public function agin_fefunds_request($order_id){
        $orders = Db::name('orders')->where('id',$order_id)->where('deleted',0)->find();

        if (!$orders){

            return false;
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

                return true;
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

                return false;
            }
        }else{

            return false;
        }
    }

    //获取 海南师范大学食堂外卖销售平台按日汇总表
    public function getOrdersDayCount($store_list,$days)
    {
        $all_data = [];
        $datas = [];
        $count_data = [
            'order_count'=>0,
            'total_price'=>0,
            'rider_price'=>0,
            'develop_price'=>0,
            'discount_money'=>0,
            'month_card_money'=>0,
            'coupon_money'=>0,
            'manage_price'=>0,
            'pay_manage_price'=>0,
            'store_price'=>0
        ];
        $data = [
            'month_card_money'=>0,
            'coupon_money'=>0,
        ];
        foreach ($days['dates'] as $day){
            foreach ($store_list as $store){
                $data = Orders::where('deleted', 0)
                    ->where('store_id', $store['id'])
                    ->where('status', 7)
                    ->where('pay_status', 2)
                    ->where('status7_time', '>=', $day." 00:00:00")
                    ->where('status7_time', '<=', $day." 23:59:59")
                    ->field('COUNT(1) as order_count,
                    IFNULL(SUM(total_price),0) as total_price,
                IFNULL(SUM(develop_price),0) as develop_price,
                IFNULL(SUM(manage_price),0) as manage_price,
                IFNULL(SUM(pay_manage_price),0) as pay_manage_price,
                COUNT(use_month_card) as month_card_count,
                COUNT(use_coupon) as coupon_count,
                IFNULL(SUM(store_price),0) as store_price, 
                IFNULL(SUM(discount_money),0) as discount_money')
                    ->find()->toArray();

                $data['date'] = $day;
                $data['store_name'] = $store['name'];
                $data['rider_price'] = $data['order_count']*$store['delivery_price'];
                $data['discount_money']=$data['discount_money'];
                $data['month_card_money']=$data['month_card_count']*5;
                $data['coupon_money']    =$data['discount_money']-$data['month_card_count']*5;
                $datas[] = $data;

                $count_data['order_count'] += $data['order_count'];
                $count_data['total_price'] += $data['total_price'];
                $count_data['rider_price'] += $data['rider_price'];
                $count_data['month_card_money']+=$data['month_card_count']*5;
                $count_data['coupon_money']    +=$data['discount_money']-$data['month_card_count']*5;
                $count_data['discount_money'] += $data['discount_money'];
                $count_data['develop_price'] += $data['develop_price'];
                $count_data['manage_price'] += $data['manage_price'];
                $count_data['pay_manage_price'] += $data['pay_manage_price'];
                $count_data['store_price'] += $data['store_price'];
            }
        }


        $all_data['data'] = $datas;
        $all_data['count'] = $count_data;

        return $all_data;
    }


    //获取 海南师范大学食堂外卖销售平台按月汇总表
    public function getOrdersMonthCount($store_list,$months)
    {
        $all_data = [];
        $datas = [];
        $count_data = [
            'order_count'=>0,
            'total_price'=>0,
            'develop_price'=>0,
            'manage_price'=>0,
            'pay_manage_price'=>0,
            'discount_money'=>0,
            'month_card_money'=>0,
            'coupon_money'=>0,
            'store_price'=>0
        ];
        $data = [
            'month_card_money'=>0,
            'coupon_money'=>0,
        ];
        foreach ($months['dates'] as $month){
            foreach ($store_list as $store){
                $data = Orders::where('deleted', 0)
                    ->where('store_id', $store['id'])
                    ->where('status', 7)
                    ->where('pay_status', 2)
                    ->where('status7_time', '>=', $month['first'])
                    ->where('status7_time', '<=', $month['last'])
                    ->field('COUNT(1) as order_count,
                     IFNULL(SUM(total_price),0) as total_price,
                IFNULL(SUM(develop_price),0) as develop_price,
                IFNULL(SUM(manage_price),0) as manage_price,
                IFNULL(SUM(pay_manage_price),0) as pay_manage_price,
                COUNT(use_month_card) as month_card_count,
                COUNT(use_coupon) as coupon_count,
                IFNULL(SUM(store_price),0) as store_price, 
                IFNULL(SUM(discount_money),0) as discount_money')
                    ->find()->toArray();

                $data['date'] = $month['month'];
                $data['store_name'] = $store['name'];
                $data['discount_money']=$data['discount_money'];
                $data['month_card_money']=$data['month_card_count']*5;
                $data['coupon_money']    =$data['discount_money']-$data['month_card_count']*5;
                $datas[] = $data;

                $count_data['order_count'] += $data['order_count'];
                $count_data['total_price'] += $data['total_price'];
                $count_data['develop_price'] += $data['develop_price'];
                $count_data['month_card_money']+=$data['month_card_count']*5;
                $count_data['coupon_money']    +=$data['discount_money']-$data['month_card_count']*5;
                $count_data['discount_money'] += $data['discount_money'];
                $count_data['manage_price'] += $data['manage_price'];
                $count_data['pay_manage_price'] += $data['pay_manage_price'];
                $count_data['store_price'] += $data['store_price'];
            }
        }


        $all_data['data'] = $datas;
        $all_data['count'] = $count_data;

        return $all_data;
    }

    //获取 海南师范大学食堂外卖销售平台年度汇总表
    public function getOrdersYearCount($store_list,$year)
    {
        $all_data = [];
        $datas = [];
        $count_data = [
            'order_count'=>0,
            'total_price'=>0,
            'develop_price'=>0,
            'rider_price'=>0,
            'manage_price'=>0,
            'discount_money'=>0,
            'month_card_money'=>0,
            'coupon_money'=>0,
            'pay_manage_price'=>0,
            'store_price'=>0
        ];
        $data = [
            'month_card_money'=>0,
            'coupon_money'=>0,
        ];
        foreach ($store_list as $store){
            $data = Orders::where('deleted', 0)
                ->where('store_id', $store['id'])
                ->where('status', 7)
                ->where('pay_status', 2)
                ->where('status7_time', '>=', $year.'-01-01 00:00:00')
                ->where('status7_time', '<=', $year.'-12-31 23:59:59')
                ->field('COUNT(1) as order_count,
                  IFNULL(SUM(total_price),0) as total_price,
                IFNULL(SUM(develop_price),0) as develop_price,
                IFNULL(SUM(manage_price),0) as manage_price,
                IFNULL(SUM(pay_manage_price),0) as pay_manage_price,
                COUNT(use_month_card) as month_card_count,
                COUNT(use_coupon) as coupon_count,
                IFNULL(SUM(store_price),0) as store_price,
                IFNULL(SUM(discount_money),0) as discount_money')
                ->find()->toArray();

            $data['date'] = $year.'年';
            $data['store_name'] = $store['name'];
            $data['rider_price'] = $data['order_count']*$store['delivery_price'];
            $data['discount_money']=$data['discount_money'];
            $data['month_card_money']=$data['month_card_count']*5;
            $data['coupon_money']    =$data['discount_money']-$data['month_card_count']*5;
            $datas[] = $data;

            $count_data['order_count'] += $data['order_count'];
            $count_data['total_price'] += $data['total_price'];
            $count_data['rider_price'] += $data['rider_price'];
            $count_data['month_card_money']+=$data['month_card_count']*5;
            $count_data['coupon_money']    +=$data['discount_money']-$data['month_card_count']*5;
            $count_data['discount_money'] += $data['discount_money'];
            $count_data['develop_price'] += $data['develop_price'];
            $count_data['manage_price'] += $data['manage_price'];
            $count_data['pay_manage_price'] += $data['pay_manage_price'];
            $count_data['store_price'] += $data['store_price'];
        }


        $all_data['data'] = $datas;
        $all_data['count'] = $count_data;

        return $all_data;
    }

    //获取 海南师范大学食堂外卖销售平台季度汇总表
    public function getOrdersQuarterCount($store_list,$quarter,$select_time)
    {
        $all_data = [];
        $datas = [];
        $count_data = [
            'order_count'=>0,
            'total_price'=>0,
            'develop_price'=>0,
            'rider_price'=>0,
            'manage_price'=>0,
            'pay_manage_price'=>0,
            'discount_money'=>0,
            'month_card_money'=>0,
            'coupon_money'=>0,
            'store_price'=>0
        ];
        $data = [
            'month_card_money'=>0,
            'coupon_money'=>0,
        ];
        foreach ($store_list as $store){
            $data = Orders::where('deleted', 0)
                ->where('store_id', $store['id'])
                ->where('status', 7)
                ->where('pay_status', 2)
                ->where('status7_time', '>=', $quarter['first'])
                ->where('status7_time', '<=', $quarter['last'])
                ->field('COUNT(1) as order_count,
                   IFNULL(SUM(total_price),0) as total_price,
                IFNULL(SUM(develop_price),0) as develop_price,
                IFNULL(SUM(manage_price),0) as manage_price,
                IFNULL(SUM(pay_manage_price),0) as pay_manage_price,
                COUNT(use_month_card) as month_card_count,
                COUNT(use_coupon) as coupon_count,
                IFNULL(SUM(store_price),0) as store_price, 
                IFNULL(SUM(discount_money),0) as discount_money')
                ->find()->toArray();

            $data['date'] = $select_time;
            $data['store_name'] = $store['name'];
            $data['rider_price'] = $store['delivery_price']*$data['order_count'];
            $data['discount_money']=$data['discount_money'];
            $data['month_card_money']=$data['month_card_count']*5;
            $data['coupon_money']    =$data['discount_money']-$data['month_card_count']*5;
            $datas[] = $data;

            $count_data['order_count'] += $data['order_count'];
            $count_data['total_price'] += $data['total_price'];
            $count_data['develop_price'] += $data['develop_price'];
            $count_data['month_card_money']+=$data['month_card_count']*5;
            $count_data['coupon_money']    +=$data['discount_money']-$data['month_card_count']*5;
            $count_data['discount_money'] += $data['discount_money'];
            $count_data['rider_price'] += $data['rider_price'];
            $count_data['manage_price'] += $data['manage_price'];
            $count_data['pay_manage_price'] += $data['pay_manage_price'];
            $count_data['store_price'] += $data['store_price'];
        }


        $all_data['data'] = $datas;
        $all_data['count'] = $count_data;

        return $all_data;
    }

    //获取 海南师范大学食堂外卖销售平台半年汇总表
    public function getOrdersHalfYearCount($store_list,$quarter,$select_time)
    {
        $all_data = [];
        $datas = [];
        $count_data = [
            'order_count'=>0,
            'total_price'=>0,
            'develop_price'=>0,
            'rider_price'=>0,
            'discount_money'=>0,
            'month_card_money'=>0,
            'coupon_money'=>0,
            'manage_price'=>0,
            'pay_manage_price'=>0,
            'store_price'=>0
        ];
        $data = [
            'month_card_money'=>0,
            'coupon_money'=>0,
        ];
        foreach ($store_list as $store){
            $data = Orders::where('deleted', 0)
                ->where('store_id', $store['id'])
                ->where('status', 7)
                ->where('pay_status', 2)
                ->where('status7_time', '>=', $quarter['first'])
                ->where('status7_time', '<=', $quarter['last'])
                ->field('COUNT(1) as order_count,
                     IFNULL(SUM(total_price),0) as total_price,
                IFNULL(SUM(develop_price),0) as develop_price,
                IFNULL(SUM(manage_price),0) as manage_price,
                IFNULL(SUM(pay_manage_price),0) as pay_manage_price,
                COUNT(use_month_card) as month_card_count,
                COUNT(use_coupon) as coupon_count,
                IFNULL(SUM(store_price),0) as store_price, 
                IFNULL(SUM(discount_money),0) as discount_money')
                ->find()->toArray();

            $data['date'] = $select_time;
            $data['rider_price'] = $store['delivery_price']*$data['order_count'];
            $data['store_name'] = $store['name'];
            $data['discount_money']=$data['discount_money'];
            $data['month_card_money']=$data['month_card_count']*5;
            $data['coupon_money']    =$data['discount_money']-$data['month_card_count']*5;
            $datas[] = $data;

            $count_data['order_count'] += $data['order_count'];
            $count_data['total_price'] += $data['total_price'];
            $count_data['rider_price'] += $data['rider_price'];
            $count_data['month_card_money']+=$data['month_card_count']*5;
            $count_data['coupon_money']    +=$data['discount_money']-$data['month_card_count']*5;
            $count_data['discount_money'] += $data['discount_money'];
            $count_data['develop_price'] += $data['develop_price'];
            $count_data['manage_price'] += $data['manage_price'];
            $count_data['pay_manage_price'] += $data['pay_manage_price'];
            $count_data['store_price'] += $data['store_price'];
        }


        $all_data['data'] = $datas;
        $all_data['count'] = $count_data;

        return $all_data;
    }
    //统计使用优惠券的订单列表数据
    public function getListDatacoupon($maps, $searchFields, $limit, $page){
        
        if (!empty($searchFields['field_name']) && !empty($searchFields['field_content'])) {
            $key = $searchFields['field_name'];
            $value = $searchFields['field_content'];
            $maps[$key] = ['like', '%' . $value . '%'];
        }

        if(!empty($searchFields['select_time'])){
            $select_time_list = explode("~", $searchFields['select_time']);
            $start_time = $select_time_list[0]." 00:00:00";
            $end_time = $select_time_list[1]." 23:59:59";

            $maps['create_time'] = ['between',[$start_time,$end_time]];
        }

        if(!empty($searchFields['discount_money'])){

            $maps['discount_money'] = $searchFields['discount_money'];
        }

        if(!empty($searchFields['campus_id'])){

            $maps['campus_id'] = $searchFields['campus_id'];
        }
        if(!empty($searchFields['store_id'])){
            $maps['store_id'] = $searchFields['store_id'];
        }

        if(session('admin.is_root') == 0){
            $maps['store_id'] = session('admin.store_id');
        }
        $result = $this->with('user')->where($maps)->limit($limit)->page($page)->order('create_time desc')->select();
        if(!empty($result)){
            foreach($result as $k=>$v){
                if($v['discount_money']>5){//说明使用了月卡
                    $result[$k]['discount_money']=number_format($v['discount_money']-5,2);
                }
                $user_info=Db::name('user')->where('id',$v['user_id'])->field('nickname')->find();
                if(!empty($user_info)){
                    $result[$k]['user_name']=$user_info['nickname'];
                }else{
                    $result[$k]['user_name']='';
                }
            }
        }
        // $amount_arr = $this->where($maps)->field('SUM(discount_money) as amount')->select();
        $amount_arr = $this->field('discount_money ')->select();
        $amount=0;
        if(!empty($amount_arr)){
            foreach($result as $k=>$v){
                if($v['discount_money']>5){//说明使用了月卡
                    $result[$k]['discount_money']=$v['discount_money']-5;
                }
                $amount +=$result[$k]['discount_money'];
            }
        }
        $count = $this->where($maps)->count();
        //$amount = number_format($amount_arr[0]['amount'],2);
        $count_arr['amount'] = $amount;
        return new listData($result, $count,'200','获取成功',$count_arr);
    }
    //统计使用月卡的订单列表数据
    public function getListDatacard($maps, $searchFields, $limit, $page){

        if (!empty($searchFields['field_name']) && !empty($searchFields['field_content'])) {
            $key = $searchFields['field_name'];
            $value = $searchFields['field_content'];
            $maps[$key] = ['like', '%' . $value . '%'];
        }

        if(!empty($searchFields['select_time'])){
            $select_time_list = explode("~", $searchFields['select_time']);
            $start_time = $select_time_list[0]." 00:00:00";
            $end_time = $select_time_list[1]." 23:59:59";

            $maps['create_time'] = ['between',[$start_time,$end_time]];
        }


        if(!empty($searchFields['store_id'])){
            $maps['store_id'] = $searchFields['store_id'];
        }

        if(session('admin.is_root') == 0){
            $maps['store_id'] = session('admin.store_id');
        }
        $result = $this->where($maps)->limit($limit)->page($page)->order('create_time desc')->select();

        $amount_arr = $this->where($maps)->field('SUM(discount_money) as amount')->select();
        $amount=0;
        if(!empty($amount_arr)){
            foreach($result as $k=>$v){

                $amount +=5;
            }
        }
        $count = $this->where($maps)->count();

        //$amount = number_format($amount_arr[0]['amount'],2);
        //$count_arr['amount'] = $amount;
        $count_arr['amount'] =$amount;
        return new listData($result, $count,'','',$count_arr);
    }

}