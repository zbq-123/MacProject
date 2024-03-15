<?php
namespace app\api\controller;
use alipay\Wappay;
use think\Collection;
use think\Db;
use app\admin\model\Comment;
use think\Model;
use util\WxPay;

class Train extends Collection{

    //获取驾校的信息
    public function getTraininfo(){
        $campus_id=input('campus_id');
        if(empty($campus_id)){
            return json(['code'=>201,'msg'=>'参数有误!']);
        }
        //获取驾校
        $train_data = Db::name('train')
            ->where('deleted',0)
            ->where('campus_id',$campus_id)
            ->find();
        if(!empty($train_data)){
            //驾校下的体验券
            $train_data['experience']=Db::name('experience')
                ->where('train_id',$train_data['id'])
                ->where('deleted',0)
                ->select();
            //驾校下的评论
            $comment=Db::name('comment')
                ->where('train_id',$train_data['id'])
                ->where('deleted',0)
                ->order('create_time desc')
                ->select();
            
            $sum_grade=0;
            if(!empty($comment)){
                foreach($comment as $k=>$v){
                    $comment[$k]['content_image']=explode(',',$v['content_image']);
                    $sum_grade+=$v['grade'];
                }
            }
            $train_data['comment']  = $comment;
            //驾校下的教练
            $train_data['coach']=Db::name('coach')
                ->where('train_id',$train_data['id'])
                ->where('deleted',0)
                ->select();
            //体检总数
            $train_data['count_experience']=Db::name('user_experience')
                ->where('train_id',$train_data['id'])
                ->where('deleted',0)
                ->where('status',1)
                ->count();
            $train_data['count_comment']  = count($comment);
            $train_data['average_grade']  =round($sum_grade/count($comment),1);
            return json(['code'=>200,'msg'=>'操作成功','data'=>$train_data]);
        }
        else{
            return json(['code'=>204,'msg'=>'没有数据']);
        }
    }
    //新增评论信息
    public function add_comment(){
        $data['train_id']=input('train_id');
        $data['train_name']=input('train_name');
//        $data['experience_id']=input('experience_id');
//        $data['experience_name']=input('experience_name');
        $data['user_id']=input('user_id');
        $data['user_image']=input('user_image');
        $data['user_name']=input('user_name');
        $data['content_image']=input('content_image');
        $data['content']=input('content');
        $data['grade']=input('grade');
        $comment = new Comment();
        if(empty($data['train_id'])&&empty($data['user_id'])){
            return json(['code'=>201,'msg'=>'参数有误!']);
        }
        if(empty($data['content'])){
            return json(['code'=>202,'msg'=>'评论内容为空!']);
        }
        $result = $comment->editTrain($data);
        if(!empty($result)){
            return json(['code'=>200,'msg'=>'操作成功','data'=>$result]);
        }else{
            return json(['code'=>204,'msg'=>'添加失败']);
        }
    }
    //上传图片
    public function upload_image()
    {
        //获得上传文件对像
        $file = request()->file("file");
        //判断$file是不是文件对像
        if($file){
            $info = $file->validate(['size' => 50 * 1024 * 1024, 'ext' => 'jpg,png,gif,jpeg,heif,bmp,tiff,raw,wmf,lic,eps,dib,rle,emf,jpe,jif,pcx,dcx,pic,tga,tif,fiffxif,wmf,jfif,JPG,PNG,GIF,JPEG,HEIF,BMP,TIFF,RAW,WMF,LIC,EPS,DIB,RLE,EMF,JPE,JIF,PIC,TIF,JFIF'])
                ->move(ROOT_PATH . 'public' . '/' . 'uploads' . '/' . 'train' . '/' . 'comment');
            $imgpath = '/uploads' . '/' . 'train' . '/' . 'comment' .'/'. $info->getSaveName();
            return json(['code'=>200,'msg'=>'上传成功','img'=>$imgpath]);
        }else{
            return json(['code'=>201,'msg'=>'上传失败']);
        }
    }

    //支付驾校体验券
    public function wxpay_train_experience()
    {
        $user_id = input('user_id');
        $train_id = input('train_id');

        $experience_id = input('experience_id');
        if(empty($user_id)||empty($experience_id)){
            return json(['code'=>201,'msg'=>'参数有误']);
        }
        $experience=Db::name('experience')->where('id',$experience_id)->find();
        if(empty($experience)){
            return json(['code'=>202,'msg'=>'该体验券不存在!']);
        }
        Db::startTrans();
        $experience_data['user_id']=$user_id;
        $experience_data['train_id']=$train_id;
        $experience_data['experience_id']=$experience['id'];
        $experience_data['e_name']=$experience['name'];
        $experience_data['e_price']=$experience['price'];
        $experience_data['e_number']=build_order_no();
        $insert_experience_id=Db::name('user_experience')->insertGetId($experience_data);
        if(empty($insert_experience_id)){
            Db::rollback();
            return false;
        }
        Db::commit();
        $useropenid = Db::name('user')->where('id',$user_id)->field('gz_openid')->find();
        //调用支付方式 RICE
        if(1==2){//支付宝
            $wappay = new Wappay();
            $data = [];
            $wappay->pay($data);
        }else{//微信
            //调用统一下单API
            $params = [
                'appid' => 'wx97228d4a1cc79b44',
                'mch_id' => config('wx_config.wxpay_mchid'),
                'nonce_str' => md5(time()),
                'body' => '购买月卡',
                'detail' => '购买月卡',
                'out_trade_no' => $experience_data['e_number'],
                'total_fee' => $experience['price']*100,
                'spbill_create_ip' => $_SERVER['SERVER_ADDR'],
                'notify_url' => 'https://hnqt.0898yzzx.com/client/notify/wxpay_train_experience',
                'trade_type' => 'JSAPI',
                'product_id' => $insert_experience_id,
                'openid' => $useropenid['gz_openid']
            ];
            $wxpay = new WxPay();
            $arr = $wxpay->unifiedorder($params);
            $wxpay->logs('logs.txt',$arr);

            if (isset($arr['prepay_id'])) {
                //重新签名
                $data = [
                    'appId' => $arr['appid'],
                    'timeStamp' => "".time(),
                    'nonceStr' => md5(time()),
                    'package' => 'prepay_id='.$arr['prepay_id'],
                    'signType' => 'MD5'
                ];
                $data = $wxpay->setSign($data);
                $data['paySign'] = $data['sign'];
                unset($data['sign']);
                $data['e_number'] = $experience_data['e_number'];
                return json(['code'=>200,'msg'=>'操作成功','data'=>$data]);
            } else {
                return json(['code'=>204,'msg'=>'prepay_id不存在']);
            }
        }

    }


}