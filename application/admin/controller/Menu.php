<?php

namespace app\admin\controller;
use app\admin\model\AuthRule;
use org\LeftNav;
use think\App;
use think\Db;

class Menu extends Base
{
    public $class;

    public function __construct(App $app = null)
    {
        parent::__construct($app);

        $this->class = getClass(get_class(), 1);

    }

        /**
     * [index 列表]
     * @return [type] [description]
     * @author [田建龙] [864491238@qq.com]
     */
    public function index()
    {
        //        当前类名称
        $class = $this->class;
        $this->assign('class', $class);

        $nav = new LeftNav();
        $menu = new AuthRule();
        $admin_rule = $menu->getAllMenu();
        $arr = $nav::rule($admin_rule);
        $this->assign('admin_rule',$arr);

        return $this->fetch();
    }
    public function getList(){

        $nav = new LeftNav();
        $menu = new AuthRule();
        $admin_rule = $menu->getAllMenu();

        $arr = $nav::rule($admin_rule);
        return toJson(0, $admin_rule, 1, $arr
        );

    }

	
    /**
     * [add_rule 添加菜单]
     * @return [type] [description]
     * @author [田建龙] [864491238@qq.com]
     */
	public function add_rule()
    {
        if(request()->isAjax()){
            $param = input('post.');           
            $menu = new AuthRule();
            $flag = $menu->insertMenu($param);
            return json(['code' => $flag['code'], 'data' => $flag['data'], 'msg' => $flag['msg']]);
        }

        return $this->fetch();
    }


    /**
     * [edit_rule 编辑菜单]
     * @return [type] [description]
     * @author [田建龙] [864491238@qq.com]
     */
    public function edit_rule()
    {
        $menu = new AuthRule();
        if(request()->isPost()){
            $param = input('post.');
            $flag = $menu->editMenu($param);
            return json(['code' => $flag['code'], 'data' => $flag['data'], 'msg' => $flag['msg']]);
        }
        $id = input('param.id');
        $this->assign('menu',$menu->getOneMenu($id));
        return $this->fetch();
    }


    public function edits()
    {
        $model = new AuthRule();
        if (request()->isAjax()) {
            $param = input('post.');
            $flag = $model->editMenu($param);

            return json(["code" => $flag["code"], "data" => $flag["data"], "msg" => $flag["msg"]]);
        }
    }



    /**
     * [roleDel 删除角色]
     * @return [type] [description]
     * @author [田建龙] [864491238@qq.com]
     */
    public function del_rule()
    {
        $id = input('param.id');
        $menu = new AuthRule();
        $flag = $menu->delMenu($id);
        return json(['code' => $flag['code'], 'data' => $flag['data'], 'msg' => $flag['msg']]);
    }



    /**
     * [ruleorder 排序]
     * @return [type] [description]
     * @author [田建龙] [864491238@qq.com]
     */
    public function ruleorder()
    {
        if (request()->isAjax()){
            $param = input('post.');
            $auth_rule = new AuthRule();

            foreach ($param as $id => $sort){
//                去掉空格
                $sort = trim($sort);
                $re=  $auth_rule->where(array('id' => $id ))->setField('sort' , $sort);
                $arr[] = [$id=>[$sort,$re]];
            }
            return json(['code' => 1, 'msg' => '排序更新成功','data'=>$arr]);
        }
    }


    /**
     * [rule_state 菜单状态]
     * @return [type] [description]
     * @author [田建龙] [864491238@qq.com]
     */
    public function rule_state()
    {
        $id = input('param.id');
        $status = Db::name('auth_rule')->where('id',$id)->value('status');//判断当前状态
        if($status==1)
        {
            $flag = Db::name('auth_rule')->where('id',$id)->setField(['status'=>0]);
            return json(['code' => 1, 'data' => $flag['data'], 'msg' => '已禁止']);
        }
        else
        {
            $flag = Db::name('auth_rule')->where('id',$id)->setField(['status'=>1]);
            return json(['code' => 0, 'data' => $flag['data'], 'msg' => '已开启']);
        }
    
    }

    public function del(){
        $auth = new AuthRule();
        $delMenu = $auth->delMenu(input('id'));
        return $delMenu;
    }


    /**
     * 修改字段
     * @return \think\response\Json
     */
    public function editStatus()
    {
        $id = input('param.id');

        $field = input('field', 'status');

        $class = 'auth_rule';
        $table = Db::name($class)->where('id', $id);
        //判断当前状态情况
        $status = $table->value($field);

        if ($status == 1) {
            $flag = $table->setField([$field => 0]);
            return json(['code' => 1, 'data' => $flag, 'msg' => '成功']);

        } else {
            $flag = $table->setField([$field => 1]);
            return json(['code' => 0, 'data' => $flag, 'msg' => 'ok']);

        }

    }



}