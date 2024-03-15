<?php
namespace app\admin\model;

use think\Model;
use think\Db;
use util\listData;

class MonthCard extends Model
{
    // protected $updateTime = 'last_login_time';
    public function coupon(){
        return $this->belongsTo('Coupon','coupon_id')->field('id,name,discount_money,full_money,start_time,end_time');
    }
    public function user(){
        return $this->belongsTo('User','user_id')->field('id,nickname');
    }
     //获取列表数据
    public function getListData($maps, $searchFields, $limit, $page){
        if (session('admin.store_id') != null && session('admin.is_root') == 0) {
            $maps['store_id'] = session('admin.store_id');
        }

        if (!empty($searchFields['field_name']) && !empty($searchFields['field_content'])) {
            $key = $searchFields['field_name'];
            $value = $searchFields['field_content'];
            $maps[$key] = ['like', '%' . $value . '%'];
        }

        /*  if (session('admin.store_id') != null) {
              $maps['store_id'] = session('admin.store_id');
          }*/

        if(session('admin.id')==48){//琼台超级管理员
            $maps['campus_id'] = 4;
        }

        $result = $this->with('coupon,user')->where($maps)->limit($limit)->page($page)->order('create_time desc')->select();

        $count = $this->where($maps)->count();
        return new listData($result, $count);
    }

    public function editCard($data){
        
        if (!isset($data['id']) || empty($data['id'])) {
            $result = $this->validate(true)->allowField(true)->data($data)->save();
            if($result) {
                return true;
            }else {
                return false;
            }
        }else {
            $id = $data['id'];
            $portal = $this->where('id', $id)->find();
            $result = $portal->validate(true)->allowField(true)->except('id')->data($data)->save();
            if ($result) {
                return true;
            }else {
                return false;
            }
        }
    }

    // 根据条件查询月卡
    public function getMonthCard($maps=[],$count=''){
        
        $maps['end_time'] = ['>',date('Y-m-d H:i:s',time())];
        $maps['status'] = 1;
        if(empty($count)){
            $maps['count'] =  ['>',0];
        }
        
        $info = $this->with('coupon')->where($maps)->find();
        return $info;
    }

    // 扣减月卡次数
    public function deductCount($card_id){
        if(empty($card_id)){
            return false;
        }

        // 查询月卡
        $maps = [
            'id' => $card_id
        ];

        $info = $this->getMonthCard($maps);
        if(empty($info)){
            return false;
        }

        // 扣减次数
        $res = Db::name('month_card')->where($maps)->update(['count'=>$info['count']-1,'update_time'=>date('Y-m-d H:i:s')]);
        if($res){
            return true;
        }else{
            return false;
        }

    }

    // 增加月卡次数
    public function addCount($card_id){
        if(empty($card_id)){
            return false;
        }

        // 查询月卡
        $maps = [
            'id' => $card_id
        ];

        $info = $this->getMonthCard($maps,1);
        if(empty($info)){
            return false;
        }

        // 增加次数
        $res = Db::name('month_card')->where($maps)->update(['count'=>$info['count']+1,'update_time'=>date('Y-m-d H:i:s')]);
        if($res){
            return true;
        }else{
            return false;
        }
    }
    
    // 新增月卡
    public function add($user_id='',$total_price=15,$discount_money=5,$full_money=0)
    {

        $user_id = !empty($user_id) ? $user_id : session('user.id');
        $total_price = !empty($total_price) ? $total_price : 15;
        $discount_money = !empty($discount_money) ? $discount_money : 5;
        $full_money = !empty($full_money) ? $full_money : 0;

        $start_time = $create_time = date("Y-m-d H:i:s",time());
        $end_time = date("Y-m-d H:i:s", strtotime("+1 months", strtotime($start_time)));

        // 启动事务
        Db::startTrans();

        // 生成优惠券
        $coupon_arr = [];
        $coupon_arr['type'] = 2;
        $coupon_arr['name'] = '月卡';
        $coupon_arr['discount_money'] = $discount_money;
        $coupon_arr['full_money'] = $full_money;
        $coupon_arr['start_time'] = $start_time;
        $coupon_arr['end_time'] = $end_time;
        $coupon_arr['create_time'] = $create_time;

        $coupon_id = Db::name('coupon')->insertGetId($coupon_arr);
        if(empty($coupon_id)){
            Db::rollback();
            return false;
        }

        //订单数据生成
        $orders_data = [];

        $orders_data['card_number'] = build_order_no();
        $orders_data['user_id'] = $user_id;
        $orders_data['coupon_id'] = $coupon_id;
        $orders_data['total_price'] = $total_price;
        $orders_data['count'] = 6;
        $orders_data['status'] = 0;
        $orders_data['start_time'] = $start_time;
        $orders_data['end_time'] = $end_time;
        $orders_data['create_time'] = $start_time; 
        /*订单数据生成完成，准备插入数据*/
        $insert_orders_id = Db::name('month_card')->where('card_number','<>',$orders_data['card_number'])->insertGetId($orders_data);
        if(empty($insert_orders_id)){
            Db::rollback();
            return false;
        }

        Db::commit(); 
        return ['card_number'=>$orders_data['card_number'],'insert_orders_id'=>$insert_orders_id,'total_price'=>$total_price];
    }

    // 月卡新增日志
    public function addLog($card_id,$status,$order_id)
    {
        // Db::name('pay_test')->insert(['msg'=>'执行方法开始-新增月卡开始：']);
        if(empty($card_id) || empty($status) || empty($order_id)){
            return false;
        }
        // Db::name('pay_test')->insert(['msg'=>'新增月卡开始：']);
        // 查询月卡
        $maps = [
            'id' => $card_id
        ];

        $info = $this->getMonthCard($maps,1);
        if(empty($info)){
            return false;
        }
        // Db::name('pay_test')->insert(['msg'=>'查询到月卡：']);
        $order = Orders::where('id',$order_id)->field('id,order_number')->find();
        if(empty($order)){
            return false;
        }
        // Db::name('pay_test')->insert(['msg'=>'查询到订单：']);
        $insert_arr = [
            'card_id' => $card_id,
            'order_number' => $order['order_number'],
            'order_id' => $order_id,
            'user_id' => $info['user_id'],
            'status' => $status,
            'create_time' => date('Y-m-d H:i:s')
        ];

        // 增加日志
        $res = Db::name('month_cart_log')->insertGetId($insert_arr);
        if($res){
             // Db::name('pay_test')->insert(['msg'=>'日志成功：']);
            return true;
        }else{
            return false;
            // Db::name('pay_test')->insert(['msg'=>'日志失败：']);
        }
    }
}