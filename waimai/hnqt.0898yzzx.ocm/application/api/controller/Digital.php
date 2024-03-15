<?php
namespace app\api\controller;
use app\admin\model\Spec;
use think\Collection;
use app\admin\model\CouponUser;
use app\admin\model\Digital as Digital_mod;
use app\admin\model\Orders;
use app\admin\model\UserAddress;
use app\admin\model\SpecGoodsPrice;
use think\Db;
use think\Model;


class Digital extends Collection{
    //数码商家列表
    public function get_digital()
    {


        $campus_id = input('campus_id');
        if(empty($campus_id)){
            return json(['code'=>201,'msg'=>'参数有误']);
        }
        $limit = 10;
        $digital = new Digital_mod();
        $order = new Orders();
        $page = input('page')?input('page'):1;
        $name =  input('name');

        if(!empty($name)){
            $digitaldata = Db::name('digital') ->alias('s')
                ->join('digital_good g' ,'s.id = g.store_id','left')
                ->field('s.id,s.image,s.name,s.campus_id')
                ->where( 's.name|g.name','like', '%' . $name . '%' )
                ->where('s.campus_id', $campus_id)
                ->where('s.deleted', 0)
                ->where('g.deleted', 0)
                ->order('s.id asc')
                ->group('s.id')
                ->limit($limit)
                ->page($page)
                ->select();
            $digitalcount = Db::name('digital') ->alias('s')
                ->join('digital_good g' ,'s.id = g.store_id','left')
                ->field('s.id,s.image,s.name,s.campus_id')
                ->where( 's.name|g.name','like', '%' . $name . '%' )
                ->where('s.campus_id', $campus_id)
                ->where('s.deleted', 0)
                ->where('g.deleted', 0)
                ->group('s.id')
                ->count();

        }else{
            $digitaldata = $digital
                ->where('campus_id', $campus_id)
                ->where('deleted', 0)
                ->field("id,image,name,campus_id")
                ->order('id asc')
                ->limit($limit)
                ->page($page)
                ->select();
            $digitalcount = $digital
                ->where('campus_id', $campus_id)
                ->where('deleted', 0)
                ->field("id,image,name")
                ->order('id asc')
                ->count();
        }
        if (!empty($digitaldata)) {
            foreach ($digitaldata as &$store_item) {
                //计算订单量
                $store_item['sale'] = $order
                    ->where('store_id', $store_item['id'])
                    ->where('deleted', 0)
                    ->where('status', 7)
                    ->count();
            }
            return json(['code'=>200,'msg'=>'操作成功','data'=>$digitaldata,'count'=>$digitalcount]);
        }
        else {
            return json(['code' => 204, 'msg' => '没有数据']);
        }

    }
    //获取商家信息
    public function get_digital_info(){
        $store_id = input('store_id/d');
        if(empty($store_id)){
            return json(['code'=>201,'msg'=>'参数有误']);
        }
        //1 获取店铺信息
        $store = Db::name('digital')
            ->where('id',$store_id)
            ->where('deleted',0)
            ->field("id store_id,name,address,image,campus_id")
            ->find();

        if(!empty($store)){
            //$now_time = date('Y-m-d H:i:s', time());
            $campus = Db::name('campus')
                ->where('id',$store['campus_id'])
                ->where('deleted',0)
                ->field('name')
                ->find();

            if(empty($campus)){
                return json(['code'=>204,'msg'=>'该校区暂不支持下单']);
            }
            //所属校区
            $store['campus_name'] = $campus['name'];
            //已售
            $store['sale']=Db::name('orders')
                ->where('store_id',$store_id)
                ->where('pay_status',2)
                ->where('status',7)
                ->where('deleted',0)
                ->where('user_deleted',0)
                ->count();
            
            return json(['code'=>200,'msg'=>'操作成功','data'=>$store]);
        }
        else{
            return json(['code'=>204,'msg'=>'没有数据']);
        }
    }
    //通过店铺id获取商家商品
    public function get_digitalgoods_list(){
        $store_id = input('store_id/d');
        if(empty($store_id)){
            return json(['code'=>201,'msg'=>'参数有误']);
        }
       // $campus_id = input('campus_id/d');
        $goods_list = Db::name('digital_category')
            ->where('supermarket_id',$store_id)
            ->where('deleted',0)
            ->field('id category_id,name')
            ->order('id asc')
            ->select();
        foreach ($goods_list as &$category){
            $category['goods'] = Db::name('digital_good')
                ->where('category_id',$category['category_id'])
                ->where('status',1)
                ->where('deleted',0)
                ->field('id goods_id,category_id,name,price,image,sale,stock')
                ->select();
            // 查询规格
            if(!empty($category['goods'])){
                foreach ($category['goods'] as $key => $value){
                    $category['goods'][$key]['spec'] = $this->getspec($value['goods_id']);
                }
            }
        }
        if(!empty($goods_list)){
            return json(['code'=>200,'msg'=>'操作成功','data'=>$goods_list]);
        }
        else{
            return json(['code'=>204,'msg'=>'没有数据']);
        }

    }
    //组装商品规格
    public function getspec($goods_id){
        $specgoods  = Db::name('digitalspec_goods')->where('goods_id',$goods_id)->find();
        if(!empty($specgoods)){
            $newspec    = array();
            if(!empty($specgoods)){
                for($i=1; $i<=4; $i++){
                    if(!empty($specgoods['spec_id_'.$i.''])){
                        $newspec[$specgoods['spec_id_'.$i.'']]['goods_id']  =$specgoods['goods_id'];
                        $newspec[$specgoods['spec_id_'.$i.'']]['spec_id']   =$specgoods['spec_id_'.$i.''];
                        $newspec[$specgoods['spec_id_'.$i.'']]['spec_name'] =$specgoods['spec_name_'.$i.''];
                    }
                }
            }
            foreach($newspec as $k=>$v){
                $newspec[$k]['name']    = Db::name('digitalspec_goods_item')
                    ->where('goods_id',$v['goods_id'])
                    ->where('spec_id',$v['spec_id'])
                    ->select();
            }
            return $newspec;
        }else{
            return $specgoods;
        }
    }

    public function getsku(){
        $goods_id = input('goods_id');
        $maps = [
            'goods_id' => $goods_id,

        ];
        $res = Db::name('digitalspec_goods_price')->where($maps)->select();
        if(!empty($res)){
            foreach($res as $k=>$v){

                $res[$k]['specname']=$v['key1'].'-'.$v['key2'].'-'.$v['key3'].'-'.$v['key4'];
                $res[$k]['specname'] = explode("-", $res[$k]['specname']);
            }
            return json(['code'=>200,'msg'=>'操作成功','data'=>$res]);
        }else{
            return json(['code'=>204,'msg'=>'操作失败','data'=>0]);
        }
    }




}