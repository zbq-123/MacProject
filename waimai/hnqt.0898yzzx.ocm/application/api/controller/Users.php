<?php
namespace app\api\controller;
use app\admin\model\Goods;
use think\Collection;
use app\admin\model\CouponUser;
use app\admin\model\User;
use app\admin\model\Orders;
use app\admin\model\UserAddress;
use app\admin\model\MonthCard;
use think\Db;
use think\Model;
use think\Validate;



class Users extends Collection{

    //用户优惠券列表
    public function getusercunponlist(){

        $user_id   = input('user_id');
        $campus_id   = input('campus_id');
        $CouponUser = new CouponUser();
        $now_time = date('Y-m-d H:i:s', time());
        $coupons = $CouponUser ->alias('u')
            ->join('coupon c' ,'u.coupon_id = c.id','left')
            ->field('c.id,c.name,c.discount_money,c.full_money,c.seller_id,c.start_time,c.end_time,u.is_used')
            ->where(['u.user_id'=>$user_id,'c.type'=>1,'c.campus_id'=>$campus_id])
            ->where('c.start_time','<=',$now_time)
            ->where('c.end_time','>=',$now_time)
            ->select();
        if(!empty($coupons)){
            foreach ($coupons as $key => $val){
                $coupons[$key]['start_time'] = substr($val['start_time'],0,10);
                $coupons[$key]['end_time'] = substr($val['end_time'],0,10);

            }
            return json(['code'=>200,'msg'=>'操作成功','data'=>$coupons]);
        }
        else{
            return json(['code'=>204,'msg'=>'没有数据']);
        }
    }
    //用户基本信息、消费统计、订单数、订单状态、优惠券统计
    public function getuserallinfo(){
        $user_id   = input('user_id');
        $campus_id   = input('campus_id');
        $user = new User();
        $userinfo = $user
            ->where('id',$user_id)
            ->where('deleted',0)
            ->where('disabled',0)
            ->field('image,nickname,phone')
            ->find();
        if(empty($userinfo)){
            return json(['code'=>204,'msg'=>'账号异常']);
        }
        $userorder  = new Orders();
        //获取用户消费统计
        $userinfo['saleprice'] = $userorder
            ->where('user_id',$user_id)
            ->where('deleted',0)
            ->where('pay_status',2)
            ->sum('total_price');
        //获取用户订单数
        $userinfo['all_order'] = $userorder
            ->where('user_id',$user_id)
            ->where('deleted',0)
            ->where('user_deleted',0)
            ->count();
        //获取待处理订单
        $userinfo['pending_order'] = $userorder
            ->where('user_id',$user_id)
            ->where('deleted',0)
            ->where('user_deleted',0)
            ->where('status','in',[1,2,8,11])
            ->count();

        //获取未使用的优惠券
        $userconpun = new CouponUser();
        $now_time = date('Y-m-d H:i:s', time());
        $userinfo['usercunpun'] = $userconpun ->alias('u')
            ->join('coupon c' ,'u.coupon_id = c.id','left')
            ->field('c.id,c.name,c.discount_money,c.full_money,c.seller_id,c.start_time,c.end_time,u.is_used')
            ->where(['u.user_id'=>$user_id,'c.type'=>1,'c.campus_id'=>$campus_id])
            ->where('c.start_time','<=',$now_time)
            ->where('c.end_time','>=',$now_time)
            ->count();
        //获取未使用的月卡
        $usercard   = new MonthCard();
        $userinfo['usercard'] = $usercard
            ->where('user_id',$user_id)
            ->where('status',1)
            ->count();

        if(empty($userinfo)){
            return json(['code'=>204,'msg'=>'操作失败']);
        }else{
            return json(['code'=>200,'msg'=>'操作成功','data'=>$userinfo]);
        }

    }
    //获取用户地址
    public function getuseraddresslist(){
        $user_id   = input('user_id');
        $useraddress   = new UserAddress();
        $useraddressdata = $useraddress
            ->where('user_id',$user_id)
            ->where('deleted',0)
            ->field("id address_id,gender,delivery_name,delivery_phone,delivery_address,is_default")
            ->order('is_default desc,update_time desc,id desc')
            ->select();
        if(empty($useraddressdata)){
            return json(['code'=>204,'msg'=>'没有数据']);
        }else{
            return json(['code'=>200,'msg'=>'操作成功','data'=>$useraddressdata]);
        }
    }
    //新增用户地址
    public function add_address(){
        $user_id = input('user_id');
        if(empty($user_id)){
            return json(['code'=>204,'msg'=>'用户未登录']);
        }
        $data['delivery_name'] = input('delivery_name');
        $data['gender'] = input('gender');
        $data['delivery_phone'] = input('delivery_phone');
        $data['delivery_address'] = input('delivery_address');
        $data['user_id'] = $user_id;
        $data['is_default'] = input('is_default');

        //判断是否改地址已存在（相同信息地址）
        $is_address = Db::name('user_address')
            ->where('user_id',$user_id)
            ->where('deleted',0)
            ->where($data)
            ->find();


        if($is_address){
            return json(['code'=>204,'msg'=>'该地址已存在']);

        }
        //判断是否已经存在默认地址，存在就取消之前的默认地址
        if($data['is_default'] == 1){
            $is_address_default = Db::name('user_address')
                ->where('user_id',$user_id)
                ->where('deleted',0)
                ->where('is_default',1)
                ->select();

            if($is_address_default){
                $update_default_res = Db::name('user_address')
                    ->where('user_id',$user_id)
                    ->where('deleted',0)
                    ->where('is_default',1)
                    ->update(['is_default'=>0]);

                if(!$update_default_res){
                    return json(['code'=>204,'msg'=>'默认地址已存在']);
                }
            }
        }

        
        $add_address_res = Db::name('user_address')->insert($data);
        if(empty($add_address_res)){
            return json(['code'=>204,'msg'=>'没有数据']);
        }else{
            return json(['code'=>200,'msg'=>'操作成功','data'=>$add_address_res]);
        }

    }
    //修改地址
    public function edit_address()
    {

            $address_id = input('address_id/d');
            $data['delivery_name'] = input('delivery_name');
            $data['gender'] = input('gender/d');
            $data['delivery_phone'] = input('delivery_phone');
            $data['delivery_address'] = input('delivery_address');
            $user_id = input('user_id');
            $data['is_default'] = input('is_default/d');

            $address_info = Db::name('user_address')
                ->where('user_id',$user_id)
                ->where('deleted',0)
                ->where('id',$address_id)
                ->field('id address_id,gender,delivery_name,delivery_phone,delivery_address,is_default')
                ->find();

            if(empty($address_info)){
                return json(['code'=>204,'msg'=>'地址信息不存在']);
            }

            //判断是否改地址已存在（相同信息地址）
            $is_address = Db::name('user_address')
                ->where('user_id',$user_id)
                ->where('deleted',0)
                ->where($data)
                ->find();

            if($is_address){
                if($is_address['id'] == $address_id && $data['is_default'] == $is_address['is_default']){
                    return json(['code'=>204,'msg'=>'未做修改']);

                }else if($is_address['id'] != $address_id){
                    return json(['code'=>204,'msg'=>'该地址已存在']);
                }
            }

            //判断是否已经存在默认地址，存在就取消之前的默认地址
            if($data['is_default'] == 1){
                $is_address_default = Db::name('user_address')
                    ->where('user_id',$user_id)
                    ->where('deleted',0)
                    ->where('is_default',1)
                    ->select();

                if($is_address_default){
                    $update_default_res = Db::name('user_address')
                        ->where('user_id',$user_id)
                        ->where('deleted',0)
                        ->where('is_default',1)
                        ->update(['is_default'=>0]);

                    if(!$update_default_res){
                        return json(['code'=>204,'msg'=>'默认地址已存在']);
                    }
                }
            }

            $edit_address_res = Db::name('user_address')->where('id',$address_id)->update($data);

            if($edit_address_res){
                return json(['code'=>200,'msg'=>'操作成功','data'=>$edit_address_res]);
            }else{
                return json(['code'=>204,'msg'=>'修改地址失败,请重试']);
            }
        
    }
    //显示修改地址
    public function show_edit_address(){
        $address_id = input('get.address_id/d');
        $user_id = input('user_id');
        $validate = new Validate([
            'address_id' => 'require|number|>:0',
        ]);

        if (!$validate->check(input('get.'))) {

            return json(['code'=>204,'msg'=>$validate->getError()]);
        }

        $address_info = Db::name('user_address')
            ->where('user_id',$user_id)
            ->where('deleted',0)
            ->where('id',$address_id)
            ->field('id address_id,gender,delivery_name,delivery_phone,delivery_address,is_default')
            ->find();

        if(!empty($address_info)){
            return json(['code'=>200,'msg'=>'操作成功','data'=>$address_info]);
        }else{
            return json(['code'=>204,'msg'=>'操作失败']);
        }
    }

    //删除地址
    public function del_address()
    {
        $address_id = input('address_id/d');
        if(empty($address_id)){
            return json(['code'=>204,'msg'=>'地址参数有误']);
        }
        $user_id = input('user_id');
        if(empty($user_id)){
            return json(['code'=>204,'msg'=>'用户参数有误']);
        }
        $address_info = Db::name('user_address')
            ->where('user_id',$user_id)
            ->where('deleted',0)
            ->where('id',$address_id)
            ->field('id address_id,gender,delivery_name,delivery_phone,delivery_address,is_default')
            ->find();

        if(empty($address_info)){
            return json(['code'=>204,'msg'=>'地址信息不存在']);
        }

        $edit_address_res = Db::name('user_address')->where('id',$address_id)->update(['deleted'=>1]);

        if(empty($edit_address_res)){
            return json(['code'=>204,'msg'=>'没有数据']);
        }else{
            return json(['code'=>200,'msg'=>'操作成功','data'=>$edit_address_res]);
        }
    }
    //获取用户月卡
    public function getusercardlist(){
        $user_id   = input('user_id');
        if(empty($user_id)){
            return json(['code'=>204,'msg'=>'用户参数有误']);
        }
        $card_mod = new MonthCard();
        $card_info = $card_mod->getMonthCard(['user_id'=>$user_id ]);
        if(empty($card_info)){
            return json(['code'=>204,'msg'=>'没有数据']);
        }else{
            return json(['code'=>200,'msg'=>'操作成功','data'=>$card_info]);
        }
    }
    //获取用户资料
    public function getuserinfo(){
        $user_id   = input('user_id');
        if(empty($user_id)){
            return json(['code'=>204,'msg'=>'用户参数有误']);
        }
        $user = new User();
        $userinfo   = $user
            ->where('id',$user_id)
            ->where('deleted',0)
            ->where('disabled',0)
            ->field('image,nickname,id')
            ->find();
        if(empty($userinfo)){
            return json(['code'=>204,'msg'=>'账号异常']);
        }
        else{
            return json(['code'=>200,'msg'=>'操作成功','data'=>$userinfo]);
        }
    }
    //修改用户资料
    public function edituserinfo(){
        $userid =   input('user_id');
        if(empty($user_id)){
            return json(['code'=>204,'msg'=>'用户参数有误']);
        }
        $userinfo   = [
            'nickname'  => input('nickname'),
            'image'     => input('image'),
            'phone'     => input('phone'),
        ];
        $user = new User();
        $res    = $user->where('id',$userid)->update($userinfo);
        if(!empty($res)){
            return json(['code'=>200,'msg'=>'操作成功','data'=>$res]);
        }else{
            return json(['code'=>204,'msg'=>'修改失败']);
        }

    }
    //获取用户订单
    public function getuserorderlist(){
        $user_id   = input('user_id');
        if(empty($user_id)){
            return json(['code'=>204,'msg'=>'用户参数有误']);
        }
        $orders = new Orders();
        $page = input('page') ? input('page') : 1;
        $orderscount = $orders
            ->where('user_id',$user_id)
            ->where('deleted',0)
            ->where('user_deleted',0)
            ->count();
        $orders_list = $orders
            ->where('user_id',$user_id)
            ->where('deleted',0)
            ->where('user_deleted',0)
            ->field('id,order_number,store_id,store_name,discount_money,order_name,total_price,gender,convey_price,box_price,delivery_name,delivery_phone,delivery_address,cancel_time,status,create_time,count,goods_detail,goods_ids,pay_status')
            ->order('create_time desc,id desc')
            ->limit(10)
            ->page($page)
            ->select();
        if(!empty($orders_list)) {
            foreach ($orders_list as $k => $v) {
//                $goods = Db::name('goods')->where('id', 'in', $v['goods_ids'])->where('deleted', 0)->field('image,price,name,id')->select();
//                if (!empty($goods)) {
//                    foreach ($goods as $key => $val) {
//                        $goods[$key]['price'] = $val['price'] / 100;
//                    }
//                }
                $goods_id=[];
                $buy_goods = explode(",",$v['goods_ids']);
                foreach($buy_goods as $key =>$value){
                    $goods_id[$key]=explode(":",$value);

                }
                $order_goods_detail =  explode('--onelist--', $v['goods_detail']);
                foreach($order_goods_detail as $key =>$val){
                    if(!empty($val)){
                        $order_goods_detail[$key]=explode('--twolist--', $val);
                    }
                }
                $goods=array();
                if(strstr($v['order_name'],'数码产品')){
                    $surface='digital_good';
                    $surface1='digitalspec_goods_price';
                    $store_data=Db::name('digital')->where('id',$v['store_id'])->field('image,phone')->find();
                    if(!empty($store_data)){
                        $orders_list[$k]['logo'] = $store_data['image'];
                        $orders_list[$k]['store_phone'] = $store_data['phone'];
                    }
                    $orders_list[$k]['type']=2;
                  
                }else{
                    $surface='goods';
                    $surface1='spec_goods_price';
                    $store_data=Db::name('store')->where('id',$v['store_id'])->field('logo,phone,name')->find();
                    if(!empty($store_data)){
                        $orders_list[$k]['logo'] = $store_data['logo'];
                        $orders_list[$k]['store_phone'] = $store_data['phone'];
                        $orders_list[$k]['store_name'] = $store_data['name'];
                    }
                    //店铺是否允许退款
                    $orders_list[$k]['is_refund'] = Db::name('store')
                        ->where('id', $v['store_id'])
                        ->field('is_refund')
                        ->find()['is_refund'];
                    $orders_list[$k]['type']=1;

                }
                $order_goods_detail=array_filter($order_goods_detail);
                $order_goods_detail=array_values($order_goods_detail);
                
                foreach($order_goods_detail as $ke =>$va){
                    if($goods_id[$ke][0]==$va[0]){
                        if(!empty($goods_id[$ke][1])){
                            $skudata=Db::name($surface1)->where('id',$goods_id[$ke][1])->find();
                            if(!empty($skudata)){
                                $goods[$ke]['sku']=$skudata['key1'].'-'.$skudata['key2'].'-'.$skudata['key3'].'-'.$skudata['key4'];
                                $goods[$ke]['sku_id']=$goods_id[$ke][1];
                            }
                        }
                    }
                    $goods[$ke]['id']=$va[0];
                    $goods[$ke]['name']=$va[2];
                    $goods[$ke]['number']=$va[3];
                    $goods[$ke]['price']=$va[4]/100;
                    $goodsimage=Db::name($surface)
                        ->where('id',$va[0])
                        ->where('deleted',0)
                        ->field('image')
                        ->find();
                    $goods[$ke]['image']=$goodsimage['image'];
                }
                $orders_list[$k]['goods'] = $goods;
                $orders_list[$k]['total_price'] = $v['total_price'] / 100;
                $orders_list[$k]['convey_price'] = $v['convey_price'] / 100;
                $orders_list[$k]['box_price'] = $v['box_price'] / 100;
                $orders_list[$k]['status_name'] = order_list_status($v['status']);
                //该订单是否支持支付 该订单是否支持取消
                if ($v['status'] == 1) {
                    $orders_list[$k]['is_pay'] = 1;
                    $orders_list[$k]['is_cancel'] = 1;
                } else {
                    $orders_list[$k]['is_pay'] = 0;
                    if ($v['status'] == 2 || ($v['status'] == 3 && $v['cancel_time'] > date("Y-m-d H:i:s"))) {
                        $orders_list[$k]['is_cancel'] = 2;
                    } else if ($v['status'] == 3) {
                        $orders_list[$k]['is_cancel'] = 3;
                    } else {
                        $orders_list[$k]['is_cancel'] = 0;
                    }
                }
                //订单是否支持删除
                if ($v['status'] == 1 || $v['status'] == 7 || $v['status'] == 9 || $v['status'] == 12 || $v['status'] == 14 || $v['status'] == 15) {
                    $orders_list[$k]['is_deleted'] = 1;
                } else {
                    $orders_list[$k]['is_deleted'] = 0;
                }
            }
           
            return json(['code'=>200,'msg'=>'操作成功','orderdata'=>$orders_list,'orderscount'=>$orderscount]);
        }
        else{
            return json(['code'=>204,'msg'=>'没有数据','orderdata'=>0]);
        }
    }



}