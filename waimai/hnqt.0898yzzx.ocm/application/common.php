<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2016 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 流年 <liu21st@gmail.com>
// +----------------------------------------------------------------------

// 应用公共文件
use think\Db;
use think\Log;
use util\WxTemplateMsg;

/**
 * 检查是否缺少请求参数
 * @param array $param
 * @return bool
 */
function missParam(array $param = [])
{
    foreach($param as $key => $value){
        if (empty($value) && $value !== 0 && $value !== '0'){
            abort(601, '缺少请求参数'.$key);
        }
    }
    return true;
}


//创建订单编号
function build_order_no()
{
    //订单号码主体（YYYYMMDDHHIISSNNNNNNNN）
    $order_id_main = date('YmdHis') . rand(10000000, 99999999);
    //订单号码主体长度
    $order_id_len = strlen($order_id_main);
    $order_id_sum = 0;
    for ($i = 0; $i < $order_id_len; $i++) {
        $order_id_sum += (int)(substr($order_id_main, $i, 1));
    }
    //唯一订单号码（YYYYMMDDHHIISSNNNNNNNNCC）
    $order_id = $order_id_main . str_pad((100 - $order_id_sum % 100) % 100, 2, '0', STR_PAD_LEFT);
    return $order_id;
}

//创建订单退款编号
function build_order_refund_no()
{
    //订单号码主体（YYYYMMDDHHIISSNNNNNNNN）
    $order_id_main = date('YmdHis') . rand(10000000, 99999999);
    //订单号码主体长度
    $order_id_len = strlen($order_id_main);
    $order_id_sum = 0;
    for ($i = 0; $i < $order_id_len; $i++) {
        $order_id_sum += (int)(substr($order_id_main, $i, 1));
    }
    //唯一订单号码（YYYYMMDDHHIISSNNNNNNNNCC）
    $order_id = 'tk'.$order_id_main . str_pad((100 - $order_id_sum % 100) % 100, 2, '0', STR_PAD_LEFT);
    return $order_id;
}

//用户标识码
function encodeUserCode($user_id) {
    static $source_string = '1DE5FCZIG3HQAB1NOPJ2RST4UV67MX89KLYW';
    $num = $user_id;
    $code = '';
    while ( $num > 0) {
        $mod = $num % 35;
        $num = ($num - $mod) / 35;
        $code = $source_string[$mod].$code;
    }
    if(empty($code[3]))
        $code = str_pad($code,6,'0',STR_PAD_LEFT);
    return $code;
}


//解码用户标识码
function decodeUserCode($code) {
    static $source_string = '1DE5FCZIG3HQAB1NOPJ2RST4UV67MX89KLYW';
    if (strrpos($code, '0') !== false)
        $code = substr($code, strrpos($code, '0')+1);
    $len = strlen($code);
    $code = strrev($code);
    $user_id = 0;
    for ($i=0; $i < $len; $i++) {
        $user_id += strpos($source_string, $code[$i]) * pow(35, $i);
    }
    return $user_id;
}

//获取订单状态编码对应订单状态文字
function order_status($status){
    $order_text = "";
    switch ($status) {
        case 1:
            $order_text = '订单提交成功';
            break;
        case 2:
            $order_text = '订单已支付';
            break;
        case 3:
            $order_text = '商家已接单';
            break;
//            case 4:$order_status['text'] = '配送中';$order_status['style'] = 'color:#ff813d;';break;
//            case 6:$order_status['text'] = '配送失败';$order_status['style'] = 'color: red;';break;
        case 7:
            $order_text = '订单已完成';
            break;
        case 15:
            $order_text = '商家拒接单';
            break;
        case 8:
            $order_text = '取消订单，申请退款';
            break;
        case 9:
            $order_text = '退款成功';
            break;
        case 10:
            $order_text = '退款失败';
            break;
        case 11:
            $order_text = '商家发起退款';
            break;
        case 12:
            $order_text = '退款成功';
            break;
        case 13:
            $order_text = '退款失败';
            break;
        case 14:
            $order_text = '订单已取消';
            break;
        case 15:
            $order_text = '商家拒单';
            break;
        default:
            $order_text = '未知状态';
            break;
    }

    return $order_text;
}

//获取订单状态编码对应订单状态文字
function order_list_status($status){
    $order_text = "";
    switch ($status) {
        case 1:
            $order_text = '待支付';
            break;
        case 2:
            $order_text = '待接单';
            break;
        case 3:
            $order_text = '配送中';
            break;
//            case 4:$order_status['text'] = '配送中';$order_status['style'] = 'color:#ff813d;';break;
//            case 6:$order_status['text'] = '配送失败';$order_status['style'] = 'color: red;';break;
        case 7:
            $order_text = '已完成';
            break;
        case 8:
            $order_text = '取消中';
            break;
        case 9:
            $order_text = '已退款';
            break;
        case 10:
            $order_text = '未退款';
            break;
        case 11:
            $order_text = '退款中';
            break;
        case 12:
            $order_text = '已退款';
            break;
        case 13:
            $order_text = '未退款';
            break;
        case 14:
            $order_text = '已取消';
            break;
        case 15:
            $order_text = '商家取消';
            break;
        default:
            $order_text = '未知状态';
            break;
    }

    return $order_text;
}

/*
发送 下单成功提醒 模板消息 status=1
---------------------------------------- 外卖单下单成功提醒 start -----------------------------------------
模板ID：
yb1v4a5yV4yJhsuRpdgCcUCgozw7eGcwESjUVsgpO3E

详细内容：
{{first.DATA}}
餐厅：{{keyword1.DATA}}
下单时间：{{keyword2.DATA}}
菜品：{{keyword3.DATA}}
金额：{{keyword4.DATA}}
{{remark.DATA}}

示例：
您好，您的外卖单下单成功
餐厅:添添聚源味石美店
下单时间:2014-8-20 11:45:20
菜品:农家小炒肉 1份
金额:20元
约35分钟送到，请备好零钞。

---------------------------------------- 外卖单下单成功提醒 end -----------------------------------------
*/
function send_add_success($gz_openid,$order_number,$store_name,$create_time,$order_describe,$total_price){
    $first = '您好，您的订单已提交成功';
    $remark = '点击即可查看订单详情、支付订单';
    $template = array(
        'touser'=>$gz_openid,
        'template_id'=>"vFgJ7TtiG6p91zSx2ibSf5wr5ay-bo4TSzuEni94OSc",
        'url'=>config('sys_config.client_orders_detail').$order_number,
        'data'=>array(
            'first'=>array('value'=>urlencode($first),'color'=>"#000000"),
            'keyword1'=>array('value'=>urlencode($store_name),'color'=>'#0000FF'),
            'keyword2'=>array('value'=>urlencode($create_time),'color'=>'#0000FF'),
            'keyword3'=>array('value'=>urlencode($order_describe),'color'=>'#0000FF'),
            'keyword4'=>array('value'=>urlencode(($total_price/100).'元'),'color'=>'#0000FF'),
            'remark'=>array('value'=>urlencode($remark),'color'=>'#FF0000'), )
    );

    $wxMsg = new WxTemplateMsg();
    $result = $wxMsg->sendMsg($template);


    if($result['errcode'] == 0){
        return true;
    }else{
        Log::error('[sendAddSuccessError]openid:' . $gz_openid . ';order_number:'.$order_number.';res:' . json_encode($result));
        return false;
    }
}


/*
发送 下单成功提醒 模板消息 status=1
---------------------------------------- 外卖即将到达提醒 start -----------------------------------------
模板ID：
bnEhIneCXmRD1qxU9gflu1kQDb8N82aj2UqZqgrZ7Is

详细内容：
{{first.DATA}}
外卖店铺：{{keyword1.DATA}}
实时状态信息：{{keyword2.DATA}}
送餐员电话：{{keyword3.DATA}}
{{remark.DATA}}

示例：
尊敬的小明会员
外卖店铺：标点时代
实时状态信息：预计十分钟后到达公寓楼下
送餐员电话：130xxxx8888
如有需求，请点击“我的”，查找当前订单送餐员电话，进行沟通。

---------------------------------------- 外卖即将到达提醒 end -----------------------------------------
*/
function send_rider_success($gz_openid,$order_number,$store_name,$rider_phone){
    $first = '您好，您的订单已骑手已接单，派送中！';
    $remark = '如有需求，请点击“我的”，查找当前订单送餐员电话，进行沟通。';
    $template = array(
        'touser'=>$gz_openid,
        'template_id'=>"bqcuM-88ftPVirC_KMUgvFmzgp-eqIMts3s79whsQi0",
        'url'=>config('sys_config.client_orders_detail').$order_number,
        'data'=>array(
            'first'=>array('value'=>urlencode($first),'color'=>"#000000"),
            'keyword1'=>array('value'=>urlencode($store_name),'color'=>'#0000FF'),
            'keyword2'=>array('value'=>urlencode('预计十分钟后到达公寓楼下'),'color'=>'#0000FF'),
            'keyword3'=>array('value'=>urlencode($rider_phone),'color'=>'#0000FF'),
            'remark'=>array('value'=>urlencode($remark),'color'=>'#FF0000'), )
    );

    $wxMsg = new WxTemplateMsg();
    $result = $wxMsg->sendMsg($template);


    if($result['errcode'] == 0){
        return true;
    }else{
        Log::error('[sendAddSuccessError]openid:' . $gz_openid . ';order_number:'.$order_number.';res:' . json_encode($result));
        return false;
    }
}


/*
发送 支付成功通知 模板消息 status=2
-------------------------------------------- 支付成功通知 start --------------------------------------------
模板ID：
NE0A-wU0Qc71CMBNBd2_EOlv4MM5l3Jr1_qeK6SVGEc

详细内容：
{{first.DATA}}
订单编号：{{keyword1.DATA}}
消费金额：{{keyword2.DATA}}
消费门店：{{keyword3.DATA}}
消费时间：{{keyword4.DATA}}
{{remark.DATA}}

示例：
您好，您的微信支付已成功
订单编号：123456789901234567
消费金额：28.88元
消费门店：一元超市
消费时间：2016年4月8日17:00:00
点击累计消费金额

-------------------------------------------- 支付成功通知 end --------------------------------------------
*/
function send_pay_success($gz_openid,$order_number,$total_price,$store_name,$pay_time){
    $first = '您好，您的订单支付成功';
    $remark = '请等待店家确认订单';
    $template = array(
        'touser'=>$gz_openid,
        'template_id'=>"G6TaUGqCXvIa36csUkr6eM2CuZqVIAojogvNKzD_y_A",
        'url'=>config('sys_config.client_orders_detail').$order_number,
        'data'=>array(
            'first'=>array('value'=>urlencode($first),'color'=>"#000000"),
            'keyword1'=>array('value'=>urlencode($order_number),'color'=>'#0000FF'),
            'keyword2'=>array('value'=>urlencode(($total_price/100).'元'),'color'=>'#0000FF'),
            'keyword3'=>array('value'=>urlencode($store_name),'color'=>'#0000FF'),
            'keyword4'=>array('value'=>urlencode($pay_time),'color'=>'#0000FF'),
            'remark'=>array('value'=>urlencode($remark),'color'=>'#FF0000'), )
    );

    $wxMsg = new WxTemplateMsg();
    $result = $wxMsg->sendMsg($template);


    if($result['errcode'] == 0){
        return true;
    }else{
        Log::error('[sendPaySuccessError]openid:' . $gz_openid . ';order_number:'.$order_number.';res:' . json_encode($result));
        return false;
    }
}

/*
发送 订单状态更新通知 模板消息 status=3,7,14
------------------------------------------ 订单状态更新通知 start -----------------------------------------
模板ID：
vFrBMRr_bFzO-nlfVbBYUUjwI6cwCEstgEv5UXAC-OY

详细内容：
{{first.DATA}}
订单编号：{{keyword1.DATA}}
订单金额：{{keyword2.DATA}}
付款方式：{{keyword3.DATA}}
配送方式：{{keyword4.DATA}}
订单状态：{{keyword5.DATA}}
{{remark.DATA}}

示例：
您有一个新订单需要出库
订单编号：P2014121226540
订单金额：365.78
付款方式：货到付款
配送方式：门店配送
订单状态：待出库
送货上门前请先与客户联系！

------------------------------------------ 订单状态更新通知 end -----------------------------------------
*/
function send_orders_status($gz_openid,$order_number,$total_price,$pay_type,$order_status){
    $first = '您好，您的订单状态已更新';
    $remark = '如有疑问，点击查看订单、联系客服';
    $template = array(
        'touser'=>$gz_openid,
        'template_id'=>"sdFv1JWqX5PFBZIvVXPyrdvZCgI0q_e5rVwiqjiYwGw",
        'url'=>config('sys_config.client_orders_detail').$order_number,
        'data'=>array(
            'first'=>array('value'=>urlencode($first),'color'=>"#000000"),
            'keyword1'=>array('value'=>urlencode($order_number),'color'=>'#0000FF'),
            'keyword2'=>array('value'=>urlencode(($total_price/100).'元'),'color'=>'#0000FF'),
            'keyword3'=>array('value'=>urlencode($pay_type),'color'=>'#0000FF'),
            'keyword4'=>array('value'=>urlencode('店家配送'),'color'=>'#0000FF'),
            'keyword5'=>array('value'=>urlencode($order_status),'color'=>'#0000FF'),
            'remark'=>array('value'=>urlencode($remark),'color'=>'#FF0000'), )
    );

    $wxMsg = new WxTemplateMsg();
    $result = $wxMsg->sendMsg($template);


    if($result['errcode'] == 0){
        return true;
    }else{
        Log::error('[sendOrdersStatusError]openid:' . $gz_openid . ';order_number:'.$order_number.';res:' . json_encode($result));
        return false;
    }
}

/*
发送 退款进度通知 模板消息 status=8,9,10,11,12,13,15
------------------------------------------ 退款进度通知 start --------------------------------------------
模板ID：
CuDD2UANGYqWe8wyvH4fG2P9kVDroB7cpmfgU22ruL4

详细内容：
{{first.DATA}}
订单编号：{{keyword1.DATA}}
订单金额：{{keyword2.DATA}}
下单时间：{{keyword3.DATA}}
{{remark.DATA}}

示例：
您在必胜客（中山公园店）的订单退款申请被通过，钱款将在1-7工作日内退还至您的支付账户，请耐心等待。
订单编号：201500001
订单金额：123
下单时间：20:15
点击可查看更多信息&gt;

-------------------------------------------- 退款进度通知 end --------------------------------------------
*/
function send_refund_status($gz_openid,$order_number,$total_price,$create_time,$status){
    switch ($status){
        case 8:
            $first = '您好，您的订单退款申请已提交';
            $remark = '退款申请已提交，等待店家确认，请耐心等待';
            break;
        case 9:
            $first = '您好，您的订单退款已成功';
            $remark = '退款申请已成功，钱款将会退还至您的支付账户，请耐心等待';
            break;
        case 10:
            $first = '您好，您的订单退款申请失败';
            $remark = '退款申请失败，如有疑问，点击查看订单、联系客服';
            break;
        case 11:
            $first = '您好，您有订单被店家发起退款申请';
            $remark = '该订单被店家发起了退款申请，如有疑问，点击查看订单、联系客服';
            break;
        case 12:
            $first = '您好，您的订单退款已成功';
            $remark = '退款申请已成功，钱款将会退还至您的支付账户，请耐心等待';
            break;
        case 13:
            $first = '您好，您的订单退款申请失败';
            $remark = '退款申请失败，如有疑问，点击查看订单、联系客服';
            break;
        case 15:
            $first = '您好，您有订单被取消已退款';
            $remark = '您的订单被店家取消，钱款将会退还至您的支付账户，请耐心等待';
            break;
        default:
            $first = '退款进度通知';
            $remark = '点击查看订单详情';
            break;
    }
    $template = array(
        'touser'=>$gz_openid,
        'template_id'=>"RwoaKVoB-_w0wJYb6o6mtgqc6e96KFPADgkaEizAKi0",
        'url'=>config('sys_config.client_orders_detail').$order_number,
        'data'=>array(
            'first'=>array('value'=>urlencode($first),'color'=>"#000000"),
            'keyword1'=>array('value'=>urlencode($order_number),'color'=>'#0000FF'),
            'keyword2'=>array('value'=>urlencode(($total_price/100).'元'),'color'=>'#0000FF'),
            'keyword3'=>array('value'=>urlencode($create_time),'color'=>'#0000FF'),
            'remark'=>array('value'=>urlencode($remark),'color'=>'#FF0000'), )
    );

    $wxMsg = new WxTemplateMsg();
    $result = $wxMsg->sendMsg($template);


    if($result['errcode'] == 0){
        return true;
    }else{
        Log::error('[sendRefundStatusError]openid:' . $gz_openid . ';order_number:'.$order_number.';res:' . json_encode($result));
        return false;
    }
}

/*
 * 微信支付申请原路退回方法
 *
 * 创建退款申请订单号示例：$refund_order_code = build_order_refund_no();
 * wx_refund($wx_order_code=微信支付返回订单号,$order_number=自己平台订单号,$total_price=订单金额(单位分),$refund_order_code=自己平台退款单号,$status=订单状态)
 * $status = 8,11,15
 */
function wx_refund($wx_order_code,$order_number,$total_price,$refund_order_code,$status){

    switch ($status){
        case 2:
            $refund_reason = '用户发起退款';
            break;
        case 8:
            $refund_reason = '用户申请退款';
            break;
        case 11:
            $refund_reason = '商家申请退款';
            break;
        case 15:
            $refund_reason = '商家拒绝接单退款';
            break;
        default:
            $refund_reason = '系统退款';
            break;
    }
    //导入微信支付类库
    import('WxPayApi', EXTEND_PATH . '/WxpayAPI_php_v3/lib/', '.php');

    //调用统一退款接口
    $input = new \WxPayRefund();

    $input->SetTransaction_id($wx_order_code);
    $input->SetOut_trade_no($order_number);
    $input->SetTotal_fee($total_price);
    $input->SetRefund_fee($total_price);
    $input->SetOut_refund_no($refund_order_code);
    $input->SetRefund_desc($refund_reason);
    $input->SetOp_user_id(config('wx_config.wxpay_mchid'));
    $input->SetNotify_url(config('wx_config.wx_refund_notify'));//异步回调地址

    $result = \WxPayApi::refund($input);//调用微信API提交数据并取得返回

    //判断退款s申请是否成功
    if ($result['return_code'] == 'SUCCESS' && $result['result_code'] == 'SUCCESS') {
        //增加本地数据
        $refund_record = [
            'refund_order_code' => $refund_order_code,
            'total_price' => $total_price,
            'status' => $status,
            'wx_order_code' => $wx_order_code,
            'order_number' => $order_number,
            'pay_type' => 1
        ];
        $record_insert_res = Db::name('refund_payment_record')
            ->insert($refund_record);
        if (!$record_insert_res){
            Log::error('[wxRefundError]insert fail res:' . json_encode($refund_record));
        }

        return true;

    } else {
        Log::error('[wxRefundError]request fail res:' . json_encode($result));

        return false;
    }


    function qrcode($url='',$filename='',$level='L',$size=10){
        Vendor('phpqrcode.phpqrcode');
        //容错级别
        $errorCorrectionLevel = intval($level);
        //生成图片大小
        $matrixPointSize = intval($size);
        //生成二维码图片
        $object = new \QRcode();
        //第二个参数false的意思是不生成图片文件，如果你写上‘picture.png’则会在根目录下生成一个png格式的图片文件
        $object->png($url, $filename, $errorCorrectionLevel, $matrixPointSize, 2);
    }
}
