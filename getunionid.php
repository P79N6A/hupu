<?php
	session_start();
	//防止点击回退出现不可预知bug
	header("Cache-Control: no-store, no-cache, must-revalidate, post-check=0, pre-check=0");
	header("Expires: Sat, 26 Jul 1997 05:00:00 GMT");
	header("Pragma: no-cache");
	
	$user_agent = $_SERVER['HTTP_USER_AGENT'];
	if (!strpos($user_agent, 'MicroMessenger')) { die('非法访问');}

	$host = $_SERVER['HTTP_HOST'];
	if (in_array($host,array('mingxin365.com','101.132.37.1'))) {
		$appid = 'wx89da12909cf80b55'; //22度
		$secret = '1ca1e09e59902b7e71032c53312ca91d';
	}else{
		$appid = 'wx4cc2b44cf67fbf21'; //互浦科技
		$secret = '5fe63a1a6bfe9b5adb7d8a3da7080fd4';
	}
	
	$code = isset($_GET['code']) ? trim($_GET["code"]) : 0;
	$target_url = isset($_REQUEST['target_url']) ? $_REQUEST['target_url'] : '';
	$arg = 'target_url='.$target_url;
	//判断code是否存在
	if (!$code) 
	{
		//微信授权
		SetCookie('target_url',$target_url);
		$back_url = 'http://'.$host.'/getunionid.php?'.$arg;
		$redirect_uri = urlencode($back_url);
		$url = 'https://open.weixin.qq.com/connect/oauth2/authorize?appid='.$appid.'&redirect_uri='.$redirect_uri.'&response_type=code&scope=snsapi_base&state=STATE#wechat_redirect';
		header('Location:'.$url);
	}

	$url = 'https://api.weixin.qq.com/sns/oauth2/access_token?appid='.$appid.'&secret='.$secret.'&code='.$code.'&grant_type=authorization_code';
	$content = file_get_contents($url);
	$data = json_decode($content,true);
	$openid = $data['openid'];
	
	$_SESSION['openid'] = $openid;
	$vendor_path = "./ThinkPHP/Library/Vendor";
	$fullData = json_decode(file_get_contents($vendor_path."/wxsdkapi/access_token.php"));//适应旧版
	$data = $fullData->access_token;//适应旧版
	$token = $data->value;
	if ($data->expire_time < time()) 
	{
		$urls = 'https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid='.$appid .'&secret='.$secret;
		$contents = file_get_contents($urls);
		$datas = json_decode($contents,true);
		$token = $datas['access_token'];
		if ($token) 
		{
			$data= (object)array();
			$fullData = (object)array();
            $data->expire_time = time() + 3600;
            $data->value = $token;//适应旧版
            $fullData->access_token = $data;//适应旧版
            $filename = $vendor_path."/wxsdkapi/access_token.php";
            file_put_contents($filename,json_encode($fullData));
        }
	}
	
	$urlss = 'https://api.weixin.qq.com/cgi-bin/user/info?access_token='.$token.'&openid='.$openid.'&lang=zh_CN';
	$contentss = file_get_contents($urlss);
	$datass = json_decode($contentss,true);
	SetCookie('openid_contents',$contentss);
	$_SESSION['openid_contents'] = $contentss;
	header('Location:'.$_COOKIE['target_url'].'&openid='.$openid);
	
?>