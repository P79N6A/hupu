<?php
namespace app\api\controller;
use think\console\Input;

use PHPExcel;
use think\Controller;
use think\Db;
use think\facade\Cache;
use app\common\event\imgUpload  as imgModel;

class ActivityCheck extends Controller 
{
    const __OK__ = '0'; //请求成功
    const __ERROR__ = '1'; //参数错误
    const __ERROR1__ = '2'; //没有绑定
    const __ERROR2__ = '3';//数据库错误
    private $array_return = array("ResultType"=>1,"Message"=>"success","AppendData"=>"");

    public function is_yxm_user()
    {
        $res = $this->request->post();
        $count = Db::table("s_user_detail")->alias('u')->leftJoin('s_member m','m.id = u.member_id')
            ->where(array("m.phone"=>$res["phone"],"u.vip_id"=>array("GT","0")))->count();
            
        if($count>0){
            $this->array_return['ResultType'] = self::__OK__;
            $this->array_return['Message'] = "操作成功";
            $this->array_return['AppendData'] = true;
        }  else {
            $this->array_return['ResultType'] = self::__ERROR2__;
            $this->array_return['Message'] = "操作失败";
        }
        
        return json($this->array_return);
    }

    public  function  del_activity()
    {
        $res = $this->request->post();
        $s_activity_id = $res['$s_activity_id'];
        if(empty($s_activity_id))
        {
            $s_activity_id = Db::table("s_activity")->max("s_activity_id");
        }

        $this->array_return['ResultType'] = self::__ERROR2__;
        $this->array_return['Message'] = "参数缺少";
        if(!empty($res["id"]))
        {
        	$this->array_return['ResultType'] = self::__ERROR2__;
	        $this->array_return['Message'] = "操作失败";
	            
            $Signin_usre  = Db::table("s_signin_usre")->where(array("s_activity_id"=>$s_activity_id,"s_userid"=>$res["id"]))->delete();
            $activity_user = Db::table("s_activity_user")->where(array("s_activityid"=>$res["id"]))->delete();
            if($Signin_usre && $activity_user){
                $this->array_return['ResultType'] = self::__OK__;
                $this->array_return['Message'] = "操作成功";
                $this->array_return['AppendData'] = true;
            }
        }
        
        return json($this->array_return);
    }

    /**
     * 分页查询数据
     */
    public function  activity_list()
    {
        $res = $this->request->post();
        $n = $res['n'] ? $res['n'] : 1;
        $pageSize = 10;
        $m = Db::table("s_activity");
        $pageTotal = $m->count();
        $list = $m->limit(($n-1)* $pageSize,$pageSize)->select();

        $group = Db::table("s_activity_group");
        foreach ($list as $k =>$v){
            $list[$k]['group']=$group->where(array('s_activityid'=>$v['s_activity_id']))->select();
        }
        
        $this->array_return['ResultType'] = self::__ERROR2__;
        $this->array_return['Message'] = "操作失败";
        $datalist=array("list"=>$list,"pageTotal"=>$pageTotal,"pageSize"=>$pageSize,"pageNum"=>$n);
        if ($datalist) {
            $this->array_return['ResultType'] = self::__OK__;
            $this->array_return['Message'] = "操作成功";
            $this->array_return['AppendData'] = $datalist;
        }
        
        return json($this->array_return);
    }

    public function a_group_del()
    {
        $id = input('post.id',0);
        if (!$id) {
            $this->array_return['ResultType'] = self::__ERROR2__;
            $this->array_return['Message'] = "参数缺失";
            return json($this->array_return);
        }
        
        $this->array_return['ResultType'] = self::__ERROR2__;
        $this->array_return['Message'] = "操作失败";
        $returnRes = Db::table("s_activity_group")->where(array('s_id'=>$id))->delete();
        if ($returnRes) {
        	$this->array_return['ResultType'] = self::__OK__;
            $this->array_return['Message'] = "操作成功";
            $this->array_return['AppendData'] = $returnRes;
        } 
        
        return json($this->array_return);
    }
    
    /**
     * 增加或修改活动
     */
    public  function  i_and_u_activity()
    {
        $res = $this->request->post();
        unset($res['group']);
        if(!empty($res['s_activity_id'])){
            $returnRes = Db::table("s_activity")->where(array('s_activity_id'=>$res['s_activity_id']))->update($res);
        }else{
            $returnRes = Db::table("s_activity")->data($res)->insert();
        }
        if ($returnRes) {
            $this->array_return['ResultType'] = self::__OK__;
            $this->array_return['Message'] = "操作成功";
            $this->array_return['AppendData'] = $returnRes;
        } else {
            $this->array_return['ResultType'] = self::__ERROR2__;
            $this->array_return['Message'] = "操作失败";
        }
        
        return json($this->array_return);
    }
    
    /**
     * 增加或修改团队
     *
     */
    public  function  i_and_u_group()
    {
        $res = $this->request->post();
        if(!empty($res['s_id'])){
            $returnRes = Db::table('s_activity_group')->update($res);
        }else{
            $returnRes = Db::table("s_activity_group")->data($res)->insert();
        }
        if ($returnRes) {
            $this->array_return['ResultType'] = self::__OK__;
            $this->array_return['Message'] = "操作成功";
            $this->array_return['AppendData'] = $returnRes;
	    } else {
	        $this->array_return['ResultType'] = self::__ERROR2__;
	        $this->array_return['Message'] = "操作失败";
	    }
       
       return json($this->array_return);
    }

    /**
     * 分页得到活动图片
     */
	public  function  getactivity_img_list()
	{
	    $res = $this->request->post();
	    $n = $res['n'] ? $res['n'] : 1;
	    if(empty($res["s_vact_id"])){
	        $res["s_vact_id"] = Db::table("s_activity")->max("s_activity_id");
	    }
	    $map = array();
	    $map['s_vact_id']=array('in',$res["s_vact_id"]);
	    $pageSize=5;
	    $m = Db::table("s_activity_img");
	    $pageTotal= $m->where($map)->count();
	    $list=$m->where($map)->order('s_id desc')->limit(($n-1)* $pageSize,$pageSize)->select();
	
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
     * 得到当前活动图片的imglist
     */
	public function  get_maxactivity_imglist()
	{
	    $res = $this->request->post();
	    if(empty($res["s_vact_id"])){
	        $res["s_vact_id"] = Db::table("s_activity")->max("s_activity_id");
	    }
	    $imgobj = Db::table("s_activity_img");
	    $imglist=$imgobj->where(array("s_vact_id", $res["s_vact_id"]))->select();
	    if ($imglist) {
	        $this->array_return['ResultType'] = self::__OK__;
	        $this->array_return['Message'] = "操作成功";
	        $this->array_return['AppendData'] = $imglist;
	    } else {
	        $this->array_return['ResultType'] = self::__ERROR2__;
	        $this->array_return['Message'] = "操作失败";
	    }
	    
	    return json($this->array_return);
	}

    /**
     * 插入活动的img
     */
	public function i_and_u_activity_img()
	{
	    $res = $this->request->post();
	    $imgobj = Db::table("s_activity_img");
	    if(!empty($res["s_id"])){
	        $retm=$imgobj->where(array("s_id"=>$res["s_id"]))->update($res);
	    }else{
	        $retm=$imgobj->data($res)->insert();
	    }
	    if ($retm) {
	        $this->array_return['ResultType'] = self::__OK__;
	        $this->array_return['Message'] = "操作成功";
	        $this->array_return['AppendData'] = $retm;
	    } else {
	        $this->array_return['ResultType'] = self::__ERROR2__;
	        $this->array_return['Message'] = "操作失败";
	    }
	    
	    return json($this->array_return);
	}
	
    /**
     * 分页查询数据
     *
     */
	public  function  signin_usre_list()
	{
	    $res = $this->request->post();
	    $n = $res['n'] ? $res['n'] : 1;
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
	    if(!empty($res['s_start']) && $res['s_start']!=-1) {
	        $map['a.s_start']  = $res['s_start'];
	    }
	    if($res['s_start']==-1){
	        $map['a.s_start'] =0;
	    }
	    if(!empty($res['companyid'])) {
	        $map['a.companyid']  = $res['companyid'];
	    }
	    
	    $map["a.s_activity_id"] = $s_activity_id;
	    $pageSize = 10;
	    $m = Db::table("s_signin_usre");
	    $pageTotal= $m->alias("a")->leftJoin("s_activity_group b","a.s_group=b.s_id")
	        ->leftJoin("s_activity c","a.s_activity_id=c.s_activity_id")->leftJoin("s_k_company d","a.companyid=d.cid")
	        ->leftJoin("s_activity_order dd","a.s_userid=dd.s_activity_userid")->where($map)->count();
	    $list=$m->alias("a")->leftJoin("s_activity_group b","a.s_group=b.s_id")
	        ->leftJoin(" s_activity c","a.s_activity_id=c.s_activity_id")->leftJoin("s_k_company d","a.companyid=d.cid")
	        ->leftJoin("s_activity_order dd","a.s_userid=dd.s_activity_userid")
	        ->field("a.*,b.s_name,c.s_activityname,d.cid as ccid,d.cname as ccname,dd.s_order_pay_time,dd.s_order_price,dd.s_order_no")
	        ->where($map)->order('a.s_userid desc')->limit(($n-1)* $pageSize,$pageSize)->select();
	
	   $group = Db::table("s_activity_user");
	   foreach ($list as $k =>$v){
	   		$list[$k]['activity_user']=$group->where(array('s_userid'=>$v['s_userid']))->select();
	   }
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
	
    public  function  is_signin_usre_list()
    {
        $res = $this->request->post();
        $s_useruuid = $res['useruuid'];
        $s_activity_id = $res['s_activity_id'];
        if(empty($s_activity_id)){
            $s_activity_id = Db::table("s_activity")->order('s_activity_id desc')->column("s_activity_id");
        }
        
        $m = Db::table("s_signin_usre");
        $list=$m->alias("a")->leftJoin("s_activity_group b","a.s_group=b.s_id")->leftJoin(" s_activity c","a.s_activity_id=c.s_activity_id")
            ->field("a.*,b.s_name,c.s_activityname")->where(array("a.s_useruuid"=>$s_useruuid))->find();
        
        $group = Db::table("s_activity_user");
        $list['activity_user'] = $group->where(array('s_userid'=>$list['s_userid']))->select();
        if ($list) {
            $this->array_return['ResultType'] = self::__OK__;
            $this->array_return['Message'] = "操作成功";
            $this->array_return['AppendData'] = $list;
        } else {
            $this->array_return['ResultType'] = self::__ERROR2__;
            $this->array_return['Message'] = "操作失败";
        }
        
        return json($this->array_return);
    }
    
    /**
     * 增加签到用户
     */
	public function insert_signin_usre()
	{
	    $res = $this->request->post();
	    if(!empty($res["s_userid"]))
	    {
	        //修改签到用户
	        //判断是否有音频
	        if(!empty($res['s_vbaiduurl'])){
	            $res['s_vurl'] = $res['s_vbaiduurl'];
	        }else{
	            $res['s_vbaiduurl']=$this-> TextToAudio2($res['tex'],$res['spd'],$res['pit'],$res['vol'],$res['per']);
	            $res['s_vurl'] = $res['s_vbaiduurl'];
	        }

	        $returnRes = Db::table("s_signin_usre")-> save($res);
	        if ($returnRes) {
	            $this->array_return['ResultType'] = self::__OK__;
	            $this->array_return['Message'] = "操作成功1";
	            $this->array_return['AppendData'] = $returnRes;
	        } else {
	            $this->array_return['ResultType'] = self::__ERROR2__;
	            $this->array_return['Message'] = "操作失败";
	        }
	        
	        return json($this->array_return);
	    }
	    
	    //添加签到用户
	    $s_activity_id = $res["s_activity_id"];
	    $activityObj = Db::table("s_activity")->field("s_activitydate")->where(array("s_activity_id"=>$s_activity_id))->find();
	    $s_activitydate=(int)$activityObj["s_activitydate"];
	    $res["s_useruuid"]= getRandomString(10);
	    if(!empty($res['s_vbaiduurl'])){
	        $res['s_vurl'] = $res['s_vbaiduurl'];
	    }else{
	        $res['s_vbaiduurl']=$this-> TextToAudio2($res['tex'],$res['spd'],$res['pit'],$res['vol'],$res['per']);
	        $res['s_vurl'] = $res['s_vbaiduurl'];
	    }
	    
	    $returnRes = Db::table("s_signin_usre")->insert($res);
	    $userid = $returnRes;
	    if(!empty($returnRes)){
	        for($i=0;$i<$s_activitydate;$i++){
	          $dataList[$i]=array(
	              's_start' => '1',
	              's_userid' =>$userid,
	              's_activityid' => $s_activity_id,
	              's_dis' => '批量添加'
	          );
	       }
	    	Db::table("s_activity_user")->addAll($dataList);
	    }
	    if ($returnRes) {
	        $this->array_return['ResultType'] = self::__OK__;
	        $this->array_return['Message'] = "操作成功";
	        $this->array_return['AppendData'] = $returnRes;
	    } else {
	        $this->array_return['ResultType'] = self::__ERROR2__;
	        $this->array_return['Message'] = "操作失败";
	    }
	    
	    return json($this->array_return);
	}

	public function error_supplement()
	{
	    //得到当前的最大的s_activity_id
	    $s_activity_id = Db::table("s_activity")->max("s_activity_id");
	    $useridList = Db::table("s_signin_usre")->where(array("s_activity_id"=>$s_activity_id))->field("s_userid")->select();
	    for($i=0;$i<count($useridList);$i++) 
	    {     
	    	$dataList[$i] = array(
	    		's_start' => '1',
	    		's_userid' => $useridList[$i]["s_userid"],   
	    		's_activityid' => $s_activity_id,  
	    		's_dis' => '批量添加'
	         );
	    }
	    
	    $ret = Db::table("s_activity_user")->addAll($dataList);
	    if ($ret) {
	        $this->array_return['ResultType'] = self::__OK__;
	        $this->array_return['Message'] = "操作成功";
	        $this->array_return['AppendData'] = $ret;
	    } else {
	        $this->array_return['ResultType'] = self::__ERROR2__;
	        $this->array_return['Message'] = "操作失败";
	    }
	    
	    return json($this->array_return);
	}


	public  function  init_data_activity_user()
	{
	    $activityList = Db::table("s_activity")->field("s_activityname as label,s_activity_id as value")->order('s_activity_id desc')->select();
	    $groupList = Db::table("s_activity_group")->field("s_name as label,s_id as value")
	        ->where(array("s_activityid"=>$activityList[0]["value"]))->select();
	
	    $companyList = Db::table("s_k_company")->field("cid as value,cname as label")->select();
	    $returnRes=array("activityList"=>$activityList,"groupList"=>$groupList,"companyList"=>$companyList);
	
	    if ($returnRes) {
	        $this->array_return['ResultType'] = self::__OK__;
	        $this->array_return['Message'] = "操作成功";
	        $this->array_return['AppendData'] = $returnRes;
	    } else {
	        $this->array_return['ResultType'] = self::__ERROR2__;
	        $this->array_return['Message'] = "操作失败";
	    }
	    
	    return json($this->array_return);
	}

    /**
     * 开始新的轮播
     */
    public  function  new_broadcast_user()
    {
	    $res = $this->request->post();
	    $data["s_start"]=3;
	    $where["s_start"]=array("in","1,2");
	    $retuenMap = Db::table("s_broadcast_users")->where($where)->update($data);
	    if ($retuenMap) {
	        $this->array_return['ResultType'] = self::__OK__;
	        $this->array_return['Message'] = "操作成功";
	        $this->array_return['AppendData'] = $retuenMap;
	    } else {
	        $this->array_return['ResultType'] = self::__ERROR2__;
	        $this->array_return['Message'] = "操作失败";
	    }
	    
	    return json($this->array_return);
	}

    /**
     * 将已轮播用户标志为未轮播
     */
	public  function  broadcast_userTo()
	{
	    $res = $this->request->post();
	    $sid = $res['sid'];
	    $where["s_bid"]=$sid;
	    $data["s_start"]=1;
	    $retuenMap = Db::table("s_broadcast_users")->where($where)->update($data);
	    if ($retuenMap) {
	        $this->array_return['ResultType'] = self::__OK__;
	        $this->array_return['Message'] = "操作成功";
	        $this->array_return['AppendData'] = $retuenMap;
	    } else {
	        $this->array_return['ResultType'] = self::__ERROR2__;
	        $this->array_return['Message'] = "操作失败";
	    }
	    
	    return json($this->array_return);
	}

    /**
     * 得到轮播用户
     */
	 public  function listbroadcast_users()
	 {
	     $res = $this->request->post();
	     $n = $res['n'] ? $res['n'] : 1;
	
	     $map = array();
	     if(!empty($res['s_username'])){
	         $map['b.s_username']=array('like','%'.$res['s_username'].'%');
	     }
	
	  	 if(!empty($res['s_userphone'])) {
	       $map['b.s_userphone'] = array('like', "%".$res['s_userphone']."%");
	     }
	
	     $map['a.s_start'] = array('in',"1,2");
	     $pageSize = 5;
	     $m = Db::table("s_broadcast_users");
	     $pageTotal=$m->alias("a")->leftJoin("s_signin_usre b","a.s_userid=b.s_userid")->where($map)->count();
	
	     $list=$m->alias("a")->leftJoin("s_signin_usre b","a.s_userid=b.s_userid")
	             ->field("a.*,b.s_username,b.s_usertopimgurl,b.s_grade,b.s_userphone,b.s_vurl")->where($map)->order('a.s_signin_date desc')->
	         limit(($n-1)* $pageSize,$pageSize)->select();

	     $datalist = array("list"=>$list,"pageTotal"=>$pageTotal,"pageSize"=>$pageSize,"pageNum"=>$n);
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
     * 得到当前已经签到但是未轮播的用户
     */
    public  function getbroadcast_users()
    {
    	$res = $this->request->post();
       	if(!empty($res["Lasttimeuserid"])){
        	$where = array();
            $where['s_bid'] = array('in',$res["Lasttimeuserid"]);
            $data["s_start"]=2;
         	$s = Db::table("s_broadcast_users")->where($where)->update($data);
        }
        
       	$list = Db::table("s_broadcast_users")->alias("a")->leftJoin("s_signin_usre b","a.s_userid=b.s_userid")
           ->field("a.*,b.s_username,b.s_usertopimgurl,b.s_grade,b.s_userphone,b.s_vurl")->where(array("a.s_start"=>1))->select();

        if ($list) {
           $this->array_return['ResultType'] = self::__OK__;
           $this->array_return['Message'] = "操作成功";
           $this->array_return['AppendData'] = $list;
        } else {
           $this->array_return['ResultType'] = self::__ERROR2__;
           $this->array_return['Message'] = "操作失败";
        }
       
        return json($this->array_return);
   }

    /**
     * 插入或修改签到
     */
    public  function  i_and_u_activity_user()
    {
        $res = $this->request->post();
        $res['s_start']=="-1"?'0': $res['s_start'];
        if(!empty($res['s_id'])){
            if($res['s_start']==2) {
                $res['s_signin_date'] = date('Y-m-d H:i:s');
                $data=array("s_start"=>1,"s_signin_date"=>$res['s_signin_date'],"s_userid"=>$res['s_userid']);
                Db::table("s_broadcast_users")->data($data)->insert();
            }else{
                Db::table("s_broadcast_users")->where(array("s_userid"=>$res['s_userid']))->delete();
            }
            $returnRes = Db::table('s_activity_user')->update($res);
        }else{
            $returnRes = Db::table("s_activity_user")->data($res)->insert();
        }
        
        if ($returnRes) {
            $this->array_return['ResultType'] = self::__OK__;
            $this->array_return['Message'] = "操作成功";
            $this->array_return['AppendData'] = $returnRes;
        } else {
            $this->array_return['ResultType'] = self::__ERROR2__;
            $this->array_return['Message'] = "操作失败";
        }
        
        return json($this->array_return);
    }
    
    /**
     * 删除签到
     */
    public function Delactivity_user()
    {
        $res = $this->request->post();
        $id = $res['s_id'];
        if(empty($id)){
            $this->array_return['ResultType'] = self::__ERROR2__;
            $this->array_return['Message'] = "参数缺失";
        }else{
            $returnRes = Db::table("s_activity_user")->where(array('s_id'=>$id))->delete();
            if ($returnRes) {
                $this->array_return['ResultType'] = self::__OK__;
                $this->array_return['Message'] = "操作成功";
                $this->array_return['AppendData'] = $returnRes;
            } else {
                $this->array_return['ResultType'] = self::__ERROR2__;
                $this->array_return['Message'] = "操作失败";
            }
        }
        
        return json($this->array_return);
    }

    /**
     *
     */
    public function  get_activity_max()
    {
        $s_activity_id = Db::table("s_activity")->max("s_activity_id");
        $s_activity = Db::table("s_activity")->where(array("s_activity_id"=>$s_activity_id))->find();
        if($s_activity){
            $this->array_return['ResultType'] = self::__OK__;
            $this->array_return['Message'] = "操作成功";
            $this->array_return['AppendData'] =$s_activity ;
        }else{
            $this->array_return['ResultType'] = self::__OK__;
            $this->array_return['Message'] = "操作成功";
            $this->array_return['AppendData'] =false ;
        }

        return json($this->array_return);
    }

    /**
     * 判断此用户用户是否存在
     */
    public function  is_user()
    {
        $res = $this->request->post();
        $phone = $res['phone'];
        $s_activity_id = Db::table("s_activity")->max("s_activity_id");
        $usre = Db::table("s_signin_usre")->where(array("s_activity_id"=>$s_activity_id,"s_userphone"=>$phone))->count();
        if($usre >=1){
            $this->array_return['ResultType'] = self::__OK__;
            $this->array_return['Message'] = "操作成功";
            $this->array_return['AppendData'] =true ;
        }else{
            $this->array_return['ResultType'] = self::__OK__;
            $this->array_return['Message'] = "操作成功";
            $this->array_return['AppendData'] =false ;
        }

        return json($this->array_return);
    }

    /**
     * 初始化
     */
    public  function  init_company()
    {
        $res = $this->request->post();
        $phone = $res['phone'];
        $s_activity_id = Db::table("s_activity")->max("s_activity_id");
        $res["s_vcontent"]="恭喜".$res["s_username"]."签到成功";
        $res["s_vsex"]=4;
        $res["s_vspeed"]=4;
        $res["s_volumn"]=5;
        $res["s_vpit"]=5;
        $res["s_vbaiduurl"]= $this->text_to_audio2( $res["s_vcontent"],5,5,5,4);
        $res["s_vurl"] = $res["s_vbaiduurl"];
        $data["s_start"]=1;
        $a = Db::table("s_signin_usre")->where(array("s_activity_id"=>$s_activity_id,"s_userphone"=>$phone))->update($res);
        if($a){
            $a = Db::table("s_signin_usre")->where(array("s_activity_id"=>$s_activity_id,"s_userphone"=>$phone))->column("s_useruuid");
            $this->array_return['ResultType'] = self::__OK__;
            $this->array_return['Message'] = "操作成功";
            $this->array_return['AppendData'] =$a ;
        }else{
            $a = Db::table("s_signin_usre")->where(array("s_activity_id"=>$s_activity_id,"s_userphone"=>$phone))->column("s_useruuid");
            $this->array_return['ResultType'] = self::__ERROR2__;
            $this->array_return['Message'] = "操作失败";
            $this->array_return['AppendData'] =$a ;
        }

        return json($this->array_return);
    }

    private function text_to_audio2($tex='',$spd=5,$pit=5,$vol=5,$per=4)
    {
        $appid = '11679016';//填写自己的appid
        $apikey = 'tCP0CE2C5L5zjvCsM4s1Cogf';//填写自己的
        $secretkey = 'LM90LDvGgHx0GAIjXWD2vOqGYzVrMbQQ';//填写自己的
        $res = $this->request->post();
        if(!$tex){
            $this->error('字符非法');
        }

        $tex = urlencode($tex);
        //语言文件地址
        $file = './Uploads/Mp3/'.md5($tex).'.mp3'; //本地缓存音源的路径 避免同样提示音重复去百度生成
        if(!is_dir($file.date('Ymd'))){
            $a=mkdir($file.date('Ymd'),0777,true);
        }
         //缓存名称
        $name = 'bdtsn_'.$appid;
        if(!$json = Cache::get($name)){
            //获取百度语音token信息 并缓存
            $jsonStr =$this-> http_post('https://openapi.baidu.com/oauth/2.0/token?grant_type=client_credentials&client_id='.$apikey.'&client_secret='.$secretkey);
            $json = json_decode($jsonStr,true);
            Cache::set($name,$json,$json['expires_in']);
        }
        if(!$json){
            $this->error('token获取失败');
        }

        //获取语语音数据 并生成 本地mp3文件 per
        $url = 'http://tsn.baidu.com/text2audio?tex='.$tex.'&lan=zh&spd='.$spd.'&pit='.$pit.'&vol='.$vol.'&per='.$per.'&cuid='.$appid.'&ctp=1&tok='.$json['access_token'];
        $data = file_get_contents($url);
        if(!$json){
            $this->error('音频数据获取失败');
        }
        file_put_contents($file,$data);
        $file=$this->upload_mp3($file,"activity");
       // $file='http://'.$_SERVER['HTTP_HOST'].substr($file,strpos($file,'.')+1);
       	return $file;
    }

    private function upload_mp3($imgs,$start_name)
    {
        if ($start_name) {
            $end_name = trim(strrchr($imgs, '.'), '.');
            if ($end_name == 'jpeg') {
                $save_name = $start_name . '/' . date('Y-m-d') . '/' . time() . rand(1, 9999) . '.jpg';
            } else {
                $save_name = $start_name . '/' . date('Y-m-d') . '/' . time() . rand(1, 9999) . '.' . $end_name;
            }
            try {
            	$conf = config('OSS_VIDEO');
                $ossClient = new \OSS\OssClient($conf['accessKeyId'], $conf['accessKeySecret'], $conf['endpoint']);
                $image_file = $ossClient->uploadFile($conf['bucket'], $save_name,$imgs);
                if ($image_file['status'] == true) {
                    $shift_url = str_replace($conf['oss_url'], $conf['cdn_usl'], $image_file['url']);
                    $img = $shift_url;
                } else {
                    // 上传错误提示错误信息
                }
            } catch (OssException $e) {
            }
        }
        
        return $img;
    }

    public function  del_call()
    {
        $res = $this->request->post();
        $cid = $res["cid"];
        $a = Db::table("s_k_call")->where(array("cid"=>$cid))->delete();
        if ($a) {
            $this->array_return['ResultType'] = self::__OK__;
            $this->array_return['Message'] = "操作成功";
            $this->array_return['AppendData'] = $a;
        } else {
            $this->array_return['ResultType'] = self::__ERROR2__;
            $this->array_return['Message'] = "操作失败";
        }
        
        return json($this->array_return);
    }

    public function  user_login_to()
    {
        $res = $this->request->post();
        $phone = $res['phone'];
        $s_activity_id = Db::table("s_activity")->max("s_activity_id");

        if(empty($res["phone"]) || empty($res["yxmuseropenid"]))
        {
            $this->array_return['ResultType'] =-2;
            $this->array_return['Message'] = "非法访问";
            $this->array_return['AppendData'] =false ;
            return json($this->array_return);
        }
        
        $usre = Db::table("s_signin_usre")->where(array("s_activity_id"=>$s_activity_id,"s_userphone"=>$phone))->find();
        $userinfos = Db::table("s_user_detail")->alias("a")->leftJoin("s_member as b","a.member_id=b.id")->field('a.vip_id,a.nick_name')->where(array("b.phone"=>$phone))->find();
        $vip_id = intval($userinfos["vip_id"]);

        if($vip_id==3 || $vip_id==4 || $vip_id==5 || $vip_id==6){
            $s_vtitle=2;
        }else{
            $s_vtitle=1;
        }
        
        if(empty($usre))
        {
            $data["s_useruuid"]= getRandomString(10);
            $data["s_activity_id"]=$s_activity_id;
            $data["s_userphone"]=$phone;

            $data["openid"] = $res["yxmuseropenid"];
            $data["s_vtitle"]=$s_vtitle;
            if($s_vtitle==1){
                $data["s_grade"]="-1";
                $data["s_grade"]="创客";
                $data["s_useridentitycard"]="Q";
                $data["s_username"]=$userinfos['nick_name'];
                $data["s_vcontent"]="恭喜签到成功";
                $data["s_vsex"]=4;
                $data["s_vspeed"]=4;
                $data["s_volumn"]=5;
                $data["s_vpit"]=5;
                $data["s_vbaiduurl"]= $this->text_to_audio2($data["s_vcontent"],5,5,5,4);
                $data["s_vurl"]=$data["s_vbaiduurl"];
                $data["s_start"]=1;
            }else{
                $data["s_start"]=0;
            }

            $a = Db::table("s_signin_usre")->insert($data);
            if($a){
                $this->array_return['ResultType'] =0;
                $this->array_return['Message'] = "用户信息不完善";
                $this->array_return['AppendData'] =array("s_vtitle"=>$s_vtitle,"uuid"=> $data["s_useruuid"]);
            }
     }else if($usre["s_start"]==0){
            $c = Db::table("s_signin_usre")->where(array("s_userphone"=>$res["phone"],"s_activity_id"=>$s_activity_id))->update(array("openid"=>$res["yxmuseropenid"]));
            $this->array_return['ResultType'] =0;
            $this->array_return['Message'] = "用户信息不完善";
            $this->array_return['AppendData'] =array("s_vtitle"=>$usre["s_vtitle"],"uuid"=> $usre["s_useruuid"]);
        }else if($usre["s_start"]==1){
            $c = Db::table("s_signin_usre")->where(array("s_userphone"=>$res["phone"],"s_activity_id"=>$s_activity_id))->update(array("openid"=>$res["yxmuseropenid"]));
            $this->array_return['ResultType'] =1;
            $this->array_return['Message'] = "用户未缴费";
            $this->array_return['AppendData'] =$usre["s_useruuid"] ;
        }else if($usre["s_start"]==2){
            $c = Db::table("s_signin_usre")->where(array("s_userphone"=>$res["phone"],"s_activity_id"=>$s_activity_id))->update(array("openid"=>$res["yxmuseropenid"]));
            $this->array_return['ResultType'] =2;
            $this->array_return['Message'] = "用户已缴费";
            $this->array_return['AppendData'] =$usre["s_useruuid"] ;
        }else if($usre["s_start"]==3){
               $c = Db::table("s_signin_usre")->where(array("s_userphone"=>$res["phone"],"s_activity_id"=>$s_activity_id))->update(array("openid"=>$res["yxmuseropenid"]));
            $this->array_return['ResultType'] =3;
            $this->array_return['Message'] = "用户头像已上传";
            $this->array_return['AppendData'] =$usre["s_useruuid"];
        }else if($usre["s_start"]==4){
            $c = Db::table("s_signin_usre")->where(array("s_userphone"=>$res["phone"],"s_activity_id"=>$s_activity_id))->update(array("openid"=>$res["yxmuseropenid"]));
            $this->array_return['ResultType'] =4;
            $this->array_return['Message'] = "用户二维码已生成";
            $this->array_return['AppendData'] =$usre["s_useruuid"];
        }else{
            $this->array_return['ResultType']=-1;
            $this->array_return['Message'] = "服务繁忙或用户未缴费";
            $this->array_return['AppendData'] =true ;
        }
        
        return json($this->array_return);
    }

    /**
     * 用户登录
     */
    public function  user_login()
    {
        $res = $this->request->post();
        $phone = $res['phone'];
        $s_activity_id = Db::table("s_activity")->max("s_activity_id");
        $usre = Db::table("s_signin_usre")->where(array("s_activity_id"=>$s_activity_id,"s_userphone"=>$phone))->find();

        if(empty($usre)){
            $this->array_return['ResultType'] =0;
            $this->array_return['Message'] = "用户不存在";
            $this->array_return['AppendData'] =false ;
        }else if($usre["s_start"]==1){
            $this->array_return['ResultType'] =1;
            $this->array_return['Message'] = "用户未缴费";
            $this->array_return['AppendData'] =false ;
        }else if($usre["s_start"]==2){
            $this->array_return['ResultType'] =2;
            $this->array_return['Message'] = "用户已缴费";
            $this->array_return['AppendData'] =$usre["s_useruuid"] ;
        }else if($usre["s_start"]==3){
            $this->array_return['ResultType'] =3;
            $this->array_return['Message'] = "用户头像已上传";
            $this->array_return['AppendData'] =$usre["s_useruuid"];
        }else if($usre["s_start"]==4){
            $this->array_return['ResultType'] =4;
            $this->array_return['Message'] = "用户二维码已生成";
            $this->array_return['AppendData'] =$usre["s_useruuid"];
        }else{
            $this->array_return['ResultType']=-1;
            $this->array_return['Message'] = "服务繁忙或用户未缴费";
            $this->array_return['AppendData'] =true ;
        }
        
        return json($this->array_return);
    }

    /**
     *保存头像地址，并且生成二维码
     */
    public function save_topimg_and_insert_qr()
    {
        $res = $this->request->post();
        $s_useruuid = $res['useruuid'];
        $s_usertopimgurl = $res['usertopimgurl'];
        $s_usertopimgurl='http:'.substr($s_usertopimgurl,strpos($s_usertopimgurl,':')+1);
        $user = Db::table("s_signin_usre") ->alias('a')->leftJoin("s_activity b","a.s_activity_id=b.s_activity_id")
            ->leftJoin("s_activity_group c","a.s_group=c.s_id")->
            field("a.s_useruuid,a.s_username,a.s_userphone,a.s_grade,a.s_referee,a.s_qr_url,
        b.s_activityname,c.s_name,c.s_captains_name,b.s_activitybuttomimgurl")->where(array("s_useruuid"=>$s_useruuid))->find();

        if(empty($s_useruuid) || empty($s_usertopimgurl)){
            $this->array_return['ResultType'] = self::__ERROR2__;
            $this->array_return['Message'] = "操作失败";
            return json($this->array_return);
        }
        $qrcode_save_path="./Uploads/Xcx/";
        if(!is_dir($qrcode_save_path.date('Ymd'))){
            $a=mkdir($qrcode_save_path.date('Ymd'),0777,true);
        }

        //保存路径和文件名称
        $qrCode_file_name = $qrcode_save_path.date('Ymd').'/ewm_'.md5($s_useruuid).'.png';
        $url = 'http://'.$_SERVER['HTTP_HOST'].'/Home/Conference/card.html?uid='.$s_useruuid;
        //背景、头象、二维码、文字
        qrcode3($url,4,$qrCode_file_name,$s_usertopimgurl);

        //二维码保存路径
        $qrCode_file_name2=$qrCode_file_name;
        $qrCode_file_=$qrCode_file_name2;
        $qrCode_file_name='http://'.$_SERVER['HTTP_HOST'].substr($qrCode_file_name,strpos($qrCode_file_name,'.')+1);

        $font_path = './Uploads/msyh.ttf';
        $imgEven = new imgModel();

        //合并到背景图中
        $sharePublicIMg = $user['s_activitybuttomimgurl'];
        $sharePublicIMg = 'http:'.substr($sharePublicIMg,strpos($sharePublicIMg,':')+1);

        //合成之后保存的路径listbroadcast_users
        $saveFile = $qrcode_save_path.date('Ymd').'/ewm_'.md5($s_useruuid).'1.png';
        //头像保存路径
        $headFile = $qrcode_save_path.date('Ymd').'/ewm_'.md5($s_useruuid).'h.jpg';
      	//$sharePublicIMg="http://wx.yxm360.com/Uploads/111.png";
        $image = new \Think\Image();
        $image->open($s_usertopimgurl);
        $image->thumb('210', '210',3)->update($headFile);
        $saveFile=$imgEven->update_activity_bck($sharePublicIMg,$saveFile,$font_path,$qrCode_file_name,$user,$headFile);

        $saveFile2=$saveFile;
        $saveF1=$saveFile2;

        $start_name = 'activity';
        $end_namesaveFile2 = trim(strrchr(substr($saveFile2,1), '.'),'.');
        $save_namesaveFile2= $start_name.'/'.date('Ymd').'/'.time().rand(1,9999).'.'.$end_namesaveFile2;

        $end_nameqrCode_file_name2= trim(strrchr(substr($qrCode_file_name2,1), '.'),'.');
        $save_nameqrCode_file_name2 = $start_name.'/'.date('Ymd').'/'.time().rand(1,9999).'.'.$end_nameqrCode_file_name2;

        try{
        	$conf = config('OSS_IMAGE');
            $ossClient =   new \OSS\OssClient($conf['accessKeyId'], $conf['accessKeySecret'], $conf['endpoint']);
            $image_file =  $ossClient->uploadFile($conf['bucket'], $save_namesaveFile2, $saveF1);
            $image_file1 =  $ossClient->uploadFile($conf['bucket'], $save_nameqrCode_file_name2, $qrCode_file_);
            if ($image_file['status'] == true)
            {
                $shift_url = str_replace($conf['oss_url'],$conf['cdn_usl'],$image_file['url']);
                $result_url =  $shift_url."@!protected";

                $shift_url1 = str_replace($conf['oss_url'],$conf['cdn_usl'],$image_file1['url']);
                $result_url1 =  $shift_url1."@!protected";
                unlink(substr($saveFile2,2));
                unlink(substr($qrCode_file_name2,2));
                $saveFile='http://'.$_SERVER['HTTP_HOST'].substr($saveFile,strpos($saveFile,'.')+1);
                $data['s_myqr_url'] =$result_url;
                $data['s_usertopimgurl'] =$s_usertopimgurl;
                $data['s_qr_url'] = $result_url1;
                $data['s_start'] = 4;
                $users = Db::table("s_signin_usre")->where(array('s_useruuid'=>$s_useruuid))->update($data);
                if ($users) {
                    $this->array_return['ResultType'] = self::__OK__;
                    $this->array_return['Message'] = "操作成功";
                    $this->array_return['AppendData'] = $users;
                } else {
                    $this->array_return['ResultType'] = self::__ERROR2__;
                    $this->array_return['Message'] = "操作失败";
                }

            }else{
                // 上传错误提示错误信息
                $this->array_return['ResultType'] = self::__ERROR2__;
                $this->array_return['Message'] = "操作失败";
            }

        } catch(OssException $e) {
            $this->array_return['ResultType'] = self::__ERROR2__;
            $this->array_return['Message'] = "操作失败";
        }
        
        return json($this->array_return);
    }

    public function save_topimg_and_insert_qr_admin()
    {
        $res = $this->request->post();
        $s_useruuid = $res['useruuid'];
        $user = Db::table("s_signin_usre") ->alias('a')->leftJoin("s_activity b","a.s_activity_id=b.s_activity_id")
            ->leftJoin("s_activity_group c","a.s_group=c.s_id")->field("a.s_useruuid,a.s_username,a.s_userphone,a.s_grade,a.s_referee,a.s_qr_url,a.s_usertopimgurl,
        b.s_activityname,c.s_name,c.s_captains_name,b.s_activitybuttomimgurl")->where(array("s_useruuid"=>$s_useruuid))->find();

        if(empty($s_useruuid)){
            $this->array_return['ResultType'] = self::__ERROR2__;
            $this->array_return['Message'] = "操作失败";
            return json($this->array_return);
        }
        $s_usertopimgurl=$user['s_usertopimgurl'];
        $s_usertopimgurl='http:'.substr($s_usertopimgurl,strpos($s_usertopimgurl,':')+1);

        $qrcode_save_path="./Uploads/Xcx/";
        if(!is_dir($qrcode_save_path.date('Ymd'))){
            $a=mkdir($qrcode_save_path.date('Ymd'),0777,true);
        }

        //保存路径和文件名称
        $qrCode_file_name = $qrcode_save_path.date('Ymd').'/ewm_'.md5($s_useruuid).'.png';
        $url = 'http://'.$_SERVER['HTTP_HOST'].'/Home/Conference/card.html?uid='.$s_useruuid;
        //背景、头象、二维码、文字
        qrcode3($url,4,$qrCode_file_name,$s_usertopimgurl);

        //二维码保存路径
        $qrCode_file_name2=$qrCode_file_name;
        $qrCode_file_=$qrCode_file_name2;
        $qrCode_file_name='http://'.$_SERVER['HTTP_HOST'].substr($qrCode_file_name,strpos($qrCode_file_name,'.')+1);

        $font_path = './Uploads/msyh.ttf';
         $imgEven = new imgModel();

        //合并到背景图中
        $sharePublicIMg = $user['s_activitybuttomimgurl'];
        $sharePublicIMg = 'http:'.substr($sharePublicIMg,strpos($sharePublicIMg,':')+1);

        //合成之后保存的路径listbroadcast_users
        $saveFile = $qrcode_save_path.date('Ymd').'/ewm_'.md5($s_useruuid).'1.png';
        //头像保存路径
        $headFile = $qrcode_save_path.date('Ymd').'/ewm_'.md5($s_useruuid).'h.jpg';

        $image = new \Think\Image();
        $image->open($s_usertopimgurl);

        $image->thumb('210', '210',3)->update($headFile);
        $saveFile=$imgEven->update_activity_bck($sharePublicIMg,$saveFile,$font_path,$qrCode_file_name,$user,$headFile);
        $saveFile2=$saveFile;
        $saveF1=$saveFile2;

        $start_name = 'activity';
        $end_namesaveFile2 = trim(strrchr(substr($saveFile2,1), '.'),'.');
        $save_namesaveFile2= $start_name.'/'.date('Ymd').'/'.time().rand(1,9999).'.'.$end_namesaveFile2;

        $end_nameqrCode_file_name2= trim(strrchr(substr($qrCode_file_name2,1), '.'),'.');
        $save_nameqrCode_file_name2 = $start_name.'/'.date('Ymd').'/'.time().rand(1,9999).'.'.$end_nameqrCode_file_name2;

        try{
            $ossClient =   new \OSS\OssClient($conf['accessKeyId'], $conf['accessKeySecret'], $conf['endpoint']);
            $image_file =  $ossClient->uploadFile($conf['bucket'], $save_namesaveFile2, $saveF1);
            $image_file1 =  $ossClient->uploadFile($conf['bucket'], $save_nameqrCode_file_name2, $qrCode_file_);
            if ($image_file['status'] == true)
            {
                $shift_url = str_replace($conf['oss_url'],$conf['cdn_usl'],$image_file['url']);
                $result_url =  $shift_url."@!protected";
                $shift_url1 = str_replace($conf['oss_url'],$conf['cdn_usl'],$image_file1['url']);
                $result_url1 =  $shift_url1."@!protected";
                unlink(substr($saveFile2,2));
                unlink(substr($qrCode_file_name2,2));
                $saveFile='http://'.$_SERVER['HTTP_HOST'].substr($saveFile,strpos($saveFile,'.')+1);
                $data['s_myqr_url'] =$result_url;
                $data['s_usertopimgurl'] =$s_usertopimgurl;
                $data['s_qr_url'] = $result_url1;
                $users = Db::table("s_signin_usre")->where(array('s_useruuid'=>$s_useruuid))->update($data);
                if ($users) {
                    $this->array_return['ResultType'] = self::__OK__;
                    $this->array_return['Message'] = "操作成功";
                    $this->array_return['AppendData'] = $users;
                } else {
                    $this->array_return['ResultType'] = self::__ERROR2__;
                    $this->array_return['Message'] = "操作失败";
                }
            }else{
                // 上传错误提示错误信息
                $this->array_return['ResultType'] = self::__ERROR2__;
                $this->array_return['Message'] = "操作失败";
            }

        } catch(OssException $e) {
            $this->array_return['ResultType'] = self::__ERROR2__;
            $this->array_return['Message'] = "操作失败";
        }
        
        return json($this->array_return);
    }
    
    /**
     *
     */
    public function get_user_type()
    {
        $res = $this->request->post();
        $nameid = $res["nameid"];
        $users = Db::table("s_signin_usre")->where(array("s_useruuid"=>$nameid))->column("s_start");
        if ($users) {
            $this->array_return['ResultType'] = self::__OK__;
            $this->array_return['Message'] = "操作成功";
            $this->array_return['AppendData'] = $users;
        } else {
            $this->array_return['ResultType'] = self::__ERROR2__;
            $this->array_return['Message'] = "操作失败";
        }
        return json($this->array_return);
    }

    public function find_user_by_id()
    {
        $res = $this->request->post();
        $s_useruuid = $res['useruuid'];
        if(empty($s_useruuid)){
            $this->array_return['ResultType'] = self::__ERROR2__;
            $this->array_return['Message'] = "操作失败";
            return json($this->array_return);
        }
        $user = Db::table("s_signin_usre") ->where(array("s_useruuid"=>$s_useruuid))->find();
        if ($user) {
            $this->array_return['ResultType'] = self::__OK__;
            $this->array_return['Message'] = "操作成功";
            $this->array_return['AppendData'] = $user;
        } else {
            $this->array_return['ResultType'] = self::__ERROR2__;
            $this->array_return['Message'] = "操作失败";
        }
        
        return json($this->array_return);
	}
	
    public function uploadImg()
    {
        $upload = new \Think\Upload();//实例化上传类
        $upload->maxSize = 0 ;//设置附件上传大小
        $upload->exts = array('jpg','png','jpeg');//设置附件上传类型
        $upload->rootPath = './Uploads/back/'; //设置附件上传根目录
        $upload->updatePath = '';//设置附件上传（子）目录
        // 上传文件
        if(!is_dir("./Uploads/back/")){
            $a=mkdir("./Uploads/back/",0777,true);
        }
        $info = $upload->upload();
        if(!$info) {//上传错误提示错误信息
            $this->error($upload->getError());
        }else {//上传成功
            $file = './Uploads/Xcx/' . $info['file']['savepath'] . $info['file']['savename'];
            $file='http://'.$_SERVER['HTTP_HOST'].substr($file,strpos($file,'.')+1);
            $this->array_return['ResultType'] = self::__OK__;
            $this->array_return['Message'] = "操作成功";
            $this->array_return['AppendData'] = $file;
        }
        
        return json($this->array_return);
    }

    public function qiliaodaru()
    {
        $upload = new \Think\Upload();//实例化上传类
        $upload->maxSize = 0 ;//设置附件上传大小
        $upload->exts = array('csv','xls','xlsx');//设置附件上传类型
        $upload->rootPath = './Uploads/Xcx/'; //设置附件上传根目录
        $upload->updatePath = '';//设置附件上传（子）目录
        // 上传文件
        $info = $upload->upload();
        if(!$info) {//上传错误提示错误信息
            $this->error($upload->getError());
        }else {//上传成功
            // $this->success('上传成功！' . $info['file']['savepath'] . $info['file']['savename']);
            $file = './Uploads/Xcx/' . $info['file']['savepath'] . $info['file']['savename'];
            //件的完整路径
            $dataList[]=[];
            require('/louse2/base/ExcelDrivers/PHPExcel.php');
            $PHPExcel = new PHPExcel();
            require('/louse2/base/ExcelDrivers/PHPExcel/Reader/Excel2007.php');
            $PHPReader=new \PHPExcel_Reader_Excel2007();
            //载入文件
            $PHPExcel=$PHPReader->load($file);
            //获取表中的第一个工作表，如果要获取第二个，把0改为1，依次类推
            $currentSheet=$PHPExcel->getSheet(0);
            //获取总列数
            $allColumn=$currentSheet->getHighestColumn();
            //获取总行数
            $allRow=$currentSheet->getHighestRow();
            //循环获取表中的数据，$currentRow表示当前行，从哪行开始读取数据，索引值从0开始
            for($currentRow=2;$currentRow<=$allRow;$currentRow++){
                //从哪列开始，A表示第一列
                $i=0;
                for($currentColumn='A';$currentColumn<=$allColumn;$currentColumn++){
                    //数据坐标
                    $address=$currentColumn.$currentRow;
                    //读取到的数据，保存到数组$arr中，$cell获取了每个单元格的内容，然后再根据需要对数据进行拼装
                    $cell =$currentSheet->getCell($address)->getValue();
                    $dataList[$currentRow-2][$i]=$cell;
                    $i++;
                }
            }
            
            $s_activity_id = Db::table("s_activity")->max("s_activity_id");
            $activityObj = Db::table("s_activity")->field("s_activitydate")->where(array("s_activity_id" => $s_activity_id))->find();
            $s_activitydate = (int)$activityObj["s_activitydate"];
            for($i=0;$i<count($dataList);$i++)
            {
                $arr=array(
                    "s_username"=>$dataList[$i][0],
                    "s_userphone"=>$dataList[$i][1],
                    "s_useridentitycard"=>$dataList[$i][2],
                    "s_grade"=>$dataList[$i][3],
                    "s_referee"=>$dataList[$i][4],
                    "s_group"=>$dataList[$i][5],
                    "s_start"=>$dataList[$i][6],
                    "s_userdis"=>$dataList[$i][7],
                    "s_activity_id"=>$s_activity_id
                );
                
                $arr["s_useruuid"] =  getRandomString(10);
                $returnRes = Db::table("s_signin_usre")->insert($arr);
                if (!empty($returnRes)) {
                    for ($ii = 0; $ii < $s_activitydate; $ii++) 
                    {
                        $dataList1[$ii] = array(
                            's_start' => '1',
                            's_userid' => $returnRes,
                            's_activityid' => $arr["s_activity_id"],
                            's_dis' => '批量添加用户'
                        );
                    }

                    Db::table("s_activity_user")->addAll($dataList1);
               }
            }
        }

        return json($this->array_return);
    }


    public function text_to_audio()
    {
        $appid = '11679016';//填写自己的appid
        $apikey = 'tCP0CE2C5L5zjvCsM4s1Cogf';//填写自己的
        $secretkey = 'LM90LDvGgHx0GAIjXWD2vOqGYzVrMbQQ';//填写自己的
        $res = $this->request->post();
            //        tex	String	合成的文本，使用UTF-8编码，
            //请注意文本长度必须小于1024字节	是
            //cuid	String	用户唯一标识，用来区分用户，
            //填写机器 MAC 地址或 IMEI 码，长度为60以内	否
            //spd	String	语速，取值0-9，默认为5中语速	否
            //pit	String	音调，取值0-9，默认为5中语调	否
            //vol	String	音量，取值0-15，默认为5中音量	否
            //per	String	发音人选择, 0为女声，1为男声，
            //3为情感合成-度逍遥，4为情感合成-度丫丫，默认为普通女	否
        $tex = $res["tex"]==null?'':$res["tex"];
        $spd = $res["spd"]==null?'5':$res["spd"];
        $pit = $res["pit"]==null?'5':$res["pit"];
        $vol = $res["vol"]==null?'5':$res["vol"];
        $per = $res["per"]==null?'4':$res["per"];

        if(!$tex){
            $this->error('字符非法');
        }

        $tex = urlencode($tex);
        //语言文件地址
        $file = './Uploads/Mp3/'.md5($tex).'.mp3'; //本地缓存音源的路径 避免同样提示音重复去百度生成

        if(!is_dir($file.date('Ymd'))){
            $a=mkdir($file.date('Ymd'),0777,true);
        }
      
        //缓存名称
        $name = 'bdtsn_'.$appid;
        if(!$json = Cache::get($name)){
        	//获取百度语音token信息 并缓存
            $jsonStr =$this-> http_post('https://openapi.baidu.com/oauth/2.0/token?grant_type=client_credentials&client_id='.$apikey.'&client_secret='.$secretkey);
            $json = json_decode($jsonStr,true);
            Cache::set($name,$json,$json['expires_in']);
        }

        if(!$json){
        	$this->error('token获取失败');
        }

        //获取语语音数据 并生成 本地mp3文件 per
        $url = 'http://tsn.baidu.com/text2audio?tex='.$tex.'&lan=zh&spd='.$spd.'&pit='.$pit.'&vol='.$vol.'&per='.$per.'&cuid='.$appid.'&ctp=1&tok='.$json['access_token'];
        $data = file_get_contents($url);

        if(!$json){
        	$this->error('音频数据获取失败');
        }

        file_put_contents($file,$data);
        $file=$this->upload_mp3($file,"activity");
         
        if ($file) {
            $this->array_return['ResultType'] = self::__OK__;
            $this->array_return['Message'] = "操作成功";
            $this->array_return['AppendData'] = $file;
            return json($this->array_return);
        } else {
            $this->array_return['ResultType'] = self::__ERROR2__;
            $this->array_return['Message'] = "操作失败";
        }
        
        return json($this->array_return);
    }
     
    function TextToAudio2($tex,$spd=5,$pit=5,$vol=5,$per=4)
    {
        $appid = '11679016';//填写自己的appid
        $apikey = 'tCP0CE2C5L5zjvCsM4s1Cogf';//填写自己的
        $secretkey = 'LM90LDvGgHx0GAIjXWD2vOqGYzVrMbQQ';//填写自己的

        if(!$tex){
            $this->error('字符非法');
        }

        $tex = urlencode($tex);
        //语言文件地址
        $file = './Uploads/Mp3/'.md5($tex).'.mp3'; //本地缓存音源的路径 避免同样提示音重复去百度生成
        if(!is_dir($file.date('Ymd'))){
            $a=mkdir($file.date('Ymd'),0777,true);
        }
        
        //缓存名称
        $name = 'bdtsn_'.$appid;
        if(!$json = Cache::get($name)){
            //获取百度语音token信息 并缓存
            $jsonStr =$this-> http_post('https://openapi.baidu.com/oauth/2.0/token?grant_type=client_credentials&client_id='.$apikey.'&client_secret='.$secretkey);
            $json = json_decode($jsonStr,true);
            Cache::set($name,$json,$json['expires_in']);
        }

        if(!$json){
            $this->error('token获取失败');
        }
        //获取语语音数据 并生成 本地mp3文件 per
        $url = 'http://tsn.baidu.com/text2audio?tex='.$tex.'&lan=zh&spd='.$spd.'&pit='.$pit.'&vol='.$vol.'&per='.$per.'&cuid='.$appid.'&ctp=1&tok='.$json['access_token'];
        $data = file_get_contents($url);
        if(!$json){
            $this->error('音频数据获取失败');
        }
        file_put_contents($file,$data);
        $file='http://'.$_SERVER['HTTP_HOST'].substr($file,strpos($file,'.')+1);
        return $file;
    }


    /**
     * @param $url
     * @param $param
     * @param bool $post_file
     * @return bool|mixed请求数据
     */
    function http_post($url,$param=array(),$post_file=false)
    {
        $oCurl = curl_init();
        if(stripos($url,"https://")!==FALSE){
            curl_setopt($oCurl, CURLOPT_SSL_VERIFYPEER, FALSE);
            curl_setopt($oCurl, CURLOPT_SSL_VERIFYHOST, false);
            curl_setopt($oCurl, CURLOPT_SSLVERSION, 1); //CURL_SSLVERSION_TLSv1
        }
        if (is_string($param) || $post_file) {
            $strPOST = $param;
        } else {
            $aPOST = array();
            foreach($param as $key=>$val){
                $aPOST[] = $key."=".urlencode($val);
            }
            $strPOST =  join("&", $aPOST);
        }
        curl_setopt($oCurl, CURLOPT_URL, $url);
        curl_setopt($oCurl, CURLOPT_RETURNTRANSFER, 1 );
        curl_setopt($oCurl, CURLOPT_POST,true);
        curl_setopt($oCurl, CURLOPT_POSTFIELDS,$strPOST);
        $sContent = curl_exec($oCurl);
        $aStatus = curl_getinfo($oCurl);
        curl_close($oCurl);
        if(intval($aStatus["http_code"])==200){
            return $sContent;
        }else{
            return false;
        }
    }
}