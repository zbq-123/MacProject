<?php
/**
 * User: rice
 * Date: 2021/7/06
 * Time: 11:14
 */
namespace app\admin\model;

use think\Model;
use util\listData;

class DigitalspecGoodsPrice extends Model{
    
    // public function admin(){
    //     return $this->belongsTo('Admin','admin_id')->field('id,login_name,real_name');
    // }
    
    //后台查询范围
    // protected function scopeStoreList($query){
    //     return $query->field('id,goods_category_id,number,name,store_id,price,unit,tag,image,sale,detail,status,sort,admin_id,create_time,update_time,deleted')
    //         ->order('sort desc,update_time desc');
    // }
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

    public function editGoods($data){

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

}