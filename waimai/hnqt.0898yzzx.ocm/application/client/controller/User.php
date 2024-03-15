<?php

namespace app\client\controller;

use app\common\controller\ClientBase;
use app\admin\model\MonthCard;
use PHPMailer\SendEmail;
use think\Db;
use think\Log;
use think\Validate;
use util\Ret;
use util\RetList;
use util\wxBizDataCrypt;

class User extends ClientBase
{
    public function index()
    {
        $user = Db::name('user')
            ->where('id',session('user.id'))
            ->where('deleted',0)
            ->where('disabled',0)
            ->field('image,nickname')
            ->find();

        if(empty($user)){
            exit('账号异常，请联系管理员');
        }

        $user['code'] = encodeUserCode(session('user.id'));

        //获取用户消费
        $user['total_price'] = Db::name('orders')
            ->where('user_id',session('user.id'))
            ->where('deleted',0)
            ->where('pay_status',2)
            ->sum('total_price');

        //获取用户订单数
        $user['all_order'] = Db::name('orders')
            ->where('user_id',session('user.id'))
            ->where('deleted',0)
            ->where('user_deleted',0)
            ->count();

        //获取待处理订单
        $user['pending_order'] = Db::name('orders')
            ->where('user_id',session('user.id'))
            ->where('deleted',0)
            ->where('user_deleted',0)
            ->where('status','in',[1,2,8,11])
            ->count();

        $this->assign('user',$user);

        return $this->fetch();
    }

    public function address_list()
    {
        $address = Db::name('user_address')
            ->where('user_id',session('user.id'))
            ->where('deleted',0)
            ->field("id address_id,gender,delivery_name,delivery_phone,delivery_address,is_default,CASE WHEN(gender=0) THEN '先生' WHEN(gender=1) THEN '女士' ELSE '未知' END AS gender")
            ->order('is_default desc,update_time desc,id desc')
            ->select();

        $this->assign('address',$address);

        return $this->fetch();
    }

    public function address_add()
    {
        if (request()->isAjax()) {
            $data['delivery_name'] = input('name/s');
            $data['gender'] = input('gender/d');
            $data['delivery_phone'] = input('phone/s');
            $data['delivery_address'] = input('address/s');

            $validate = new Validate([
                'name' => 'require|max:50',
                'gender' => 'require|number|in:0,1',
                'phone' => 'require|length:11',
                'address' => 'require|max:100',
                'is_default' => 'require|number|in:0,1',
            ]);

            if (!$validate->check(input('post.'))) {
                abort(10003,$validate->getError());
            }

            //判断是否改地址已存在（相同信息地址）
            $is_address = Db::name('user_address')
                ->where('user_id',session('user.id'))
                ->where('deleted',0)
                ->where($data)
                ->find();

            $data['is_default'] = input('is_default/d');

            if($is_address){
                abort(10003,'该地址已存在');
            }

            //判断是否已经存在默认地址，存在就取消之前的默认地址
            if($data['is_default'] == 1){
                $is_address_default = Db::name('user_address')
                    ->where('user_id',session('user.id'))
                    ->where('deleted',0)
                    ->where('is_default',1)
                    ->select();

                if($is_address_default){
                    $update_default_res = Db::name('user_address')
                        ->where('user_id',session('user.id'))
                        ->where('deleted',0)
                        ->where('is_default',1)
                        ->update(['is_default'=>0]);

                    if(!$update_default_res){
                        abort(10003,'默认地址已存在');
                    }
                }
            }

            $data['user_id'] = session('user.id');

            $add_address_res = Db::name('user_address')->insert($data);

            if($add_address_res){
                return new Ret();
            }else{
                abort(10003,'新增地址失败');
            }


        }else{
            return $this->fetch();
        }
    }

    public function address_edit()
    {
        if (request()->isAjax()) {
            $address_id = input('address_id/d');
            $data['delivery_name'] = input('name/s');
            $data['gender'] = input('gender/d');
            $data['delivery_phone'] = input('phone/s');
            $data['delivery_address'] = input('address/s');

            $validate = new Validate([
                'address_id' => 'require|>:0',
                'name' => 'require|max:50',
                'gender' => 'require|number|in:0,1',
                'phone' => 'require|length:11',
                'address' => 'require|max:100',
                'is_default' => 'require|number|in:0,1',
            ]);

            if (!$validate->check(input('post.'))) {
                abort(10003,$validate->getError());
            }

            $address_info = Db::name('user_address')
                ->where('user_id',session('user.id'))
                ->where('deleted',0)
                ->where('id',$address_id)
                ->field('id address_id,gender,delivery_name,delivery_phone,delivery_address,is_default')
                ->find();

            if(empty($address_info)){
                $this->error('地址信息不存在');
            }

            //判断是否改地址已存在（相同信息地址）
            $is_address = Db::name('user_address')
                ->where('user_id',session('user.id'))
                ->where('deleted',0)
                ->where($data)
                ->find();

            $data['is_default'] = input('is_default/d');

            if($is_address){
                if($is_address['id'] == $address_id && $data['is_default'] == $is_address['is_default']){
                    abort(10003,'未做修改');
                }else if($is_address['id'] != $address_id){
                    abort(10003,'该地址已存在');
                }
            }

            //判断是否已经存在默认地址，存在就取消之前的默认地址
            if($data['is_default'] == 1){
                $is_address_default = Db::name('user_address')
                    ->where('user_id',session('user.id'))
                    ->where('deleted',0)
                    ->where('is_default',1)
                    ->select();

                if($is_address_default){
                    $update_default_res = Db::name('user_address')
                        ->where('user_id',session('user.id'))
                        ->where('deleted',0)
                        ->where('is_default',1)
                        ->update(['is_default'=>0]);

                    if(!$update_default_res){
                        abort(10003,'默认地址已存在');
                    }
                }
            }

            $edit_address_res = Db::name('user_address')->where('id',$address_id)->update($data);

            if($edit_address_res){
                return new Ret();
            }else{
                abort(10003,'修改地址失败,请重试');
            }
        }else{
            $address_id = input('get.address_id/d');

            $validate = new Validate([
                'address_id' => 'require|number|>:0',
            ]);

            if (!$validate->check(input('get.'))) {
                    $this->error($validate->getError());
            }

            $address_info = Db::name('user_address')
                ->where('user_id',session('user.id'))
                ->where('deleted',0)
                ->where('id',$address_id)
                ->field('id address_id,gender,delivery_name,delivery_phone,delivery_address,is_default')
                ->find();

            if(empty($address_info)){
                $this->error('该地址不可编辑');
            }

            $this->assign('address',$address_info);

            return $this->fetch();
        }
    }

    //删除地址
    public function address_del()
    {
        $address_id = input('address_id/d');

        $validate = new Validate([
            'address_id' => 'require|>:0',
        ]);

        if (!$validate->check(input('post.'))) {
            abort(10003,$validate->getError());
        }

        $address_info = Db::name('user_address')
            ->where('user_id',session('user.id'))
            ->where('deleted',0)
            ->where('id',$address_id)
            ->field('id address_id,gender,delivery_name,delivery_phone,delivery_address,is_default')
            ->find();

        if(empty($address_info)){
            $this->error('地址信息不存在');
        }

        $edit_address_res = Db::name('user_address')->where('id',$address_id)->update(['deleted'=>1]);

        if($edit_address_res){
            return new Ret();
        }else{
            abort(10003,'删除失败，请重试');
        }
    }

    //下单时选中地址
    public function address_select()
    {
        $address_id = input('post.address_id/d');

        $validate = new Validate([
            'address_id' => 'require|>:0',
        ]);

        if (!$validate->check(input('post.'))) {
            abort(10003,$validate->getError());
        }

        $address_info = Db::name('user_address')
            ->where('user_id',session('user.id'))
            ->where('deleted',0)
            ->where('id',$address_id)
            ->field('id address_id,gender,delivery_name,delivery_phone,delivery_address,is_default')
            ->find();

        if(empty($address_info)){
            $this->error('地址信息不存在');
        }

        session('user.address_id',$address_id);

        if(session('user.address_id')){
            return new Ret();
        }else{
            abort(10003,'选择收货地址失败，请重试');
        }
    }
    
    // 我的优惠券
    public function youhui_list()
    {
        //获取平台优惠券
        $coupons = Db::name('coupon_user')->alias('u')
                    ->join('coupon c' ,'u.coupon_id = c.id','left')
                    ->field('c.id,c.name,c.discount_money,c.full_money,c.seller_id,c.start_time,c.end_time,u.is_used')
                    ->where(['u.user_id'=>session('user.id'),'c.type'=>1])
                    ->select();
        if(!empty($coupons)){
            foreach ($coupons as $key => $val){
                $coupons[$key]['start_time'] = substr($val['start_time'],0,10);
                $coupons[$key]['end_time'] = substr($val['end_time'],0,10);
                if($coupons[$key]['end_time']<date('Y-m-d')){
                    unset($coupons[$key]);
                }
            }
        }

        $this->assign('coupons',$coupons);
       
        return $this->fetch();
    }

    // 我的月卡
    public function my_card(){
        $card_mod = new MonthCard();
        $card_info = $card_mod->getMonthCard(['user_id'=>session('user.id')]);
        
        $this->assign('card_info',$card_info);
        return $this->fetch();
    }
}