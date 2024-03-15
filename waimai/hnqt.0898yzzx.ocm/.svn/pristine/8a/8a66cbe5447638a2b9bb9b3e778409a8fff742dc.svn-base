<?php
namespace app\admin\controller;
use app\admin\model\Goods;
use app\admin\model\Store as store_mod;
use app\common\controller\AdminBase;

use app\admin\model\GoodsPromotion;
use think\Model;
use util\Data;
use util\Ret;

class Goodsp extends AdminBase {
    //促销商品列表
    public function goodsplist(){
        if(request()->isAjax() ){
            $goodsp = new GoodsPromotion();
            $fields = input('param.fields/a');
            $maps=[];
            //$maps['start_time'] = ['<',date("Y-m-d H:i:s",time())];
            $maps['end_time'] = ['>',date("Y-m-d H:i:s",time())];
            $result = $goodsp ->getListData($maps,$fields,$this->limit,$this->page);
            //$now_time = date('Y-m-d', time());
     
            return json($result);
        }
        
        return $this ->fetch();
    }
    //历史促销列表
    public function historygplist(){
        if(request()->isAjax() ){
            $goodsp = new GoodsPromotion();
            $fields = input('param.fields/a');
            $maps['end_time'] = ['<',date("Y-m-d H:i:s",time())];
            $result = $goodsp ->getListData($maps,$fields,$this->limit,$this->page);
            return json($result);
        }

        return $this ->fetch();
    }
    //添加促销商品
    public function add_goodsp($salesprice,$startime,$endtime,$data){
        if(request()->isAjax()){

            $goodspdata['salesprice']=$salesprice*100;
            $goodspdata['start_time']=$startime;
            $goodspdata['end_time']  =$endtime;
            $goodspdata['good_id']   =$data['id'];
            $goodspdata['store_id']  =$data['store_id'];
            $goodspdata['campus_id'] =$data['campus_id'];
            $goodspdata['admin_id']  =$data['admin_id'];
            $goodspdata['storename'] =$data['store']['name'];
            $goodspdata['goodprice'] =$data['price'];
            $goodspdata['goodsname'] =$data['name'];
            $goodspdata['goodsimage'] =$data['image'];
            $goodspdata['type']      =1;
            $goodsp = new GoodsPromotion();

            $res =$goodsp->where(['good_id'=>$data['id'],'deleted'=>0])->find();
            if(!empty($res)){
               
                abort(608, '该商品已加入促销列表，请前往促销列表查看');
            }
            $result = $goodsp->editgoodsp($goodspdata);
            if ($result) {
                return json(new Ret($result));
            } else {
                abort(608);
            }
        }
    }

    public function delete_goodsp(){
        $goodsp = new GoodsPromotion();
        $subject =$goodsp->where(['id'=>input('post.id')])->find();
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
    public function edit_goodsp(){

        $goodsp = new GoodsPromotion();
        if (request()->isPost()){

            $data['salesprice']= request()->param('salesprice')*100;
            $data['start_time']= request()->param('start_time');
            $data['end_time']= request()->param('end_time');
            $data['update_time']= date('Y-m-d H:i:s', time());
            $data['id'] = request()->param('id');
            $result = $goodsp->editgoodsp($data);
            if(!empty($result)){
                $this->success("操作成功", 'goodsp/goodsplist', null ,1);
            }
        }else{

            $id = request()->param('id');
            $res =$goodsp->where(['id'=>$id])->find();
            if(!empty($res)){
                $res['salesprice']    = $res['salesprice']/100;
                $this->assign("goodsp", $res);
            }
            return $this ->fetch();
        }
    }

    public function edit_sort1()
    {
        $goodsp = new GoodsPromotion();
        $media = $goodsp->get(input('post.id'));
        $media->sort = input('post.sort/d');
        $result = $media->save();
        if ($result) {
            return json(new Ret());
        } else {
            abort(608);
        }
    }


}