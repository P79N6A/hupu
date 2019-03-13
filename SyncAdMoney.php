<?php  
if (PHP_SAPI != 'cli'){
	exit("Only Run In CLI Mode");
}
/*
 * 定时脚本,每天执行一次回收广告费
 */   
class SyncAdMoney {
	
    /*
	 * 定时凌晨回收前一天广告余额脚本
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
		
    	$time = $date ? strtotime($date) : strtotime(date('Ymd')); 
		$sql = 'select id,user_id,amount,single_amount,clicked_count,activity_id from s_share_list where `status` = 2 and expire_time<'.$time;
		//初始化一个PDO对象，查询发布过期的广告数据
		$dbh = new PDO($dsn, $user, $pass); 
    	$stmt = $dbh->query($sql);
		$data = $stmt->fetchAll(PDO::FETCH_ASSOC);
		if ( empty($data) ) { exit('empty data');} //数据为空不执行
		//print_r($data);die($sql);
		$cur_time = time();
 		foreach ($data as $item) 
 		{
 			$uid = $item['user_id'];
 			$share_id = $item['id'];
 			$money = $item['amount'] - ($item['single_amount']*$item['clicked_count']);
 			if ($money) {
	 			//回收用户广告费
	 			$sql = 'update s_user_info set ad_money=ad_money+:ad_money where id=:userId';
	 			$stmt = $dbh->prepare($sql);  
				$stmt->execute(array(':userId'=>$uid, ':ad_money'=>$money));  
				//插入广告消费记录表
	        	$sql = 'insert into s_ad_consume(share_id,user_id,money,type,add_time)value('.$share_id.','.$uid.','.$money.','.$item['activity_id'].','.$cur_time.')';
	 			$stmt = $dbh->exec($sql);
			}
			//修改分享活动状态
			$sql = 'update s_share_list set `status`=3 where id ='.$share_id;
	 		$stmt = $dbh->exec($sql);
 		}
 	  	
 		echo 'success';
 	  	exit();
    }
    
}

//初始化对象
$sys = new SyncAdMoney();
$sys->run(); //程序运行
