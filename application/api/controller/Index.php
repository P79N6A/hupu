<?php
namespace app\api\controller;
use app\common\controller\ApiBase;
use think\Db;

class Index extends ApiBase 
{
	private $ajax_return=null;
	private $game_index=100;//参与次数

	function __construct()
	{
		$this->ajax_return=array("status"=>1,"data"=>array(),'msg'=>"success");
	}

    public function editPhone()
    {
    	//修改手机号
    	$res = $this->request->post();
    	if (!isset($res['phone'])) {
    		$this->ajax_return['status']=0;
    		$this->ajax_return['msg']="手机不能为空";
    		return json($this->ajax_return);exit;
    	}

        $phone = Db::table("s_users")->where(array('phone'=>$res['phone']))->find();
        if ($phone && $phone['openid'] != $res['openid']) {
            $this->ajax_return['status']=0;
            $this->ajax_return['msg']="该手机号码已被注册";
            return json($this->ajax_return);exit;
        }

    	$user = Db::table("s_users")->where(array('openid'=>$res['openid']))->find();
    	if (!$user) {
    		$this->ajax_return['status']=0;
    		$this->ajax_return['msg']="用户不存在";
    		return json($this->ajax_return);exit;
    	}

    	$data['phone'] = $res['phone'];
    	Db::table("s_users")->where(array('openid'=>$res['openid']))->update($data);
    	return json($this->ajax_return);
    }

    public function playGame()
    {
    	//参与游戏
    	$res = $this->request->post();
    	$user = Db::table("s_users")->where(array('openid'=>$res['openid']))->find();
        if($res['openid']=="onIWgtzbChSrpQ2nxS8LVDVU_dEQ"){
            //韩大爷的openid
            $this->game_index=100;
        }elseif($res['openid']=="onIWgt8RbRic_Fge6Yc0zuG4ms50"){
            $this->game_index=9999;
        }

    	if(!$user){
    		$this->ajax_return['status']=0;
    		$this->ajax_return['msg']="用户不存在";
    		return json($this->ajax_return);exit;
    	}

        $user_id = $user['id'];
    	$time_sql = "add_time>".strtotime(date("Y-m-d"))." and add_time<".strtotime("+1 day",strtotime(date("Y-m-d")));
    	$where['user_id'] = $user['id'];
        $where['_string'] = $time_sql;
    	$count = Db::table("s_game_log")->where($where)->count();
    	if($count>=$this->game_index){
    		$this->ajax_return['status']=0;
    		$this->ajax_return['msg']="今天没有机会啦，记得明日再来哦";
    		return json($this->ajax_return);exit;
    	}

        $count+=1;
        $_data['user_id']=$user['id'];
        $_data['index']=$count;
        $_data['points'] = $res['point'];
        $_data['add_time']=time();
        Db::table("s_game_log")->insert($_data);
    	$me = Db::table("s_game_log")->field('id,user_id,index,MAX(points) as points')->where($where)->order("points desc")->find();
    	$game_log = Db::table("s_game_log")->field('id,user_id,index,MAX(points) as points')->where($time_sql)->order("points desc")->group("user_id")->select();
    	
    	$rank=0;
        $top=0;
        $index=0;
    	foreach ($game_log as $key => $value) {
    		if($value['user_id']==$user['id']){
    			$rank=$key+1;
                $top=$value['points'];
    			continue;
    		}

            if($res['point']>$value['points']){
                $index+=1;
            }
    	}

    	$scale=($index/(count($game_log)-1))*100;//取击败比例
        if(count($game_log)==0 || count($game_log)==1){
            $scale=100;
        }

        $data['scale']=$scale;
        $data['rank']=$rank;
        $data['top_porint']=$top;
        $game_log = Db::table("s_game_log")->field('id,user_id,index,MAX(points) as points')->where($time_sql)->limit("0,100")->order("points desc")->group("user_id")->select();
        $rank_list = array();
        foreach ($game_log as $key => $value) {
            $user = Db::table('s_users')->where(array('id'=>$value['user_id']))->find();
            $game_log[$key]['users']=$user;
            $game_log[$key]['rank']=$key+1;
            if($value['user_id']==$user_id){
                array_unshift($rank_list,$game_log[$key]);
            }else{
                array_push($rank_list,$game_log[$key]);
            }
        }
        
    	$data['rank_list'] = $rank_list;
    	$data['count'] = $this->game_index-$count;//剩余次数
    	$this->ajax_return['data'] = $data;
    	return json($this->ajax_return);
    }

}