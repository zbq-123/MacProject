<?php


namespace app\api\controller;

use think\Controller;
use app\admin\model\RiderOrder;
use app\admin\model\Orders;
use app\admin\model\Rider as rider_mod;
use think\Db;
use app\admin\model\SysSetting;
use app\admin\model\HomeCarousel;
use think\Model;

class Rider extends Controller
{
    public function getcarouseldata(){
       // verify('id');
       // $id = input('id');

        $homecarousel= new HomeCarousel();
        //$data = $homecarousel->where('id',$id)->find();
        $data = $homecarousel->order('id')->select();
        if(!empty($data)){

            return json(['code'=>200,'msg'=>'操作成功','data'=>$data]);
        }else{
            return json(['code'=>204,'msg'=>'操作失败']);
        }
    }
    //扫码入单
    public function addOrder(){

        $order_id = input('order_id');
        $rider_id = input('rider_id');

        if(empty($order_id)){
            return json(['code'=>400,'msg'=>'order_id不能为空']);
        }

        if(empty($rider_id)){
            return json(['code'=>400,'msg'=>'rider_id']);
        }

        // 查询订单
        $orders = Orders::where(['id'=>$order_id,'pay_status'=>2,'status'=>3])->field('order_number,user_id,store_name,total_price,status,update_time,delivery_name,delivery_phone,today_number')->find();
        if(empty($orders)){
            return json(['code'=>202,'msg'=>'没有订单记录']);
        }
        // 查询骑手
        $rider = rider_mod::where('id',$rider_id)->field('user_name,phone')->find();
        if(empty($rider)){
            return json(['code'=>203,'msg'=>'没有骑手记录']);
        }

        // 启动事务
        Db::startTrans();
        try{
            // 骑手今日订单号
            $today_numbers = Db::name('rider_order')->where('rider_id',$rider_id)
                ->where('create_time','>=',date("Y-m-d 00:00:00",time()))
                ->where('create_time','<=',date("Y-m-d 23:59:59",time()))
                ->order('today_number desc')
                ->field('today_number')
                ->lock(true)
                ->find();
                $today_number = $today_numbers['today_number'] ? intval($today_numbers['today_number'])+1 : 1;

            $data = [
                'rider_id' => $rider_id,
                'rider_name' => $rider['user_name'],
                'order_id' => $order_id,
                'order_number' => $orders['order_number'],
                'delivery_name' => $orders['delivery_name'],
                'delivery_phone' => $orders['delivery_phone'],
                'status' => 1,
                'create_time' => date('Y-m-d H:i:s',time()),
                'update_time' => date('Y-m-d H:i:s',time()),
                'today_number' => $today_number
            ];

            $order_id = Db::name('rider_order')->insertGetId($data);

             // 发送模板消息
            $user = Db::name('user')
                    ->where('id',$orders['user_id'])
                    ->where('deleted',0)
                    ->where('disabled',0)
                    ->field('id,gz_openid')
                    ->find();
            if($user){
                $store_name =  $orders['store_name'] .'  单号 : '.$orders['today_number'];
                send_rider_success($user['gz_openid'],$orders['order_number'],$orders['store_name'],$rider['phone']);
            }

            // 发送短消息

            // 修改订单状态
            $orders->status = 4;
            $orders->update_time = date('Y-m-d H:i:s',time());
            $orders->save();
            
            Db::commit(); 

            $res = 1;
        } catch (\Exception $e) {
            // 回滚事务
            Db::rollback();
            $res = 0;
        }   
        if($res){
            return json(['code'=>200,'msg'=>'操作成功','rider_order_id'=>$order_id]);
        }else{
            return json(['code'=>204,'msg'=>'操作失败']);
        }
    }

    //登录验证
    public function login(){
        verify('user_name,password');
        $user_name = input('user_name');
        $password = input('password');

        $maps = [
            'deleted' => 0,
            'user_name' => $user_name,
            'password' => md5($password)
        ];

        // 查询骑手
        $rider_info = rider_mod::where($maps)->field('id,user_name,campus_id,phone,balance')->find();
        if(empty($rider_info)){
            return json(['code'=>201,'msg'=>'登录失败，没有此骑手记录信息']);
        }else{
            // 查询校区名称
            $rider_info['campus_name'] = '';
            if($rider_info['campus_id']){
                $rider_info['campus_name'] = Db::name('campus')->where('id',$rider_info['campus_id'])->value('name');
            }
            
            return json(['code'=>200,'msg'=>'操作成功','rider_info'=>$rider_info]);
        }
    }

    //获取骑手订单
    public function getRiderOderList(){
        verify('rider_id');
        $rider_id = input('rider_id');
        $status = input('status');
        $starttime = input('starttime');
        $endtime = input('endtime');
        $keyword = input('keyword');
        $page = input('page') ? input('page') : 1;

        $maps = [
            'deleted' => 0,
            'id' => $rider_id,
        ];

        // 查询骑手
        $rider_info = rider_mod::where($maps)->field('id,user_name')->find();
        if(empty($rider_info)){
            return json(['code'=>202,'msg'=>'没有此骑手记录信息']);
        }

        // 获取订单
        $order_maps = [
            'rider_id' => $rider_id,
        ];

        if(!empty($status)){
            $order_maps['status'] = $status;
        }
        if(!empty($starttime) && !empty($endtime)){
            $order_maps['create_time'] = ['between',[$starttime,$endtime]];
        }

        if(!empty($keyword)){
            $order_maps['order_number|delivery_name|delivery_phone'] = ['like', '%' . $keyword . '%'];;
        }

        $rider_order = RiderOrder::with('order')->where($order_maps)->page($page)->limit(10)->select();
        $rider_order_count = RiderOrder::with('order')->where($order_maps)->count();
        $unsent = RiderOrder::with('order')->where(['rider_id'=>$rider_id,'status'=>1])->count();
        if(!empty($rider_info)){
            return json(['code'=>200,'msg'=>'操作成功','unsent_num'=>$unsent,'rider_order'=>$rider_order,'rider_order_count'=>$rider_order_count]);
        }else{
            return json(['code'=>201,'msg'=>'操作失败']);
        }
    }

    // 确认送达
    public function confirmRiderOder(){
        verify('rider_order_id');
        $rider_order_id = input('rider_order_id');

        $maps = [
            'status' => 1,
            'id' => $rider_order_id,
        ];

        $rider_order = RiderOrder::where($maps)->find();
        if(empty($rider_order)){
            return json(['code'=>202,'msg'=>'骑手订单不存在']);
        }

        // $rider_order['status'] = 2;
        // $rider_order['update_time'] = date('Y-m-d H:i:s',time());
        // $res = $rider_order->save();
        // if(!empty($res)){
        // 启动事务
        Db::startTrans();
        try{
            $rider_updata['status'] = 2;
            $rider_updata['update_time'] = date('Y-m-d H:i:s',time());
            Db::name('rider_order')->where('id',$rider_order_id)->update($rider_updata);

            // 修改订单状态
            $order_update['update_time'] = date('Y-m-d H:i:s',time());
            $order_update['status'] = 5;
            Db::name('orders')->where(['id'=>$rider_order['order_id'],'pay_status'=>2,'status'=>4])->update($order_update);

            // 骑手余额增加
            $balance = rider_mod::where('id',$rider_order['rider_id'])->value('balance');
            // if($rider_info){
            $wx_ratio = SysSetting::where('id',1)->value('wx_ratio');
            $convey_price = Db::name('orders')->where(['id'=>$rider_order['order_id']])->value('convey_price');
            $rider_info_updata['balance'] = $balance + ($convey_price - $convey_price*$wx_ratio);
            $rider_info_updata['update_time'] = date('Y-m-d H:i:s',time());

           $res = Db::name('rider')->where('id',$rider_order['rider_id'])->update($rider_info_updata);
            // }

            Db::commit(); 
            $res = 1;
        } catch (\Exception $e) {
            // 回滚事务
            Db::rollback();
            $res = 0;
        }

        if($res){
            return json(['code'=>200,'msg'=>'操作成功']);
        }else{
            return json(['code'=>201,'msg'=>'操作失败']);
        }
        
        
            // return json(['code'=>200,'msg'=>'操作成功']);
        // }else{
        //     return json(['code'=>201,'msg'=>'操作失败']);
        // }
    }

    // 未送达
    public function undeliveredRiderOder(){
        verify('rider_order_id,remarks');
        $rider_order_id = input('rider_order_id');
        $remarks = input('remarks');

        $maps = [
            'status' => 1,
            'id' => $rider_order_id,
        ];

        $rider_order = RiderOrder::where($maps)->find();
        if(empty($rider_order)){
            return json(['code'=>202,'msg'=>'骑手订单不存在']);
        }

        
        // if(!empty($res)){
        // 启动事务
        Db::startTrans();
        try{
            $rider_order->status = 3;
            $rider_order->remarks = $remarks;
            $rider_order->update_time = date('Y-m-d H:i:s',time());
            $res = $rider_order->save();

            // 修改订单状态
            $order = Orders::where(['id'=>$rider_order['order_id'],'pay_status'=>2,'status'=>4])->field('id,status,update_time')->find();

            $order->update_time = date('Y-m-d H:i:s',time());
            $order->status = 6;
            $order->save();

            Db::commit(); 
            $res = 1;
        } catch (\Exception $e) {
            // 回滚事务
            Db::rollback();
            $res = 0;
        }
        if($res){
            return json(['code'=>200,'msg'=>'操作成功']);
        }else{
            return json(['code'=>201,'msg'=>'操作失败']);
        }
    }

    // 统计订单
    public function countRiderOrder(){
        verify('rider_id');
        $rider_id = input('rider_id');
        $type = input('type') ? input('type') : 1;

        $maps = [
            'rider_id' => $rider_id,
        ];

        switch ($type){
            case '1'://今日订单
                $starttime = date('Y-m-d',time()).' 00:00:00';
                $endtime = date('Y-m-d',time()).' 23:59:59';
                $maps['create_time'] = ['between',[$starttime,$endtime]];
                break;
            case '2'://月订单
                $starttime = date('Y-m',time()).'-01 00:00:00';
                $endtime = date('Y-m',time()).'-31 23:59:59';
                $maps['create_time'] = ['between',[$starttime,$endtime]];
               
                break;
        }

        $count['zj'] = RiderOrder::where($maps)->count();//总计
        $count['ysd'] = RiderOrder::where($maps)->where('status',2)->count();//总计
        $count['wsd'] = RiderOrder::where($maps)->where('status',3)->count();//总计

        // 最近4个月
        $before = [];
        if($type ==2){
            $before_maps = [
                'rider_id' => $rider_id,
            ];
            for($i=1;$i<5;$i++){
                
              $before[$i-1]['month'] = $month = date('m',strtotime('-1 month',strtotime($starttime))); 
              $before[$i-1]['year'] = $year = date('Y',strtotime('-1 month',strtotime($starttime)));
              
              $starttime = date($year.'-'.$month.'-d',time()).' 00:00:00';
              $endtime = date($year.'-'.$month.'-d',time()).' 23:59:59';
              $before_maps['create_time'] = ['between',[$starttime,$endtime]];

              $before[$i-1]['zj'] = RiderOrder::where($before_maps)->count();//总计 
              $before[$i-1]['ysd'] = RiderOrder::where($before_maps)->where('status',2)->count(); 
              $before[$i-1]['wsd'] = RiderOrder::where($before_maps)->where('status',3)->count(); 
            }
        }
        return json(['code'=>200,'msg'=>'操作成功','count'=>$count,'before'=>$before]);
    }
}
