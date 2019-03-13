<?php

namespace app\admin\controller;

use think\App;
use think\Db;
use think\Request;

class PayCode extends Base
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

        return $this->fetch();
    }


    public function getList()
    {
        //        分页必备参数
        $Nowpage = input('get.page') ? input('get.page') : 1;
        $limits = input('get.limit');// 获取总条数

        $condition = [];//查询条件

        $param = input('type');//类别
        if ('' != $param) {
            $condition['A.type'] = $param;
        }

        $param = input('status');//类别
        if ('' != $param) {
            $condition['A.status'] = $param;
        }

        $param = input('phone');//类别
        if ('' != $param) {
            $condition['E.phone'] = $param;
        }


        $param = input('res_phone');//类别
        if ('' != $param) {
            $condition['D.phone'] = $param;
        }


        $param = input('res_name');//类别
        if ('' != $param) {
            $condition['B.nick_name'] = ['like', "%{$param}%"];;
        }

        $param = input('nick_name');//类别
        if ('' != $param) {
            $condition['C.nick_name'] = $param;
        }

        $model = $this->getModelClass();

        $data = $model
            ->alias("A")
            ->join("user_info B ", "B.id=A.user_id","left")
            ->join("member D ", "D.id=B.member_id","left")
            ->join("pay_code_log F ", "F.id=A.id","left")
            ->join("user_info C ", "C.id=F.user_id","left")
            ->join("member E ", "E.id=C.member_id","left")
            ->field('A.*,B.nick_name as res_name,D.phone as res_phone,C.nick_name,E.phone')
            ->order('A.id desc')
            ->where($condition);

        $lists = $data->page($Nowpage, $limits)->select();
        $count = $data->count();
        return toJson(0, '', $count, $lists);

    }


    public function add(Request $request)
    {

        if ($request->isAjax()) {

            $param = $request->param();
            $param['addtime'] = time();
            $param['user_id'] = $param['name_num'];

            $param['code'] = getRandomString(11,'0123456789');
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
