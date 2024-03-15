<?php

namespace app\client\controller;

use app\common\controller\ClientBase;
use PHPMailer\SendEmail;
use think\Db;
use think\Log;
use util\Ret;
use util\RetList;
use util\wxBizDataCrypt;

class User extends ClientBase
{
    /*
        *登录（调用wx.login获取）
        * @param $code string
        * @param $rawData string
        * @param $signatrue string
        * @param $encryptedData string
        * @param $iv string
        * @return $code 成功码
        * @return $session3rd  第三方3rd_session
        * @return $data  用户数据
    */
    public function login()
    {
        $login_name = input('post.login_name');
        $password = input('post.password');
        $user = Db::name('user')->where('login_name',$login_name)->where('deleted',0)->where('disabled',0)->where('reg_status','in',[0,2])->find();
        if($user){
            if(md5($password) == $user['password']){
                //开发者使用登陆凭证 code 获取 session_key 和 openid
                $APPID = config('wx_config.mp_id');
                $AppSecret = config('wx_config.mp_secret');

                $code = input('post.code');
                $url = "https://api.weixin.qq.com/sns/jscode2session?appid=" . $APPID . "&secret=" . $AppSecret . "&js_code=" . $code . "&grant_type=authorization_code";
                $arr = $this->vget($url);  // 一个使用curl实现的get方法请求
                $arr = json_decode($arr, true);
                if(!isset($arr['session_key']) && empty($arr['session_key'])){
                    return new Ret('', 10003,'没有获取到session_key！');
                }
                $session_key = $arr['session_key'];
                // 数据签名校验
                $signature = input('post.signature');
                $rawData = input('post.rawData');
                $signature2 = sha1($rawData . $session_key);
                if ($signature != $signature2) {
                    return new Ret('',10003, '数据签名验证失败！');
                }
                $encryptedData = input('post.encryptedData');
                $iv = input('post.iv');
                $pc = new wxBizDataCrypt($APPID, $session_key);
                $errCode = $pc->decryptData($encryptedData, $iv, $data);  //其中$data包含用户的所有数据
                $data = json_decode($data, true);

                if ($errCode == 0) {
                    $user_token = md5($data['openId'] . time());
                    // 小程序登录获取用户微信信息
                    $userInfo = [];
                    $userInfo['last_login_time'] = date('Y-m-d H:i:s');
                    $userInfo['update_time'] = date('Y-m-d H:i:s');
                    $userInfo['ip'] = request()->ip();
                    if(empty($user['mp_openid']) || $user['mp_openid'] != $data['openId']){
                        $userInfo['mp_openid'] = $data['openId'];
                    }
                    if(empty($user['nickname'])){
                        $userInfo['nickname'] = $data['nickName'];
                    }
                    if(empty($user['image'])){
                        $userInfo['image'] = $data['avatarUrl'];
                    }
                    if(empty($user['address'])){
                        $userInfo['address'] = $data['province'] . ' ' . $data['city'];
                    }
                    if(empty($user['sex'])){
                        $userInfo['sex'] = $data['gender'];
                    }

                    //更新信息，绑定微信openid
                    Db::name('user')->where('id',$user['id'])->update($userInfo);

                    $userCache['open_id'] = $data['openId'];
                    $userCache['id'] = $user['id'];
                    cache($user_token, $userCache);
                    $returnData = ['token' => $user_token];

                    return new Ret($returnData);
                } else {
                    return new Ret('',10003, $errCode);
                }
            }
            return new Ret('',10003, '密码错误，请重试');
        }
        return new Ret('',10003, '账号不存在,或已被禁用');
    }

    public function autoLogin(){
        //开发者使用登陆凭证 code 获取 session_key 和 openid
        $APPID = config('wx_config.mp_id');
        $AppSecret = config('wx_config.mp_secret');

        $code = input('post.code');
        $url = "https://api.weixin.qq.com/sns/jscode2session?appid=" . $APPID . "&secret=" . $AppSecret . "&js_code=" . $code . "&grant_type=authorization_code";
        $arr = $this->vget($url);  // 一个使用curl实现的get方法请求
        Log::info('登录请求返回结果:'.$arr);
        $arr = json_decode($arr, true);
        if(!isset($arr['session_key']) && empty($arr['session_key'])){
            return new Ret('', 10003,'没有获取到session_key！');
        }
        $session_key = $arr['session_key'];
        // 数据签名校验
        $signature = input('post.signature');
        $rawData = input('post.rawData');
        $signature2 = sha1($rawData . $session_key);
        if ($signature != $signature2) {
            return new Ret('',10003, '数据签名验证失败！');
        }
        $encryptedData = input('post.encryptedData');
        $iv = input('post.iv');
        $pc = new wxBizDataCrypt($APPID, $session_key);
        $errCode = $pc->decryptData($encryptedData, $iv, $data);  //其中$data包含用户的所有数据
        $data = json_decode($data, true);

        if ($errCode == 0) {
            $user = Db::name('user')->where('mp_openid',$data['openId'])->where('deleted',0)->where('disabled',0)->where('reg_status','in',[0,2])->find();

            if(empty($user)){
                return new Ret('',10002);
            }

            $user_token = md5($data['openId'] . time());
            // 小程序登录获取用户微信信息
            $userInfo = [];
            $userInfo['last_login_time'] = date('Y-m-d H:i:s');
            $userInfo['update_time'] = date('Y-m-d H:i:s');
            $userInfo['ip'] = request()->ip();
            if(empty($user['mp_openid']) || $user['mp_openid'] != $data['openId']){
                $userInfo['mp_openid'] = $data['openId'];
            }
            if(empty($user['nickname'])){
                $userInfo['nickname'] = $data['nickName'];
            }
            if(empty($user['image'])){
                $userInfo['image'] = $data['avatarUrl'];
            }
            if(empty($user['address'])){
                $userInfo['address'] = $data['province'] . ' ' . $data['city'];
            }
            if(empty($user['sex'])){
                $userInfo['sex'] = $data['gender'];
            }

            //更新信息，绑定微信openid
            Db::name('user')->where('id',$user['id'])->update($userInfo);

            $userCache['open_id'] = $data['openId'];
            $userCache['id'] = $user['id'];
            cache($user_token, $userCache);
            $returnData = ['token' => $user_token];

            return new Ret($returnData);
        } else {
            return new Ret("",10003, $errCode);
        }
    }

    public function logout()
    {
        if ($this->islogin) {

            $userId = cache(input('token'))['id'];

            $isMpLogin = Db::name('user')->where('id', $userId)->find();

            if(empty($isMpLogin)){
                return new Ret('',10003,'账号异常');
            }

            if(empty($isMpLogin['mp_openid'])){
                cache(input('token'), null);
                return new Ret();
            }else{
                $res = Db::name('user')->where('id', $userId)->update(['mp_openid'=>'']);

                if($res){
                    cache(input('token'), null);
                    return new Ret();
                }else{
                    return new Ret('',10003,'退出失败');
                }
            }
        }
    }

    //获取用户基本信息，在我的页面使用
    public function getUserInfo()
    {
        if ($this->islogin) {
            $userId = cache(input('token'))['id'];

            $user = Db::name('user')->field('id,nickname,image,login_name')->where('id', $userId)->find();

            return new Ret($user);

        } else {
            return new Ret('', 10001);
        }
    }

    //获取用户是否登录
    public function userIsLogin()
    {
        return new Ret($this->islogin);
    }

    //获取用户是否登录，未登录自动登录
    public function checkIsLogin()
    {
        if ($this->islogin) {
            return new Ret();
        } else {
            return new Ret('', 10001);
        }
    }

    //用户阅读的文章
    public function getUserRead()
    {
        if (!$this->islogin) {
            return new Ret('', 10001);
        }
        $user_id = cache(input('post.token'))['id'];

        $data = Db::name('article')->alias('a')
            ->join('user_pv up','a.id=up.article_id')
            ->where('up.deleted',0)
            ->where('up.user_id',$user_id)
            ->where('a.is_index',2)
            ->where('a.status',2)
            ->where('a.deleted',0)
            ->field("a.id aid,a.title,a.summary,DATE_FORMAT(a.publish_time, '%Y年%m月%d日') as publish_time,DATE_FORMAT(up.update_time, '%Y年%m月%d日') as read_time")
            ->order('up.update_time desc,up.id desc')
            ->limit($this->limit)
            ->page($this->page)
            ->select();
        $count = Db::name('article')->alias('a')
            ->join('user_pv up','a.id=up.article_id')
            ->where('up.deleted',0)
            ->where('up.user_id',$user_id)
            ->where('a.is_index',2)
            ->where('a.status',2)
            ->where('a.deleted',0)
            ->count();
        return new Ret(new RetList($data, $count,$this->page, $this->limit));
    }

    //删除阅读记录
    public function delUserRead()
    {
        if (!$this->islogin) {
            return new Ret('', 10001);
        }
        $user_id = cache(input('post.token'))['id'];

        $article_id = input('post.aid/d');
        if (empty($article_id)) {
            return new Ret('', 10003,'缺少参数');
        }

        $res = Db::name('user_pv')
            ->where('article_id',$article_id)
            ->where('user_id',$user_id)
            ->where('deleted',0)
            ->update(['deleted'=>1]);

        if($res){
            return new Ret('');
        }else{
            return new Ret('', 10003,'删除失败');
        }
    }

    //用户收藏的文章
    public function getUserLike()
    {
        if (!$this->islogin) {
            return new Ret('', 10001);
        }
        $user_id = cache(input('post.token'))['id'];

        $data = Db::name('article')->alias('a')
            ->join('user_like ul','a.id=ul.article_id')
            ->where('ul.deleted',0)
            ->where('ul.user_id',$user_id)
            ->where('a.is_index',2)
            ->where('a.status',2)
            ->where('a.deleted',0)
            ->field("a.id aid,a.title,a.summary,DATE_FORMAT(a.publish_time, '%Y年%m月%d日') as publish_time,DATE_FORMAT(ul.create_time, '%Y年%m月%d日') as like_time,1 as is_like")
            ->order('ul.create_time desc,ul.id desc')
            ->limit($this->limit)
            ->page($this->page)
            ->select();
        $count = Db::name('article')->alias('a')
            ->join('user_like ul','a.id=ul.article_id')
            ->where('ul.deleted',0)
            ->where('ul.user_id',$user_id)
            ->where('a.is_index',2)
            ->where('a.status',2)
            ->where('a.deleted',0)
            ->count();
        return new Ret(new RetList($data, $count,$this->page, $this->limit));
    }

    //获取用户评论
    public function getUserComment()
    {
        if (!$this->islogin) {
            return new Ret('', 10001);
        }
        $user_id = cache(input('post.token'))['id'];

        $type = input('post.type/d'); //1-已通过评论；2-待审核评论；3-审核不通过评论；4-被评论;

        if(empty($type) || $type == 1){
            $data = Db::name('user_comment')->alias('uc')
                ->join('article a','a.id = uc.article_id')
                ->join('user u','uc.user_id=u.id')
                ->join('user uu','uc.user_pid=uu.id and uu.deleted=0 and uu.disabled=0','LEFT')
                ->join('user_comment_like ucl','ucl.comment_id=uc.id and ucl.deleted=0 and ucl.user_id='.$user_id,'LEFT')
                ->where('uc.user_id',$user_id)
                ->where('uc.deleted',0)
                ->where('u.deleted',0)
                ->where('u.disabled',0)
                ->where('uc.status',1)
                ->field("uc.id cid,uc.article_id aid,a.title article_title,uc.detail,uc.create_time,uc.like,uc.picture_1,uc.picture_2,uc.picture_3,uc.picture_4,uc.picture_5,uc.picture_6,uc.picture_7,uc.picture_8,uc.picture_9,u.id uid,u.nickname,u.image,uu.nickname p_nickname,CASE WHEN ucl.id IS NOT NULL THEN 1 ELSE 0 END AS is_like")
                ->order('uc.create_time desc,uc.id desc')
                ->limit($this->limit)
                ->page($this->page)
                ->select();

            $count = Db::name('user_comment')->alias('uc')
                ->join('user u','uc.user_id=u.id')
                ->where('uc.user_id',$user_id)
                ->where('uc.deleted',0)
                ->where('u.deleted',0)
                ->where('u.disabled',0)
                ->where('uc.status',1)
                ->count();
        }else if($type == 2){
            $data = Db::name('user_comment')->alias('uc')
                ->join('article a','a.id = uc.article_id')
                ->join('user u','uc.user_id=u.id')
                ->join('user uu','uc.user_pid=uu.id and uu.deleted=0 and uu.disabled=0','LEFT')
                ->join('user_comment_like ucl','ucl.comment_id=uc.id and ucl.deleted=0 and ucl.user_id='.$user_id,'LEFT')
                ->where('uc.user_id',$user_id)
                ->where('uc.deleted',0)
                ->where('u.deleted',0)
                ->where('u.disabled',0)
                ->where('uc.status',0)
                ->field("uc.id cid,uc.article_id aid,a.title article_title,uc.detail,uc.create_time,uc.like,uc.picture_1,uc.picture_2,uc.picture_3,uc.picture_4,uc.picture_5,uc.picture_6,uc.picture_7,uc.picture_8,uc.picture_9,u.id uid,u.nickname,u.image,uu.nickname p_nickname,CASE WHEN ucl.id IS NOT NULL THEN 1 ELSE 0 END AS is_like")
                ->order('uc.create_time desc,uc.id desc')
                ->limit($this->limit)
                ->page($this->page)
                ->select();

            $count = Db::name('user_comment')->alias('uc')
                ->join('user u','uc.user_id=u.id')
                ->where('uc.user_id',$user_id)
                ->where('uc.deleted',0)
                ->where('u.deleted',0)
                ->where('u.disabled',0)
                ->where('uc.status',0)
                ->count();
        }else if($type == 3){
            $data = Db::name('user_comment')->alias('uc')
                ->join('article a','a.id = uc.article_id')
                ->join('user u','uc.user_id=u.id')
                ->join('user uu','uc.user_pid=uu.id and uu.deleted=0 and uu.disabled=0','LEFT')
                ->join('user_comment_like ucl','ucl.comment_id=uc.id and ucl.deleted=0 and ucl.user_id='.$user_id,'LEFT')
                ->where('uc.user_id',$user_id)
                ->where('uc.deleted',0)
                ->where('u.deleted',0)
                ->where('u.disabled',0)
                ->where('uc.status',2)
                ->field("uc.id cid,uc.article_id aid,a.title article_title,uc.detail,uc.create_time,uc.like,uc.picture_1,uc.picture_2,uc.picture_3,uc.picture_4,uc.picture_5,uc.picture_6,uc.picture_7,uc.picture_8,uc.picture_9,u.id uid,u.nickname,u.image,uu.nickname p_nickname,CASE WHEN ucl.id IS NOT NULL THEN 1 ELSE 0 END AS is_like")
                ->order('uc.create_time desc,uc.id desc')
                ->limit($this->limit)
                ->page($this->page)
                ->select();

            $count = Db::name('user_comment')->alias('uc')
                ->join('user u','uc.user_id=u.id')
                ->where('uc.user_id',$user_id)
                ->where('uc.deleted',0)
                ->where('u.deleted',0)
                ->where('u.disabled',0)
                ->where('uc.status',2)
                ->count();
        }else if($type == 4){
            $data = Db::name('user_comment')->alias('uc')
                ->join('article a','a.id = uc.article_id')
                ->join('user u','uc.user_id=u.id')
                ->join('user uu','uc.user_pid=uu.id and uu.deleted=0 and uu.disabled=0','LEFT')
                ->join('user_comment_like ucl','ucl.comment_id=uc.id and ucl.deleted=0 and ucl.user_id='.$user_id,'LEFT')
                ->where('uc.user_pid',$user_id)
                ->where('uc.deleted',0)
                ->where('u.deleted',0)
                ->where('u.disabled',0)
                ->where('uc.status',1)
                ->field("uc.id cid,uc.article_id aid,a.title article_title,uc.detail,uc.create_time,uc.like,uc.picture_1,uc.picture_2,uc.picture_3,uc.picture_4,uc.picture_5,uc.picture_6,uc.picture_7,uc.picture_8,uc.picture_9,u.id uid,u.nickname,u.image,uu.nickname p_nickname,CASE WHEN ucl.id IS NOT NULL THEN 1 ELSE 0 END AS is_like")
                ->order('uc.create_time desc,uc.id desc')
                ->limit($this->limit)
                ->page($this->page)
                ->select();

            $count = Db::name('user_comment')->alias('uc')
                ->join('user u','uc.user_id=u.id')
                ->where('uc.user_pid',$user_id)
                ->where('uc.deleted',0)
                ->where('u.deleted',0)
                ->where('u.disabled',0)
                ->where('uc.status',1)
                ->count();
        }else{
            return new Ret('', 10003,'类型错误');
        }

        foreach($data as $k=>$v){
            $data[$k]['picture'] = [];
            for($i = 1; $i <= 9;$i++){
                if(!empty($v['picture_'.$i])){
                    $data[$k]['picture'][] = $v['picture_'.$i];
                }
                unset($data[$k]['picture_'.$i]);
            }
        }

        return new Ret(new RetList($data, $count, $this->page, $this->limit));
    }

    //获取用户基本信息
    public function getChangeInfo()
    {
        if (!$this->islogin) {
            return new Ret('', 10001);
        }
        $user_id = cache(input('post.token'))['id'];

        $user = Db::name('user')
            ->where('id', $user_id)
            ->where('reg_status','in',[0,2])
            ->where('disabled',0)
            ->where('deleted',0)
            ->find();

        if(empty($user)){
            return new Ret('',10003,'账号异常');
        }
        $data = [];
        $data['head'] = $user['image'];
        $data['nickname'] = $user['nickname'];
        $data['sex'] = $user['sex'];
        $data['sid'] = $user['sid'];
        $data['name'] = $user['name'];
        $data['email'] = $user['email'];

        return new Ret($data);
    }

    //设置用户基本信息
    public function setChangeInfo()
    {
        if (!$this->islogin) {
            return new Ret('', 10001);
        }
        $user_id = cache(input('post.token'))['id'];

        $isUser = Db::name('user')
            ->where('id', $user_id)
            ->where('reg_status','in',[0,2])
            ->where('disabled',0)
            ->where('deleted',0)
            ->find();

        if(empty($isUser)){
            return new Ret('',10003,'账号异常');
        }

        $data['image'] = input('post.head');
        $data['nickname'] = input('post.nickname');
        $data['sex'] = input('post.sex');
        $data['email'] = input('post.email');

        if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            return new Ret('',10003,'非法邮箱格式');
        }

        $isEmail = Db::name('user')
            ->where('id','<>', $user_id)
            ->where('email', $data['email'])
            ->where('reg_status','in',[0,2])
            ->where('disabled',0)
            ->where('deleted',0)
            ->find();

        if($isEmail){
            return new Ret('', 10003, '邮箱已被使用');
        }

        $user = Db::name('user')
            ->where('id', $user_id)
            ->where('reg_status','in',[0,2])
            ->where('disabled',0)
            ->where('deleted',0)
            ->update($data);
        if ($user) {
            return new Ret();
        } else {
            return new Ret('', 10003, '未做修改');
        }
    }

    //获取邮箱地址
    public function getUserEmail()
    {
        if (!$this->islogin) {
            return new Ret('', 10001);
        }
        $user_id = cache(input('post.token'))['id'];

        $user = Db::name('user')
            ->where('id', $user_id)
            ->where('reg_status','in',[0,2])
            ->where('disabled',0)
            ->where('deleted',0)
            ->find();

        if(empty($user)){
            return new Ret('',10003,'账号异常');
        }
        $data = [];
        $data['is_email'] = $user['email']?1:2;
        $data['email'] = $user['email']?$user['email']:'';

        return new Ret($data);
    }

    //修改密码 发送邮件
    public function sendEmail()
    {
        if (!$this->islogin) {
            return new Ret('', 10001);
        }
        $user_id = cache(input('post.token'))['id'];

        $user = Db::name('user')
            ->where('id', $user_id)
            ->where('reg_status','in',[0,2])
            ->where('disabled',0)
            ->where('deleted',0)
            ->find();

        if(empty($user)){
            return new Ret('',10003,'账号异常');
        }

        if(!$user['email']){
            return new Ret('',10003,'未设置邮箱');
        }

        $code = rand(100000, 999999);
        cache($user['email'], $code);

        $emailTheme = '【人体解剖学绘图集小程序】修改密码验证码提醒';
        $emailNotes = '【人体解剖学绘图集小程序】本次修改密码验证码为:'.$code.'。';
        $sendStatus = SendEmail::SendEmail($emailTheme, $emailNotes, $user['email']);

        if ($sendStatus === true) {
            $data['success'] = true;
            //$data['code'] = $code;
            return new Ret($data);
        } else {
            return new Ret('', 10003, $sendStatus);
        }
    }

    //注册账号使用 发送邮箱方法
    public function regSendEmail()
    {
        $email = input('email');

        if(!$email){
            return new Ret('',10003,'请输入邮箱');
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return new Ret('',10003,'非法邮箱格式');
        }

        $isEmail = Db::name('user')
            ->where('email', $email)
            ->where('disabled',0)
            ->where('deleted',0)
            ->find();

        if($isEmail){
            return new Ret('', 10003, '邮箱已被使用');
        }

        $code = rand(100000, 999999);
        cache($email, $code);

        $emailTheme = '【人体解剖学绘图集小程序】注册账号验证码提醒';
        $emailNotes = '【人体解剖学绘图集小程序】本次注册账号验证码为:'.$code.'。温馨提示：账号需要后台审核，审核通过后方可登录。';
        $sendStatus = SendEmail::SendEmail($emailTheme, $emailNotes, $email);

        if ($sendStatus === true) {
            $data['success'] = true;
            //$data['code'] = $code;
            return new Ret($data);
        } else {
            return new Ret('', 10003, $sendStatus);
        }
    }

    //注册账号
    public function register()
    {
        $data['name'] = input("post.name");
        $data['idcard'] = input("post.idcard");
        $data['login_name'] = input("post.login_name");
        $data['email'] = input("post.email");
        $code = input("post.code");
        $pwd = input('post.pwd');

        if(empty($data['name']) || empty($data['idcard']) || empty($data['login_name']) || empty($data['email']) || empty($code) || empty($pwd)){
            return new Ret('', 10003, '信息填写不完整');
        }

        //判断验证码是否正确
        if (!empty($code) && cache($data['email']) == $code) {

            cache($data['email'], null);

            $data['password'] = md5($pwd);
            $data['source'] = 3;
            $data['reg_status'] = 1;
            $res = Db::name('user')->insert($data);
            if ($res) {
                return new Ret();
            } else {
                return new Ret('', 10003, '申请失败');
            }
        } else {
            return new Ret('', 10003, '验证码不正确');
        }
    }

    //设置新密码
    public function setNewPwd()
    {
        if (!$this->islogin) {
            return new Ret('', 10001);
        }
        $user_id = cache(input('post.token'))['id'];

        $code = input("post.code");
        $pwd = input('post.pwd');

        $user = Db::name('user')
            ->where('id', $user_id)
            ->where('reg_status','in',[0,2])
            ->where('disabled',0)
            ->where('deleted',0)
            ->find();

        if(empty($user)){
            return new Ret('',10003,'账号异常');
        }

        //判断验证码是否正确
        if (!empty($code) && cache($user['email']) == $code) {

            cache($user['email'], null);

            $md5pwd = md5($pwd);
            $res = Db::name('user')
                ->where('id', $user_id)
                ->where('reg_status','in',[0,2])
                ->where('disabled',0)
                ->where('deleted',0)
                ->update(['password' => $md5pwd]);
            if ($res) {
                return new Ret();
            } else {
                return new Ret('', 10003, '设置失败，新密码不能和原密码相同');
            }
        } else {
            return new Ret('', 10003, '验证码不正确');
        }
    }

    //意见反馈
    public function addFeedback()
    {
        if (!$this->islogin) {
            return new Ret('', 10001);
        }

        $user_id = cache(input('post.token'))['id'];
        $detail = input('post.detail/s');//文本框
        $picture_1 = input('post.picture_1/s');//图片1
        $picture_2 = input('post.picture_2/s');//图片2
        $picture_3 = input('post.picture_3/s');//图片3

        $user = Db::name('user')
            ->where('id', $user_id)
            ->where('reg_status','in',[0,2])
            ->where('disabled',0)
            ->where('deleted',0)
            ->find();

        if(empty($user)){
            return new Ret('',10003,'账号异常');
        }

        $data['user_id'] = $user_id;
        $data['detail'] = $detail;
        $data['picture_1'] = $picture_1;
        $data['picture_2'] = $picture_2;
        $data['picture_3'] = $picture_3;

        $res = Db::name('feedback')->insert($data);
        if($res){
            return new Ret();
        }else{
            return new Ret('',10003,'评论失败');
        }
    }

    //找回密码使用 发送邮箱方法
    public function findPwdSendEmail()
    {
        $email = input('post.email');

        if(empty($email)){
            return new Ret('',10003,'请输入邮箱');
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return new Ret('',10003,'非法邮箱格式');
        }

        $isEmail = Db::name('user')
            ->where('email', $email)
            ->where('reg_status','in',[0,2])
            ->where('disabled',0)
            ->where('deleted',0)
            ->find();

        if(empty($isEmail)){
            return new Ret('', 10003, '该邮箱未绑定账号');
        }

        $code = rand(100000, 999999);
        cache($email, $code);

        $emailTheme = '【人体解剖学绘图集小程序】找回密码验证码提醒';
        $emailNotes = '【人体解剖学绘图集小程序】本次找回密码的验证码为:'.$code.'。';
        $sendStatus = SendEmail::SendEmail($emailTheme, $emailNotes, $email);

        if ($sendStatus === true) {
            $data['success'] = true;
            //$data['code'] = $code;
            return new Ret($data);
        } else {
            return new Ret('', 10003, $sendStatus);
        }
    }

    //找回密码 设置新密码
    public function setFindNewPwd()
    {
        $code = input("post.code");
        $email = input('post.email');
        $pwd = input('post.pwd');

        if(empty($code) || empty($email) || empty($pwd)){
            return new Ret('', 10003, '填写信息不完整');
        }

        $user = Db::name('user')
            ->where('email', $email)
            ->where('reg_status','in',[0,2])
            ->where('disabled',0)
            ->where('deleted',0)
            ->find();

        if(empty($user)){
            return new Ret('',10003,'账号异常');
        }

        //判断验证码是否正确
        if (!empty($code) && cache($email) == $code) {

            cache($email, null);

            $md5pwd = md5($pwd);
            $res = Db::name('user')
                ->where('email', $email)
                ->where('reg_status','in',[0,2])
                ->where('disabled',0)
                ->where('deleted',0)
                ->update(['password' => $md5pwd]);
            if ($res) {
                return new Ret();
            } else {
                return new Ret('', 10003, '设置失败，新密码不能和原密码相同');
            }
        } else {
            return new Ret('', 10003, '验证码不正确');
        }
    }

    //获取用户 阅读 点赞 评论统计
    public function getUserCount()
    {
        if (!$this->islogin) {
            return new Ret('', 10001);
        }
        $user_id = cache(input('post.token'))['id'];

        $data['read_count'] = Db::name('article')->alias('a')
            ->join('user_pv up','a.id=up.article_id')
            ->where('up.deleted',0)
            ->where('up.user_id',$user_id)
            ->where('a.is_index',2)
            ->where('a.status',2)
            ->where('a.deleted',0)
            ->count();

        $data['like_count'] = Db::name('article')->alias('a')
            ->join('user_like ul','a.id=ul.article_id')
            ->where('ul.deleted',0)
            ->where('ul.user_id',$user_id)
            ->where('a.is_index',2)
            ->where('a.status',2)
            ->where('a.deleted',0)
            ->count();

        $data['comment_count'] = Db::name('user_comment')->alias('uc')
            ->join('user u','uc.user_id=u.id')
            ->where('uc.user_id|uc.user_pid',$user_id)
            ->where('uc.deleted',0)
            ->where('u.deleted',0)
            ->where('u.disabled',0)
            ->count();

        return new Ret($data);
    }
}