<?php

namespace app\admin\controller;

use think\App;
use think\Db;
use think\Request;

class Album extends Base
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
            $condition[] = ['A.type_id','=',$param];
        }
        $model = $this->getModelClass();
        $data = $model
            ->alias("A")
            ->join("album_type B",'B.id=A.type_id','left')
            ->join("music C",'C.id=A.music_id','left')
            ->where($condition)
            ->order('A.id desc')
            ->field('A.*,B.title as type_name,C.name as music_name');

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

        $photo_type =DB::name('album_type')->select();
        $music_name =DB::name('music')->select();
        $this->assign('type_list', $photo_type);
        $this->assign('music_list', $music_name);

        return $this->fetch();
    }


    public function edits(Request $request)
    {
        $model = $this->getModelClass();
        if (request()->isAjax()) {
            $param = input('post.');

            $flag = $model->editMember($param);

            return json(["code" => $flag["code"], "data" => $flag["data"], "msg" => $flag["msg"]]);
        }

        $id = input('id');
        $res = Db::name('album')->where(['id' => $id])->find();
        $this->assign('data', $res);


        $photo_type =DB::name('album_type')->select();
        $music_name =DB::name('music')->select();
        $this->assign('type_list', $photo_type);
        $this->assign('music_list', $music_name);


        $tpl= $request->controller().'/edit';

        return $this->fetch($tpl);
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
