<?php
namespace app\admin\model;
use think\Model;
use util\listData;

class GoodsPromotion extends Model{
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
    public function campus(){
        return $this->belongsTo('Campus','campus_id')->field('id,name');
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
        if(session('admin.id')==48){//琼台超级管理员
            $maps['campus_id'] = 4;
        }

        $result = $this::scope('promotion')->with('admin,store,campus')->where($maps)->limit($limit)->page($page)->order('sort desc,update_time desc')->select();
        $count = $this->where($maps)->count();

        return new listData($result, $count);
    }
    public function editgoodsp($data){


        if (!isset($data['id']) || empty($data['id'])) {
            $result = $this->validate(true)->allowField(true)->data($data)->save();
            if($result) {
                return true;
            }else {
                return false;
            }
        }else {
            $res = $this->where('id', $id = $data['id'])->find();

            $result = $res->validate(true)->allowField(true)->except('id')->data($data)->save();

            if ($result) {
                return true;
            }else {
                return false;
            }
        }
    }
}