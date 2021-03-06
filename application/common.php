<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2016 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 流年 <liu21st@gmail.com>
// +----------------------------------------------------------------------

//
if(version_compare(PHP_VERSION,'7.0.0','>')) {
	function titleArticle($url)
	{
		$ql = \QL\QueryList::getInstance();
		$ql->use(\QL\Ext\PhantomJs::class,'/usr/bin/phantomjs');
		$ql->browser($url);
	
		$thumb = '';
		$images = $ql->find('img')->attrs('src');
		if ($images) {
	       foreach ($images as $key=>$img) {
	            $thumb = $img;
	            if ($key > 3 ) {
	                break;
	            }
	        }
	    }
	    
		$title = $ql->find('.article-title')->eq(0)->text();
		$content = $ql->find(".article-content")->html();
		return array("content"=>trim($content),"thumb"=>$thumb,"title"=>trim($title));
	}
}else{
	function titleArticle($url)
	{
	    header("Content-Type: text/html;charset=utf-8");
	    require_once EXTEND_PATH.'/PHPExcel/PHPExcel.php';;
	    phpQuery::newDocumentFile($url);
	    //获取标题
	    $title = pq(".article-title:first")->text();
	    $title = htmlspecialchars($title);
	    //图片处理
	    $thumb = '';
	    $images = pq('.article-content img');
	    if ($images->length) {
	       foreach ($images as $key=>$img) {
	            $img = pq($img);
	            $thumb = $img->attr("src");
	            if ($key>3) {
	                break;
	            }
	        }
	    }
	    
	    //特殊处理
	    pq("p")->contents()->not('[nodeType=1]')->wrap('<span/>');
	    pq("div")->contents()->not('[nodeType=1]')->wrap('<span/>');
	    pq("span")->contents()->not('[nodeType=1]')->wrap('<span/>');
	    $content = pq(".article-content")->html();
	
	    return array("content"=>trim($content),"thumb"=>$thumb,"title"=>trim($title));
	}
}

	/**
 * @param $imgs
 * @param $start_name
 * @return string
 * @throws \OSS\Core\OssException
 * 上传阿里云图片
 */
function upload_img_to_aliyun($imgs,$start_name)
{
    if(substr($_SERVER['HTTP_HOST'],0,1) == 'w'){
        $start_name = ucwords($start_name);
    }else{
        $start_name = 'Test';
    }

    $img = '';
    if(substr($imgs,0,5) == 'data:'){
        //上传base64图
        $result = $this->base64_to_blob($imgs);
        $content =  $result['blob'];
        //匹配成功
        $img_type = str_replace('image/','',$result['type']);
        $img_type = str_replace('jpeg','jpg',$img_type);
        $image_name = uniqid().rand(1000,9999).'.'.$img_type;
        $object = $start_name.'/'.date("Y-m-d",time())."/".$image_name;
        try{
        	$conf = config('OSS_IMAGE');
            $ossClient = new \OSS\OssClient($conf['accessKeyId'], $conf['accessKeySecret'], $conf['endpoint']);
            $image_file = $ossClient->putObject($conf['bucket'], $object, $content);
            if ($image_file['status'] == 1){
                $shift_url = str_replace($conf['oss_url'],$conf['cdn_usls'],$image_file['url']);
                $img =  $shift_url."@!protected";
            }
        } catch(OssException $e) {

        }
    }else{
        $end_name = trim(strrchr($imgs['name'], '.'),'.');
        $end_name = $end_name == 'jpeg' ? 'jpg' : $end_name;
        $save_name = $start_name.'/'.date('Y-m-d').'/'.time().rand(1,9999).'.'.$end_name;
        try{
            $ossClient = new \OSS\OssClient($conf['accessKeyId'], $conf['accessKeySecret'], $conf['endpoint']);
            $image_file =  $ossClient->uploadFile($conf['bucket'], $save_name, $imgs['tmp_name']);
            if ($image_file['status'] == true){
                $shift_url = str_replace($conf['oss_url'],$conf['cdn_usls'],$image_file['url']);
                $img =  $shift_url."@!protected";

            }else{
                // 上传错误提示错误信息

            }
        } catch(OssException $e) {

        }
    }
    
    return $img;

}
/**
 * 上传视频到阿里云
 */
function upload_video_to_aliyun($videos,$start_name)
{
    if(substr($_SERVER['HTTP_HOST'],0,1) == 'w'){
        $start_name = ucwords($start_name);
    }else{
    	$start_name = 'Test';
    }
    
    $video = '';
    $end_name = trim(strrchr($videos['name'], '.'),'.');
    $save_name = $start_name.'/'.date('Y-m-d').'/'.time().rand(1,9999).'.'.$end_name;
    try{
    	$conf = config('OSS_VIDEO');
        $ossClient = new \OSS\OssClient($conf['accessKeyId'], $conf['accessKeySecret'], $conf['endpoint']);
        $image_file =  $ossClient->uploadFile($conf['bucket'], $save_name, $videos['tmp_name']);
        if ($image_file['status'] == true){
            $shift_url = str_replace($conf['oss_url'],$conf['cdn_usl'],$image_file['url']);
            $video =  $shift_url;

        }else{
            // 上传错误提示错误信息

        }
    } catch(OssException $e) {

    }
    return $video;
}

/*
 *微信新闻抓取
 */
function captArticle($url="")
{
	header("Content-Type: text/html;charset=utf-8");
    require_once EXTEND_PATH.'/PHPExcel/PHPExcel.php';;
    phpQuery::newDocumentFile($url);    
    //获取标题
    $title = pq(".rich_media_title:first")->text();
    $title = htmlspecialchars($title);
    //图片处理
    $thumb = ''; 
	$images = pq(".rich_media_content img");
	if ($images->length) {
	    foreach ($images as $key=>$data) {
	        $data_src = pq($data)->attr('data-src');
	        pq($data)->removeAttr('data-src');
	        $len = strpos($data_src,"?");
	        if ($len) { $data_src = substr($data_src,0,$len); }
	 		pq($data)->attr("src",$data_src);
	 		$data_src = str_replace("https","http",$data_src);
	    	if ($key<2) {
	    		$thumb = $data_src;
	    	}
	    }
	}
	
	//特殊处理
	pq("p")->contents()->not('[nodeType=1]')->wrap('<span/>');
	pq("div")->contents()->not('[nodeType=1]')->wrap('<span/>');
    pq("span")->contents()->not('[nodeType=1]')->wrap('<span/>');
	$content = pq(".rich_media_content")->html();
	$content = preg_replace('/[\x{10000}-\x{10FFFF}]/u', '', $content); //过滤掉表情
	$content = str_replace('text-indent: 2em;', 'text-indent:1.5em;', $content); 
 	return array("content"=>trim($content),"thumb"=>$thumb,"title"=>trim($title));
}

/**
 * @param $string
 * @param int $length
 * @param string $ellipsis
 * @return null|string|string[]
 * 过滤所有标签
 */
function cutstr_html($string,$length=0,$ellipsis='…',$type=0)
{
    $string=htmlspecialchars_decode($string);
    $string=strip_tags($string);
    if($type != 1){//不过滤断行
        $string=preg_replace('/\n/is','',$string);
        $string=preg_replace('/&nbsp;/is','',$string);
    }
    $string=preg_replace('/ |　/is','',$string);
    preg_match_all("/[\x01-\x7f]|[\xc2-\xdf][\x80-\xbf]|\xe0[\xa0-\xbf][\x80-\xbf]|[\xe1-\xef][\x80-\xbf][\x80-\xbf]|\xf0[\x90-\xbf][\x80-\xbf][\x80-\xbf]|[\xf1-\xf7][\x80-\xbf][\x80-\xbf][\x80-\xbf]/",$string,$string);
    if(is_array($string)&&!empty($string[0])){
        if(is_numeric($length)&&$length){
            $string=join('',array_slice($string[0],0,$length)).$ellipsis;
        }else{
            $string=implode('',$string[0]);
        }

        $string = preg_replace( "@<script(.*?)</script>@is", "", $string ); 
        $string = preg_replace( "@<iframe(.*?)</iframe>@is", "", $string ); 
        $string = preg_replace( "@<style(.*?)</style>@is", "", $string ); 
        $string = preg_replace( "@<(.*?)>@is", "", $string ); 
    }else{
        $string='';
    }
    return $string;
}

function  sendWxTemplateMessages($tempKey,$dataArr)
{
    require_once EXTEND_PATH.'/wxpayapi/lib/WxPay.Config.php';
    require_once EXTEND_PATH.'/wxpayapi/lib/wxSendMsg.php';
    $templatBrige=new wxSendMsg();
    $templatBrige->sendTempMsg($tempKey,$dataArr);
}

/**
 * 得到新订单号
 * @return  string
 */
function getOrderSn()
{
    /* 选择一个随机的方案 */
    mt_srand((double) microtime() * 1000000);
    return date('YmdHis') . str_pad(mt_rand(1, 99999), 5, '0', STR_PAD_LEFT);
}

/**
 * 随机字符串
* @param $length
* @return string
*/
function getRandomString($len, $chars=null)
{
    if (is_null($chars)){
        $chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
    }

    $str = '';
    $num = strlen($chars)-1;
    for ( $i = 0; $i < $len; $i++)
    {
        $str .= $chars[mt_rand(0, $num)];
    }
    
    return $str;
}

function card_query($img)
{
    $gambar = file_get_contents($img);
    $base64 = urlencode(base64_encode($gambar));
    $body='image='.$base64;
    $curl = curl_init();
    $apikey = '28d351f44b271c89e6c03fdd5f7f539e';
    $CurTime = (string)time();
    $json = ["engine_type"=>'business_card'];
    $param = base64_encode(utf8_encode(json_encode($json, JSON_UNESCAPED_SLASHES)));

    //验证参数
    $checkSum = md5($apikey.$CurTime.$param);
    curl_setopt_array($curl, array(
        CURLOPT_URL => "http://webapi.xfyun.cn/v1/service/v1/ocr/business_card",
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => "",
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => "POST",
        CURLOPT_POSTFIELDS => "$body",
        CURLOPT_HTTPHEADER => array(
            "Cache-Control: no-cache",
            "Content-Type: application/x-www-form-urlencoded;charset=utf-8",
            "X-Appid: 5ba9f8ed",
            "X-CheckSum: $checkSum",
            "X-CurTime: $CurTime",
            "X-Param: $param"
        ),
    ));

    $response = curl_exec($curl);
    $err = curl_error($curl);
    curl_close($curl);
    if ($err) {
        echo "cURL Error #:" . $err;
        return $err;
    } else {
        return json_decode($response);
    }
}

function httpcopy($url, $file="", $timeout=60) 
{

  $file = empty($file) ? pathinfo($url,PATHINFO_BASENAME) : $file;
  $dir = pathinfo($file,PATHINFO_DIRNAME);
  !is_dir($dir) && @mkdir($dir,0777,true);
  $url = str_replace(" ","%20",$url);
  
  if(function_exists('curl_init')) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
    $temp = curl_exec($ch);
    if(@file_put_contents($file, $temp) && !curl_error($ch)) {
      return $file;
    } else {
      return false;
    }
  } else {
    $opts = array(
      "http"=>array(
      "method"=>"GET",
      "header"=>"",
      "timeout"=>$timeout)
    );
    $context = stream_context_create($opts);
    
    if(@copy($url, $file, $context)) {
      //$http_response_header
      return $file;
    } else {
      return false;
    }
  }
}
/**
 * 使用curl获取远程数据
 * @param  string $url url连接
 * @return string      获取到的数据
 */
function https_request($url = '', $post_data = array())
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
    $output = curl_exec($ch);//运行curl
    curl_close($ch);  
         
    return $output;
}

/**
 * @param $url
 * @param null $data
 * @param bool $json
 * @return array|mixed
 * 模拟post请求
 */
function request_post($url, $data = NULL, $json = false)
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

/**
* 计算两点地理坐标之间的距离
* @param  Decimal $longitude1 起点经度
* @param  Decimal $latitude1  起点纬度
* @param  Decimal $longitude2 终点经度
* @param  Decimal $latitude2  终点纬度
* @param  Int     $unit       单位 1:米 2:公里
* @param  Int     $decimal    精度 保留小数位数
* @return Decimal
*/
 function getDistance($longitude1, $latitude1, $longitude2, $latitude2, $unit=1, $decimal=0)
 {
        
	$EARTH_RADIUS = 6370.996; // 地球半径系数
    $PI = 3.1415926;
        
    $radLat1 = $latitude1 * $PI / 180.0;
    $radLat2 = $latitude2 * $PI / 180.0;
        
    $radLng1 = $longitude1 * $PI / 180.0;
    $radLng2 = $longitude2 * $PI /180.0;
        
    $a = $radLat1 - $radLat2;
    $b = $radLng1 - $radLng2;
        
    $distance = 2 * asin(sqrt(pow(sin($a/2),2) + cos($radLat1) * cos($radLat2) * pow(sin($b/2),2)));
    $distance = $distance * $EARTH_RADIUS * 1000;
        
    if($unit==2){
        $distance = $distance / 1000;
    }
        
    $range = round($distance, $decimal);
    return $range;
}

function get_current_host_url()
{
    $current_url = 'http://';
    if(isset($_SERVER['HTTPS'])&&$_SERVER['HTTPS']=='on'){
        $current_url = 'https://';
    }
    if($_SERVER['SERVER_PORT']!='80'){
        $current_url.= $_SERVER['SERVER_NAME'].':'.$_SERVER['SERVER_PORT'];
    }else{
        $current_url.= $_SERVER['SERVER_NAME'];
    }
    
    return $current_url;
}

//判断是否会员状态
function is_vip()
{
    $uinfo = session("user_info");
    //防止缓存
    $user_info = Db::table('s_user_detail')->where(['user_id'=>$uinfo['id']])->field('user_id,vip_group_id,vip_over_time')->find();
    $user_id = $user_info['user_id'];
    $group_id = $user_info['vip_group_id'];
    $over_time = $user_info['vip_over_time'];

  	if($group_id ==0 || $over_time <= time()){
  		$this->redirect('Home/Capital/recharges', ['user_id' => $user_id]);
        exit;
    }
}

//微信用户访问统计
function user_stats($user_id=0,$target_id=0)
{	    
	//需要微信浏览器打开
	$user_agent = $_SERVER['HTTP_USER_AGENT'];
	if(!strpos($user_agent, 'MicroMessenger') || !is_numeric($user_id)) return ;

	$openid = $nickname = $headimgurl = '';
	$uinfo = session("user_info")?session("user_info"):'';
   	if ($uinfo) {
   		$openid = isset($uinfo['openid']) ? $uinfo['openid'] : '';
		$nickname = $uinfo['nick_name'];
		$headimgurl = $uinfo['headimg'];
   	}
    
   	$ctr_name = CONTROLLER_NAME; 
	$act_name = ACTION_NAME;
	$share_openid = session("share_openid") ? session("share_openid") : '' ;
	if (!$share_openid && in_array($act_name,array('previewArticle'))) {
        $url = get_current_url();
    	header('Location:/getuserinfo.php?target_url='.urlencode($url));
    	exit;
    }elseif($share_openid){   
    	$openid = $share_openid;
		$nickname = session("nickname"); 
		$headimgurl = session("headimgurl"); 
    }
    
	$data = array('control' => $ctr_name,'action'=>$act_name,'nick_name'=>$nickname,'uid'=>$user_id,'target_id'=>$target_id,
        'openid'=>$openid,'avatar'=>$headimgurl,'param'=>json_encode($_REQUEST),'add_time'=>time());    
	Db::table('s_user_stats')->insert($data);
}

function get_current_Host(){

    $current_url='http://';
    if(isset($_SERVER['HTTPS'])&&$_SERVER['HTTPS']=='on'){
        $current_url='https://';
    }

    if($_SERVER['SERVER_PORT']!='80'){
        $current_url.=$_SERVER['SERVER_NAME'].':'.$_SERVER['SERVER_PORT'];
    }else{
        $current_url.=$_SERVER['SERVER_NAME'];
    }

    return $current_url;
}

/**
 * 获取客户端IP地址
 * @param integer $type 返回类型 0 返回IP地址 1 返回IPV4地址数字
 * @return mixed
 */
function get_client_ip($type = 0) {
	$type       =  $type ? 1 : 0;
    static $ip  =   NULL;
    if ($ip !== NULL) return $ip[$type];
    if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        $arr    =   explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
        $pos    =   array_search('unknown',$arr);
        if(false !== $pos) unset($arr[$pos]);
        $ip     =   trim($arr[0]);
    }elseif (isset($_SERVER['HTTP_CLIENT_IP'])) {
        $ip     =   $_SERVER['HTTP_CLIENT_IP'];
    }elseif (isset($_SERVER['REMOTE_ADDR'])) {
        $ip     =   $_SERVER['REMOTE_ADDR'];
    }
    // IP地址合法验证
    $long = sprintf("%u",ip2long($ip));
    $ip   = $long ? array($ip, $long) : array('0.0.0.0', 0);
    return $ip[$type];
}

//$begin_time 开始时间戳
//$end_time 结束时间戳
function timediff($begin_time,$end_time)
{
	if($begin_time < $end_time){
		$starttime = $begin_time;
		$endtime = $end_time;
	}else{
		$starttime = $end_time;
		$endtime = $begin_time;
	}
	
	//计算天数
	$timediff = $endtime-$starttime;
	$days = intval($timediff/86400);
	//计算小时数
	$remain = $timediff%86400;
	$hours = intval($remain/3600);
	//计算分钟数
	$remain = $remain%3600;
	$mins = intval($remain/60);
	//计算秒数
	$secs = $remain%60;
	$res = array("day" => $days,"hour" => $hours,"min" => $mins,"sec" => $secs);
	return $res;
}