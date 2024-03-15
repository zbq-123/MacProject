<?php
namespace app\api\controller;
use app\admin\model\Spec;
use think\Collection;
use app\admin\model\CouponUser;
use app\admin\model\User;
use app\admin\model\Orders;
use app\admin\model\UserAddress;
use app\admin\model\SpecGoodsPrice;
use think\Db;
use think\Model;


class Stores extends Collection{

    //通过店铺id获取店铺信息
    public function getstoreinfo(){
        $store_id = input('store_id/d');
        //1 获取店铺信息
        $store = Db::name('store')
            ->where('id',$store_id)
            ->where('deleted',0)
            ->field("id store_id,name,phone,address,detail,campus_id,min_price,start_time1,end_time1,start_time2,end_time2,start_time3,end_time3,logo,delivery_price,delivery_name,box_type,box_price,box_name,status,notice")
            ->find();

        if(!empty($store)){
            $now_time = date('H:i:s', time());
            $campus = Db::name('campus')
                ->where('id',$store['campus_id'])
                ->where('deleted',0)
                ->field('name')
                ->find();

            if(empty($campus)){
                return json(['code'=>204,'msg'=>'该校区暂不支持下单']);
            }
            $store['min_price'] = $store['min_price']/100;
            $store['delivery_price'] = $store['delivery_price']/100;
            $store['box_price']    =$store['box_price']/100;
            

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
            //是否营业

            if($store['status'] == 1&&!empty($store['start_time1'])) {

                if ($store['start_time1'] < $now_time && $store['end_time1'] > $now_time || $store['start_time2'] < $now_time && $store['end_time2'] > $now_time || $store['start_time3'] < $now_time && $store['end_time3'] > $now_time) {
                    $store['status'] = 1;
                } else {
                    $store['status'] = 2;
                }
            }
            return json(['code'=>200,'msg'=>'操作成功','data'=>$store]);
        }
        else{
            return json(['code'=>204,'msg'=>'没有数据']);
        }
    }
    //通过店铺id获取店铺菜单
    public function getgoodslist(){
        $store_id = input('store_id/d');
       // $campus_id = input('campus_id/d');
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
                ->field('id goods_id,goods_category_id,name,price,unit,tag,image,sale,stock,is_only_one,isrequired')
                ->select();
            // 查询规格
            if(!empty($category['goods'])){
                foreach ($category['goods'] as $key => $value){
                    $category['goods'][$key]['spec'] = $this->getspec($value['goods_id']);
                    $category['goods'][$key]['price'] = $value['price']/100;
                     //是否促销
                    $now_time = date('Y-m-d H:i:s', time());
                    $res    = Db::name('goods_promotion')
                        ->where('good_id',$value['goods_id'])
                        ->where('deleted',0)
                        ->where('start_time','<=',$now_time)
                        ->where('end_time','>=',$now_time)
                        ->field('salesprice,start_time,end_time')
                        ->find();
                   
                    if(!empty($res)){
                        $category['goods'][$key]['salesprice'] = $res['salesprice']/100;
                    }else{
                        $category['goods'][$key]['salesprice'] = '';
                    }
                    // 是否限购
//                    if($value['is_only_one']==1 && session('user.id') != null){
//
//                        $is_only_one = Db::name('orders')->where('find_in_set('.$value['goods_id'].',goods_ids)')->where('user_id',session('user.id'))->where(['status'=>['in','2,3,4,5,7']])->value('id');
//                        if($is_only_one){//已购买过一次
//                            unset($category['goods'][$key]);
//                        }
//                    }

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
        $specgoods  = Db::name('spec_goods')->where('goods_id',$goods_id)->find();
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
                $newspec[$k]['name']    = Db::name('spec_goods_item')
                    ->where('goods_id',$v['goods_id'])
                    ->where('spec_id',$v['spec_id'])
                    ->select();
            }
            return $newspec;
        }else{
            return $specgoods;
        }
    }
    //通过选规格获取商品sku的价格，库存
//    public function getspecprice(){
//
//        $goods_id = input('goods_id');
//        $spec_name = input('spec_name');
//        $spec_name = explode(",", $spec_name);
//
//
//        $maps = [
//            'goods_id' => $goods_id,
//        ];
//        if(!empty($spec_name[0])){
//            $maps['key1'] = $spec_name[0];
//        }
//        if(!empty($spec_name[1])){
//            $maps['key2'] = $spec_name[1];
//        }
//        if(!empty($spec_name[2])){
//            $maps['key3'] = $spec_name[2];
//        }
//        if(!empty($spec_name[3])){
//            $maps['key4'] = $spec_name[3];
//        }
//
//        $res = Db::name('spec_goods_price')->where($maps)->find();
//        if(!empty($res)){
//            return json(['code'=>200,'msg'=>'操作成功','data'=>$res]);
//        }else{
//            return json(['code'=>204,'msg'=>'操作失败','data'=>0]);
//        }
//    }
    public function getsku(){

        $goods_id = input('goods_id');
        $maps = [
            'goods_id' => $goods_id,

        ];
        //$maps['key1'] = '小杯';

        $res = Db::name('spec_goods_price')->where($maps)->select();
        if(!empty($res)){
            foreach($res as $k=>$v){
               // $res[$k]['specname']=$v['key1'].','.$v['key2'].','.$v['key3'].','.$v['key4'];
//                if(!empty($v['key1'])){
//                    $res[$k]['specname']=$v['key1'];
//                    if(!empty($v['key2'])){
//                        $res[$k]['specname']=$v['key1'].'-'.$v['key2'];
//                        if(!empty($v['key3'])){
//                            $res[$k]['specname']=$v['key1'].'-'.$v['key2'].'-'.$v['key3'];
//                            if(!empty($v['key4'])){
//                                $res[$k]['specname']=$v['key1'].'-'.$v['key2'].'-'.$v['key3'].'-'.$v['key4'];
//                            }
//                        }
//                    }
//                }
                $res[$k]['specname']=$v['key1'].'-'.$v['key2'].'-'.$v['key3'].'-'.$v['key4'];
                $res[$k]['specname'] = explode("-", $res[$k]['specname']);
            }
            return json(['code'=>200,'msg'=>'操作成功','data'=>$res]);
        }else{
            return json(['code'=>204,'msg'=>'操作失败','data'=>0]);
        }
    }




}