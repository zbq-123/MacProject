<?php
/**
 * Created by PhpStorm.
 * User: weitrun
 * Date: 2020/7/9
 * Time: 9:16
 */

namespace app\admin\controller;


use app\admin\model\SupermarketGood as SupermarketGood_mod;
use app\common\controller\AdminBase;
use think\Db;
use app\admin\model\SupermarketSort;
use app\admin\model\Supermarket;
use util\Ret;

class SupermarketGood extends AdminBase
{
    public function index()
    {
        if (request()->isAjax()) {
            $campus = new SupermarketGood_mod();
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
            $colum = new SupermarketGood_mod();
            $result = $colum->editTrain($data);
            if ($result) {
                $this->success('操作成功', 'index', null, 1);
            } else {
                $this->error($colum->getError());
            }
        }
        else{
            $supermarket_sort = new SupermarketSort();
            $res=$supermarket_sort->where('deleted',0)->field('id,name')->select();
            $this->assign('sort_list',$res);
            $supermarket = new Supermarket();
            $res=$supermarket->where('deleted',0)->field('id,name')->select();
            $this->assign('supermarket_list',$res);
            return $this->fetch();
        }
    }

    //删除
    public function delete(){
        $subject =SupermarketGood_mod::where(['id'=>input('post.id')])->find();
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
            $column = new SupermarketGood_mod();
            $result = $column->editTrain($data);
            if ($result){
                $this->success("操作成功", 'index', null ,1);
            }
            else{
                $this->error($column ->getError());
            }
        }else{
            $id = request()->param('id');
            $friendship=SupermarketGood_mod::where('id', $id)->find();
            if (empty($friendship)) {
                $this->error("资源不存在");
            }
            $supermarket_sort = new SupermarketSort();
            $res=$supermarket_sort->where('deleted',0)->field('id,name')->select();
            if(!empty($res)){
                foreach($res as $k=>$v){
                    if($friendship['category_id']==$v['id']){
                        $res[$k]['type']=1;
                    }else{
                        $res[$k]['type']=0;
                    }
                }
            }
            $supermarket = new Supermarket();
            $super_res=$supermarket->where('deleted',0)->field('id,name')->select();
            if(!empty($super_res)){
                foreach($super_res as $k=>$v){
                    if($friendship['supermarket_id']==$v['id']){
                        $super_res[$k]['type']=1;
                    }else{
                        $super_res[$k]['type']=0;
                    }
                }
            }
            $this->assign('supermarket_list',$super_res);
            $this->assign('sort_list',$res);
            $this->assign("store", $friendship);
            
            return $this->fetch();
        }

    }


}