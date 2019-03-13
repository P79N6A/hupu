<?php
	session_start();
    header("content-type:text/html;charset=utf-8");
    
    $user_agent = $_SERVER['HTTP_USER_AGENT'];
	if (!strpos($user_agent, 'MicroMessenger')) { die('非法访问');}
    $host = $_SERVER['HTTP_HOST'];
	if (in_array($host,array('mingxin365.com','101.132.37.1'))) {
		$appid ='wx89da12909cf80b55'; //22度
		$secret = '1ca1e09e59902b7e71032c53312ca91d';
	}else{
		$appid ='wx4cc2b44cf67fbf21'; //互浦科技
		$secret='5fe63a1a6bfe9b5adb7d8a3da7080fd4';
	}
	
    $code = isset($_GET['code']) ? trim($_GET["code"]) : 0;
	$target_url = isset($_GET['target_url']) ? trim($_GET['target_url']) : '';
	$arg = 'target_url='.$target_url;

	//判断code是否存在
    if (!$code) {
		//微信授权
		$back_url = 'http://'.$_SERVER['HTTP_HOST'].'/getuserinfo.php?'.$arg;
		$redirect_uri = urlencode($back_url);
		$url = 'https://open.weixin.qq.com/connect/oauth2/authorize?appid='.$appid.'&redirect_uri='.$redirect_uri.'&response_type=code&scope=snsapi_userinfo&state=STATE#wechat_redirect';
		header('Location:'.$url);
    }
    
	$url = 'https://api.weixin.qq.com/sns/oauth2/access_token?appid='.$appid.'&secret='.$appsecret.'&code='.$code.'&grant_type=authorization_code';
	$json_data = file_get_contents($url);
	$data = json_decode($json_data,true);
	if ($data['errcode']) { return; }
	
	//初始化变量
	$openid = $data['openid'];
	$access_token = $data['access_token'];
 	$_SESSION['share_openid'] = $openid;
 
	$url = "https://api.weixin.qq.com/sns/userinfo?access_token=$access_token&openid=$openid&lang=zh_CN";
	$json_data = file_get_contents($url);
	$res = json_decode($json_data,true);
	if ($res['errcode']) { return; }
	
	$_SESSION['nickname'] = $res['nickname'];
	$_SESSION['headimgurl'] = $res['headimgurl'];
	
	header('Location:'.$target_url);

    
?>