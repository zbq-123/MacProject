<?php
namespace app\api\controller;
use alipay\Wappay;
use think\Collection;
use think\Db;
use think\Validate;
use util\WxPay;

class Supermarket extends Collection{

    //商超列表
    public function get_supermarket_list(){
        $campus_id = input('campus_id');
        if(empty($campus_id)){
            return json(['code'=>201,'msg'=>'参数有误']);
        }
        $res=Db::name('supermarket')
            ->where('deleted',0)
            ->where('campus_id',$campus_id)
            ->select();
        if(!empty($res)){
            return json(['code'=>200,'msg'=>'操作成功','data'=>$res]);
        }else{
            return json(['code'=>204,'msg'=>'没有数据']);
        }
    }
    //商超下的商品
    public function get_supermarket_good(){
        $supermarket_id = input('supermarket_id');
        if(empty($supermarket_id)){
            return json(['code'=>201,'msg'=>'参数有误']);
        }
        $goods_list = Db::name('supermarket_sort')
            ->where('supermarket_id',$supermarket_id)
            ->where('deleted',0)
            ->field('id,name')
            ->order('id asc')
            ->select();

        if(!empty($goods_list)){
            foreach($goods_list as $k=>$v){
                $goods_list[$k]['goods'] = Db::name('supermarket_good')
                    ->where('category_id',$v['id'])
                    ->where('status',1)
                    ->where('deleted',0)
                    ->field('id,name,category_id,price,unit,tag,image,sale,stock')
                    ->select();
            }
            return json(['code'=>200,'msg'=>'操作成功','data'=>$goods_list]);
        }else{
            return json(['code'=>204,'msg'=>'没有数据']);
        }
    }

    public function wxpay_supermarket_goods(){
        $supermarket_id = input('post.supermarket_id/d');
        $buy_goods = input('post.buy_goods/s');
        $buy_number = input('post.buy_number/s');
        $address_id = input('post.address_id/d');
        $user_id = input('post.user_id/d');
        $remake = input('post.remake');
        $validate = new Validate([
            'supermarket_id' => 'require|number|>:0',
            'buy_goods' => 'require',
            'buy_number' => 'require',
            'address_id' => 'require|number|>:0',
            'user_id' => 'require|number|>:0',
        ]);
        if (!$validate->check(input('post.'))) {
            return json(['code'=>201,'msg'=>'参数错误，请重试']);
        }
        $goods_ids = $buy_goods;
        $buy_goods = explode(",",$buy_goods);
        $buy_number = explode(",",$buy_number);
        if(count($buy_goods) != count($buy_number)){
            return json(['code'=>202,'msg'=>'数据异常，请重试']);
        }
        //1 获取用户收货地址信息
        $address = Db::name('user_address')
            ->where('user_id',$user_id)
            ->where('deleted',0)
            ->where('id',$address_id)
            ->field('id address_id,gender,delivery_name,delivery_phone,delivery_address')
            ->find();
        if(empty($address)){
            return json(['code'=>203,'msg'=>'地址不可用']);
        }
        //2 获取下单商品信息
        $supermarket = Db::name('supermarket')
            ->where('id',$supermarket_id)
            ->where('deleted',0)
            ->field("id,name")
            ->find();
        if(empty($supermarket)){
            return json(['code'=>204,'msg'=>'商超不存在']);
        }

    }

}