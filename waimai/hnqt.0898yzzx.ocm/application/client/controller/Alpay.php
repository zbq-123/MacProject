<?php
/**
 * User: RICE
 * Date: 2020/10/28
 */
namespace app\client\controller;

use app\common\controller\ClientBase;
use think\Db;
use think\Log;
use think\Validate;
use util\Ret;
use think\Config;

class Alipay extends ClientBase {

    /**
     * 支付宝支付post提交页面
     */
    public function alipay(){
      if (IS_POST){
        Vendor('Alipay.aop.AopClient');
        Vendor('Alipay.aop.request.AlipayTradeWapPayRequest');
        //$out_trade_no = I('post.order_sn');
        /*
         * $out_trade_no 为自己业务逻辑中要支付的订单号
         *   可从POST数据中提取，具体安全起见可自行加密操作 此处仅举例测试数据
         * $order_amount 为要进行支付的金额 注意要用小数转换
         *   例如：3.50，10.00
         * $aliConfig 获取支付宝配置数据
         */
        $out_trade_no = '2017M'.time();
        $body = '欢迎购买商品，愿您购物愉快';
        $subject = '你好';
        $order_amount = 9.00;
        //$aliConfig = C('ALI_CONFIG');
        $aop = new \AopClient();
        $aop->gatewayUrl = config('ali_config.gatewayUrl');
        $aop->appId = config('ali_config.appId');
        $aop->rsaPrivateKey = config('ali_config.rsaPrivateKey');
        $aop->alipayrsaPublicKey=config('ali_config.alipayrsaPublicKey');
        $aop->apiVersion = '1.0';
        $aop->postCharset='UTF-8';
        $aop->format='json';
        $aop->signType='RSA2';
        $request = new \AlipayTradeWapPayRequest ();
        $bizContent = "{" .
          "  \"body\":\"$body.\"," .
          "  \"subject\":\"$subject\"," .
          "  \"out_trade_no\":\"$out_trade_no\"," .
          "  \"timeout_express\":\"90m\"," .
          "  \"total_amount\":$order_amount," .
          "  \"product_code\":\"QUICK_WAP_WAY\"" .
          " }";
        $request->setBizContent($bizContent);
        $request->setNotifyUrl(config('ali_config.notifyUrl'));
        $request->setReturnUrl(config('ali_config.returnUrl'));
        $result = $aop->pageExecute ( $request);
        echo $result;
      }else{
        echo 'sorry,非法请求失败';
      }
    }


    /**
     * 支付宝支付通知功能
     */
     public function notify_ali(){
       $out_trade_no = I('post.out_trade_no');
       $this->toUpdatePayInfo($out_trade_no,'ali');
       echo 'success';
     }
}