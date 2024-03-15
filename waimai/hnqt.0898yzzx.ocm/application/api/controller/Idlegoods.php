<?php
namespace app\api\controller;
use alipay\Wappay;
use think\Collection;
use think\Db;
use app\admin\model\IdleGoods as IdleGoods_mod;
use app\admin\model\IdleGoodsComment;
use think\Model;
use util\WxPay;

class Idlegoods extends Collection{
    //闲置商品分类
    public function get_idle_category(){
        $campus_id = input('campus_id');
        if(empty($campus_id)){
            return json(['code'=>201,'msg'=>'参数有误']);
        }
        $res = Db::name('idle_goods_category')
            ->where('deleted',0)
            ->where('campus_id',$campus_id)
            ->select();
        if(!empty($res)){
            return json(['code'=>200,'msg'=>'操作成功','data'=>$res]);
        }else{
            return json(['code'=>204,'msg'=>'获取失败']);
        }
    }
    //闲置商品列表
    public function get_idle_goods(){
        $category_id = input('category_id');
        $name = input('name');
        $maps['deleted']=0;
        if(!empty($category_id)){
            $maps['category_id']=$category_id;
        }
        if(!empty($name)){
            $maps['name']=['like', '%' . $name . '%'];
        }
        $res=Db::name('idle_goods')
            ->where($maps)
            ->select();
       
        if(!empty($res)){
            foreach($res as $k=>$v){
                $goods_image=explode(',',$v['content_image']);
                $res[$k]['goods_image']=$goods_image[0];
                $res[$k]['count_comment']=Db::name('idle_goods_comment')
                    ->where('idlegoods_id',$v['id'])
                    ->count();
            }
            return json(['code'=>200,'msg'=>'操作成功','data'=>$res]);
        }else{
            return json(['code'=>204,'msg'=>'获取失败']);
        }
    }

    //发布闲置
    public function add_idlegoods(){
        $data['category_id']=input('category_id');
        $data['name']=input('name');
        $data['price']=input('price');
        $data['oldprice']=input('oldprice');
        $data['user_id']=input('user_id');
        $data['user_image']=input('user_image');
        $data['user_name']=input('user_name');
        $data['content_image']=input('content_image');
        $data['content']=input('content');
        $comment = new IdleGoods_mod();
        if(empty($data['user_id'])||empty($data['category_id'])){
            return json(['code'=>201,'msg'=>'参数有误!']);
        }
        if(empty($data['content'])){
            return json(['code'=>202,'msg'=>'商品说明为空!']);
        }
        $result = $comment->editTrain($data);
        if(!empty($result)){
            return json(['code'=>200,'msg'=>'操作成功','data'=>$result]);
        }else{
            return json(['code'=>204,'msg'=>'添加失败']);
        }
    }
    //闲置评论列表
    public function get_idle_goods_comment(){
        $data['idlegoods_id']=input('idlegoods_id');
        if(empty($data['idlegoods_id'])){
            return json(['code'=>201,'msg'=>'参数有误!']);
        }
        $res=Db::name('idle_goods_comment')
            ->where('idlegoods_id',$data['idlegoods_id'])
            ->order('id desc')
            ->select();
        if(!empty($res)){
            return json(['code'=>200,'msg'=>'操作成功','data'=>$res]);
        }else{
            return json(['code'=>204,'msg'=>'获取失败']);
        }
    }
    //评论闲置商品
    public function add_idlegoods_comment(){

        $data['idlegoods_id']=input('idlegoods_id');
        $data['user_id']=input('user_id');
        $data['user_image']=input('user_image');
        $data['user_name']=input('user_name');
        $data['content']=input('content');
        $comment = new IdleGoodsComment();
        if(empty($data['user_id'])||empty($data['idlegoods_id'])){
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
    //点赞闲置商品
    public function spot_idle_goods(){
        $data['idlegoods_id']=input('idlegoods_id');
        $data['user_id']=input('user_id');
       
        if(empty($data['user_id'])||empty($data['idlegoods_id'])){
            return json(['code'=>201,'msg'=>'参数有误!']);
        }
        $res=Db::name('idle_goods_spot')
            ->where('idlegoods_id',$data['idlegoods_id'])
            ->where('user_id',$data['user_id'])
            ->find();
        if(!empty($res)){
            return json(['code'=>202,'msg'=>'该商品您已点赞，请勿重复操作!']);
        }else{
            $result = Db::name('idle_goods_spot')->insert($data);
            if(!empty($result)){
                $find_spot=Db::name('idle_goods')->where('id',$data['idlegoods_id'])->field('spot')->find();
                if(!empty($find_spot)){
                    $spot   = $find_spot['spot']+1;
                    $updata_spot    = Db::name('idle_goods')
                        ->where('id',$data['idlegoods_id'])
                        ->update(['spot'=>$spot]);
                    if(!empty($updata_spot)){
                        return json(['code'=>200,'msg'=>'操作成功','data'=>$updata_spot]);
                    }else{
                        return json(['code'=>204,'msg'=>'点赞失败!']);
                    }
                }
            }
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
                ->move(ROOT_PATH . 'public' . '/' . 'uploads' . '/' . 'idlegoods');
            $imgpath = '/uploads' . '/' . 'idlegoods' .'/'. $info->getSaveName();
            return json(['code'=>200,'msg'=>'上传成功','img'=>$imgpath]);
        }else{
            return json(['code'=>201,'msg'=>'上传失败']);
        }
    }




}