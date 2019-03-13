<?php
namespace app\api\controller;
use think\Controller;
use think\Db;
use app\common\controller\ApiBase;
use app\api\logic\Member  as memberLogic;

class Code extends  ApiBase
{
	
 	function initialize()
 	{
 		parent::initialize();
    }
    
    /**
     * 隔空传
     */
    public function frimessage()
    {
        if($_POST)
        {
            $post = $this->request->post();
            if(empty($post['phone'])||!preg_match("/^1[3456789]{1}\d{9}$/",$post['phone']))
            {
                $this->array_return['code']=1;
                $this->array_return['msg']="手机号码格式错误!";
                return json($this->array_return);
            }
            
            $user_id = $this->userInfo['id'];
            $msg_num = Db::table('s_msg_num')->where(array('user_id'=>$user_id,'add_time'=>array('gt',strtotime(date('Ymd')))))->count();
            if($msg_num>=10)
            {
                return json(array('code'=>1,'msg'=>'已达到今天上限'));
            }
            
            $res = array();
            $res['phone'] = $post['phone'];
            $res['user_id'] = $post['user_id']?$post['user_id']:$user_id;
            $res['add_time']=time();
            Db::table('s_msg_num')->insert($res);
            $par = array('nick_name'=>$this->userInfo['nick_name'],'user_id'=>$user_id);
            $member_mod = new memberLogic();
            $res = $member_mod->sendFriendMessage($post['phone'],$par);
            if($res['status']=="0")
            {
                $this->array_return['code']=0;
                $this->array_return['msg']="发送成功";
            }else{
                $this->array_return['code']=1;
                $this->array_return['msg']="发送失败";
            }
            return json($this->array_return);
        }else{
            return json(['ResultType'=>self::__ERROR5__,'Message'=>"请求类型错误"]);
        }
    }
}