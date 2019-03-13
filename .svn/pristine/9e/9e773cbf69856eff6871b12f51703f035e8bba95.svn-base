<?php
namespace app\admin\controller;

use app\admin\model\Age;
use think\Db;

class Index extends Base
{
    public function index()
    {
        $type = session('admin')['vip'];
        $this->assign('admin_type',$type);

        return $this->fetch();

    }

    public function indexTest()
    {

        return $this->fetch();

    }

    /**
     * 首页
     *
     * @return \think\Response
     */
    public function console()
    {

//        待办事项数目
        $num = Db::name('Opinion');
        $param[] = ['status','=',0];
        $nums  = $num->where($param)->count();
        $this->assign('opinion_num',$nums);

//        未付费
        $num = Db::name('user_detail');
        $param1[] = ['vip_group_id','<>',0];
        $nums  = $num->where($param1)->count();
        $this->assign('un_pay_num',$nums);

//        待发货
        $num = Db::name('luck_order');
        $param2[] = ['is_true','=',2];
        $nums  = $num->where($param2)->count();
        $this->assign('un_send_num',$nums);








        return $this->fetch();
    }
    /**
     * 首页
     *
     * @return \think\Response
     */
    public function homepage1()
    {
        return $this->fetch();
    }
    /**
     * 首页
     *
     * @return \think\Response
     */
    public function homepage2()
    {
        return $this->fetch();
    }

    public function test(){

        $ages = Db::name('age')->limit(2)->select();
        $ages = Age::limit(2)->select();


        foreach ($ages as $age) {
            var_dump($age->age_group);
            $age->save();
            echo "<br>";
        }

    }
}
