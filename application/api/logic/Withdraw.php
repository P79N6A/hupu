<?php
/*
**********
提现逻辑层
************
 */
namespace app\api\logic;
use think\Model;
use think\Db;
use app\api\model\UserInfo  as userModel;
use app\api\model\Member  as memberModel;
use app\api\model\Withdraw  as withdrawModel;

class Withdraw extends Model
{

    private $User = null;
    private $Member = null;
	private $Withdraw = null;
    function __construct()
    {
        $this->User = new userModel();
        $this->Member = new MemberModel();
        $this->Withdraw = new withdrawModel();
    }

    public function getData($options=array())
    {
    	//获取我的提现数据
        $field = "money,frozen_money,already_money";//可提现,提现中,已提现
        $data = $this->User->field($field)->where(array('id'=>$options['user_id']))->find();
        return $data;
    }

    public function addData($options=array())
    {
        //申请提现
        $flag=true;
        $user_money=$this->User->where(array('id'=>$options['user_id']))->column('money');
        if($user_money<$options['money']){
            $flag=false;
        }else{
            $data['user_id']=$options['user_id'];
            $data['money']=$options['money'];
            $data['type']=$options['type'];//暂时写死
            $data['status']=2;//审核中
            $data['bank_id']=$options['bank_id'];//银行卡编号
            $data['branch_name']=$options['branch_name'];//银行
            $data['bank_name']=$options['bank_name'];//开户行
            $data['bank_user_name']=$options['bank_user_name'];//开户名字
            $data['bank_number']=$options['bank_number'];//银行卡号
            $data['ip']=$options['ip'];//ip地址
            $data['add_time']=time();

            $id = $this->Withdraw->insert($data);
            $this->User->where(array('id'=>$options['user_id']))->setInc("frozen_money",$data['money']);
            $this->User->where(array('id'=>$options['user_id']))->setDec("money",$data['money']);
            $flag=$id?true:false;
        }

        return $flag;

    }

    public function getInfo($options=array()){

        //获取提现说明详情

        //暂时省略

    }

    public function getSumInfo($options=array())
    {

        $where=array();
        $where['user_id']=$options["user_id"];
        $data=$this->Withdraw->where($where)->group('status')->field('case status when 1 then \'己提现\' when 2 then \'待审核\' when  3 then \'被拒绝\' else \'其它\' end as title,  status,sum(money) as money')->select();
        return $data;
    }

    public function getLog($options=array())
    {
        //获取提现记录
        $start_time=0;//开始时间
        $end_time=0;//结束时间
        $where=array();
        $type=$options['type'];
        if(empty($options['start_time'])){
            if($type==1){
                //7天内
                $plan_time=time();
                $start_time=$plan_time-((date('w')==0?7:date('w'))-1)*24*3600;
                $end_time=$plan_time+(7-(date('w')==0?7:date('w')))*24*3600;
            }

            if($type==2){
                //上个月
                $timestamp=time();
                $firstday=date('Y-m-d',strtotime(date('Y',$timestamp).'-'.(date('m',$timestamp)-1).'-01'));
                $lastday=date('Y-m-d',strtotime("$firstday +1 month -1 day"));
                $start_time=strtotime($firstday);
                $end_time=strtotime($lastday);

            }

            if($type==3)
            {
                $timestamp=time();
                $firstday=date('Y-m-01',strtotime(date('Y',$timestamp).'-'.'01-01'));
                $lastday= date('Y-m-d', strtotime(date('Y-m', $timestamp) . '-01 00:00:00') - 86400);
                $start_time=strtotime($firstday);
                $end_time=strtotime($lastday);
            }
        }

        if($options['get_type'] == 2){
            $where['w.status']=array(array('eq',2),array('eq',4),'or');
        }else{
            $where['w.status']=$options['get_type'];
        }

        $where['w.user_id']=$options["user_id"];
        if($type!=0){
            $where['_string']="w.add_time>=".$start_time." and w.add_time<=".$end_time;
        }

        $data = Db::table('s_Withdraw')->alias('w')
            ->leftJoin(' s_users_bank b','b.id = w.bank_id')
            ->field('w.*,b.number as bank_num')
            ->order('w.add_time desc')
            ->where($where)->select();
        foreach ($data as $key=>$value) {
            $data[$key]['add_time_date']=date('Y-m-d H:i:s',$data[$key]['add_time']);
        }
        return $data;
    }

}