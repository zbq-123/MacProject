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
class Draw extends AdminBase{
    //提现页面
    public function draw(){
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
            if($balance){
                $balance['balance'] = $balance['balance'] /100;
            }else{
                $balance['id'] = 0;
                $balance['balance'] = '非店铺管理员不支持提现';
            }
            $this->assign("balance",  $balance );
            return $this ->fetch();

        }


    }
    //提现记录
    public function record(){
        if(request()->isAjax() ){
            $store = new StoreAmountRecords();
            $fields = input('param.fields/a');
            $map =[];

            $result = $store ->getListData($map,$fields,$this->limit,$this->page);

            return json($result);
        }
        if(session('admin.is_root') == 1){
            $store = new \app\admin\model\Store();
            $storemaps['deleted'] = 0;
            $store_list = $store->get_history_order_storeName($storemaps);
        }else{
            $store_list = [];
        }
        $this->assign('store_list',$store_list);
        return $this ->fetch();
    }
    //提现审核列表
    public function examine()
    {
        if (request()->isAjax()) {
            if (request()->isGet()) {
                $subject = new MoneyDraw();
                $fields = input('param.fields/a');
                $map['status'] = 1;
                $result = $subject->getListData($map, $fields, $this->limit, $this->page);
                return json($result);
            } else {
                $id = input('post.id');
                $audit = input('post.audit/d');
                $subject = MoneyDraw::where('id', $id)->where('status', 1)->find();
                $subject->status = $audit;
                $subject->pay_order_code = input('post.pay_order_code');
                if ($audit == 2){
                    $subject->status2_time = date("Y-m-d H:i:s");
                }
                if ($audit == 3){
                    $money = input('post.money');
                    $store_id = input('post.store_id/d');
                    $subject->status3_time = date("Y-m-d H:i:s");

                    $store = db('store')-> where('id',$store_id) -> find();
                    $store_amount_data = [];
                    $store_amount_data['store_id'] = $store_id;
                    $store_amount_data['money'] = $money;
                    $store_amount_data['old_balance'] = $store['balance'];
                    $store_amount_data['now_balance'] = $store['balance'] + $money;
                    $store_amount_data['status'] = 1;
                    $store_amount_data['admin_id'] = session('admin.id');
                    $store_amount_data['notes'] = '提现审核不通过退还提现金额';

                    //提现审核不通过，退还提现金额，写入店铺金额明细表
                    $store_amount_records = db('store_amount_records')->insert($store_amount_data);

                    //提现审核不通过，退还金额更新入店铺余额
                    $storeupdate = db('store')-> where('id',$store_id) -> update(['balance' => $store_amount_data['now_balance']]);
                }
                $subject->admin_id = $this->adminId;
                $result = $subject->save();
                if ($result) {
                    return json(new Ret());
                } else {
                    abort(608);
                }
            }
        } else {
            return $this->fetch();
        }
    }
    //通过审核列表
    public function pass(){
        if(request()->isAjax() ){
            $store = new MoneyDraw();
            $fields = input('param.fields/a');
            $map =[];
            $map['status'] =2;

            $result = $store ->getListData($map,$fields,$this->limit,$this->page);

            return json($result);
        }
        return $this ->fetch();


    }
    //不通过审核列表
    public function returns(){
        if(request()->isAjax() ){
            $store = new MoneyDraw();
            $fields = input('param.fields/a');
            $map =[];
            $map['status'] =3;

            $result = $store ->getListData($map,$fields,$this->limit,$this->page);

            return json($result);
        }
        return $this ->fetch();


    }






}