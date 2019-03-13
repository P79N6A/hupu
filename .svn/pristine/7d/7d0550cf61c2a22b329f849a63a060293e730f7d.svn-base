<?php

namespace app\admin\controller;

use app\admin\model\AdminUser;
use app\admin\model\Inte;
use app\admin\model\RoleUser;
use think\App;
use think\Db;
use think\Request;

class AdminLog extends Base
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

        $inte_id = input('type');//类别
        if ('' != $inte_id) {
            $condition['A.type'] = $inte_id;
        }

        $model = $this->getModelClass();

        $data = $model
            ->alias("A")
            ->field('A.*,B.username,D.nick_name')
            ->join("admin_user B ", "B.user_id=A.admin_id")
            ->join("withdraw C ", "C.id=A.object_id")
            ->join("user_info D ", "D.id=C.user_id")
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

            $role = new \app\admin\model\Role();
            $flag = $role->insertMember(json_encode($param));

            return json(['code' => $flag['code'], 'data' => $flag['data'], 'msg' => $flag['msg']]);
        }

        return $this->fetch();
    }

    public function edit(Request $request)
    {
        $id = input('id');
        $param = input('post.');
        $admin_user = new AdminUser();
        $role_user = new RoleUser();


        if ($request->isAjax()) {

            $param['id'] = $id;
            $cond = ['user_id' => $id];
            $role_user->where($cond)->where("id != 1")->delete();

            $data = ['user_id' => $id, 'role_id' => $param['role']];
            $role_user->save($data);

            if ($request->param('password', '') == '') {
                unset($param['password']);
            } else {
                $param['password'] = md5(sha1(md5($param['password'])));
            }

            $flag = $admin_user->editMember($param);

            return json(['code' => $flag['code'], 'data' => $flag['data'], 'msg' => $flag['msg']]);

        }

        $data = $admin_user
            ->alias('A')
            ->field('A.*,B.user_id,B.role_id,C.name')
            ->join("role_user B", "A.user_id=B.user_id", "left")
            ->join("role C", "B.role_id=C.id", "left")
            ->find($id);

        $roles = Role::select();

        $this->assign('data', $data);
        $this->assign('roles', $roles);

        return $this->fetch();


    }

    public function edits()
    {
        $role = new \app\admin\model\Role();
        if (request()->isAjax()) {
            $param = input('post.');

            $flag = $role->editMember($param);

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
