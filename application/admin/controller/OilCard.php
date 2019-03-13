<?php

namespace app\admin\controller;

use think\App;
use think\Db;
use think\Request;

class OilCard extends Base
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

        $param = input('status');//类别
        if ('' != $param) {
            $condition['A.status'] = $param;
        }

        $model = $this->getModelClass();

        $data = $model
            ->alias("A")
            ->order('A.id desc')
            ->where($condition);

        $lists = $data->page($Nowpage, $limits)->select();
        $count = $data->count();

        foreach ($lists as $k => $v) {
            if ($v['status'] != 0) {
                $map['l.aid'] = $v['id'];
                $user = Db::name('luck_order')
                    ->alias('l')
                    ->join('user_info u', 'u.id = l.uid')
                    ->join('member m', 'm.id = u.member_id')
                    ->where($map)
                    ->field('u.nick_name,m.phone')
                    ->find();
                $lists[$k]['nick_name'] = $user['nick_name'];
                $lists[$k]['phone'] = $user['phone'];
            }
        }

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
