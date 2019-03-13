<?php
namespace app\api\controller;
use think\Controller;
use think\Db;

class AgencyWeb extends Controller 
{
    const __OK__ = '0'; //请求成功
    const __ERROR__ = '1'; //参数错误
    const __ERROR1__ = '2'; //没有绑定
    const __ERROR2__ = '3';//数据库错误
    private $array_return = array("ResultType"=>1,"Message"=>"success","AppendData"=>"");
    public  $userInfo;
    
    //public (功能)
    public function initialize()
    {
        $act_name = strtolower($this->request->action());
        $ctr_name = strtolower($this->request->controller());
        if(!in_array($act_name,config('No_Verification')[$ctr_name])) 
        {
            $uuid = $_COOKIE['wapuuid'];
            $waptoken = $_COOKIE["waptoken"];
            if(empty($uuid) || empty($waptoken))
            {
                $this->array_return['ResultType'] = self::__ERROR__;
                $this->array_return['Message'] = "token not find";
                $this->array_return['AppendData'] = null;
                return json($this->array_return);
            }

            $uinfo = Db::table("s_api_nagent_user")->where(array("nagent_uuid"=>$uuid,"nagent_start"=>1))->find();
            if(empty($uinfo))
            {
                $this->array_return['ResultType'] = self::__ERROR__;
                $this->array_return['Message'] = "Invalid token";
                $this->array_return['AppendData'] = null;
                return json($this->array_return);
            }
            if(md5($uinfo["nagent_uuid"].$uinfo["timestamp"]."javatom")!=$waptoken)
            {
                $this->array_return['ResultType'] = self::__ERROR__;
                $this->array_return['Message'] = "Invalid token";
                $this->array_return['AppendData'] = null;
                return json($this->array_return);
            }
            
            $this->userInfo = $uinfo;
        }
    }

    /**
     * 得到百度token
     */
    public function get_baidu_token()
    {
        $yml = Db::table("s_api_yml")->where(array("id"=>1))->find();
        if(intval($yml["baidutoken_sign_in"])>strtotime("-25 day")){
            return $yml["baidutoken"];
        }else{
            return $this->get_baidu_token1();
        }
    }

    /**
     * 得到百度token
     */
    public function get_baidu_token1()
    {
        $url = 'https://aip.baidubce.com/oauth/2.0/token';
        $post_data['grant_type']       = 'client_credentials';
        $post_data['client_id']      = '9V1hzM4F25TFcpGwbjwEVHtd';
        $post_data['client_secret'] = 'RcHzQBXm8QTLP0oLnyWEEYx9I8KY7Um2';
        $res = https_request($url, $post_data);
        $res = json_decode($res,true);
        $yml = Db::table("s_api_yml")->where(array("id"=>1))->update(array("baidutoken"=> $res["access_token"],"baidutoken_sign_in"=>time()));
         
        return $res["access_token"];
    }

    /**
     * 删除
     */
    public function del_agency_shengqi_draft()
    {
        $res = $this->request->post();
        if (empty($res["pojouuid"])) {
            $this->array_return['ResultType'] = self::__ERROR2__;
            $this->array_return['Message'] = "非法访问";
            return json($this->array_return);
        }
        $m = Db::table("s_api_nagent_apply_draft")->where(array("uuid" => $res["pojouuid"]))->delete();
        
        $this->kkajaxReturn($m);
    }
    
    /**
     * 分页查找我的合同
     *
     */
    public function f_contract()
    {
        $res = $this->request->post();
        $n = $res['n'] ? $res['n'] : 1;
        
        $map = array();
        if (!empty($res['contract_id'])) {
            $map['a.contract_id'] = array('like', "%" . $res['contract_id'] . "%");
        }
        if (!empty($res['contract_type_id'])) {
            $map['a.contract_type_id'] = $res['contract_type_id'];
        }
        if (!empty($res['purchaser_id'])) {
            $map['a.purchaser_id'] = $res['purchaser_id'];
        }
        if (!empty($res['start'])) {
            $map['a.start'] = $res['start'];
        }
        if(!empty($res['using_id'])){
            $map['a.using_id'] = $res['using_id'];
        }
        
        $map["purchaser_id"] = $this->userInfo["nid"];
        $pageSize = 10;
        $m = Db::table("s_api_contract");
        $pageTotal = $m->alias("a")->leftJoin("s_api_nagent_user as b","a.purchaser_id=b.nid")->
        join("left join s_api_nagent_apply as c","a.using_id=c.nid")->
        field("a.*,b.nagent_name,c.nagent_name as cnagent_name")->where($map)->count();
        
        $list = $m->alias("a")->leftJoin("s_api_nagent_user as b","a.purchaser_id=b.nid")->
        join("left join s_api_nagent_apply as c","a.using_id=c.nid")->field("a.*,b.nagent_name as using_name,c.nagent_name as purchaser_name")
            ->where($map)->order('nid desc')->limit(($n - 1) * $pageSize, $pageSize)->select();

        $datalist = array("list" => $list, "pageTotal" => $pageTotal, "pageSize" => $pageSize, "pageNum" => $n);
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
     * 根据uuid查找error_list
     */
    public function init_error_list()
    {
       $res = $this->request->post();
       $uuid = $res["uuid"];
       if(empty($uuid))
       {
           $this->array_return['ResultType'] = self::__ERROR2__;
           $this->array_return['Message'] = "非法访问";
           return json($this->array_return);
       }
       
       $m = Db::table("s_api_audit_error_log")->alias("a")->leftJoin("s_api_nagent_apply b","a.nagent_apply_id=b.nid")->
       field("a.dis")-> where(array("b.uuid"=>$uuid))->select();
       
       $this->kkajaxReturn($m);
    }
    
    /**
     *根据uuid查找agent
     */
    public function find_agent_by_uuid()
    {
        $res = $this->request->post();
        $uuid = $res["uuid"];
        if(empty($uuid))
        {
            $this->array_return['ResultType'] = self::__ERROR2__;
            $this->array_return['Message'] = "非法访问";
            return json($this->array_return);
        }
        
        $m = Db::table("s_api_nagent_apply")->alias("a")->leftJoin("s_api_contract b","a.contract_number=b.nid")->
        field("a.*,b.contract_id as contract_name")-> where(array("a.uuid"=>$uuid))->find();
        
        $this->kkajaxReturn($m);
    }

    /**
     * 代理商修改
     */
    public  function u_apply_agent()
    {
        $res = $this->request->post();
        if(empty($res["contract_name"])){
            $this->array_return['ResultType'] = self::__ERROR2__;
            $this->array_return['Message'] = "合同编号不存在";
            return json($this->array_return);
        }
        $uuid=$_COOKIE['wapuuid'];
        $contract = Db::table("s_api_contract")->alias("a")->leftJoin("s_api_nagent_user as b","b.nid=a.purchaser_id")->
        where(array("a.contract_id"=>$res["contract_name"],"b.nagent_uuid"=>$uuid))->field("b.*,a.nid as anid,a.contract_id as acontract_id")->find();
        // $contract["nid"] 推荐人的id
        if($contract==null || $contract["nid"]=='' || $contract["nid"]==0){
            $this->array_return['ResultType'] = self::__ERROR2__;
            $this->array_return['Message'] = "合同编号不存在";
            return json($this->array_return);
        }
        if($contract["nagent_type"]=="创业家"){
            if((intval($contract["nagent_vip1_num"])<=0) || $res["nagent_type"]!="创投"){
                $this->array_return['ResultType'] = self::__ERROR2__;
                $this->array_return['Message'] = "推广名额已用 完";
                return json($this->array_return);
            }

        }
        if($contract["nagent_type"]=="创业导师"){
            if((intval($contract["nagent_vip2_num"])<=0) || $res["nagent_type"]!="创业家"){
                $this->array_return['ResultType'] = self::__ERROR2__;
                $this->array_return['Message'] = "推广名额已 用完";
                return json($this->array_return);
            }

        }
        if($contract["nagent_type"]=="创业领袖"){
            if((intval($contract["nagent_vip3_num"])<=0) || $res["nagent_type"]!="创业导师"){
                $this->array_return['ResultType'] = self::__ERROR2__;
                $this->array_return['Message'] = "推广名额 已用完";
                return json($this->array_return);
            }

        }
        $old_starte = Db::table("s_api_nagent_apply")->where(array("uuid"=>$res["uuid"]))->column("old_starte");//field("old_starte")

        if($old_starte==0){
            $this->array_return['ResultType'] = self::__ERROR2__;
            $this->array_return['Message'] = "保存  成功";
            return json($this->array_return);
        }

        $data=array(
            "nagent_name" => $res["nagent_name"],//'代理商姓名',
            "nagent_phone" => $res["nagent_phone"],// '代理商申请人手机号',
            "nagent_id" => $res["nagent_id"],// '代理商申请人ID',
            "nagent_wechar" => $res["nagent_wechar"],// '代理商微信',
            "nagent_type" => $res["nagent_type"],// '代理商类型',
            "nagent_milebox" =>  $res["nagent_milebox"],// '代理商邮箱',
            "nagent_back_account_name" => $res["nagent_back_account_name"],// '代理商银行开户名',
            "nagent_bank_name" => $res["nagent_bank_name"],// '开户行名',
            "nagent_bank_account" => $res["nagent_bank_account"],// '银行卡卡号',
            "remarks" => $res["remarks"],// '备注',
            "superior_name" => $res["superior_name"],//,
            "superior_phone" => $res["superior_phone"],// '推荐人手机号',
            "nagent_contracts" => json_encode($res["nagent_contracts"]),// '代理商合同图片地址集',
            "nagent_transfer_record" =>json_encode($res["nagent_transfer_record"]) ,// '转账图片记录集',
            "payment_voucher_img"=>json_encode($res["payment_voucher_img"]),
            "nagent_idimgs" =>json_encode($res["nagent_idimgs"]) ,// '代理商身份证正反面地址',
            "superior_type" => $res["superior_type"] ,// '代理商类型',
            "old_starte"=>0,
            "nstate" =>$old_starte,//
            "createtime"=> date('Y-m-d H:i:s'),//'创建时间',
            "nagent_dateofbirth"=>$res["nagent_dateofbirth"],//'代理商生日',
            "sex" =>$res["sex"],//int(4) DEFAULT NULL COMMENT '年龄',
            "study_assessment_img" =>json_encode($res["study_assessment_img"]),//varchar(200) DEFAULT '0',
            "expiration_of_contract_time" => date('Y-m-d H:i:s',strtotime('+1year')),//'合同到期时间',
            "contract_number" =>$contract["anid"],//int(11) DEFAULT NULL COMMENT '合同编号',
            "nagent_dis"=>$res["nagent_dis"],
            "nagent_type_id"=>$res["nagent_type_id"],//
            "superior_type_id"=>$res["superior_type_id"],//
            "superior_id" =>$contract["nid"],//'推荐人id',
        );
			
        Db::startTrans();  
        try {
        	$m = Db::table("s_api_nagent_apply")->where(array("uuid"=>$res["uuid"]))->update($data); 
            Db::table("s_api_contract")->where(array("nid"=>$res["contract_number"]))->update(array("start"=>3,"using_id"=>$m));
            $configuser=Db::table("s_api_nagent_config")->where(array("s_key_name"=>array("in","customerSP,customerSPphone")))->select();
            for($i=0;$i<count($configuser);$i++){
            	if($configuser[$i]["s_key_name"]=="customerSP"){
                    $customerSP=$configuser[$i]["s_key_value"];
                }else{
                    $customerSPphone=$configuser[$i]["s_key_value"];
                }
           }

           Db::commit();
           $this->Agency_send_msg(array("phone"=>$customerSPphone,
                    "content"=>$customerSP."先生/女士："."系统收到".$res["superior_name"]."为".$res["nagent_name"]."的申请已经修改完毕.请尽快处理.点击此链接可快速处理"
                   . "http://wx.yxm360.com/Agency/AgencyAdm/Agent_show/pojouuid/".$res["uuid"]
                ));
		} catch (\Exception $e) {
		    // 回滚事务
		    Db::rollback();
		}
        
        $this->kkajaxReturn($m);
    }

    /**
     * 升级代理代理增加
     */
    public  function i_apply_agent_goup()
    {
        $res = $this->request->post();
        if(empty($res["contract_number"])){
            $this->array_return['ResultType'] = self::__ERROR2__;
            $this->array_return['Message'] = "合同编号不存在";
            return json($this->array_return);
        }
        $uuid=$_COOKIE['wapuuid'];
        $contract = Db::table("s_api_contract")->alias("a")->leftJoin("s_api_nagent_user as b","b.nid=a.purchaser_id")->
        where(array("a.nid"=>$res["contract_number"],"b.nagent_uuid"=>$uuid))->field("b.*")->find();

        //根据同一个手机号，同一个等级搜索，看看数据库中是否存在
        $usercount = Db::table("s_api_nagent_apply")->where(array("nagent_phone"=>$res["nagent_phone"],"nagent_type"=>$res["nagent_type"]))->count();
        if($usercount>0){
            $this->array_return['ResultType'] = self::__ERROR2__;
            $this->array_return['Message'] = "同一个手机号不能申请两次";
            return json($this->array_return);
        }
        // $contract["nid"] 推荐人的id
        if($contract==null || $contract["nid"]=='' || $contract["nid"]==0)
        {
            $this->array_return['ResultType'] = self::__ERROR2__;
            $this->array_return['Message'] = "合同编号不存在";
            return json($this->array_return);
        }
        if($contract["nagent_type"]=="创业家")
        {
            if((intval($contract["nagent_vip1_num"])<=0) || $res["nagent_type"]!="创投"){
                $this->array_return['ResultType'] = self::__ERROR2__;
                $this->array_return['Message'] = "推广名额已用 完";
                return json($this->array_return);
            }

        }
        if($contract["nagent_type"]=="创业导师")
        {
            if((intval($contract["nagent_vip2_num"])<=0) || $res["nagent_type"]!="创业家"){
                $this->array_return['ResultType'] = self::__ERROR2__;
                $this->array_return['Message'] = "推广名额已 用完";
                return json($this->array_return);
            }
        }
        if($contract["nagent_type"]=="创业领袖")
        {
            if((intval($contract["nagent_vip3_num"])<=0) || $res["nagent_type"]!="创业导师"){
                $this->array_return['ResultType'] = self::__ERROR2__;
                $this->array_return['Message'] = "推广名额 已用完";
                return json($this->array_return);
            }

        }

        $data=array(
            "nagent_name" => $res["nagent_name"],//'代理商姓名',
            "nagent_phone" => $res["nagent_phone"],// '代理商申请人手机号',
            "nagent_id" => $res["nagent_id"],// '代理商申请人ID',
            "nagent_wechar" => $res["nagent_wechar"],// '代理商微信',
            "nagent_type" => $res["nagent_type"],// '代理商类型',
            "nagent_milebox" =>  $res["nagent_milebox"],// '代理商邮箱',
            "nagent_province" => $res["nagent_province"],// '代理商所在地区的省份',
            "nagent_area" => "",// '代理商地区',
            "nagent_city" => $res["nagent_city"],// '城市',
            "nagent_back_account_name" => $res["nagent_back_account_name"],// '代理商银行开户名',
            "nagent_bank_name" => $res["nagent_bank_name"],// '开户行名',
            "nagent_bank_account" => $res["nagent_bank_account"],// '银行卡卡号',
            "remarks" => $res["remarks"],// '备注',
            "superior_name" => $res["superior_name"],//,
            "superior_phone" => $res["superior_phone"],// '推荐人手机号',
            "nagent_contracts" => json_encode($res["nagent_contracts"]),// '代理商合同图片地址集',
            "nagent_transfer_record" =>json_encode($res["nagent_transfer_record"]) ,// '转账图片记录集',
            "payment_voucher_img"=>json_encode($res["payment_voucher_img"]),
            "nagent_idimgs" =>json_encode($res["nagent_idimgs"]) ,// '代理商身份证正反面地址',
            "superior_type" => $res["superior_type"] ,// '代理商类型',
            "old_starte"=>0,
            "nstate" =>6,//
            "createtime"=> date('Y-m-d H:i:s'),//'创建时间',
            "create_id"=>$this->userInfo["nid"],
            "three_id"=>0,
            "two_id"=>0,
            "one_id"=>0,// '第一次修改id',
            "nagent_dateofbirth"=>$res["nagent_dateofbirth"],//'代理商生日',
            "sex" =>$res["sex"],//int(4) DEFAULT NULL COMMENT '年龄',
            "study_assessment_img" =>json_encode($res["study_assessment_img"]),//varchar(200) DEFAULT '0',
            "expiration_of_contract_time" => date('Y-m-d H:i:s',strtotime('+1year')),//'合同到期时间',
            "contract_number" =>$res["contract_number"],//int(11) DEFAULT NULL COMMENT '合同编号',
            "payable_amount"=>0,// $int(11) DEFAULT '0' COMMENT '应交金额',
            "sj_payable_amount"=>0,//int(11) DEFAULT '0' COMMENT '实交金额',
            "uuid" => getRandomString(12),// '随机字符串',
            "nagent_dis"=>$res["nagent_dis"],
            "nagent_type_id"=>$res["nagent_type_id"],//
            "superior_type_id"=>$res["superior_type_id"],//
            "superior_id" =>$this->userInfo["nid"],//'推荐人id',
        );
         		
        Db::startTrans();
        try {
        	Db::table("s_api_nagent_apply")->insert($data);
            $m=Db::table("s_api_contract")->where(array("nid"=>$res["contract_number"]))->update(array("start"=>3,"using_id"=>$m));
            //名额
            if($contract["nagent_type"]=="创业家"){
                $m=Db::table("s_api_nagent_user")->where(array("nagent_uuid"=>$uuid))->setDec("nagent_vip1_num");
            }
            if($contract["nagent_type"]=="创业导师"){
                $m=Db::table("s_api_nagent_user")->where(array("nagent_uuid"=>$uuid))->setDec("nagent_vip2_num");
            }
            if($contract["nagent_type"]=="创业领袖"){
                $m=Db::table("s_api_nagent_user")->where(array("nagent_uuid"=>$uuid))->setDec("nagent_vip3_num");
            }

            $configuser = Db::table("s_api_nagent_config")->where(array("s_key_name"=>array("in","customerSP,customerSPphone")))->select();
            for($i=0;$i<count($configuser);$i++){
                if($configuser[$i]["s_key_name"]=="customerSP"){
                    $customerSP=$configuser[$i]["s_key_value"];
                }else{
                    $customerSPphone=$configuser[$i]["s_key_value"];
                }
            }

            Db::commit();  
            $this->Agency_send_msg(array("phone"=>$customerSPphone,
                    "content"=>$customerSP."先生/女士："."系统收到".
                        $res["superior_name"]."为".$res["nagent_name"]."提交的代理升级申请.请尽快处理.点击此链接可快速处理"
                        . "http://wx.yxm360.com/Agency/AgencyAdm/Agent_show/pojouuid/".$data["uuid"]
                ));
        } catch (\Exception $e) {
            Db::rollback();
        }
        $this->kkajaxReturn($m);
    }
    
    /**
     * 升级代理代理增加
     */
    public  function i_apply_agent_goup_draft()
    {
        $res = $this->request->post();
        $uuid=$_COOKIE['wapuuid'];
      
        $data=array(
            "nagent_name" => $res["nagent_name"],//'代理商姓名',
            "nagent_phone" => $res["nagent_phone"],// '代理商申请人手机号',
            "nagent_id" => $res["nagent_id"],// '代理商申请人ID',
            "nagent_wechar" => $res["nagent_wechar"],// '代理商微信',
            "nagent_type" => $res["nagent_type"],// '代理商类型',
            "nagent_milebox" =>  $res["nagent_milebox"],// '代理商邮箱',
            "nagent_province" => $res["nagent_province"],// '代理商所在地区的省份',
            "nagent_area" => "",// '代理商地区',
            "nagent_city" => $res["nagent_city"],// '城市',
            "nagent_back_account_name" => $res["nagent_back_account_name"],// '代理商银行开户名',
            "payment_voucher_img"=>json_encode($res["payment_voucher_img"]),
            "nagent_bank_name" => $res["nagent_bank_name"],// '开户行名',
            "nagent_bank_account" => $res["nagent_bank_account"],// '银行卡卡号',
            "remarks" => $res["remarks"],// '备注',
            "superior_name" => $res["superior_name"],//,
            "superior_phone" => $res["superior_phone"],// '推荐人手机号',
            "nagent_contracts" => json_encode($res["nagent_contracts"]),// '代理商合同图片地址集',
            "nagent_transfer_record" =>json_encode($res["nagent_transfer_record"]) ,// '转账图片记录集',
            "payment_voucher_img"=>json_encode($res["payment_voucher_img"]),
            "nagent_idimgs" =>json_encode($res["nagent_idimgs"]) ,// '代理商身份证正反面地址',
            "superior_type" => $res["superior_type"] ,// '代理商类型',
            "old_starte"=>0,
            "nstate" =>6,//
            "createtime"=> date('Y-m-d H:i:s'),//'创建时间',
            "create_id"=>$this->userInfo["nid"],
            "three_id"=>0,
            "two_id"=>0,
            "one_id"=>0,// '第一次修改id',
            "nagent_dateofbirth"=>$res["nagent_dateofbirth"],//'代理商生日',
            "sex" =>$res["sex"],//int(4) DEFAULT NULL COMMENT '年龄',
            "study_assessment_img" =>json_encode($res["study_assessment_img"]),//varchar(200) DEFAULT '0',
            "expiration_of_contract_time" => date('Y-m-d H:i:s',strtotime('+1year')),//'合同到期时间',
            "contract_number" =>$res["contract_number"],//int(11) DEFAULT NULL COMMENT '合同编号',
            "payable_amount"=>0,// $int(11) DEFAULT '0' COMMENT '应交金额',
            "sj_payable_amount"=>0,//int(11) DEFAULT '0' COMMENT '实交金额',
            "uuid" => getRandomString(12),// '随机字符串',
            "nagent_dis"=>$res["nagent_dis"],
            "nagent_type_id"=>$res["nagent_type_id"],//
            "superior_type_id"=>$res["superior_type_id"],//
            "superior_id" =>$this->userInfo["nid"],//'推荐人id',
        );
        $m=Db::table("s_api_nagent_apply_draft")->insert($data);
        $res = $m ?  $m : 0;
        
        $this->kkajaxReturn($res);
    }
    
    /**
     * 申请代理增加
     */
    public function i_apply_agent_draft()
    {
        $res = $this->request->post();
        $uuid = $_COOKIE['wapuuid'];  
        $data=array(
            "nagent_name" => $res["nagent_name"],//'代理商姓名',
            "nagent_phone" => $res["nagent_phone"],// '代理商申请人手机号',
            "nagent_id" => $res["nagent_id"],// '代理商申请人ID',
            "nagent_wechar" => $res["nagent_wechar"],// '代理商微信',
            "nagent_type" => $res["nagent_type"],// '代理商类型',
            "nagent_milebox" =>  $res["nagent_milebox"],// '代理商邮箱',
            "nagent_province" => $res["nagent_province"],// '代理商所在地区的省份',
            "nagent_area" => "",// '代理商地区',
            "nagent_city" => $res["nagent_city"],// '城市',
            "nagent_back_account_name" => $res["nagent_back_account_name"],// '代理商银行开户名',
            "nagent_bank_name" => $res["nagent_bank_name"],// '开户行名',
            "nagent_bank_account" => $res["nagent_bank_account"],// '银行卡卡号',
            "payment_voucher_img"=>json_encode($res["payment_voucher_img"]),
            "remarks" => $res["remarks"],// '备注',
            "superior_name" => $res["superior_name"],//,
            "superior_phone" => $res["superior_phone"],// '推荐人手机号',
            "nagent_contracts" => json_encode($res["nagent_contracts"]),// '代理商合同图片地址集',
            "nagent_transfer_record" =>json_encode($res["nagent_transfer_record"]) ,// '转账图片记录集',
            "nagent_idimgs" =>json_encode($res["nagent_idimgs"]) ,// '代理商身份证正反面地址',
            "superior_type" => $res["superior_type"] ,// '代理商类型',
            "old_starte"=>0,
            "nstate" =>1,//
            "createtime"=> date('Y-m-d H:i:s'),//'创建时间',
            "create_id"=>$this->userInfo["nid"],
            "three_id"=>0,
            "two_id"=>0,
            "one_id"=>0,// '第一次修改id',
            "nagent_dateofbirth"=>$res["nagent_dateofbirth"],//'代理商生日',
            "sex" =>$res["sex"],//int(4) DEFAULT NULL COMMENT '年龄',
            "study_assessment_img" =>json_encode($res["study_assessment_img"]),//varchar(200) DEFAULT '0',
            "expiration_of_contract_time" => date('Y-m-d H:i:s',strtotime('+1year')),//'合同到期时间',
            "contract_number" =>$res["contract_number"],//int(11) DEFAULT NULL COMMENT '合同编号',
            "payable_amount"=>0,// $int(11) DEFAULT '0' COMMENT '应交金额',
            "sj_payable_amount"=>0,//int(11) DEFAULT '0' COMMENT '实交金额',
            "uuid" => getRandomString(12),// '随机字符串',
            "nagent_dis"=>$res["nagent_dis"],
            "nagent_type_id"=>$res["nagent_type_id"],//
            "superior_type_id"=>$res["superior_type_id"],//
            "superior_id" =>$this->userInfo["nid"],//'推荐人id',
        );

        $m = Db::table("s_api_nagent_apply_draft")->insert($data);
        $res = $m ? $m : 0;
        
        $this->kkajaxReturn($res);
    }

    /**
     * 申请代理增加
     */
    public function i_apply_agent()
    {
        $res = $this->request->post();
        if(empty($res["contract_number"])){
            $this->array_return['ResultType'] = self::__ERROR2__;
            $this->array_return['Message'] = "合同编号 不存在";
            return json($this->array_return);
        }
        $uuid=$_COOKIE['wapuuid'];
        $contract = Db::table("s_api_contract")->alias("a")->leftJoin("s_api_nagent_user as b","b.nid=a.purchaser_id")->
        where(array("a.nid"=>$res["contract_number"],"b.nagent_uuid"=>$uuid))->field("b.*")->find();

        //根据同一个手机号，同一个等级搜索，看看数据库中是否存在
        $usercount = Db::table("s_api_nagent_apply")->where(array("nagent_phone"=>$res["nagent_phone"],"nagent_type"=>$res["nagent_type"]))->count();

        if($usercount>0){
            $this->array_return['ResultType'] = self::__ERROR2__;
            $this->array_return['Message'] = "同一个手机号不能申请两次";
            return json($this->array_return);
        }
       // $contract["nid"] 推荐人的id
        if($contract==null || $contract["nid"]=='' || $contract["nid"]==0){
            $this->array_return['ResultType'] = self::__ERROR2__;
            $this->array_return['Message'] = "合同 编号不存在";
            return json($this->array_return);
        }
        if($contract["nagent_type"]=="创业家"){
            if((intval($contract["nagent_vip1_num"])<=0) || $res["nagent_type"]!="创投"){
                $this->array_return['ResultType'] = self::__ERROR2__;
                $this->array_return['Message'] = "推广名额已用 完";
                return json($this->array_return);
            }
        }
        if($contract["nagent_type"]=="创业导师"){
            if((intval($contract["nagent_vip2_num"])<=0) || $res["nagent_type"]!="创业家"){
                $this->array_return['ResultType'] = self::__ERROR2__;
                $this->array_return['Message'] = "推广名额已 用完";
                return json($this->array_return);
            }
        }
        if($contract["nagent_type"]=="创业领袖"){
            if((intval($contract["nagent_vip3_num"])<=0) || $res["nagent_type"]!="创业导师"){
                $this->array_return['ResultType'] = self::__ERROR2__;
                $this->array_return['Message'] = "推广名额 已用完";
                return json($this->array_return);
            }
        }
        
        $data=array(
             "nagent_name" => $res["nagent_name"],//'代理商姓名',
              "nagent_phone" => $res["nagent_phone"],// '代理商申请人手机号',
              "nagent_id" => $res["nagent_id"],// '代理商申请人ID',
              "nagent_wechar" => $res["nagent_wechar"],// '代理商微信',
              "nagent_type" => $res["nagent_type"],// '代理商类型',
              "nagent_milebox" =>  $res["nagent_milebox"],// '代理商邮箱',
              "nagent_province" => $res["nagent_province"],// '代理商所在地区的省份',
              "nagent_area" => "",// '代理商地区',
              "nagent_city" => $res["nagent_city"],// '城市',
              "nagent_back_account_name" => $res["nagent_back_account_name"],// '代理商银行开户名',
              "nagent_bank_name" => $res["nagent_bank_name"],// '开户行名',
              "nagent_bank_account" => $res["nagent_bank_account"],// '银行卡卡号',
              "remarks" => $res["remarks"],// '备注',
              "superior_name" => $res["superior_name"],//,
              "superior_phone" => $res["superior_phone"],// '推荐人手机号',
              "nagent_contracts" => json_encode($res["nagent_contracts"]),// '代理商合同图片地址集',
              "nagent_transfer_record" =>json_encode($res["nagent_transfer_record"]) ,// '转账图片记录集',
              "nagent_idimgs" =>json_encode($res["nagent_idimgs"]) ,// '代理商身份证正反面地址',
              "payment_voucher_img"=>json_encode($res["payment_voucher_img"]),
              "superior_type" => $res["superior_type"] ,// '代理商类型',
              "old_starte"=>0,
              "nstate" =>1,//
              "createtime"=> date('Y-m-d H:i:s'),//'创建时间',
              "create_id"=>$this->userInfo["nid"],
              "three_id"=>0,
              "two_id"=>0,
              "one_id"=>0,// '第一次修改id',
              "nagent_dateofbirth"=>$res["nagent_dateofbirth"],//'代理商生日',
              "sex" =>$res["sex"],//int(4) DEFAULT NULL COMMENT '年龄',
              "study_assessment_img" =>json_encode($res["study_assessment_img"]),//varchar(200) DEFAULT '0',
              "expiration_of_contract_time" => date('Y-m-d H:i:s',strtotime('+1year')),//'合同到期时间',
              "contract_number" =>$res["contract_number"],//int(11) DEFAULT NULL COMMENT '合同编号',
              "payable_amount"=>0,// $int(11) DEFAULT '0' COMMENT '应交金额',
              "sj_payable_amount"=>0,//int(11) DEFAULT '0' COMMENT '实交金额',
              "uuid" => getRandomString(12),// '随机字符串',
              "nagent_dis"=>$res["nagent_dis"],
              "nagent_type_id"=>$res["nagent_type_id"],//
              "superior_type_id"=>$res["superior_type_id"],//
              "superior_id" =>$this->userInfo["nid"],//'推荐人id',
        );
        $m=Db::table("s_api_nagent_apply")->insert($data);
        if($m){
            $m=Db::table("s_api_contract")->where(array("nid"=>$res["contract_number"]))->update(array("start"=>3,"using_id"=>$m));
            //名额
            if($contract["nagent_type"]=="创业家"){
                $m=Db::table("s_api_nagent_user")->where(array("nagent_uuid"=>$uuid))->setDec("nagent_vip1_num");
            }
            if($contract["nagent_type"]=="创业导师"){
                $m=Db::table("s_api_nagent_user")->where(array("nagent_uuid"=>$uuid))->setDec("nagent_vip2_num");
            }
            if($contract["nagent_type"]=="创业领袖"){
                $m=Db::table("s_api_nagent_user")->where(array("nagent_uuid"=>$uuid))->setDec("nagent_vip3_num");
            }

            $configuser=Db::table("s_api_nagent_config")->where(array("s_key_name"=>array("in","customerSP,customerSPphone")))->select();
            for($i=0;$i<count($configuser);$i++){
                if($configuser[$i]["s_key_name"]=="customerSP"){
                    $customerSP=$configuser[$i]["s_key_value"];
                }else{
                    $customerSPphone=$configuser[$i]["s_key_value"];
                }
            }

            $this->Agency_send_msg(array(
                     "phone"=>$customerSPphone,
                     "content"=>$customerSP."先生/女士："."系统收到".
                         $res["superior_name"]."为".$res["nagent_name"]."提交的代理申请.请尽快处理.点击此链接可快速处理"
                     ."http://wx.yxm360.com/Agency/AgencyAdm/Agent_show/pojouuid/".$data["uuid"]
                 ));
                $model->commit();

        }else{
            $model->rollback();
        }
        
        $this->kkajaxReturn($m);
    }

    /**
     * 代理商发送短信
     */
    public function Agency_send_msg($option=array())
    {
        $smsapi = "http://47.95.217.41:8088/";
        $user = "shmingxin"; //短信平台帐号
        $pass = 'shmingxin123321'; //短信平台密码
        $sendurl = $smsapi."sms.aspx?action=send&userid=12&&account=".$user."&password=".$pass."&mobile=".$option['phone']."&content=".$option['content']."&sendTime=&extno=";
        $result =simplexml_load_string(file_get_contents($sendurl)) ;
        return(String)$result;
    }

    /**
     * 初始化已部分数据
     */
    public function init_agent_open_date()
    {
        $uuid=$_COOKIE['wapuuid'];
        $res = $this->request->post();
        $contractlist = Db::table("s_api_contract")->alias("a")->leftJoin("s_api_nagent_user as b","b.nid=a.purchaser_id")
           ->
            where(array("b.nagent_uuid"=>$uuid,"a.start"=>2)) ->field("a.nid,a.contract_id")->select();
                $user=array("nagent_name"=>$this->userInfo["nagent_name"],
                    "nagent_type"=>$this->userInfo["nagent_type"],
                    "nagent_phone"=>$this->userInfo["nagent_phone"],
                    "nagent_type_id"=>$this->userInfo["nagent_type_id"],
                    "baidutoken"=>$this->get_baidu_token(),
                );

                if(!empty($res["pojouuid"])){
                    $m = Db::table("s_api_nagent_apply_draft")->where(array("uuid"=>$res["pojouuid"]))->find();
                }

        $this->kkajaxReturn(array("user"=>$user,"contractlist"=>$contractlist,"obj"=>$m));
    }
    
    /**
     * 得到我的申请列表草稿箱
     */
    public function my_agent_list_draft()
    {
        $res = $this->request->post();
        $n = $res['n'];
        if(empty($n)){
            $n=1;
        }
        $map = array();
        if(!empty($res['nagent_name'])){
            $map['a.nagent_name']=array('like',"%".$res['nagent_name']."%");
        }
        if(!empty($res['nagent_phone'])){
            $map['a.nagent_phone']=array('like',"%".$res['nagent_phone']."%");
        }
        $uuid=$_COOKIE['wapuuid'];
        $map['c.nagent_uuid']=$uuid;
        $pageSize=10;
        $m = Db::table("s_api_nagent_apply_draft");
        $pageTotal= $m->alias("a")
            ->leftJoin("s_api_contract b","a.contract_number=b.nid")
            ->leftJoin("s_api_nagent_user c","a.superior_id=c.nid")
            ->where($map)->count();
        $list=$m->alias("a")->leftJoin("s_api_contract b","a.contract_number=b.nid")
            ->leftJoin("s_api_nagent_user c","a.superior_id=c.nid")
            ->field("a.nagent_name,a.nagent_phone,a.nagent_type,a.superior_name,a.superior_phone,b.contract_id as contract_name,a.nstate,a.uuid")->
            where($map)->order('a.createtime desc')->limit(($n-1)* $pageSize, $pageSize)->select();;
        $datalist=array("list"=>$list,"pageTotal"=>$pageTotal,"pageSize"=>$pageSize,"pageNum"=>$n);
        $this->kkajaxReturn($datalist);
    }
    
    /**
     * 得到我的申请列表
     */
    public function my_agent_list()
    {
        $res = $this->request->post();
        $n = $res['n'];
        if(empty($n)){
            $n=1;
        }
        $map = array();
        if(!empty($res['nagent_name'])){
            $map['a.nagent_name']=array('like',"%".$res['nagent_name']."%");
        }
        if(!empty($res['nagent_phone'])){
            $map['a.nagent_phone']=array('like',"%".$res['nagent_phone']."%");
        }
        $uuid=$_COOKIE['wapuuid'];
        $map['c.nagent_uuid']=$uuid;
        $pageSize=10;
        $m = Db::table("s_api_nagent_apply");
        $pageTotal= $m->alias("a")
            ->leftJoin("s_api_contract b","a.contract_number=b.nid")
            ->leftJoin("s_api_nagent_user c","a.superior_id=c.nid")
            ->where($map)->count();
        $list=$m->alias("a")->leftJoin("s_api_contract b","a.contract_number=b.nid")
            ->leftJoin("s_api_nagent_user c","a.superior_id=c.nid")
            ->field("a.nagent_name,a.nagent_phone,a.nagent_type,a.superior_name,a.superior_phone,b.contract_id as contract_name,a.nstate,a.uuid")->
            where($map)->order('a.createtime desc')->limit(($n-1)* $pageSize, $pageSize)->select();;
        $datalist=array("list"=>$list,"pageTotal"=>$pageTotal,"pageSize"=>$pageSize,"pageNum"=>$n);
        $this->kkajaxReturn($datalist);
    }
    
    /**
     * 初始化个人中心
     */
    public function init_vip_center()
    {
       $a=array("nagent_name"=>$this->userInfo["nagent_name"],
           "nagent_type"=>$this->userInfo["nagent_type"],
           "nagent_account"=>$this->userInfo["nagent_account"]
           );
        $this->kkajaxReturn($a);
    }

    /**
     * 修改密码
     */
    public function changPwdByuuid()
    {
        $uuid=$_COOKIE['wapuuid'];
        $res = $this->request->post();
        $pwd = $res["pwd"];
        if(empty($pwd)||empty($uuid)){
        $this->array_return['ResultType'] = self::__ERROR2__;
        $this->array_return['Message'] = "非法访问";
        return json($this->array_return);
        }
        $m = Db::table("s_api_nagent_user")->where(array("nagent_uuid"=>$uuid))->update(array("nagent_pwd"=>$pwd));
        $this->kkajaxReturn($m);
    }
    
    /**
     * 修改密码
     */
    public function chang_pwd()
    {
        $res = $this->request->post();
        $phone = $res["phone"];
        $code = $res["code"];
        $pwd = $res["pwd"];
        if(empty($pwd)||empty($phone)||empty($code)){
            $this->array_return['ResultType'] = self::__ERROR2__;
            $this->array_return['Message'] = "非法访问";
            return json($this->array_return);
        }
        $codepojo = Db::table('s_mobile_code')->where(array("phone"=>$phone))->order("id desc")->find();
        if($codepojo==null){
            $this->array_return['ResultType'] = self::__ERROR2__;
            $this->array_return['Message'] = "非法访问";
            return json($this->array_return);
        }
        if($codepojo["code"]!=$code){
            $this->array_return['ResultType'] = self::__OK__;
            $this->array_return['Message'] = "操作成功";
            $this->array_return['AppendData'] = "验证码错误";
            return json($this->array_return);
        }
        $m = Db::table("s_api_nagent_user")->where(array("nagent_account"=>$phone))->update(array("nagent_pwd"=>$pwd));
        $this->kkajaxReturn($m);
    }

    /**
     * 判断是否代理手机号
     */
    public function is_agencyphone()
    {
        $res = $this->request->post();
        $phone = $res["phone"];
        if(empty($phone)){
            $this->array_return['ResultType'] = self::__ERROR2__;
            $this->array_return['Message'] = "非法访问";
            return json($this->array_return);
        }
        $user = Db::table("s_api_nagent_user")->where(array("nagent_account"=>$phone,"nagent_start"=>1))->find();
        if(empty($user)){
            $this->array_return['ResultType'] = self::__ERROR2__;
            $this->array_return['Message'] = "操作失败";
            return json($this->array_return);
        }else{
            $this->array_return['ResultType'] = self::__OK__;
            $this->array_return['Message'] = "操作成功";
            $this->array_return['AppendData'] = "";
        }
        
        return json($this->array_return);
    }
    
    /**
     * 代理登录
     */
    public  function agency_login()
    {
        $res = $this->request->post();
        $account = $res["account"];
        $pwd = $res["pwd"];
        if(empty($account) || empty($pwd)){
            $this->array_return['ResultType'] = self::__ERROR2__;
            $this->array_return['Message'] = "非法访问";
            return json($this->array_return);
        }
        $user = Db::table("s_api_nagent_user")->where(array("nagent_account"=>$account,"nagent_start"=>1))->find();
        if(empty($user)){
            $this->array_return['ResultType'] = self::__OK__;
            $this->array_return['Message'] = "操作成功";
            $this->array_return['AppendData'] = "用户不存在";
        }else if($user["nagent_pwd"]==$pwd){
            $s=time();
            Db::table("s_api_nagent_user")->where(array("nagent_uuid"=>$user["nagent_uuid"]))->update(array("timestamp"=>$s));
            $token=md5($user["nagent_uuid"].$s."javatom");
            $this->array_return['ResultType'] = self::__OK__;
            $this->array_return['Message'] = "操作成功";
            $this->array_return['AppendData'] = array("uuid"=>$user["nagent_uuid"],"token"=>$token);
        }else{
            $this->array_return['ResultType'] = self::__OK__;
            $this->array_return['Message'] = "操作成功";
            $this->array_return['AppendData'] = "密码错误";
        }
        
        return json($this->array_return);
    }

    /**
     * 重复代码
     * @param $datalist
     */
    function kkajaxReturn($datalist)
    {
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
}