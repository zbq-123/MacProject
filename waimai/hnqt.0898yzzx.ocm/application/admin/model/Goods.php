<?php
/**
 * Created by PhpStorm.
 * User: weitrun
 * Date: 2020/7/13
 * Time: 11:14
 */
namespace app\admin\model;

use think\Model;
use util\listData;
class Goods extends Model{
    public function base($query)
    {
        return $query->where('deleted', 0);
    }
    public function admin(){
        return $this->belongsTo('Admin','admin_id')->field('id,login_name,real_name');
    }
    public function category(){
        return $this->belongsTo('GoodsCategory','goods_category_id')->field('id,name');
    }
    public function store(){
        return $this->belongsTo('Store','store_id')->field('id,name');
    }
    public function campus(){
        return $this->belongsTo('Campus','campus_id')->field('id,name');
    }
    //后台查询范围
    protected function scopeStoreList($query){
        return $query->field('id,goods_category_id,number,name,store_id,price,unit,tag,image,sale,detail,status,sort,admin_id,create_time,update_time,deleted')
            ->order('sort desc,update_time desc');
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

        /*  if (session('admin.store_id') != null) {
              $maps['store_id'] = session('admin.store_id');
          }*/

        if(session('admin.id')==48){//琼台超级管理员
            $maps['campus_id'] = 4;
        }
        $result = $this::scope('scopeStoreList')->with('admin,category,store,campus') ->order('sort desc,update_time desc')->where($maps)->limit($limit)->page($page)->select();

        $count = $this->where($maps)->count();

        return new listData($result, $count);
    }
    public function editGoods($data){

        $data['admin_id'] = session('admin.id');
        $data['store_id']  = !empty($data['store_id'] ) ? $data['store_id'] : session('admin.store_id');
        if(empty($data['campus_id'])){
            $data['campus_id'] = Store::where('id',$data['store_id'])->value('campus_id');
        }
        
        $data['price'] = $data['price']*100;


                // if (session('admin.store_id') !== null || session('admin.store_id') !== '') {
                //     $data['store_id'] = session('admin.store_id');}

        if (!isset($data['goods_id']) || empty($data['goods_id'])) {
                $result = $this->validate(true)->allowField(true)->data($data)->save();
                if($result) {
                    return true;
                }else {
                    return false;
                }
        }else {
            $id = $data['goods_id'];


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