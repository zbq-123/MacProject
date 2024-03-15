<?php
//优惠券 RICE
namespace app\client\controller;

use app\common\controller\ClientBase;
use think\Db;
use think\Log;
use think\Validate;
use util\Ret;

class Coupon extends ClientBase
{
    public function index()
    {
       
    }

    // 用户领取优惠券
    public function add_coupon()
    {
        $coupon_id = input('post.coupon_id/d');

        $validate = new Validate([
            'coupon_id' => 'require|>:0',
        ]);

        if (!$validate->check(input('post.'))) {
            abort(10003,$validate->getError());
        }
        $in_data = [];
        $in_data['user_id'] = session('user.id');
        $in_data['coupon_id'] = $coupon_id;
        $in_data['coupon_id'] = $coupon_id;
        $in_data['redeem_time'] = date('Y-m-d H:i:s',time());

        $res = $address_info = Db::name('coupon_user')->insert($in_data);
    
        if($res){
            return new Ret();
        }else{
            abort(10003,'领取失败');
        }
    }
   
}