<?php
/**
 * /**
 *createUser:JAVA_TOM
 * FileName: CustomerServiceController
 * Author:   14562
 * Date:     2018/8/22 9:42
 * Description: 客服系统api
 *
 */
namespace app\api\controller;
use think\Controller;
use think\Db;
use app\api\model\KCustomerService  as customerModel;
use app\api\model\KPosts  as kpostsModel;

class CustomerService extends Controller 
{
    const __OK__ = '0'; //请求成功
    const __ERROR__ = '1'; //参数错误
    const __ERROR1__ = '2'; //没有绑定
    const __ERROR2__ = '3';//数据库错误
    private $array_return = array("ResultType"=>1,"Message"=>"success","AppendData"=>"");

	private $customer_mod = null;
	private $kposts_mod = null;
	 
 	function initialize()
 	{
 		parent::initialize();
 		$this->customer_mod = new customerModel();
        $this->kposts_mod = new kpostsModel();
    }
    
	 public  function  nagent_init()
	 {
	 	$res = $this->request->post();
	    $phone = $res["phone"];
	    $m = Db::table("s_api_nagent_apply")->where(array("nagent_phone"=>$phone)->order('nagent_type_id desc')->find();
	    if($m){
	    	$this->array_return['AppendData'] = $m;
	        $this->array_return['ResultType'] =0;
	        $this->array_return['Message'] = "修改失败";
	    }else{
	        $this->array_return['AppendData'] = $m;
	        $this->array_return['ResultType'] =0;
	        $this->array_return['Message'] = "修改成功";
	    }
	    
		return json($this->array_return);
	}

    /**
     * 登录验证
     */
    public function to_login()
    {
        $cookie = input('cookie.');
        $a = Db::table('s_k_customer_service')->where(array("cuuid"=>$cookie['k_cuuid'],"c_start"=>2))->column("sid");
            
        if($a==null || $a==0 || $a==''){         
        	$this->array_return['ResultType'] = -1;
        	$this->array_return['Message'] = "非法访问";
        	return json($this->array_return);
        }else{
        	$this->array_return['ResultType'] =0;
        	$this->array_return['Message'] = "验证成功";
        	return json($this->array_return);    
        }
    }

    /**
     * 密码修改
     */
    public function userpass()
    {
        $res = $this->request->post();
        $cookie = input('cookie.');
        if(empty($res["usedpwd"])||empty($res["pwd"])){
            $this->array_return['ResultType'] = self::__ERROR2__;
            $this->array_return['Message'] = "参数缺少";
            return json($this->array_return);
        }

        $usre = Db::table('s_k_customer_service')->where(array("cuuid"=>$cookie['k_cuuid'],"c_start"=>2))->find();
        if(empty($usre)){
            $this->array_return['ResultType'] = 0;
            $this->array_return['Message'] = "用户不存在";
            return json($this->array_return);
        }else if($usre["cpwd"]==md5($res["usedpwd"]."javatom")){
            $a = Db::table('s_k_customer_service')->where(array("cuuid"=>$cookie['k_cuuid']))->update(array("cpwd"=> md5($res["pwd"]."javatom")));
            if($a){
                $this->array_return['AppendData'] = true;
                $this->array_return['ResultType'] =3;
                $this->array_return['Message'] = "修改成功";

            }else{
                $this->array_return['AppendData'] = true;
                $this->array_return['ResultType'] =4;
                $this->array_return['Message'] = "修改失败";
            }
        }else{
            $this->array_return['AppendData'] = true;
            $this->array_return['ResultType'] =2;
            $this->array_return['Message'] = "旧密码错误";
        }
        
        return json($this->array_return);
    }

    /**
     * 增加或修改
     */
    public function  i_u_call()
    {
        $res = $this->request->post();
        $cookie = input('cookie.');
        if(!empty($res["cid"])){
            Db::table("s_k_call")->where(array("cid"=>$res["cid"]))->update($res);
        }
        $res['customer_id'] = Db::table('s_k_customer_service')->where(array("cuuid"=>$cookie['k_cuuid']))->column("sid");
        $res["createtime"]= date('Y-m-d H:i:s');
        $a= Db::table("s_k_call")->insert($res);
      	//'日志类型 1/接待记录.2/来电记录,3/问题,4/问答',
        $this->i_customer_log($res['customer_id'],2, $a,"insert","回答了'".$res["userwenti"]."'",$res["createtime"]);
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

    /**
     * 查询日志
     */
    public  function  select_KCustomerService_model()
    {
        $d = $this->customer_mod;
        $a=$d->field('sid,cname,cuuid,caccountnumber,cphone')->where("c_start=2")->relation(true)->select();
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

    public function del_call()
    {
        $res = $this->request->post();
        $cid = $res["cid"];
        $r = Db::table("s_k_call")->where(array("cid"=>$cid))->delete();
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
     * posts
     */
    public  function  select_posts_model()
    {
        $res = $this->request->post();
                    
        $this->array_return['ResultType'] = self::__ERROR2__;
        $this->array_return['Message'] = "操作失败";
        $d = $this->kposts_mod;
        $a=$d->where(array("pid"=>$res['postid']))->relation(true)->select();
        if ($a) {
            $a=$a[0];
            $a["psolve"]=html_entity_decode($a["psolve"]);
            $this->array_return['ResultType'] = self::__OK__;
            $this->array_return['Message'] = "操作成功";
            $this->array_return['AppendData'] = $a;
        }
        
        return json($this->array_return);
    }

    /**
     * 分页查询咨询
     */
    public function f_call()
    {
        $res = $this->request->post();
        $n = $res['n'];
        if(empty($n)){
            $n=1;
        }

        $map = array();
        if(!empty($res['starttime'])){
            $map['a.createtime']=array('GT',$res['starttime']);
        }
        if(!empty($res['starttime'])){
            $map['a.createtime']=array('EGT',$res['endtime']);
        }
        if(!empty($res['userphone'])){
            $map['a.userphone']=array('like',"%".$res['userphone']."%");
        }
        if(!empty($res['username'])) {
            $map['a.username'] = array('like', "%".$res['username']."%");
        }
        if(!empty($res['fanwenleixing'])) {
            $map['a.fanwenleixing'] = array('like', "%".$res['fanwenleixing']."%");
        }
        if(!empty($res['userwenti'])) {
            $map['a.userwenti'] = array('like', "%".$res['userwenti']."%");
        }

        $pageSize = 10;
        $m = Db::table("s_k_call");
        $pageTotal = $m->alias("a")->leftJoin("s_k_customer_service b","a.customer_id=b.sid")->where($map)->count();
        $list = $m->alias("a")->leftJoin("s_k_customer_service b","a.customer_id=b.sid")->
        field("a.*,b.cname as bcname,b.caccountnumber as bcaccountnumber")
        ->where($map)->order('createtime desc')->limit(($n-1)* $pageSize, $pageSize)->select();;
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
    
    public function find_call_by_id()
    {
        $res = $this->request->post();
        $m = Db::table("s_k_call");
        if(empty($res['cid'])){
            $this->array_return['ResultType'] = self::__ERROR2__;
            $this->array_return['Message'] = "操作失败";
            return json($this->array_return);
        }
       //var_dump($m);
        $returns=$m->alias("a")->
        join("left join s_k_customer_service b","a.customer_id=b.sid")
            ->field(" a.*,b.cname as bcname,b.caccountnumber as bcaccountnumber")->
            where(array("a.cid"=>$res['cid']))->select();
        if ($returns) {
            $this->array_return['ResultType'] = self::__OK__;
            $this->array_return['Message'] = "操作成功";
            $this->array_return['AppendData'] = $returns;
        } else {
            $this->array_return['ResultType'] = self::__ERROR2__;
            $this->array_return['Message'] = "操作失败";
        }

        return json($this->array_return);
    }

	public function del_posts()
	{
	    $res = $this->request->post();
	    if(empty($res['post_id'])){
	        $this->array_return['ResultType'] = self::__ERROR2__;
	        $this->array_return['Message'] = "参数缺失";
	        return json($this->array_return);
	    }
	    $m = Db::table("s_k_posts")->where(array("pid"=>$res['post_id']))->delete();
	    Db::table("s_k_posts_one")->where(array("pid"=>$res['post_id']))->delete();
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

	public function f_posts()
	{
	    $res = $this->request->post();
	    $n = $res['n'];
	    if(empty($n)){
	        $n=1;
	    }
	
	    $map = array();
	    $cookie=I("cookie.");
	    if(!empty($res['ismy'])){
	        $map['b.cuuid']=$cookie['k_cuuid'];
	    }
	    if(!empty($res['starttime'])){
	        $map['a.createtime']=array('GT',$res['starttime']);
	    }
	    if(!empty($res['endtime'])){
	        $map['a.createtime']=array('EGT',$res['endtime']);
	    }
	    if(!empty($res['pdis'])){
	        $map['a.pdis']=array('like',"%".$res['pdis']."%");
	    }
	     if(!empty($res['psolve'])){
	         $map['a.psolve']=array('like',"%".$res['psolve']."%");
	     }
	      if(!empty($res['ptitie'])){
	          $map['a.ptitie']=array('like',"%".$res['ptitie']."%");
	      }
	
	    $pageSize=10;
	    $m = Db::table("s_k_posts");
	    $pageTotal= $m->alias("a")->leftJoin("s_k_customer_service b","a.customer_id=b.sid")
	      ->where($map)->count();
	    $list=$m->alias("a")->leftJoin("s_k_customer_service b","a.customer_id=b.sid")
	        ->field(" a.pid,a.ptitie,a.pdis,a.customer_id,a.createtime,b.cname as bcname,b.caccountnumber as bcaccountnumber")
	        ->where($map)->order('createtime desc')->limit(($n-1)* $pageSize,$pageSize)->select();
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
     * 增加或修改
     */
	public function  i_u_posts()
	{
	    $res = $this->request->post();
	    $cookie = input('cookie.');
	    if(!empty($res["pid"])){
	        Db::table("s_k_posts")->where(array("pid"=>$res["pid"]))->update($res);
	        //  '日志类型 1/接待记录.2/来电记录,3/问题,4/问答',
	        $this->i_customer_log($res['customer_id'],3, $res["pid"],"update","增加了'".$res["ptitie"]."'",date('Y-m-d H:i:s'));
	    }
	    $res['customer_id'] = Db::table('s_k_customer_service')->where(array("cuuid"=>$cookie['k_cuuid']))->column("sid");
	    $res["createtime"]= date('Y-m-d H:i:s');
	    $a= Db::table("s_k_posts")->insert($res);
	    //  '日志类型 1/接待记录.2/来电记录,3/问题,4/问答',
	    $this->i_customer_log($res['customer_id'],3, $a,"insert","增加了'".$res["ptitie"]."'",$res["createtime"]);
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
	
    public function  Delcompany()
    {
        $res = $this->request->post();
        $returnRes = Db::table('s_k_company')->where(array("cid"=>$res['cid']))->delete();
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
    
    public function selectcompany()
    {
        $m = Db::table("s_k_company");
        $returnRes=$m->field("cid as value,cname as label")->select();
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
    
    public function i_u_company()
    {
        $res = $this->request->post();
        if(!empty($res['cid'])){
            $returnRes = Db::table('s_k_company')->where(array("cid"=>$res['cid']))->update($res);
        }else{
            $returnRes = Db::table("s_k_company")->data($res)->insert();
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
     * 分页查询公司
     */
    public function f_company()
    {
        $res = $this->request->post();
        $n = $res['n'];
        if(empty($n)){
            $n=1;
        }
        $pageSize=10;
        $m = Db::table("s_k_company");
        $pageTotal= $m->count();
        $list=$m->limit(($n-1)* $pageSize,$pageSize)->select();
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
    
    public  function  getpostone()
    {
        $res = $this->request->post();
        $a= Db::table("s_k_posts_one")->where(array("poid",$res['postid']))->find();
        $a["psolve"]=html_entity_decode($a["psolve"]);
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
    
    /**
     * 增加或修补充
     */
    public function  i_u_oneposts()
    {
        $res = $this->request->post();
        $cookie = input('cookie.');
        if(!empty($res["poid"])){

            $a= Db::table("s_k_posts_one")->where(array("poid"=>$res["poid"]))->update($res);
            $this->i_customer_log($res['customer_id'],4, $res["poid"],"update","补充'".$res["ptitie"]."'",$res["createtime"]);
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
        $res['customer_id'] = Db::table('s_k_customer_service')->where(array("cuuid"=>$cookie['k_cuuid']))->column("sid");
        $res["createtime"]= date('Y-m-d H:i:s');
        $a= Db::table("s_k_posts_one")->insert($res);
        //  '日志类型 1/接待记录.2/来电记录,3/问题,4/问答',
        $this->i_customer_log($res['customer_id'],4, $a,"insert","补充'".$res["ptitie"]."'",$res["createtime"]);
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

 
    /**
     * 增加或修改接待记录
     */
	public function i_u_reception()
	{
	    $res = $this->request->post();
	    if(!empty($res["rid"])){
	        $res["s_start"]= 2;
	        Db::table("s_k_reception")->where(array("rid"=>$res["rid"]))->update($res);
	    }
	    $res["s_start"]= 1;
	    $res["createtime"]= date('Y-m-d H:i:s');
	 //   $res["one_customer_id"]
	   $a= Db::table("s_k_reception")->where("rid")->insert($res);
	    //  '日志类型 1/接待记录.2/来电记录,3/问题,4/问答',
	    $this->i_customer_log($res['customer_id'],1, $a,"insert","接待客户'".$res["username"]."'",$res["createtime"]);
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


    /**
     * 删除接待数据
     */
	public function  del_reception()
	{
	    $res = $this->request->post();
	    if(empty($res['reception_id'])){
	        $this->array_return['ResultType'] = self::__ERROR2__;
	        $this->array_return['Message'] = "参数缺失";
	        return json($this->array_return);
	    }
	    $m = Db::table("s_k_reception")->where(array("rid"=>$res['reception_id']))->delete();
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
     * 分页查询接待数据
     */
	public function f_reception()
	{
		$res = $this->request->post();
	    $n = $res['n'];
	    if(empty($n)){
	        $n=1;
	    }
	
	    $map = array();
	    $cookie=I("cookie.");
	    
	    if(!empty($res['ismy'])){
	    	$map['b.cuuid']=$cookie['k_cuuid'];
	    }
	    if(!empty($res['starttime'])){
	        $map['a.createtime']=array('GT',$res['starttime']);
	    }
	    if(!empty($res['starttime'])){
	        $map['a.createtime']=array('EGT',$res['endtime']);
	    }
	    if(!empty($res['username'])){
	        $map['a.username']=array('like',"%".$res['username']."%");
	    }
	    if(!empty($res['lr_customer_name'])){
	        $map['c.cname']=array('like',"%".$res['lr_customer_name']."%");
	    }
	    if(!empty($res['userreferee'])){
	        $map['a.userreferee']=array('like',"%".$res['userreferee']."%");
	    }
	    if(!empty($res['companyid'])){
	          $map['a.companyid'] = $res['companyid'];
	    }
	
	    $pageSize = 10;
	    $m = Db::table("s_k_reception");
	    $pageTotal = $m->alias("a")->leftJoin("s_k_customer_service b","a.one_customer_id=b.sid")
	        ->leftJoin("s_k_customer_service c","a.two_customer_id=c.sid")->where($map)->count();
	    $list = $m->alias("a")->leftJoin("s_k_customer_service b","a.one_customer_id=b.sid")
	        ->leftJoin("s_k_customer_service c","a.two_customer_id=c.sid")
	        ->field(" a.*,b.cname as bcname,b.caccountnumber as bcaccountnumber,c.cname as ccname,c.caccountnumber as ccaccountnumber")
	    ->where($map)->order('createtime desc')->limit(($n-1)* $pageSize,$pageSize)->select();
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
     * 插入修改日志
     */
    public function i_u_customer_log()
    {
        $res = $this->request->post();
        if (!empty($res["lid"])) {
            Db::table("s_k_customer_log")->where(array("lid"=>$res["lid"]))->update($res);
        }
        $res["createtime"]= date('Y-m-d H:i:s');
        $log = Db::table("s_k_customer_log")->insert($res);
        if ($log) {
            $this->array_return['ResultType'] =1;
            $this->array_return['Message'] = "log add success";
            return json($this->array_return);
        }
    }

    public function i_customer_log($sid,$logtype,$operation_data_id,$operation_type,$cdis,$createtime)
    {
        $datas = array("sid"=>$sid,"logtype"=>$logtype,"operation_data_id"=>$operation_data_id,"operation_type"=>$operation_type,"cdis"=>$cdis,"createtime"=>$createtime);
        $log = Db::table("s_k_customer_log")->insert($datas);
    }

    /**
     * 分页查询日志
     */
    public function f_log_list()
    {
        $res = $this->request->post();
        $n = $res['n'];
        if(empty($n)){
            $n=1;
        }

        $map = array();
        if(!empty($res['cdis'])){
            $map['a.cdis']=array('like',"%".$res['cdis']."%");
        }
        if(!empty($res['logtype'])) {
            $map['a.logtype'] =  $res['logtype'];
        }

        $pageSize=5;
        $m = Db::table("s_k_customer_log");
        $pageTotal= $m->alias("a")->leftJoin("s_k_customer_service b","a.sid=b.sid")->where($map)->count();
        $list=$m->alias("a")->leftJoin("s_k_customer_service b","a.sid=b.sid")
            ->field("a.*,b.cname")->
        where($map)->order('createtime desc')->limit(($n-1)* $pageSize,$pageSize)->select();
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


	public function find_reception_by_id()
	{
	    $res = $this->request->post();
	    $returns = Db::table("s_k_reception")->alias("a")->
	    join("left join s_k_customer_service b","a.one_customer_id=b.sid")
	        ->leftJoin("s_k_customer_service c","a.two_customer_id=c.sid")
	        ->leftJoin("s_k_customer_service d","a.lr_customer_id=c.sid")
	        ->leftJoin("s_k_company f","a.companyid=f.cid")
	        ->field(" a.*,b.cname as bcname,b.caccountnumber as bcaccountnumber,c.cname as ccname,c.caccountnumber as ccaccountnumber,b.cname as bcname,b.caccountnumber as lr_customer_name,f.cname as fcname")->
	    where(array("a.rid"=>$res['rid']))->select();
	    if ($returns) {
	        $this->array_return['ResultType'] = self::__OK__;
	        $this->array_return['Message'] = "操作成功";
	        $this->array_return['AppendData'] = $returns;
	    } else {
	        $this->array_return['ResultType'] = self::__ERROR2__;
	        $this->array_return['Message'] = "操作失败";
	    }
	
	    return json($this->array_return);
	}
	
    /**
     * 接待记录录入
     */
	 public function i_reception()
	 {
	     $res = $this->request->post();
	     $cookie=I("cookie.");
	    
	     //查找录入id
	     if(!empty($res['operationtype']))
	     {
	         $res['two_customer_id'] = Db::table('s_k_customer_service')->where(array("cuuid"=>$cookie['k_cuuid']))->column("sid");
	         $data=array("two_dis"=>$res['two_dis'],"two_customer_id"=> $res['two_customer_id'],"two_job_img"=>$res['two_job_img'],"two_result"=>$res["two_result"],
	             "two_time"=>date('Y-m-d H:i:s'),"s_start"=>2);
	         $returns = Db::table("s_k_reception")->where(array("rid"=>$res['rid']))->update($data);
	         //  '日志类型 1/接待记录.2/来电记录,3/问题,4/问答',
	         $res["username"] = Db::table("s_k_reception")->where(array("rid"=>$res['rid']))->column("username");
	         $this->i_customer_log($res['two_customer_id'],1, $returns,"update","送走客户'".$res["username"]."'",date('Y-m-d H:i:s'));
	         if ($returns) {
	             return json($this->array_return);
	             $this->array_return['ResultType'] = self::__OK__;
	             $this->array_return['Message'] = "操作成功";
	             $this->array_return['AppendData'] = $returns;
	         } else {
	             $this->array_return['ResultType'] = self::__ERROR2__;
	             $this->array_return['Message'] = "操作失败";
	         }
	
	         return json($this->array_return);
	     }
	     $res["one_time"]= date('Y-m-d H:i:s');
	     $res["createtime"]= date('Y-m-d H:i:s');
	     $res["s_start"]= 1;
	     $res['one_customer_id'] = Db::table('s_k_customer_service')->where(array("cuuid"=>$cookie['k_cuuid']))->column("sid");
	     $returns = Db::table('s_k_reception')->data($res)->insert();
	     //  '日志类型 1/接待记录.2/来电记录,3/问题,4/问答',
	     $this->i_customer_log($res['one_customer_id'],1, $returns,"insert","接待客户'".$res["username"]."'",$res["createtime"]);
	     if ($returns) {
	         return json($this->array_return);
	         $this->array_return['ResultType'] = self::__OK__;
	         $this->array_return['Message'] = "操作成功";
	         $this->array_return['AppendData'] = $returns;
	     } else {
	         $this->array_return['ResultType'] = self::__ERROR2__;
	         $this->array_return['Message'] = "操作失败"; 
	     }
	
	     return json($this->array_return);
	 }


	 public function find_customer_servive(){
	     $m = Db::table("s_k_customer_service")->field("sid as value,cname as label")->where(array("c_start"=>2))->select();
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
     * 通过名字查询
     */
	  public  function  find_customer_service_byname()
	  {
	      $res = $this->request->post();
	      $n = $res['name'];
	      if(empty($n)){
	          $this->array_return['ResultType'] = self::__ERROR2__;
	          $this->array_return['Message'] = "操作失败";
	          return json($this->array_return);
	      }
	      $map = array();
	      if(!empty($res['name'])){
	          $map['cname']=array('like',"%".$res['name']."%");
	      }
	
	      $map['c_start'] =2;
	       $m = Db::table("s_k_customer_service")->field("sid,cname")->where($map)->select();
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
     * 分页查询客服
     */
   public  function  f_customer_service_list()
   {
       $res = $this->request->post();
       $n = $res['n'];
       if(empty($n)){
           $n=1;
       }

       $map = array();
       if(!empty($res['cname'])){
           $map['cname']=array('like',"%".$res['cname']."%");
       }
       if(!empty($res['caccountnumber'])) {
           $map['caccountnumber'] = array('like', "%".$res['caccountnumber']."%");
       }
       if(!empty($res['cphone'])) {
           $map['cphone'] = array('like', "%".$res['cphone']."%");
       }

       $pageSize=5;
       $m = Db::table("s_k_customer_service");
       $pageTotal= $m->where($map)->count();
       $list=$m->where($map)->order('createtime desc')->limit(($n-1)* $pageSize,$pageSize)->select();
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
     * 增加和修改客服
     */
    public  function  i_u_customer_service()
    {
        $res = $this->request->post();
        if(!empty($res["sid"])){
            $res["cpwd"]=md5($res["cpwd"]."javatom");
            $usre  = Db::table("s_k_customer_service")->where(array("sid"=>$res["sid"]))->update($res);
            $this->array_return['AppendData'] = $usre;
            $this->array_return['ResultType'] =1;
            $this->array_return['Message'] = "user update success";
            return json($this->array_return);
        }

        $res["cpwd"]=md5($res["cpwd"]."javatom");
        $res["cuuid"]= getRandomString(12);
        $res['createtime'] = date('Y-m-d H:i:s');
        $res['logintime'] = date('Y-m-d H:i:s');

        $usre  = Db::table("s_k_customer_service")->insert($res);
        $this->array_return['AppendData'] = $usre;
        $this->array_return['ResultType'] =1;
        $this->array_return['Message'] = "user add success";
        return json($this->array_return);

    }

    /**
     * 客服登录
     */
    public function  k_longin()
    {
        $res = $this->request->post();
        if(empty($res["caccountnumber"])){
            $this->array_return['ResultType'] = self::__ERROR2__;
            $this->array_return['Message'] = "参数缺少";
            return json($this->array_return);
        }
        $usre  = Db::table("s_k_customer_service")->field("cname,cuuid,ctopimgurl,logintime,cpwd")->where(array("caccountnumber"=>$res["caccountnumber"],"c_start"=>"2"))->find();
        if(empty($usre)){
            $this->array_return['ResultType'] = 0;
            $this->array_return['Message'] = "account number err";
            return json($this->array_return);
        }else if($usre["cpwd"]==md5($res["cpwd"]."javatom")){
            Db::table("s_k_customer_service")->where(array("caccountnumber"=>$res["caccountnumber"]))->update(array("logintime"=>date('Y-m-d H:i:s')));
            $usre["cpwd"]='';
            $this->array_return['AppendData'] = $usre;
            $this->array_return['ResultType'] =1;
            $this->array_return['Message'] = "login success";
            return json($this->array_return);
        }else{
            $this->array_return['AppendData'] = true;
            $this->array_return['ResultType'] =2;
            $this->array_return['Message'] = "password err";
            return json($this->array_return);
        }
    }
    
}