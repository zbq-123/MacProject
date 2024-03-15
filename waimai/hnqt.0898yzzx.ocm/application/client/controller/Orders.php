<?php
namespace app\client\controller;

use app\common\controller\ClientBase;
use app\common\model\Carousel;
use app\common\model\SysExpertLibrary;
use app\common\model\SysUser;
use app\admin\model\CouponUser;
use app\admin\model\MonthCard;
use think\Db;
use think\Log;
use think\Validate;
use think\View;
use util\Ret;
use util\RetList;

class Orders extends ClientBase
{
     // 月卡下单首页
    public function card(){
        
        // 是否购买了月卡
        $is_card = 0;
        $card_mod = new MonthCard();
        $card_info = $card_mod->getMonthCard(['user_id'=>session('user.id')]);
        if($card_info){
            $is_card = 1;
        }

        $this->assign('is_card',$is_card);
        return $this->fetch();
    }

    public function index()
    {
        if(request()->isAjax()){
            //获取用户订单列表
            $orders_list = Db::name('orders')
                ->where('user_id',session('user.id'))
                ->where('deleted',0)
                ->where('user_deleted',0)
                ->field('id,order_number,store_id,store_name,total_price,gender,cancel_time,status,create_time,count,goods_detail,goods_ids')
                ->order('create_time desc,id desc')
                ->limit($this->limit)
                ->page($this->page)
                ->select();

            $orders_count = Db::name('orders')
                ->where('user_id',session('user.id'))
                ->where('deleted',0)
                ->where('user_deleted',0)
                ->field('id,order_number,store_id,store_name,total_price,gender,cancel_time,status,create_time,count')
                ->order('create_time desc,id desc')
                ->count();

            foreach ($orders_list as &$orders){
                //获取第一个商品id图片
                $order_goods_detail =  explode('--onelist--', $orders['goods_detail']);//获取订单中的全部商品并以一商品为一数组

                if(isset($order_goods_detail[1])){
                    $all_goods = explode('--twolist--', $order_goods_detail[1]);//将订单中的每样商品信息组成数组
                    if($all_goods){
                        $orders['goods_id'] = isset($all_goods[0])?$all_goods[0]:0;
                        $orders['goods_name'] = isset($all_goods[2])?$all_goods[2]:'商品已下架';
                    }

                    $goods_image = Db::name('goods')
                        ->where('id',$orders['goods_id'])
                        ->where('deleted',0)
                        ->field('image')
                        ->find();

                    if(empty($goods_image)){
                        $orders['goods_image'] = "";
                    }else{
                        $orders['goods_image'] = $goods_image['image'];
                    }
                }else{
                    $orders['goods_id'] = 0;
                    $orders['goods_name'] = '该商品已下架';
                    $orders['goods_image'] = "";
                }

                $orders['status_name'] = order_list_status($orders['status']);

                //获取订单状态颜色
                // 订单状态【1-提交订单，2-已付款待接单，3-已确认订单，4-配送中
                //，5-配送成功，6-配送失败，7-完成订单，8-取消订单申请退款（下单后限制时间内可申请）
                //，9-用户申请退款成功，10-用户退款失败,11-商家申请退款，12-商家退款成功，13-商家退款失败，14-未付款取消订单，15-商家拒单】


                if($orders['status'] == 1 || $orders['status'] == 2 || $orders['status'] == 8 || $orders['status'] == 11){
                    //黄色
                    $orders['status_class'] = 'hsst-color-minor';
                }else if($orders['status'] == 3 || $orders['status'] == 7){
                    //绿色
                    $orders['status_class'] = 'hsst-color-main';
                }else{
                    //灰色
                    $orders['status_class'] = 'hsst-color-brown';
                }

                //该订单是否支持支付 该订单是否支持取消
                if($orders['status'] == 1){
                    $orders['is_pay'] = 1;
                    $orders['is_cancel'] = 1;
                }else{
                    $orders['is_pay'] = 0;
                    if($orders['status'] == 2 || ($orders['status'] == 3 && $orders['cancel_time'] > date("Y-m-d H:i:s"))){
                        $orders['is_cancel'] = 2;
                    }else if($orders['status'] == 3){
                        $orders['is_cancel'] = 3;
                    }else{
                        $orders['is_cancel'] = 0;
                    }
                }

                //订单是否支持删除
                if($orders['status'] == 1 || $orders['status'] == 7 || $orders['status'] == 9 || $orders['status'] == 12 || $orders['status'] == 14 || $orders['status'] == 15){
                    $orders['is_deleted'] = 1;
                }else{
                    $orders['is_deleted'] = 0;
                }

                //店铺是否允许退款
                $orders['is_refund'] = Db::name('store')
                    ->where('id',$orders['store_id'])
                    ->field('is_refund')
                    ->find()['is_refund'];
                }

            return new Ret(new RetList($orders_list, $orders_count,$this->page, $this->limit));
        }else{

            return $this->fetch();
        }
    }

    public function detail()
    {
        $order_number = input('order_number/s');
        $validate = new Validate([
            'order_number' => 'require',
        ]);

        if (!$validate->check(input('get.'))) {
            //$this->error($validate->getError());
            $this->error("参数错误，请重试");
        }

        //订单信息
        $orders = Db::name('orders')
            ->where('order_number',$order_number)
            ->where('user_id',session('user.id'))
            ->where('deleted',0)
            ->field('id,order_number,store_id,user_id,store_name,box_name,box_price,convey_name,convey_price,total_price,goods_detail,delivery_name,delivery_phone,delivery_address,pay_type,gender,cancel_time,status')
            ->find();

        if(empty($orders)){
            $this->error('该订单不存在');
        }

        $orders['gender'] = $orders['gender']==0?'先生':'女士';

        //店铺电话
        $orders['store_phone'] = Db::name('store')
            ->where('id',$orders['store_id'])
            ->field('phone')
            ->find()['phone'];

        //店铺是否允许退款
        $orders['is_refund'] = Db::name('store')
            ->where('id',$orders['store_id'])
            ->field('is_refund')
            ->find()['is_refund'];

        //客服电话
        $orders['service_phone'] = Db::name('sys_setting')
            ->where('deleted',0)
            ->field('service_phone')
            ->find()['service_phone'];

        /********订单的支付方式********/
        switch ($orders['pay_type']) {
            case 1: $orders['pay_type_text'] = '微信支付';break;
            case 2: $orders['pay_type_text'] = '支付宝支付';break;
            default: $orders['pay_type_text'] = '待支付';break;
        }
        /********订单的支付方式********/

        //获取订单状态时间
        $order_time = Db::name('orders_times')
            ->where('orders_id',$orders['id'])
            ->where('deleted',0)
            ->field('status,status_time')
            ->order('update_time asc')
            ->select();

        //获取时间状态对应中文
        foreach ($order_time as &$ot){
            $ot['status_name'] = order_status($ot['status']);
        }

        $orders['add_time'] = $order_time[0]['status_time'];
        $orders['now_status'] = $order_time[sizeof($order_time)-1]['status_name'];

        /********将订单中的全部商品信息获取********/
        $order_goods_detail =  explode('--onelist--', $orders['goods_detail']);//获取订单中的全部商品并以一商品为一数组
        for ($i=1;$i<count($order_goods_detail);$i++){
            $all_goods = explode('--twolist--', $order_goods_detail[$i]);//将订单中的每样商品信息组成数组
            for ($j=0;$j<count($all_goods);$j++){  //将订单中的每一种商品与对应商品信息并入 $orders[all_goods] 变量中
                switch ($j) {
                    case 0:
                        $orders['all_goods'][$i-1]['goods_id'] = $all_goods[$j];
                        break;//商品名称
                    case 2:
                        $orders['all_goods'][$i-1]['name'] = $all_goods[$j];
                        break;//商品名称
                    case 3:
                        $orders['all_goods'][$i-1]['count'] = $all_goods[$j];
                        break;//商品数量
                    case 4:
                        $orders['all_goods'][$i-1]['price'] = $all_goods[$j];//商品单价
                        $orders['all_goods'][$i-1]['all_price'] = $orders['all_goods'][$i-1]['price']*$orders['all_goods'][$i-1]['count'];//商品小计
                        break;
                }
            }

            $goods_image = Db::name('goods')
                ->where('id',$orders['all_goods'][$i-1]['goods_id'])
                ->where('deleted',0)
                ->field('image')
                ->find();
            if(empty($goods_image)){
                $orders['all_goods'][$i-1]['image'] = "";
            }else{
                $orders['all_goods'][$i-1]['image'] = $goods_image['image'];
            }
        }
        /********将订单中的全部商品信息获取********/

        //该订单是否支持支付 该订单是否支持取消 1-表示支持取消，2-表示支持立即退款,3-表示支持申请退款
        if($orders['status'] == 1){
            $orders['is_pay'] = 1;
            $orders['is_cancel'] = 1;
        }else{
            $orders['is_pay'] = 0;
            if($orders['status'] == 2 || ($orders['status'] == 3 && $orders['cancel_time'] > date("Y-m-d H:i:s"))){
                $orders['is_cancel'] = 2;
            }else if($orders['status'] == 3){
                $orders['is_cancel'] = 3;
            }else{
                $orders['is_cancel'] = 0;
            }
        }

        $this->assign('orders',$orders);
        $this->assign('order_time',$order_time);

        return $this->fetch();
    }

    public function submit()
    {   
        $store_id = input('store_id/d');
        $buy_goods = input('buy_goods/s');
        $buy_number = input('buy_number/s');
        $address_id = session('user.address_id');

        $validate = new Validate([
            'store_id' => 'require|number|>:0',
            'buy_goods' => 'require',
            'buy_number' => 'require',
        ]);

        if (!$validate->check(input('get.'))) {
            //$this->error($validate->getError());
            $this->error("参数错误，请重试");
        }

        //准备把参数回传，用于提交订单
        $this->assign('store_id',$store_id);
        $this->assign('buy_goods',$buy_goods);
        $this->assign('buy_number',$buy_number);

        $buy_goods = explode(",",$buy_goods);
        $buy_number = explode(",",$buy_number);

        if(count($buy_goods) != count($buy_number)){
            $this->error("数据异常，请重新提交订单",'store/index?store_id='.$store_id);
        }

        //1 获取用户收货地址信息，如果用户没有选择就去查找设置为默认的收货地址，如果都没有就创建一个空的
        if($address_id){
            $address = Db::name('user_address')
                ->where('user_id',session('user.id'))
                ->where('deleted',0)
                ->where('id',$address_id)
                ->field('id address_id,gender,delivery_name,delivery_phone,delivery_address')
                ->find();
        }else{
            $address = Db::name('user_address')
                ->where('user_id',session('user.id'))
                ->where('deleted',0)
                ->where('is_default',1)
                ->field('id address_id,gender,delivery_name,delivery_phone,delivery_address')
                ->find();
        }

        if(empty($address)){
            $address = [
                "address_id"=>0,
                "gender"=>'未选择收货地址',
                "delivery_name"=>'',
                "delivery_phone"=>'',
                "delivery_address"=>'请选择收货地址'
            ];
        }else{
            $address['gender'] = $address['gender']==0?'先生':'女士';
        }

        //2 获取下单商品信息
        $store = Db::name('store')
            ->where('id',$store_id)
            ->where('deleted',0)
            ->field("id store_id,name,phone,address,detail,campus_id,min_price,start_time1,end_time1,start_time2,end_time2,start_time3,end_time3,logo,delivery_price,delivery_name,box_type,box_price,box_name,status,notice")
            ->find();

        if(empty($store)){
            $this->error('未找到店铺相关信息');
        }

        $campus = Db::name('campus')
            ->where('id',$store['campus_id'])
            ->where('deleted',0)
            ->field('name')
            ->find();

        if(empty($campus)){
            $this->error('该校区暂不支持下单');
        }

        //所属校区
        $store['campus_name'] = $campus['name'];

        //计算营业状态
        $now_time = date('H:i:s', time());
        if($store['status'] == 1 && ($store['start_time1'] < $now_time && $store['end_time1'] > $now_time)){
            $store['open'] = 1;
        }else if($store['status'] == 1 && ($store['start_time2'] < $now_time && $store['end_time2'] > $now_time)){
            $store['open'] = 1;
        }else if($store['status'] == 1 && ($store['start_time3'] < $now_time && $store['end_time3'] > $now_time)){
            $store['open'] = 1;
        }else{
            $store['open'] = 0;
        }

        if($store['open'] != 1){
            $this->error('该店铺休息中');
        }

        //3 获取购买商品信息 计算金额
        $buy_info = [];
        $box_price = 0;
        $all_price = 0;
        foreach ($buy_goods as $key=>$goods){
            if($buy_number[$key] <= 0){
                $this->error('数据错误，请重试');
            }
            $buy_info[$key] = Db::name('goods')
                ->where('id',$goods)
                ->where('status',1)
                ->where('deleted',0)
                ->field('id goods_id,image,name,price,unit')
                ->find();

            if(empty($buy_info[$key])){
                $this->error('部分商品已下架，请刷新页面重新下单');
            }

            $buy_info[$key]['count'] = $buy_number[$key];
            $buy_info[$key]['price_count'] = $buy_info[$key]['price'] * $buy_number[$key];

            if($store['box_type'] == 1){
                $box_price = $store['box_price'];
            }else{
                $box_price += $store['box_price']*$buy_info[$key]['count'];
            }

            $all_price += $buy_info[$key]['price_count'];
        }
        $goods_price = $all_price;//商品总价格 RICE
        $all_price = $all_price + $box_price + $store['delivery_price'];

        // rice add 2020-10-29 获取用户优惠券
        $user_id = session('user.id');
        $coupons = $this->get_coupon($user_id);
        $data = [];
        // 有限期,订单总额是否满足条件
        if($coupons){
            $date = date('Y-m-d H:i:s',time());
            foreach ($coupons as $key => $value) {
                if($store['campus_id'] != $value['coupon']['campus_id'] && $value['coupon']['campus_id']>0){
                    unset($coupons[$key]);
                    continue;
                }
                if($value['coupon']['start_time']<$date && $date<$value['coupon']['end_time'] && ($all_price/100)>=$value['coupon']['full_money'] && $value['coupon']['status']==1 && $value['coupon']['deleted']==0 && $value['coupon']['type']==1){
                    $data[$key]['coupon_id'] = $value['coupon']['id'];
                    $data[$key]['discount_money'] = $value['coupon']['discount_money'];
                }
            }
        }

        // 查询月卡
        $month_card_mod = new MonthCard();
        $card_maps['user_id'] = session('user.id');
        $card = $month_card_mod->getMonthCard($card_maps);
        $this->assign('card',$card);

        // 在线调试
        $debug = false;
        if(session('user.id')==1||session('user.id')==11){
            $debug = true;
        }
        $this->assign('debug',$debug);
        $this->assign('coupons',$data);
        $this->assign('lbt',0);

        $this->assign('address',$address);
        $this->assign('store',$store);
        $this->assign('goods',$buy_info);
        $this->assign('box_price',$box_price);
        $this->assign('all_price',$all_price);

        $this->assign('address_id',$address['address_id']);


        return $this->fetch();
    }

    // 获取优惠券  rice add 2020-10-29
    public function get_coupon($user_id = ''){
        
        $coupons = [];
        if(request()->isPost()){
            $user_id = input('user_id');
        }

        if($user_id){
            $coupon_mod = new CouponUser();
            $coupons = $coupon_mod->get_coupon($user_id);

        }

        if(request()->isPost()){
            return new Ret($coupons);;
        }else{
            return $coupons;
        }

    }

    //取消订单
    public function cancel_orders()
    {
        if(!request()->isPost()){
            abort(10003,'请求失败');
        }
        $order_number = input('order_number/s');
        $validate = new Validate([
            'order_number' => 'require',
        ]);

        if (!$validate->check(input('post.'))) {
            abort(10003,'参数错误，请重试');
        }

        //订单信息
        $orders = Db::name('orders')
            ->where('order_number',$order_number)
            ->where('user_id',session('user.id'))
            ->where('status',1)
            ->where('pay_status',1)
            ->where('deleted',0)
            ->field('id,cancel_time,status,user_deleted,order_number,store_name,pay_type,total_price')
            ->find();

        if(empty($orders)){
            abort(10003,'该订单不支持取消');
        }

        if($orders['user_deleted'] == 1){
            abort(10003,'操作失败，该订单已被删除');
        }

        $update_time = date("Y-m-d H:i:s");

        //如果用户没有付款，直接修改订单状态为取消
        $cancel_res = Db::name('orders')
            ->where('order_number',$order_number)
            ->where('user_id',session('user.id'))
            ->where('deleted',0)
            ->update(['status'=>14,'update_time'=>$update_time]);

        if($cancel_res){
            //订单生成成功，添加订单状态时间表
            $orders_times = [];
            $orders_times['orders_id'] = $orders['id'];
            $orders_times['status'] = 14;
            $orders_times['status_time'] = $update_time;

            $insert_orders_times = Db::name('orders_times')->insert($orders_times);
            if(!$insert_orders_times){
                Log::error("[ordersTimesError]订单时间插入失败,info=".json_encode($orders_times));
            }

             // 增加月卡次数
            // if($orders['use_month_card']){
            //      Db::name('pay_test')->insert(['msg'=>'开始追加次数：']);
            //     $card_mod = new MonthCard();
            //     // 验证月卡有效性
            //     $card_id = $orders['use_month_card'];
            //     $card_info = $card_mod->getMonthCard(['id'=>$card_id],1);
            //     if(!empty($card_info)){
            //           Db::name('pay_test')->insert(['msg'=>'查到订单：']);
            //         // 增加
            //         $card_res = $card_mod->addCount($card_id);
            //         if($card_res){
            //             Db::name('pay_test')->insert(['msg'=>'追加成功：']);
            //             // 增加日志
            //             // $card_log = [
            //             //     'card_id' => $card_id,
            //             //     'status' => 2,
            //             //     'order_id' => $orders['id'],
            //             // ];
            //             $card_mod->addLog($card_id,2,$orders['id']);
            //         }else{
            //             Db::name('pay_test')->insert(['msg'=>'追加失败：']);
            //         }
            //     }
            // }

            //给用户发送模板消息
            send_orders_status(session('user.openid'),$orders['order_number'],$orders['total_price'],'待支付','已取消');

            return new Ret();
        }else{
            abort(10003,'订单状态更改失败');
        }

    }

    //删除订单
    public function deleted_orders()
    {
        if(!request()->isPost()){
            abort(10003,'请求失败');
        }
        $order_number = input('order_number/s');
        $validate = new Validate([
            'order_number' => 'require',
        ]);

        if (!$validate->check(input('post.'))) {
            abort(10003,'参数错误，请重试');
        }

        //订单信息
        $orders = Db::name('orders')
            ->where('order_number',$order_number)
            ->where('user_id',session('user.id'))
            ->where('deleted',0)
            ->where('user_deleted',0)
            ->field('id,cancel_time,status')
            ->find();

        if(empty($orders)){
            abort(10003,'该订单不存在');
        }

        //订单是否支持删除
        if($orders['status'] == 1 || $orders['status'] == 7 || $orders['status'] == 9 || $orders['status'] == 12 || $orders['status'] == 14 || $orders['status'] == 15){
            $is_deleted = 1;
        }else{
            $is_deleted = 0;
        }

        if($is_deleted == 0){
            abort(10003,'该订单进行中，暂不支持删除');
        }
        $update_time = date("Y-m-d H:i:s");

        $deleted_res = Db::name('orders')
            ->where('order_number',$order_number)
            ->where('user_id',session('user.id'))
            ->where('deleted',0)
            ->where('user_deleted',0)
            ->update(['user_deleted'=>1,'update_time'=>$update_time]);

        if($deleted_res){
            return new Ret();
        }else{
            abort(10003,'订单删除失败');
        }

    }

    //申请退款
    public function apply_refund()
    {
        if(!request()->isPost()){
            abort(10003,'请求失败');
        }
        $order_number = input('order_number/s');
        $validate = new Validate([
            'order_number' => 'require',
        ]);

        if (!$validate->check(input('post.'))) {
            abort(10003,'参数错误，请重试');
        }

        //订单信息
        $orders = Db::name('orders')
            ->where('order_number',$order_number)
            ->where('user_id',session('user.id'))
            ->where('deleted',0)
            ->where('status',3)
            ->where('pay_status',2)
            ->where('cancel_time','<=',date("Y-m-d H:i:s"))
            ->field('id,cancel_time,status,user_deleted,order_number,store_name,pay_type,total_price,create_time,store_id')
            ->find();

        if(empty($orders)){
            abort(10003,'该订单暂不支持申请退款');
        }

        if($orders['user_deleted'] == 1){
            abort(10003,'操作失败，该订单已被删除');
        }

        $update_time = date("Y-m-d H:i:s");

        //修改状态为申请退款
        $cancel_res = Db::name('orders')
            ->where('order_number',$order_number)
            ->where('user_id',session('user.id'))
            ->where('pay_status',2)
            ->where('status',3)
            ->where('deleted',0)
            ->update(['status'=>8,'update_time'=>$update_time]);
        $store_id = $orders['store_id'];

        switch ($orders){
            case 1:
                $pay_type = '微信支付';
                break;
            case 2:
                $pay_type = '支付宝支付';
                break;
            default:
                $pay_type = '待支付';
                break;
        }

        if($cancel_res){
            //订单生成成功，添加订单状态时间表
            $orders_times = [];
            $orders_times['orders_id'] = $orders['id'];
            $orders_times['status'] = 8;
            $orders_times['status_time'] = $update_time;

            $insert_orders_times = Db::name('orders_times')->insert($orders_times);
            if(!$insert_orders_times){
                Log::error("[ordersTimesError]订单时间插入失败,info=".json_encode($orders_times));
            }

            //给用户发送模板消息 退款申请已提交
            send_refund_status(session('user.openid'),$orders['order_number'],$orders['total_price'],$orders['create_time'],$orders['status']);
            send_refund_status('NJ1Hq2tyh21w395hgbBGolkHs',$orders['order_number'],$orders['total_price'],$orders['create_time'],$orders['status']);

            // //给卖家发送模板消息 退款申请已提交
            // $store_info = Db::name('store')
            //     ->where('store_id',$store_id)
            //     ->field('user_open_id')
            //     ->find();
            // if($store_info['user_open_id']){
            //     send_refund_status($store_info['user_open_id'],$orders['order_number'],$orders['total_price'],$orders['create_time'],$orders['status']);
            // }
            
            return new Ret();
        }else{
            abort(10003,'订单状态更改失败');
        }

    }
}
