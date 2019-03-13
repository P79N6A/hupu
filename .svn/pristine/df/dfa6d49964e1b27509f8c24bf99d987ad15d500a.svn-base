<?php

namespace app\admin\controller;

use app\admin\model\AuthRule;
use think\Controller;
use think\Db;

class Base extends Controller
{

    public function __construct(){
        parent::__construct();
        //跳过检测以及主页权限
        if(!is_array(session('admin'))){
            $this->redirect('/admin/Login/index');
        }
//       权限分配
        $auth = new AuthRule();
        $menu = $auth->getMenu(session('rule'));
        $this->assign('menu',$menu);

    }


    function getModelClass()
    {
        $clazz = get_called_class();
        $clazz = str_replace('controller', 'model', $clazz);
        $object = new $clazz();
        return $object;
    }

    public function test1()
    {
        dump($this->getModelClass());
    }


    //获取地级市
    public function getCity(){
        $where['top_parentid'] = input('province_id',1);

        $where['level'] = 2;

        if (in_array($where['top_parentid'],array(1,2,9,22))) {

            $where['level'] = 3;
        }
        $listObj = Db::name('region');

        $list = $listObj->where($where)->select();

        $data=array('status'=>0,'city'=>$list);

        return json($data);
    }
}
