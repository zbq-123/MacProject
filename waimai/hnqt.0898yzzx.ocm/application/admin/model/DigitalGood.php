<?php
/**
 * Created by PhpStorm.
 * User: weitrun
 * Date: 2020/7/9
 * Time: 9:35
 */
namespace app\admin\model;

use think\Model;
use util\listData;

class DigitalGood extends Model
{
    public function base($query)
    {
        return $query->where('deleted', 0);
    }
    public function admin(){
        return $this->belongsTo('Admin','admin_id')->field('id,login_name,real_name');
    }
    //获取数据库中的字段对应数据
    protected function scopecampusName($query)
    {
        return $query->field('id,name,deleted,update_time');
    }



    //获取列表数据
    public function getListData($maps, $searchFields, $limit, $page){
        if (!empty($searchFields['field_name']) && !empty($searchFields['field_content'])) {
            $key = $searchFields['field_name'];
            $value = $searchFields['field_content'];
            $maps[$key] = ['like', '%' . $value . '%'];
        }
        if(session('admin.id')==48){//琼台超级管理员
            $maps['id'] = 4;
        }
        $result = $this::scope('StoreList')->with('admin')->where($maps)->limit($limit)->page($page)->order('create_time desc')->select();
        $count = $this->where($maps)->count();
        if(!empty($result)){
            foreach($result as $k=>$v){
                $res=DigitalCategory::where('id',$v['category_id'])->field('id,name')->find();
                if(!empty($res)){
                    $result[$k]['category_name']=$res['name'];
                }
            }
        }
        if(!empty($result)){
            foreach($result as $k=>$v){
                $supermarket_res=Digital::where('id',$v['supermarket_id'])->field('id,name')->find();
                if(!empty($res)){
                    $result[$k]['supermarket_name']=$supermarket_res['name'];
                }
            }
        }
        return new listData($result, $count);
    }
    public function editTrain($data){
        $data['admin_id'] = session('admin.id');
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