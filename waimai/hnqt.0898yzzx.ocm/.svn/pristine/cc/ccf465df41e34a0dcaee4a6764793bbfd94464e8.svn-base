<?php
namespace app\client\controller;

use app\common\controller\ClientBase;
use app\common\model\SysExpertArea;
use app\common\model\SysExpertLibrary as SysExpertLibraryModel;
use app\common\model\SysCategoryName;
use app\common\model\SysCompany;
use app\common\model\SysSubject;
use app\common\model\SysExpertPosition;
use app\admin\model\SpecGoodsPrice;
use app\common\model\SysNewsModule;
use app\common\model\SysTrainCategory;

class Api extends ClientBase
{
    protected function _initialize()
    {
        parent::_initialize();
    }

    //后台首页
    public function index()
    {
        return $this->fetch();
    }

    //获取师傅维修列表
    public function repair_name()
    {
        $train = input('post.train');
        $company = input('post.company');
        $repair= new SysExpertLibraryModel();
        $map=[];
        $map ['repairman_state']=1;
        $result = $repair->getRepairman($map,$train,$company);
        return json($result);
    }
  //获取用户类型列表
    public function subject_list()
    {
        $train = new SysSubject();
        $result = $train->allTrain();
        return json($result);
    }
    //获取维修人员职称列表
    public function position_list()
    {
        $train = new SysExpertPosition();
        $result = $train->allTrain();
        return json($result);
    }
    //获取调配地区列表
    public function train_list()
    {
        $train = new SysTrainCategory();
        $result = $train->allTrain();
        return json($result);
    }
    //获取公告所属模块列表
    public function module_list(){
        $module = new SysNewsModule();
        $result = $module->getmodule();
        return json($result);
    }



    //根据地区获取调配单位列表
    public function company_list()
    {
        $company= new SysCompany();
        $map = [];
        $map['train_category_fid'] = input('param.train_id');
        $result = $company->clientFindCompany($map);
        return json($result);
    }

    //根据地区获取调配单位列表
    public function company2_list()
    {
        $company= new SysCompany();
        $map = [];
        $map['train_category_fid'] = input('param.address_campus');
        $result = $company->findCompany($map);
        return json($result);
    }

    //获取维修分类列表
    public function repair_type()
    {
        $category= new SysCategoryName();
        $map = [];
        $map['company_fid'] = input('param.company_id');
        $result = $category->findCategory($map);
        return json($result);
    }

    //获取负责片区
    public function area_list()
    {
        $area= new SysExpertArea();
        $map = [];
        $map['address_campus'] = input('param.train_id');
        $result = $area->findTrainArea($map);
        return json($result);
    }

    // 获取规格
    public function get_spec(){

        $goods_id = input('goods_id');
        $spec_name_1 = input('spec_name_1');
        $spec_name_2 = input('spec_name_2');
        $spec_name_3 = input('spec_name_3');
        $spec_name_4 = input('spec_name_4');

        $maps = [
            'goods_id' => $goods_id,
        ];
        if($spec_name_1){
            $maps['key1'] = $spec_name_1;
        }
        if($spec_name_2){
            $maps['key2'] = $spec_name_2;
        }
        if($spec_name_3){
            $maps['key3'] = $spec_name_3;
        }
        if($spec_name_4){
            $maps['key4'] = $spec_name_4;
        }

        $res = SpecGoodsPrice::where($maps)->find();
        return json($res);
    }

}
