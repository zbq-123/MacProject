<?php
/**
 * User: rice
 * Date: 2020/10/28
 * Time: 15:17
 */
namespace app\admin\model;

use think\Model;
use util\listData;

class CouponUser extends Model
{
    public function user(){
        return $this->belongsTo('User','user_id')->field('id,nickname');
    }

    public function coupon(){
        return $this->belongsTo('Coupon','coupon_id')->field('id,type,name,discount_money,condition,full_money,start_time,end_time,seller_id,status,deleted,campus_id');
    }

    //获取列表数据
    public function getListData($maps, $searchFields, $limit, $page){

        if (!empty($searchFields['field_name']) && !empty($searchFields['field_content'])) {
            $key = $searchFields['field_name'];
            $value = $searchFields['field_content'];
            $maps[$key] = ['like', '%' . $value . '%'];
        }
        $maps['is_used'] = 0;
        $maps['seller_id'] = session('admin.store_id');
        $result = $this->with('store')->where($maps)->limit($limit)->page($page)->select();
        $count = $this->where($maps)->count();

        return new listData($result, $count);
    }


    public function editCoupon($data){
        $store_id = session('admin.store_id');
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

    // 根据用户ID获取优惠券信息
    public function get_coupon($user_id){
        $maps['user_id'] = $user_id;
        $maps['is_used'] = 0;
        $result = $this->with('coupon')
                        ->where($maps)
                        ->select();
        
        return $result;
    }

   



}