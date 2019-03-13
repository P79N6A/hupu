<?php

namespace app\admin\controller;

use think\App;
use think\Db;
use think\Request;

class ArticleType extends Base
{

    public $class;

    public function __construct(App $app = null)
    {
        parent::__construct($app);

        $this->class = getClass(get_class(), 1);

    }

    public function index()
    {

        $class = $this->class;
        //        当前类名称
        $this->assign('class', $class);
        return $this->fetch();
    }



    public function getList()
    {
        //        分页必备参数
        $Nowpage = input('get.page') ? input('get.page') : 1;
        $limits = input('get.limit');// 获取总条数

        $condition = [];//查询条件

        $inte_id = input('user_id');//类别
        if ('' != $inte_id) {
            $condition['user_id'] = $inte_id;
        }

        $condition['user_id'] = 0;


        $model = $this->getModelClass();

        $data = $model
            ->alias("A")
            ->field('A.*')
//            ->join("user_info D ", "D.id=A.user_id")
//            ->order('A.id desc')
            ->where($condition);

        $lists = $data->page($Nowpage, $limits)->select();
        $count = $data->count();
        return toJson(0, '', $count, $lists);

    }


    public function add(Request $request)
    {

        if ($request->isAjax()) {

            $param = $request->param();

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
