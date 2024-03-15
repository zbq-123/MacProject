<?php
/**
 * Created by PhpStorm.
 * User: weitrun
 * Date: 2020/7/27
 * Time: 10:30
 */
namespace app\admin\model;

use think\Model;
use util\listData;
class MoneyDraw extends Model{
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
    public function store(){
        return $this->belongsTo('Store','store_id')->field('id,name,bank_card,bank_info,bank_card_name,ali_card,ali_name');
    }
    //后台查询范围
    protected function scopeMoneyList($query){
        return $query->field('id,store_id,store_user_id,money,old_balance,now_balance,pay_order_code,status,status2_time,status3_time,sort,admin_id,create_time,update_time,deleted')
            ->order('sort desc,update_time desc，create_time asc');
    }
    public function editDraw($data, $old_balance,$new_balance,$money){

        $datas =[];
        $datas['store_id'] = $data['id'];
        $datas['old_balance'] = $old_balance;
        $datas['now_balance'] = $new_balance;
        $datas['money'] = $money;
        $datas['store_user_id'] = session('admin.id');
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
            $maps[$key] = ['like', '%' . $value . '%'];
        }
       /* if (session('admin.store_id') != null) {
            $maps['store_id'] = session('admin.store_id');
        }*/

        $result = $this::scope('StoreList')->with('admin,store')->where($maps)->limit($limit)->page($page)->order('create_time desc')->select();

        $count = $this->where($maps)->count();

        return new listData($result, $count);
    }

}