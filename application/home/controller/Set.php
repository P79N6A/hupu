<?php
/**
 *  用户设置
 * 
 * @王浩
 * 20181101
 * 
 */
namespace app\home\controller;
use app\common\controller\HomeBase;
use think\Db;

class Set extends HomeBase{

    // 设置
    public function setup()
    {
        $this->assign('title','设置');
        return  $this->fetch();
    }

    //隐私保护
    public function protect()
    {
        $this->assign('title','隐私保护');
        return  $this->fetch();
    }
   
    //修改密码
    public function signpassword()
    {
        $this->phone = Db::table('s_member')->where(array('id'=>$this->userInfo['member_id']))->column('phone');
        $this->assign('title','修改登录密码');
        return  $this->fetch();
    }

    //修改支付密码
    public function paypassword()
    {
        $this->phone = Db::table('s_member')->where(array('id'=>$this->userInfo['member_id']))->column('phone');
        $this->assign('title','修改支付密码');
        return  $this->fetch();
    }
}
