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

class RiderOrder extends Model
{
    public function base($query)
    {
        // return $query->where('deleted', 0);
    }
    public function rider(){
        return $this->belongsTo('Rider','rider_id')->field('id,login_name,real_name');
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
            $maps['order_number'] = ['like', '%' . $value . '%'];
        }

        if (!empty($searchFields['status'])) {
            $maps['status'] = $searchFields['status'];
        }


     if (session('admin.store_id') != null && session('admin.is_root') == 0) {
            $maps['id'] = session('admin.store_id');
        }

        $result = $this->where($maps)->limit($limit)->page($page)->order('update_time desc,create_time desc')->select();

        $count_arr = [];
        $count_arr['ratio'] = SysSetting::where('id',1)->value('wx_ratio');
        $amount = 0;
        if(!empty($result)){
            
            foreach ($result as $key => $value){
                $count_arr['rider_name'] = $value['rider_name'];
                $result[$key]['order'] = Orders::where('id',$value['order_id'])->field('store_name,campus_name,delivery_name,delivery_address,delivery_phone,convey_price')->find();
                $amount += $result[$key]['order']['convey_price'] - $result[$key]['order']['convey_price'] * $count_arr['ratio'];
            }
        }
        $count = $this->where($maps)->count();
        $count_arr['count'] = $count;
        
        if($count_arr['ratio']){
            $count_arr['income'] = number_format($amount,2);
        }
        return new listData($result, $count, '' , '' ,$count_arr);
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