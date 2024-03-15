<?php
namespace app\admin\controller;

use app\common\controller\AdminBase;
use app\admin\model\Admin;
use app\lib\event\PushEvent;
use think\Controller;
use think\Db;
use app\admin\model\MonthCard;

use think\Log;
use think\Validate;
use think\View;
use util\Ret;
use util\WxPay;
use think\Config;
use alipay\Wappay;

class Index extends AdminBase
{
    protected function _initialize()
    {
        parent::_initialize();
    }

    //后台首页
    public function index()
    {
        $this->assign('uid',session('admin.id'));
        return $this->fetch();
    }

    public function index2()
    {
        $id = session('admin.id');
        $admin_result = session('admin.last_login_time');
        $this->assign('admin_result',$admin_result);

        return $this->fetch();
    }

    /**
     * 推送目标页
     *
     * @return \think\response\View
     */
    public function targetPage()
    {
        return view();
    }


    // 人工返店铺金额
    public function sendStoreAmount(){
        //订单完成，将订单收入写入店铺金额明细表中
        $order_id = input('order_id');
        if(empty($order_id)){
            return '程序出错';
            exit;
        }

        // $store_price = Db::name('orders')->where('id',$order_id)->value('store_price');
        // $balance = Db::name('store')->where('id',$store_id)->value('balance');

        $orders_data = Db::name('orders')->where('id',$order_id)->where('deleted',0)->find();
        if(empty($orders_data)){
            return '找不到订单';
            exit;
        }

        $store_id = $orders_data['store_id'];
        $store = Db::name('store')->where('id',$store_id)->where('deleted',0)->find();

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
        $store_amount_data['admin_id'] = 1;
        $store_amount_data['notes'] = '人工返金额，商品出售';

        $store_amount_records = Db::name('store_amount_records')->insert($store_amount_data);

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

        echo '完成';exit;
    }

    // // 测试
    // public function test(){
    //     $autoOrderList = Db::name('orders')
    //         ->where('deleted',0)
    //         ->where('pay_status',2)
    //         ->where('status','in','3,5')
    //         ->where('update_time','<',date("Y-m-d 01:00:00",time()))
    //         ->field('id,store_id,order_number')
    //         ->select();
    //         dump($autoOrderList);exit;
    //     foreach ($autoOrderList as $autoOrder){
    //         $result = Db::name('orders')
    //             ->where('id',$autoOrder['id'])
    //             ->where('deleted',0)
    //             ->where('pay_status',2)
    //             ->where('status','in','3,5')
    //             ->where('update_time','<',date("Y-m-d 01:00:00",time()))
    //             ->update(['status'=>7,'status7_time'=>date('Y-m-d H:i:s',time()),'update_time'=>date('Y-m-d H:i:s',time())]);

    //         if($result){
    //             //订单完成，将订单收入写入店铺总额与余额
    //             $store = Db::name('store')->where('id',$autoOrder['store_id'])->where('deleted',0)->find();
    //             $orders_data = Db::name('orders')->where('id',$autoOrder['id'])->where('deleted',0)->find();
    //             $inset_data = [];
    //             $inset_data['balance'] = $store['balance']+$orders_data['store_price'];
    //             $inset_data['revenue'] = $store['revenue']+$orders_data['store_price'];
    //             $inset_data['update_time'] = date('Y-m-d H:i:s',time());
    //             $store_result =  Db::name('store')->where('id',$autoOrder['store_id'])->update($inset_data);

    //             //订单完成，将订单收入写入店铺金额明细表中
    //             $store_amount_data = [];
    //             $store_amount_data['store_id'] = $autoOrder['store_id'];
    //             $store_amount_data['money'] = $orders_data['store_price'];
    //             $store_amount_data['old_balance'] = $store['balance'];
    //             $store_amount_data['now_balance'] = $store['balance']+$orders_data['store_price'];
    //             $store_amount_data['status'] = 1;
    //             $store_amount_data['admin_id'] = session('admin.id');
    //             $store_amount_data['notes'] = '商品出售';
    //             $store_amount_records = Db::name('store_amount_records')->insert($store_amount_data);

    //             //存入订单状态改变时间表
    //             $orders_time_data = [];
    //             $orders_time_data['orders_id'] = $autoOrder['store_id'];
    //             $orders_time_data['status'] = 7;
    //             $orders_time_data['status_time'] = date('Y-m-d H:i:s',time());
    //             $orders_time_data['admin_id'] = session('admin.id');
    //             $orders_time = Db::name('orders_times')->insert($orders_time_data);

    //             /********将订单中的全部商品信息获取********/
    //             $orders_goods_sale = Db::name('orders')
    //                 ->where('id',$autoOrder['id'])
    //                 ->where('deleted',0)
    //                 ->find();
    //             $order_goods_detail =  explode('--onelist--', $orders_goods_sale['goods_detail']);//获取订单中的全部商品并以一商品为一数组
    //             for ($i=1;$i<count($order_goods_detail);$i++){
    //                 $all_goods = explode('--twolist--', $order_goods_detail[$i]);//将订单中的每样商品信息组成数组
    //                 $goods_id = '';
    //                 $sale = 0;
    //                 for ($j=0;$j<count($all_goods);$j++){  //统计每样商品数量
    //                     switch ($j) {
    //                         case 0: $goods_id = $all_goods[$j];break;//商品数量
    //                         case 3:$sale = $all_goods[$j];break;//商品数量
    //                     }
    //                 }
    //                 $goods = Db::name('goods')->where('id',$goods_id)->find();
    //                 $sale = $sale+$goods['sale'];
    //                 $goods = Db::name('goods')
    //                     ->where('id',$goods_id)
    //                     ->update(['sale'=>$sale,'update_time'=>date('Y-m-d H:i:s',time())]);
    //             }
    //             /********将订单中的全部商品信息获取********/

    //             $log = '自动完成订单->订单编号:'.$autoOrder['order_number']." 于 ".date('Y-m-d H:i:s',time())." 自动完成订单成功".PHP_EOL;
    //             // Log::write($log);
    //         }else{
    //             $log = '自动完成订单->订单编号:'.$autoOrder['order_number']+" 于 ".date('Y-m-d H:i:s',time())." 自动完成订单失败".PHP_EOL;
    //             // Log::write($log);
    //         }
    //         dump($log);exit;
    //     }
            
    // }


    public function test()
    {
        return $this->fetch();
    }

    public function wxpay_card($value=''){
        if(!request()->isPost()){
            abort(10003,'下单失败');
        }

        // 增加月购
        $total_price = input('total_price');
        $discount_money = input('discount_money');
        $full_money = input('full_money');

        $card_mod = new MonthCard();
        $card_info = $card_mod->add(1,$total_price,$discount_money,$full_money);
        if(!$card_info){
            abort(10003,'增加月卡失败');
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
                'body' => '购买月卡',
                'detail' => '购买月卡',
                'out_trade_no' => $card_info['card_number'],
                'total_fee' => $card_info['total_price']*100,
                'spbill_create_ip' => $_SERVER['SERVER_ADDR'],
                'notify_url' => 'https://hnqt.0898yzzx.com/client/notify/card_notify',
                'trade_type' => 'JSAPI',
                'product_id' => $card_info['insert_orders_id'],
                'openid' => 'olytE6g8RowKgjtS88hXhYfjZIks'
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

                $data['card_number'] = $card_info['card_number'];
                return new Ret($data);
            } else {
                abort(10003,'prepay_id不存在');
            }
        }
    }

}
