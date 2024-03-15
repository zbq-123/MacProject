<?php
/**
 * Created by PhpStorm.
 * User: weitrun
 * Date: 2020/7/9
 * Time: 9:16
 */
namespace app\admin\controller;
use app\admin\model\Admin;
use app\admin\model\Campus;
use app\common\controller\AdminBase;
use app\admin\model\RiderOrder;
use app\admin\model\SysSetting;
use app\admin\model\RiderIncomeLog;
use util\Ret;
use think\Db;

class Rider extends AdminBase{
    public function index(){
        if(request()->isAjax() ){
            $store = new \app\admin\model\Rider();
            $fields = input('param.fields/a');
            $map =[];
            $result = $store ->getListData($map,$fields,$this->limit,$this->page);
            return json($result);
        }
       
        return $this ->fetch();
    }

    //添加
    public function add_rider(){
        if (request()->post()) {
            $data = request()->except(['deleted'], 'post');

            $data['wx_code'] = input('post.logo');
            $data['password'] = md5($data['password']);
            $colum = new \app\admin\model\Rider();

            $result = $colum->editRider($data);
            if ($result) {
                $this->success('操作成功', 'Rider/index', null, 1);

            } else {
                $this->error($colum->getError());

            }
        }
        else{
            return $this->fetch();
        }
    }

    //删除
    public function delete_rider(){
        $subject =\app\admin\model\Rider::where(['id'=>input('post.id')])->find();
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
    public function edit_rider(){
        if (request()->isPost()){
            $data = request()->except(['deleted'],'post');
            $data = request()->except(['deleted'], 'post');
            $data['wx_code'] = input('post.logo');

            if(empty($data['password'])){
              unset($data['password']);
            }

            $data['password'] = md5($data['password']);
            $column = new \app\admin\model\Rider();
            $result = $column->editRider($data);
            if ($result){
                $this->success("操作成功", 'index', null ,1);
            }
            else{
                $this->error($column ->getError());}
        }else{
            $id = request()->param('id');
            $friendship=\app\admin\model\Rider::where('id', $id)->find();
            if (empty($friendship)) {
                $this->error("资源不存在");
            }
            $place = Campus::where('deleted', 0)->order('id asc')->field('id,name')->select();

            $this->assign('campus', $place);
            $this->assign("store", $friendship);
            return $this->fetch();
        }

    }

    // 收益明细
    public function income(){
        $id = input('id');
        if($id){
          session('rider_id',$id);
        }
        if(request()->isAjax() ){
            $store = new RiderOrder();
            $fields = input('param.fields/a');
            $map =[
              'status' => 2,
            ];
            if(session('rider_id')){
              $map['rider_id'] = session('rider_id');
            }

            $result = $store ->getListData($map,$fields,$this->limit,$this->page);
            return json($result);
        }
        
        $rider_name = Db::name('rider')->where('id',$id)->value('user_name');

        $this->assign('rider_name',$rider_name);
        return $this ->fetch();
    }

    // 订单列表
    public function order(){
        $id = input('id');
        if($id){
          session('rider_id',$id);
        }
        if(request()->isAjax() ){
            $store = new RiderOrder();
            $fields = input('param.fields/a');
            $map =[
              // 'status' => 2,
            ];
            if(session('rider_id')){
              $map['rider_id'] = session('rider_id');
            }

            $result = $store ->getListData($map,$fields,$this->limit,$this->page);
            return json($result);
        }
        
        return $this ->fetch();
    }

    // 打款结算
    public function settlement(){
      $id = session('rider_id');

      $rider_mod = new \app\admin\model\Rider();
      $rider_info = $rider_mod->where('id',$id)->find();
      if(empty($rider_info)){
        $this->error('骑手信息不存在','index',1);
      }

       // 启动事务
      Db::startTrans();
      try{
        // 查询骑手订单
        $orders = RiderOrder::where(['rider_id'=>$id,'status'=>2])->field('id')->select();
        $orders_id = dbCreateIn($orders);

        // 更新订单状态
        $res = Db::name('rider_order')->where(['id'=>['in',$orders_id]])->update(['status'=>4]);

        // $count = RiderOrder::where(['rider_id'=>$id,'status'=>2])->field('id')->count();
        $wx_ratio = SysSetting::where('id',1)->value('wx_ratio');

        $goods_orders = RiderOrder::where(['rider_id'=>$id,'status'=>2])->field('order_id')->select();
        $amount = 0;
        foreach ($goods_orders as $key => $value){
            $convey_price = Db::name('orders')->where(['id'=>$value['order_id']])->value('convey_price');
            $amount += $convey_price - $convey_price*$wx_ratio;
        }
        
        // 更新骑手余额
        $rider_info['balance'] -= $amount;
        $rider_info->save();

        // 日志
        $data = [
          'rider_id' => $id,
          'rider_name' => $rider_info['user_name'],
          'amount' => $amount,
          'admin_id' => session('admin.id'),
          'create_time' => date('Y-m-d H:i:s',time()),
        ];

        Db::name('rider_income_log')->insert($data);

        // 提交事务
        Db::commit(); 
        $this->success('操作成功','index',1);
      } catch (\Exception $e) {
          // 回滚事务
        Db::rollback();
        $this->error('操作失败','index',1);
      }   

    }

    public function income_log(){

        if(request()->isAjax() ){
            $store = new RiderIncomeLog();
            $fields = input('param.fields/a');
            $map =[
              // 'status' => 2,
            ];

            $result = $store ->getListData($map,$fields,$this->limit,$this->page);
            return json($result);
        }
        
        return $this ->fetch();
    }
}