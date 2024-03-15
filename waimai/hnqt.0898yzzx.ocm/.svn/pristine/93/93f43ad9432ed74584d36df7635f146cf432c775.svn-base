<?php
namespace app\admin\model;
use think\Model;
use util\listData;
use app\common\Model\HomeCarousel as CarouselModel;
class HomeCarousel extends Model
{
    public function base($query)
    {
        return $query->where('deleted', 0);
    }
     //关联
    public function admin()
    {
        return $this->belongsTo('Admin', 'admin_id')->field('id, login_name, real_name');
    }
    //关联
    public function campus()
    {
        return $this->belongsTo('campus', 'campus_id')->field('id, name');
    }
    //后台查询范围
    protected function scopeCarouselList($query){
        return $query->field('id,name,campus_id,jump_type,jump_url,picture,status,admin_id,sort,admin_id,deleted,create_time,update_time,deleted')
            ->order('sort desc, create_time desc,update_time desc');
    }




    /**
     * 获取列表数据
     * @param $maps
     * @param $limit
     * @param $page
     * @return listData
     */
    //获取列表数据
    public function getListData($maps, $searchFields, $limit, $page){

        if (!empty($searchFields['field_name']) && !empty($searchFields['field_content'])) {
            $key = $searchFields['field_name'];
            $value = $searchFields['field_content'];
            $maps[$key] = ['like', '%' . $value . '%'];
        }

        $result = $this::scope('CarouselLis')->with('admin,campus')->where($maps)->limit($limit)->page($page) ->order('sort desc, create_time desc,update_time desc')->select();


        $count = $this->where($maps)->count();
        return new listData($result, $count);
    }
    public function editCarousel($data){

        $data['admin_id'] = session('admin.id');



        if (!isset($data['carousel_id'])||empty($data['carousel_id'])){
            $result = $this->validate(true)->allowField(true)->data($data)->save();
            if ($result) {
                return true;
            }else {
                return false;
            }
        }else {
            $id = $data['carousel_id'];
            $column = HomeCarousel::where('id', $id)->find();
            if ($column){
                $result = $column->validate(true)->allowField(true)->data($data)->save();
                if ($result){
                    return true;
                }
                else {
                    return false;
                }

            }
            else {
                return false;
            }


        }
    }



}