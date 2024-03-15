<?php
/**
 * User: rice
 * Date: 2020/10/28
 * Time: 15:17
 */
namespace app\admin\model;

use think\Model;
use util\listData;

class Coupon extends Model
{
    public function store(){
        return $this->belongsTo('Store','seller_id')->field('id,name');
    }

    //获取列表数据
    public function getListData($maps, $searchFields, $limit, $page){

        if (!empty($searchFields['field_name']) && !empty($searchFields['field_content'])) {
            $key = $searchFields['field_name'];
            $value = $searchFields['field_content'];
            $maps[$key] = ['like', '%' . $value . '%'];
        }
        $maps['deleted'] = 0;
        if(session('admin.is_root') == 0){
            $maps['seller_id'] = session('admin.store_id');
        }
        $result = $this->with('store')->where($maps)->limit($limit)->page($page)->select();
        $count = $this->where($maps)->count();

        return new listData($result, $count);
    }


    public function editCoupon($data){
        $store_id = session('admin.is_root')==1 ? 0 : session('admin.store_id');
        $data['seller_id'] = $store_id ? $store_id : 0;
        if (!isset($data['id']) || empty($data['id'])) {
            $data['code'] = md5(uniqid(mt_rand(), true));
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
   
    // 根据ID查优惠券信息 rice
    public function get_coupon($coupon_id){
        $maps['id'] = $coupon_id;
        $maps['deleted'] = 0;
        $maps['status'] = 1;        
        return $result = $this->where($maps)->find();
       
    }



}