<?php
namespace app\client\controller;

use app\common\controller\ClientBase;
use app\lib\event\PushEvent;
use think\Db;
use util\WxJssdk\Jssdk;
use app\admin\model\Coupon;
use app\admin\model\CouponUser;
use util\Ret;
class Index extends ClientBase
{
    public function index()
    {
        $campus_id = input('campus_id/d');

        //获取校区相关信息
        $campus = Db::name('campus')
            ->where('deleted',0)
            ->field('id campus_id,name,address,lat,lon')
            ->order('sort desc,id asc')
            ->select();
		
        //1 设置前端选中校区，如果input中校区id不为空表示用户手动选择校区，更新用户所属校区；
        //2 否则找用户默认校区是否有历史选择校区；
        //3 否则给予默认排序最前的校区为默认校区。
        if(empty($campus_id)){
            $campus_id = session('user.campus_id') > 0?session('user.campus_id'):$campus[0]['campus_id'];
        }else{
            $update_campus = Db::name('user')
                ->where('id',session('user.id'))
                ->update(['campus_id'=>$campus_id]);

            if($update_campus){
                session('user.campus_id',$campus_id);
            }
        }

        //获取选择校区名称
        $campus_name = Db::name('campus')
            ->where('deleted',0)
            ->where('id',$campus_id)
            ->find()['name'];

        //轮播图
        $carousel = Db::name('home_carousel')
            ->where('campus_id',$campus_id)
            ->where('status',1)
            ->where('deleted',0)
            ->field('id carousel_id,jump_type,jump_url,picture')
            ->order('sort desc,id asc')
            ->select();
        if(empty($carousel)){
            $carousel = Db::name('home_carousel')
            ->where('campus_id',0)
            ->where('status',1)
            ->where('deleted',0)
            ->field('id carousel_id,jump_type,jump_url,picture')
            ->order('sort desc,id asc')
            ->select();
        }
        
        //店铺分类
        $category = Db::name('store_category')
            ->where('deleted',0)
            ->field('id category_id,image,name')
            ->order('sort desc,id asc')
            ->limit(5)
            ->page(1)
            ->select();

        //附近店铺

        $now_time = date('H:i:s', time());

        //营业中店铺
        $store_on = Db::name('store')
            ->where('campus_id',$campus_id)
            ->where('deleted',0)
            ->where('status',1)
            ->where(function($query) use ($now_time){
                $query->where(function($query) use ($now_time){
                    $query->where('start_time1','<',$now_time)->where('end_time1','>',$now_time);
                })->whereOr(function($query) use ($now_time){
                    $query->where('start_time2','<',$now_time)->where('end_time2','>',$now_time);
                })->whereOr(function($query) use ($now_time){
                    $query->where('start_time3','<',$now_time)->where('end_time3','>',$now_time);
                });
            })
            ->field("id store_id,logo,name,min_price,delivery_price,delivery_name,1 as status")
            ->order('sort desc,id asc')
            ->select();

        //休息中店铺
        $store_off = Db::name('store')
            ->where('campus_id',$campus_id)
            ->where('deleted',0)
            ->where(function($query) use ($now_time){
                $query->where('status',2)
                    ->whereOr(function($query) use ($now_time){
                        $query->where(function($query) use ($now_time){
                            $query->where('start_time1','>=',$now_time)->whereOr('end_time1','<=',$now_time);
                        })->where(function($query) use ($now_time){
                            $query->where('start_time2','>=',$now_time)->whereOr('end_time2','<=',$now_time);
                        })->where(function($query) use ($now_time){
                            $query->where('start_time3','>=',$now_time)->whereOr('end_time3','<=',$now_time);
                        });
                    });
            })
            ->field("id store_id,logo,name,min_price,delivery_price,delivery_name,0 as status")
            ->order('sort desc,id asc')
            ->select();
//        if(session('user.id')==1694){
//
//        }
        $store = array_merge($store_on, $store_off);

        foreach ($store as &$store_item){
            //计算订单量
            $store_item['sale'] = Db::name('orders')
                ->where('store_id',$store_item['store_id'])
                ->where('deleted',0)
                ->where('status',7)
                ->count();
        }

        //获取天气预报信息
        $weather_curl = curl_init();

        curl_setopt($weather_curl, CURLOPT_URL, 'http://wthrcdn.etouch.cn/weather_mini?city=海口');
        curl_setopt($weather_curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($weather_curl, CURLOPT_ENCODING, "");

        $weather_data = json_decode(curl_exec($weather_curl), true);

        curl_close($weather_curl);

        $weather = [];

        if($weather_data){
            $weather['wendu'] = $weather_data['data']['wendu'].'°';
            $weather['low'] = substr(explode(" ",$weather_data['data']['forecast'][0]['low'])[1],0,strlen(explode(" ",$weather_data['data']['forecast'][0]['low'])[1])-3).'°';
            $weather['high'] = substr(explode(" ",$weather_data['data']['forecast'][0]['high'])[1],0,strlen(explode(" ",$weather_data['data']['forecast'][0]['high'])[1])-3).'°';
            $weather['type'] = $weather_data['data']['forecast'][0]['type'];
            $weather['city'] = '海口市';
        }else{
            $weather['wendu'] = '...';
            $weather['low'] = '...';
            $weather['high'] = '...';
            $weather['type'] = '获取中';
            $weather['city'] = '海口市';
        }
        
        //获取平台优惠券
        $Coupon = new Coupon();
        $maps = [];
        $maps['start_time'] = ['<',date("Y-m-d H:i:s",time())];
        $maps['end_time'] = ['>',date("Y-m-d H:i:s",time())];
        $maps['seller_id'] = 0;
        $maps['status'] = 1;
        $maps['deleted'] = 0;
        $maps['type'] = 1;
        $coupons = $Coupon->where($maps)->field('id,name,discount_money,full_money,start_time,end_time,campus_id')->select();
        if($coupons){
            $users = [];
            $user_info = [];
            $CouponUser = new CouponUser();
            foreach ($coupons as $key => $val){
                if($val['campus_id']>0 && $val['campus_id']!=$campus_id){
                    unset($coupons[$key]);
                    continue;
                }
                $user_info = $CouponUser->where(['coupon_id'=>$val['id'],'is_used'=>0])->field('user_id')->select();
                if($user_info){
                    foreach($user_info as $k => $v){
                        $users[] = $v['user_id'];
                    }
                }
                $coupons[$key]['users'] = $users;
                
                $coupons[$key]['start_time'] = substr($val['start_time'],0,10);
                $coupons[$key]['end_time'] = substr($val['end_time'],0,10);
                $users = [];
            }
        }
        //var_dump($store);
        $this->assign('user_id',session('user.id'));
        $this->assign('coupons',$coupons);
        $this->assign('campus',$campus);
        $this->assign('carousel',$carousel);
        $this->assign('category',$category);
        $this->assign('store',$store);
        
        $this->assign('weather',$weather);
        $this->assign('campus_name',$campus_name);

        //首次打开默认校区
        
        return $this->fetch();
    }

    public function home()
    {
        if(config('web_config.zg_debug')) {
            //测试环境
            $signPackage = [
                'appId'=> 'wx9569bfe00332918f',
                'timestamp'=> '1598296783',
                'nonceStr'=> 'zsCjTuzX9WuEzSuc',
                'signature'=> '95e38ebadbd451486b1a9ddf2259cedc47a060fc',
            ];
            $this->assign('signPackage',$signPackage);
        }else{
            //线上环境
            $jssdk = new Jssdk(config('wx_config.weixin_appID'), config('wx_config.weixin_appSecret'));
            $signPackage = $jssdk->GetSignPackage();
            $this->assign('signPackage',$signPackage);
        }
        return $this->fetch();
    }
    
    // 用户领取优惠券
    public function add_coupon()
    {
        $coupon_id = input('post.coupon_id/d');

        $in_data = [];
        $in_data['user_id'] = session('user.id');
        $in_data['coupon_id'] = $coupon_id;
        $in_data['redeem_time'] = date('Y-m-d H:i:s',time());
        
        $maps = [];
        $maps['user_id'] = $in_data['user_id'];
        $maps['coupon_id'] = $in_data['coupon_id'];
        $maps['is_used'] = 0;
        $coupon = Db::name('coupon_user')->where($maps)->find();
        if($coupon){
            abort(10003,'已领取，不能再重复领');
            exit;
        }
        
        $res = $address_info = Db::name('coupon_user')->insert($in_data);
    
        if($res){
            abort(10001,'领取成功');
            exit;
        }else{
            abort(10003,'领取失败');
        }
    }

    public function test(){
        // $campus_id = 5;
        // //获取平台优惠券
        // $Coupon = new Coupon();
        // $maps = [];
        // $maps['start_time'] = ['<',date("Y-m-d H:i:s",time())];
        // $maps['end_time'] = ['>',date("Y-m-d H:i:s",time())];
        // $maps['seller_id'] = 0;
        // $maps['status'] = 1;
        // $maps['deleted'] = 0;
        // $coupons = $Coupon->where($maps)->field('id,name,discount_money,full_money,start_time,end_time,campus_id')->select();
        // if($coupons){
        //     $users = [];
        //     $user_info = [];
        //     $CouponUser = new CouponUser();
        //     foreach ($coupons as $key => $val){
        //         if($val['campus_id']>0 && $val['campus_id']!=$campus_id){
        //             unset($coupons[$key]);
        //             continue;
        //         }
        //         $user_info = $CouponUser->where(['coupon_id'=>$val['id'],'is_used'=>0])->field('user_id')->select();
        //         if($user_info){
        //             foreach($user_info as $k => $v){
        //                 $users[] = $v['user_id'];
        //             }
        //         }
        //         $coupons[$key]['users'] = $users;
                
        //         $coupons[$key]['start_time'] = substr($val['start_time'],0,10);
        //         $coupons[$key]['end_time'] = substr($val['end_time'],0,10);
        //         $users = [];
        //     }
        // }

        // dump($coupons);exit;
    }

}
