<?php
/**
 * Created by IntelliJ IDEA.
 * User: 14562
 * Date: 2018/8/22
 * Time: 9:42
 */
namespace app\api\controller;
use think\Controller;
use think\Db;
use app\api\logic\Vote as voteLogic;

class Vote extends Controller 
{
    const __OK__ = '0'; //请求成功
    const __ERROR__ = '1'; //参数错误
    const __ERROR1__ = '2'; //没有绑定
    const __ERROR2__ = '3';//数据库错误
    private $array_return = array("ResultType"=>1,"Message"=>"success","AppendData"=>"");

    public  function  vote_user_top13()
    {
        $res = $this->request->post();
        $n = $res['n'] ? $res['n'] : 1;
        if(empty($res["openid"])){
            $this->array_return['ResultType'] = self::__ERROR2__;
            $this->array_return['Message'] = "非法访问";
            return json($this->array_return);
        }
        if(!empty($res["openid"])){
            $wheredata["createtime"]= array('egt',date('Y-m-d 00:00:00'));
            $wheredata["yxmopenid"]= $res["openid"];
            $vuuids= Db::table("s_vote_record")->where($wheredata)->field("vuuid")->select();
            if($n==1){
                $maxs = Db::table("s_vote_user")->order("u_vote_num desc")->field("u_vote_num")->find();
            }
        }
        
        $map = array();
        $pageSize = $res["s"] ? $res["s"] : 8;
        
        $fields="a.uid,a.uname,a.u_vote_num,a.u_img_url,a.u_img_top,a.uuuid,a.u_number";
        $m = Db::table("s_vote_user");
        $pageTotal = $m->alias('a')->where($map)->count();
        $list=$m->alias('a')->order('a.u_vote_num desc')->field($fields)->where($map)->limit(($n-1)* $pageSize,$pageSize)->select();
        $datalist=array("list"=>$list,"pageTotal"=>$pageTotal,"pageSize"=>$pageSize,"pageNum"=>$n,"uuid"=>$vuuids,"maxs"=>$maxs);
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

    public function del_vote_user()
    {
        $res = $this->request->post();
        $uuid = $res["uuid"];
        $m = Db::table("s_vote_user")->where(array("uuuid"=>$uuid))->delete();
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
     * 分页查找投票用户
     */
    public  function  f_vote_useris()
    {
        $res = $this->request->post();
        $n = $res['n'] ? $res['n'] : 1;
		$pageSize = $res["s"] ? $res["s"] : 10;

        $map = array();
        if(!empty($res["dname"])){
            $map["c.uname"]=array("like","%".$res["dname"]."%");
        }
        $m = Db::table("s_vote_record");
        $pageTotal= $m->alias('a')->leftJoin("s_vote_recored_user b"," b.yxmopenid=a.yxmopenid")->
        join("left join s_vote_user c","c.uuuid=a.vuuid")->where($map)->count();
        $list=$m->alias('a')->leftJoin("s_vote_recored_user b","a.yxmopenid=b.yxmopenid")->leftJoin("s_vote_user c","a.vuuid=c.uuuid")
            ->field("c.uname,c.uuuid,a.createtime,b.nickname,b.headimgurl")->where($map)->limit(($n-1)* $pageSize,$pageSize)->select();
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
     * 分页查找投票用户
     */
   public  function  f_vote_user()
   {
       $res = $this->request->post();
       $n = $res['n'] ? $res['n'] : 1;
	   $pageSize = $res["s"] ? $res["s"] : 10;

       $map = array();
       if(!empty($res["dname"])){
           $map["a.uname"]=array("like","%".$res["dname"]."%");
       }
       if(!empty($res["openid"])){
           $wheredata["createtime"]= array('egt',date('Y-m-d 00:00:00'));
           $wheredata["yxmopenid"]= $res["openid"];
           $vuuids= Db::table("s_vote_record")->where($wheredata)->field("vuuid")->select();
           if($n==1){
               $maxs = Db::table("s_vote_user")->order("u_vote_num desc")->field("u_vote_num")->find();
           }

       }
       if(empty($res["isfalg"])){
           $fields="a.uname,a.u_vote_num,a.u_img_url,a.u_img_top,a.uuuid,a.u_number,a.u_sort,a.u_number";
       }else{
           $fields="a.uid,a.uname,a.u_vote_num,a.u_img_url,a.u_img_top,a.uuuid,a.u_number,a.u_sort,a.createtime,u_sharing_audio,u_sharing_title
           ,u_sharing_dis";
       }
       
       $m = Db::table("s_vote_user");
       $pageTotal= $m->alias('a')->where($map)->count();
       $list=$m->alias('a')->order('a.u_vote_num desc')->field($fields)->where($map)->limit(($n-1)* $pageSize,$pageSize)->select();
       $datalist=array("list"=>$list,"pageTotal"=>$pageTotal,"pageSize"=>$pageSize,"pageNum"=>$n,"uuid"=>$vuuids,"maxs"=>$maxs);
       if ($datalist) 
       {
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
    * 用户投票
    */
   public function  user_vote_log()
   {
       $res = $this->request->post();
       $uuuid = $res["uuuid"];
       $openid = $res["openid"];
       
       if(empty($uuuid)){
           $this->array_return['ResultType'] = self::__ERROR2__;
           $this->array_return['Message'] = "参数缺失";
           return json($this->array_return);
       }
       
       if(empty($openid)){
           $this->array_return['ResultType'] = self::__ERROR2__;
           $this->array_return['Message'] = "未关注公众号或未从公众号菜单进入";
           return json($this->array_return);
       }
       
       $stoptime = 1541865600;
       $thistime = time();
       if(time()>1541865600){
       		$this->array_return['ResultType'] = self::__ERROR2__;
            $this->array_return['Message'] = "活动已截止";
            return json($this->array_return);
       }

       $isc = Db::table("s_vote_recored_user")->where(array("yxmopenid"=>$openid))->count();
       if($isc<=0){
           $this->array_return['ResultType'] = self::__ERROR2__;
           $this->array_return['Message'] = "未关注公众号或未从公众号菜单进入";
           return json($this->array_return);
       }
       $isjilu = Db::table("s_vote_record")->where(array("createtime"=>array("egt",date('Y-m-d 00:00:00')),"yxmopenid"=>$openid))->select();
       foreach ($isjilu as $k =>$v){
           if($isjilu[$k]["vuuid"]==$uuuid){
               $this->array_return['ResultType'] = self::__ERROR2__;
               $this->array_return['Message'] = "同一天内不可投同一个讲师";
               return json($this->array_return);
           }
       }
       if(count($isjilu)<2){
           $reco = Db::table("s_vote_record")->data(array("yxmopenid"=>$openid,"createtime"=>date('Y-m-d H:i:s'),"vuuid"=>$uuuid))->insert();
           $reco = Db::table("s_vote_user")->where(array("uuuid"=>$uuuid))->setInc("u_vote_num");
           $this->array_return['ResultType'] = self::__OK__;
           $this->array_return['Message'] = "投票成功";
           $this->array_return['AppendData'] = count($isjilu)+1;
       }else if(count($isjilu)==2){
           $reco = Db::table("s_vote_record")->data(array("yxmopenid"=>$openid,"createtime"=>date('Y-m-d H:i:s'),"vuuid"=>$uuuid))->insert();
           $reco = Db::table("s_vote_user")->where(array("uuuid"=>$uuuid))->setInc("u_vote_num");
           $reco = Db::table("s_vote_recored_user")->where(array("yxmopenid"=>$openid))->setInc("luck_drawnum");
           if ($reco) {
           		$this->array_return['ResultType'] = self::__OK__;
                $this->array_return['Message'] = "操作成功";
                $this->array_return['AppendData'] = $reco;
           } else {
                $this->array_return['ResultType'] = self::__ERROR2__;
                $this->array_return['Message'] = "操作失败";
          }
       }else{
           $this->array_return['ResultType'] = self::__ERROR2__;
           $this->array_return['Message'] = "您今天的投票机会已用完";
           return json($this->array_return);
       }
       
       return json($this->array_return);
   }

    /**
     * 增加
     */
    public function add_remaks()
    {
        $res = $this->request->post();
        if(empty($res["uid"])){
            $re = Db::table("s_vote_user")->insert($res);
        }else{
            $re = Db::table("s_vote_user")->where(array("rid"=>$res["rid"]))->update($res);
        }
        if ($re) {
            $this->array_return['ResultType'] = self::__OK__;
            $this->array_return['Message'] = "操作成功";
            $this->array_return['AppendData'] = $re;
        } else {
            $this->array_return['ResultType'] = self::__ERROR2__;
            $this->array_return['Message'] = "操作失败";
        }
        
        return json($this->array_return);
    }
    
    /**
     * 插入用户id
     */
    public function i_u_vote_user()
    {
        $res = $this->request->post();
        if(empty($res["uid"])){
            $res["uuuid"] = getRandomString(10);
            $res["createtime"]=date("Y-m-d H:i:s");
            $re = Db::table("s_vote_user")->insert($res);
        }else{
            $re = Db::table("s_vote_user")->where(array("uid"=>$res["uid"]))->update($res);
        }
        if ($re) {
            $this->array_return['ResultType'] = self::__OK__;
            $this->array_return['Message'] = "操作成功";
            $this->array_return['AppendData'] = $re;
        } else {
            $this->array_return['ResultType'] = self::__ERROR2__;
            $this->array_return['Message'] = "操作失败";
        }
        
        return json($this->array_return);
    }

    /**
     * 新增和修改商品
     */
   public function i_u_vote_prize()
   {
       $res = $this->request->post();
       if(empty($res["pid"])){
           $re = Db::table("s_vote_prize")->insert($res);
       }else{
           $re = Db::table("s_vote_prize")->where(array("pid"=>$res["pid"]))->update($res);
       }
       if ($re) {
           $this->array_return['ResultType'] = self::__OK__;
           $this->array_return['Message'] = "操作成功";
           $this->array_return['AppendData'] = $re;
       } else {
           $this->array_return['ResultType'] = self::__ERROR2__;
           $this->array_return['Message'] = "操作失败";
       }
       
       return json($this->array_return);
   }

    /**
     * 分页得到所有
     */
   public function  f_prize()
   {
       $res = $this->request->post();
       $n = $res['n'] ? $res['n'] : 1;
       $map = array();
       $pageSize = $res["s"] ? $res["s"] : 8;
       $m = Db::table("s_vote_prize");
       $pageTotal= $m->count();
       $list=$m->order('pprobability desc')->limit(($n-1)* $pageSize,$pageSize)->select();
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
     * 查找所有的抽奖商品
     */
   public function init_prize_page()
   {
       $res = $this->request->post();
       $openid = $res["openid"];
       $return = Db::table("s_vote_prize")->field("pid,pname,ptype,pdis,pimgurl")->order('pid desc')->select();
       $r=array("userinfo"=>"","initdata"=>$return);
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
   
   public function f_prize_luck_ok()
   {
       $res = $this->request->post();
       $lid = $res['lid'];
       $uid = $res["uid"];
       $a = Db::table("s_vote_prize_log")->where(array("lid"=>$lid))->update(array("uid"=>1));
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
     * 分页查看日志
     */
    public function f_prize_luck_log()
    {
        $res = $this->request->post();
        $n = $res['n'] ? $res['n'] : 1;

        $map = array();
        $map['a.pid']=array('not in',"10,14");
     	$pageSize = $res["s"] ? $res["s"] : 20;

        $m = Db::table("s_vote_prize_log");
        $pageTotal= $m->alias('a')->leftJoin("s_vote_prize p","a.pid=p.pid")
            ->leftJoin("s_vote_recored_user c","a.uid=c.rid")
            ->leftJoin("s_pay_code d","a.experience_id=d.id")->where($map)->count();
        $list=$m->alias('a')
            ->leftJoin("s_vote_prize p","a.pid=p.pid")
            ->leftJoin("s_vote_recored_user c","a.uid=c.rid")
            ->leftJoin("s_pay_code d","a.experience_id=d.id")
            ->where($map)->order('a.lid desc')->field("a.lid,a.uid as isfalg,p.pname,p.pprice,p.ptype,p.pnumber,p.pdis,p.pimgurl,c.nickname,c.sex,c.headimgurl,a.createtime,a.lid,d.code")->limit(($n-1)* $pageSize,$pageSize)->select();
        $datalist=array("list"=>$list,"pageTotal"=>$pageTotal,"pageSize"=>$pageSize,"pageNum"=>$n);
        //c.nicknamec.sex,c.headimgurl,
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
     * 分页查看日志
     */
   public function f_prize_log()
   {
       $res = $this->request->post();
       $n = $res['n'] ? $res['n'] : 1;
       
       $map = array();
       if(!empty($res['nick_name'])){
           $map['c.nickname']=array('like',"%".$res['nick_name']."%");

       }
       if(!empty($res['yxmopenid'])){
           $map['c.yxmopenid'] = $res['yxmopenid'];
       }
       if(!empty($res['id'])){
           $map['a.lid'] = $res['id'];
       }
     
       $pageSize = $res["s"] ? $res["s"] : 10;
       $map['a.pid']=array('not in',"10,14");
       $m = Db::table("s_vote_prize_log");
       $pageTotal= $m->alias('a')->leftJoin("s_vote_prize p","a.pid=p.pid")
           ->leftJoin("s_vote_recored_user c","a.uuid=c.yxmopenid")
           ->leftJoin("s_pay_code d","a.experience_id=d.id")->where($map)->count();
       $list=$m->alias('a')->leftJoin("s_vote_prize p","a.pid=p.pid")
           ->leftJoin("s_vote_recored_user c","a.uuid=c.yxmopenid")->leftJoin("s_pay_code d","a.experience_id=d.id")
           ->where($map)->order('a.lid desc')->field("a.lid,a.uid as isfalg,p.pname,p.pprice,p.ptype,p.pnumber,p.pdis,p.pimgurl,a.createtime,c.nickname,a.lid,c.sex,c.headimgurl,d.code")->limit(($n-1)* $pageSize,$pageSize)->select();
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
     * 得到我的中奖纪录
     */
    public function get_my_prize()
    {
       $res = $this->request->post();
       if(empty($res["openid"])){
           $this->array_return['ResultType'] = self::__ERROR2__;
           $this->array_return['Message'] = "参数缺失";
           return json($this->array_return);
       }
       
       $res = Db::table("s_vote_prize_log")->alias('a')->leftJoin("s_vote_prize p","a.pid=p.pid")
           ->leftJoin("s_vote_recored_user c","a.uuid=c.yxmopenid")->leftJoin("s_pay_code d","a.experience_id=d.id")
           ->field("a.lid,p.pname,p.pprice,p.ptype,p.pnumber,p.pdis,p.pimgurl,a.createtime,c.nickname,c.sex,c.headimgurl,d.code")
           ->where(array("a.uuid"=>$res["openid"],"a.pid"=>array('not in',"10,14")))->select();

       if ($res) {
           $this->array_return['ResultType'] = self::__OK__;
           $this->array_return['Message'] = "操作成功";
           $this->array_return['AppendData'] = $res;
       } else {
           $this->array_return['ResultType'] = self::__ERROR2__;
           $this->array_return['Message'] = "操作失败";
       }
       
       return json($this->array_return);
   }

    /**
     * 抽奖
     */
   public function luck_draw()
   {
        $res = $this->request->post();
        $openid = $res["openid"];
        $userobj = Db::table("s_vote_recored_user")->where(array("yxmopenid"=>$openid))->field("rid,nickname,luck_drawnum")->find();
        if(empty($userobj)){
            $this->array_return['ResultType'] = self::__OK__;
            $this->array_return['Message'] = "用户不存在";
            $this->array_return['AppendData'] = "";
            return json($this->array_return);
        }
        if((int)$userobj["luck_drawnum"]<=0){
            $this->array_return['ResultType'] = self::__OK__;
            $this->array_return['Message'] = "您的抽奖次数已用完";
            $this->array_return['AppendData'] ="";
            return json($this->array_return);
        }
        $prizelist = Db::table("s_vote_prize")->field("pname,pid,pprice,pstock,ptype,pdis,pprobability,pimgurl")->order('pid desc')->select();
        foreach ($prizelist as $key =>$obj){
            $arrs[$obj["pid"]]=$obj["pprobability"];
        }

        //得到随机数
        $vote_mod = new voteLogic();
        $rid =  $vote_mod->get_rand($arrs);
        $isfalg=true;
        //保正在大并發情況下價值高的商品也也不會出去
       //
       $iunm=0;
        while ($isfalg){
            if($iunm>12){
                $isfalg=false;
                $this->array_return['ResultType'] = self::__OK__;
                $this->array_return['Message'] = "抽奖人数太多了，下次在来吧";
                $this->array_return['AppendData'] ="";
                return json($this->array_return);
            }
            foreach ($prizelist as $key =>$obj){
                if($obj["pid"]==$rid){
                    $rkey=$key;
                    break;
                }
            }
            if((int)$prizelist[$rkey]["pprice"]<2000){
                $isfalg=false;
            }else{
                $rid = $vote_mod->get_rand($arrs);
            }
            $iunm++;
        }
        
       //沒有庫存要求的產品
        if((int)$prizelist[$rkey]["pstock"]==-1){
            if((int)$prizelist[$rkey]["ptype"]==2){
                //月卡
                if($prizelist[$rkey]["pname"]=="谢谢惠顾"){
                    Db::table("s_vote_prize_log")->data(array(
                        "uid"=>$userobj["rid"],
                        "pid"=>$rid,
                        "uuid"=>$openid,
                        "createtime"=>date('Y-m-d 00:00:00')
                    ))->insert();
                    Db::table("s_vote_recored_user")->where(array("rid"=>$userobj["rid"]))->setDec('luck_drawnum');
                }else{
                    $pay_obj = Db::table('s_pay_code')->where(array('type' => 1, 'status' => 0))->order('id desc')->find();
                    if ($pay_obj["id"]) {
                        $prizelist[$rkey]["code"]=$pay_obj["code"];
                        Db::table('s_pay_code')->where(array('id' => $pay_obj["id"]))->update(array('status' => 2));
                        $data = array("uid"=>$userobj["rid"],"pid"=>$rid,"uuid"=>$openid,"createtime"=>date('Y-m-d 00:00:00'),"experience_id"=>$pay_obj["id"]);
                        Db::table("s_vote_prize_log")->data($data)->insert();
                        Db::table("s_vote_recored_user")->where(array("rid"=>$userobj["rid"]))->setDec('luck_drawnum');
                    }else{
                        $this->array_return['ResultType'] = self::__ERROR2__;
                        $this->array_return['Message'] = "修改失败";
                        return json($this->array_return);
                    }
                }

            }else{
                $isstr=Db::table("s_vote_prize_log")->data(array(
                    "uid"=>$userobj["rid"],
                    "pid"=>$rid,
                    "uuid"=>$openid,
                    "createtime"=>date('Y-m-d 00:00:00')
                ))->insert();
                $isstr=Db::table("s_vote_recored_user")->where(array("rid"=>$userobj["rid"]))->setDec('luck_drawnum');
                if(!$isstr){
                    $model->rollback();
                    $this->array_return['ResultType'] = self::__ERROR2__;
                    $this->array_return['Message'] = "修改失败";
                    return json($this->array_return);
                }
            }
        }else{
            if((int)$prizelist[$rkey]["ptype"]==2){
                //月卡
                if($prizelist[$rkey]["pname"]=="谢谢惠顾"){
                    Db::table("s_vote_prize_log")->data(array(
                        "uid"=>$userobj["rid"],
                        "pid"=>$rid,
                        "uuid"=>$openid,
                        "createtime"=>date('Y-m-d 00:00:00')
                    ))->insert();
                    Db::table("s_vote_recored_user")->where(array("rid"=>$userobj["rid"]))->setDec('luck_drawnum');
                }else {
                    $pay_obj = Db::table('s_pay_code')->where(array('type' => 1, 'status' => 0))->order('id desc')->find();
                    if ($pay_obj["id"]) {
                        $prizelist[$rkey]["code"] = $pay_obj["code"];
                        Db::table('s_pay_code')->where(array('id' => $pay_obj["id"]))->update(array('status' => 2));
                        Db::table("s_vote_prize_log")->data(array(
                            "uid"=>$userobj["rid"],
                            "pid" => $rid,
                            "uuid" => $openid,
                            "createtime" => date('Y-m-d 00:00:00'),
                            "experience_id" => $pay_obj["id"]
                        ))->insert();

                        Db::table("s_vote_recored_user")->where(array("rid"=>$userobj["rid"]))->setDec('luck_drawnum');
                        if ($prizelist[$rkey]["pstock"] == 1) {
                            Db::table("s_vote_prize")->where(array("pid" => $rid))->update(array("pprobability" => 0));
                        }
                        Db::table("s_vote_prize")->where(array("pid" => $rid))->setDec("pstock");
                    } else {
                        $model->rollback();
                        $this->array_return['ResultType'] = self::__ERROR2__;
                        $this->array_return['Message'] = "修改失败";
                        return json($this->array_return);
                    }
                }
            }else{
                $isstr=Db::table("s_vote_prize_log")->data(array(
                    "uid"=>$userobj["rid"],
                    "pid"=>$rid,
                    "uuid"=>$openid,
                    "createtime"=>date('Y-m-d 00:00:00')
                ))->insert();
                $isstr=Db::table("s_vote_recored_user")->where(array("rid"=>$userobj["rid"]))->setDec('luck_drawnum');
                if($prizelist[$rkey]["pstock"]==1){
                    $isstr=Db::table("s_vote_prize")->where(array("pid"=>$rid))->update(array("pprobability"=>0));
                }
                $isstr=Db::table("s_vote_prize")->where(array("pid"=>$rid))->setDec("pstock");
                if(!$isstr){
                    $model->rollback();
                    $this->array_return['ResultType'] = self::__ERROR2__;
                    $this->array_return['Message'] = "修改失败";
                    return json($this->array_return);
                }
            }
        }

       $model->commit();
       $userobj["luck_drawnum"]=$userobj["luck_drawnum"]-1;
       $prizelist[$rkey]["pstock"]=null;
       $prizelist[$rkey]["pprobability"]=null;
       $this->array_return['ResultType'] = self::__OK__;
       $this->array_return['Message'] = "操作成功";
       $this->array_return['AppendData'] = array("prize"=>$prizelist[$rkey],"pid"=>$rid,"key"=>$rkey,"user"=>$userobj);
       return json($this->array_return);
   }

}