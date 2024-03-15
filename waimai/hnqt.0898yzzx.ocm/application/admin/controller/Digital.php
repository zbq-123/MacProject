<?php
/**
 * Created by PhpStorm.
 * User: weitrun
 * Date: 2020/7/9
 * Time: 9:16
 */

namespace app\admin\controller;

use app\admin\model\Digital as Digital_mod;
use app\common\controller\AdminBase;
use app\admin\model\DigitalCategory;
use think\Db;
use app\admin\model\Campus;
use util\Ret;

class Digital extends AdminBase
{
    public function index()
    {
        if (request()->isAjax()) {
            $campus = new Digital_mod();
            $fields = input('param.fields/a');
            $map = [];
            $result = $campus->getListData($map, $fields, $this->limit, $this->page);

            return json($result);
        }
        
        return $this->fetch();
    }

    //添加
    public function add(){
        if (request()->post()) {
            $data = request()->except(['deleted'], 'post');
            $colum = new Digital_mod();
            $result = $colum->editTrain($data);
            if ($result) {
                $this->success('操作成功', 'index', null, 1);
            } else {
                $this->error($colum->getError());
            }
        }
        else{
            $digital_category = new DigitalCategory();
            $res=$digital_category->where('deleted',0)->field('id,name')->select();
            $this->assign('sort_list',$res);
            $campus = new Campus();
            $res=$campus->where('deleted',0)->field('id,name')->select();
            $this->assign('campus_list',$res);
            return $this->fetch();
        }
    }

    //删除
    public function delete(){
        $subject =Digital_mod::where(['id'=>input('post.id')])->find();
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
            $column = new Digital_mod();
            $result = $column->editTrain($data);
            if ($result){
                $this->success("操作成功", 'index', null ,1);
            }
            else{
                $this->error($column ->getError());
            }
        }else{
            $id = request()->param('id');
            $friendship=Digital_mod::where('id', $id)->find();
            if (empty($friendship)) {
                $this->error("资源不存在");
            }
            $this->assign("store", $friendship);
            $digital_category = new DigitalCategory();
            $res=$digital_category->where('deleted',0)->field('id,name')->select();
            $this->assign('sort_list',$res);
            $campus = new Campus();
            $res=$campus->where('deleted',0)->field('id,name')->select();
            if(!empty($res)){
                foreach($res as $k=>$v){
                    if($friendship['campus_id']==$v['id']){
                        $res[$k]['type']=1;
                    }else{
                        $res[$k]['type']=0;
                    }
                }
            }
            $this->assign('campus_list',$res);
            return $this->fetch();
        }

    }


}