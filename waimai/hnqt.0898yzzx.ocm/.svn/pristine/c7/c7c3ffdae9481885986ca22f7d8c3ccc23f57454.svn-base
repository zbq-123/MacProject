<?php
/**
 * Created by PhpStorm.
 * User: weitrun
 * Date: 2020/7/9
 * Time: 9:17
 */
namespace app\admin\model;

use think\Model;
use util\listData;

class RiderIncomeLog extends Model
{
    public function base($query)
    {
        // return $query->where('deleted', 0);
    }
    public function admin(){
        return $this->belongsTo('Admin','admin_id')->field('id,login_name,real_name');
    }
    public function order(){
        return $this->belongsTo('Orders','order_id','id')->field('id,store_id,store_name,campus_name,delivery_name,delivery_address,delivery_phone');
    }
    //后台查询范围
    // protected function scopeStoreList($query){
    //     return $query->field('id,notice,status,balance,revenue,bank_card,bank_info,bank_card_name,ali_card,ali_name,name,store_category_ids,update_time,phone,address,campus_id,detail,min_price,start_time1,end_time1,start_time2,end_time2,start_time3,logo,delivery_price,delivery_name,box_type,box_price,box_name,manage_ratio,order_cancel_time,sort,admin_id,create_time,update_time,deleted,is_refund')
    //         ->order('sort desc,update_time desc');
    // }

    // protected function scopeStoreName($query){
    //     return $query->field('id,name,deleted');
    // }

    //获取列表数据
    public function getListData($maps, $searchFields, $limit, $page){

        if (!empty($searchFields['field_name']) && !empty($searchFields['field_content'])) {
            $key = $searchFields['field_name'];
            $value = $searchFields['field_content'];
            $maps[$key] = ['like', '%' . $value . '%'];
        }

        if (!empty($searchFields['status'])) {
            $maps['status'] = $searchFields['status'];
        }


     if (session('admin.store_id') != null && session('admin.is_root') == 0) {
            $maps['id'] = session('admin.store_id');
        }

        $result = $this::with('admin')->where($maps)->limit($limit)->page($page)->order('create_time desc')->select();
        $count = $this->where($maps)->count();
      
        return new listData($result, $count);
    }
   
    public function editRider($data){


        // $data['wx_code'] = $data['wx_code'];
        $data['update_time'] = date('Y-m-d H:i:s',time());
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

            unset($data['id']);

            $result = $portal->validate(true)->allowField(true)->except('id')->data($data)->save();
            if ($result) {
                return true;
            }else {
                return false;
            }
        }
    }

}