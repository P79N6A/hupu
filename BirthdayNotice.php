<?php  
if (PHP_SAPI != 'cli'){
	exit("Only Run In CLI Mode");
}
/*
 * 定时脚本,每天执行一次发送生日短信通知
 */   
class BirthdayNotice {
	
    /*
	 * 定时9点发送生日短信通知
	 */
    public function  run()
    {
    	global $argv;
		if (isset($argv[1]) && strtotime($argv[1])) {
			$date = $argv[1];
		} else {
			$date = null;
		}
		
    	$dbms = 'mysql';  //数据库类型
		$host = 'rm-uf6ebt0bbv8c972ktbo.mysql.rds.aliyuncs.com';  //数据库主机名
		$dbName = 'card'; //使用的数据库
		$user = 'yxm360';  //数据库连接用户名
		$pass = '4211d646219f4128b93ae058f23560d@';  //对应的密码
		$dsn = "$dbms:host=$host;dbname=$dbName";
		
    	$time = $date ? $date : date('md'); 
		$sql = "SELECT nagent_name as name,nagent_phone as phone from s_api_nagent_apply  WHERE date_format(nagent_dateofbirth, '%m%d')=".$time." group by nagent_phone";
		//初始化一个PDO对象，查询发布过期的广告数据
		$dbh = new PDO($dsn, $user, $pass); 
    	$stmt = $dbh->query($sql);
		$data = $stmt->fetchAll(PDO::FETCH_ASSOC);
		//print_r($data);die();
		if ( empty($data) ) { exit('empty data');} //数据为空不执行
 		
		foreach ($data as $val) 
		{
			$name = $val['name'];
			$phone = $val['phone'];
		    $content = "报~洋小秘总部发来贺电，我们有一句新鲜出炉的话先要对您说：莫失莫忘，仙寿恒昌；不离不弃，芳龄永继。生日快乐哦*（".$name ."总）~";//要发送的短信内容
            $url = 'http://intapi.253.com/send/json';
            $post_data['account']       = 'I4612514';
            $post_data['password']      = 'b8fW1R4HZkd53e';
            $post_data['msg'] = '【洋小秘】'.$content;
            $post_data['mobile']    = '86'.$phone;
            $res = $this->request_post($url, $post_data,true);
            
            if($res['code'] == 0){
            	$sql = "insert into s_new_msg(`msgid`,`mobile`,`code`,`addtime`)value('".$res['msgid']."',".
            	$phone.",'".$content."',".time().")";
            	$stmt = $dbh->query($sql);
            } 
		}
 	  	
 		echo 'success';
 	  	exit();
    }
    
	/**
	 * @param $url
	 * @param null $data
	 * @param bool $json
	 * @return array|mixed
	 * 模拟post请求
	 */
	private  function request_post($url, $data = NULL, $json = false)
	{
	    $curl = curl_init();
	    curl_setopt($curl, CURLOPT_URL, $url);
	    if (!empty($data)) {
	        if($json && is_array($data)){
	            $data = json_encode( $data );
	        }
	        curl_setopt($curl, CURLOPT_POST, 1);
	        curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
	        if($json){ //发送JSON数据
	            curl_setopt($curl, CURLOPT_HEADER, 0);
	            curl_setopt($curl, CURLOPT_HTTPHEADER,
	                array(
	                    'Content-Type: application/json; charset=utf-8',
	                    'Content-Length:' . strlen($data))
	            );
	        }
	    }
	    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
	    $res = curl_exec($curl);
	    $errorno = curl_errno($curl);
	
	    if ($errorno) {
	        return array('errorno' => false, 'errmsg' => $errorno);
	    }
	    curl_close($curl);
	    return json_decode($res, true);
	}
    
}

//初始化对象
$notice = new BirthdayNotice();
$notice->run(); //程序运行
