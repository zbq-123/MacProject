<?php
use util\Auth;
/**
 * 管理员是否登录
 * @return bool
 */
function isLogin()
{
    return session('admin') == null ? false : true;
}
//获取管理员id
function adminId()
{
    return session('admin.id');
}
//返回是否是超级管理员
function isRoot()
{
    return 1 == session('admin.is_root') ? true : false;
}

/**
 * 获取有权限的菜单
 * @param array $menus
 * @return array
 */
function getMenus(Array $menus)
{
    $is_root = session('admin.is_root') == 1;
    $auth = new Auth();
    foreach ($menus as $key => $value) {
        $path = $value['path'];
        if (!$is_root && false == $auth->check($path, session('admin.id'))) {
            unset($menus[$key]);
        } else{
            if(isset($value['item'])){
                $items = $value['item'];
                foreach ($items as $k => $item) {
                    if (!$is_root && false == $auth->check($item['path'], session('admin.id'))) {
                        unset($items[$k]);
                    }else{
                        $items[$k]['path']= url($item['path']);
                    }
                }
                $menus[$key]['item'] = $items;
            }
            $menus[$key]['path'] = url($path);
        }
    }
    return array_filter($menus);
}

/**
** 将数组转换成 in()
**/
function dbCreateIn($itemList)
{
    if(empty($itemList )){
        return " ";
    }else{
        $_s = '';
        foreach ($itemList as $key => $value) {
            if($key==0){
                $_s = $value['id'];
            }else{
                $_s .= ','.$value['id'];
            }
        }
        return $_s;
    }
}

/**
 * 获取面包屑导航
 * @param $menus
 * @return null|string
 */
function getBreadcrumb($menus)
{
    $bread = [];
    $path1 =  strtolower(request()->module().'/'.request()->controller());
    $path2 = strtolower(request()->module().'/'.request()->controller().'/'.request()->action());
    foreach ($menus as $value){
        if($value['path'] == $path1 || $value['path'] == $path2){
            $bread[] = $value['name'];
        }
        if(isset($value['item'])){
            foreach($value['item'] as $item){
                if($item['path'] == $path2){
                    $bread[] = $item['name'];
                }
            }
        }
    }
    return $bread;
}
/**
 * 获得树状数据
 * @param $data 数据
 * @param $title 字段名
 * @param string $fieldPri 主键id
 * @param string $fieldPid 父id
 * @return array
 */
function getTreeData($data, $title, $fieldPri = 'id', $fieldPid = 'pid') {
    $data = \util\Data::tree($data, $title, $fieldPri, $fieldPid);
    $result = [];
    foreach ($data as $key => $value) {
        $result[] = $value;
    }
    return $result;
}
function fen_change_yuan($fen){
    $yuan = $fen/100;

    return $yuan;
}

function yuan_change_fen($yuan){
    $fen = $yuan*100;

    return $fen;
}

/**
 * 获取指定日期段内每一天的日期和天数
 * @param  Date  $startdate 开始日期 格式化时间 Y-m-d H:i:s
 * @param  Date  $enddate   结束日期 格式化时间 Y-m-d H:i:s
 * @return Array
 */
function getDateFromRange($startdate, $enddate)
{
    $startdate = date('Y-m-d 00:00:00', strtotime($startdate));
    $enddate = date('Y-m-d 23:59:59', strtotime($enddate));
    $stimestamp = strtotime($startdate);
    $etimestamp = strtotime($enddate);
    if ($etimestamp < $stimestamp) return [];
    // 计算日期段内有多少天
    $days = (int)ceil(($etimestamp - $stimestamp) / 86400);
    // 保存每天日期
    $date = array();
    for ($i = 0; $i < $days; $i++) {
        $date[] = date('Y-m-d', $stimestamp + (86400 * $i));
    }
    $data = [
        'dates' => $date,
        'days' => $days,
    ];
    return $data;
}

/**
 * 计算出两个日期之间的月份
 * @param  [type] $start_date [开始日期，如2014-03]
 * @param  [type] $end_date   [结束日期，如2015-12]
 * @param  string $explode    [年份和月份之间分隔符，此例为 - ]
 * @param  boolean $addOne    [算取完之后最后是否加一月，用于算取时间戳用]
 * @return [type]             [返回是两个月份之间所有月份起始时间和结束时间还有总月数]
 */
function getDateFromMonths($start_date,$end_date,$explode='-',$addOne=false){
    //判断两个时间是不是需要调换顺序
    $start_int = strtotime($start_date);
    $end_int = strtotime($end_date);
    if($start_int > $end_int){
        $tmp = $start_date;
        $start_date = $end_date;
        $end_date = $tmp;
    }


    //结束时间月份+1，如果是13则为新年的一月份
    $start_arr = explode($explode,$start_date);
    $start_year = intval($start_arr[0]);
    $start_month = intval($start_arr[1]);


    $end_arr = explode($explode,$end_date);
    $end_year = intval($end_arr[0]);
    $end_month = intval($end_arr[1]);


    $data = array();
    $data[] = $start_date;


    $tmp_month = $start_month;
    $tmp_year = $start_year;


    //如果起止不相等，一直循环
    while (!(($tmp_month == $end_month) && ($tmp_year == $end_year))) {
        $tmp_month ++;
        //超过十二月份，到新年的一月份
        if($tmp_month > 12){
            $tmp_month = 1;
            $tmp_year++;
        }
        $data[] = $tmp_year.$explode.str_pad($tmp_month,2,'0',STR_PAD_LEFT);
    }


    if($addOne == true){
        $tmp_month ++;
        //超过十二月份，到新年的一月份
        if($tmp_month > 12){
            $tmp_month = 1;
            $tmp_year++;
        }
        $data[] = $tmp_year.$explode.str_pad($tmp_month,2,'0',STR_PAD_LEFT);
    }

    $all_data = [];
    //获取每个月的第一秒和最后一秒的时间，并且统计出间隔月份
    foreach ($data as $k=>$d){
        $all_data['dates'][$k]['month'] = $d;
        //$all_data['dates'][$k]['first'] = date('Y-m-01 00:00:00', strtotime($d));
        //$all_data['dates'][$k]['last'] = date('Y-m-d 23:59:59', strtotime($all_data['dates'][$k]['first']." +1 month -1 day"));
        $all_data['dates'][$k]['first'] = date('Y-m-26 00:00:00', strtotime($d." -1 month"));
        $all_data['dates'][$k]['last'] = date('Y-m-25 23:59:59', strtotime($d));
    }

    $all_data['count'] = sizeof($data);


    return $all_data;
}
