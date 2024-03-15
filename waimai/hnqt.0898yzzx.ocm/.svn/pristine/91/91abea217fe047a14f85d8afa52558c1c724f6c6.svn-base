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

class OrdersTimes extends Model
{
    public function base($query)
    {
        return $query->where('deleted', 0);
    }
    public function admin(){
        return $this->belongsTo('Admin','admin_id')->field('id,login_name,real_name');
    }

    //后台查询范围
    protected function scopeTimeList($query){
        return $query->field('id,oeders_id,status,status_time,admin_id,create_time,update_time,deleted')
            ->order('sort desc,update_time desc');
    }
    public function editTime($id,$status){



        $res = $this->where('orders_id',$id)->find();

        if ($res){
            $data =[];
            $data['orders_id'] = $id;
            $data['status'] = $status;
            $data['status_time'] = date('Y-m-d h:i:s', time());
            $data['admin_id'] = session('admin.id');

            $result = $this->insert($data);


            if ($result) {
                return true;
            } else {
                return false;
            }
        }
        else{
            $data =[];
            $data['orders_id'] = $id;
            $data['status'] = $status;
            $data['admin_id'] = session('admin.id');


            $data['status_time'] = date('Y-m-d h:i:s', time());
            $result = $this->validate(true)->allowField(true)->data($data)->save();

            if ($result) {
                return true;
            } else {
                return false;
            }

        }




    }

}