<?php
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

class Card extends ClientBase
{
    // 月卡下单首页
    public function index(){
        
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

    // 下单支付月卡
    public function wxpay_card()
    {
        if(!request()->isPost()){
            abort(10003,'下单失败');
        }

        // 增加月购
        $total_price = input('total_price');
        $discount_money = input('discount_money');
        $full_money = input('full_money');

        $card_mod = new MonthCard();
        $card_info = $card_mod->add('',$total_price,$discount_money,$full_money);
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
                'notify_url' => 'https://hnqt.0898yzzx.com/client/notify/wxpay_card',
                'trade_type' => 'JSAPI',
                'product_id' => $card_info['insert_orders_id'],
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
                $data['card_number'] = $card_info['card_number'];
                return new Ret($data);
            } else {
                abort(10003,'prepay_id不存在');
            }
        }

        
    }

    // 微信回调
    public function notify()
    {
        $wxpay = new WxPay();
        try {
            // 获取腾讯传回来的通知数据
            $xml = $wxpay->getPost();
            // 将XML格式的数据转换为数组
            $data = $wxpay->XmlToArr($xml);
            $wxpay->logs('logs.txt', $data);

            $save = "";
            foreach ($data as $k => $v) {
                $save = $save . $k . '=' . $v . ';';
            }

            //存储接收到的支付回调数据信息
            $notify_id = Db::name('orders_notify')->insertGetId(['content' => $save, 'type' => 2, 'pay_type' => 1]);

            //查找是否有符合要求的订单信息 注意：微信支付回调不是单次，会是多次，所以要特别注意，如果找不到符合条件订单就返回SUCCESS
            $orders = Db::name('month_card')
                ->where('status', 0)
                ->where('card_number',$data['out_trade_no'])
                ->field('id,user_id,card_number,status,total_price')
                ->find();

            if (empty($orders)) {

                $params = [
                    'return_code' => 'SUCCESS',
                    'return_msg' => 'OK'
                ];
                echo $wxpay->ArrToXml($params);
                exit();
            }

            // 验证签名
            if ($wxpay->checkSign($data) && $orders['total_price'] == $data['total_fee']/100) {

                 // 启动事务
                Db::startTrans();
                try{
                    $model = Db::name('month_card');
                    $update_time = date("Y-m-d H:i:s");
                 
                    //改变订单信息
                    $order_update = [];
                    $order_update['status'] = 1;
                    $order_update['wx_order_code'] = $data['transaction_id'];
                    $order_update['update_time'] = $update_time;
                    
                    $update_res = Db::name('month_card')
                        ->where('card_number',$data['out_trade_no'])
                        ->update($order_update);                                   

                    // 提交事务
                    if($update_res){
                        Db::commit(); 
                    }else{
                        Db::rollback();
                    }
                    
                } catch (\Exception $e) {
                    // 回滚事务
                    Db::rollback();
                    $update_res = 0;
                }   

                if(!$update_res){

                    Log::error("[ordersUpdateError]支付回调修改订单错误,orders_number=".$data['out_trade_no']);

                    $params = [
                        'return_code' => 'FAIL',
                        'return_msg' => '修改订单状态失败'
                    ];
                    echo $wxpay->ArrToXml($params);
                    exit();
                }

                $params = [
                    'return_code' => 'SUCCESS',
                    'return_msg' => 'OK'
                ];
                echo $wxpay->ArrToXml($params);
            }else{
                $params = [
                    'return_code' => 'FAIL',
                    'return_msg' => '签名失败'
                ];
                echo $wxpay->ArrToXml($params);
                exit();
            }
        } catch (\Exception $e) {
            $wxpay->logs('logs.txt', $e->getMessage());
            exit();
        }
    }

}
