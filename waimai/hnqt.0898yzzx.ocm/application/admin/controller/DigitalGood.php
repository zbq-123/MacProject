<?php
/**
 * Created by PhpStorm.
 * User: weitrun
 * Date: 2020/7/9
 * Time: 9:16
 */

namespace app\admin\controller;


use app\admin\model\DigitalGood as DigitalGood_mod;
use app\common\controller\AdminBase;
use think\Db;
use app\admin\model\DigitalCategory;
use app\admin\model\Digital;
use util\Ret;
use app\admin\model\DigitalSpec;
use app\admin\model\DigitalspecGoodsItem;
use app\admin\model\DigitalspecGoods;
use app\admin\model\DigitalspecGoodsPrice;

class DigitalGood extends AdminBase
{
    public function index()
    {
        if (request()->isAjax()) {
            $campus = new DigitalGood_mod();
            $fields = input('param.fields/a');
            $map = [];
            $result = $campus->getListData($map, $fields, $this->limit, $this->page);
            return json($result);
        }
        return $this->fetch();
    }

    //添加
    public function add(){
        if (request()->post()) {
            $data = request()->except(['deleted'], 'post');
            $colum = new DigitalGood_mod();
            $result = $colum->editTrain($data);

            if($result) {
                $goods_id = $colum->getLastInsID();
                
                // 添加规格
                if(isset($data['add_tag']['spec_id1']) && !empty($data['add_tag']['spec_id1'])){
                    $spec_goods_mod = new DigitalspecGoods();
                    $spec_goods_item_mod = new DigitalspecGoodsItem();
                    $spec_goods_price_mod = new DigitalspecGoodsPrice();
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
                $this->success('操作成功', 'index', null, 1);

            } else {
                $this->error($colum->getError());

            }

        }
        else{
//            $supermarket_sort = new SupermarketSort();
//            $res=$supermarket_sort->where('deleted',0)->field('id,name')->select();
//            $this->assign('sort_list',$res);
//            $supermarket = new Supermarket();
//            $res=$supermarket->where('deleted',0)->field('id,name')->select();
//            $this->assign('supermarket_list',$res);
            $spec_mod = Db::name("spec");
            $spec = $spec_mod->select();
            $this->assign('spec',$spec);
            return $this->fetch();
        }
    }

    //删除
    public function delete(){
        $subject =DigitalGood_mod::where(['id'=>input('post.id')])->find();
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

    //修改
    public function edit(){
        if (request()->isPost()){
            $data = request()->except(['deleted'],'post');
            $data['admin_id'] = $this->adminId;
            $column = new DigitalGood_mod();
            $result = $column->editTrain($data);
            if ($result){
                // 规格
                if(isset($data['add_tag']['spec_id1']) && !empty($data['add_tag']['spec_id1'])){
                    $spec_goods_mod = new DigitalspecGoods();
                    $spec_goods_item_mod = new DigitalspecGoodsItem();
                    $spec_goods_price_mod = new DigitalspecGoodsPrice();
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

                $this->success("操作成功", 'index', null ,1);
            }
            else{
                $this->error($column ->getError());
            }
        }else{
            $id = request()->param('id');
            $friendship=DigitalGood_mod::where('id', $id)->find();
            if (empty($friendship)) {
                $this->error("资源不存在");
            }
          
            $res=Db::name('digital')
                ->where('deleted',0)
                ->where('id',$friendship['supermarket_id'])
                ->field('id,name')
                ->find();

            $this->assign('digital',$res);

            $result=Db::name('digital_category')
                ->where('deleted',0)
                ->where('id',$friendship['category_id'])
                ->field('id,name')
                ->find();
            $this->assign('digital_category',$result);
            $spec_mod = Db::name("spec");
            $spec = $spec_mod->select();
            $this->assign('spec',$spec);
            // 查询规格
            $spec_mod = new DigitalSpec();
            $spec_goods = $spec_mod->get_goods_spec($id);
            $this->assign('spec_goods',$spec_goods);
            $this->assign("store", $friendship);
            
            return $this->fetch();
        }

    }


}