<?php
namespace app\client\controller;

use app\common\controller\ClientBase;
use app\admin\model\Coupon;
use app\admin\model\CouponUser;
use app\admin\model\Spec;
use think\Db;
use util\Ret;
class Store extends ClientBase
{
    public function index()
    {
        $store_id = input('store_id/d');
        if(empty($store_id)||$store_id <= 0){
            $this->error('未找到店铺相关信息');
        }

        //1 获取店铺信息
        $store = Db::name('store')
            ->where('id',$store_id)
            ->where('deleted',0)
            ->field("id store_id,name,phone,address,detail,campus_id,min_price,start_time1,end_time1,start_time2,end_time2,start_time3,end_time3,logo,delivery_price,delivery_name,box_type,box_price,box_name,status,notice")
            ->find();

        if(empty($store)){
            $this->error('未找到店铺相关信息');
        }

        $campus = Db::name('campus')
            ->where('id',$store['campus_id'])
            ->where('deleted',0)
            ->field('name')
            ->find();

        if(empty($campus)){
            $this->error('该校区暂不支持下单');
        }

        //所属校区
        $store['campus_name'] = $campus['name'];

        //计算营业状态
        $now_time = date('H:i:s', time());
        if($store['status'] == 1 && ($store['start_time1'] < $now_time && $store['end_time1'] > $now_time)){
            $store['open'] = 1;
        }else if($store['status'] == 1 && ($store['start_time2'] < $now_time && $store['end_time2'] > $now_time)){
            $store['open'] = 1;
        }else if($store['status'] == 1 && ($store['start_time3'] < $now_time && $store['end_time3'] > $now_time)){
            $store['open'] = 1;
        }else{
            $store['open'] = 0;
        }

        //计算营业时间是否显示
        if((empty($store['start_time1']) || $store['start_time1'] == '00:00:00') && (empty($store['end_time1']) || $store['end_time1'] == '00:00:00')){
            $store['time1_status'] = 0;
        }else{
            $store['time1_status'] = 1;
        }

        if((empty($store['start_time2']) || $store['start_time2'] == '00:00:00') && (empty($store['end_time2']) || $store['end_time2'] == '00:00:00')){
            $store['time2_status'] = 0;
        }else{
            $store['time2_status'] = 1;
        }

        if((empty($store['start_time3']) || $store['start_time3'] == '00:00:00') && (empty($store['end_time3']) || $store['end_time3'] == '00:00:00')){
            $store['time3_status'] = 0;
        }else{
            $store['time3_status'] = 1;
        }

        //2 获取店铺菜单
        $goods_list = Db::name('goods_category')
            ->where('store_id',$store_id)
            ->where('deleted',0)
            ->field('id category_id,name')
            ->order('sort desc,id asc')
            ->select();
        foreach ($goods_list as &$category){
            $category['goods'] = Db::name('goods')
                ->where('goods_category_id',$category['category_id'])
                ->where('status',1)
                ->where('deleted',0)
                ->field('id goods_id,goods_category_id,name,price,unit,tag,image,sale,stock,is_only_one')
                ->select();
            // 查询规格
            if(!empty($category['goods'])){
                foreach ($category['goods'] as $key => $value){
                    $category['goods'][$key]['spec'] = Spec::get_goods_spec($value['goods_id']);
                    // 是否限购
                    if($value['is_only_one']==1 && session('user.id') != null){
                        
                        $is_only_one = Db::name('orders')->where('find_in_set('.$value['goods_id'].',goods_ids)')->where('user_id',session('user.id'))->where(['status'=>['in','2,3,4,5,7']])->value('id');
                        if($is_only_one){//已购买过一次
                            unset($category['goods'][$key]);
                        }
                    }
                    
                }
            }
         }
       
        //获取平台优惠券
        $Coupon = new Coupon();
        $maps = [];
        $maps['start_time'] = ['<',date("Y-m-d H:i:s",time())];
        $maps['end_time'] = ['>',date("Y-m-d H:i:s",time())];
        $maps['seller_id'] = $store_id;
        $maps['status'] = 1;
        $coupons = $Coupon->where($maps)->field('id,name,discount_money,full_money')->select();
        if($coupons){
            $users = [];
            $user_info = [];
            $CouponUser = new CouponUser();
            foreach ($coupons as $key => $val){
                $user_info = $CouponUser->where(['coupon_id'=>$val['id'],'is_used'=>0])->field('user_id')->select();
                if($user_info){
                    foreach($user_info as $k => $v){
                        $users[] = $v['user_id'];
                    }
                }
                $coupons[$key]['users'] = $users;
            }
        }

        $this->assign('user_id',session('user.id'));
        $this->assign('coupons',$coupons);
        $this->assign('store',$store);
        $this->assign('goods_list',$goods_list);

        return $this->fetch();
    }
    
    // 用户领取优惠券
    public function add_coupon()
    {
        $coupon_id = input('post.coupon_id/d');

        $in_data = [];
        $in_data['user_id'] = session('user.id');
        $in_data['coupon_id'] = $coupon_id;
        $in_data['redeem_time'] = date('Y-m-d H:i:s',time());
        
        $maps = [];
        $maps['user_id'] = $in_data['user_id'];
        $maps['coupon_id'] = $in_data['coupon_id'];
        $maps['is_used'] = 0;
        $coupon = Db::name('coupon_user')->where($maps)->find();
        if($coupon){
            abort(10003,'已领取,不能再重复领取');
            exit;
        }
        
        $res = $address_info = Db::name('coupon_user')->insert($in_data);
    
        if($res){
            abort(10001,'领取成功');
            exit;
        }else{
            abort(10003,'领取失败');
        }
    }
}
