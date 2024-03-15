<?php
/**
 * Created by PhpStorm.
 * User: weitrun
 * Date: 2020/7/9
 * Time: 9:16
 */
namespace app\admin\controller;
use app\admin\model\MoneyDraw;
use app\admin\model\StoreAmountRecords;
use app\common\controller\AdminBase;
use util\Ret;
class Prints extends AdminBase{
    //打印列表
    public function prints(){

        if(request()->isAjax() ){
            $store = new \app\admin\model\Prints();
            $fields = input('param.fields/a');
            $map =[];
            $map['store_id'] = session('admin.store_id');
            $result = $store ->getListData($map,$fields,$this->limit,$this->page);
            return json($result);
        }

        return $this ->fetch();
    }
    //添加打印机
    public function add_prints(){
        if (request()->post()) {
            $data = request()->except(['deleted'], 'post');


            $colum = new \app\admin\model\Prints();
            $result = $colum->editPrint($data);
            if ($result) {
                $this->success('操作成功', 'prints/prints', null, 1);

            } else {
                $this->error($colum->getError());

            }
        }
        else{
            return $this->fetch();
        }
    }
    //删除打印机
    public function delete_prints(){
        $subject =\app\admin\model\Prints::where(['id'=>input('post.id')])->find();
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
    //状态修改
    public function status($id, $status)
    {
        $store = \app\admin\model\Prints::get($id);
        if ($store['status'] == 1){
            $store->status = 2;
            $res = $store->save();

            if ($res) {
                return json(new Ret($store['status']));
            } else {
                abort(608);
            }

        }
        else{
            $store->status = 1;
            $res = $store->save();
            if ($res) {
                return json(new Ret($store['status']));
            } else {
                abort(608);
            }
        }
    }
    //修打印机
    public function edit_prints(){

        if (request()->isPost()){
            $data = request()->except(['deleted'],'post');
            $data['admin_id'] = $this->adminId;
            $column = new \app\admin\model\Prints();

            $result = $column->editPrint($data);
            if ($result){
                $this->success("操作成功", 'prints/prints', null ,1);
            }
            else{
                $this->error($column ->getError());}
        }else{
            $id = request()->param('id');
            $friendship=\app\admin\model\Prints::where('id', $id)->find();
            if (empty($friendship)) {
                $this->error("资源不存在");
            }
            $this->assign("prints", $friendship);
            return $this->fetch();
        }

    }
    //设置置顶序号
    public function edit_sort()
    {
        $media = \app\admin\model\Prints::get(input('post.id'));
        $media->sort = input('post.sort/d');
        $result = $media->save();
        if ($result) {
            return json(new Ret());
        } else {
            abort(608);
        }
    }

}