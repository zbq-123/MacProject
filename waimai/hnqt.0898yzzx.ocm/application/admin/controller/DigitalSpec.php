<?php
/**
 * Created by PhpStorm.
 * User: weitrun
 * Date: 2020/7/9
 * Time: 9:16
 */

namespace app\admin\controller;

use app\admin\model\DigitalSpec as DigitalSpec_mod;
use app\common\controller\AdminBase;

use util\Ret;

class DigitalSpec extends AdminBase
{
    public function index()
    {
        if (request()->isAjax()) {
            $campus = new DigitalSpec_mod();
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
            $colum = new DigitalSpec_mod();
            $result = $colum->editTrain($data);
            if ($result) {
                $this->success('操作成功', 'index', null, 1);
            } else {
                $this->error($colum->getError());
            }
        }
        else{
            
            return $this->fetch();
        }
    }

    //删除
    public function delete(){
        $subject =DigitalSpec_mod::where(['id'=>input('post.id')])->find();
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
            $column = new DigitalSpec_mod();
            $result = $column->editTrain($data);
            if ($result){
                $this->success("操作成功", 'index', null ,1);
            }
            else{
                $this->error($column ->getError());
            }
        }else{
            $id = request()->param('id');
            $friendship=DigitalSpec_mod::where('id', $id)->find();
            if (empty($friendship)) {
                $this->error("资源不存在");
            }
            $this->assign("store", $friendship);

            return $this->fetch();
        }

    }


}