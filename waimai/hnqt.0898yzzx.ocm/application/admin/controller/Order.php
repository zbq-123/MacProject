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
use app\admin\model\Orders;
use app\admin\model\OrdersTimes;
use app\common\controller\AdminBase;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use think\Db;
use util\Ret;
use app\store\common\AppPrint;
use app\admin\model\Store;
use app\admin\model\User;
use app\admin\model\UserAddress;

class Order extends AdminBase
{


    public function order()
    {
        if (request()->isAjax()) {
            $store = new Orders();
            $fields = input('param.fields/a');
            $type = input('param.status/d');
            $paystatu = input('param.paystatus/d');

            $map = [];
            if ($paystatu) {
                if ($paystatu == 1) {
                    /*$map['status'] = array('in','0,3,4');*/
                    $map['pay_status'] = 2;
                } else if ($paystatu == 2) {
                    $map['pay_status'] = 3;
                }


            }


            if ($type) {


                if ($type == 1) {
                    $map['status'] = 2;
                } else if ($type == 2) {
                    $map['status'] = 3;
                } else if ($type == 3) {
                    $map['status'] = 7;
                } else if ($type == 4) {
                    $map['status'] = 8;
                } else if ($type == 5) {
                    $map['status'] = 9;
                } else if ($type == 6) {
                    $map['status'] = 10;
                } else if ($type == 7) {
                    $map['status'] = 11;
                } else if ($type == 8) {
                    $map['status'] = 12;
                } else if ($type == 9) {
                    $map['status'] = 13;
                } else if ($type == 10) {
                    $map['status'] = 14;
                } else if ($type == 11) {
                    $map['status'] = 15;
                }


            }
            /* $map['admin_id'] = $this->adminId;*/
            $map['type'] =1;
            $result = $store->getListData($map, $fields, $this->limit, $this->page);
            return json($result);
        }
        return $this->fetch();
    }

    //订单修改
    public function status($id, $status)
    {
        $store = \app\admin\model\Store::get($id);
        if ($store['status'] == 1) {
            $store->status = 2;
            $res = $store->save();

            if ($res) {
                return json(new Ret($store['status']));
            } else {
                abort(608);
            }

        } else {
            $store->status = 1;
            $res = $store->save();
            if ($res) {
                return json(new Ret($store['status']));
            } else {
                abort(608);
            }
        }
    }

    //设置置顶序号
    public function edit_sort()
    {
        $media = \app\admin\model\Store::get(input('post.id'));
        $media->sort = input('post.sort/d');
        $result = $media->save();
        if ($result) {
            return json(new Ret());
        } else {
            abort(608);
        }
    }

    //添加历史订单订单
    public function add_history_order()
    {
        if (request()->post()) {
            $data = request()->except(['status7_time'], 'post');

            $status7_time = request()->post('status7_time');

            if ($status7_time) {
                $data['status7_time'] = $status7_time;
            }
            $campus = Campus::get($data['campus_id']);
            $data['campus_name'] = $campus['name'];
            $store_name = Store::get($data['store_id']);
            $data['store_name'] = $store_name['name'];

            $colum = new Orders();
            $result = $colum->editOrder($data);
            if ($result) {
                $this->success('操作成功', 'order/history_order', null, 1);

            } else {
                $this->error($colum->getError());

            }
        }

        //获取店铺列表
        $store = new Store();
        $storemaps = [];
        $storemaps['deleted'] = 0;
        $store_result = $store->get_history_order_storeName($storemaps);
//        dump($store_result);
        $this->assign('store', $store_result);

        $place = Campus::where('deleted', 0)->order('id asc')->field('id,name')->select();
        $this->assign('campus', $place);

        return $this->fetch();


    }

    //查看添加的历史订单
    public function history_order()
    {
        if (request()->isAjax()) {
            $store = new Orders();
            $fields = input('param.fields/a');
            $map = [];

            $map['type'] =2;
            $result = $store->getListData($map, $fields, $this->limit, $this->page);
            return json($result);
        }
        return $this->fetch();


    }

    //确认订单
    public function confirm()
    {
        $subject = Orders::where(['id' => input('post.id')])->find();

        $times = new OrdersTimes();
        $subject['admin_id'] = $this->adminId;
        $updatime = $times->editTime($subject, 3);

        if (false == $subject) {
            abort(608, '资源不存在');
        }
        $subject->status = 3;
        $result = $subject->save();
        if ($result) {
            return json(new Ret());
        } else {
            abort(608);
        }
    }

    //删除订单
    public function delete_order()
    {
        $subject = Orders::where(['id' => input('post.id')])->find();
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

    //订单状态改变
    public function edit_status()
    {
        $statuss = request()->get('status');

        $id = request()->get('id');
//        if ($statuss == 7) {
//            $money = Orders::where('id', $id)->field('total_price,store_id')->find();
//            $total = \app\admin\model\Store::where('id', $money['store_id'])->field('balance,revenue')->find();
//            $a = $total['balance'] + $money['total_price'];
//            $b = $total['revenue'] + $money['total_price'];
//            $records= \app\admin\model\StoreAmountRecords::where('id', $money['store_id'])->field('old_balance,now_balance')->find();
//            $c = $records['old_balance'] +  $money['total_price'];
//
//
//
//            Db::table('store')->where('id', $money['store_id'])->update(['balance' => $a,'revenue' => $b]);
//            Db::table('store_amount_records')
//                ->insert([
//
//                    'now_balance'=>$c,
//                    'old_balance'=>$records['old_balance'] ,
//                    'status'=>1,
//                    'admin_id'=>$money['store_id'],
//                ]);
//
//
//        }

        $data['admin_id'] = $this->adminId;
        $column = new Orders();

        $result = $column->editStatus($id, $statuss);
//        $times = new OrdersTimes();
//        $updatime = $times->editTime($id, $statuss);
        if ($result) {
            $this->success("操作成功", 'order/order', null, 1);
        } else {
            $this->error($column->getError());
        }


    }

    //打印平台报表 按日范围
    public function inspection()
    {
        $campus_id = request()->param('campus_id');

        $store_id = request()->param('store_id') ? request()->param('store_id') : session('admin.store_id');

        $select_time = request()->param('select_time');


        $maps = [];
        if ($campus_id) {
            $maps['campus_id'] = $campus_id;
        }

        if ($store_id && session('admin.is_root')==0) {
            $maps['id'] = $store_id;
        }

        if(session('admin.id')==48){//琼台超级管理员
            $maps['campus_id'] = 4;
        }

        $store_list = Db::name('store')
            ->where($maps)
            ->where('deleted', 0)
            ->field('id,name,delivery_price')
            ->select();

        if ($select_time) {
            $select = $select_time.'~'.$select_time;
            $select_time_list = explode("~", $select);
            $days = getDateFromRange($select_time_list[0] . ' 00:00:00', $select_time_list[1] . ' 23:59:59');
        } else {
            $days = getDateFromRange(date("Y-m-d H:i:s"), date("Y-m-d H:i:s"));
        }

        $orders_model = new Orders();

        $count_res = $orders_model->getOrdersDayCount($store_list, $days);

        $this->assign('data', $count_res);

        $this->assign('campus_id', $campus_id);
        $this->assign('store_id', $store_id);
        $this->assign('select_time', $select_time);

        return $this->fetch();
    }

    //打印平台报表 按月范围
    // public function inspection_month()
    // {
    //     $campus_id = request()->param('campus_id');

    //     $store_id = request()->param('store_id');

    //     $select_time = request()->param('select_time');


    //     $maps = [];
    //     if ($campus_id) {
    //         $maps['campus_id'] = $campus_id;
    //     }

    //     if ($store_id) {
    //         $maps['id'] = $store_id;
    //     }

    //     $store_list = Db::name('store')
    //         ->where($maps)
    //         ->where('deleted', 0)
    //         ->field('id,name')
    //         ->select();

    //     if ($select_time) {
    //         $select_time_list = explode("~", $select_time);
    //         $months = getDateFromMonths($select_time_list[0], $select_time_list[1]);
    //     } else {
    //         $months = getDateFromMonths(date("Y-m"), date("Y-m"));
    //     }


    //     $orders_model = new Orders();

    //     $count_res = $orders_model->getOrdersMonthCount($store_list, $months);

    //     $this->assign('data', $count_res);

    //     $this->assign('campus_id', $campus_id);
    //     $this->assign('store_id', $store_id);
    //     $this->assign('select_time', $select_time);

    //     return $this->fetch();
    // }
    
     //打印平台报表 按月范围 改 rice edit 2020-10-29
    public function inspection_month()
    {
        $campus_id = request()->param('campus_id');
        $store_id = request()->param('store_id') ? request()->param('store_id') : session('admin.store_id');
        $select_time = request()->param('select_time');
        if($select_time){
            $select_time_list = explode("~", $select_time);
            $start_time = $select_time_list[0]." 00:00:00";
            $end_time = $select_time_list[1]." 23:59:59";
            $month['month'] = $select_time;
        }else{
            $d = date('Y-m',time());
            $start_time = date('Y-m-26 00:00:00', strtotime($d." -1 month"));
            $end_time = date('Y-m-25 23:59:59', strtotime($d));
            $month['month'] = date('Y-m', strtotime($d." -1 month")).'~'.date('Y-m-25', strtotime($d));
        }
        
        $maps = [];
        if ($campus_id) {
            $maps['campus_id'] = $campus_id;
        }

        if ($store_id && session('admin.is_root')==0) {
            $maps['id'] = $store_id;
        }

        if(session('admin.id')==48){//琼台超级管理员
            $maps['campus_id'] = 4;
        }
        $store_list = Db::name('store')
            ->where($maps)
            ->where('deleted', 0)
            ->field('id,name,delivery_price')
            ->select();

        $orders_model = new Orders();    

        $all_data = [];
        $datas = [];
        $count_data = [
            'order_count'=>0,
            'discount_money'=>0,
            'month_card_money'=>0,
            'coupon_money'=>0,
            'total_price'=>0,
            'rider_price'=>0,
            'develop_price'=>0,
            'manage_price'=>0,
            'pay_manage_price'=>0,
            'store_price'=>0
        ];
        $data = [
            'month_card_money'=>0,
            'coupon_money'=>0,
        ];
        foreach ($store_list as $store){
            $data = $orders_model->where('deleted', 0)
                ->where('store_id', $store['id'])
                ->where('status', 7)
                ->where('pay_status', 2)
                ->where('status7_time', '>=', $start_time)
                ->where('status7_time', '<=', $end_time)
                ->field('COUNT(1) as order_count,
                IFNULL(SUM(total_price),0) as total_price,
                IFNULL(SUM(develop_price),0) as develop_price,
                IFNULL(SUM(manage_price),0) as manage_price,
                IFNULL(SUM(pay_manage_price),0) as pay_manage_price,
                COUNT(use_month_card) as month_card_count,
                COUNT(use_coupon) as coupon_count,
                IFNULL(SUM(store_price),0) as store_price, 
                IFNULL(SUM(discount_money),0) as discount_money')
                ->find()->toArray();

            $data['date'] = $month['month'];
            $data['store_name'] = $store['name'];
            $data['rider_price'] = $data['order_count']*$store['delivery_price'];
            $data['discount_money']=$data['discount_money'];
            $data['month_card_money']=$data['month_card_count']*5;
            $data['coupon_money']    =$data['discount_money']-$data['month_card_count']*5;
            $datas[] = $data;

            $count_data['order_count'] += $data['order_count'];
            $count_data['total_price'] += $data['total_price'];
            $count_data['month_card_money']+=$data['month_card_count']*5;
            $count_data['coupon_money']    +=$data['discount_money']-$data['month_card_count']*5;
            $count_data['discount_money'] += $data['discount_money'];

            $count_data['rider_price'] += $data['rider_price'];
            $count_data['develop_price'] += $data['develop_price'];
            $count_data['manage_price'] += $data['manage_price'];
            $count_data['pay_manage_price'] += $data['pay_manage_price'];
            $count_data['store_price'] += $data['store_price'];

        }


   
        $all_data['data'] = $datas;
        $all_data['count'] = $count_data;

        $this->assign('data', $all_data);
        $this->assign('campus_id', $campus_id);
        $this->assign('store_id', $store_id);
        $this->assign('select_time', $select_time);

        return $this->fetch();
    }

    //打印平台报表 年度汇总
    public function inspection_year()
    {
        $campus_id = request()->param('campus_id');

        $store_id = request()->param('store_id');

        $select_time = request()->param('select_time');


        $maps = [];
        if ($campus_id) {
            $maps['campus_id'] = $campus_id;
        }

        if ($store_id) {
            $maps['id'] = $store_id;
        }
        if(session('admin.id')==48){//琼台超级管理员
            $maps['campus_id'] = 4;
        }
        $store_list = Db::name('store')
            ->where($maps)
            ->where('deleted', 0)
            ->field('id,name,delivery_price')
            ->select();

        if ($select_time) {
            $year = $select_time;
        } else {
            $year = date("Y");
        }

        $orders_model = new Orders();

        $count_res = $orders_model->getOrdersYearCount($store_list, $year);

        $this->assign('data', $count_res);

        $this->assign('campus_id', $campus_id);
        $this->assign('store_id', $store_id);
        $this->assign('select_time', $select_time);

        return $this->fetch();
    }

    //打印平台报表 季度汇总
    public function inspection_quarter()
    {
        $campus_id = request()->param('campus_id');

        $store_id = request()->param('store_id');

        $select_time = request()->param('select_time');


        $maps = [];
        if ($campus_id) {
            $maps['campus_id'] = $campus_id;
        }

        if ($store_id) {
            $maps['id'] = $store_id;
        }
        if(session('admin.id')==48){//琼台超级管理员
            $maps['campus_id'] = 4;
        }
        $store_list = Db::name('store')
            ->where($maps)
            ->where('deleted', 0)
            ->field('id,name,delivery_price')
            ->select();

        $quarter = [];

        $orders_model = new Orders();

        if ($select_time) {
            $select_time_list = explode("-", $select_time);
            switch ($select_time_list[1]){
                case '第1季度':
                    $quarter['first'] = $select_time_list[0].'-01-01 00:00:00';
                    $quarter['last'] = $select_time_list[0].'-03-31 23:59:59';
                    break;
                case '第2季度':
                    $quarter['first'] = $select_time_list[0].'-04-01 00:00:00';
                    $quarter['last'] = $select_time_list[0].'-06-30 23:59:59';
                    break;
                case '第3季度':
                    $quarter['first'] = $select_time_list[0].'-07-01 00:00:00';
                    $quarter['last'] = $select_time_list[0].'-09-30 23:59:59';
                    break;
                case '第4季度':
                    $quarter['first'] = $select_time_list[0].'-10-01 00:00:00';
                    $quarter['last'] = $select_time_list[0].'-12-31 23:59:59';
                    break;
                default:
                    $quarter['first'] = $select_time_list[0].'-01-01 00:00:00';
                    $quarter['last'] = $select_time_list[0].'-03-31 23:59:59';
                    break;
            }

            $count_res = $orders_model->getOrdersQuarterCount($store_list, $quarter,$select_time);
        } else {
            if(date("m-d") >= '01-01' && date("m-d") <= '03-31'){
                $auto_time = date("Y").'第1季度';
                $quarter['first'] = date("Y").'-01-01 00:00:00';
                $quarter['last'] = date("Y").'-03-31 23:59:59';

            }else if(date("m-d") >= '04-01' && date("m-d") <= '06-30'){
                $auto_time = date("Y").'第2季度';
                $quarter['first'] = date("Y").'-04-01 00:00:00';
                $quarter['last'] = date("Y").'-06-30 23:59:59';

            }else if(date("m-d") >= '07-01' && date("m-d") <= '09-30'){
                $auto_time = date("Y").'第3季度';
                $quarter['first'] = date("Y").'-07-01 00:00:00';
                $quarter['last'] = date("Y").'-09-30 23:59:59';

            }else if(date("m-d") >= '10-01' && date("m-d") <= '12-31'){
                $auto_time = date("Y").'第4季度';
                $quarter['first'] = date("Y").'-10-01 00:00:00';
                $quarter['last'] = date("Y").'-12-31 23:59:59';

            }else{
                $auto_time = date("Y").'第1季度';
                $quarter['first'] = date("Y").'-01-01 00:00:00';
                $quarter['last'] = date("Y").'-03-31 23:59:59';
            }

            $count_res = $orders_model->getOrdersQuarterCount($store_list, $quarter,$auto_time);
        }


        $this->assign('data', $count_res);

        $this->assign('campus_id', $campus_id);
        $this->assign('store_id', $store_id);
        $this->assign('select_time', $select_time);

        return $this->fetch();
    }

    //打印平台报表 半年汇总
    public function inspection_half_year()
    {
        $campus_id = request()->param('campus_id');

        $store_id = request()->param('store_id');

        $select_time = request()->param('select_time');


        $maps = [];
        if ($campus_id) {
            $maps['campus_id'] = $campus_id;
        }

        if ($store_id) {
            $maps['id'] = $store_id;
        }
        if(session('admin.id')==48){//琼台超级管理员
            $maps['campus_id'] = 4;
        }
        $store_list = Db::name('store')
            ->where($maps)
            ->where('deleted', 0)
            ->field('id,name,delivery_price')
            ->select();

        $quarter = [];

        $orders_model = new Orders();

        if ($select_time) {
            $select_time_list = explode("-", $select_time);
            switch ($select_time_list[1]){
                case '上半年':
                    $quarter['first'] = $select_time_list[0].'-01-01 00:00:00';
                    $quarter['last'] = $select_time_list[0].'-06-30 23:59:59';
                    break;
                case '下半年':
                    $quarter['first'] = $select_time_list[0].'-07-01 00:00:00';
                    $quarter['last'] = $select_time_list[0].'-12-31 23:59:59';
                    break;
                default:
                    $quarter['first'] = $select_time_list[0].'-01-01 00:00:00';
                    $quarter['last'] = $select_time_list[0].'-06-30 23:59:59';
                    break;
            }

            $count_res = $orders_model->getOrdersHalfYearCount($store_list, $quarter,$select_time);
        } else {
            if(date("m-d") >= '01-01' && date("m-d") <= '06-30'){
                $auto_time = date("Y").'-上半年';
                $quarter['first'] = date("Y").'-01-01 00:00:00';
                $quarter['last'] = date("Y").'-06-30 23:59:59';

            }else if(date("m-d") >= '07-01' && date("m-d") <= '12-31'){
                $auto_time = date("Y").'-下半年';
                $quarter['first'] = date("Y").'-07-01 00:00:00';
                $quarter['last'] = date("Y").'-12-31 23:59:59';

            }else{
                $auto_time = date("Y").'-上半年';
                $quarter['first'] = date("Y").'-01-01 00:00:00';
                $quarter['last'] = date("Y").'-06-30 23:59:59';
            }

            $count_res = $orders_model->getOrdersHalfYearCount($store_list, $quarter,$auto_time);
        }


        $this->assign('data', $count_res);

        $this->assign('campus_id', $campus_id);
        $this->assign('store_id', $store_id);
        $this->assign('select_time', $select_time);

        return $this->fetch();
    }


    //生成报表 xls文件 按日
    public function produce_doc()
    {
        $campus_id = request()->param('campus_id');

        $store_id = request()->param('store_id');

        $select_time = request()->param('select_time');


        $maps = [];
        if ($campus_id) {
            $maps['campus_id'] = $campus_id;
        }

        if ($store_id) {
            $maps['id'] = $store_id;
        }

        $store_list = Db::name('store')
            ->where($maps)
            ->where('deleted', 0)
            ->field('id,name,delivery_price')
            ->select();

        if ($select_time) {
            //$select_time_list = explode("~", $select_time);
            $select = $select_time.'~'.$select_time;
            $days = getDateFromRange($select[0] . ' 00:00:00', $select[1] . ' 23:59:59');
        } else {
            $days = getDateFromRange(date("Y-m-d H:i:s"), date("Y-m-d H:i:s"));
        }

        $orders_model = new Orders();

        $count_res = $orders_model->getOrdersDayCount($store_list, $days);

        $path = ROOT_PATH . DS . 'public' . DS . 'uploads';
        $path1 = ROOT_PATH . DS . 'public' . DS . 'static';
        //以下代码写数据到excel表

        $uploadfile = $path1 . "/model/" . 'hsst_takeout_day_sale.xlsx';
        $reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReader('Xlsx'); //设置以Excel5格式(Excel97-2003工作簿)
        $PHPExcel = $reader->load($uploadfile); // 载入excel文件
        $sheet = $PHPExcel->getActiveSheet(1);
        /*......*/
        //得数据

        /*写数据*/
        $line = 0;
        foreach ($count_res['data'] as $key => $set_value) {
            $line = $key + 4;

            $sheet->getStyle('A' . $line . ':H' . $line)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);
            $sheet->getStyle('A' . $line . ':H' . $line)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
            $sheet->getRowDimension($line)->setRowHeight(29);

            $sheet->setCellValue('A' . $line, $set_value['date']);
            $sheet->setCellValue('B' . $line, $set_value['store_name']);
            $sheet->setCellValue('C' . $line, $set_value['order_count']);
            $sheet->setCellValue('D' . $line, $set_value['total_price'] / 100);
            $sheet->setCellValue('E' . $line, $set_value['develop_price'] / 100);
            $sheet->setCellValue('F' . $line, $set_value['manage_price'] / 100);
            $sheet->setCellValue('G' . $line, $set_value['pay_manage_price'] / 100);
            $sheet->setCellValue('H' . $line, $set_value['store_price'] / 100);
        }

        $line++;

        $sheet->getStyle('A' . $line . ':H' . $line)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);
        $sheet->getStyle('A' . $line . ':H' . $line)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
        $sheet->getRowDimension($line)->setRowHeight(29);

        $sheet->setCellValue('A' . $line, '合计');
        $sheet->mergeCells('A' . $line . ':B' . $line);
        $sheet->setCellValue('C' . $line, $count_res['count']['order_count']);
        $sheet->setCellValue('D' . $line, $count_res['count']['total_price'] / 100);
        $sheet->setCellValue('E' . $line, $count_res['count']['develop_price'] / 100);
        $sheet->setCellValue('F' . $line, $count_res['count']['manage_price'] / 100);
        $sheet->setCellValue('G' . $line, $count_res['count']['pay_manage_price'] / 100);
        $sheet->setCellValue('H' . $line, $count_res['count']['store_price'] / 100);

        $line++;

        $sheet->getStyle('A' . $line . ':H' . $line)->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
        $sheet->getRowDimension($line)->setRowHeight(29);

        $sheet->setCellValue('A' . $line, '经办人：');
        $sheet->setCellValue('B' . $line, '系统复核：');
        $sheet->setCellValue('C' . $line, '核对人（平台维护人员）：');
        $sheet->mergeCells('C' . $line . ':D' . $line);
        $sheet->setCellValue('E' . $line, '食堂经理：');
        $sheet->setCellValue('F' . $line, '主任：');
        $sheet->setCellValue('G' . $line, '审核：');
        $sheet->setCellValue('H' . $line, '');


        $writer = new Xlsx($PHPExcel);

        $new_file = $path . "/order_file/";
        if (!file_exists($new_file)) {
            //检查是否有该文件夹，如果没有就创建，并给予最高权限
            mkdir($new_file, 0700);
        }

        $new_file = $path . "/order_file/day/";
        if (!file_exists($new_file)) {
            //检查是否有该文件夹，如果没有就创建，并给予最高权限
            mkdir($new_file, 0700);
        }

        $new_file = $path . "/order_file/day/" . date('Ymd', time()) . "/";
        if (!file_exists($new_file)) {
            //检查是否有该文件夹，如果没有就创建，并给予最高权限
            mkdir($new_file, 0700);
        }

        $writer->save($path . "/order_file/day/" . date('Ymd', time()) . "/" . "hsst_takeout_day_sale_" . date('Ymd', time()) . ".xlsx");
        return new Ret(config('web_config.upload_host') . "/order_file/day/" . date('Ymd', time()) . "/" . "hsst_takeout_day_sale_" . date('Ymd', time()) . ".xlsx");


    }

    //生成报表 xls文件 按月
    public function inspection_month_doc()
    {
        $campus_id = request()->param('campus_id');

        $store_id = request()->param('store_id');

        $select_time = request()->param('select_time');


        $maps = [];
        if ($campus_id) {
            $maps['campus_id'] = $campus_id;
        }

        if ($store_id) {
            $maps['id'] = $store_id;
        }

        $store_list = Db::name('store')
            ->where($maps)
            ->where('deleted', 0)
            ->field('id,name')
            ->select();

        if ($select_time) {
            $select_time_list = explode("~", $select_time);
            // $months = getDateFromMonths($select_time_list[0], $select_time_list[1]);
            $months["dates"][0] = [
                "month" => $select_time_list[0],
                "first" => $select_time_list[0]." 00:00:00",
                "last" => $select_time_list[1]." 23:59:59",
            ];
            $months["count"] = 1;
        } else {
            $months = getDateFromMonths(date("Y-m"), date("Y-m"));
        }


        $orders_model = new Orders();

        $count_res = $orders_model->getOrdersMonthCount($store_list, $months);

        $path = ROOT_PATH . DS . 'public' . DS . 'uploads';
        $path1 = ROOT_PATH . DS . 'public' . DS . 'static';
        //以下代码写数据到excel表

        $uploadfile = $path1 . "/model/" . 'hsst_takeout_months_sale.xlsx';
        $reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReader('Xlsx'); //设置以Excel5格式(Excel97-2003工作簿)
        $PHPExcel = $reader->load($uploadfile); // 载入excel文件
        $sheet = $PHPExcel->getActiveSheet(1);
        /*......*/
        //得数据

        /*写数据*/
        $line = 0;
        foreach ($count_res['data'] as $key => $set_value) {
            $line = $key + 4;

            $sheet->getStyle('A' . $line . ':H' . $line)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);
            $sheet->getStyle('A' . $line . ':H' . $line)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
            $sheet->getRowDimension($line)->setRowHeight(29);

            $sheet->setCellValue('A' . $line, $set_value['date']);
            $sheet->setCellValue('B' . $line, $set_value['store_name']);
            $sheet->setCellValue('C' . $line, $set_value['order_count']);
            $sheet->setCellValue('D' . $line, $set_value['total_price'] / 100);
            $sheet->setCellValue('E' . $line, $set_value['develop_price'] / 100);
            $sheet->setCellValue('F' . $line, $set_value['manage_price'] / 100);
            $sheet->setCellValue('G' . $line, $set_value['pay_manage_price'] / 100);
            $sheet->setCellValue('H' . $line, $set_value['store_price'] / 100);
        }

        $line++;

        $sheet->getStyle('A' . $line . ':H' . $line)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);
        $sheet->getStyle('A' . $line . ':H' . $line)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
        $sheet->getRowDimension($line)->setRowHeight(29);

        $sheet->setCellValue('A' . $line, '合计');
        $sheet->mergeCells('A' . $line . ':B' . $line);
        $sheet->setCellValue('C' . $line, $count_res['count']['order_count']);
        $sheet->setCellValue('D' . $line, $count_res['count']['total_price'] / 100);
        $sheet->setCellValue('E' . $line, $count_res['count']['develop_price'] / 100);
        $sheet->setCellValue('F' . $line, $count_res['count']['manage_price'] / 100);
        $sheet->setCellValue('G' . $line, $count_res['count']['pay_manage_price'] / 100);
        $sheet->setCellValue('H' . $line, $count_res['count']['store_price'] / 100);

        $line++;

        $sheet->getStyle('A' . $line . ':H' . $line)->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
        $sheet->getRowDimension($line)->setRowHeight(29);

        $sheet->setCellValue('A' . $line, '经办人：');
        $sheet->setCellValue('B' . $line, '系统复核：');
        $sheet->setCellValue('C' . $line, '核对人（平台维护人员）：');
        $sheet->mergeCells('C' . $line . ':D' . $line);
        $sheet->setCellValue('E' . $line, '食堂经理：');
        $sheet->setCellValue('F' . $line, '主任：');
        $sheet->setCellValue('G' . $line, '审核：');
        $sheet->setCellValue('H' . $line, '');


        $writer = new Xlsx($PHPExcel);

        $new_file = $path . "/order_file/";
        if (!file_exists($new_file)) {
            //检查是否有该文件夹，如果没有就创建，并给予最高权限
            mkdir($new_file, 0700);
        }

        $new_file = $path . "/order_file/month/";
        if (!file_exists($new_file)) {
            //检查是否有该文件夹，如果没有就创建，并给予最高权限
            mkdir($new_file, 0700);
        }

        $new_file = $path . "/order_file/month/" . date('Ymd', time()) . "/";
        if (!file_exists($new_file)) {
            //检查是否有该文件夹，如果没有就创建，并给予最高权限
            mkdir($new_file, 0700);
        }

        $writer->save($path . "/order_file/month/" . date('Ymd', time()) . "/" . "hsst_takeout_months_sale_" . date('Ymd', time()) . ".xlsx");
        return new Ret(config('web_config.upload_host') . "/order_file/month/" . date('Ymd', time()) . "/" . "hsst_takeout_months_sale_" . date('Ymd', time()) . ".xlsx");


    }

    //生成报表 xls文件 年度汇算
    public function inspection_year_doc()
    {
        $campus_id = request()->param('campus_id');

        $store_id = request()->param('store_id');

        $select_time = request()->param('select_time');


        $maps = [];
        if ($campus_id) {
            $maps['campus_id'] = $campus_id;
        }

        if ($store_id) {
            $maps['id'] = $store_id;
        }

        $store_list = Db::name('store')
            ->where($maps)
            ->where('deleted', 0)
            ->field('id,name')
            ->select();

        if ($select_time) {
            $year = $select_time;
        } else {
            $year = date("Y");
        }

        $orders_model = new Orders();

        $count_res = $orders_model->getOrdersYearCount($store_list, $year);

        $path = ROOT_PATH . DS . 'public' . DS . 'uploads';
        $path1 = ROOT_PATH . DS . 'public' . DS . 'static';
        //以下代码写数据到excel表

        $uploadfile = $path1 . "/model/" . 'hsst_takeout_year_sale.xlsx';
        $reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReader('Xlsx'); //设置以Excel5格式(Excel97-2003工作簿)
        $PHPExcel = $reader->load($uploadfile); // 载入excel文件
        $sheet = $PHPExcel->getActiveSheet(1);
        /*......*/
        //得数据

        /*写数据*/
        $line = 0;
        foreach ($count_res['data'] as $key => $set_value) {
            $line = $key + 4;

            $sheet->getStyle('A' . $line . ':H' . $line)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);
            $sheet->getStyle('A' . $line . ':H' . $line)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
            $sheet->getRowDimension($line)->setRowHeight(29);

            $sheet->setCellValue('A' . $line, $set_value['date']);
            $sheet->setCellValue('B' . $line, $set_value['store_name']);
            $sheet->setCellValue('C' . $line, $set_value['order_count']);
            $sheet->setCellValue('D' . $line, $set_value['total_price'] / 100);
            $sheet->setCellValue('E' . $line, $set_value['develop_price'] / 100);
            $sheet->setCellValue('F' . $line, $set_value['manage_price'] / 100);
            $sheet->setCellValue('G' . $line, $set_value['pay_manage_price'] / 100);
            $sheet->setCellValue('H' . $line, $set_value['store_price'] / 100);
        }

        $line++;

        $sheet->getStyle('A' . $line . ':H' . $line)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);
        $sheet->getStyle('A' . $line . ':H' . $line)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
        $sheet->getRowDimension($line)->setRowHeight(29);

        $sheet->setCellValue('A' . $line, '合计');
        $sheet->mergeCells('A' . $line . ':B' . $line);
        $sheet->setCellValue('C' . $line, $count_res['count']['order_count']);
        $sheet->setCellValue('D' . $line, $count_res['count']['total_price'] / 100);
        $sheet->setCellValue('E' . $line, $count_res['count']['develop_price'] / 100);
        $sheet->setCellValue('F' . $line, $count_res['count']['manage_price'] / 100);
        $sheet->setCellValue('G' . $line, $count_res['count']['pay_manage_price'] / 100);
        $sheet->setCellValue('H' . $line, $count_res['count']['store_price'] / 100);

        $line++;

        $sheet->getStyle('A' . $line . ':H' . $line)->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
        $sheet->getRowDimension($line)->setRowHeight(29);

        $sheet->setCellValue('A' . $line, '经办人：');
        $sheet->setCellValue('B' . $line, '系统复核：');
        $sheet->setCellValue('C' . $line, '核对人（平台维护人员）：');
        $sheet->mergeCells('C' . $line . ':D' . $line);
        $sheet->setCellValue('E' . $line, '食堂经理：');
        $sheet->setCellValue('F' . $line, '主任：');
        $sheet->setCellValue('G' . $line, '审核：');
        $sheet->setCellValue('H' . $line, '');


        $writer = new Xlsx($PHPExcel);

        $new_file = $path . "/order_file/";
        if (!file_exists($new_file)) {
            //检查是否有该文件夹，如果没有就创建，并给予最高权限
            mkdir($new_file, 0700);
        }

        $new_file = $path . "/order_file/year/";
        if (!file_exists($new_file)) {
            //检查是否有该文件夹，如果没有就创建，并给予最高权限
            mkdir($new_file, 0700);
        }

        $new_file = $path . "/order_file/year/" . date('Ymd', time()) . "/";
        if (!file_exists($new_file)) {
            //检查是否有该文件夹，如果没有就创建，并给予最高权限
            mkdir($new_file, 0700);
        }

        $writer->save($path . "/order_file/year/" . date('Ymd', time()) . "/" . "hsst_takeout_year_sale_" . date('Ymd', time()) . ".xlsx");
        return new Ret(config('web_config.upload_host') . "/order_file/year/" . date('Ymd', time()) . "/" . "hsst_takeout_year_sale_" . date('Ymd', time()) . ".xlsx");


    }

    //生成报表 xls文件 季度汇算
    public function inspection_quarter_doc()
    {
        $campus_id = request()->param('campus_id');

        $store_id = request()->param('store_id');

        $select_time = request()->param('select_time');


        $maps = [];
        if ($campus_id) {
            $maps['campus_id'] = $campus_id;
        }

        if ($store_id) {
            $maps['id'] = $store_id;
        }

        $store_list = Db::name('store')
            ->where($maps)
            ->where('deleted', 0)
            ->field('id,name')
            ->select();

        $quarter = [];

        if ($select_time) {
            $select_time_list = explode("-", $select_time);
            switch ($select_time_list[1]){
                case '第1季度':
                    $quarter['first'] = $select_time_list[0].'-01-01 00:00:00';
                    $quarter['last'] = $select_time_list[0].'-03-31 23:59:59';
                    break;
                case '第2季度':
                    $quarter['first'] = $select_time_list[0].'-04-01 00:00:00';
                    $quarter['last'] = $select_time_list[0].'-06-30 23:59:59';
                    break;
                case '第3季度':
                    $quarter['first'] = $select_time_list[0].'-07-01 00:00:00';
                    $quarter['last'] = $select_time_list[0].'-09-30 23:59:59';
                    break;
                case '第4季度':
                    $quarter['first'] = $select_time_list[0].'-10-01 00:00:00';
                    $quarter['last'] = $select_time_list[0].'-12-31 23:59:59';
                    break;
                default:
                    $quarter['first'] = $select_time_list[0].'-01-01 00:00:00';
                    $quarter['last'] = $select_time_list[0].'-03-31 23:59:59';
                    break;
            }
        } else {
            if(date("m-d") >= '01-01' && date("m-d") <= '03-31'){
                $select_time = date("Y").'第1季度';
                $quarter['first'] = date("Y").'-01-01 00:00:00';
                $quarter['last'] = date("Y").'-03-31 23:59:59';

            }else if(date("m-d") >= '04-01' && date("m-d") <= '06-30'){
                $select_time = date("Y").'第2季度';
                $quarter['first'] = date("Y").'-04-01 00:00:00';
                $quarter['last'] = date("Y").'-06-30 23:59:59';

            }else if(date("m-d") >= '07-01' && date("m-d") <= '09-30'){
                $select_time = date("Y").'第3季度';
                $quarter['first'] = date("Y").'-07-01 00:00:00';
                $quarter['last'] = date("Y").'-09-30 23:59:59';

            }else if(date("m-d") >= '10-01' && date("m-d") <= '12-31'){
                $select_time = date("Y").'第4季度';
                $quarter['first'] = date("Y").'-10-01 00:00:00';
                $quarter['last'] = date("Y").'-12-31 23:59:59';

            }else{
                $select_time = date("Y").'第1季度';
                $quarter['first'] = date("Y").'-01-01 00:00:00';
                $quarter['last'] = date("Y").'-03-31 23:59:59';
            }
        }

        $orders_model = new Orders();
        $count_res = $orders_model->getOrdersQuarterCount($store_list, $quarter,$select_time);

        $path = ROOT_PATH . DS . 'public' . DS . 'uploads';
        $path1 = ROOT_PATH . DS . 'public' . DS . 'static';
        //以下代码写数据到excel表

        $uploadfile = $path1 . "/model/" . 'hsst_takeout_quarter_sale.xlsx';
        $reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReader('Xlsx'); //设置以Excel5格式(Excel97-2003工作簿)
        $PHPExcel = $reader->load($uploadfile); // 载入excel文件
        $sheet = $PHPExcel->getActiveSheet(1);
        /*......*/
        //得数据

        /*写数据*/
        $line = 0;
        foreach ($count_res['data'] as $key => $set_value) {
            $line = $key + 4;

            $sheet->getStyle('A' . $line . ':H' . $line)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);
            $sheet->getStyle('A' . $line . ':H' . $line)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
            $sheet->getRowDimension($line)->setRowHeight(29);

            $sheet->setCellValue('A' . $line, $set_value['date']);
            $sheet->setCellValue('B' . $line, $set_value['store_name']);
            $sheet->setCellValue('C' . $line, $set_value['order_count']);
            $sheet->setCellValue('D' . $line, $set_value['total_price'] / 100);
            $sheet->setCellValue('E' . $line, $set_value['develop_price'] / 100);
            $sheet->setCellValue('F' . $line, $set_value['manage_price'] / 100);
            $sheet->setCellValue('G' . $line, $set_value['pay_manage_price'] / 100);
            $sheet->setCellValue('H' . $line, $set_value['store_price'] / 100);
        }

        $line++;

        $sheet->getStyle('A' . $line . ':H' . $line)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);
        $sheet->getStyle('A' . $line . ':H' . $line)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
        $sheet->getRowDimension($line)->setRowHeight(29);

        $sheet->setCellValue('A' . $line, '合计');
        $sheet->mergeCells('A' . $line . ':B' . $line);
        $sheet->setCellValue('C' . $line, $count_res['count']['order_count']);
        $sheet->setCellValue('D' . $line, $count_res['count']['total_price'] / 100);
        $sheet->setCellValue('E' . $line, $count_res['count']['develop_price'] / 100);
        $sheet->setCellValue('F' . $line, $count_res['count']['manage_price'] / 100);
        $sheet->setCellValue('G' . $line, $count_res['count']['pay_manage_price'] / 100);
        $sheet->setCellValue('H' . $line, $count_res['count']['store_price'] / 100);

        $line++;

        $sheet->getStyle('A' . $line . ':H' . $line)->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
        $sheet->getRowDimension($line)->setRowHeight(29);

        $sheet->setCellValue('A' . $line, '经办人：');
        $sheet->setCellValue('B' . $line, '系统复核：');
        $sheet->setCellValue('C' . $line, '核对人（平台维护人员）：');
        $sheet->mergeCells('C' . $line . ':D' . $line);
        $sheet->setCellValue('E' . $line, '食堂经理：');
        $sheet->setCellValue('F' . $line, '主任：');
        $sheet->setCellValue('G' . $line, '审核：');
        $sheet->setCellValue('H' . $line, '');


        $writer = new Xlsx($PHPExcel);

        $new_file = $path . "/order_file/";
        if (!file_exists($new_file)) {
            //检查是否有该文件夹，如果没有就创建，并给予最高权限
            mkdir($new_file, 0700);
        }

        $new_file = $path . "/order_file/quarter/";
        if (!file_exists($new_file)) {
            //检查是否有该文件夹，如果没有就创建，并给予最高权限
            mkdir($new_file, 0700);
        }

        $new_file = $path . "/order_file/quarter/" . date('Ymd', time()) . "/";
        if (!file_exists($new_file)) {
            //检查是否有该文件夹，如果没有就创建，并给予最高权限
            mkdir($new_file, 0700);
        }

        $writer->save($path . "/order_file/quarter/" . date('Ymd', time()) . "/" . "hsst_takeout_quarter_sale_" . date('Ymd', time()) . ".xlsx");
        return new Ret(config('web_config.upload_host') . "/order_file/quarter/" . date('Ymd', time()) . "/" . "hsst_takeout_quarter_sale_" . date('Ymd', time()) . ".xlsx");


    }

    //生成报表 xls文件 半年汇算
    public function inspection_half_year_doc()
    {
        $campus_id = request()->param('campus_id');

        $store_id = request()->param('store_id');

        $select_time = request()->param('select_time');


        $maps = [];
        if ($campus_id) {
            $maps['campus_id'] = $campus_id;
        }

        if ($store_id) {
            $maps['id'] = $store_id;
        }

        $store_list = Db::name('store')
            ->where($maps)
            ->where('deleted', 0)
            ->field('id,name')
            ->select();

        $quarter = [];

        if ($select_time) {
            $select_time_list = explode("-", $select_time);
            switch ($select_time_list[1]){
                case '上半年':
                    $quarter['first'] = $select_time_list[0].'-01-01 00:00:00';
                    $quarter['last'] = $select_time_list[0].'-06-30 23:59:59';
                    break;
                case '下半年':
                    $quarter['first'] = $select_time_list[0].'-07-01 00:00:00';
                    $quarter['last'] = $select_time_list[0].'-12-31 23:59:59';
                    break;
                default:
                    $quarter['first'] = $select_time_list[0].'-01-01 00:00:00';
                    $quarter['last'] = $select_time_list[0].'-06-30 23:59:59';
                    break;
            }
        } else {
            if(date("m-d") >= '01-01' && date("m-d") <= '06-30'){
                $select_time = date("Y").'-上半年';
                $quarter['first'] = date("Y").'-01-01 00:00:00';
                $quarter['last'] = date("Y").'-06-30 23:59:59';

            }else if(date("m-d") >= '07-01' && date("m-d") <= '12-31'){
                $select_time = date("Y").'-下半年';
                $quarter['first'] = date("Y").'-07-01 00:00:00';
                $quarter['last'] = date("Y").'-12-31 23:59:59';

            }else{
                $select_time = date("Y").'-上半年';
                $quarter['first'] = date("Y").'-01-01 00:00:00';
                $quarter['last'] = date("Y").'-06-30 23:59:59';
            }
        }

        $orders_model = new Orders();
        $count_res = $orders_model->getOrdersHalfYearCount($store_list, $quarter,$select_time);

        $path = ROOT_PATH . DS . 'public' . DS . 'uploads';
        $path1 = ROOT_PATH . DS . 'public' . DS . 'static';
        //以下代码写数据到excel表

        $uploadfile = $path1 . "/model/" . 'hsst_takeout_half_year_sale.xlsx';
        $reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReader('Xlsx'); //设置以Excel5格式(Excel97-2003工作簿)
        $PHPExcel = $reader->load($uploadfile); // 载入excel文件
        $sheet = $PHPExcel->getActiveSheet(1);
        /*......*/
        //得数据

        /*写数据*/
        $line = 0;
        foreach ($count_res['data'] as $key => $set_value) {
            $line = $key + 4;

            $sheet->getStyle('A' . $line . ':H' . $line)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);
            $sheet->getStyle('A' . $line . ':H' . $line)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
            $sheet->getRowDimension($line)->setRowHeight(29);

            $sheet->setCellValue('A' . $line, $set_value['date']);
            $sheet->setCellValue('B' . $line, $set_value['store_name']);
            $sheet->setCellValue('C' . $line, $set_value['order_count']);
            $sheet->setCellValue('D' . $line, $set_value['total_price'] / 100);
            $sheet->setCellValue('E' . $line, $set_value['develop_price'] / 100);
            $sheet->setCellValue('F' . $line, $set_value['manage_price'] / 100);
            $sheet->setCellValue('G' . $line, $set_value['pay_manage_price'] / 100);
            $sheet->setCellValue('H' . $line, $set_value['store_price'] / 100);
        }

        $line++;

        $sheet->getStyle('A' . $line . ':H' . $line)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);
        $sheet->getStyle('A' . $line . ':H' . $line)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
        $sheet->getRowDimension($line)->setRowHeight(29);

        $sheet->setCellValue('A' . $line, '合计');
        $sheet->mergeCells('A' . $line . ':B' . $line);
        $sheet->setCellValue('C' . $line, $count_res['count']['order_count']);
        $sheet->setCellValue('D' . $line, $count_res['count']['total_price'] / 100);
        $sheet->setCellValue('E' . $line, $count_res['count']['develop_price'] / 100);
        $sheet->setCellValue('F' . $line, $count_res['count']['manage_price'] / 100);
        $sheet->setCellValue('G' . $line, $count_res['count']['pay_manage_price'] / 100);
        $sheet->setCellValue('H' . $line, $count_res['count']['store_price'] / 100);

        $line++;

        $sheet->getStyle('A' . $line . ':H' . $line)->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
        $sheet->getRowDimension($line)->setRowHeight(29);

        $sheet->setCellValue('A' . $line, '经办人：');
        $sheet->setCellValue('B' . $line, '系统复核：');
        $sheet->setCellValue('C' . $line, '核对人（平台维护人员）：');
        $sheet->mergeCells('C' . $line . ':D' . $line);
        $sheet->setCellValue('E' . $line, '食堂经理：');
        $sheet->setCellValue('F' . $line, '主任：');
        $sheet->setCellValue('G' . $line, '审核：');
        $sheet->setCellValue('H' . $line, '');


        $writer = new Xlsx($PHPExcel);

        $new_file = $path . "/order_file/";
        if (!file_exists($new_file)) {
            //检查是否有该文件夹，如果没有就创建，并给予最高权限
            mkdir($new_file, 0700);
        }

        $new_file = $path . "/order_file/half_year/";
        if (!file_exists($new_file)) {
            //检查是否有该文件夹，如果没有就创建，并给予最高权限
            mkdir($new_file, 0700);
        }

        $new_file = $path . "/order_file/half_year/" . date('Ymd', time()) . "/";
        if (!file_exists($new_file)) {
            //检查是否有该文件夹，如果没有就创建，并给予最高权限
            mkdir($new_file, 0700);
        }

        $writer->save($path . "/order_file/half_year/" . date('Ymd', time()) . "/" . "hsst_takeout_half_year_sale_" . date('Ymd', time()) . ".xlsx");
        return new Ret(config('web_config.upload_host') . "/order_file/half_year/" . date('Ymd', time()) . "/" . "hsst_takeout_half_year_sale_" . date('Ymd', time()) . ".xlsx");


    }


    //商品详情
    public function detail()
    {
        $id = input('param.id/d');
        $subject = Orders::where('id', $id)->find();
        $user_order_count = Db::name('orders')
            ->where('store_id',session('admin.store_id'))
            ->where('user_id',$subject['user_id'])
            ->where('deleted',0)
            ->count();
        $subject['user_order_count'] =$user_order_count;
        if($subject['use_coupon']){
            if($subject['use_month_card']){
                $subject['discount_money']= $subject['discount_money']-5;
            }
        }
        $this->assign('details', $subject);

        if ($subject) {

            $w = $subject['goods_detail'];
            $we1 = explode('--onelist--', $w);
            foreach ($we1 as $item) {
                $we2[] = explode('--twolist--', $item);
            }

            //获取订单状态时间
            $order_time = Db::name('orders_times')
                ->where('orders_id',$id)
                ->where('deleted',0)
                ->field('status,status_time')
                ->order('update_time asc')
                ->select();


            $this->assign('we', $we2);

            $this->assign('order_times', $order_time);

        } else {
            abort(608, '资源不存在');
        }
        return $this->fetch();
    }

    //打印订单
    public function print_orders()
    {
        $order_id = request()->param('id');

        $AppPrint = new AppPrint();
        $print_result = $AppPrint->get_store_print($order_id);


        return Json(new Ret($print_result));

    }

    //审核商家申请退款列表
    public function examine()
    {
        if (request()->isAjax()) {
            if (request()->isGet()) {
                $subject = new Orders();
                $fields = input('param.fields/a');
                $map['status'] = 11;
                $result = $subject->getListData($map, $fields, $this->limit, $this->page);
                return json($result);
            } else {
                $id = input('post.id');
                $audit = input('post.audit/d');
                $subject = Orders::where('id', $id)->where('status', 11)->find();

                if ($audit == 2) {
                    $subject->status = 12;
                }
                if ($audit == 3) {
                    $subject->status = 13;
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

      /**
     * 用户消费次数统计
     * @return [type] [description]
     */
    public function user_stasticstic()
    {
        $where = ' status = 7 and deleted = 0';
        // $where = '1 ';
        $store_id = intval(request()->param('store_id'));
        if (!empty($store_id)) {
            $where = $where . ' and store_id = ' . $store_id;
        }else if(!empty(session('admin.store_id'))){
            $where = $where . ' and store_id = ' . session('admin.store_id');
        }

        $sql = "SELECT b.*,store.name as store_name FROM ( SELECT user.nickname,a.* FROM user INNER JOIN (select user_id, store_id,COUNT(*) AS num FROM orders where {$where} GROUP BY user_id,store_id) as a ON user.id=a.user_id) AS b INNER JOIN store ON b.store_id=store.id;";

        $data = Db::query($sql);
        $uModel = new User();
        $uaddModel = new UserAddress();        
        foreach ($data as $key => $value) {
            $user = $uModel->find($value['user_id']);
            $useraddress = $uaddModel->find(['user_id'=>$value['user_id'],'is_default'=>0]);
            $data[$key]['user_name'] = empty($user)? '???' : $user['nickname'];
            $data[$key]['phone'] = empty($useraddress)? '???' : $useraddress['delivery_phone'];
        }
        
        if (request()->isAjax()) {
            return Json(new Ret($data));
        }

        $this->assign('data', $data);
        return $this->fetch();

    }

}