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

class UserExperience extends Model
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

        return $query->field('id,deleted,update_time');
    }


    //获取列表数据
    public function getListData($maps, $searchFields, $limit, $page){

        if (!empty($searchFields['field_name']) && !empty($searchFields['field_content'])) {
            $key = $searchFields['field_name'];
            $value = $searchFields['field_content'];
            $maps['e_name'] = ['like', '%' . $value . '%'];
        }

        if(session('admin.id')==48){//琼台超级管理员
            $maps['id'] = 4;
        }

        $result = $this::scope('StoreList')->with('admin')->where($maps)->limit($limit)->page($page)->order('create_time desc')->select();

        $data=array();
        if(!empty($result)){
            foreach($result as $k=>$v){
                $data[$k]['id']=$v['id'];
                $data[$k]['train_id']=$v['train_id'];
                $train=Train::where('id',$v['train_id'])->field('name')->find();
                if(!empty($train)){
                    $data[$k]['train_name']=$train['name'];
                }else{
                    $data[$k]['train_name']='';
                }
                $user_info=User::where('id',$v['user_id'])->field('nickname')->find();
                if(!empty($user_info)){
                    $data[$k]['user_name']=$user_info['nickname'];
                }else{
                    $data[$k]['user_name']='';
                }
                $data[$k]['e_name']=$v['e_name'];
                $data[$k]['e_price']=$v['e_price'];
                $data[$k]['create_time']=$v['create_time'];
                $data[$k]['update_time']=$v['update_time'];
            }
        }
        $count = $this->where($maps)->count();

        return new listData($data, $count);
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