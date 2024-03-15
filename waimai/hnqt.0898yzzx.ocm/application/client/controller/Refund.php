<?php
/**
 * Created by PhpStorm.
 * User: WangZiyong
 * Date: 2020/8/29
 * Time: 5:20
 */
namespace app\client\controller;

use app\common\controller\ClientBase;
use think\Db;
use think\Log;
use think\Validate;
use util\Ret;
use util\WxPay;
use think\Config;
use app\store\common\AppPrint;
use app\store\common\AppFePrint;

class Refund extends ClientBase {

    /*
     * 用户发起退款
     * */
    public function wxpay_goods()
    {
        if(!request()->isPost()){
            abort(10003,'退款失败');
        }
        $order_number = input('post.order_number');

        $validate = new Validate([
            'order_number' => 'require',
        ]);

        if (!$validate->check(input('post.'))) {
            abort(10003,'参数错误，请重试');
        }

        $orders = Db::name('orders')
            ->where('order_number',$order_number)
            ->where('user_id',session('user.id'))
            ->where('pay_status',2)
            ->where('deleted',0)
            ->where('user_deleted',0)
            ->find();

        if (!$orders) {
            abort(10003,'该订单不支持退款');
        }


        $update_time = date("Y-m-d H:i:s");

        $update_data = [
            'update_time' => $update_time,
            'refund_order_code' => build_order_refund_no(),
            'status' => 8,
        ];

        $update_res = Db::name('orders')
            ->where('order_number',$order_number)
            ->where('user_id',session('user.id'))
            ->where('pay_status',2)
            ->where('deleted',0)
            ->where('user_deleted',0)
            ->update($update_data);


        if($update_res){
            //订单生成成功，添加订单状态时间表
            $orders_times = [];
            $orders_times['orders_id'] = $orders['id'];
            $orders_times['status'] = $update_data['status'];
            $orders_times['status_time'] = $update_time;

            $insert_orders_times = Db::name('orders_times')->insert($orders_times);
            if(!$insert_orders_times){
                Log::error("[ordersTimesError]订单时间插入失败,info=".json_encode($orders_times));
            }
            //使用优惠券，改变状态为未使用 RICE add 2020-11-11
            if($orders['discount_money']){
                $coupon_data = [];
                $coupon_data['is_used'] = 0;
                $coupon_data['order_no'] = '';
                $coupon_data['use_time'] = '';

                Db::name('coupon_user')
                    ->where('coupon_id',$orders['use_coupon'])
                    ->where('user_id',$orders['user_id'])
                    ->where('order_no',$orders['order_number'])
                    ->update($coupon_data);
            }
            //给用户发送模板消息 退款已提交
            $user = Db::name('user')
                ->where('id',$orders['user_id'])
                ->where('deleted',0)
                ->where('disabled',0)
                ->field('id,gz_openid')
                ->find();
            if($user){
                send_refund_status($user['gz_openid'],$orders['order_number'],$orders['total_price'],$orders['create_time'],$update_data['status']);
            }

            //给卖家发送模板消息 退款申请已提交
            $store_info = Db::name('store')
                ->where('id',$orders['store_id'])
                ->field('user_open_id')
                ->find();
            if($store_info['user_open_id']){
                send_refund_status($store_info['user_open_id'],$orders['order_number'],$orders['total_price'],$orders['create_time'],$orders['status']);
            }


        }


        //调用微信取消订单接口
        if($orders['pay_type'] == 1){
            $wx_refund_res = wx_refund($orders['wx_order_code'],$orders['order_number'],$orders['total_price'],$update_data['refund_order_code'],$update_data['status']);

            //自动打印订单
            $print = Db::name('prints')->where('store_id',$orders['store_id'])->where('deleted',0)->field('type')->find();

            if($print['type'] == 1){
                $AppPrint = new AppPrint();
            }else{ //飞鹅
                $AppPrint = new AppFePrint();
            }
            //$AppPrint = new AppPrint();
            $AppPrint->get_refund_print($orders['id']);

            
            if($wx_refund_res){
                return new Ret();
            }else{
                abort(10003,'微信退款申请失败，请重试');
            }
        }else{
            abort(10003,'该订单暂不支持退款，请联系店家');
        }
    }


    /*
     * 用户确认订单，生成订单信息
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
            ->where('user_id',session('user.id'))
            ->where('deleted',0)
            ->where('user_deleted',0)
            ->find();

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
            'notify_url' => config('wx_config.wxpay_notify'),
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
                'timeStamp' => time(),
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