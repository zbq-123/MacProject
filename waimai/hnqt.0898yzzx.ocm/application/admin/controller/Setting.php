<?php
/**
 * Created by PhpStorm.
 * User: wenyi
 * Date: 2019/12/18
 * Time: 17:11
 */

namespace app\admin\controller;


use app\common\controller\AdminBase;
use think\Model;
use util\Ret;
use app\common\model\Feedback;
use app\admin\model\SysSetting;
use app\admin\model\Spec;

class Setting extends AdminBase
{
    /**
     * @Author: Wanglixian
     * @Date: 2019/12/19 10:04
     * @Description: 获取意见反馈列表
     * @return mixed|\think\response\Json
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function feedback(){
        if (request()->isAjax()) {
            $feedback = new Feedback();
            $fields = input('param.fields/a');
            $type = input('param.status/d');
            $map = [];
            if ($type==0) {
                $map['status'] = array('in','0,1');
            } else if($type==1){
                $map['status'] = 2;
            }
            $result = $feedback->getFeedbackList($map, $fields ,$this->limit,$this->page);

            return json($result);
        }

        return $this->fetch();
    }

    /**
     * @Author: Wanglixian
     * @Date: 2019/12/19 10:04
     * @Description: 意见反馈已处理
     * @return \think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function yes_feedback(){
        $id = input('post.id');
        $feedback = Feedback::where('id', $id)->find();
        if (false == $feedback) {
            abort(608, '资源不存在');
        }else{
            $feedback->status = 2;
            $result = $feedback->save();
            if ($result) {
                return json(new Ret());
            } else {
                abort(608);
            }
        }
    }

    /**
     * @Author: Wanglixian
     * @Date: 2019/12/19 10:04
     * @Description: 删除意见反馈
     * @return \think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function delete_feedback(){
        $id = input('post.id');
        $feedback = Feedback::where('id', $id)->find();
        if (false == $feedback) {
            abort(608, '资源不存在');
        }else{
            $feedback->deleted = 1;
            $result = $feedback->save();
            if ($result) {
                return json(new Ret());
            } else {
                abort(608);
            }
        }
    }

// 费率
    public function ratio(){
        if (request()->isAjax()) {
            $sys = new SysSetting();
            $info = $sys->where('id',1)->field('id,wx_ratio')->find();
            $data[0] = $info;
           return json(new Ret($data));
        }

        return $this->fetch();
    }

    public function edit_ratio(){
        if (request()->isPost()) {
            $data['wx_ratio'] = request()->post('wx_ratio');
            $id = request()->post('id');
            $sys = new SysSetting();
            $result = $sys->where('id',$id)->update($data);
            if ($result) {
                $this->success('操作成功', 'setting/ratio', null, 1);

            } else {
                $this->error($colum->getError());

            }
        }
        $id = request()->post('id');
        $sys = new SysSetting();
        $info = $sys->where('id',1)->field('id,wx_ratio')->find();
        $this->assign('ratio',$info);
        
        return $this->fetch();
    }

    // 骑手费率
    public function rider(){
        if (request()->isAjax()) {
            $sys = new SysSetting();
            $info = $sys->where('id',1)->field('id,rider_ratio')->find();
            $data[0] = $info;
           return json(new Ret($data));
        }

        return $this->fetch();
    }

    public function edit_rider(){
        if (request()->isPost()) {
            $data['rider_ratio'] = request()->post('rider_ratio');
            $id = request()->post('id');
            $sys = new SysSetting();
            $result = $sys->where('id',$id)->update($data);
            if ($result) {
                $this->success('操作成功', 'setting/rider', null, 1);

            } else {
                $this->error($colum->getError());

            }
        }
        $id = request()->post('id');
        $sys = new SysSetting();
        $info = $sys->where('id',1)->field('id,rider_ratio')->find();
        $this->assign('ratio',$info);
        
        return $this->fetch();
    }
    //规格管理
    public function speclist(){
        if (request()->isAjax()) {
            $spec   =   new Spec();
            $data = $spec->where('deleted',0)->field('id,name')->select();

            return json(new Ret($data));
        }

        return $this->fetch();
    }
    //添加规格
    public function add_spec(){
        if(request()->isPost()) {
            $data['name'] = request()->post('name');
            $Spec = new Spec();
            $data['deleted'] =0;
            $data['add_time']= date('Y-m-d H:i:s', time());
            $result = $Spec->insert($data);
            if (!empty($result)) {
                $this->success('操作成功', 'setting/speclist', null, 1);

            } else {
                $this->error('没有数据');

            }
        }

        return $this->fetch();
    }
    //修改规格
    public function edit_spec(){
        if(request()->isPost()) {
            $data['name'] = request()->post('name');
            $id = request()->post('id');
            $Spec = new Spec();
            $data['deleted'] =0;
            $data['update_time']= date('Y-m-d H:i:s', time());
            $result = $Spec->where('id',$id)->update($data);
            if (!empty($result)) {
                $this->success('操作成功', 'setting/speclist', null, 1);

            } else {
                $this->error('没有数据');

            }
        }
        $id = request()->get('id');
        $Spec = new Spec();
        $result = $Spec->where('id',$id)->field('id,name')->find();
        $this->assign('spec',$result);
        return $this->fetch();

    }
    //删除规格
    public function del_spec(){
        $id = request()->post('id');
        $Spec = new Spec();

        $result = $Spec->where('id',$id)->update(['deleted'=>1]);
        if ($result) {
            return json(new Ret());
        } else {
            abort(608);
        }
    }
}