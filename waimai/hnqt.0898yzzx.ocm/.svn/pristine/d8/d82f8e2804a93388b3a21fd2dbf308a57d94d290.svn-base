<?php
namespace app\api\controller;
use think\Controller;
use think\Db;
use think\Request;

// accomodations for versions earlier than 5.0.2
// borrowed from PHP_Compat, LGPL licensed, by Aidan Lister <aidan@php.net>
if (!defined('PHP_EOL')) {
    switch (strtoupper(substr(PHP_OS, 0, 3))) {
        case 'WIN':
            define('PHP_EOL', "\r\n");
            break;
        case 'DAR':
            define('PHP_EOL', "\r");
            break;
        default:
            define('PHP_EOL', "\n");
    }
}
class Base extends Controller
{
    protected $userid;
    protected $useritem;
    protected $touser;
    
    protected $is_check_login = ['get_category','get_building_room']; //存放用户登录才能操作的方法，每个控制器都可以定义,等于*为控制器所有方法都要登录
    public function _initialize()
    {
        header("Access-Control-Request-Method:GET,POST");
        header("Access-Control-Allow-Credentials:true");
        header("Access-Control-Allow-Origin:*");

        // 用户每次请求接口时修改下线时间为半小时后
        if(input('post.key')){
            $this->change_leave_time(input('post.key'));
        }

        //登录验证
        if(!isLogin()){
            if(!in_array(Request::instance()->action(),$this->is_check_login)){
                echo json_encode(['code'=>-1,'msg'=>'微信未验证']);exit; 
            }
        }

    }

    function change_leave_time($key)
    {
        Db::name('tp_user_token')->where('token="'.$key.'"')->update(['leave_time'=>date("Y-m-d H:i:s",strtotime("30 minute"))]);
    }


    /**
     * 是否登录
     * @return boolean
     */
    function isLogin()
    {
        return session('user_openid') == null ? false : true;
    }

    function in_login($key=null,$item=1){//验证登录
		if(!empty($key)){
            $token = Db::name('tp_user_token')->where('token="'.$key.'"')->find();
            if(empty($token)){
                echo json_encode(['code'=>-2,'msg'=>'请登录']);exit;
            }
            $this->userid = $token['id_tb_user_info'];
            $this->useritem = $token['item'];
            $this->touser = $token['touser'];
		}else{
            echo json_encode(['code'=>-2,'msg'=>'请登录']);exit;
        }
    }
    
    // 基本excel导出
    protected function _excel($data=array(), $fileName = 'sheet.xls'){
        $str = "<html xmlns:o=\"urn:schemas-microsoft-com:office:office\"\r\nxmlns:x=\"urn:schemas-microsoft-com:office:excel\"\r\nxmlns=\"http://www.w3.org/TR/REC-html40\">\r\n<head>\r\n<meta http-equiv=Content-Type content=\"text/html; charset=utf-8\">\r\n</head>\r\n<body>";
        $str .="<table border=1>";
        foreach ($data as $row )
        {
            $str .= "<tr>";
            foreach ( $row as $cell )
            {
                $v = str_replace("&","&amp;",$cell);
                $v = str_replace(">","&gt;",$v);
                $v = str_replace("<","&lt;",$v);
                $v = str_replace("'","&apos;",$v);
                $v = str_replace("\"","&quot;",$v);
                $str .= "<td>". $v ."</td>";
            }
            $str .= "</tr>\n";
        }  
        $str .= "</table></body></html>"; 
        $str.= PHP_EOL;

        header("Content-Type: application/vnd.ms-excel; charset=GB2312");
        header("Content-Disposition: attachment; filename=\"" .$fileName ."\"");
    
        echo( $str ); 
    }

    /**
     * 登录生成token
     */
    function _get_token($id_tb_user_info, $name_tb_user_info,$item=1,$touser='',$openid='') {

        //重新登录后以前的令牌失效
        Db::name('tp_user_token')->where('id_tb_user_info="'.$id_tb_user_info.'" and item='.$item)->delete();

        //生成新的token
        $tp_user_token_info = array();
        $token = md5($name_tb_user_info . strval(time()) . strval(rand(0,999999)));
        $tp_user_token_info['weixin_id'] = $openid;
        $tp_user_token_info['touser'] = $touser;
        $tp_user_token_info['id_tb_user_info'] = $id_tb_user_info;
        $tp_user_token_info['name_tb_user_info'] = $name_tb_user_info;
        $tp_user_token_info['token'] = $token;
        $tp_user_token_info['login_time'] = date('Y-m-d H:i:s');
        $tp_user_token_info['item'] = $item;

        $result = Db::name('tp_user_token')->insert($tp_user_token_info);

        if($result) {
            return $token;
        } else {
            return null;
        }
    }

}