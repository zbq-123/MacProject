<?php
/**
 * User: rice
 * Date: 2021/7/06
 * Time: 11:14
 */
namespace app\admin\model;

use think\Model;
use util\listData;

class DigitalSpec extends Model{

    public function base($query)
    {
        return $query->where('deleted', 0);
    }

    public function admin(){
        return $this->belongsTo('Admin','admin_id')->field('id,login_name,real_name');
    }

    //获取数据库中的字段对应数据
    protected function scopecampusName($query)
    {
        return $query->field('id,name,deleted,update_time');
    }
    //获取列表数据
    public function getListData($maps, $searchFields, $limit, $page){

        if (!empty($searchFields['field_name']) && !empty($searchFields['field_content'])) {
            $key = $searchFields['field_name'];
            $value = $searchFields['field_content'];
            $maps[$key] = ['like', '%' . $value . '%'];
        }
        $result = $this->order('order desc')->where($maps)->limit($limit)->page($page)->select();
        $count = $this->where($maps)->count();
        return new listData($result, $count);
    }
    public function editTrain($data){
        if (!isset($data['id']) || empty($data['id'])) {
                $result = $this->validate(true)->allowField(true)->data($data)->save();
                if($result) {
                    return true;
                }else {
                    return false;
                }
        }else {
            $id = $data['id'];
            $portal = $this->where('id', $id)->find();
            $result = $portal->validate(true)->allowField(true)->except('id')->data($data)->save();
            if ($result) {
                return true;
            }else {
                return false;
            }
        }
    }
    // 查询规格
    static function get_goods_spec($goods_id){
        $spec_goods_mod = new DigitalspecGoods();
        $spec_goods_item_mod = new DigitalspecGoodsItem();
        $spec_goods_price_mod = new DigitalspecGoodsPrice();
        $spec_goods = $spec_goods_mod->where('goods_id',$goods_id)->find();
        if($spec_goods){
            $items = [];
            // 规格1
            if($spec_goods['spec_id_1']) 
                $items[$spec_goods['spec_id_1']] = $spec_goods_item_mod->where(['spec_id'=>$spec_goods['spec_id_1'],'goods_id'=>$goods_id])->select();

            // 规格2
            if($spec_goods['spec_id_2'])
                $items[$spec_goods['spec_id_2']]= $spec_goods_item_mod->where(['spec_id'=>$spec_goods['spec_id_2'],'goods_id'=>$goods_id])->select();

            // 规格3
            if($spec_goods['spec_id_3'])
                $items[$spec_goods['spec_id_3']]= $spec_goods_item_mod->where(['spec_id'=>$spec_goods['spec_id_3'],'goods_id'=>$goods_id])->select();

            // 规格4
            if($spec_goods['spec_id_4'])
                $items[$spec_goods['spec_id_4']]= $spec_goods_item_mod->where(['spec_id'=>$spec_goods['spec_id_4'],'goods_id'=>$goods_id])->select();
            
            $spec_goods['items'] = $items;
            // 规格价格
            $spec_goods['price'] = $spec_goods_price_mod->where('goods_id',$goods_id)->select();
        }
        
        return $spec_goods;
    }

}