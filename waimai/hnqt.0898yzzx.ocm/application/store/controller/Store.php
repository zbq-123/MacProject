<?php
/**
 * Created by PhpStorm.
 * User: wenyi
 * Date: 2020/7/13
 * Time: 13:24
 */

namespace app\store\controller;

;
use app\common\controller\StoreBase;
use think\Db;
use util\Ret;

class Store extends StoreBase
{
    /**
     * @Author: Wanglixian
     * @Date: 2020/8/22 14:23
     * @Description: 获取店铺信息
     * @return mixed|Ret
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @throws \think\exception\PDOException
     */
    public function store(){
        $admin = session('admin');
        $store = Db::name('store')->alias('a')
            ->where('a.id',$admin['store_id'])
            ->join('campus b','a.campus_id=b.id')
            ->where('a.deleted',0)
            ->field('a.name store_name,b.name campus_name,logo,number,start_time1,start_time2,start_time3,end_time1,end_time2,end_time3,a.address,status,notice')
            ->find();

        $this->assign('store',$store);
        if(request()->isPost()){
            $status = request()->post('status');
            $status_result = Db::name('store') ->where('id',$admin['store_id'])->update(['status'=>$status]);
            if ($status_result){
                return new Ret('1');
            }else{
                return new Ret('0');
            }
        }

        return $this->fetch();
    }

    /**
     * @Author: Wanglixian
     * @Date: 2020/8/22 14:23
     * @Description: 修改店铺信息
     * @return mixed
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @throws \think\exception\PDOException
     */
    public function edit_store(){
        $admin = session('admin');
        $store_id = $admin['store_id'];
        $store = Db::name('store')->where('id',$store_id)->where('deleted',0)->find();
        $store['min_price'] = fen_change_yuan($store['min_price']);
        $store['delivery_price'] = fen_change_yuan($store['delivery_price']);
        $store['box_price'] = fen_change_yuan($store['box_price']);

        if (request()->isPost()){
            $data = request()->except(['file'],'post');

            $data['min_price'] = yuan_change_fen($data['min_price']);
            $data['delivery_price'] = yuan_change_fen($data['delivery_price']);
            $data['box_price'] = yuan_change_fen($data['box_price']);
            $data['update_time'] = date('Y-m-d H:i:s',time());
            $store = Db::name('store')->where('id',$store_id)->where('deleted',0)->update($data);
            if ($store){
                $this->success("修改成功！", 'store/store', null, 1);
            }else{
                $this->error('未作更改，请重试！','store/store', null, 1);
            }
        }

        $this->assign('store',$store);

        return $this->fetch();
    }
}