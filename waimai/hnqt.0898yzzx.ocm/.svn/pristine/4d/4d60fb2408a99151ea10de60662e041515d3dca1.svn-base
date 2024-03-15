<?php
/**
 * Created by PhpStorm.
 * User: weitrun
 * Date: 2020/7/14
 * Time: 10:32
 */
namespace app\admin\model;

use think\Model;
use util\listData;
class GoodsCategory extends Model{
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
    //获取数据库中的字段对应数据
    protected function scopecategoryName($query)
    {

        return $query->field('id,store_id ,name,deleted,update_time');
    }

    public function getCategory($map)
    {
        $map['store_id'] = !empty($map['store_id']) ? $map['store_id'] : session('admin.store_id');
        if(session('admin.id')==48){//琼台超级管理员
            $map['campus_id'] = 4;
        }
        $result = GoodsCategory::scope('categoryName')->where($map)->select();
        $count = GoodsCategory::where($map)->count();

        return new listData($result, $count);
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
        $result = $this::scope('category')->with('admin,store,campus')->where($maps)->limit($limit)->page($page)->order('sort desc,update_time desc')->select();
        $count = $this->where($maps)->count();

        return new listData($result, $count);
    }
    public function editCategory($data){

        $data['admin_id'] = session('admin.id');
        // $data['store_id']  = session('admin.store_id');
        $data['store_id']  = !empty($data['store_id'] ) ? $data['store_id'] : session('admin.store_id');
        if(empty($data['campus_id'])){
            $data['campus_id'] = Store::where('id',$data['store_id'])->value('campus_id');
        }
        // if (session('admin.store_id') !== null || session('admin.store_id') !== '') {
        //     $data['store_id'] = session('admin.store_id');}

        if (!isset($data['category_id']) || empty($data['category_id'])) {
            $result = $this->validate(true)->allowField(true)->data($data)->save();
            if($result) {
                return true;
            }else {
                return false;
            }
        }else {
            $id = $data['category_id'];

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