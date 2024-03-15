<?php
/**
 * Created by PhpStorm.
 * User: rice
 * Date: 2020/10/09
 * Time: 16:28
 */

namespace app\store\common;

use app\store\common\HttpClient;
use think\Db;
use think\Log;
use util\Ret;


class AppFePrint
{
    public function get_store_print($order_id){
        $admin = session('admin');
        $store_id = $admin['store_id'];
        $orders = Db::name('orders')->where('id',$order_id)->where('deleted',0)->find();
        /********将订单中的全部商品信息获取********/
        $orders['total_price'] = fen_change_yuan($orders['total_price']);//订单总价
        $orders['convey_price'] = fen_change_yuan($orders['convey_price']);//跑腿费
        $orders['box_price'] = fen_change_yuan($orders['box_price']);//餐盒费
        $orders['all_goods'] = [];//订单中全部商品信息
        $order_goods_detail =  explode('--onelist--', $orders['goods_detail']);//获取订单中的全部商品并以一商品为一数组
        for ($i=1;$i<count($order_goods_detail);$i++){
            $all_goods = explode('--twolist--', $order_goods_detail[$i]);//将订单中的每样商品信息组成数组
            for ($j=2;$j<count($all_goods);$j++){  //将订单中的每一种商品与对应商品信息并入 $orders[all_goods] 变量中
                switch ($j) {
                    case 2: $orders['all_goods'][$i-1]['name'] = $all_goods[$j];break;//商品名称
                    case 3: $orders['all_goods'][$i-1]['count'] = $all_goods[$j];break;//商品数量
                    case 4:
                        $orders['all_goods'][$i-1]['price'] = fen_change_yuan($all_goods[$j]);//商品单价
                        $orders['all_goods'][$i-1]['all_price'] = $orders['all_goods'][$i-1]['price']*$orders['all_goods'][$i-1]['count'];//商品小计
                        break;
                    case 5: $orders['all_goods'][$i-1]['unit'] = $all_goods[$j];break;//商品单位
                }
            }
        }
        /********将订单中的全部商品信息获取********/

        

        $print = Db::name('prints')->where('store_id',$store_id)->where('deleted',0)->where('type',2)->find();
        //$client_id = config('sys_config.yly_app_id');
        //$client_secret = config('sys_config.yly_app_key');
        //$sign = md5($client_id.time().$client_secret);
        $uuid = md5($print['id']);
        $timestamp = time();
        $machine_code = $print['code'];
        $origin_id = $orders['order_number'];
       
        if (empty($print)){
            $ret_data['type'] = 903;
            $ret_data['content'] = '未检索到店铺绑定的打印机！';
            return $ret_data;
        }


        // /********** 获取打印机token **********/
        // $sys_setting = Db::name('sys_setting')->where('deleted',0)->find();
        // $access_token = $sys_setting['access_token'];
        // if (empty($sys_setting['access_token'])){
        //     $get_token_result = $this->get_store_print_access_token($access_token);
        //     $sys_setting = Db::name('sys_setting')->where('deleted',0)->find();
        //     $access_token = $sys_setting['access_token'];
        // }
        /********** 获取打印机token **********/

        header("Content-type: text/html; charset=utf-8");

        define('USER', '568903558@qq.com');  //*必填*：飞鹅云后台注册账号
        define('UKEY', 'YvGW2JFnvnqqIqhr');  //*必填*: 飞鹅云后台注册账号后生成的UKEY 【备注：这不是填打印机的KEY】
        define('SN', $machine_code);      //*必填*：打印机编号，必须要在管理后台里添加打印机或调用API接口添加之后，才能调用API

        //以下参数不需要修改
        define('IP','api.feieyun.cn');      //接口IP或域名
        define('PORT',80);            //接口IP端口
        define('PATH','/Api/Open/');    //接口路径

        //根据打印纸张的宽度，自行调整内容的格式，可参考下面的样例格式
        $content = '<CB>**'.$orders['today_number'].' 圈圈食堂外卖**</CB><BR>';
        $content .= '<CB>'.$orders['store_name'].'</CB><BR>';
        $content .= '订餐时间：'.$orders['create_time'].'<BR>';
        $content .= '订单编号：'.$orders['order_number'].'<BR>';
        $content .= '--------------------------------<BR>';
        foreach ($orders['all_goods'] as $item){
            $content .= '<B>'.$item['name'].'</B><BR>';
            $content .= '<RIGHT><W>*'.$item['count'].'  '.$item['all_price'].'</W></RIGHT><BR>';
        }

        $content .= '--------------------------------<BR>';
        if($orders['remake']){
        $content .= '备注：'.$orders['remake'].'<BR>';
        }
        $content .= $orders['convey_name'].'：￥'.$orders['convey_price'].'<BR>';
        $content .= $orders['box_name'].'：￥'.$orders['box_price'].'<BR>';
        $content .= '<B>合计：￥'.$orders['total_price'].'元</B><BR>';
        $content .= '--------------------------------<BR>';
        if($orders['gender'] == 0){
            $content .=  '<B>'.$orders['delivery_name'].'（先生）</B><BR>';
        }else{
            $content .=  '<B>'.$orders['delivery_name'].'（女士）</B><BR>';
        } 
        $content .= '<W>地点：'.$orders['delivery_address'].'</B><BR>';
        $content .= '<W>电话：'.$orders['delivery_phone'].'</B><BR>';
        
        $content .= '<CB>**'.$orders['today_number'].' 完**</CB><BR>';

        // $content .= '<QR>http://www.feieyun.com</QR>';//把二维码字符串用标签套上即可自动生成二维码


        //提示：
        //SN => 打印机编号
        //$content => 打印内容,不能超过5000字节
        //$times => 打印次数，默认为1。
        $result = $this->printMsg(SN,$content,1);
        $print_result = json_decode($result,true);

        if($print_result['msg'] == 'ok'){
            $ret_data['content'] = '打印成功！';
        }else{
            $ret_data['content'] = '打印失败,参数有误！';
        }
        
        return $ret_data;
        
//        $ret_data['type'] = 902;
//        $ret_data['content'] = '打印失败！';
//        return $ret_data;

    }

    /**
    * [打印订单接口 Open_printMsg]
    * @param  [string] $sn      [打印机编号sn]
    * @param  [string] $content [打印内容]
    * @param  [string] $times   [打印联数]
    * @return [string]          [接口返回值]
    */
    function printMsg($sn,$content,$times){
        $time = time();         //请求时间
        $msgInfo = array(
          'user'=>USER,
          'stime'=>$time,
          'sig'=>$this->signature($time),
          'apiname'=>'Open_printMsg',
          'sn'=>$sn,
          'content'=>$content,
          'times'=>$times//打印次数
        );
        $client = new HttpClient(IP,PORT);
        if(!$client->post(PATH,$msgInfo)){
          echo 'error';
        }else{
          //服务器返回的JSON字符串，建议要当做日志记录起来
          $result = $client->getContent();
          return $result;
        }
    }    

    /**
    * [signature 生成签名]
    * @param  [string] $time [当前UNIX时间戳，10位，精确到秒]
    * @return [string]       [接口返回值]
    */
    function signature($time){
        return sha1(USER.UKEY.$time);//公共参数，请求公钥
    }


    /**
     * Author: WangZiyong
     * 用于用户端支付后自动接单、打印机自动出单功能
     */
    function get_client_print($order_id){
        $orders = Db::name('orders')->where('id',$order_id)->where('deleted',0)->find();
        $store_id = $orders['store_id'];
        /********将订单中的全部商品信息获取********/
        $orders['total_price'] = fen_change_yuan($orders['total_price']);//订单总价
        $orders['convey_price'] = fen_change_yuan($orders['convey_price']);//跑腿费
        $orders['box_price'] = fen_change_yuan($orders['box_price']);//餐盒费
        $orders['all_goods'] = [];//订单中全部商品信息
        $order_goods_detail =  explode('--onelist--', $orders['goods_detail']);//获取订单中的全部商品并以一商品为一数组
        for ($i=1;$i<count($order_goods_detail);$i++){
            $all_goods = explode('--twolist--', $order_goods_detail[$i]);//将订单中的每样商品信息组成数组
            for ($j=2;$j<count($all_goods);$j++){  //将订单中的每一种商品与对应商品信息并入 $orders[all_goods] 变量中
                switch ($j) {
                    case 2: $orders['all_goods'][$i-1]['name'] = $all_goods[$j];break;//商品名称
                    case 3: $orders['all_goods'][$i-1]['count'] = $all_goods[$j];break;//商品数量
                    case 4:
                        $orders['all_goods'][$i-1]['price'] = fen_change_yuan($all_goods[$j]);//商品单价
                        $orders['all_goods'][$i-1]['all_price'] = $orders['all_goods'][$i-1]['price']*$orders['all_goods'][$i-1]['count'];//商品小计
                        break;
                    case 5: $orders['all_goods'][$i-1]['unit'] = $all_goods[$j];break;//商品单位
                }
            }
        }
        /********将订单中的全部商品信息获取********/


        $print = Db::name('prints')->where('store_id',$store_id)->where('deleted',0)->where('type',2)->find();
        $uuid = md5($print['id']);
        $timestamp = time();
        $machine_code = $print['code'];
        $origin_id = $orders['order_number'];
       
        if (empty($print)){
            $ret_data['type'] = 903;
            $ret_data['content'] = '未检索到店铺绑定的打印机！';
            return $ret_data;
        }
       
        header("Content-type: text/html; charset=utf-8");

        //if(!USER){
            define('USER', '568903558@qq.com');  //*必填*：飞鹅云后台注册账号
            define('UKEY', 'YvGW2JFnvnqqIqhr');  //*必填*: 飞鹅云后台注册账号后生成的UKEY 【备注：这不是填打印机的KEY】
            //以下参数不需要修改
            define('IP','api.feieyun.cn');      //接口IP或域名
            define('PORT',80);            //接口IP端口
            define('PATH','/Api/Open/');    //接口路径
            define('SN', $machine_code);      //*必填*：打印机编号，必须要在管理后台里添加打印机或调用API接口添加之后，才能调用API
        //}
        
        

        //根据打印纸张的宽度，自行调整内容的格式，可参考下面的样例格式
        $content = '<CB>**'.$orders['today_number'].' 圈圈食堂外卖**</CB><BR>';
        $content .= '<CB>'.$orders['store_name'].'</CB><BR>';
        $content .= '订餐时间：'.$orders['create_time'].'<BR>';
        $content .= '订单编号：'.$orders['order_number'].'<BR>';
        $content .= '--------------------------------<BR>';
        foreach ($orders['all_goods'] as $item){
            $content .= '<B>'.$item['name'].'</B><BR>';
            $content .= '<RIGHT><W>*'.$item['count'].'  '.$item['all_price'].'</W></RIGHT><BR>';
        }

        $content .= '--------------------------------<BR>';
        if($orders['remake']){
            $content .= '备注：'.$orders['remake'].'<BR>';
        }
        
        $content .= $orders['convey_name'].'：￥'.$orders['convey_price'].'<BR>';
        $content .= $orders['box_name'].'：￥'.$orders['box_price'].'<BR>';
        $content .= '<B>合计：￥'.$orders['total_price'].'元</B><BR>';
        $content .= '--------------------------------<BR>';
        if($orders['gender'] == 0){
            $content .=  '<B>'.$orders['delivery_name'].'（先生）</B><BR>';
        }else{
            $content .=  '<B>'.$orders['delivery_name'].'（女士）</B><BR>';
        } 
        $content .= '<W>地点：'.$orders['delivery_address'].'</B><BR>';
        $content .= '<W>电话：'.$orders['delivery_phone'].'</B><BR>';
        
        $content .= '<CB>**'.$orders['today_number'].' 完**</CB><BR>';
        $content .= '<QR>http://hnqt.0898yzzx.com/api/rider/addOrder?order_id='.$order_id.'</QR>';//把二维码字符串用标签套上即可自动生成二维码


        //提示：
        //SN => 打印机编号
        //$content => 打印内容,不能超过5000字节
        //$times => 打印次数，默认为1。
        $result = $this->printMsg(SN,$content,1);
        $print_result = json_decode($result,true);

        if($print_result['msg'] == 'ok'){
            $ret_data['content'] = '打印成功！';
        }else{
            $ret_data['content'] = '打印失败,参数有误！';
        }
        
        return $ret_data;
        
    }


     /**
     * Author: RICE
     * 用于用户端退款、打印机自动出单功能
     */
    function get_refund_print($order_id){
        $orders = Db::name('orders')->where('id',$order_id)->where('deleted',0)->find();
        $store_id = $orders['store_id'];
        $orders['all_goods'] = [];//订单中全部商品信息
        $order_goods_detail =  explode('--onelist--', $orders['goods_detail']);//获取订单中的全部商品并以一商品为一数组
        for ($i=1;$i<count($order_goods_detail);$i++){
            $all_goods = explode('--twolist--', $order_goods_detail[$i]);//将订单中的每样商品信息组成数组
            for ($j=2;$j<count($all_goods);$j++){  //将订单中的每一种商品与对应商品信息并入 $orders[all_goods] 变量中
                switch ($j) {
                    case 2: $orders['all_goods'][$i-1]['name'] = $all_goods[$j];break;//商品名称
                    case 3: $orders['all_goods'][$i-1]['count'] = $all_goods[$j];break;//商品数量
                    case 4:
                        $orders['all_goods'][$i-1]['price'] = fen_change_yuan($all_goods[$j]);//商品单价
                        $orders['all_goods'][$i-1]['all_price'] = $orders['all_goods'][$i-1]['price']*$orders['all_goods'][$i-1]['count'];//商品小计
                        break;
                    case 5: $orders['all_goods'][$i-1]['unit'] = $all_goods[$j];break;//商品单位
                }
            }
        }

        $print = Db::name('prints')->where('store_id',$store_id)->where('deleted',0)->where('type',2)->find();
        $machine_code = $print['code'];
        $origin_id = $orders['order_number'];
       
        if (empty($print)){
            $ret_data['type'] = 903;
            $ret_data['content'] = '未检索到店铺绑定的打印机！';
            return $ret_data;
        }
       
        header("Content-type: text/html; charset=utf-8");

        //if(!USER){
            define('USER', '568903558@qq.com');  //*必填*：飞鹅云后台注册账号
            define('UKEY', 'YvGW2JFnvnqqIqhr');  //*必填*: 飞鹅云后台注册账号后生成的UKEY 【备注：这不是填打印机的KEY】
            //以下参数不需要修改
            define('IP','api.feieyun.cn');      //接口IP或域名
            define('PORT',80);            //接口IP端口
            define('PATH','/Api/Open/');    //接口路径
            define('SN', $machine_code);      //*必填*：打印机编号，必须要在管理后台里添加打印机或调用API接口添加之后，才能调用API
        //}
        
        

        //根据打印纸张的宽度，自行调整内容的格式，可参考下面的样例格式
        $content = '<CB>**'.$orders['today_number'].' 圈圈食堂外卖**</CB><BR>';
        $content .= '<CB>'.$orders['store_name'].'(订单退款)</CB><BR>';
        $content .= '订餐时间：'.$orders['create_time'].'<BR>';
        $content .= '订单编号：'.$orders['order_number'].'<BR>';
        $content .= '--------------------------------<BR>';
        foreach ($orders['all_goods'] as $item){
            $content .= '<B>'.$item['name'].'</B><BR>';
            $content .= '<RIGHT><W>*'.$item['count'].'  '.$item['all_price'].'</W></RIGHT><BR>';
        }
        
        
        $content .= '<CB>**'.$orders['today_number'].' 完**</CB><BR>';

        $content .= '<QR>http://hnqt.0898yzzx.com/client/orders/detail?order_number='.$orders['today_number'].'</QR>';//把二维码字符串用标签套上即可自动生成二维码


        //提示：
        //SN => 打印机编号
        //$content => 打印内容,不能超过5000字节
        //$times => 打印次数，默认为1。
        $result = $this->printMsg(SN,$content,1);
        $print_result = json_decode($result,true);

        if($print_result['msg'] == 'ok'){
            $ret_data['content'] = '打印成功！';
        }else{
            $ret_data['content'] = '打印失败,参数有误！';
        }
        
        return $ret_data;
        
    }
}