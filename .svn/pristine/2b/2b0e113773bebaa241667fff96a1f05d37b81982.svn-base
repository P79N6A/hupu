<?php
/** 大转盘 */namespace app\home\controller;use app\common\controller\HomeBase;use think\Db;

class Lottery extends HomeBase{
    /**
     *大转盘
     */
    public function demo()    {
        $this->title="大转盘";
        $this->id=$this->userInfo['id'];
        $this->inte = Db::table('s_user_info')->where(array('id'=>$this->userInfo['id']))->column('inte');        
        return $this->fetch();
    }
    public function turntable()    {
        $this->title="大转盘";
        $this->id=$this->userInfo['id'];
        $this->inte = Db::table('s_user_info')->where(array('id'=>$this->userInfo['id']))->column('inte');
        
        return $this->fetch();
    }
	public function addgoods()	{
        $this->title="大转盘";
        $this->id=$this->userInfo['unionid'];
        return $this->fetch();
    }
    public function dress()    {
        $this->title="大转盘";
        $this->id=$this->userInfo['unionid'];
        return $this->fetch();
    }
    public function editdress()    {
        $this->title="大转盘";
        $this->id=$this->userInfo['unionid'];
        return $this->fetch();
    }
}