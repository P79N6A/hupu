<?php
namespace app\common\controller;

use think\facade\Cache;
use think\Controller;
use think\Db;

class AdminBase extends Controller
{

	/**
	 * 初始化方法
	 */
	public function initialize()
	{
		// 判断用户是否登录
        if(!isset($_SESSION[config('USER_AUTH_KEY')]))
        {
            $this->redirect('Public/login');
        }

        $admin = Db::table('s_adminUser')->field('username,pid,user_info_id,address_id,vip')->find($_SESSION[config('USER_AUTH_KEY')]);
        $this->assign('admin', $admin);
        $notAuth = in_array(MODULE_NAME, explode(',', config('NOT_AUTH_MODULE'))) || in_array($this->request->action(), explode(',', config('NOT_AUTH_ACTION'))) ;
		//这段代码的作用是什么，要仔细查一下
       if(config('USER_AUTH_ON') && !$notAuth)
       {
       		$rbac = new \Org\Util\Rbac();
		    //检测是否登录，没有登录就打回设置的网关
		    $rbac::checkLogin();
		    //检测是否有权限没有权限就做相应的处理
			if(!$rbac::AccessDecision())
			{
			    echo '<script type="text/javascript">alert("没有权限");</script>';
			    die();
			}
       }
	}
	
	public function admin_log($type,$object_id=null,$content=null)
	{
        $data = array(
            'admin_id' => $_SESSION['admin']['user_id'],
            'object_id'=>$object_id,
            'content'=>$content,
            'addtime'=>time(),
            'type'=>$type
        );

        $res = Db::table('s_admin_log')->insert($data);
        return $res;
    }
    
}