<?php

namespace app\admin\controller;

use think\App;
use think\Db;
use think\Request;

class VipOrder extends Base
{

    public $class;

    public function __construct(App $app = null)
    {
        parent::__construct($app);

        $this->class = getClass(get_class(), 1);

    }

    public function index()
    {
        //        当前类名称
        $class = $this->class;
        $this->assign('class', $class);

        $con[] = ['pay_status','=',1];
        $con[] = ['vip_list_id','lt',4];
        $price_list = Db::name('vip_order')->where($con)->field('price')
            ->group('price')->select();

        $this->assign('lists', $price_list);

        $lists = Db::name('region')->where(array('parent_id'=>0))->select();
        $this->assign('province_list', $lists);


        return $this->fetch();
    }


    public function getList()
    {
        //        分页必备参数
        $Nowpage = input('get.page') ? input('get.page') : 1;
        $limits = input('get.limit');// 获取总条数

        $condition = [];//查询条件

        $condition[] =['A.pay_status','=',1];

        $condition[] =array('A.vip_list_id','lt',4);


        $param = input('phone');//类别
        if ('' != $param) {
            $condition[] = ['A.phone','=',$param];
        }

        $param = input('order_no');//类别
        if ('' != $param) {
            $pay = Db::name('capital_log')->where(array('pay_sn'=>$param))->field('order_sn')->find();
            $condition[] = ['A.order_no','=',$pay['order_sn']];
        }
        $param = input('isPay');//类别
        if ('' != $param) {
            $condition[] = ['A.price','=',$param];
        }
        $param = input('type');//类别
        if ('' != $param) {
            $condition[] = ['A.type','=',$param];
        }
        $param = input('city_id');//类别
        if ('' != $param) {
            $condition[] = ['B.city_id','=',$param];
        }

        $param = input('province_id');//类别
        if ('' != $param) {
            $condition[] = ['B.province_id','=',$param];
        }

        $param = input('nick_name');//类别
        if ('' != $param) {
            $condition[] = ['B.nick_name','like',"%{$param}%"];
        }

        $model = $this->getModelClass();

        $data = $model
            ->alias("A")
            ->join('user_info B', 'A.user_id = B.id','left')
            ->join('member E', 'B.member_id = E.id','left')
            ->join('vip_list D','A.vip_list_id = D.id','left')
            ->join('vip_group C','D.vip_group_id = C.id','left')
            ->join('region F','B.province_id = F.region_id','left')
            ->join('region G','B.city_id = G.region_id','left')
            ->join('region H','B.district_id = H.region_id','left')
            ->where($condition)
            ->order('A.id desc')
            ->field('A.*,B.nick_name,C.vip_name,F.region_name as province_name,G.region_name as city_name,H.region_name as district_name,E.phone');

        $lists = $data->page($Nowpage, $limits)->select();
        $count = $data->count();

        return toJson(0, '', $count, $lists);

    }


    public function add(Request $request)
    {

        if ($request->isAjax()) {

            $param = $request->param();
            $param['addtime'] = time();

            $model = $this->getModelClass();
            $flag = $model->insertMember(json_encode($param));

            return json(['code' => $flag['code'], 'data' => $flag['data'], 'msg' => $flag['msg']]);
        }

        return $this->fetch();
    }


    public function edits()
    {
        $model = $this->getModelClass();
        if (request()->isAjax()) {
            $param = input('post.');

            $flag = $model->editMember($param);

            return json(["code" => $flag["code"], "data" => $flag["data"], "msg" => $flag["msg"]]);
        }
    }


    /**
     * 修改字段
     * @return \think\response\Json
     */
    public function editStatus()
    {
        $id = input('param.id');

        $field = input('field', 'status');

        $class = $this->class;
        $table = Db::name($class)->where('id', $id);
        //判断当前状态情况
        $status = $table->value($field);

        if ($status == 1) {
            $flag = $table->setField([$field => 2]);
            return json(['code' => 1, 'data' => $flag, 'msg' => '成功']);

        } else {
            $flag = $table->setField([$field => 1]);
            return json(['code' => 0, 'data' => $flag, 'msg' => 'ok']);

        }

    }

    /**
     * 删除选中的记录
     * @return \think\response\Json
     */
    public function delSelecteds()
    {
        $ids = input('ids');
        $class = getClass(get_class());
        return deletes($class, $ids, '');
    }


    /**
     * 删除单个实例
     * @return mixed
     */
    public function del()
    {
        $id = input('id');
        $model = $this->getModelClass();
        $flag = $model->del($id, get_class());
        return $flag;
    }

}
