<?php

namespace app\admin\controller;

use think\App;
use think\Db;
use think\Request;

class VideoCourses extends Base
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

        $param = input('type_id');//类别
        if ('' != $param) {
            $condition['A.type_id'] = $param;
        }
        $param = input('title');//类别
        if ('' != $param) {
            $condition['A.title'] = $param;
        }
        $param = input('username');//类别
        if ('' != $param) {
            $condition['D.username'] = $param;
        }

        $model = $this->getModelClass();

        $data = $model->where($condition)->order('id desc');

        $lists = $data->page($Nowpage, $limits)->select();
        $count = $data->count();
        return toJson(0, '', $count, $lists);

    }


    public function add(Request $request)
    {

        if ($request->isAjax()) {

            $param = $request->param();

            $param['addtime'] = time();
            $param['admin_id'] = 1;
            $param['status'] = 1;

            $model = $this->getModelClass();
            $flag = $model->insertMember(json_encode($param));

            return json(['code' => $flag['code'], 'data' => $flag['data'], 'msg' => $flag['msg']]);
        }

        $lists = Db::name('article_type')->where('user_id=0')->select();
        $this->assign('lists', $lists);

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

        $id = input('id');
        if (!$id) {
            return $this->fetch('index');
        }

        $data = $model->getOneMember($id);
        $this->assign('data', $data);

        $lists = Db::name('article_type')->where('user_id=0')->select();
        $this->assign('lists', $lists);

        return $this->fetch();
    }


    /**
     * 修改字段
     * @return \think\response\Json
     */
    public function editStatus()
    {
        $id = input('param.id');
        $status = Db::name('admin_user')->where('user_id', $id)->value('lock');//判断当前状态情况
        if ($status == 1) {
            $flag = Db::name('admin_user')->where('user_id', $id)->setField(['lock' => 0]);
            return json(['code' => 0, 'data' => $flag['data'], 'msg' => '已解冻']);
        } else {
            $flag = Db::name('admin_user')->where('user_id', $id)->setField(['lock' => 1]);
            return json(['code' => 1, 'data' => $flag['data'], 'msg' => '已冻结']);
        }

    }

    /**
     * 批量删除选中的记录
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

}
