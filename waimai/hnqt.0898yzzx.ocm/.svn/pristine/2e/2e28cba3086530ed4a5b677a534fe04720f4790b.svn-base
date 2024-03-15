<?php
/**
 * Created by PhpStorm.
 * User: wenyi
 * Date: 2020/7/13
 * Time: 13:25
 */

namespace app\store\controller;


use app\common\controller\StoreBase;
use think\Db;
use util\Ret;


class Goods extends StoreBase
{
    /**
     * @Author: Wanglixian
     * @Date: 2020/7/29 10:39
     * @Description: 店铺商品列表
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function goods(){
        $admin = session('admin');
        $store_id = $admin['store_id'];
        $keyword = isset($_GET['keyword']) ? $_GET['keyword'] : ''; //rice 
        $goods_category = Db::name('goods_category')
            ->where('store_id',$store_id)
            ->where('deleted',0)
            ->order('sort desc')->select();
        $goods = Db::name('goods')
            ->where('store_id',$store_id)
            ->where('deleted',0)
            ->wherelike('name','%'.$keyword.'%') //rice 
            ->order('sort desc')->select();
        foreach ($goods as &$item){
            $item['price'] = fen_change_yuan($item['price']);
        }

        $category_and_goods = $goods_category;
        foreach ($category_and_goods as &$item){
            $item['goods'] = [];
            $j = 0;//分类下的商品数
            foreach ($goods as &$goodsitem){
                if ($goodsitem['goods_category_id'] == $item['id']){
                    $item['goods'][$j] = $goodsitem;
                    $j++;
                }
            }
        }
        
        $this->assign('keyword',$keyword);
        $this->assign('category_and_goods',$category_and_goods);
        $category_and_goods = json_encode($category_and_goods);
        $this->assign('category_and_goods_btn',$category_and_goods);

        return $this->fetch();
    }

    /**
     * @Author: Wanglixian
     * @Date: 2020/7/29 10:45
     * @Description: 商品上架或下架
     * @return Ret
     * @throws \think\Exception
     * @throws \think\exception\PDOException
     */
    public function goods_sell(){
        $status = request()->post('status');
        $goods_id = request()->post('goods_id');
        $status_result = Db::name('goods')
            ->where('id',$goods_id)
            ->where('deleted',0)
            ->update(['status'=>$status,'update_time'=>date('Y-m-d H:i:s',time())]);

        if ($status_result){
            return new Ret('1');
        }else{
            return new Ret('0');
        }

    }

    /**
     * @Author: Wanglixian
     * @Date: 2020/7/30 15:52
     * @Description: 删除商品
     * @return Ret
     * @throws \think\Exception
     * @throws \think\exception\PDOException
     */
    public function deleted_goods(){
        $goods_id = request()->post('goods_id');
        $deleted_result = Db::name('goods')
            ->where('id',$goods_id)
            ->where('deleted',0)
            ->update(['deleted'=>1,'update_time'=>date('Y-m-d H:i:s',time())]);

        if ($deleted_result){
            return new Ret('1');
        }else{
            return new Ret('0');
        }
    }

    /**
     * @Author: Wanglixian
     * @Date: 2020/7/30 15:52
     * @Description: 添加商品
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function add_goods(){
        $admin = session('admin');
        $store_id = $admin['store_id'];

        if(request()->isPost()){
            $data = request()->except(['file'],'post');
            $data['store_id'] = $store_id;
            $data['admin_id'] = $store_id;

            $data['price'] = yuan_change_fen($data['price']);
            $add_goods_id = Db::name('goods')->insertGetId($data);
            if ($add_goods_id){
                $this->success("添加商品成功！", 'goods/goods', null, 1);
            }else{
                $this->error('添加商品错误，请重试！');
            }
        }

        $goods_category = Db::name('goods_category')
            ->where('store_id',$store_id)
            ->where('deleted',0)
            ->order('sort desc')->select();
        $this->assign('goods_category',json_encode($goods_category));//商品分类选择器数据

        return $this->fetch();
    }

    /**
     * @Author: Wanglixian
     * @Date: 2020/7/30 15:52
     * @Description: 修改商品
     * @return mixed
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @throws \think\exception\PDOException
     */
    public function edit_goods(){
        $admin = session('admin');
        $store_id = $admin['store_id'];
        $goods_id = request()->param('goods_id');

        if(request()->isPost()){
            $data = request()->except(['file'],'post');
            $data['store_id'] = $store_id;

            $data['price'] = yuan_change_fen($data['price']);
            $data['update_time'] = date('Y-m-d H:i:s',time());
            $edit_goods_result = Db::name('goods')->where('id',$goods_id)->where('deleted',0)->update($data);
            if ($edit_goods_result){
                $this->success("修改商品成功！", 'goods/goods', null, 1);
            }else{
                $this->error('修改商品错误，请重试！');
            }
        }

        $goods = Db::name('goods')->where('id',$goods_id)->where('deleted',0)->find();
        $goods['price'] = fen_change_yuan($goods['price']);
        $goods_category = Db::name('goods_category')
            ->where('store_id',$store_id)
            ->where('deleted',0)
            ->order('sort desc')->select();
        $this->assign('goods',$goods);
        $this->assign('goods_category',json_encode($goods_category));//商品分类选择器数据

        return $this->fetch();
    }

    /**
     * @Author: Wanglixian
     * @Date: 2020/7/30 15:51
     * @Description: 商品分类列表
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function category(){
        $admin = session('admin');
        $store_id = $admin['store_id'];

        $goods_category = Db::name('goods_category')
            ->where('store_id',$store_id)
            ->where('deleted',0)
            ->order('sort desc')->select();

        $this->assign('goods_category',$goods_category);
        $this->assign('goods_category_btn',json_encode($goods_category));

        return $this->fetch();
    }

    /**
     * @Author: Wanglixian
     * @Date: 2020/7/30 15:51
     * @Description: 添加商品分类
     * @return mixed
     */
    public function add_category(){
        $admin = session('admin');
        $store_id = $admin['store_id'];

        if(request()->isPost()){
            $data = request()->post();
            $data['store_id'] = $store_id;
            $data['admin_id'] = $store_id;

            $add_category_id = Db::name('goods_category')->insertGetId($data);
            if ($add_category_id){
                $this->success("添加商品分类成功！", 'goods/goods', null, 1);
            }else{
                $this->error('添加商品分类错误，请重试！');
            }
        }

        return $this->fetch();
    }

    /**
     * @Author: Wanglixian
     * @Date: 2020/7/30 15:51
     * @Description: 修改商品分类
     * @return mixed
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @throws \think\exception\PDOException
     */
    public function edit_category(){
        $admin = session('admin');
        $store_id = $admin['store_id'];
        $category_id = request()->param('category_id');

        if(request()->isPost()){
            $data = request()->post();
            $data['store_id'] = $store_id;
            $data['update_time'] = date('Y-m-d H:i:s',time());
            $edit_category_result = Db::name('goods_category')->where('id',$category_id)->where('deleted',0)->update($data);
            if ($edit_category_result){
                $this->success("修改商品分类成功！", 'goods/category', null, 1);
            }else{
                $this->error('修改商品分类错误，请重试！');
            }
        }

        $goods_category = Db::name('goods_category')->where('id',$category_id)->where('deleted',0)->find();
        $this->assign('goods_category',$goods_category);

        return $this->fetch();
    }

    /**
     * @Author: Wanglixian
     * @Date: 2020/7/30 15:51
     * @Description: 删除商品分类
     * @return Ret
     * @throws \think\Exception
     * @throws \think\exception\PDOException
     */
    public function deleted_category(){
        $category_id = request()->post('category_id');
        $deleted_result = Db::name('goods_category')
            ->where('id',$category_id)
            ->where('deleted',0)
            ->update(['deleted'=>1,'update_time'=>date('Y-m-d H:i:s',time())]);

        if ($deleted_result){
            return new Ret('1');
        }else{
            return new Ret('0');
        }
    }

    /**
     * @Author: rice
     * @Date: 2020/10/07 12:45
     * @Description: 商品一键上架或下架
     * @return Ret
     * @throws \think\Exception
     * @throws \think\exception\PDOException
     */
    public function on_off_goods(){
        $status = request()->post('status');
        $store_id = request()->post('store_id');
        $goods_category_id = request()->post('goods_category_id');
        $status_result = Db::name('goods')
            ->where('store_id',$store_id)
            ->where('goods_category_id',$goods_category_id)
            ->where('deleted',0)
            ->update(['status'=>$status,'update_time'=>date('Y-m-d H:i:s',time())]);

        if ($status_result){
            return new Ret('1');
        }else{
            return new Ret('0');
        }

    }

}