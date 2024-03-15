<?php
/**
 * Created by PhpStorm.
 * User: WangZiyong
 * Date: 2018/6/5
 * Time: 23:28
 */
namespace util;

class WxTemplateMsg
{
    //发送模板消息
    public function sendMsg($template)
    {

        $json_token=$this->http_request("https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=".config('wx_config.weixin_appID')."&secret=".config('wx_config.weixin_appSecret'));

        $access_token=json_decode($json_token,true);
        //获得access_token
        $this->access_token=$access_token['access_token'];
        //echo $this->access_token;exit;

        $json_template=json_encode($template);
        //echo $json_template;
        //echo $this->access_token;
        $url="https://api.weixin.qq.com/cgi-bin/message/template/send?access_token=".$this->access_token;
        $json_res=$this->http_request($url,urldecode($json_template));
        $res=json_decode($json_res,true);
        return $res;
    }

    //获取模板消息列表
    public function getMsgList()
    {
        $json_token=$this->http_request("https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=".config('wx_config.weixin_appID')."&secret=".config('wx_config.weixin_appSecret'));
        //dump($json_token);
        $access_token=json_decode($json_token,true);
        //获得access_token
        $this->access_token=$access_token['access_token'];
        //echo $this->access_token;exit;
        //echo $json_template;
        //echo $this->access_token;
        $url="https://api.weixin.qq.com/cgi-bin/template/get_all_private_template?access_token=".$this->access_token;
        $json_res=$this->http_request($url);
        $res=json_decode($json_res,true);
        if($res['template_list'])
        {
            return $res['template_list'];
        }else{
            return false;
        }
    }

    public function getUserList()
    {
        $json_token=$this->http_request("https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=".config('wx_config.weixin_appID')."&secret=".config('wx_config.weixin_appSecret'));

        $access_token=json_decode($json_token,true);
        //获得access_token
        $this->access_token=$access_token['access_token'];
        //echo $this->access_token;exit;
        //echo $json_template;
        //echo $this->access_token;
        $url="https://api.weixin.qq.com/cgi-bin/user/get?access_token=".$this->access_token;
        $json_res=$this->http_request($url);
        $res=json_decode($json_res,true);
        if(isset($res) && $res['count'] < 10000){
            return $res['data']['openid'];
        }else if($res['count'] > 10000){

        }else{
            return false;
        }
    }

    function http_request($url,$data=array()){
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
        // POST数据
        curl_setopt($ch, CURLOPT_POST, 1);
        // 把post的变量加上
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        $output = curl_exec($ch);
        curl_close($ch);
        return $output;
    }
}