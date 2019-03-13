<?php
/** 新V网首页*/
namespace app\home\controller;
use app\common\controller\HomeBase;
use app\api\logic\User  as userLogic;

class Copyv extends HomeBase
{

    public function vcopy()
    {
    	$this->assign('id',$this->userInfo['unionid']);
        if($this->request->isPost()){
            $res = Input('post.');
            $user_id = $this->userInfo['id'];
            $object_id = $res['user_id'];//复制人id
            $user_logic = new userLogic();
            $user_logic->copyCard($user_id,$object_id);
            exit;
        }

        $this->title="复制V网";
        return  $this->fetch();
    }
    
    public function vcopc()
    {
        return  $this->fetch();    
    }

    public function copychos()
    {
        return  $this->fetch();
    }
}