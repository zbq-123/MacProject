<?php
/**
 * Created by PhpStorm.
 * User: WangZiyong
 * Date: 2020/8/29
 * Time: 5:20
 */
namespace app\client\controller;

use app\common\controller\Base;
use app\store\common\AppPrint;
use app\store\common\AppFePrint;
use app\admin\model\MonthCard;
use think\Db;
use think\Log;
use think\Validate;
use util\Ret;
use util\WxPay;
use think\Config;

class Notify extends Base {

    /*
     * 接收腾讯推送支付通知_外卖支付回调通知
     * */
    // public function wxpay_goods()
    // {
    //     $wxpay = new WxPay();
    //     try {
    //         // 获取腾讯传回来的通知数据
    //         $xml = $wxpay->getPost();
    //         // 将XML格式的数据转换为数组
    //         $data = $wxpay->XmlToArr($xml);
    //         $wxpay->logs('logs.txt', $data);

    //         $save = "";
    //         foreach ($data as $k => $v) {
    //             $save = $save . $k . '=' . $v . ';';
    //         }

    //         //存储接收到的支付回调数据信息
    //         $notify_id = Db::name('orders_notify')->insertGetId(['content' => $save, 'type' => 1, 'pay_type' => 1]);

    //         //查找是否有符合要求的订单信息 注意：微信支付回调不是单次，会是多次，所以要特别注意，如果找不到符合条件订单就返回SUCCESS
    //         $orders = Db::name('orders')
    //             ->where('deleted', 0)
    //             ->where('status', 1)
    //             ->where('pay_status', 1)
    //             ->where('order_number',$data['out_trade_no'])
    //             ->field('id,user_id,order_number,status,pay_status,total_price,store_id,store_name,pay_type')
    //             ->find();

    //         if (empty($orders)) {

    //             Log::error("[ordersNotifyError]回调请求订单找不到,notify_id=".$notify_id.";order_number=".$data['out_trade_no']);
    //             $wxpay->logs('ordersNotifyError.txt', "[ordersNotifyError]回调请求订单找不到,notify_id=".$notify_id.";order_number=".$data['out_trade_no']);
    //             $params = [
    //                 'return_code' => 'SUCCESS',
    //                 'return_msg' => 'OK'
    //             ];
    //             echo $wxpay->ArrToXml($params);
    //             exit();
    //         }

    //         // 验证签名
    //         if ($wxpay->checkSign($data) && $orders['total_price'] == $data['total_fee']) {

    //              // 启动事务
    //             Db::startTrans();
    //             try{

    //                 // 
    //                 $model = Db::name('orders');
    //                 $model->execute('set autocommit=0');
                    
    //                 $update_time = date("Y-m-d H:i:s");

    //                 //改变订单信息
    //                 $order_update = [];
    //                 $order_update['status'] = 2;
    //                 $order_update['pay_status'] = 2;
    //                 $order_update['pay_type'] = 1;
    //                 $order_update['wx_order_code'] = $data['transaction_id'];
    //                 $order_update['update_time'] = $update_time;
    //                 // $order_update['today_number'] = Db::name('orders')
    //                 //     ->where('store_id',$orders['store_id'])
    //                 //     ->where('deleted',0)
    //                 //     ->where('pay_status','>',1)
    //                 //     ->where('status','in',[2,3,4,5,6,7,8,9,10,11,12,13,15])
    //                 //     ->where('create_time','>=',date("Y-m-d 00:00:00",time()))
    //                 //     ->where('create_time','<=',date("Y-m-d 23:59:59",time()))
    //                 //     ->count();
    //                 // usleep(rand(1,100));
    //                 // 查询今日订单最大
    //                 $today_numbers = $model->where('store_id',$orders['store_id'])
    //                     ->where('deleted',0)
    //                     ->where('create_time','>=',date("Y-m-d 00:00:00",time()))
    //                     ->where('create_time','<=',date("Y-m-d 23:59:59",time()))
    //                     ->order('today_number desc')
    //                     ->field('today_number')
    //                     ->lock(true)
    //                     ->find();
    //                 $order_update['today_number'] = intval($today_numbers['today_number'])+1;
                    
    //                 // 查询是否存在相同的数据 rice add 2020-12-03
    //                 // $res = Db::name('orders')->where('store_id',$orders['store_id'])->where('today_number',$order_update['today_number'])->where('create_time','>=',date("Y-m-d 00:00:00",time()))->where('create_time','<=',date("Y-m-d 23:59:59",time()))->find();
    //                 // if($res){
    //                 //     $order_update['today_number'] = $order_update['today_number'] + 1;
    //                 // }

    //                 $update_res = Db::name('orders')
    //                     ->where('order_number',$data['out_trade_no'])
    //                     ->where('deleted',0)
    //                     ->update($order_update);                                   

    //                 // 提交事务
    //                 Db::commit(); 
    //             } catch (\Exception $e) {
    //                 // 回滚事务
    //                 Db::rollback();
    //                 $update_res = 0;
    //             }   

    //             if(!$update_res){

    //                 Log::error("[ordersUpdateError]支付回调修改订单错误,orders_number=".$data['out_trade_no']);
    //                 $wxpay->logs('ordersNotifyError.txt', "[ordersUpdateError]支付回调修改订单错误,orders_number=".$data['out_trade_no']);
    //                 $params = [
    //                     'return_code' => 'FAIL',
    //                     'return_msg' => '修改订单状态失败'
    //                 ];
    //                 echo $wxpay->ArrToXml($params);
    //                 exit();
    //             }


    //             //订单状态修改成功，添加订单状态时间表
    //             $orders_times = [];
    //             $orders_times['orders_id'] = $orders['id'];
    //             $orders_times['status'] = $order_update['status'];
    //             $orders_times['status_time'] = $update_time;

    //             $insert_orders_times = Db::name('orders_times')->insert($orders_times);
    //             if(!$insert_orders_times){
    //                 Log::error("[ordersTimesError]订单时间插入失败,info=".json_encode($orders_times));
    //                 $wxpay->logs('ordersNotifyError.txt', "[ordersTimesError]订单时间插入失败,info=".json_encode($orders_times));
    //             }

    //             //获取店铺管理员用户列表
    //             $store_user_list = Db::name('admin')
    //                 ->where('store_id',$orders['store_id'])
    //                 ->where('disabled',0)
    //                 ->select();

    //             //给管理员用户发送新的订单提醒
    //             foreach ($store_user_list as $store_user) {
    //                 push_orders_msg($store_user['id'],$data['out_trade_no']);
    //             }

    //             //给用户发送模板消息
    //             $user = Db::name('user')
    //                 ->where('id',$orders['user_id'])
    //                 ->where('deleted',0)
    //                 ->where('disabled',0)
    //                 ->field('id,gz_openid')
    //                 ->find();
    //             if($user){
    //                  $store_name = $orders['store_name'] .'  单号 : '.$order_update['today_number'];
    //                 send_pay_success($user['gz_openid'],$orders['order_number'],$orders['total_price'],$store_name,$update_time);
    //             }

    //             //------自动接单开始 新增 自动接单功能 修改订单状态，计时------
    //             $store_id = $orders['store_id'];
    //             $store = Db::name('store')->where('id',$store_id)->field('id,order_cancel_time')->find();
    //             $cancel_time = date("Y-m-d H:i:s", strtotime(date("Y-m-d H:i:s") . " +".$store['order_cancel_time']." seconds"));
    //             $take_orders_res = Db::name('orders')
    //                 ->where('order_number',$data['out_trade_no'])
    //                 ->where('deleted',0)
    //                 ->update(['status'=>3,'cancel_time'=>$cancel_time,'update_time'=>date('Y-m-d H:i:s',time())]);

    //             if ($take_orders_res){
    //                 if($orders['pay_type'] == 1){
    //                     //给用户发送模板消息 商家已接单
    //                     send_orders_status($user['gz_openid'],$orders['order_number'],$orders['total_price'],'微信支付','已接单');
    //                 }

    //                 //存入订单状态改变时间表
    //                 $orders_time_data = [];
    //                 $orders_time_data['orders_id'] = $orders['id'];
    //                 $orders_time_data['status'] = 3;
    //                 $orders_time_data['status_time'] = date('Y-m-d H:i:s',time());
    //                 $orders_time_data['admin_id'] = session('admin.id');
    //                 $insert_orders_time_data = Db::name('orders_times')->insert($orders_time_data);

    //                 if(!$insert_orders_time_data){
    //                     Log::error("[ordersTimesError]订单时间插入失败,info=".json_encode($orders_time_data));
    //                     $wxpay->logs('ordersNotifyError.txt', "[ordersTimesError]订单时间插入失败,info=".json_encode($orders_time_data));
    //                 }

    //                 //自动打印订单
    //                 $print = Db::name('prints')->where('store_id',$store_id)->where('deleted',0)->field('type')->find();

    //                 if($print['type'] == 1){
    //                     $AppPrint = new AppPrint();
    //                 }else{ //飞鹅
    //                     $AppPrint = new AppFePrint();
    //                 }
    //                 //$AppPrint = new AppPrint();
    //                 $print_result = $AppPrint->get_client_print($orders['id']);

    //                 if(!$print_result){
    //                     Log::error("[autoPrintError]自动打印订单错误,orders_id=".$orders['id']);
    //                 }
    //             }

    //             //------自动接单结束------

    //             $params = [
    //                 'return_code' => 'SUCCESS',
    //                 'return_msg' => 'OK'
    //             ];
    //             echo $wxpay->ArrToXml($params);
    //         }else{
    //             $params = [
    //                 'return_code' => 'FAIL',
    //                 'return_msg' => '签名失败'
    //             ];
    //             echo $wxpay->ArrToXml($params);
    //             exit();
    //         }
    //     } catch (\Exception $e) {
    //         $wxpay->logs('logs.txt', $e->getMessage());
    //         $wxpay->logs('ordersNotifyError.txt',  $e->getMessage());
    //         exit();
    //     }
    // }

    public function wxpay_goods()
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
            $notify_id = Db::name('orders_notify')->insertGetId(['content' => $save, 'type' => 1, 'pay_type' => 1]);
            // 启动事务
            Db::startTrans();

            $model = Db::name('orders');
            $model->execute('set autocommit=0');
            //查找是否有符合要求的订单信息 注意：微信支付回调不是单次，会是多次，所以要特别注意，如果找不到符合条件订单就返回SUCCESS
            $orders = $model->where('deleted', 0)
                ->where('status', 1)
                ->where('pay_status', 1)
                ->where('order_number',$data['out_trade_no'])
                ->field('id,user_id,order_number,status,pay_status,total_price,store_id,store_name,pay_type')
                ->lock(true)
                ->find();

            if (empty($orders)) {
                Log::error("[ordersUpdateError]支付回调修改订单错误,orders_number=".$data['out_trade_no']);
                $params = [
                    'return_code' => 'FAIL',
                    'return_msg' => '订单不存在'
                ];
                echo $wxpay->ArrToXml($params);
                Db::rollback();
            }

            // 验证签名
            if ($wxpay->checkSign($data) && $orders['total_price'] == $data['total_fee']) {
                $update_time = date("Y-m-d H:i:s");
                $store_id = $orders['store_id'];
                $store = Db::name('store')->where('id',$store_id)->field('id,order_cancel_time')->find();
                $cancel_time = date("Y-m-d H:i:s", strtotime(date("Y-m-d H:i:s") . " +".$store['order_cancel_time']." seconds"));
                
                //改变订单信息
                $order_update = [];
                $order_update['status'] = 3;
                $order_update['pay_status'] = 2;
                $order_update['pay_type'] = 1;
                $order_update['wx_order_code'] = $data['transaction_id'];
                $order_update['update_time'] = $update_time;
                $order_update['cancel_time'] = $cancel_time;

                // 查询今日订单最大
                $today_number = Db::name('order_today_numbers')->where(['store_id'=>$orders['store_id'],'create_time'=>['between',[date('Y-m-d 00:00:00'),date('Y-m-d 23:59:59')]]])->order('create_time desc')->limit(1)->value('today_number');
                if(empty($today_number)){
                    $today_number = 0;
                }

                $order_update['today_number'] = intval($today_number)+1;
                // 修改订单表
                $update_res = $model->where('order_number',$data['out_trade_no'])
                    ->where('deleted',0)
                    ->update($order_update);  
                

                //订单状态修改成功，添加订单状态时间表
                $orders_times = [];
                $orders_times['orders_id'] = $orders['id'];
                $orders_times['status'] = $order_update['status'];
                $orders_times['status_time'] = $update_time;

                $insert_orders_times = Db::name('orders_times')->insert($orders_times);

                // 插入今天订单号表
                $insert_today_number = Db::name('order_today_numbers')->insert(['order_id'=>$orders['id'],'store_id'=>$orders['store_id'],'today_number'=>$order_update['today_number'],'create_time'=>date('Y-m-d H:i:s',time())]);

                // 提交事务
                if(!empty($update_res) && !empty($insert_orders_times) && !empty($insert_today_number)){
                    
                    Db::commit(); 

                    $params = [
                        'return_code' => 'SUCCESS',
                        'return_msg' => 'OK'
                    ];
                    echo $wxpay->ArrToXml($params);

                    //自动打印订单
                    $print = Db::name('prints')->where('store_id',$store_id)->where('deleted',0)->field('type')->find();

                    if($print['type'] == 1){
                        $AppPrint = new AppPrint();
                    }else{ //飞鹅
                        $AppPrint = new AppFePrint();
                    }
                    //$AppPrint = new AppPrint();
                    $print_result = $AppPrint->get_client_print($orders['id']);

                    if(!$print_result){
                        Log::error("[autoPrintError]自动打印订单错误,orders_id=".$orders['id']);
                    }
                }else{
                    Db::rollback();

                    Log::error("[ordersUpdateError]支付回调修改订单错误,orders_number=".$data['out_trade_no']);
                    $params = [
                        'return_code' => 'FAIL',
                        'return_msg' => '修改订单状态失败'
                    ];
                    echo $wxpay->ArrToXml($params);
                    exit();
                }

                //获取店铺管理员用户列表
                $store_user_list = Db::name('admin')
                    ->where('store_id',$orders['store_id'])
                    ->where('disabled',0)
                    ->select();

                //给管理员用户发送新的订单提醒
                foreach ($store_user_list as $store_user) {
                    push_orders_msg($store_user['id'],$data['out_trade_no']);
                }

                //给用户发送模板消息
                $user = Db::name('user')
                    ->where('id',$orders['user_id'])
                    ->where('deleted',0)
                    ->where('disabled',0)
                    ->field('id,gz_openid')
                    ->find();
                if($user){
                    $store_name = $orders['store_name'] .'  单号 : '.$order_update['today_number'];
                    send_pay_success($user['gz_openid'],$orders['order_number'],$orders['total_price'],$store_name,$update_time);
                }                               


            }else{
                $params = [
                    'return_code' => 'FAIL',
                    'return_msg' => '签名失败'
                ];
                echo $wxpay->ArrToXml($params);

                Db::rollback();
                exit();
            }
        } catch (\Exception $e) {
            $wxpay->logs('logs.txt', $e->getMessage());
            exit();
        }
    }



    //退款接口
    public function wxrefund_goods()
    {
        $xmlData = file_get_contents('php://input');
        libxml_disable_entity_loader(true);
        $data = json_decode(json_encode(simplexml_load_string($xmlData, 'SimpleXMLElement', LIBXML_NOCDATA)), true);

        if($data['return_code'] == 'SUCCESS'){
            $decrypt = base64_decode($data['req_info'],true);
            $key = md5(config('wx_config.wxpay_appkey'));
            $refundDecryptData = openssl_decrypt($decrypt , 'aes-256-ecb',$key, OPENSSL_RAW_DATA);
            libxml_disable_entity_loader(true);
            $refundData = json_decode(json_encode(simplexml_load_string($refundDecryptData, 'SimpleXMLElement', LIBXML_NOCDATA)), true);

            $save = "";
            foreach ($refundData as $k => $v) {
                $save = $save . $k . '=' . $v . ';';
            }

            $notify_id = Db::name('refund_order_notify')->insertGetId(['content' => $save, 'type' => 1, 'pay_type' => 1]);

            /**处理事务逻辑**/
            $conditions = [];
            $conditions['refund_order_code'] = $refundData['out_refund_no'];

            $orders = Db::name('orders')
                ->where('deleted', 0)
                ->where('status', 'in',[2,8,9,11])
                ->where('pay_status', 2)
                ->where($conditions)
                ->find();

            if (empty($orders)) {

                Log::error("[ordersRefundNotifyError]回调请求退款订单找不到,notify_id=".$notify_id.";refund_order_code=".$refundData['out_refund_no']);

                echo '<xml>
                    <return_code><![CDATA[FAIL]]></return_code>
                    <return_msg><![CDATA[FAIL]]></return_msg>
                  </xml>';
                exit();
            }

            //判断算出的签名和通知信息的签名是否一致
            if ($orders['total_price'] == $refundData['refund_fee']) {

                //改变订单状态为已退款
                $orderStatus = [];

                switch ($orders['status']){
                    case 8:
                        $orderStatus['status'] = 9;
                        break;
                    case 11:
                        $orderStatus['status'] = 12;
                        break;
                    case 2:
                        $orderStatus['status'] = 15;
                        break;
                    default:
                        $orderStatus['status'] = 0;
                        break;
                }

                $now_time = date('Y-m-d H:i:s');

                $orderStatus['pay_status'] = 3;
                $orderStatus['wx_refund_order_code'] = $refundData['refund_id'];
                $orderStatus['refund_time'] = $now_time;

                //更新订单状态
                $update = Db::name('orders')
                    ->where('deleted', 0)
                    ->where('status', 'in',[2,8,9,11])
                    ->where('pay_status', 2)
                    ->where($conditions)
                    ->update($orderStatus);



                if ($update) {

                    //  // 增加月卡次数
                    // if($orders['use_month_card']){
                        
                    //     $card_mod = new MonthCard();
                    //     // 验证月卡有效性
                    //     $card_id = $orders['use_month_card'];
                    //     $card_info = $card_mod->getMonthCard(['id'=>$card_id],1);
                    //     if(!empty($card_info)){
                           
                    //         // 增加
                    //         $card_res = $card_mod->addCount($card_id);
                    //         if($card_res){
                    //             // 增加日志
                    //             // $card_log = [
                    //             //     'card_id' => $card_id,
                    //             //     'status' => 2,
                    //             //     'order_id' => $orders['id'],
                    //             // ];
                    //             $card_mod->addLog($card_id,2,$orders['id']);
                    //         }else{
                    //             $is_true = false;
                    //         }
                    //     }
                    // }

                    //添加订单状态时间表
                    $orders_times = [];
                    $orders_times['orders_id'] = $orders['id'];
                    $orders_times['status'] = $orderStatus['status'];
                    $orders_times['status_time'] = $now_time;

                    $insert_orders_times = Db::name('orders_times')->insert($orders_times);
                    if(!$insert_orders_times){
                        Log::error("[ordersTimesError]订单时间插入失败,info=".json_encode($orders_times));
                    }

                    //给用户发送模板消息
                    $user = Db::name('user')
                        ->where('id',$orders['user_id'])
                        ->where('deleted',0)
                        ->where('disabled',0)
                        ->field('id,gz_openid')
                        ->find();
                    if($user){
                        send_refund_status($user['gz_openid'],$orders['order_number'],$orders['total_price'],$orders['create_time'],$orderStatus['status']);
                    }

                    //处理完成之后，告诉微信成功结果
                    echo '<xml>
                            <return_code><![CDATA[SUCCESS]]></return_code>
                            <return_msg><![CDATA[OK]]></return_msg>
                          </xml>';
                    exit();
                } else {
                    echo '<xml>
                            <return_code><![CDATA[FAIL]]></return_code>
                            <return_msg><![CDATA[FAIL]]></return_msg>
                          </xml>';
                    exit();
                }
            } else {
                echo '<xml>
                        <return_code><![CDATA[SUCCESS]]></return_code>
                        <return_msg><![CDATA[OK]]></return_msg>
                      </xml>';
                exit();
            }
        }else{
            //记录错误日志  $data
            echo '<xml>
                    <return_code><![CDATA[SUCCESS]]></return_code>
                    <return_msg><![CDATA[OK]]></return_msg>
                  </xml>';
            exit();
        }
    }


    // 月卡微信回调
    public function wxpay_card()
    {
        // Db::name('pay_test')->insert(['msg'=>'开始']);
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
            // Db::name('pay_test')->insert(['msg'=>'取到值']);
            //存储接收到的支付回调数据信息
            $notify_id = Db::name('orders_notify')->insertGetId(['content' => $save, 'type' => 2, 'pay_type' => 1]);

            //查找是否有符合要求的订单信息 注意：微信支付回调不是单次，会是多次，所以要特别注意，如果找不到符合条件订单就返回SUCCESS
            $orders = Db::name('month_card')
                ->where('status', 0)
                ->where('card_number',$data['out_trade_no'])
                ->field('id,user_id,card_number,status,total_price')
                ->find();
               // Db::name('pay_test')->insert(['msg'=>Db::name('month_card')->getLastsql()]);
            if (empty($orders)) {

                $params = [
                    'return_code' => 'FAIL',
                    'return_msg' => '月卡订单不存在'
                ];
                echo $wxpay->ArrToXml($params);
                exit();

            }
             // Db::name('pay_test')->insert(['msg'=>'取到订单信息']);
            // 验证签名
            if ($wxpay->checkSign($data) && $orders['total_price'] == $data['total_fee']/100) {
                // Db::name('pay_test')->insert(['msg'=>'开始事务']);
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
                // Db::name('pay_test')->insert(['msg'=>'事务结束']);
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
             // Db::name('pay_test')->insert(['msg'=>'失败']);
            $wxpay->logs('logs.txt', $e->getMessage());
            exit();
        }
    }
    
    //驾校体验券回调
    public function wxpay_train_experience(){
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
            // Db::name('pay_test')->insert(['msg'=>'取到值']);
            //存储接收到的支付回调数据信息
            $notify_id = Db::name('orders_notify')->insertGetId(['content' => $save, 'type' => 2, 'pay_type' => 1]);

            //查找是否有符合要求的订单信息 注意：微信支付回调不是单次，会是多次，所以要特别注意，如果找不到符合条件订单就返回SUCCESS
            $orders = Db::name('user_experience')
                ->where('status', 0)
                ->where('e_number',$data['out_trade_no'])
                ->field('id,user_id,e_number,status,e_price')
                ->find();
            // Db::name('pay_test')->insert(['msg'=>Db::name('month_card')->getLastsql()]);
            if (empty($orders)) {

                $params = [
                    'return_code' => 'FAIL',
                    'return_msg' => '驾校体验券订单不存在'
                ];
                echo $wxpay->ArrToXml($params);
                exit();

            }
            // Db::name('pay_test')->insert(['msg'=>'取到订单信息']);
            // 验证签名
            if ($wxpay->checkSign($data) && $orders['total_price'] == $data['total_fee']/100) {
                // Db::name('pay_test')->insert(['msg'=>'开始事务']);
                // 启动事务
                Db::startTrans();
                try{
                    $update_time = date("Y-m-d H:i:s");
                    //改变订单信息
                    $order_update = [];
                    $order_update['status'] = 1;
                    $order_update['wx_order_code'] = $data['transaction_id'];
                    $order_update['update_time'] = $update_time;
                    $update_res = Db::name('user_experience')
                        ->where('e_number',$data['out_trade_no'])
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
                // Db::name('pay_test')->insert(['msg'=>'事务结束']);
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