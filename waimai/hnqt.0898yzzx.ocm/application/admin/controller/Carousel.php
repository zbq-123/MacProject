<?php
/**
 * Created by PhpStorm.
 * User: hnwc1
 * Date: 2018/5/24
 * Time: 8:44
 */
namespace app\admin\controller;
use app\admin\model\Campus;
use app\common\controller\AdminBase;
use app\admin\model\HomeCarousel as CarouselModel;

use app\admin\model\HomeCarousel;
use util\Ret;
Class Carousel extends AdminBase{

    //轮播图列表
    public function carousel (){
        if(request()->isAjax() ){
            $paystatu = input('param.paystatus/d');
            $map = [];
            if ($paystatu) {
                if ($paystatu == 1) {
                    /*$map['status'] = array('in','0,3,4');*/
                    $map['campus_id'] = 1;
                } else if ($paystatu == 2) {
                    $map['campus_id'] = 2;
                }


            }

            $carousel = new HomeCarousel();
            $fields = input('param.fields/a');

            /*$map['admin_id'] = $this->adminId;*/
            $result = $carousel ->getListData($map,$fields,$this->limit,$this->page);
            return json($result);
        }
        return $this->fetch();
    }
    //添加轮播图
    public function add_carousel(){
        if (request()->post()) {
            $data = request()->except(['deleted'], 'post');
            $colum = new HomeCarousel();
            $result = $colum->editCarousel($data);
            if ($result) {
                $this->success('操作成功', 'carousel/carousel', null, 1);

            } else {
                $this->error($colum->getError());

            }
        }
        else{
            return $this->fetch();
        }
    }
    //删除轮播
    public function deleted_carousel(){
        $colum = HomeCarousel::where(['id'=>input('post.id')])->find();
        if (false==$colum){
            abort(608, '资源不存在');
        }
        $colum->deleted=1;
        $result = $colum->save();
        if ($result) {
            return json(new Ret());
        } else {
            abort(608);
        }
    }


   /* //修改栏目
    public function edit_carousel(){
        if (request()->isPost()){
            $data = request()->except(['deleted'],'post');
            $data['admin_id'] = $this->adminId;
            $column = new CarouselModel();
            $result = $column->editCarousel($data);
            if ($result){
                $this->success("操作成功", 'carousel/carousel', null ,1);
            }
            else{
                $this->error($column ->getError());}
        }

    }*/
//修改轮播图
    public function edit_carousel()
    {
        if (request()->isPost()) {
            $id = input('post.id');
            $c = HomeCarousel::where('id', $id)->find();
            $data = request()->post();
            $result = $c->allowField(true)->data($data)->save();
            if ($result) {
                $this->success("修改成功",'carousel/carousel', null, 1);
            }else{
                $this->error('修改失败',null, null, 1);
            }
        }else{
            $c = HomeCarousel::where('id', input('param.id'))->find();
            $this->assign("carousel", $c);
            $place = Campus::where('deleted', 0)->order('id asc')->field('id,name')->select();
            $this->assign('campus', $place);
            return $this->fetch();
        }
    }

    //设置置顶序号
    public function edit_sort()
    {
        $media = HomeCarousel::get(input('post.id'));
        $media->sort = input('post.sort/d');
        $result = $media->save();
        if ($result) {
            return json(new Ret());
        } else {
            abort(608);
        }
    }
}