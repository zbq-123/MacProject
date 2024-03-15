<?php
/**
 * Created by PhpStorm.
 * User: weitrun
 * Date: 2020/7/9
 * Time: 9:16
 */

namespace app\admin\controller;

use app\admin\model\Admin;
use app\admin\model\Experience as Experience_mod;
use app\common\controller\AdminBase;
use think\Db;
use app\admin\model\Train as Train_mod;
use util\Ret;

class Experience extends AdminBase
{
    public function index()
    {
        if (request()->isAjax()) {
            $experience = new Experience_mod();
            $fields = input('param.fields/a');
            $map = [];
            $result = $experience->getListData($map, $fields, $this->limit, $this->page);
            return json($result);
        }
        return $this->fetch();
    }

    //添加
    public function add(){
        if (request()->post()) {
            $data = request()->except(['deleted'], 'post');

            $experience = new Experience_mod();
            $result = $experience->editTrain($data);
            if ($result) {
                $this->success('操作成功', 'index', null, 1);
            } else {
                $this->error($experience->getError());
            }
        }
        else{
            $train = new Train_mod();
            $res=$train->where('deleted',0)->field('id,name')->select();
            $this->assign('train_list',$res);
            return $this->fetch();
        }
    }

    //删除
    public function delete(){
        $subject =Experience_mod::where(['id'=>input('post.id')])->find();
        if (false == $subject) {
            abort(608, '资源不存在');
        }
        $subject->deleted = 1;
        $result = $subject->save();
        if ($result) {
            return json(new Ret());
        } else {
            abort(608);
        }
    }

    //修改
    public function edit(){
        if (request()->isPost()){
            $data = request()->except(['deleted'],'post');
            $data['admin_id'] = $this->adminId;
            $experience = new Experience_mod();

            $result = $experience->editTrain($data);
            if ($result){
                $this->success("操作成功", 'index', null ,1);
            }
            else{
                $this->error($experience ->getError());
            }
        }else{
            $id = request()->param('id');
            $experience = new Experience_mod();
            $friendship = $experience->where('id', $id)->find();
            if (empty($friendship)) {
                $this->error("资源不存在");
            }
            $train = new Train_mod();
            $res=$train->where('deleted',0)->field('id,name')->select();
            if(!empty($res)){
                foreach($res as $k=>$v){
                    if($friendship['train_id']==$v['id']){
                        $res[$k]['type']=1;
                    }else{
                        $res[$k]['type']=0;
                    }
                }
            }
           
            $this->assign('train_list',$res);
            $this->assign("store", $friendship);
            
            return $this->fetch();
        }

    }


}