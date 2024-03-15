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




class Prints extends Model
{
    public function base($query)
    {
        return $query->where('deleted', 0);
    }
    public function admin(){
        return $this->belongsTo('Admin','admin_id')->field('id,login_name,real_name');
    }
    public function store(){
        return $this->belongsTo('Store','store_id')->field('id,name');
    }
    //后台查询范围
    protected function scopeStoreList($query){
        return $query->field('id,store_id,name,type,code,key,part,status,sort,admin_id,create_time,update_time,deleted,app_id,app_key,access_token')
            ->order('sort desc,update_time desc');
    }
    //获取列表数据
    public function getListData($maps, $searchFields, $limit, $page){

        if (!empty($searchFields['field_name']) && !empty($searchFields['field_content'])) {
            $key = $searchFields['field_name'];
            $value = $searchFields['field_content'];
            $maps[$key] = ['like', '%' . $value . '%'];
        }


        if (session('admin.store_id') != null && session('admin.is_root') == 0) {
            $maps['store_id'] = session('admin.store_id');
        }
        $result = $this::scope('StoreList')->with('admin,store')->where($maps)->limit($limit)->page($page)->select();
        $count = $this->where($maps)->count();

        return new listData($result, $count);
    }


    public function editPrint($data){

        $data['admin_id'] = session('admin.id');


               if (session('admin.store_id') !== null || session('admin.store_id') !== '') {
                    $data['store_id'] = session('admin.store_id');}

        if (!isset($data['prints_id']) || empty($data['prints_id'])) {
            $result = $this->validate(true)->allowField(true)->data($data)->save();
            if($result) {
                return true;
            }else {
                return false;
            }
        }else {
            $id = $data['prints_id'];

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