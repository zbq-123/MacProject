<?php
/**
 * Created by PhpStorm.
 * User: wzy
 * Date: 2018/6/30
 * Time: 17:01
 */

namespace app\client\controller;

use think\Controller;
use think\Db;

class Auth extends Controller
{
    public function index()
    {
        //用户同意授权后回调的网址.
        $redirect_uri = config('http_url').'/auth/callback';
        $this->redirect('https://open.weixin.qq.com/connect/oauth2/authorize?appid=' . config('wx_config.weixin_appID') . '&redirect_uri=' . $redirect_uri . '&response_type=code&scope=snsapi_userinfo&state=1#wechat_redirect');
    }

    //获取code后的回调函数
    public function callBack()
    {

        //获取到的code
        $code = input('code');

        //获取access_token
        $curl = curl_init();

        curl_setopt($curl, CURLOPT_URL, 'https://api.weixin.qq.com/sns/oauth2/access_token?appid=' . config('wx_config.weixin_appID') . '&secret=' . config('wx_config.weixin_appSecret') . '&code=' . $code . '&grant_type=authorization_code');

        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);//阻止对证书的检查
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 2);
        //获取access_token和openid,转换为数组
        $data = json_decode(curl_exec($curl), true);
        curl_close($curl);

        //如果获取成功，根据access_token和openid获取用户的基本信息
        if ($data != null && isset($data['openid']) && isset($data['access_token'])) {

            //获取用户基本信息
            $user_curl = curl_init();

            curl_setopt($user_curl, CURLOPT_URL, 'https://api.weixin.qq.com/sns/userinfo?access_token='.$data['access_token'].'&openid='.$data['openid'].'&lang=zh_CN');

            curl_setopt($user_curl, CURLOPT_RETURNTRANSFER, true);
            //获取access_token和openid,转换为数组
            $user_data = json_decode(curl_exec($user_curl), true);
            curl_close($user_curl);

            $user = Db::name('user')
                ->where('gz_openid',$data['openid'])
                ->where('deleted',0)
                ->find();

            if(empty($user)){
                $campus = Db::name('campus')
                    ->where('deleted',0)
                    ->field('id')
                    ->order('sort desc,id asc')
                    ->limit(1)
                    ->page(1)
                    ->select();

                $user_data['nickname'] = preg_replace_callback('/./u', function (array $match) {
                    return strlen($match[0]) >= 4 ? '' : $match[0];
                }, $user_data['nickname']);

                $user_data['nickname'] = preg_replace('/[\x{10000}-\x{10FFFF}]/u', "\xEF\xBF\xBD", $user_data['nickname']);

                $new_user = [
                    'campus_id' => isset($campus[0]['id'])?$campus[0]['id']:0,
                    'nickname' => isset($user_data['nickname'])?$user_data['nickname']:'小海狮',
                    'image' => isset($user_data['headimgurl'])?$user_data['headimgurl']:config('web_config.upload_host').'avatar.jpg',
                    'gz_openid' => $data['openid'],
                    'register_time' => date('Y-m-d H:i:s', time()),
                    'last_login_time' => date('Y-m-d H:i:s', time()),
                    'sex' => isset($user_data['sex'])?$user_data['sex']:0,
                    'address' => isset($user_data['country'])?($user_data['country'].' '):''.isset($user_data['province'])?($user_data['province'].' '):''.isset($user_data['city'])?$user_data['city']:'',
                ];

                $user['id'] = Db::name('user')->insertGetId($new_user);
                $user['campus_id'] = isset($campus[0]['id'])?$campus[0]['id']:0;
            }else if($user['disabled'] == 1){
                exit('该账号已被禁用，请联系管理员');
            }else{
                $user_data['nickname'] = preg_replace_callback('/./u', function (array $match) {
                    return strlen($match[0]) >= 4 ? '' : $match[0];
                }, $user_data['nickname']);

                $user_data['nickname'] = preg_replace('/[\x{10000}-\x{10FFFF}]/u', "\xEF\xBF\xBD", $user_data['nickname']);

                $update_user = [
                    'nickname' => isset($user_data['nickname'])?$user_data['nickname']:$user['nickname'],
                    'image' => isset($user_data['headimgurl'])?$user_data['headimgurl']:$user['image'],
                    'update_time' => date('Y-m-d H:i:s', time()),
                    'last_login_time' => date('Y-m-d H:i:s', time()),
                    'sex' => isset($user_data['sex'])?$user_data['sex']:$user['sex'],
                    'address' => isset($user_data['country'])?($user_data['country'].' '.isset($user_data['province'])?($user_data['province'].' '.isset($user_data['city'])?$user_data['city']:''):''):$user['address'],
                ];

                Db::name('user')
                    ->where('gz_openid',$data['openid'])
                    ->where('deleted',0)
                    ->update($update_user);
            }
            
            $session_data = [
                'id'=>$user['id'],
                'campus_id'=>$user['campus_id'],
                'openid'=>$data['openid']
            ];

            session('user', $session_data);
            
            //跳转网页
            if (session('path')) {
                $this->redirect(session('path'));
            } else {
                $this->redirect('client/index/home');
            }
            dump($session);exit;
        } else {
            exit('微信授权失败');
        }
    }
}
