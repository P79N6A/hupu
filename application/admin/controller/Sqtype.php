<?php

namespace app\admin\controller;

use think\App;
use think\Db;
use think\db\Where;
use think\Request;

class Sqtype extends Base
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

        $type = input('type',0);
        $this->assign('type',$type);

        $type = input('id',0);
        $this->assign('id',$type);

        return $this->fetch();
    }


    public function getList()
    {
        //        分页必备参数
        $Nowpage = input('get.page') ? input('get.page') : 1;
        $limits = input('get.limit');// 获取总条数

        $condition = [];//查询条件

        $type = input('id',0);
        if($type == 0 ){
            $condition['parent_id'] = 0;

        }else{
            $condition['parent_id'] = $type;

        }

        $type = input('type',0);
        $this->assign('type',$type);


        $model = $this->getModelClass();
        $count = $model->getAllCountByWhere($condition);
        $lists = $model->getMemberByWhere($condition,$Nowpage, $limits);

        return toJson(0, $type, $count, $lists);

    }


    public
    function getUserOne()
    {

        $id = input('id');//类别
        $model = $this->getModelClass();
        $data = $model->getOneMember($id);

        return toJson(0, '', 1, $data);

    }

    public function blocking()
    {
        $id = input('id');

        $model = $this->getModelClass();
        $data = $model->getOneMember($id);

        return toJson(0, '', 1, $data);

    }


    public
    function add(Request $request)
    {

        if ($request->isAjax()) {

            $param = $request->param();

            $model = $this->getModelClass();
            $flag = $model->insertMember(json_encode($param));

            return json(['code' => $flag['code'], 'data' => $flag['data'], 'msg' => $flag['msg']]);
        }


        $model = $this->getModelClass();
        $condition['parent_id'] = 0;
        $lists = $model->getMembers($condition);
        $this->assign('lists', $lists);

        return $this->fetch();
    }


    public
    function edits()
    {
        $model = $this->getModelClass();
        if (request()->isAjax()) {
            $param = input('post.');

            $flag = $model->editMember($param);

            return json(["code" => $flag["code"], "data" => $flag["data"], "msg" => $flag["msg"]]);
        }

        $id = input('id');
        if (!$id) {
            return $this->fetch('index');
        }

        $condition['parent_id'] = 0;
        $lists = $model->getMembers($condition);
        $this->assign('lists', $lists);

        return $this->fetch();
    }


    /**
     * 修改字段
     * @return \think\response\Json
     */
    public
    function editStatus()
    {
        $id = input('param.id');
        //判断当前状态情况
        $status = Db::name('member')->where('id', $id)->value('status');

        if ($status == 1) {

            $flag = Db::name('member')->where('id', $id)->setField(['status' => 2]);
            return json(['code' => 1, 'data' => $flag, 'dd' => $status, 'msg' => '冻结成功']);

        } else {
            $flag = Db::name('member')->where('id', $id)->setField(['status' => 1]);
            return json(['code' => 0, 'data' => $flag, 'dd' => $status, 'msg' => '已解冻']);

        }

    }

    /**
     * 批量删除选中的记录
     * @return \think\response\Json
     */
    public
    function delSelecteds()
    {
        $ids = input('ids');
        $class = getClass(get_class());
        return deletes($class, $ids, '');
    }

    /**
     * 删除单个实例
     * @return mixed
     */
    public
    function del()
    {
        $id = input('id');
        $model = $this->getModelClass();
        $flag = $model->del($id, get_class());
        return $flag;
    }

    /**
     * 假删除
     */
    function delMember()
    {

        $id = input('id', 0);
        if ($id == 0) {
            return ['code' => 0, 'data' => '', 'msg' => '缺省参数id'];
        }
        $model = $this->getModelClass();
        $flag = $model->delMember($id, 'status', 0);
        return $flag;
    }

    function showRec(){
        //        当前类名称
        $class = $this->class;
        $this->assign('class', $class);

        $this->assign('user_id',input('id'));

        return $this->fetch();

    }

    public function getRecUsers()
    {
        // 分页必备参数
        $Nowpage = input('get.page') ? input('get.page') : 1;
        $limits = input('get.limit');// 获取总条数
        $id = input('id',0);
        $condition['A.rec_user_id'] = $id;

        $param = input('isPay');
        if ('' != $param) {
            if ($param == 1) {
                $condition['A.vip_group_id'] = 1;
            } else {
                $condition['A.vip_group_id'] = array('neq', 0);
            }
        }

        $model = $this->getModelClass();
        $data = $model
            ->alias('A')
            ->join('vip_group B','B.id=A.vip_group_id','left')
            ->join('user_info C','C.id=A.rec_user_id','left')
            ->where($condition)
            ->field('A.*,B.vip_name,C.nick_name as rec_name');

        $count = $data->count();

        $lists = $data->page($Nowpage, $limits)->select();

        foreach ($lists as $key => $list) {
            $lists[$key]['balance'] = number_format(($lists[$key]['money'] + $lists[$key]['frozen_money'] + $lists[$key]['already_money']), 2, ".", "");
        }

        return toJson(0, '', $count, $lists);

    }

}
