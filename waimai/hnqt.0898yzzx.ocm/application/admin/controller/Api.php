<?php
/**
 * Created by PhpStorm.
 * User: hnwc1
 * Date: 2019/12/6
 * Time: 9:51
 */
namespace app\admin\controller;
use app\admin\model\Campus;
use app\admin\model\ColumnType;
use app\admin\model\GoodsCategory;
use think\Controller;
use app\admin\model\Supermarket;
use app\admin\model\SupermarketSort;
use app\admin\model\Digital;
use app\admin\model\DigitalCategory;
use think\Model;

class Api extends Controller
{
    protected function _initialize()
    {
        parent::_initialize();
    }
    public function all_column()
    {
        $subject = new \app\common\model\Column();
        $maps = [];
        $maps['deleted'] = 0;

        $result = $subject->getSubjectName($maps);
        return json($result);
    }



    public function all_campus(){
        $campus = new Campus();
        $map = [];

        if(session('admin.id')==48){//琼台超级管理员
            $maps['id'] = 4;
        }
        $result = $campus ->getCampusName($map);
        return json($result);
    }
    //商品分类
    public function all_category()
    {
        if((input('param.store_id')=='')|(input('param.store_id')==null)){

            $result1 = [];
            return json($result1);
        }else{
            $small= new GoodsCategory();

            $map['store_id'] = input('param.store_id');

            if(session('admin.id')==48){//琼台超级管理员
                $map['campus_id'] = 4;
            }
            $result = $small->getCategory($map);
            return json($result);
        }
    }

    //所有店铺
    public function get_campus()
    {
        $campus = new Campus();
        $maps = [];

        if(session('admin.id')==48){//琼台超级管理员
                $maps['id'] = 4;
            }
        $result = $campus->getCampusName($maps);
        return json($result);
    }


    //所有店铺
    public function get_store()
    {
        if(input('param.campus_id')){
            $maps['campus_id'] = input('param.campus_id');
        }

        if(session('admin.id')==48){//琼台超级管理员
                $maps['campus_id'] = 4;
            }
        $subject = new \app\admin\model\Store();
        $maps['deleted'] = 0;

        $result = $subject->getStoreName($maps);
        return json($result);
    }
    //所有商超
    public function get_supermarket()
    {
        $campus = new Supermarket();
        $maps = [];


        $result = $campus->getsupermarketname($maps);
        return json($result);
    }
    //商超商品分类
    public function supermarket_sort()
    {
        if((input('param.supermarket_id')=='')|(input('param.supermarket_id')==null)){

            $result1 = [];
            return json($result1);
        }else{
            $small= new SupermarketSort();
            $map['supermarket_id'] = input('param.supermarket_id');

            $result = $small->getCategory($map);
            return json($result);
        }
    }

    //所有数码商家
    public function get_digital()
    {
        $campus = new Digital();
        $maps = [];


        $result = $campus->getsupermarketname($maps);
        return json($result);
    }
    //数码商家商品分类
    public function digital_category()
    {
        if((input('param.supermarket_id')=='')|(input('param.supermarket_id')==null)){

            $result1 = [];
            return json($result1);
        }else{
            $small= new DigitalCategory();
            $map['supermarket_id'] = input('param.supermarket_id');

            $result = $small->getCategory($map);
            return json($result);
        }
    }

}