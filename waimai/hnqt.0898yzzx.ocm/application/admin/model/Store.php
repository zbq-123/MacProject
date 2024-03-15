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

class Store extends Model
{
    public function base($query)
    {
        return $query->where('deleted', 0);
    }
    public function admin(){
        return $this->belongsTo('Admin','admin_id')->field('id,login_name,real_name');
    }
    public function campus(){
        return $this->belongsTo('Campus','campus_id')->field('id,name');
    }
    //后台查询范围
    protected function scopeStoreList($query){
        return $query->field('id,notice,status,balance,revenue,bank_card,bank_info,bank_card_name,ali_card,ali_name,name,store_category_ids,update_time,phone,address,campus_id,detail,min_price,start_time1,end_time1,start_time2,end_time2,start_time3,logo,delivery_price,delivery_name,box_type,box_price,box_name,manage_ratio,order_cancel_time,sort,admin_id,create_time,update_time,deleted,is_refund')
            ->order('sort desc,update_time desc');
    }

    protected function scopeStoreName($query){
        return $query->field('id,name,deleted');
    }

    //获取列表数据
    public function getListData($maps, $searchFields, $limit, $page){

        if (!empty($searchFields['field_name']) && !empty($searchFields['field_content'])) {
            $key = $searchFields['field_name'];
            $value = $searchFields['field_content'];
            $maps[$key] = ['like', '%' . $value . '%'];
        }


        if (session('admin.store_id') != null && session('admin.is_root') == 0) {
            $maps['id'] = session('admin.store_id');
        }

        if(!empty($searchFields['campus_id'])){
            $maps['campus_id'] = $searchFields['campus_id'];
        }

        if(session('admin.id')==48){//琼台超级管理员
            $maps['campus_id'] = 4;
        }
        $result = $this::scope('StoreList')->with('admin,campus')->where($maps)->limit($limit)->page($page)->select();
        $count = $this->where($maps)->count();

        return new listData($result, $count);
    }
    public function getStoreName($map)
    {

        $result = Store::scope('StoreName')->where($map)->select();
        $count = Store::where($map)->count();

        return new listData($result, $count);
    }

    public function get_history_order_storeName($map)
    {
        $result = Store::scope('StoreName')->where($map)->select();

        return $result;
    }

    public function editStore($data){


        $data['admin_id'] = session('admin.id');
        $data['logo'] = $data['logo'];

/*        if (session('admin.store_id') !== null || session('admin.store_id') !== '') {
            $data['store_id'] = session('admin.store_id');}*/

        if (!isset($data['store_id']) || empty($data['store_id'])) {
            $result = $this->validate(true)->allowField(true)->data($data)->save();
            if($result) {
                return true;
            }else {
                return false;
            }
        }else {
            $id = $data['store_id'];

            $portal = $this->where('id', $id)->find();

            unset($data['store_id']);

            $result = $portal->validate(true)->allowField(true)->except('id')->data($data)->save();
            if ($result) {
                return true;
            }else {
                return false;
            }
        }
    }
    public function editDraw($data){

            $portal = $this->where('id', $data['id'])->find();
               $old_balance = $portal['balance'];
              $money = ($data['balance']) *100;
            $data['balance'] =$portal['balance']  - ($data['balance']) *100;

            $result = $portal->validate(true)->allowField(true)->except('id')->data($data)->save();
            if ($result) {
                 $amount = new StoreAmountRecords();
                $result1 = $amount->editRecord($portal, $old_balance,$data['balance'],$money);
                //把数据存入money_draw表
                $draw = new MoneyDraw();
                $result2 = $draw->editDraw($portal, $old_balance,$data['balance'],$money);
                 if ($result1 && $result2){
                     return true;
                 }else{
                     return false;
                 }
            }else {
                return false;
            }
        }



}