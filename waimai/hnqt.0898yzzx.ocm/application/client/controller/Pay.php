<?php
/**
 * Created by PhpStorm.
 * User: WangZiyong
 * Date: 2020/8/29
 * Time: 5:20
 */
namespace app\client\controller;

use app\common\controller\ClientBase;
use app\admin\model\Coupon;
use app\admin\model\MonthCard;
use think\Db;
use think\Log;
use think\Validate;
use util\Ret;
use util\WxPay;
use think\Config;
use alipay\Wappay;

class Pay extends ClientBase {

    /*
     * 用户确认订单，生成订单信息，返回支付信息
     * */
    public function wxpay_goods()
    {
        if(!request()->isPost()){
            abort(10003,'下单失败');
        }

        $store_id = input('post.store_id/d');
        $buy_goods = input('post.buy_goods/s');
        $buy_number = input('post.buy_number/s');
        $address_id = input('post.address_id/d');
        $remake = input('post.remake');
        $use_coupon = input('post.use_coupon'); //rice 优惠券
        $use_month_card = input('post.use_month_card'); //rice 月卡

        $validate = new Validate([
            'store_id' => 'require|number|>:0',
            'buy_goods' => 'require',
            'buy_number' => 'require',
            'address_id' => 'require|number|>:0',
        ]);

        if (!$validate->check(input('post.'))) {
            abort(10003,'参数错误，请重试');
        }

        $goods_ids = $buy_goods;

        $buy_goods = explode(",",$buy_goods);
        $buy_number = explode(",",$buy_number);

        if(count($buy_goods) != count($buy_number)){
            abort(10003,'数据异常，请重试');
        }

        // 获取优惠券并验证 rice add 2020-10-30
        $coupon_info = [];
        if($use_coupon){
            $coupon_mod = new Coupon();
            $coupon_info = $coupon_mod->get_coupon($use_coupon);
            if($coupon_info){
                $date = date('Y-m-d H:i:s',time());
                if($date>$coupon_info['end_time']){
                    abort(10003,'优惠券有效期已过');
                }
            }
        }

        // 获取月卡并验证 rice add 2022-5-26
        $card_info = [];
        if($use_month_card){
            $card_mod = new MonthCard();
            $card_maps = [
                'id'=> $use_month_card,
            ];
            $card_info = $card_mod->getMonthCard($card_maps);
             // Db::name('pay_test')->insert(['msg'=>$card_mod->getLastsql()]);
            if(empty($card_info)){
                abort(10003,'月卡有效期已过');
            }
        }
       
        //1 获取用户收货地址信息，如果用户没有选择就去查找设置为默认的收货地址，如果都没有就创建一个空的
        $address = Db::name('user_address')
            ->where('user_id',session('user.id'))
            ->where('deleted',0)
            ->where('id',$address_id)
            ->field('id address_id,gender,delivery_name,delivery_phone,delivery_address')
            ->find();

        if(empty($address)){
            abort(10003,'地址不可用');
        }

        //2 获取下单商品信息
        $store = Db::name('store')
            ->where('id',$store_id)
            ->where('deleted',0)
            ->field("id store_id,name,phone,address,detail,campus_id,min_price,start_time1,end_time1,start_time2,end_time2,start_time3,end_time3,logo,delivery_price,delivery_name,box_type,box_price,box_name,status,manage_ratio,develop_ratio,pay_ratio,order_cancel_time,user_open_id")
            ->find();
        if(empty($store)){
            abort(10003,'店铺不存在');
        }

        $campus = Db::name('campus')
            ->where('id',$store['campus_id'])
            ->where('deleted',0)
            ->field('name')
            ->find();

        if(empty($campus)){
            abort(10003,'该校区暂不支持下单');
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
            abort(10003,'该店铺休息中');
        }

        //3 获取购买商品信息 计算金额
        $buy_info = [];
        $box_price = 0; //餐盒费价格
        $all_price = 0; //购买总价格=商品+配送费+餐盒费
        $goods_count = 0; //购买总数量
        $discount_money = 0; //优惠金额 RICE

        $goods_detail = ""; //获取订单表中的商品信息内容
        foreach ($buy_goods as $key=>$goods){
            if($buy_number[$key] <= 0){
                abort(10003,'商品异常，请重试');
            }
            $buy_info[$key] = Db::name('goods')
                ->where('id',$goods)
                ->where('status',1)
                ->where('deleted',0)
                ->field('id goods_id,number,image,name,price,unit')
                ->find();

            if(empty($buy_info[$key])){
                abort(10003,'部分商品已下架');
            }

            $buy_info[$key]['count'] = $buy_number[$key];
            $buy_info[$key]['price_count'] = $buy_info[$key]['price'] * $buy_number[$key];

            if($store['box_type'] == 1){
                $box_price = $store['box_price'];
            }else{
                $box_price += $store['box_price']*$buy_info[$key]['count'];
            }

            $all_price += $buy_info[$key]['price_count'];
            $goods_count += $buy_number[$key];

            $goods_detail .= '--onelist--'.$buy_info[$key]['goods_id'];     //1 商品id
            $goods_detail .= '--twolist--'.$buy_info[$key]['number'];       //2 商品编号
            $goods_detail .= '--twolist--'.$buy_info[$key]['name'];         //3 商品名称
            $goods_detail .= '--twolist--'.$buy_number[$key];               //4 购买数量
            $goods_detail .= '--twolist--'.$buy_info[$key]['price'];        //5 单价
            $goods_detail .= '--twolist--'.$buy_info[$key]['unit'];         //6 单位
        }
        // 判断优惠券是否满足条件
        $new_all_price=$all_price;//2022-10-24
        if($coupon_info){
            if($all_price < $coupon_info['full_money']){
                abort(10003,'优惠券未满足条件');
            }

            $discount_money += $coupon_info['discount_money'];
            $new_all_price  =$all_price-$discount_money;//2022-10-24
        }
        //判断月卡是否满足条件 
        if($card_info){
//            if($all_price < $card_info['coupon']['full_money']){
//                abort(10003,'月卡未满足条件');
//            }
            if($new_all_price < $card_info['coupon']['full_money']){//2022-10-24
                abort(10003,'月卡未满足条件');
            }

            $discount_money += $card_info['coupon']['discount_money'];
        }
        // $all_price = $all_price + $box_price + $store['delivery_price'];

        // 计算订单总金额 增加优惠金额 RICE
        $count_price = $all_price + $box_price + $store['delivery_price'];//2022-10-24
        $all_price = $all_price + $box_price + $store['delivery_price'] - ($discount_money*100);
        // $all_price = $all_price + $box_price - ($discount_money*100);
        if($all_price < 0){
            abort(10003,'优惠券优惠金额未满足条件');
        }
        $sys_setting = Db::name('sys_setting')
            ->where('deleted',0)
            ->field('wx_ratio')
            ->find();

        $create_time = date("Y-m-d H:i:s");

        //订单数据生成
        $orders_data = [];

        $orders_data['order_number'] = build_order_no();
        $orders_data['store_id'] = $store['store_id'];
        $orders_data['goods_ids'] = $goods_ids;
        $orders_data['user_id'] = session('user.id');
        $orders_data['store_name'] = $store['name'];
        $orders_data['address_id'] = $address['address_id'];
        $orders_data['count'] = $goods_count;
        $orders_data['box_type'] = $store['box_type'];
        $orders_data['box_price'] = $box_price;
        $orders_data['box_name'] = $store['box_name'];
        $orders_data['manage_ratio'] = $store['manage_ratio'];
        //$orders_data['manage_price'] = round(($all_price-$store['delivery_price'])*$orders_data['manage_ratio']);
        $orders_data['manage_price'] = round($count_price*$orders_data['manage_ratio']);//2022-10-24
        $orders_data['develop_ratio'] = $store['develop_ratio'];
        //$orders_data['develop_price'] = round(($all_price-$store['delivery_price'])*$orders_data['develop_ratio']);
        $orders_data['develop_price'] = round($count_price*$orders_data['develop_ratio']);//2022-10-24
        $orders_data['pay_manage_ratio'] = $store['pay_ratio'];//$sys_setting['wx_ratio'];
        //$orders_data['pay_manage_price'] = round(($all_price-$store['delivery_price'])*$orders_data['pay_manage_ratio']);
        $orders_data['pay_manage_price'] = round($count_price*$orders_data['pay_manage_ratio']);//2022-10-24
        // $orders_data['store_price'] = $all_price - $orders_data['manage_price'] - $orders_data['develop_price'] - $orders_data['pay_manage_price'];

        // 计算订单店铺获得金额
        //$store_price = $all_price - $orders_data['manage_price'] - $orders_data['develop_price'] - $orders_data['pay_manage_price'] - $store['delivery_price'];
        $store_price = $all_price - $orders_data['manage_price'] - $orders_data['develop_price'] - $orders_data['pay_manage_price'];//2022-10-24
        if($coupon_info){
            //使用卖家的优惠券
            if($coupon_info['seller_id'] == $store_id){
                $store_price = $store_price - $discount_money;
            }
        }

        $orders_data['store_price'] = $store_price;
        $orders_data['convey_price'] = $store['delivery_price'];
        $orders_data['convey_name'] = $store['delivery_name'];
        $orders_data['total_price'] = $all_price;
        $orders_data['campus_id'] = $store['campus_id'];
        $orders_data['campus_name'] = $store['campus_name'];
        $orders_data['delivery_name'] = $address['delivery_name'];
        $orders_data['delivery_phone'] = $address['delivery_phone'];
        $orders_data['delivery_address'] = $address['delivery_address'];
        $orders_data['gender'] = $address['gender'];
        $orders_data['pay_status'] = 1;
        $orders_data['status'] = 1;
        $orders_data['goods_detail'] = $goods_detail;
        $orders_data['order_source'] = 1;
        $orders_data['order_name'] = '海师外卖-'.$store['name'];
        $orders_data['order_describe'] = $buy_info[0]['name'].'等，共'.$orders_data['count'].'件商品';
        $orders_data['create_time'] = $create_time;
        $orders_data['use_month_card'] = $use_month_card;
        $orders_data['use_coupon'] = $use_coupon;
        $orders_data['discount_money'] = $discount_money;
        $orders_data['remake'] = $remake;
       // Db::name('pay_test')->insert(['msg'=>'订单生成数据：'. $orders_data['discount_money']]);
        /*订单数据生成完成，准备插入数据*/
        // 启动事务

        if(session('user.id')==11){
          //dump($orders_data);
          //exit;
        }
        Db::startTrans();

        $is_true = false;
        $insert_orders_id = Db::name('orders')->where('order_number','<>',$orders_data['order_number'])->insertGetId($orders_data);
        if($insert_orders_id){
            $is_true = true;
            //订单生成成功，添加订单状态时间表
            $orders_times = [];
            $orders_times['orders_id'] = $insert_orders_id;
            $orders_times['status'] = $orders_data['status'];
            $orders_times['status_time'] = $create_time;

            $insert_orders_times = Db::name('orders_times')->insert($orders_times);
            if(!$insert_orders_times){
                $is_true = false;
                Log::error("[ordersTimesError]订单时间插入失败,info=".json_encode($orders_times));
            }

            // 更新coupon_user 表，用户使用优惠券信息 RICE add 2020-10-30
            if($coupon_info){
                $coupon_user_data['is_used'] = 1;
                $coupon_user_data['order_no'] = $orders_data['order_number'];
                $coupon_user_data['use_time'] = date('Y-m-d H:i:s',time());
                $coupon_user_data['remark'] = '购物优惠';

                $coupon_arr = Db::name('coupon_user')->where('coupon_id',$use_coupon)->where('user_id',$orders_data['user_id'])->update($coupon_user_data);
                if(!$coupon_arr){
                    $is_true = false;
                }
            }

             // 扣减月卡次数
            if($use_month_card){
                $card_mod = new MonthCard();
                // 验证月卡有效性
                $card_id = $use_month_card;
                $card_info = $card_mod->getMonthCard(['id'=>$card_id]);
                if(!empty($card_info)){
                    // 扣减
                    $card_res = $card_mod->deductCount($card_id);
                    if($card_res){
                        // 增加日志
                        $card_mod->addLog($card_id,1,$insert_orders_id);
                    }else{
                        $is_true = false;
                    }
                }
                // Db::name('pay_test')->insert(['msg'=>'开始支付：'.$card_info['coupon']['discount_money']]);
            }

        }
       
        if($is_true){
            Db::commit();
        }else{
            Db::rollback();
            abort(10003,'下单失败,请重试');
        }
        
        //调用支付方式 RICE
        if(1==2){//支付宝
            $wappay = new Wappay();
            $data = [];
            $wappay->pay($data);
        }else{//微信

            //调用统一下单API
            $params = [
                'appid' => config('wx_config.weixin_appID'),
                'mch_id' => config('wx_config.wxpay_mchid'),
                'nonce_str' => md5(time()),
                'body' => $orders_data['order_name'],
                'detail' => $orders_data['order_describe'],
                'out_trade_no' => $orders_data['order_number'],
                'total_fee' => $orders_data['total_price'],
                'spbill_create_ip' => $_SERVER['SERVER_ADDR'],
                'notify_url' => config('wx_config.wx_pay_notify'),
                'trade_type' => 'JSAPI',
                'product_id' => $insert_orders_id,
                'openid' => session('user.openid')
            ];
            $wxpay = new WxPay();
            $arr = $wxpay->unifiedorder($params);
            $wxpay->logs('logs.txt',$arr);

            if (isset($arr['prepay_id'])) {
                //重新签名
                $data = [
                    'appId' => $arr['appid'],
                    'timeStamp' => "".time(),
                    'nonceStr' => md5(time()),
                    'package' => 'prepay_id='.$arr['prepay_id'],
                    'signType' => 'MD5'
                ];
                $data = $wxpay->setSign($data);
                $data['paySign'] = $data['sign'];
                unset($data['sign']);

                $data['order_number'] = $orders_data['order_number'];

                // 今日下单单号统计 RICE
                $today_number = Db::name('orders')
                    ->where('store_id',$orders_data['store_id'])
                    ->where('deleted',0)
                    ->where('pay_status','>',1)
                    ->where('status','in',[2,3,4,5,6,7,8,9,10,11,12,13,15])
                    ->where('create_time','>=',date("Y-m-d 00:00:00",time()))
                    ->where('create_time','<=',date("Y-m-d 23:59:59",time()))
                    ->count();
                // $store_name = $orders_data['store_name'] .'  单号 : '.$today_number;
                //给用户发送模板消息
                send_add_success(session('user.openid'),
                    $data['order_number'],
                    $orders_data['store_name'],
                    $orders_data['create_time'],
                    $orders_data['order_describe'],
                    $orders_data['total_price']);

                // 给卖家发送模板消息
                if($store['user_open_id']){
                    send_add_success($store['user_open_id'],
                    $data['order_number'],
                    $orders_data['store_name'],
                    $orders_data['create_time'],
                    $orders_data['order_describe'],
                    $orders_data['total_price']);
                }
                
                // 修改商品库存
                $up_goods_info = '';
                $up_stock = '';
                foreach ($buy_goods as $key => $value) {
                    // 查库存
                    $up_goods_info = Db::name('goods')->where('id',$value)->field('stock')->find();
                    $up_stock = $up_goods_info['stock'] - $buy_number[$key];
                    Db::name('goods')->where('id',$value)->update(['stock' => $up_stock]);
                }
                return new Ret($data);
            } else {
                abort(10003,'prepay_id不存在');
            }
        }

        
    }


    /*
     * 用户订单已经生成，传入订单编号，直接获取支付信息
     * */
    public function wxpay_goods_orders()
    {
        if(!request()->isPost()){
            abort(10003,'下单失败');
        }

        $order_number = input('post.order_number');

        $validate = new Validate([
            'order_number' => 'require',
        ]);

        if (!$validate->check(input('post.'))) {
            abort(10003,'参数错误，请重试');
        }

        $orders_data = Db::name('orders')
            ->where('order_number',$order_number)
            ->where('status',1)
            ->where('pay_status',1)
            ->where('user_id',session('user.id'))
            ->where('deleted',0)
            ->where('user_deleted',0)
            ->find();

        if(empty($orders_data)){
            abort(10003,'该订单已取消，请重新下单');
        }

        //2 获取下单商品信息
        $store = Db::name('store')
            ->where('id',$orders_data['store_id'])
            ->where('deleted',0)
            ->field("id store_id,name,phone,address,detail,campus_id,min_price,start_time1,end_time1,start_time2,end_time2,start_time3,end_time3,logo,delivery_price,delivery_name,box_type,box_price,box_name,status,manage_ratio,develop_ratio,order_cancel_time")
            ->find();

        if(empty($store)){
            abort(10003,'店铺不存在');
        }

        $campus = Db::name('campus')
            ->where('id',$store['campus_id'])
            ->where('deleted',0)
            ->field('name')
            ->find();

        if(empty($campus)){
            abort(10003,'该校区暂不支持下单');
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
            abort(10003,'该店铺休息中');
        }

        $sys_setting = Db::name('sys_setting')
            ->where('deleted',0)
            ->field('wx_ratio')
            ->find();

        //店铺当前信息和订单信息不一致拒绝支付，要求重新下单
        if($store['box_type'] != $orders_data['box_type'] || ($orders_data['box_type'] == 1 && $store['box_price'] != $orders_data['box_price']) || ($orders_data['box_type'] == 2 && $store['box_price']*$orders_data['count'] != $orders_data['box_price']) || $store['box_name'] != $orders_data['box_name'] || $store['manage_ratio'] != $orders_data['manage_ratio'] || $store['develop_ratio'] != $orders_data['develop_ratio'] || $store['delivery_price'] != $orders_data['convey_price'] || $store['delivery_name'] != $orders_data['convey_name']){
            abort(10003,'店铺信息有变，请重新下单');
        }

        if($orders_data['pay_manage_ratio'] != $sys_setting['wx_ratio']){
            abort(10003,'平台信息有变，请重新下单');
        }

        //查看商品是否下架,先获取下单商品id
        $goods_ids =  explode('--onelist--', $orders_data['goods_ids']);

        //3 获取购买商品信息 计算金额
        $buy_info = [];

        foreach ($goods_ids as $key=>$goods) {
            $goods_info = Db::name('goods')
                ->where('id', $goods)
                ->where('status', 1)
                ->where('deleted', 0)
                ->field('id goods_id,number,image,name,price,unit')
                ->find();

            if (empty($goods_info)) {
                abort(10003, '部分商品已下架，请重新下单');
            }
        }

        //调用统一下单API
        $params = [
            'appid' => config('wx_config.weixin_appID'),
            'mch_id' => config('wx_config.wxpay_mchid'),
            'nonce_str' => md5(time()),
            'body' => $orders_data['order_name'],
            'detail' => $orders_data['order_describe'],
            'out_trade_no' => $orders_data['order_number'],
            'total_fee' => $orders_data['total_price'],
            'spbill_create_ip' => $_SERVER['SERVER_ADDR'],
            'notify_url' => config('wx_config.wx_pay_notify'),
            'trade_type' => 'JSAPI',
            'product_id' => $orders_data['id'],
            'openid' => session('user.openid')
        ];
        $wxpay = new WxPay();
        $arr = $wxpay->unifiedorder($params);

        if (isset($arr['prepay_id'])) {
            //重新签名
            $data = [
                'appId' => $arr['appid'],
                'timeStamp' => "".time(),
                'nonceStr' => md5(time()),
                'package' => 'prepay_id='.$arr['prepay_id'],
                'signType' => 'MD5'
            ];
            $data = $wxpay->setSign($data);
            $data['paySign'] = $data['sign'];
            unset($data['sign']);

            $data['order_number'] = $orders_data['order_number'];

            return new Ret($data);
        } else {
            abort(10003,'prepay_id不存在');
        }
    }


}