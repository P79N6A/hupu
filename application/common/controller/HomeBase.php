<?php
namespace app\common\controller;
use think\facade\Cache;
use think\Controller;
use think\Db;
use app\api\logic\User  as userLogic;

class HomeBase extends Controller
{
    protected $userInfo = array();

	/**
	 * 初始化方法
	 */
	public function initialize()
	{			
		$noLogin = config('NO_LOGIN');		
        $new_noLogin = array();
        $act_name = strtolower($this->request->action());
        $ctr_name = strtolower($this->request->controller());
        foreach ($noLogin as $key=>$value) {
        	foreach ($value as $k => $v) {
            	$new_noLogin[strtolower($key)][] = strtolower($v);
            }
        }

        $user_info = session("user_info");
        $this->userInfo = $user_info;
        $openid = session('openid');
        
		if(isset($new_noLogin[$ctr_name]) && in_array($act_name,$new_noLogin[$ctr_name])) 
        {
    		return ;
	    }
	    
	    //判断是否是是微信公众号环境start
        $user_agent = $_SERVER['HTTP_USER_AGENT'];
        $is_wechat = strpos($user_agent, 'MicroMessenger') ? true : false;
        if ($is_wechat && empty($openid)) {
        	$url = get_current_url();
            header('Location:/getunionid.php?target_url='.urlencode($url));
            exit;
        }
        	
        if(empty($user_info) && $openid) {
	        $user_logic = new userLogic();
	        $user_info = $user_logic->login(array('openid' => $openid, 'headimg' => cookie('headimg')));
	        if ($user_info) {
	        	$this->userInfo = $user_info;
	            session("user_info",$user_info);
	            $wx_info = session('openid_contents') ? session('openid_contents') : '';
	            Db::table('s_member')->where(array('openid'=>$openid))->update(array('wx_info' => $wx_info,'last_log_time'=>time(),'last_log_ip'=>get_client_ip()));
	        }
	    }
                     
        if(empty($user_info) && !in_array($act_name, array('mycard','login'))) {
           header("location:".url("Home/Login/login"));
           exit;
        }
        
        $this->assign('id',$this->userInfo['id']);
        $this->assign('unionid',$this->userInfo['unionid']);
    }
    
}