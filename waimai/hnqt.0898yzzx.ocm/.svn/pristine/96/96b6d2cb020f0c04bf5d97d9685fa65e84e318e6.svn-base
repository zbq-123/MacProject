<?php
/**
 * Created by PhpStorm.
 * User: weitrun
 * Date: 2020/7/9
 * Time: 9:16
 */
namespace app\admin\controller;
use app\admin\model\Admin;
use app\admin\model\Campus;
use app\admin\model\Goods;
use app\admin\model\GoodsCategory;
use app\admin\model\Spec;
use app\admin\model\Store as store_mod;
use app\admin\model\SpecGoods;
use app\admin\model\SpecGoodsItem;
use app\admin\model\SpecGoodsPrice;
use app\common\controller\AdminBase;
use util\Ret;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use think\Db;
class Store extends AdminBase{
    public function store(){
        if(request()->isAjax() ){
            $store = new \app\admin\model\Store();
            $fields = input('param.fields/a');
            $map =[];
          /*  if (!empty($fields['navigation_id'])) {
                $map['navigation_id'] = $fields['navigation_id'];
            }*/
            /* $map['admin_id'] = $this->adminId;*/
            $result = $store ->getListData($map,$fields,$this->limit,$this->page);

            return json($result);
        }
        $admin = session('admin');
        $this->assign('is_root',$admin['is_root']);

       
        return $this ->fetch();
    }
    //添加店铺
    public function add_store(){
        if (request()->post()) {
            $data = request()->except(['deleted'], 'post');
            $data['min_price'] =  $data['min_price']*100;
            $data['delivery_price'] =  $data['delivery_price']*100;
            $data['box_price'] =  $data['box_price']*100;

            $data['logo'] = input('post.logo');
            $colum = new \app\admin\model\Store();
            $result = $colum->editStore($data);
            if ($result) {
                $this->success('操作成功', 'store/store', null, 1);

            } else {
                $this->error($colum->getError());

            }
        }
        else{
            return $this->fetch();
        }
    }

    //删除商品
    public function delete_store(){
        $subject =\app\admin\model\Store::where(['id'=>input('post.id')])->find();
        if (false == $subject) {
            abort(608, '资源不存在');
        }
        $subject->deleted = 1;
        $result = $subject->save();
        if ($result) {
            return json(new Ret());
        } else {
            abort(608);
        }
    }

    //状态修改
    public function status($id, $status)
    {
        $store = \app\admin\model\Store::get($id);
       if ($store['status'] == 1){
             $store->status = 2;
           $res = $store->save();

           if ($res) {
               return json(new Ret($store['status']));
           } else {
               abort(608);
           }

       }
       else{
          $store->status = 1;
           $res = $store->save();
           if ($res) {
               return json(new Ret($store['status']));
           } else {
               abort(608);
           }
       }
    }
    //允许退款状态修改
    public function is_refund($id, $status)
    {
        $store = \app\admin\model\Store::get($id);
       if ($status == 1){
             $store->is_refund = 1;
           $res = $store->save();
           if ($res) {
               return json(new Ret($store['is_refund']));
           } else {
               abort(608);
           }

       }
       else{
          $store->is_refund = 0;
           $res = $store->save();
           if ($res) {
               return json(new Ret($store['is_refund']));
           } else {
               abort(608);
           }
       }
    }
    //设置置顶序号
    public function edit_sort()
    {
        $media = \app\admin\model\Store::get(input('post.id'));
        $media->sort = input('post.sort/d');
        $result = $media->save();
        if ($result) {
            return json(new Ret());
        } else {
            abort(608);
        }
    }
    //设置置顶序号
    public function edit_sort1()
    {
        $media = \app\admin\model\Goods::get(input('post.id'));
        $media->sort = input('post.sort/d');
        $result = $media->save();
        if ($result) {
            return json(new Ret());
        } else {
            abort(608);
        }
    }

    //一键上下架
    public function on_off_goods()
    {
        print_r(input('post.status/d'));exit;
        // $media->status = input('post.status/d');
        // $result = $media->save();
        // if ($result) {
        //     return json(new Ret());
        // } else {
        //     abort(608);
        // }
    }
    //修改店铺
    public function edit_store(){
        if (request()->isPost()){
            $data = request()->except(['deleted'],'post');
            $data['admin_id'] = $this->adminId;
            $data = request()->except(['deleted'], 'post');
            $data['min_price'] =  $data['min_price']*100;
            $data['delivery_price'] =  $data['delivery_price']*100;
            $data['box_price'] =  $data['box_price']*100;

            $data['logo'] = input('post.logo');

            $column = new \app\admin\model\Store();

            $result = $column->editStore($data);
            if ($result){
                $this->success("操作成功", 'store/store', null ,1);
            }
            else{
                $this->error($column ->getError());}
        }else{
            $id = request()->param('id');
            $friendship=\app\admin\model\Store::where('id', $id)->find();
            if (empty($friendship)) {
                $this->error("资源不存在");
            }
            $friendship['min_price'] =  $friendship['min_price']/100;
            $friendship['delivery_price'] =  $friendship['delivery_price']/100;
            $friendship['box_price'] =  $friendship['box_price']/100;
            $this->assign("store", $friendship);
            $place = Campus::where('deleted', 0)->order('id asc')->field('id,name')->select();
            if ( session('admin.is_root') == 0){
                $this->assign('root', 1);
            }
            else{
                $this->assign('root', 2);
            }
            $this->assign('campus', $place);
            return $this->fetch();
        }

    }
   //商品列表
    public function goods(){
        $store_id = session('admin.store_id');
        $this->assign('store_id',  $store_id);

        $category_sift  = new GoodsCategory();
        if(request()->isAjax() ){
            $store = new \app\admin\model\Goods();
            $fields = input('param.fields/a');
            $map =[];
            /*  if (!empty($fields['navigation_id'])) {
                  $map['navigation_id'] = $fields['navigation_id'];
              }*/
            /* $map['admin_id'] = $this->adminId;*/
            $result = $store ->getListData($map,$fields,$this->limit,$this->page);
            return json($result);
        }

        return $this ->fetch();
    }
    //添加商品
    public function add_goods(){
        $store_id = session('admin.id');
        $this->assign('store_id',  $store_id);
        if (request()->post()) {
            $data = request()->except(['deleted'], 'post');
            $data['logo'] = input('post.logo');
            $colum = new Goods();
            $result = $colum->editGoods($data);
            if ($result) {
                $goods_id = $colum->getLastInsID();
                // 添加规格
                if(isset($data['add_tag']['spec_id_1']) && !empty($data['add_tag']['spec_id_1'])){
                   $spec_goods_mod = new SpecGoods();
                   $spec_goods_item_mod = new SpecGoodsItem();
                   $spec_goods_price_mod = new SpecGoodsPrice();

                   // 规格商品
                   $spec_id_1 =  explode('-',$data['add_tag']['spec_id1']);
                   $spec_id_2 = explode('-',$data['add_tag']['spec_id2']);
                   $spec_id_3 = explode('-',$data['add_tag']['spec_id3']);
                   $spec_id_4 = explode('-',$data['add_tag']['spec_id4']);

                   $specgoodsdata['spec_id_1'] = !empty($spec_id_1[0]) ? $spec_id_1[0] : '';
                   $specgoodsdata['spec_id_2'] = !empty($spec_id_2[0]) ? $spec_id_2[0] : '';
                   $specgoodsdata['spec_id_3'] = !empty($spec_id_3[0]) ? $spec_id_3[0] : '';
                   $specgoodsdata['spec_id_4'] = !empty($spec_id_4[0]) ? $spec_id_4[0] : '';
                   $specgoodsdata['spec_name_1'] = !empty($spec_id_1[1]) ? $spec_id_1[1] : '';
                   $specgoodsdata['spec_name_2'] = !empty($spec_id_2[1]) ? $spec_id_2[1] : '';
                   $specgoodsdata['spec_name_3'] = !empty($spec_id_3[1]) ? $spec_id_3[1] : '';
                   $specgoodsdata['spec_name_4'] = !empty($spec_id_4[1]) ? $spec_id_4[1] : '';
                   $specgoodsdata['goods_id'] = $goods_id;
                   $spec_goods_mod->validate(true)->allowField(true)->isUpdate(false)->data($specgoodsdata)->save();

                   if(!empty($data['add_tag']['goods_spec'])){
                      $goods_spec = $data['add_tag']['goods_spec'];
                      foreach ($goods_spec as $key => $value){
                           // 规格项
                           // key1
                           if(!empty($specgoodsdata['spec_id_1']) && !empty($value['key1'])){
                             $specgoodsitemdata['spec_id'] = $specgoodsdata['spec_id_1'];
                             $specgoodsitemdata['item'] = $value['key1'];
                             $specgoodsitemdata['goods_id'] = $goods_id;

                            //查询  
                            $key1info = $spec_goods_item_mod->where($specgoodsitemdata)->find();
                            if(!$key1info){
                                $spec_goods_item_mod->validate(true)->allowField(true)->isUpdate(false)->data($specgoodsitemdata)->save();
                            }
                           }
                           // key2
                           if(!empty($specgoodsdata['spec_id_2']) && !empty($value['key2'])){
                               $specgoodsitemdata['spec_id'] = $specgoodsdata['spec_id_2'];;
                               $specgoodsitemdata['item'] = $value['key2'];
                               $specgoodsitemdata['goods_id'] = $goods_id;

                               //查询
                               $key2info = $spec_goods_item_mod->where($specgoodsitemdata)->find();
                               if(!$key2info){
                                 $spec_goods_item_mod->validate(true)->allowField(true)->isUpdate(false)->data($specgoodsitemdata)->save();
                               }
                           }
                           // key3
                           if(!empty($specgoodsdata['spec_id_3']) && !empty($value['key3'])){
                               $specgoodsitemdata['spec_id'] = $specgoodsdata['spec_id_3'];;
                               $specgoodsitemdata['item'] = $value['key3'];
                               $specgoodsitemdata['goods_id'] = $goods_id;

                               //查询
                               $key3info = $spec_goods_item_mod->where($specgoodsitemdata)->find();
                               if(!$key3info){
                                 $spec_goods_item_mod->validate(true)->allowField(true)->isUpdate(false)->data($specgoodsitemdata)->save();
                               }
                           }
                           // key4
                           if(!empty($specgoodsdata['spec_id_4']) && !empty($value['key4'])){
                               $specgoodsitemdata['spec_id'] = $specgoodsdata['spec_id_4'];;
                               $specgoodsitemdata['item'] = $value['key4'];
                               $specgoodsitemdata['goods_id'] = $goods_id;

                               //查询
                               $key4info = $spec_goods_item_mod->where($specgoodsitemdata)->find();
                               if(!$key4info){
                                 $spec_goods_item_mod->validate(true)->allowField(true)->isUpdate(false)->data($specgoodsitemdata)->save();
                               }
                           }

                           // 规格价格
                           $specgoodspricedata['goods_id'] = $goods_id;
                           $specgoodspricedata['key1'] = $value['key1'];
                           $specgoodspricedata['key2'] = $value['key2'];
                           $specgoodspricedata['key3'] = $value['key3'];
                           $specgoodspricedata['key4'] = $value['key4'];
                           $specgoodspricedata['price'] = $value['price'];
                           $specgoodspricedata['store_count'] = $value['store_count'];
                           
                           $spec_goods_price_mod->validate(true)->allowField(true)->isUpdate(false)->data($specgoodspricedata)->save();
                      }
                   }
                }
                $this->success('操作成功', 'store/goods', null, 1);

            } else {
                $this->error($colum->getError());

            }
        }
        else{
            // 规格
            $spec_mod = Db::name("spec");
            $spec = $spec_mod->select();
            $this->assign('spec',$spec);

            // 店铺
            $is_root = session('admin.is_root');
            if(!empty($is_root)){
              $store_list = store_mod::where('deleted',0)->field('id,name')->select();
              $this->assign('store_list',$store_list);
            }
            $this->assign('is_root',$is_root);

            
            return $this->fetch();
        }
    }
    //删除商品
    public function delete_goods(){
        $subject =Goods::where(['id'=>input('post.id')])->find();
        if (false == $subject) {
            abort(608, '资源不存在');
        }
        $subject->deleted = 1;
        $result = $subject->save();
        if ($result) {
            return json(new Ret());
        } else {
            abort(608);
        }
    }
    //修改商品
    public function edit_goods(){
        $this->assign('store_id', session('admin.store_id'));
        if (request()->isPost()){
            $data = request()->except(['deleted'],'post');
            $data['admin_id'] = $this->adminId;
            $column = new Goods();

            $result = $column->editGoods($data);
            if ($result){
                // 规格
                if(isset($data['add_tag']['spec_id1']) && !empty($data['add_tag']['spec_id1'])){
                   $spec_goods_mod = new SpecGoods();
                   $spec_goods_item_mod = new SpecGoodsItem();
                   $spec_goods_price_mod = new SpecGoodsPrice();
                   $goods_id = $data['goods_id'];

                   // 删除旧数据
                   $spec_goods_mod->where('goods_id',$goods_id)->delete();
                   $spec_goods_item_mod->where('goods_id',$goods_id)->delete();
                   $spec_goods_price_mod->where('goods_id',$goods_id)->delete();

                   // 规格商品
                   $spec_id_1 =  explode('-',$data['add_tag']['spec_id1']);
                   $spec_id_2 = explode('-',$data['add_tag']['spec_id2']);
                   $spec_id_3 = explode('-',$data['add_tag']['spec_id3']);
                   $spec_id_4 = explode('-',$data['add_tag']['spec_id4']);

                   $specgoodsdata['spec_id_1'] = !empty($spec_id_1[0]) ? $spec_id_1[0] : '';
                   $specgoodsdata['spec_id_2'] = !empty($spec_id_2[0]) ? $spec_id_2[0] : '';
                   $specgoodsdata['spec_id_3'] = !empty($spec_id_3[0]) ? $spec_id_3[0] : '';
                   $specgoodsdata['spec_id_4'] = !empty($spec_id_4[0]) ? $spec_id_4[0] : '';
                   $specgoodsdata['spec_name_1'] = !empty($spec_id_1[1]) ? $spec_id_1[1] : '';
                   $specgoodsdata['spec_name_2'] = !empty($spec_id_2[1]) ? $spec_id_2[1] : '';
                   $specgoodsdata['spec_name_3'] = !empty($spec_id_3[1]) ? $spec_id_3[1] : '';
                   $specgoodsdata['spec_name_4'] = !empty($spec_id_4[1]) ? $spec_id_4[1] : '';
                   $specgoodsdata['goods_id'] = $goods_id;
                   $spec_goods_mod->validate(true)->allowField(true)->isUpdate(false)->data($specgoodsdata)->save();

                   if(!empty($data['add_tag']['goods_spec'])){
                      $goods_spec = $data['add_tag']['goods_spec'];
                      foreach ($goods_spec as $key => $value){
                           // 规格项
                           // key1
                           if(!empty($specgoodsdata['spec_id_1']) && !empty($value['key1'])){
                             $specgoodsitemdata['spec_id'] = $specgoodsdata['spec_id_1'];
                             $specgoodsitemdata['item'] = $value['key1'];
                             $specgoodsitemdata['goods_id'] = $goods_id;

                            //查询  
                            $key1info = $spec_goods_item_mod->where($specgoodsitemdata)->find();
                            if(!$key1info){
                                $spec_goods_item_mod->validate(true)->allowField(true)->isUpdate(false)->data($specgoodsitemdata)->save();
                            }
                           }
                           // key2
                           if(!empty($specgoodsdata['spec_id_2']) && !empty($value['key2'])){
                               $specgoodsitemdata['spec_id'] = $specgoodsdata['spec_id_2'];;
                               $specgoodsitemdata['item'] = $value['key2'];
                               $specgoodsitemdata['goods_id'] = $goods_id;

                               //查询
                               $key2info = $spec_goods_item_mod->where($specgoodsitemdata)->find();
                               if(!$key2info){
                                 $spec_goods_item_mod->validate(true)->allowField(true)->isUpdate(false)->data($specgoodsitemdata)->save();
                               }
                           }
                           // key3
                           if(!empty($specgoodsdata['spec_id_3']) && !empty($value['key3'])){
                               $specgoodsitemdata['spec_id'] = $specgoodsdata['spec_id_3'];;
                               $specgoodsitemdata['item'] = $value['key3'];
                               $specgoodsitemdata['goods_id'] = $goods_id;

                               //查询
                               $key3info = $spec_goods_item_mod->where($specgoodsitemdata)->find();
                               if(!$key3info){
                                 $spec_goods_item_mod->validate(true)->allowField(true)->isUpdate(false)->data($specgoodsitemdata)->save();
                               }
                           }
                           // key4
                           if(!empty($specgoodsdata['spec_id_4']) && !empty($value['key4'])){
                               $specgoodsitemdata['spec_id'] = $specgoodsdata['spec_id_4'];;
                               $specgoodsitemdata['item'] = $value['key4'];
                               $specgoodsitemdata['goods_id'] = $goods_id;

                               //查询
                               $key4info = $spec_goods_item_mod->where($specgoodsitemdata)->find();
                               if(!$key4info){
                                 $spec_goods_item_mod->validate(true)->allowField(true)->isUpdate(false)->data($specgoodsitemdata)->save();
                               }
                           }

                           // 规格价格
                           $specgoodspricedata['goods_id'] = $goods_id;
                           $specgoodspricedata['key1'] = $value['key1'];
                           $specgoodspricedata['key2'] = $value['key2'];
                           $specgoodspricedata['key3'] = $value['key3'];
                           $specgoodspricedata['key4'] = $value['key4'];
                           $specgoodspricedata['price'] = $value['price'];
                           $specgoodspricedata['store_count'] = $value['store_count'];
                           $spec_goods_price_mod->validate(true)->allowField(true)->isUpdate(false)->data($specgoodspricedata)->save();
                      }
                   }
               }
                $this->success("操作成功", 'store/goods', null ,1);
            }
            else{
                $this->error($column ->getError());}
        }else{
            $id = request()->param('id');
            $friendship=Goods::where('id', $id)->find();
            if (empty($friendship)) {
                $this->error("资源不存在");
            }
            $friendship['price'] =  $friendship['price']/100;
            $this->assign("goods", $friendship);

            $store_id = empty($friendship['store_id']) ? session('admin.store_id') : $friendship['store_id'];
            $place = GoodsCategory::where('deleted', 0)->where('store_id',$store_id)->order('id asc')->field('id,name')->select();
        
            $this->assign('category', $place);
            
            $spec_mod = new Spec();
            $spec = $spec_mod->select();
            $this->assign('spec',$spec);

            // 查询规格
            $spec_goods = $spec_mod->get_goods_spec($id);
            $this->assign('spec_goods',$spec_goods);

            // 店铺
            $is_root = session('admin.is_root');
            if(!empty($is_root)){
              $store_list = store_mod::where('deleted',0)->field('id,name')->select();
              $this->assign('store_list',$store_list);
            }
            $this->assign('is_root',$is_root);

            return $this->fetch();
        }

    }
    //商品分类
    public function category(){

        if(request()->isAjax() ){
            $store = new GoodsCategory();
            $fields = input('param.fields/a');
            $map =[];

            $result = $store ->getListData($map,$fields,$this->limit,$this->page);
            return json($result);
        }

        return $this ->fetch();
    }
    //添加分类
    public function add_category(){

        if (request()->post()) {
            $data = request()->except(['deleted'], 'post');


            $colum = new GoodsCategory();
            $result = $colum->editCategory($data);
            if ($result) {
                $this->success('操作成功', 'store/category', null, 1);

            } else {
                $this->error($colum->getError());

            }
        }
        else{
          // 店铺
            $is_root = session('admin.is_root');
            if(!empty($is_root)){
              $store_list = store_mod::where('deleted',0)->field('id,name')->select();
              $this->assign('store_list',$store_list);
            }
            $this->assign('is_root',$is_root);
            return $this->fetch();
        }
    }
    //删除分类
    public function delete_category(){
        $subject =GoodsCategory::where(['id'=>input('post.id')])->find();
        if (false == $subject) {
            abort(608, '资源不存在');
        }
        $subject->deleted = 1;
        $result = $subject->save();
        if ($result) {
            return json(new Ret());
        } else {
            abort(608);
        }
    }
    //修改分类
    public function edit_category(){

        if (request()->isPost()){
            $data = request()->except(['deleted'],'post');
            $data['admin_id'] = $this->adminId;
            $column = new GoodsCategory();

            $result = $column->editCategory($data);
            if ($result){
                $this->success("操作成功", 'store/category', null ,1);
            }
            else{
                $this->error($column ->getError());}
        }else{
            $id = request()->param('id');
            $friendship=GoodsCategory::where('id', $id)->find();
            if (empty($friendship)) {
                $this->error("资源不存在");
            }
            $this->assign("goods", $friendship);
            $place = GoodsCategory::where('deleted', 0)->where('store_id',session('admin.store_id'))->order('id asc')->field('id,name')->select();
            $this->assign('category', $place);
            // 店铺
            $is_root = session('admin.is_root');
            if(!empty($is_root)){
              $store_list = store_mod::where('deleted',0)->field('id,name')->select();
              $this->assign('store_list',$store_list);
            }
            $this->assign('is_root',$is_root);
            return $this->fetch();
        }

    }
    //设置置顶序号
    public function edit_sort_category()
    {
        $media = GoodsCategory::get(input('post.id'));
        $media->sort = input('post.sort/d');
        $result = $media->save();
        if ($result) {
            return json(new Ret());
        } else {
            abort(608);
        }
    }


    //是否必填修改
    public function isrequired($id, $isrequired)
    {
        $res = Db::name('goods')->where('id',$id)->update(['isrequired'=>$isrequired]);
        if ($res) {
            return json(new Ret($isrequired));
        } else {
            abort(608);
        }

    }

    public function rule(){
        return $this->fetch();
    }




}