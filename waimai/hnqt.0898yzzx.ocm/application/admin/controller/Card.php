<?php
/**
 * User: rice
 * Date: 2020/10/28
 * Time: 15:16
 */
namespace app\admin\controller;
use app\admin\model\Admin;
use app\admin\model\MonthCard;
use app\admin\model\MonthCardView;
use app\common\controller\AdminBase;
use app\admin\model\Orders AS Order_mod;
use util\Ret;

class Card extends AdminBase{

   //优惠券列表
    public function index(){

        if(request()->isAjax() ){
            $store = new MonthCard();
            $fields = input('param.fields/a');
            $map =[];
            
            $result = $store ->getListData($map,$fields,$this->limit,$this->page);
            return json($result);
        }

        return $this ->fetch();
    }
    //添加优惠券
    public function add(){

        if (request()->post()) {

            $data = request()->post();
            $colum = new MonthCard();
            $result = $colum->editCoupon($data);
            if ($result) {
                $this->success('操作成功', 'coupon/index', null, 1);

            } else {
                $this->error($colum->getError());

            }
        }
        else{
            return $this->fetch();
        }
    }
    //删除优惠券
    public function delete(){
        $subject =MonthCard::where(['id'=>input('post.id')])->find();
        if (false == $subject) {
            abort(608, '资源不存在');
        }
        // $subject->deleted = 1;
        $result = $subject->delete();
        if ($result) {
            return json(new Ret());
        } else {
            abort(608);
        }
    }
    //修改优惠券
    public function edit(){
        if (request()->isPost()){
            $data = request()->post();
            $column = new MonthCard();
            $result = $column->editCard($data);
            if ($result){
                $this->success("操作成功", 'card/index', null ,1);
            }
            else{
                $this->error($column ->getError());}
        }else{
            $id = request()->param('id');
            $coupon=MonthCard::where('id', $id)->find();

            if (empty($coupon)) {
                $this->error("资源不存在");
            }

            $this->assign('coupon', $coupon);
            return $this->fetch();
        }

    } 

       // 使用优惠券统计
    public function count(){

        if(request()->isAjax() ){
            $store = new Order_mod();
            $fields = input('param.fields/a');
            $map =[
                'status'=>7,
                'use_month_card'=>['>',0]
            ];

            $result = $store ->getListDatacard($map,$fields,$this->limit,$this->page);
            return json($result);
        }

        return $this->fetch();
    }
}