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
class Check extends AdminBase{
    //提现页面
    public function check(){
        if (request()->isPost()) {
            $data = request()->except(['deleted'], 'post');
            if ($data['balance']<1){
                $this->error('不能提现小于1元');
            }
            $m = \app\admin\model\Store::where('id',$data['id'])->field('balance')->find();

            if ($m){



                if (($m['balance']- $data['balance']*100) <0  ){
                    $this->error('提现金额不足');
                }

            }else{
                $this->error('没有查找到数据');
            }
            $moneys = MoneyDraw::where('store_id',$data['id'])->where('status',1)->find();
            if ($moneys){
                $this->error('已经有提现在待审核中，请审核通过后再申请');

            }
            $student = new \app\admin\model\Store();
            $result = $student->editDraw($data);

            if ($result) {

                $this->success("操作成功", 'draw/draw', null, 1);
            } else {
                $this->error($student->getError());
            }
        } else {
            $balance = \app\admin\model\Store::where('id',session('admin.store_id'))->find();
            $balance['balance'] = $balance['balance'] /100;
            $this->assign("balance",  $balance );
            return $this ->fetch();

        }


    }

}