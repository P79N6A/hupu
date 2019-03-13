<?php
namespace app\api\controller;
use Common\Controller\AgentApiBase;
use think\Controller;
use think\Db;
use app\api\model\ApiResources  as resourceModel;

class AgencySystem extends AgentApi
{

	private $resc_mod = null;
	 
 	function initialize()
 	{
 		parent::initialize();
        $this->resc_mod = new resourceModel();
    }
    
    public function user_by_uuid_changePwd(){
    }
    /**
     * 根据uuid查找用户基本信息
     */
    public function find_admin_by_uuid()
    {
        $res = $this->request->post();
        if (empty($res["uuid"])) {
            $this->array_return['ResultType'] = self::__ERROR2__;
            $this->array_return['Message'] = "非法访问";
            return json($this->array_return);
        }
        $m = Db::table("s_api_admin")->where(array("uuid"=>$res["uuid"]))->field("admin_account,bind_phone,uuid,sex,bind_wx,bind_mail,compulsory_downline,compulsory_downline,create_time,last_time,systeminfo")->find();
        $this->kkajaxReturn($m);
    }

    /**
     * 得到多有的find
     */
    public function find_all_role()
    {
        $list = Db::table("s_api_role")->field("role_name as label,role_id as value")->select();
        $this->kkajaxReturn($list);
    }

    /**
     * 分页查找管理者
     */
    public function f_admin()
    {
        $res = $this->request->post();
        $n = $res['n'];
        if(empty($n)){
            $n=1;
        }

        $map = array();
        if(!empty($res['admin_account'])){
            $map['a.admin_account']=array('like',"%".$res['admin_account']."%");
        }
        $pageSize=10;
        $m = Db::table("s_api_admin");
        $pageTotal= $m->alias("a")
            ->leftJoin("s_api_role b","a.role_id=b.role_id")->where($map)->count();
        $list=$m->alias("a")->leftJoin("s_api_role b","a.role_id=b.role_id")
            ->field("b.role_name,a.admin_id,a.admin_account,a.admin_pwd,a.bind_phone,a.uuid,a.sex,a.topimgurl,a.isfalg,a.role_id,a.create_time,a.last_time")->
            where($map)->order('a.create_time desc')->limit(($n-1)* $pageSize, $pageSize)->select();;
        $datalist=array("list"=>$list,"pageTotal"=>$pageTotal,"pageSize"=>$pageSize,"pageNum"=>$n);
        $this->kkajaxReturn($datalist);
    }

    /**
     * 增加管理者
     */
    public function i_u_admin()
    {
        $res = $this->request->post();
        if(empty($res["admin_id"])){

            $res['create_time'] = date('Y-m-d H:i:s');
            $res["uuid"]= getRandomString(10);
            $a = Db::table("s_api_admin")->data($res)->insert();

        }else{
            $datas=array("admin_id"=>$res['admin_id'],"admin_account"=>$res['admin_account'],
            "admin_pwd"=>$res["admin_pwd"],"bind_phone"=>$res["bind_phone"],
                "sex"=>$res["sex"],"bind_wx"=>$res["bind_wx"],
                "bind_mail"=>$res["bind_mail"],
                "topimgurl"=>$res["topimgurl"],
                "isfalg"=>$res["isfalg"],
                "role_id"=>$res["role_id"]


                );
            $a= Db::table("s_api_admin")->where(array("admin_id"=> $res['admin_id']))->data($datas)->update();

        }
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
     * 保存角色和资源的关系
     */
    public function  save_resources_role()
    {
        $res = $this->request->post();
        $listresources = $res["reslist"];
        $role_id = $res["role_id"];
        if(empty($role_id)){
            $this-> ajaxReturn($listresources);
        }
        if(empty($listresources)){
            $this-> ajaxReturn($role_id);
        }

        $b = Db::table("s_api_resources_role")->where(array("role_id"=>$role_id))->delete();
        for($i=0;$i<count($listresources);$i++){
        	$dataList[$i]=array('role_id' =>$role_id,'resources_id' => $listresources[$i],);    
        }
            
        $c = Db::table("s_api_resources_role")->addAll($dataList);
        if($c>0){
            return json(true);
        }else{
            return json(false);
        }
    }

    /**
     * 查找此角色的
     */
    public  function  select_myresources_role()
    {
        $res = $this->request->post();
        if(empty($res["role_id"])){
            $this-> ajaxReturn(null);
        }
        $m = Db::table("s_api_resources_role");
        $a= $m->where(array("role_id"=>$res["role_id"]))->select();
        return json($a);
    }

    /**
     * 角色资源分配页面查询数据
     */
     public function  select_resources_role_list()
     {
        $d = $this->resc_mod;
        $backstage=$d->where(array("r_group_paterid"=>0,"r_group_type"=>1))->relation(true)->select();
        $m = Db::table("s_api_resources");
        foreach($backstage as $k=>$v){
            foreach ( $backstage[$k]["one"] as $k1 =>$v1){
                $backstage[$k]["one"][$k1]["two"]=$m->where(array('r_group_paterid'=> $backstage[$k]["one"][$k1]["r_id"]))->select();
            }
        }
        $frooneend=$d->where(array("r_group_paterid"=>0,"r_group_type"=>2))->relation(true)->select();
        $a=array("frontEnd"=>$backstage,"backstage"=>$frooneend);
        return json($a);
     }
    
    /**
     * 查询日志
     */
    public  function  select_k()
    {
        $d=$this->resc_mod;
        $a=$d->where(array("r_group_paterid"=>0,"r_group_type"=>1))->relation(true)->select();
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
     * 删除资源
     */
    public function del_by_resources()
    {
        $res = $this->request->post();
        if(empty($res['id'])){
        	$this->array_return['ResultType'] = self::__ERROR2__;
            $this->array_return['Message'] = "参数缺失";
            return json($this->array_return);
        }
        $falg = Db::table("s_api_resources")->where(array("r_group_paterid"=>$res['id']))->delete();
        $falg = Db::table("s_api_resources")->where(array("r_id"=>$res['id']))->delete();
        
        $this->kkajaxReturn($falg);
    }

    /**
     * 根据资源名查询数据
     */
    public function select_by_resources_name()
    {
        $res = $this->request->post();
        $map = array();
        if(!empty($res['r_name'])){
            $map['r_name']=array('like',"%".$res['r_name']."%");
        }
        $list = Db::table("s_api_resources")->where($map)->field("r_group_series,r_group_type,r_id,r_name")->select();
        $this->kkajaxReturn($list);
    }

    /**
     * 添加资源
     */
    public function i_u_resources()
    {
        $res = $this->request->post();
        $res["r_img"] = input('post.r_img','',false);
        if(empty($res["r_id"])){
            if($res["r_group_paterid"]==0 || $res["r_group_paterid"]==''){
                $res["r_group_series"]=1;
            }else{
                $res["r_group_series"] = Db::table("s_api_resources")->where(array("r_id"=>$res["r_group_paterid"]))->column("r_group_series");
                $res["r_group_series"]=(int)$res["r_group_series"]+1;
            }

         //   <i class="layui-icon" data-icon="&#xe705;">&#xe705;</i>
            $m = Db::table("s_api_resources")->data($res)->insert();
            $this->kkajaxReturn($m);
        }
        //  `r_name` varchar(60) DEFAULT NULL COMMENT '资源名',
//  `r_group_type` int(11) DEFAULT NULL COMMENT '资源类型',
//  `r_group_paterid` int(11) DEFAULT NULL COMMENT '资源分类id',
//  `r_group_series` int(11) DEFAULT NULL COMMENT '资源级数',
//  `r_url` varchar(150) DEFAULT NULL COMMENT '资源url',
//  `r_img` varchar(150) DEFAULT NULL COMMENT '资源图标',
//  `r_sort` int(11) DEFAULT NULL COMMENT '资源排序',
//  `r_describe` varchar(250) DEFAULT NULL COMMENT '资源描述',
        $datas=array("r_name"=>$res["r_name"],"r_group_type"=>$res["r_group_type"]
        ,"r_group_paterid"=>$res["r_group_paterid"]
        ,"r_url"=>$res["r_url"]
        ,"r_img"=>$res["r_img"]
        ,"r_sort"=>$res["r_sort"]
        ,"r_describe"=>$res["r_describe"]);
        $m = Db::table("s_api_resources")->where(array("r_id"=>$res["r_id"]))->update($datas);
        $this->kkajaxReturn($m);
    }
    /**
     * 分页查询资源
     */
    public function pqfl_resources_list()
    {
        $res = $this->request->post();
        $n = $res['n'];
        if(empty($n)){
            $n=1;
        }

        $map = array();
        if(!empty($res['r_name'])){
            $map['a.r_name']=array('like',"%".$res['r_name']."%");
        }
        $pageSize=10;
        $m = Db::table("s_api_resources");
        $pageTotal= $m->alias("a")->where($map)->count();
        $list=$m->alias("a")->field("a.*")->
            where($map)->order('r_id desc')->limit(($n-1)* $pageSize, $pageSize)->select();
        $datalist=array("list"=>$list,"pageTotal"=>$pageTotal,"pageSize"=>$pageSize,"pageNum"=>$n);
        $this->kkajaxReturn($datalist);
    }

    /**
     * 插入和修改角色
     */
    public function i_u_role()
    {
        $res = $this->request->post();
        if(empty($res["role_id"])){
            $m = Db::table("s_api_role")->data($res)->insert();
            $this->kkajaxReturn($m);
        }
        $datas=array("role_name"=>$res["role_name"],"role_descride"=>$res["role_descride"]);
        $m = Db::table("s_api_role")->where(array("role_id"=>$res["role_id"]))->update($datas);
        $this->kkajaxReturn($m);
    }

    /**
     * 分页得到角色
     */
    public function pqfl_role_list()
    {
        $res = $this->request->post();
        $n = $res['n'];
        if(empty($n)){
            $n=1;
        }

        $map = array();
        if(!empty($res['role_name'])){
            $map['role_name']=array('like',"%".$res['role_name']."%");
        }
//  `role_id` int(11) NOT NULL AUTO_INCREMENT,
//  `role_name` varchar(30) DEFAULT NULL COMMENT '角色名',
//  `role_descride` varchar(250) DEFAULT NULL COMMENT '资源描述',
        $pageSize=10;
        $m = Db::table("s_api_role");
        $pageTotal= $m->where($map)->count();
        $list=$m->where($map)->order('role_id desc')->limit(($n-1)* $pageSize, $pageSize)->select();;
        $datalist=array("list"=>$list,"pageTotal"=>$pageTotal,"pageSize"=>$pageSize,"pageNum"=>$n);
        $this->kkajaxReturn($datalist);
    }
    
    public function userlock()
    {
        $res = $this->request->post();
        $uuid = $res["uuid"];
        $pwd = $res["pwd"];
        $admin = Db::table("s_api_admin")->where(array("uuid"=>$uuid))->find();
        if($admin==null){
            $this->array_return['ResultType'] = -1;
            $this->array_return['Message'] = "账号不存在";
            $this->array_return['AppendData'] = "";
            //logtype 1 后台用户,2增加权限，3角色，4，代理商申请，5，代理商用户，6合同管理，7，审核配置
            $this->setAdminLog($this->userInfo["admin_id"],1,$this->userInfo["admin_id"],serialize($res),"后台用户解锁");
            return json($this->array_return);
        }else{
            if($admin["admin_pwd"]!=$pwd){
                $this->array_return['ResultType'] = -1;
                $this->array_return['Message'] = "密码错误";
                $this->array_return['AppendData'] = "";
                return json($this->array_return);
            }else{
                $this->array_return['ResultType'] = 0;
                $this->array_return['Message'] = "解锁成功";
                $this->array_return['AppendData'] =true;
                return json($this->array_return);
            }
        }
    }
    
    /**
     * 登录功能
     */
    public function login()
    {
        $res = $this->request->post();
        $account = $res["account"];
        $pwd = $res["pwd"];
        $admin = Db::table("s_api_admin")->where(array("admin_account"=>$account,"isfalg"=>2,"isdel"=>0,"compulsory_downline"=>0))->find();
        if($admin==null){
            $this->array_return['ResultType'] = -1;
            $this->array_return['Message'] = "账号不存在";
            $this->array_return['AppendData'] = "";
            return json($this->array_return);
        }
        if($admin["admin_pwd"]!=$pwd){
            $this->array_return['ResultType'] = -1;
            $this->array_return['Message'] = "密码错误";
            $this->array_return['AppendData'] = "";
            return json($this->array_return);
        }
        //现在先简单登录一下
        $browser=$this->GetBrowser();
        $lang=$this->GetLang();
        $os=$this->GetLang();
        $ip=$this->Getip();
       // $address=$this->Getaddress();    {admindatetime
        $datatime=date('Y-m-d H:i:s');
        $login_num=intval($admin["login_num"])+1;
        $data=array("login_ip"=>$ip,
            "hostname"=>"系统".$os."浏览器".$browser."语言".$lang,
            "descript"=>'',"last_time"=>$datatime,"login_num"=>$login_num);

        $isok = Db::table("s_api_admin")->where(array("admin_id"=>$admin["admin_id"]))->update($data);
        //logtype 1 后台用户,2增加权限，3角色，4，代理商申请，5，代理商用户，6合同管理，7，审核配置
        $this->setAdminLog($this->userInfo["admin_id"],1,$this->userInfo["admin_id"],serialize($res),"后台用户登录");
        if ($isok) {
            $this->array_return['ResultType'] = self::__OK__;
            $this->array_return['Message'] = "操作成功";
            $this->array_return['AppendData'] = array("usename"=>$admin["admin_account"],"topimgurl"=>$admin["topimgurl"],"id"=>$admin["uuid"],"pctoken"=>md5($admin["uuid"].strtotime($datatime))."%s".$login_num);
        } else {
            $this->array_return['ResultType'] = -1;
            $this->array_return['Message'] = "操作失败";
        }
        
        return json($this->array_return);
    }
    
    //获得访客浏览器类型
    function GetBrowser()
    {
        if(!empty($_SERVER['HTTP_USER_AGENT'])){
            $br = $_SERVER['HTTP_USER_AGENT'];
            if (preg_match('/MSIE/i',$br)) {
                $br = 'MSIE';
            }elseif (preg_match('/Firefox/i',$br)) {
                $br = 'Firefox';
            }elseif (preg_match('/Chrome/i',$br)) {
                $br = 'Chrome';
            }elseif (preg_match('/Safari/i',$br)) {
                $br = 'Safari';
            }elseif (preg_match('/Opera/i',$br)) {
                $br = 'Opera';
            }else {
                $br = 'Other';
            }
            return $br;
        }else{return "获取浏览器信息失败！";}
    }
    
    //获得访客浏览器语言
    function GetLang()
    {
        if(!empty($_SERVER['HTTP_ACCEPT_LANGUAGE'])){
            $lang = $_SERVER['HTTP_ACCEPT_LANGUAGE'];
            $lang = substr($lang,0,5);
            if(preg_match("/zh-cn/i",$lang)){
                $lang = "简体中文";
            }elseif(preg_match("/zh/i",$lang)){
                $lang = "繁体中文";
            }else{
                $lang = "English";
            }
            return $lang;

        }else{return "获取浏览器语言失败！";}
    }

    //获取访客操作系统
    function GetOs()
    {
        if(!empty($_SERVER['HTTP_USER_AGENT'])){
            $OS = $_SERVER['HTTP_USER_AGENT'];
            if (preg_match('/win/i',$OS)) {
                $OS = 'Windows';
            }elseif (preg_match('/mac/i',$OS)) {
                $OS = 'MAC';
            }elseif (preg_match('/linux/i',$OS)) {
                $OS = 'Linux';
            }elseif (preg_match('/unix/i',$OS)) {
                $OS = 'Unix';
            }elseif (preg_match('/bsd/i',$OS)) {
                $OS = 'BSD';
            }else {
                $OS = 'Other';
            }
            return $OS;
        }else{return "获取访客操作系统信息失败！";}
    }

    ////获得访客真实ip
    function Getip()
    {
        if(!empty($_SERVER["HTTP_CLIENT_IP"])){
            $ip = $_SERVER["HTTP_CLIENT_IP"];
        }
        if(!empty($_SERVER['HTTP_X_FORWARDED_FOR'])){ //获取代理ip
            $ips = explode(',',$_SERVER['HTTP_X_FORWARDED_FOR']);
        }
        if($ip){
            $ips = array_unshift($ips,$ip);
        }

        $count = count($ips);
        for($i=0;$i<$count;$i++)
        {
            if(!preg_match("/^(10|172\.16|192\.168)\./i",$ips[$i])){//排除局域网ip
                $ip = $ips[$i];
                break;
            }
        }
        $tip = empty($_SERVER['REMOTE_ADDR']) ? $ip : $_SERVER['REMOTE_ADDR'];
        if($tip=="127.0.0.1"){ //获得本地真实IP
            return "";
        }else{
            return $tip;
        }
    }

    //根据ip获得访客所在地地名
    function Getaddress($ip='')
    {
        if(empty($ip)){
            $ip = $this->Getip();
        }
        $ipadd = file_get_contents("http://int.dpool.sina.com.cn/iplookup/iplookup.php?ip=".$ip);//根据新浪api接口获取
        if($ipadd){
            $charset = iconv("gbk","utf-8",$ipadd);
            preg_match_all("/[\x{4e00}-\x{9fa5}]+/u",$charset,$ipadds);

            return $ipadds;   //返回一个二维数组
        }else{return "addree is none";}
    }
    
    public function setAdminLog($admin_id,$logtype,$operation_data_id,$operation_data_str,$dis)
    {
        $data=array(
            "admin_id"=> $admin_id,
            "logtype"=>$logtype,
            "operation_data_id"=>$operation_data_id,
            "operation_data_str"=>$operation_data_str,
            "dis"=>$dis,
            "createtime"=>date('Y-m-d H:i:s')
        );
        
        $admin = Db::table("s_api_log")->insert($data);
    }
    
    /**
     * 前端功能权限三级显示
     */
    public function  admin_qx()
    {
        $role_id=$this->userInfo["role_id"];
        $listone= Db::table("s_api_resources_role")->alias("a")
            ->leftJoin("s_api_resources b"," a.resources_id=b.r_id")
            ->field("b.r_id,b.r_name,b.r_url,b.r_img,b.r_sort,b.r_describe")
            ->where(array("b.r_group_paterid"=>0,"a.role_id"=>$role_id,"b.r_group_type"=>1))->select();
        for ($c = 0; $c < count($listone); $c++) {
            $wherein[$c]=$listone[$c]["r_id"];
        }

        $listtwo= Db::table("s_api_resources_role")->alias("a")
            ->leftJoin("s_api_resources b"," a.resources_id=b.r_id")
            ->field("b.r_id,b.r_name,b.r_url,b.r_img,b.r_sort,b.r_describe,b.r_group_paterid")
            ->where(array("b.r_group_paterid"=>array("in",implode(',',$wherein)),"a.role_id"=>$role_id,"b.r_group_type"=>1))->select();

        for ($e = 0; $e < count($listtwo); $e++) {
            $wheretowin[$e]=$listtwo[$e]["r_id"];
        }
        $listthre= Db::table("s_api_resources_role")->alias("a")
            ->leftJoin("s_api_resources b"," a.resources_id=b.r_id")
            ->field("b.r_id,b.r_name,b.r_url,b.r_img,b.r_sort,b.r_describe,b.r_group_paterid")
            ->where(array("b.r_group_paterid"=>array("in",implode(',',$wheretowin)),"a.role_id"=>$role_id,"b.r_group_type"=>1))->select();

        $list=$listone;
        $s=0;
        $g=0;
        for ($f = 0; $f < count($listone); $f++) {
            for ($d = 0; $d < count($listtwo); $d++) {
                    if($listtwo[$d]["r_group_paterid"]==$listone[$f]["r_id"]){
                        $list[$f]["one"][$s]=$listtwo[$d];
                        for ($h = 0; $h < count($listthre); $h++) {
                            if($listthre[$h]["r_group_paterid"]==$listtwo[$d]["r_id"]){

                                $list[$f]["one"][$s]["two"][$g]=$listthre[$h];
                                $g++;
                            }
                        }
                        $s++;
                    }
                $g=0;
            }
            $s=0;
            $g=0;
        }
        
        $this->kkajaxReturn($list);

//        $sql = "select a.r_id,a.r_name,a.r_url,a.r_img,a.r_sort,a.r_describe,
//b.r_id as r_id1,b.r_name as r_name1,b.r_url as r_url1,
//b.r_img as r_img1,b.r_sort as r_sort1,b.r_describe as r_describe1,
//c.r_id as r_id2,c.r_name as r_name2,c.r_url as r_url2,
//c.r_img as r_img2,c.r_sort as r_sort2,c.r_describe as  r_describe2 from
//s_api_resources_role e
//LEFT JOIN s_api_resources  a','e.resources_id=a.r_id
//LEFT JOIN s_api_resources  b','a.r_id=b.r_group_paterid
//LEFT JOIN s_api_resources  c','b.r_id=c.r_group_paterid
//where a.r_group_paterid=0 and  e.role_id=1 and a.r_group_type=1 ORDER BY r_id";
//模仿此结构
//        <id  column="r_id" property="r_id"/>
//         <result column="r_name" property="r_name"/>
//         <result column="r_url" property="r_url"/>
//         <result column="r_img" property="r_img"/>
//         <result column="r_sort" property="r_sort"/>
//         <result column="r_describe" property="r_describe"/>
//         <!--二级-->
//         <collection property="one" ofType="com.b2b2c.developer_center.vo.resourcesOneVo">
//             <id  column="r_id1" property="r_id"/>
//             <result column="r_name1" property="r_name"/>
//             <result column="r_url1" property="r_url"/>
//             <result column="r_img1" property="r_img"/>
//             <result column="r_sort1" property="r_sort"/>
//             <result column="r_describe1" property="r_describe"/>
//             <!--三级-->
//             <collection property="two" ofType="com.b2b2c.developer_center.vo.resourcesTwoVo">
//                 <id  column="r_id2" property="r_id"/>
//                 <result column="r_name2" property="r_name"/>
//                 <result column="r_url2" property="r_url"/>
//                 <result column="r_img2" property="r_img"/>
//                 <result column="r_sort2" property="r_sort"/>
//                 <result column="r_describe2" property="r_describe"/>
//             </collection>
    }
    /**
     * 重复代码
     * @param $datalist
     */
    function kkajaxReturn($datalist){
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