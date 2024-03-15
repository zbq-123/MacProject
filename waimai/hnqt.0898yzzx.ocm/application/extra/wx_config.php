<?php
return [
    //公众号信息
    'weixin_appID' => 'wx6895a741117ce1d8',
    'weixin_appSecret' => '064339b58504d34b78dfb8e1dd2c33ae',

    //微信支付账号
    'wxpay_mchid' => '1490022892',
    'wxpay_appkey' => 'hnxydtxjsfwyxgs23653285850815czh',

    //微信支付回调接口
    'wx_pay_notify' => 'https://hnqt.0898yzzx.com/client/notify/wxpay_goods', //用户支付回调接口

    'wx_refund_notify' => 'https://hnqt.0898yzzx.com/client/notify/wxrefund_goods', //用户退款回调接口

    'uno_url' => 'https://api.mch.weixin.qq.com/pay/unifiedorder', //无需更改 统一下单API地址
];
