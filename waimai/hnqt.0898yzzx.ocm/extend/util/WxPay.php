<?php
/**
 * Created by PhpStorm.
 * User: WangZiyong
 * Date: 2020/8/29
 * Time: 5:16
 */

namespace util;
use think\Config;
/**
 * Description of WxPay
 *
 * @author admin
 */
class WxPay {

    /**
     * 获取签名
     * @param type $arr
     * @return type
     */
    public function getSign($arr)
    {
        //去除数组的空值
        array_filter($arr);
        if(isset($arr['sign'])){
            unset($arr['sign']);
        }
        //排序
        ksort($arr);
        //组装字符
        $str = $this->arrToUrl($arr) . '&key=' . config('wx_config.wxpay_appkey');
        //使用md5 加密 转换成大写
        return strtoupper(md5($str));
    }

    /**
     * 校验签名
     * @param type $arr
     * @return boolean
     */
    public function checkSign($arr){
        //生成新签名
        $sign = $this->getSign($arr);
        //和数组中原始签名比较
        if($sign == $arr['sign']){
            return true;
        }else{
            return false;
        }
    }

    /**
     * 获取带签名的数组
     * @param array $arr
     * @return type
     */
    public function setSign($arr)
    {
        $arr['sign'] = $this->getSign($arr);
        return $arr;
    }

    /**
     * 数组转URL字符串 不带key
     * @param type $arr
     * @return type
     */
    public function arrToUrl($arr)
    {
        return urldecode(http_build_query($arr));
    }

    /**
     * 记录到文件
     * @param type $file
     * @param type $data
     */
    public function logs($file,$data)
    {
        $data = is_array($data) ? print_r($data,true) : $data;
        file_put_contents(ROOT_PATH.'runtime/log/paylogs/' .$file, $data);
    }
    /**
     * 接收POST推送
     * @return type
     */
    public function getPost()
    {
        return file_get_contents('php://input');
    }

    /**
     * Xml 文件转数组
     * @param type $xml
     * @return string
     */
    public function XmlToArr($xml)
    {
        if($xml == '') return '';
        libxml_disable_entity_loader(true);
        $arr = json_decode(json_encode(simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA)), true);
        return $arr;
    }

    /**
     * 数组转XML
     * @param type $arr
     * @return string
     */
    public function ArrToXml($arr)
    {
        if(!is_array($arr) || count($arr) == 0) return '';

        $xml = "<xml>";
        foreach ($arr as $key=>$val)
        {
            if (is_numeric($val)){
                $xml.="<".$key.">".$val."</".$key.">";
            }else{
                $xml.="<".$key."><![CDATA[".$val."]]></".$key.">";
            }
        }
        $xml.="</xml>";
        return $xml;
    }

    /**
     * 发送POST请求
     * @param type $url
     * @param type $postfields
     * @return type
     */
    public function postStr($url,$postfields)
    {
        $ch = curl_init();
        $params[CURLOPT_URL] = $url;    //请求url地址
        $params[CURLOPT_HEADER] = false; //是否返回响应头信息
        $params[CURLOPT_RETURNTRANSFER] = true; //是否将结果返回
        $params[CURLOPT_FOLLOWLOCATION] = true; //是否重定向
        $params[CURLOPT_POST] = true;
        $params[CURLOPT_SSL_VERIFYPEER] = false;//禁用证书校验
        $params[CURLOPT_SSL_VERIFYHOST] = false;
        $params[CURLOPT_POSTFIELDS] = $postfields;
        curl_setopt_array($ch, $params); //传入curl参数
        $content = curl_exec($ch); //执行
        curl_close($ch); //关闭连接
        return $content;
    }

    /**
     * 统一下单
     * @param type $params
     * @return boolean
     */
    public function unifiedorder($params)
    {
        //获取到带签名的数组
        $params = $this->setSign($params);

        //数组转xml
        $xml = $this->ArrToXml($params);

        //发送数据到统一下单API地址
        $data = $this->postStr(config('wx_config.uno_url'), $xml);

        $arr = $this->XmlToArr($data);
        if($arr['result_code'] == 'SUCCESS' && $arr['return_code'] == 'SUCCESS'){
            return $arr;
        }else{
            $this->logs('error.txt', $data);
            return false;
        }
    }
}