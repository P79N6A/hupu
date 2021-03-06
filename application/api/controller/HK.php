<?php
namespace app\api\controller;

use think\Controller;
use think\facade\Config;
use think\Db;

class HK extends Controller
 {
    const __OK__ = '0'; //请求成功
    const __ERROR__ = '1'; //参数错误
    const __ERROR1__ = '2'; //没有绑定
    const __ERROR2__ = '3';//数据库错误
    private $array_return = array("ResultType"=>1,"Message"=>"success","AppendData"=>"");
    
    /**
     * 得到百度token
     */
	public function get_baidu_token()
	{
		$url = 'https://aip.baidubce.com/oauth/2.0/token';
	    $post_data['grant_type']       = 'client_credentials';
	    $post_data['client_id']      = '9V1hzM4F25TFcpGwbjwEVHtd';
	    $post_data['client_secret'] = 'RcHzQBXm8QTLP0oLnyWEEYx9I8KY7Um2';
	   
	    $res = https_request($url, $post_data);
	    $this->array_return['ResultType'] = self::__OK__;
	    $this->array_return['Message'] = "操作成功";
	    $this->array_return['AppendData'] = json_decode($res,true);
	    return json($this->array_return);
	}
	
    public function baidu_mp_post()
    {
        $res = $this->request->post();
        $token = Db::table("s_api_yml")->where(array("id"=>1))->column("baidutoken");
        $url = 'https://aip.baidubce.com/rest/2.0/ocr/v1/business_card?access_token='.$token;
        $image_data  = $res["image"];
        $bodys = array(
            "image" =>  $image_data
        );
        $ids =json_decode(https_request($url, $bodys),true);
        return json($ids);
    }

    /**
     * 身份证上传并且识别
     */
    public function idcard_upload_identification()
    {
    	$res = $this->request->post();
	    $id_card_side = $res['id_card_side']==null?"front":$res['id_card_side'];
	    $token = '24.a9e40f24a363cabc4724bb9e9cd4e1cc.2592000.1542370808.282335-14464974';
	    $url = 'https://aip.baidubce.com/rest/2.0/ocr/v1/idcard?access_token=' . $token;
	    $end_name = trim(strrchr($_FILES['img']['name'], '.'),'.');
	    if($end_name == 'jpeg'){
	   		$save_name = "hk".'/'.date('Y-m-d').'/'.time().rand(1,9999).'.jpg';
	    }else{
	        $save_name = "hk".'/'.date('Y-m-d').'/'.time().rand(1,9999).'.'.$end_name;
	    }
      	try{
      	  $conf = config('OSS_IMAGE');
          $ossClient = new \OSS\OssClient($conf['accessKeyId'], $conf['accessKeySecret'], $conf['endpoint']);
          $image_file =  $ossClient->uploadFile($conf['bucket'],$save_name,$_FILES['img']['tmp_name']);
          $image_data = fread(fopen($_FILES['img']['tmp_name'], 'r'), filesize($_FILES['img']['tmp_name']));

          $bodys = array(
              "image" =>  base64_encode($image_data),
              "detect_direction"=>true,
              "id_card_side"=>$id_card_side
          );
          $ids =json_decode(https_request($url, $bodys),true);
		//normal-识别正常
		//reversed_side-身份证正反面颠倒
		//non_idcard-上传的图片中不包含身份证
		//blurred-身份证模糊
		//other_type_card-其他类型证照
		//over_exposure-身份证关键字段反光或过曝
		//over_dark-身份证欠曝（亮度过低）
		//unknown-未知状态
          if($ids["image_status"]=="unknown" || $ids["image_status"]=="normal" ){
             // substr(string,start,length)
                  $birth=$ids["words_result"]["出生"]["words"];
              //20181201
              $aaa=$birth;
              $birth= substr($aaa,0,4)."-".substr($aaa,4,2)."-".substr($aaa,6,2);
              $age=$this->diffBetweenTwoDays($birth,date("Y-m-d",time()));
              $retrunids=array("image_atatus"=>$ids["image_status"],
                  "adress"=>$ids["words_result"]["住址"]["words"],
                  "birth"=>$birth,
                  "id"=>$ids["words_result"]["公民身份号码"]["words"],
                  "sex"=>$ids["words_result"]["性别"]["words"],
                  "nation"=>$ids["words_result"]["民族"]["words"],
                  "name"=>$ids["words_result"]["姓名"]["words"]
                  ,"age"=>intval($age/365));
          }else if($ids["image_status"]=="reversed_side"){
              $retrunids=array("image_atatus"=>$ids["image_status"],
                  "info"=>"身份证正反面颠倒");
          }else if($ids["image_status"]=="non_idcard"){
              $retrunids=array("image_atatus"=>$ids["image_status"],
                  "info"=>"上传的图片中不包含身份证");
          }else if($ids["image_status"]=="blurred"){
              $retrunids=array("image_atatus"=>$ids["image_status"],
                  "info"=>"身份证模糊");
          }else if($ids["image_status"]=="other_type_card"){
              $retrunids=array("image_atatus"=>$ids["image_status"],
                  "info"=>"身份证关键字段反光或过曝");
          }else if($ids["image_status"]=="over_dark"){
              $retrunids=array("image_atatus"=>$ids["image_status"],
                  "info"=>"身份证欠曝（亮度过低）");
          }

          if ($image_file['status'] == true){
              $shift_url = str_replace($conf['oss_url'],$conf['cdn_usl'],$image_file['url']);
              $result_url =  $shift_url."@!protected";
              $add['img'] = $result_url;
          }else{
              // 上传错误提示错误信息
              $this->array_return['ResultType'] = self::__ERROR2__;
              $this->array_return['Message'] = "上传操作失败";
          }
          $add['ids']=$retrunids;
          $this->array_return['AppendData']= $add;
      } catch(OssException $e) {
          $this->array_return['ResultType'] = self::__ERROR2__;
          $this->array_return['Message'] = "上传操作失败";
      }
      
    	return json($this->array_return);
 	}

    /**
     * 测试
     */
    public function test_idcard_upload_identification()
    {
         $res = $this->request->post();
         $id_card_side = $res['id_card_side']==null?"front":$res['id_card_side'];
         $token = '24.a9e40f24a363cabc4724bb9e9cd4e1cc.2592000.1542370808.282335-14464974';
         $url = 'https://aip.baidubce.com/rest/2.0/ocr/v1/idcard?access_token=' . $token;
         $img = file_get_contents('http://htest.yxm360.com/upload/commodity/20185/13170908864604314035.jpeg');
          $img = base64_encode($img);
          $bodys = array(
              "image" => $img,
              "detect_direction"=>true,
              "id_card_side"=>$id_card_side
          );
          $res =json_decode(https_request($url, $bodys),true);
          $this->array_return['ResultType'] = self::__OK__;
          $this->array_return['Message'] = "操作成功";
          $this->array_return['AppendData'] =$res;
		// array("image_atatus"=>$res["image_status"],
		//"adress"=>$res["words_result"]["住址"]["words"],
		//"birth"=>$res["words_result"]["出生"]["words"],
		//"id"=>$res["words_result"]["公民身份号码"]["words"],
		//"sex"=>$res["words_result"]["性别"]["words"],
		//"nation"=>$res["words_result"]["民族"]["words"]);
          return json($this->array_return);
     }
     
    /**
     * 求两个日期之间相差的天数
     */
    function diffBetweenTwoDays ($day1, $day2)
    {
        $second1 = strtotime($day1);
        $second2 = strtotime($day2);

        if ($second1 < $second2) {
            $tmp = $second2;
            $second2 = $second1;
            $second1 = $tmp;
        }
        return ($second1 - $second2) / 86400;
    }


    public function  user_login_to()
    {
        $res = $this->request->post();
        $phone = $res['phone'];
        $usre = Db::table("s_hk_user")->where(array("user_phone"=>$phone))->find();
        $openid = $res["yxmuseropenid"];
        if(empty($usre)){
            $data["uuid"] = getRandomString(10);
            $data["createTime"]= date('Y-m-d H:i:s');
            $data["start"]= -1;
            $data["user_phone"]=$phone;
            $data["yxmuseropenid"] = $res["yxmuseropenid"];

            $a = Db::table("s_hk_user")->insert($data);
            if($a){
                $this->array_return['ResultType'] =0;
                $this->array_return['Message'] = "用户信息不完善";
                $this->array_return['AppendData'] =array("yxmuseropenid"=>$data["yxmuseropenid"],"uuid"=>$data["uuid"],"start"=>$data["start"]) ;
            }
        }else if($usre["start"]==-1){
            $c = Db::table("s_hk_user")->where(array("user_phone"=>$res["phone"]))->update(array("yxmuseropenid"=>$res["yxmuseropenid"]));
            $this->array_return['ResultType'] =0;
            $this->array_return['Message'] = "用户信息不完善";
            $this->array_return['AppendData'] =array("yxmuseropenid"=>$openid,"uuid"=>$usre["uuid"],"start"=>$usre["start"])  ;
        }else if($usre["start"]==1){
            $c = Db::table("s_hk_user")->where(array("user_phone"=>$res["phone"]))->update(array("yxmuseropenid"=>$res["yxmuseropenid"]));
            $this->array_return['ResultType'] =1;
            $this->array_return['Message'] = "用户未缴费";
            $this->array_return['AppendData'] = array("yxmuseropenid"=>$openid,"uuid"=>$usre["uuid"],"start"=>$usre["start"]) ;
        }else if($usre["start"]==2){
            $c = Db::table("s_hk_user")->where(array("user_phone"=>$res["phone"]))->update(array("yxmuseropenid"=>$res["yxmuseropenid"]));
            $this->array_return['ResultType'] =2;
            $this->array_return['Message'] = "用户已缴费";
            $this->array_return['AppendData'] =array("yxmuseropenid"=>$openid,"uuid"=>$usre["uuid"],"start"=>$usre["start"])  ;
        }
        
        return json($this->array_return);
    }
    
    public function myoder()
    {
        $res = $this->request->post();
        $n = $res['n'] ? $res['n'] : 1;
        $userphone = $res["userphone"];
        $superior_phone = $res["superior_phone"];
        $map = array();
        if(!empty($userphone)){
            $map["a.user_phone"]=$userphone;
        }
        if(!empty($superior_phone)){
            $map["a.superior_phone"]=$superior_phone;
        }
        $pageSize=10;
        $m = Db::table("s_hk_user");
        $pageTotal= $m->alias("a")->leftJoin("s_k_company d","a.company_id=d.cid")->where($map)->count();
        $list=$m->alias("a")->leftJoin("s_k_company d","a.company_id=d.cid")->field("a.*,d.cid as ccid,d.cname as ccname")
            ->where($map)->order('a.id desc')->limit(($n-1)* $pageSize,$pageSize)->select();

        $datalist=array("list"=>$list,"pageTotal"=>$pageTotal,"pageSize"=>$pageSize,"pageNum"=>$n);
        if ($datalist) {
            $this->array_return['ResultType'] = self::__OK__;
            $this->array_return['Message'] = "操作成功";
            $this->array_return['AppendData'] = $datalist;
        } else {
            $this->array_return['ResultType'] = self::__ERROR2__;
            $this->array_return['Message'] = "操作失败";
        }
        
        return json($this->array_return);
    }
    
    /**
     * 第一个页面提交代码
     *
     */
     public function hk_user_one()
     {
         $res = $this->request->post();
         if(empty($res["user_phone"]) || empty($res["yxmuseropenid"])  ){
             $this->array_return['ResultType'] =  self::__ERROR2__;
             $this->array_return['Message'] = "非法访问,关键数据无";
             return json($this->array_return);
         }
         
         $age=$this->diffBetweenTwoDays($res["id_dateofbirth"],date("Y-m-d",time()));
         if($res["age"]!=intval($age/365)){
             $this->array_return['ResultType'] =  self::__ERROR2__;
             $this->array_return['Message'] = "非法访问";
             return json($this->array_return);
         }
         if($res["age"]>65){
             $this->array_return['ResultType'] = self::__ERROR2__;
             $this->array_return['Message'] = "此次活动用户年龄不得大于65";
             return json($this->array_return);
         }
       //  500 200 300 500
         //广东省罗定市罗镜镇水摆村委唐屋12号 ===false
         if(strpos($res["id_adress"], "广东省")===false){
             if($res["age"]>=55 && $res["age"]<=65){
                 $res["order_type"]=2;
                 $res["order_pri"]=500+200+300;
             }else if($res["age"]>=22 && $res["age"]<55){
                 $res["order_type"]=1;
                 $res["order_pri"]=500+200;
             }else{
                 $res["order_type"]=6;
                 $res["order_pri"]= 500+200+1500;
             }
         }else{
             if($res["age"]>=55 && $res["age"]<=65){
                 $res["order_type"]=4;
                 $res["order_pri"]= 500+200+300+500;
             }else if($res["age"]>=22 && $res["age"]<55){
                 $res["order_type"]=3;
                 $res["order_pri"]= 500+200+500;
             }else{
                 $res["order_type"]=5;
                 $res["order_pri"]= 500+200+500+1500;
             }
         }
         
         $res["start"]= 1;
         if(empty($res["uuid"])){
             $res["uuid"] = getRandomString(10);
         }

         $res["createTime"]= date('Y-m-d H:i:s');
         $co = Db::table("s_hk_user")->where(array("user_phone"=>  $res["user_phone"]))->count();
         if(intval($co)>0){
             $m = Db::table("s_hk_user")->where(array("user_phone"=>  $res["user_phone"],"start"=>array("in","-1,1")))->update($res);
         }else{
             $m = Db::table("s_hk_user")->insert($res);
         }
         if ($m) {
             $this->array_return['ResultType'] = self::__OK__;
             $this->array_return['Message'] = "操作成功";
             $this->array_return['AppendData'] = array("yxmuseropenid"=>$res["yxmuseropenid"],"uuid"=>$res["uuid"]);
         } else {
             $this->array_return['ResultType'] = self::__ERROR2__;
             $this->array_return['Message'] = "操作失败";
         }
         
         return json($this->array_return);
     }

     public function hk_user_two()
     {
         $res = $this->request->post();
         if(empty($res["uuid"]))
         {
             $this->array_return['ResultType'] = 0;
             $this->array_return['Message'] = "非法访问";
             return json($this->array_return);
         }
         
         $r = Db::table("s_hk_user")->where(array("uuid"=>$res["uuid"]))->update($res);
         if ($r) {
             $this->array_return['ResultType'] = self::__OK__;
             $this->array_return['Message'] = "操作成功";
             $this->array_return['AppendData'] = $r;
         } else {
             $this->array_return['ResultType'] = self::__ERROR2__;
             $this->array_return['Message'] = "操作失败";
         }
         
         return json($this->array_return);
     }

    /**
     * 得到促使数
     */
     public function init_hk_user_two()
     {
         $res = $this->request->post();
         $openid = $res["yxmuseropenid"];
         $uuid = $res["uuid"];
         if(empty($uuid)|| empty($openid)){
             $this->array_return['ResultType'] = 0;
             $this->array_return['Message'] = "非法访问";
             return json($this->array_return);
         }
         $user = Db::table("s_hk_user")->where(array("uuid"=>$uuid))->find();
         if(empty($user)){
             $this->array_return['ResultType'] = 0;
             $this->array_return['Message'] = "非法访问";
             return json($this->array_return);
         }
         
         $data = array("uuid"=>$user["uuid"],"order_type"=>$user["order_type"],"order_pri"=>$user["order_pri"],"yxmuseropenid"=>$user["yxmuseropenid"]);
             
         $this->array_return['ResultType'] = self::__OK__;
         $this->array_return['Message'] = "操作成功";
         $this->array_return['AppendData'] = $data;    
         return json($this->array_return);
    }

    public  function i_u_hk_user()
    {
        $res = $this->request->post();
        $id = $res["id"];
        $age = $this->diffBetweenTwoDays($res["id_dateofbirth"],date("Y-m-d",time()));
        $res["age"]=intval($age/365);
        if($res["age"]>65)
        {
            $this->array_return['ResultType'] = self::__ERROR2__;
            $this->array_return['Message'] = "此次活动用户年龄不得大于65".$res["age"];
            return json($this->array_return);
        }
        //  500 200 300 500
        //广东省罗定市罗镜镇水摆村委唐屋12号 ===false
        if(strpos($res["id_adress"], "广东省")===false){
            if($res["age"]>=55 && $res["age"]<=65){
                $res["order_type"]=2;
                $res["order_pri"]=500+200+300;
            }else if($res["age"]>=22 && $res["age"]<55){
                $res["order_type"]=1;
                $res["order_pri"]=500+200;
            }else{
                $res["order_type"]=6;
                $res["order_pri"]= 500+200+1500;
            }
        }else{
            if($res["age"]>=55 && $res["age"]<=65){
                $res["order_type"]=4;
                $res["order_pri"]= 500+200+300+500;
            }else if($res["age"]>=22 && $res["age"]<55){
                $res["order_type"]=3;
                $res["order_pri"]= 500+200+500;
            }else{
                $res["order_type"]=5;
                $res["order_pri"]= 500+200+500+1500;
            }
        }
        if(empty($id)){
            if(empty($res["uuid"])){
                $res["uuid"] = getRandomString(10);
                $res["createTime"]= date('Y-m-d H:i:s');
            }
            $res["createTime"]= date('Y-m-d H:i:s');
            $res["isok"]= 2;
            $co = Db::table("s_hk_user")->where(array("user_phone"=>  $res["user_phone"]))->count();
            if(intval($co)>0){
                $m = Db::table("s_hk_user")->where(array("user_phone"=>  $res["user_phone"],"start"=>array("in","-1,1")))->update($res);
            }else{
                $m = Db::table("s_hk_user")->insert($res);
            }
            if ($m) {
                $this->array_return['ResultType'] = self::__OK__;
                $this->array_return['Message'] = "操作成功";
                $this->array_return['AppendData'] = "";
            } else {
                $this->array_return['ResultType'] = self::__ERROR2__;
                $this->array_return['Message'] = "操作失败";
            }
            
            return json($this->array_return);
        }

        $m = Db::table("s_hk_user")->where(array("id"=>$id))->update($res);
        if ($m) {
            $this->array_return['ResultType'] = self::__OK__;
            $this->array_return['Message'] = "操作成功";
            $this->array_return['AppendData'] = $m;
        } else {
            $this->array_return['ResultType'] = self::__ERROR2__;
            $this->array_return['Message'] = "操作失败";
        }
        
        return json($this->array_return);
    }

    /**
     * 删除
     */
    public  function del_hk_user()
    {
        $res = $this->request->post();
        $uuid = $res["uuid"];
        if(empty($uuid)){
            $this->array_return['ResultType'] = self::__ERROR2__;
            $this->array_return['Message'] = "非法访问";
            return json($this->array_return);
        }
        
        $m = Db::table("s_hk_user")->where(array("uuid"=>$uuid))->delete();
        if ($m) {
            $this->array_return['ResultType'] = self::__OK__;
            $this->array_return['Message'] = "操作成功";
            $this->array_return['AppendData'] = $m;
        } else {
            $this->array_return['ResultType'] = self::__ERROR2__;
            $this->array_return['Message'] = "操作失败";
        }
        
        return json($this->array_return);
    }
    
    /**
     * 后台香港查找分页
     */
     public function f_hk_user()
     {
         $res = $this->request->post();
         $n = $res['n'] ? $res['n'] : 1;
         
         $map = array();
         if(!empty($res['id_username'])){
             $map['a.id_username']=array('like','%'.$res['id_username'].'%');
         }
         if(!empty($res['referee_name'])){
             $map['a.referee_name']=array('like','%'.$res['referee_name'].'%');
         }
         if(!empty($res['start'])){
             $map['a.start'] = $res['start'];
         }
         if(!empty($res['referee_name'])){
             $map['a.referee_name']=array('like','%'.$res['su_name'].'%');
         }

         if(!empty($res['commpeid'])){
             $map['a.company_id'] = $res['commpeid'];
         }

         if(!empty($res['isok'])){
             if($res['isok']==2){
                 $map['a.isok'] = $res['2'];
             }else{
                 $map['a.isok'] = array('neq','2');
             }
         }

         $pageSize = 10;
         $m = Db::table("s_hk_user");
         $pageTotal=$m->alias("a")->where($map)->count();

         $list=$m->alias("a")->leftJoin("s_hk_order b","a.order_id=b.id")->field("a.*,b.s_order_no,b.s_order_pay_time")->
         where($map)->order('id desc')->limit(($n-1)* $pageSize,$pageSize)->select();

         $datalist=array("list"=>$list,"pageTotal"=>$pageTotal,"pageSize"=>$pageSize,"pageNum"=>$n);
         if ($datalist) {
             $this->array_return['ResultType'] = self::__OK__;
             $this->array_return['Message'] = "操作成功";
             $this->array_return['AppendData'] = $datalist;
         } else {
             $this->array_return['ResultType'] = self::__ERROR2__;
             $this->array_return['Message'] = "操作失败";
         }
         
         return json($this->array_return);
     }
     
    public function all_mey_excelTo_activity()
    {
        $res = input('get.');
        $s_activity_id = $res['s_activity_id'];
        if(empty($s_activity_id)){
            $s_activity_id = Db::table("s_activity")->max("s_activity_id");
        }

        $map = array();
        if(!empty($res['name'])){
            $map['a.s_username']=array('like',"%".$res['name']."%");
        }
        if(!empty($res['phone'])) {
            $map['a.s_userphone'] = array('like', "%".$res['phone']."%");
        }
        if(!empty($res['s_start']) || $res['s_start']!=-1) {
            $map['a.s_start']  = $res['s_start'];
        }
        if($res['s_start']==-1){
            $map['a.s_start'] =0;
        }
        if(!empty($res['companyid'])) {
            $map['a.companyid']  = $res['companyid'];
        }
        if(!empty($res['s_grade'])) {
            $map['a.s_grade']  = $res['s_grade'];
        }
        $map["a.s_activity_id"]=$s_activity_id;
        $m = Db::table("s_signin_usre");
        $userMod=$m->alias("a")->leftJoin("s_activity_group b","a.s_group=b.s_id")
            ->leftJoin(" s_activity c","a.s_activity_id=c.s_activity_id")->leftJoin("s_k_company d","a.companyid=d.cid")
            ->leftJoin("s_activity_order dd","a.s_userid=dd.s_activity_userid")
            ->field("a.*,b.s_name,c.s_activityname,d.cid as ccid,d.cname as ccname,dd.s_order_pay_time,dd.s_order_price,dd.s_order_no")
            ->where($map)->order('a.s_userid desc')->select();

        $data = array();
        foreach ($userMod as $k=>$goods_info){
            $data[$k]['参会人员名'] = $goods_info['s_username'];
            $data[$k]['参会人员手机号'] = "\t".$goods_info['s_userphone'];
            $data[$k]['参会人员所属公司'] = $goods_info['ccname'];

            if( $goods_info['s_start']==0){
                $data[$k]['参会人员状态'] = "用户已验证身份";
            }else if($goods_info['s_start']==1){
                $data[$k]['参会人员状态'] = "未付费";
            }else if($goods_info['s_start']==2){
                $data[$k]['参会人员状态'] = "已付费";
            }else if($goods_info['s_start']==3){
                $data[$k]['参会人员状态'] = "头像已上传";
            }else if($goods_info['s_start']==4){
                $data[$k]['参会人员状态'] = "二维码已生成";
            }
            $data[$k]['用户等级'] = "\t".$goods_info['s_grade'];
            $data[$k]['用户推荐人的姓名'] = $goods_info['s_referee'];
            $data[$k]['用户付费时间'] = "\t".$goods_info['s_order_pay_time'];
            $data[$k]['用户订单号'] = "\t".$goods_info['s_order_no'];
            $data[$k]['用户所付费用'] = "\t".$goods_info['s_order_price'];
        }
        $headArr=array('参会人员名','参会人员手机号','参会人员所属公司','参会人员状态','用户等级','用户推荐人的姓名','用户付费时间','用户订单号','用户所付费用');
        $filename="年终大会数据导出";
        $this->getExcel($filename,$headArr,$data);
    }

    /**
     *
     */
    public function all_mey_excel()
    {
        $res = input('get.');
        $map = array();
        if(!empty($res['id_username'])){
            $map['a.id_username']=array('like','%'.$res['id_username'].'%');
        }
        if(!empty($res['referee_name'])){
            $map['a.referee_name']=array('like','%'.$res['referee_name'].'%');
        }
        if(!empty($res['start'])){
            $map['a.start'] = $res['start'];
        }
        if(!empty($res['referee_name'])){
            $map['a.referee_name']=array('like','%'.$res['su_name'].'%');
        }

        if(!empty($res['commpeid'])){
            $map['a.company_id'] = $res['commpeid'];
        }
        if(!empty($res['isok'])){
            if($res['isok']==2){
                $map['a.isok'] = $res['2'];
            }else{
                $map['a.isok'] = array('neq','2');
            }
        }

        $m = Db::table("s_hk_user");
        $userMod = $m->alias("a")->leftJoin("s_hk_order b","a.order_id=b.id")->
            join('left join s_k_company c','c.cid = a.company_id')->field("a.*,b.s_order_no,b.s_order_pay_time,c.cname")->
            where($map)->order('b.s_order_pay_time desc,a.id desc')->select();

        $data = array();
        foreach ($userMod as $k=>$goods_info){
            $data[$k]['用户唯一id'] = $goods_info['id'];
            $data[$k]['推荐人姓名'] = $goods_info['referee_name'];
            $data[$k]['姓名'] = $goods_info['id_username'];
            $data[$k]['地址'] = $goods_info['id_adress'];
            $data[$k]['身份证号'] = "\t".$goods_info['id_number'];
            $data[$k]['性别'] = $goods_info['id_sex'];
            $data[$k]['民族'] = $goods_info['id_nation'];
            $data[$k]['出生日期'] = $goods_info['id_dateofbirth'];
            $data[$k]['手机号'] = $goods_info['user_phone'];
            $data[$k]['年龄'] = $goods_info['age'];
            $data[$k]['缴纳费用'] = $goods_info['order_pri'];
            $data[$k]['所属分公司'] = $goods_info['cname'];
            $data[$k]['付费时间'] = $goods_info['s_order_pay_time'];
            $data[$k]['是否线上报名'] = $goods_info['isok']==2?"不是":"是";
        }
        $headArr=array('用户唯一id','推荐人姓名','姓名','地址','身份证号','性别','民族','出生日期','手机号','年龄','缴纳费用','所属分公司','付费时间','是否线上报名');
        $filename="全球移动互联网大会在线报名系统数据";
        $this->getExcel($filename,$headArr,$data);
    }
    
    private  function getExcel($fileName,$headArr,$data)
    {
        //导入PHPExcel类库，因为PHPExcel没有用命名空间，只能inport导入
        require_once EXTEND_PATH.'/PHPExcel/PHPExcel.php';
        $date = date("Y_m_d",time());
        $fileName .= "_{$date}.xls";

        //创建PHPExcel对象，注意，不能少了\
        $objPHPExcel = new \PHPExcel();
        $objProps = $objPHPExcel->getProperties();

        //设置表头
        $key = ord("A");
        foreach($headArr as $v){
            $colum = chr($key);
            $objPHPExcel->setActiveSheetIndex(0) ->setCellValue($colum.'1', $v);
            $objPHPExcel->setActiveSheetIndex(0) ->setCellValue($colum.'1', $v);
            $key += 1;
        }

        $column = 2;
        $objActSheet = $objPHPExcel->getActiveSheet();
        foreach($data as $key => $rows){ //行写入
            $span = ord("A");
            foreach($rows as $keyName=>$value){// 列写入
                $j = chr($span);
                $objActSheet->setCellValue($j.$column, $value);
                $span++;
            }
            $column++;
        }

        $fileName = iconv("utf-8", "gb2312", $fileName);
        //重命名表
        //$objPHPExcel->getActiveSheet()->setTitle('test');
        //设置活动单指数到第一个表,所以Excel打开这是第一个表
        $objPHPExcel->setActiveSheetIndex(0);
        ob_end_clean();//清除缓冲区,避免乱码
        header('Content-Type: application/vnd.ms-excel');
        header("Content-Disposition: attachment;filename=\"$fileName\"");
        header('Cache-Control: max-age=0');

        $objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
        $objWriter->update('php://output'); //文件通过浏览器下载
        exit;
    }

}