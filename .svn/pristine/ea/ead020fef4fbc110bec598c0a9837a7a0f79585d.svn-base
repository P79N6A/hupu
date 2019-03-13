<?php  
if (PHP_SAPI != 'cli'){
	exit("Only Run In CLI Mode");
}
/*
 * 定时脚本,同步token数据,每小时执行一次
 */   
class SyncToken {

    //互浦科技
    private $appid = 'wx4cc2b44cf67fbf21'; //微信服务号appid
    private $secret = '5fe63a1a6bfe9b5adb7d8a3da7080fd4'; //微信服务号密钥
	
    /*
	 * 定时脚本获得微信access_token
	 */
    public function  run(){
		
		$urls = 'https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid='.$this->appid.'&secret='.$this->secret;
		$contents = file_get_contents($urls);
		$res = json_decode($contents,true);
		$token = isset($res['access_token']) ? $res['access_token'] : '';
		if ($token) {
			$data= $fullData = array();
            $data['expire_time'] = time() + 7200;
            $data['value'] = $token; 
            $fullData['access_token'] = $data;        
	        $json = json_encode($fullData);    
	        //负载均衡ip地址
	        $ip_arr = array('139.224.12.238','101.132.32.228');
	        $data = array('sign'=>'yxm1!2@3#2018','data'=>$json);
	        foreach ($ip_arr as $val) {
	        	$url = 'http://'.$val.'/Api/Sync/Index';
	         	$this->sendPost($url,$data);
	        }
		}
    }
    
    //发送post请求
    private function sendPost($url = '', $post_data = array())
    {
         if(empty($url) || empty($post_data)) {
            return false;
         }  
         
         $curlPost = is_array($post_data) ? http_build_query($post_data, '', '&') : $post_data;
         $ch = curl_init();//初始化curl
         curl_setopt($ch, CURLOPT_URL,$url);//抓取指定网页
         curl_setopt($ch, CURLOPT_HEADER, 0);//设置header
         curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
         curl_setopt($ch, CURLOPT_POST, 1);//post提交方式
         curl_setopt($ch, CURLOPT_POSTFIELDS, $curlPost);
         curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE); // https请求 不验证证书和hosts
         curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
         $data = curl_exec($ch);//运行curl
         curl_close($ch);  
         
         return $data;
    }
}

//初始化对象
$sys = new SyncToken();
$sys->run(); //程序运行
