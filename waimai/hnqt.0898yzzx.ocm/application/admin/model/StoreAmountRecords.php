<?php
/**
 * Created by PhpStorm.
 * User: weitrun
 * Date: 2020/7/23
 * Time: 15:34
 */
namespace app\admin\model;
use think\Model;
use util\listData;


class StoreAmountRecords extends Model{
    public function base($query)
    {
        return $query->where('deleted', 0);
    }
 //后台查询范围
    protected function scopeStoreList($query){
        return $query->field('id,store_id,money,old_balance,now_balance,notes,status,sort,admin_id,create_time,update_time,deleted')
            ->order('sort desc,update_time desc');
    }
    public function admin(){
        return $this->belongsTo('Admin','admin_id')->field('id,login_name,real_name');
    }
    public function store(){
        return $this->belongsTo('Store','store_id')->field('id,name');
    }
    public function editRecord($data, $old_balance,$new_balance,$money){

            $datas =[];
            $datas['store_id'] = $data['id'];
            $datas['old_balance'] = $old_balance;
           $datas['now_balance'] = $new_balance;
           $datas['money'] = $money;
            $datas['status'] = 2;
            $datas['notes'] = '提现';

            $datas['admin_id'] = session('admin.id');
            $result = $this->validate(true)->allowField(true)->data($datas)->save();

            if ($result) {
                return true;
            } else {
                return false;
            }

    }
    //获取列表数据
    public function getListData($maps, $searchFields, $limit, $page){
        if (session('admin.store_id') != null && session('admin.is_root') == 0) {
            $maps['store_id'] = session('admin.store_id');
        }

        if (!empty($searchFields['field_name']) && !empty($searchFields['field_content'])) {
            $key = $searchFields['field_name'];
            $value = $searchFields['field_content'];
            //$maps[$key] = ['like', '%' . $value . '%'];
            $maps[$key] = $value;
        }
        /*if (session('admin.store_id') != null) {
            $maps['store_id'] = session('admin.store_id');
        }*/

        $result = $this::scope('StoreList')->with('admin,store')->where($maps)->limit($limit)->page($page)->select();

        $count = $this->where($maps)->count();

        return new listData($result, $count);
    }

}