<?php
/** 新版V网 */
namespace app\home\controller;
use think\Db;
use Think\Controller;
use app\api\logic\User  as userLogic;

class Newnetwork extends Controller 
{
    /**
     * 初始化方法
     */
    public function _initialize()
    {
        $noLogin = config('NO_LOGIN');
        $new_noLogin = array();
        $act_name = strtolower(ACTION_NAME);
        $ctr_name = strtolower(CONTROLLER_NAME);
        foreach ($noLogin as $key=>$value) {
            foreach ($value as $k => $v) {
                $new_noLogin[strtolower($key)][] = strtolower($v);
            }
        }
        
    	if(isset($noLogin[$ctr_name]) && in_array($act_name,$noLogin[$ctr_name])) 
        {
    		return ;
	    }
	    
       	$user_info = session("user_info");
        $this->userInfo = $user_info;
        $openid = session('openid');
            
        //判断是否是是微信公众号环境start
        $user_agent = $_SERVER['HTTP_USER_AGENT'];
        $is_wechat = strpos($user_agent, 'MicroMessenger') ? true : false;
        if ($is_wechat && empty($openid)) {
        	$url = get_current_url();
            header('Location:/getunionid.php?target_url='.urlencode($url));
            exit;
        }
            
        if(empty($user_info)) {
           	$user_logic = new userLogic();
            $user_info = $user_logic->login(array('openid' => $openid, 'headimg' => cookie('headimg')));
            if ($user_info) {
            	$this->userInfo = $user_info;
                session("user_info",$user_info);
                $wx_info = session('openid_contents') ? session('openid_contents') : '';
                Db::table('s_member')->where(array('openid'=>$openid))->update(array('wx_info' => $wx_info,'last_log_time'=>time(),'last_log_ip'=>get_client_ip()));
            }
        }

        if(empty($user_info) && $act_name != "mycard") {
        	header("location:".url("Home/Mycenter/login"));
            exit;
        }
    }

    public function  all_imgtext()
    {
        $this->assign('title','全部图文');
        return  $this->fetch();

    }

    public function  all_product()
    {
        $this->assign('title','全部产品');
        $this->assign('id',$this->userInfo['id']);
        return  $this->fetch();
    }


    public function  all_link()
    {
        $this->assign('title','全部连接');
        $this->assign('id',$this->userInfo['unionid']);
        $this->assign('user',$this->userInfo['id']);
        return  $this->fetch();
    }

    public function  all_video()
    {
        $this->assign('title','全部视频');
        $this->assign('id',$this->userInfo['id']);
        return  $this->fetch();
    }

    public function  all_imgtext1()
    {
        $this->title="全部图文";
        $unionid = input("get.unionid");
        $this->assign('unionid',$unionid);
        return  $this->fetch();        
    }

    public function  all_product1()
    {
     	$this->title="全部产品";
        $unionid = input("get.unionid");
        $id = input("get.id");
        $this->assign('unionid',$unionid);
        $this->assign('id',$id);
        return  $this->fetch();       
    }

    public function  all_link1()
    {
        $this->title="全部链接";
        $unionid = input("get.unionid");
        $id = input("get.id");
        $this->assign('id',$unionid);
        $this->assign('user',$id);
        return  $this->fetch();       
    }

    public function  all_video1()
    {
        $this->title="全部视频";
        $unionid = input("get.unionid");
        $id = input("get.id");
        $this->assign('unionid',$unionid);
        $this->assign('id',$id);
        return  $this->fetch();
            
     }

     public function  add_video()
     {
        $this->assign('title','添加视频');
        $this->assign('id',$this->userInfo['unionid']);
        return  $this->fetch();       
     }

     public function  my_video()
     {
        $this->assign('title','我的视频');
        return  $this->fetch();       
     }

     public function video_type()
     {
        $this->assign('title','视频分类');
        return  $this->fetch();
     }

     public function video_sort()
     {
        $this->assign('title','视频分类排序');
        return  $this->fetch();
     }

     public function move_video()
     {
        $this->title="移动";
        return  $this->fetch();
     }

     public function move()
     {
     	$this->title="移动到分类";
        return  $this->fetch();
     }
        
     //产品
    public function add_protype()
    {  
        $this->title="添加产品分类";
        return  $this->fetch();
    }

    public function sort_product()
    {  
        $this->title="分类排序";
        return  $this->fetch();
    }

    public function my_product()
    {  
        $this->title="我的分类";
        return  $this->fetch();
    }
        
    public function move_pro()
    {  
        $this->title="分类移动";
        return  $this->fetch();
    }

    public function sure_con()
    {  
     	$this->title="完成移动";
        return  $this->fetch();
    }
}