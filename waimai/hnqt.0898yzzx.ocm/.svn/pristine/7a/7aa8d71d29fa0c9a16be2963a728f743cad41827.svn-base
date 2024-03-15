<?php
/**
 * Created by PhpStorm.
 * User: wenyi
 * Date: 2020/8/12
 * Time: 17:52
 */

namespace app\store\common;


use think\Db;
use think\Log;
use util\Ret;

class AppPrint
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


        $print = Db::name('prints')->where('store_id',$store_id)->where('deleted',0)->where('type',1)->find();
        $client_id = config('sys_config.yly_app_id');
        $client_secret = config('sys_config.yly_app_key');
        $sign = md5($client_id.time().$client_secret);
        $uuid = md5($print['id']);
        $timestamp = time();
        $machine_code = $print['code'];
        $origin_id = $orders['order_number'];

        if (empty($print)){
            $ret_data['type'] = 903;
            $ret_data['content'] = '未检索到店铺绑定的打印机！';
            return $ret_data;
        }


        /********** 获取打印机token **********/
        $sys_setting = Db::name('sys_setting')->where('deleted',0)->find();
        $access_token = $sys_setting['access_token'];
        if (empty($sys_setting['access_token'])){
            $get_token_result = $this->get_store_print_access_token($access_token);
            $sys_setting = Db::name('sys_setting')->where('deleted',0)->find();
            $access_token = $sys_setting['access_token'];
        }
        ///********** 获取打印机token **********/


        /********** 打印机状态 **********/
//        $print_status_url = 'https://open-api.10ss.net/printer/getprintstatus';
//        $post_data_state = array(
//            'client_id' => $client_id,
//            'access_token' => $access_token,
//            'machine_code' => $machine_code,
//            'cmd' => 'oauth_printStatus',
//            'url' => $machine_code,
//            'status' => 'open',
//            'sign' => $sign,
//            'id' => $uuid,
//            'timestamp' => $timestamp
//        );
//        $print_status = $this->send_post($print_status_url,$post_data_state);//获取打印机状态
//        $print_status = json_decode($print_status,true);

//        if ($print_status['body']['state'] == 0){
//            $ret_data['type'] = 901;
//            $ret_data['content'] = '打印失败，请查看打印机是否开启或缺纸！';
//            return $ret_data;
//        }
//        if ($print_status['body']['state'] == 2){
//            $ret_data['type'] = 901;
//            $ret_data['content'] = '打印失败，请查看打印机是否开启或缺纸！';
//            return $ret_data;
//        }
        /********** 打印机状态 **********/


        /********** 打印 **********/
        $content = '<FH2><FW2><center>**#'.$orders['today_number'].' 圈圈海软外卖**</center></FW2></FH2>
................................
<FH2><FW2><center>--在线支付--</center></FW2></FH2>
<FH><FW>'.$orders['store_name'].'</FW></FH>
下单时间：'.$orders['create_time'].'
单号：'.$orders['order_number'].'
***************商品*************';
        foreach ($orders['all_goods'] as $item){
            $content =$content.'
<FH><FW><table><tr><td>'.$item['name'].'</td><td>'.$item['count'].'*'.$item['unit'].'</td><td>￥'.$item['all_price'].'</td></tr></table></FW></FH>';
        }
        $content =$content.'................................
<FH><FW>'.$orders['convey_name'].'：￥'.$orders['convey_price'].'</FW></FH>
<FH><FW>'.$orders['box_name'].'：￥'.$orders['box_price'].'</FW></FH>
<FH><FW>总价：￥'.$orders['total_price'].'</FW></FH>';
if($orders['remake']){
    $content =$content.'
    *******************************
    <FH><FW>备注：'.$orders['remake'].'</FW></FH>';
}
$content =$content.'
*******************************
<FH2><FW2><center>--配送--</center></FW2></FH2>';
        if ($orders['gender'] == 0){
            $content =$content.'
<FH><FW>'.$orders['delivery_name'].'（先生）</FW></FH>';
        }
        if ($orders['gender'] == 1){
            $content =$content.'
<FH><FW>'.$orders['delivery_name'].'（女士）</FW></FH>';
        }
        $content =$content.'
<FH><FW>'.$orders['delivery_phone'].'</FW></FH>
<FH><FW>'.$orders['delivery_address'].'</FW></FH>
<FH2><FW2><center>**#'.$orders['today_number'].' 完 **</center></FW2></FH2>';

        $print_url = 'https://open-api.10ss.net/print/index';
        $post_data_print = array(
            'client_id' => $client_id,
            'access_token' => $access_token,
            'machine_code' => $machine_code,
            'content' => $content,
            'origin_id' => $origin_id,
            'sign' => $sign,
            'id' => $uuid,
            'timestamp' => $timestamp
        );
        $print_result = $this->send_post($print_url,$post_data_print);//打印
        $print_result = json_decode($print_result,true);
        /********** 打印 **********/


        /********** 打印失败，更新token再次打印 **********/
        if ($print_result['error'] == 18){ //打印失败，重新获取token再次打印
            $get_token_result = $this->get_store_print_access_token($access_token);
            $sys_setting = Db::name('sys_setting')->where('deleted',0)->find();
            $access_token = $sys_setting['access_token'];
            $print_result = $this->send_post($print_url,$post_data_print);//再次打印
            $print_result = json_decode($print_result,true);
        }
        /********** 打印失败，更新token再次打印 **********/


//        if ($print_result['error'] == 0){
//            $ret_data['type'] = 900;
//            $ret_data['content'] = '打印成功！';
//            return $ret_data;
//        }
        switch ($print_result['error']) {
            case 0:$ret_data['type'] = $print_result['error'];$ret_data['content'] = '打印成功！';return $ret_data;break;
            case 8:$ret_data['type'] = $print_result['error'];$ret_data['content'] = '打印机信息错误,参数有误！';return $ret_data;break;
            case 9:$ret_data['type'] = $print_result['error'];$ret_data['content'] = '连接打印机失败,参数有误！';return $ret_data;break;
            case 10:$ret_data['type'] = $print_result['error'];$ret_data['content'] = '权限不足！';return $ret_data;break;
            case 12:$ret_data['type'] = $print_result['error'];$ret_data['content'] = '缺少必要参数！';return $ret_data;break;
            case 13:$ret_data['type'] = $print_result['error'];$ret_data['content'] = '打印失败,参数有误！';return $ret_data;break;

        }
//        $ret_data['type'] = 902;
//        $ret_data['content'] = '打印失败！';
//        return $ret_data;

    }

    /**
     * @Author: Wanglixian
     * @Date: 2020/8/18 11:53
     * @Description: 获取打印access_token
     * @param $access_token
     * @return array|false|int|\PDOStatement|string|\think\Model
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @throws \think\exception\PDOException
     */
    public function get_store_print_access_token($access_token){
        $admin = session('admin');
        $store_id = $admin['store_id'];
        $print = Db::name('prints')->where('store_id',$store_id)->where('deleted',0)->find();
        $client_id =  config('sys_config.yly_app_id');
        $client_secret =  config('sys_config.yly_app_key');
        $post_url = 'https://open-api.10ss.net/oauth/oauth';
        $sign = md5($client_id.time().$client_secret);
        $uuid = md5($print['id']);
        $timestamp = time();
        if (empty($access_token)){
            $post_data_token = array(
                'client_id' => $client_id,
                'sign' => $sign,
                'id' => $uuid,
                'grant_type' => 'client_credentials',
                'scope' => 'all',
                'timestamp' => $timestamp
            );
            $token_result = $this->send_post($post_url,$post_data_token);//获取返回的打印token。
        }else{
            $post_data_token = array(
                'client_id' => $client_id,
                'sign' => $sign,
                'id' => $uuid,
                'grant_type' => 'client_credentials',
                'scope' => 'all',
                'timestamp' => $timestamp,
                'refresh_token' => $access_token
            );
            $token_result = $this->send_post($post_url,$post_data_token);//获取更新的打印token。
        }

        $token_result = json_decode($token_result,true);
        if ($token_result['error'] == 0){
            $token = $token_result['body']['access_token'];
            $sys_setting = Db::name('sys_setting')->where('deleted',0)->update(['access_token' =>$token]);
            if ($sys_setting){
                return $sys_setting;
            }
            $this->error('获取打印token错误，请重试！','orders/orders', null, 1);
        }else{
            $this->error('获取打印token错误，请重试！','orders/orders', null, 1);
        }

    }

    /**
     * 发送post请求
     * @param string $url 请求地址
     * @param array $post_data post键值对数据
     * @return string
     */
    function send_post($url, $post_data) {
        $postdata = http_build_query($post_data);
        $options = array(
            'http' => array(
                'method' => 'POST',
                'header' => 'Content-type:application/x-www-form-urlencoded',
                'content' => $postdata,
                'timeout' => 15 * 60 // 超时时间（单位:s）
            )
        );
        $context = stream_context_create($options);
        $result = file_get_contents($url, false, $context);

        return $result;
    }

    /**
     * @Author: Wanglixian
     * @Date: 2020/9/24 15:38
     * 发送post请求
     * @param string $url 请求地址
     * @param array $post_data post键值对数据
     * @return string
     * @return false|string
     */
    function send_get($url, $post_data) {
        $postdata = http_build_query($post_data);
        $options = array(
            'http' => array(
                'method' => 'GET',
                'header' => 'Content-type:application/x-www-form-urlencoded',
                'content' => $postdata,
                'timeout' => 15 * 60 // 超时时间（单位:s）
            )
        );
        $context = stream_context_create($options);
        $result = file_get_contents($url, false, $context);

        return $result;
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


        $print = Db::name('prints')->where('store_id',$store_id)->where('deleted',0)->find();
        $client_id = config('sys_config.yly_app_id');
        $client_secret = config('sys_config.yly_app_key');
        $sign = md5($client_id.time().$client_secret);
        $uuid = md5($print['id']);
        $timestamp = time();
        $machine_code = $print['code'];
        $origin_id = $orders['order_number'];

        if (empty($print)){
            $ret_data['type'] = 903;
            $ret_data['content'] = '未检索到店铺绑定的打印机！';
            return $ret_data;
        }


        /********** 获取打印机token **********/
        $sys_setting = Db::name('sys_setting')->where('deleted',0)->find();
        $access_token = $sys_setting['access_token'];
        if (empty($sys_setting['access_token'])){
            $get_token_result = $this->get_store_print_access_token($access_token);
            $sys_setting = Db::name('sys_setting')->where('deleted',0)->find();
            $access_token = $sys_setting['access_token'];
        }
        /********** 获取打印机token **********/


        /********** 打印机状态 **********/
//        $print_status_url = 'https://open-api.10ss.net/printer/getprintstatus';
//        $post_data_state = array(
//            'client_id' => $client_id,
//            'access_token' => $access_token,
//            'machine_code' => $machine_code,
//            'sign' => $sign,
//            'id' => $uuid,
//            'timestamp' => $timestamp
//        );
//        $print_status = $this->send_post($print_status_url,$post_data_state);//获取打印机状态
//        $print_status = json_decode($print_status,true);
//
//        if ($print_status['body']['state'] == 0){
//            $ret_data['type'] = 901;
//            $ret_data['content'] = '打印失败，请查看打印机是否开启或缺纸！';
//            return $ret_data;
//        }
//        if ($print_status['body']['state'] == 2){
//            $ret_data['type'] = 901;
//            $ret_data['content'] = '打印失败，请查看打印机是否开启或缺纸！';
//            return $ret_data;
//        }
        /********** 打印机状态 **********/


        /********** 打印 **********/
        $content = '<FH2><FW2><center>**#'.$orders['today_number'].' 圈圈海软外卖**</center></FW2></FH2>
................................
<FH2><FW2><center>--在线支付--</center></FW2></FH2>
<FH><FW>'.$orders['store_name'].'</FW></FH>
下单时间：'.$orders['create_time'].'
单号：'.$orders['order_number'].'
***************商品*************';
        foreach ($orders['all_goods'] as $item){
            $content =$content.'
<FH><FW><table><tr><td>'.$item['name'].'</td><td>'.$item['count'].'*'.$item['unit'].'</td><td>￥'.$item['all_price'].'</td></tr></table></FW></FH>';
        }
        $content =$content.'................................
<FH><FW>'.$orders['convey_name'].'：￥'.$orders['convey_price'].'</FW></FH>
<FH><FW>'.$orders['box_name'].'：￥'.$orders['box_price'].'</FW></FH>
<FH><FW>总价：￥'.$orders['total_price'].'</FW></FH>';
if($orders['remake']){
    $content =$content.'
    *******************************
    <FH><FW>备注：'.$orders['remake'].'</FW></FH>';
}
$content =$content.'
*******************************
<FH2><FW2><center>--配送--</center></FW2></FH2>';
        if ($orders['gender'] == 0){
            $content =$content.'
<FH><FW>'.$orders['delivery_name'].'（先生）</FW></FH>';
        }
        if ($orders['gender'] == 1){
            $content =$content.'
<FH><FW>'.$orders['delivery_name'].'（女士）</FW></FH>';
        }
        $content =$content.'
<FH><FW>'.$orders['delivery_phone'].'</FW></FH>
<FH><FW>'.$orders['delivery_address'].'</FW></FH>
<FH2><FW2><center>**#'.$orders['today_number'].' 完 **</center></FW2></FH2>';

        $print_url = 'https://open-api.10ss.net/print/index';
        $post_data_print = array(
            'client_id' => $client_id,
            'access_token' => $access_token,
            'machine_code' => $machine_code,
            'content' => $content,
            'origin_id' => $origin_id,
            'sign' => $sign,
            'id' => $uuid,
            'timestamp' => $timestamp
        );
        $print_result = $this->send_post($print_url,$post_data_print);//打印
        $print_result = json_decode($print_result,true);
        /********** 打印 **********/


        /********** 打印失败，更新token再次打印 **********/
        if ($print_result['error'] == 18){ //打印失败，重新获取token再次打印
            $get_token_result = $this->get_store_print_access_token($access_token);
            $sys_setting = Db::name('sys_setting')->where('deleted',0)->find();
            $access_token = $sys_setting['access_token'];
            $print_result = $this->send_post($print_url,$post_data_print);//再次打印
            $print_result = json_decode($print_result,true);
        }
        /********** 打印失败，更新token再次打印 **********/


        if ($print_result['error'] == 0){
            return true;
        }else{
            Log::error('[autoPrintError]order_id:'.$order_id.';res:' . json_encode($print_result));
            return false;
        }
    }

    /**
     * Author: RICE
     * 用于用户退款、打印机自动出单功能
     */
    function get_refund_print($order_id){
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


        $print = Db::name('prints')->where('store_id',$store_id)->where('deleted',0)->find();
        $client_id = config('sys_config.yly_app_id');
        $client_secret = config('sys_config.yly_app_key');
        $sign = md5($client_id.time().$client_secret);
        $uuid = md5($print['id']);
        $timestamp = time();
        $machine_code = $print['code'];
        $origin_id = $orders['order_number'];

        if (empty($print)){
            $ret_data['type'] = 903;
            $ret_data['content'] = '未检索到店铺绑定的打印机！';
            return $ret_data;
        }


        /********** 获取打印机token **********/
        $sys_setting = Db::name('sys_setting')->where('deleted',0)->find();
        $access_token = $sys_setting['access_token'];
        if (empty($sys_setting['access_token'])){
            $get_token_result = $this->get_store_print_access_token($access_token);
            $sys_setting = Db::name('sys_setting')->where('deleted',0)->find();
            $access_token = $sys_setting['access_token'];
        }
        /********** 获取打印机token **********/


        /********** 打印 **********/
        $content = '<FH2><FW2><center>**#'.$orders['today_number'].' 圈圈海软外卖**</center></FW2></FH2>
................................
<FH2><FW2><center>--订单退款--</center></FW2></FH2>
<FH><FW>'.$orders['store_name'].'</FW></FH>
下单时间：'.$orders['create_time'].'
单号：'.$orders['order_number'].'
***************商品*************';
        foreach ($orders['all_goods'] as $item){
            $content =$content.'
<FH><FW><table><tr><td>'.$item['name'].'</td><td>'.$item['count'].'*'.$item['unit'].'</td><td>￥'.$item['all_price'].'</td></tr></table></FW></FH>';
        }
        $content =$content.'................................
<FH><FW>'.$orders['convey_name'].'：￥'.$orders['convey_price'].'</FW></FH>
<FH><FW>'.$orders['box_name'].'：￥'.$orders['box_price'].'</FW></FH>
<FH><FW>总价：￥'.$orders['total_price'].'</FW></FH>
<FH2><FW2><center>**#'.$orders['today_number'].' 完 **</center></FW2></FH2>';

        $print_url = 'https://open-api.10ss.net/print/index';
        $post_data_print = array(
            'client_id' => $client_id,
            'access_token' => $access_token,
            'machine_code' => $machine_code,
            'content' => $content,
            'origin_id' => $origin_id,
            'sign' => $sign,
            'id' => $uuid,
            'timestamp' => $timestamp
        );
        $print_result = $this->send_post($print_url,$post_data_print);//打印
        $print_result = json_decode($print_result,true);
        /********** 打印 **********/


        /********** 打印失败，更新token再次打印 **********/
        if ($print_result['error'] == 18){ //打印失败，重新获取token再次打印
            $get_token_result = $this->get_store_print_access_token($access_token);
            $sys_setting = Db::name('sys_setting')->where('deleted',0)->find();
            $access_token = $sys_setting['access_token'];
            $print_result = $this->send_post($print_url,$post_data_print);//再次打印
            $print_result = json_decode($print_result,true);
        }
        /********** 打印失败，更新token再次打印 **********/


        if ($print_result['error'] == 0){
            return true;
        }else{
            Log::error('[autoPrintError]order_id:'.$order_id.';res:' . json_encode($print_result));
            return false;
        }
    }

}