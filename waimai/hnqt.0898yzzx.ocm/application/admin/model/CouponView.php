<?php
/**
 * User: rice
 * Date: 2020/10/28
 * Time: 15:17
 */
namespace app\admin\model;

use think\Model;
use util\listData;

class CouponView extends Model
{
    public function user(){
        return $this->belongsTo('User','user_id')->field('id,nickname');
    }

    //获取列表数据
    public function getListData($maps, $searchFields, $limit, $page){

        if (!empty($searchFields['field_name']) && !empty($searchFields['field_content'])) {
            $key = $searchFields['field_name'];
            $value = $searchFields['field_content'];
            $maps[$key] = ['like', '%' . $value . '%'];
        }

        if(!empty($searchFields['select_time'])){
            $select_time_list = explode("~", $searchFields['select_time']);
            $start_time = $select_time_list[0]." 00:00:00";
            $end_time = $select_time_list[1]." 23:59:59";
            
            $maps['create_time'] = ['between',[$start_time,$end_time]];
        }

        if(!empty($searchFields['discount_money'])){
            
            $maps['discount_money'] = $searchFields['discount_money'];
        }

        if(!empty($searchFields['campus_id'])){
            
            $maps['campus_id'] = $searchFields['campus_id'];
        }
        if(!empty($searchFields['store_id'])){
            $maps['store_id'] = $searchFields['store_id'];
        }
        
        if(session('admin.is_root') == 0){
            $maps['store_id'] = session('admin.store_id');
        }
        $result = $this->with('user')->where($maps)->limit($limit)->page($page)->order('create_time desc')->select();
        if(!empty($result)){
            foreach($result as $k=>$v){
                if($v['discount_money']>5){//说明使用了月卡
                    $result[$k]['discount_money']=$v['discount_money']-5;
                }
            }
        }
       // $amount_arr = $this->where($maps)->field('SUM(discount_money) as amount')->select();
        $amount_arr = $this->field('discount_money ')->select();
        $amount=0;
        if(!empty($amount_arr)){
            foreach($result as $k=>$v){
                if($v['discount_money']>5){//说明使用了月卡
                    $result[$k]['discount_money']=$v['discount_money']-5;
                }
                $amount +=$result[$k]['discount_money'];
            }
        }
        $count = $this->where($maps)->count();
        //$amount = number_format($amount_arr[0]['amount'],2);
        $count_arr['amount'] = $amount;
        return new listData($result, $count,'200','获取成功',$count_arr);
    }

}