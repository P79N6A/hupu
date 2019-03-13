<?php
/** 资金管理 */
namespace app\home\controller;
use think\Db;
use app\common\controller\HomeBase;
use app\api\logic\UsersFans  as fansLogic;
use app\api\logic\VipList  as vipLogic;
use app\api\model\UserInfo  as userMod;
use app\api\logic\CapitalLog  as capitalLogic;
use app\api\logic\Withdraw  as withdrawLogic;

class Capital extends HomeBase
{
	private $fans_logic = null;
	private $vip_logic = null;
	private $user_mod = null;
	private $capital_logic = null;
	
    public function _initialize()
    {
        parent::_initialize();
        if(!empty($this->userInfo)){
            $this->userInfo = $this->user_mod->alias('u')->leftJoin(' s_vip_group v','v.id = u.vip_group_id')
                ->leftJoin(' s_member m','m.id = u.member_id')->field('u.*,m.phone,v.vip_name')->where(array('u.id'=>$this->userInfo['id']))->find();
        }
        
        $this->vip_logic = new vipLogic();
        $this->fans_logic = new fansLogic();
        $this->user_mod = new userMod();
        $this->capital_logic = new capitalLogic();
    }

    //新的充值页面
    public function newrecharge()
    {
        $this->title="充值";
        return $this->fetch();
    }

    /* 充值*/
    public function recharge()
    {
        $this->title="会员购买";

        if(isPOST){
            $res = Input("post.");
            if($res['vip_id'] == 8){
                $old = Db::table('s_pay_code')->where(array('code'=>$res['pay_code'],'status'=>array('neq',1)))->find();
                if($old){
                   	Db::startTrans();
                   	try {
                    	Db::table('s_pay_code_log')->insert(array('code_id'=>$old['id'],'user_id'=>$res['user_id'],'addtime'=>time()));
                    	Db::table('s_pay_code')->where(array('id'=>$old['id']))->update(array('status'=>1));
	                    $userData['vip_id']=8;
	                    $userData['vip_group_id']=7;
	                    $userData['vip_orver_time']=strtotime('+1 month');//1个月过期
                    	$this->user_mod->where(array('id'=>$res['user_id']))->update($userData);

                        Db::commit();
                        $send = $this->user_mod->alias('u')->leftJoin(' s_member m','m.id = u.member_id')
                            ->field('m.openid,u.nick_name,m.phone')->where(array('u.id'=>$res['user_id']))->find();
                        sendWxTemplateMessages('SendBindInfo',array( 'wecha_id' => $send['openid'],'href'=>get_current_Host().url('/Home/User/usercenter'), 'first' => '您己成为洋小秘平台会员，您的会员等级是：体验用户', 'keyword1' =>$send['nick_name'], 'keyword2' => $send['phone'], 'keyword3'=> date("Y-m-d H:i:s"),'remark' => '马上创建您的V网吧！'));
                        header('Location: '.url("Home/User/success"));
                    } catch (\Exception $e) {
                        Db::rollback();
                        $this->error("提交订单失败！");
                    }
                }else{
                    $this->error("此验证码无效！");
                }
            }else{
                if ($res['pay_type'] == 1){
                    $res['pay_source']='服务号';
                    $res['vip_id']=$res['vip_id']?$res['vip_id']:1;
                    $order_id=$this->vip_logic->buyVip($res);
                    if($order_id!=0){
                        return json(array('status'=>1,'order_id'=>$order_id));
                    }else{
                        return json(array('status'=>0,'msg'=>'创建订单失败'));
                    }
                }else{
                    $user_info = $this->user_mod->where(array('id'=>$res['user_id']))->find();
                    if (!$user_info){
                        return json(array('status'=>0,'msg'=>'查询失败'));
                    }
                    $money = $user_info['money'];
                    if ( $money < 198 ){
                        return json(array('status'=>3,'msg'=>'您的小秘币余额不足！'));
                    }
                    $res['pay_source']='小秘币';
                    $order_id=$this->vip_logic->buyVip($res);
                    if($order_id!=0){
                        return json(array('status'=>2,'order_id'=>$order_id));
                    }else{
                        return json(array('status'=>3,'msg'=>'创建订单失败'));
                    }
                }
            }
        }else{
            $user = $this->user_mod->find(Input('get.user_id'));
            $vip = $this->vip_logic->getVipList();
            if($user['vip_id'] == 8){
                //月卡只能用一次
                unset($vip[1]);
            }
            $this->assign('vip',$vip);
            $this->assign('user',$this->userInfo);
        }
        
        return $this->fetch();
    }

    /**
     * 充值
     */
    public function recharge2()
    {
        $this->title="会员购买";
        if(isPOST)
        {
            $res = Input("post.");
            $user_info = $this->user_mod->where(array('id'=>$res['user_id']))->find();
            if (!$user_info){
                return json(array('status'=>0,'msg'=>'查询失败'));
            }
            $money = $user_info['money'];
            if ( $money < 198 ){
                return json(array('status'=>3,'msg'=>'您的小秘币余额不足！'));
            }

            if($res['vip_id'] == 8){
                $old = Db::table('s_pay_code')->where(array('code'=>$res['pay_type'],'status'=>array('neq',1)))->find();
                if($old){
                    Db::startTrans();
                    try {
	                    Db::table('s_pay_code_log')->insert(array('code_id'=>$old['id'],'user_id'=>$res['user_id'],'addtime'=>time()));
	                    Db::table('s_pay_code')->where(array('id'=>$old['id']))->update(array('status'=>1));
	                    $userData['vip_id']=8;
	                    $userData['vip_group_id']=7;
	                    $userData['vip_orver_time']=strtotime('+1 month');//1个月过期
	                    $this->user_mod->where(array('id'=>$res['user_id']))->update($userData);
                   
                        Db::commit();
                        $send = $this->user_mod->alias('u')->leftJoin(' s_member m','m.id = u.member_id')
                            ->field('m.openid,u.nick_name,m.phone')->where(array('u.id'=>$res['user_id']))->find();
                        sendWxTemplateMessages('SendBindInfo',array( 'wecha_id' => $send['openid'],'href'=>get_current_Host().url('/Home/User/usercenter'), 'first' => '您己成为洋小秘平台会员，您的会员等级是：体验用户', 'keyword1' =>$send['nick_name'], 'keyword2' => $send['phone'], 'keyword3'=> date("Y-m-d H:i:s"),'remark' => '马上创建您的V网吧！'));
                        header('Location: '.url("Home/User/success"));
                    } catch (\Exception $e) {
                        Db::rollback();
                        $this->error("提交订单失败！");
                    }
                }else{
                    $this->error("此验证码无效！");
                }
            }else{
                $res['pay_source']='小秘币';
                $order_id=$this->vip_logic->buyVip($res);
                if($order_id!=0){
                    return json(array('status'=>1,'order_id'=>$order_id));
                }else{
                    return json(array('status'=>3,'msg'=>'创建订单失败'));
                }
            }
        }else{
            $user = $this->user_mod->find(Input('get.user_id'));
            $vip = $this->vip_logic->getVipList();
            if($user['vip_id'] == 8){
                //月卡只能用一次
                unset($vip[1]);
            }
            $this->assign('vip',$vip);
            $this->assign('user',$this->userInfo);
        }
        
        return $this->fetch();
    }

    /**
     * 续费
     */
    public function recharges()
    {
        $this->title="会员续费";
        if(isPOST){
            $array_return=array("status"=>1,"msg"=>"success","data"=>array());
            $post = Input("post.");
            $openid_info=Db::table('s_member')->Where('id='.$this->userInfo['member_id'])->Find();
            $openid=$openid_info['openid'];
            $_data['user_id']=$this->userInfo['id'];
            $_data['day']=$post['day'];

            if($openid!=''){
                $data=[
                    'connect_id'=>$_data['user_id'],
                    'type' =>2,
                    'order_type'=>2,
                    'pay_money' =>$post['price'],
                    //'pay_money' =>0.01,
                    'pay_title'=>"会员续费",
                    'pay_stitle'=>"会员续费",
                    'pay_tag'=>'续费',
                    'mark'=>json_encode($_data),
                    'openid'=>$openid,
                ];

                $pay_info=get_obj('pay_test')->add_pay_log($data);
                $pay_info=(json_decode($pay_info,true));
                $pay_info=$pay_info['obj'];
                $array_return['data']=json_decode($pay_info['pra']);
            }else{
                $array_return['status']=0;
                $array_return['msg']="发起支付失败";
            }
            
            return json($array_return);
        }else{
            if(!$_GET['openid']){
                header('Location:/getunionid.php?target_url='.urlencode('http://wx.yxm360.com/Home/Capital/recharges?status=1'));
            }
            $user = $this->user_mod->where(array('id'=>$this->userInfo['id']))->find();
            $group=Db::table("s_vip_group")->where(array('id'=>$user['vip_group_id']))->find();
            $this->fee=array(array("fee"=>$group['annual_fee'],"day"=>365,"msg"=>"会员年费"));
            if($this->userInfo['vip_orver_time']<time()){
                $this->assign('is_pay', 1);
            }else{
                $this->assign('is_pay', 0);
            }
            $this->day=timediff(time(),$this->userInfo['vip_orver_time']);
            
            return $this->fetch();
        }
    }

    public function getUserMoney()
    {
        if(isPOST){
            $user_id = Input("post.user_id");
            $user_info = $this->user_mod->where(array('id'=>$user_id))->find();
            if (!$user_info){
                $array_return['status'] = 0;
                $array_return['msg']="查询失败";
            }
            $money = $user_info['money'];
            $array_return['status'] = 1;
            $array_return['money'] = $money;
            return json($array_return);
        }else{
            $array_return['status']=0;
            $array_return['msg']="格式错误";
            return json($array_return);
        }
    }

    /**
     * 我的钱包
     */
    public function myWallet()
    {
        $this->title="我的收益";
        $this->data=$this->capital_logic->getLogData(array("user_id"=>$this->userInfo['id']));
        $this->assign('users', $this->userInfo);
        
        return $this->fetch();
    }

    /**
     * 提现页面
     */
    public function carry()
    {
        if($this->userInfo['id'] == 1){
            $this->error('您不能提现');
        }
        $this->user=$this->user_mod->field("money,frozen_money,already_money")->where(array('id'=>$this->userInfo['id']))->find();
        return $this->fetch();
    }

    /**
     * 申请提现
     */
    public function applyCarry()
    {
        if(isPOST){
            $array_return=array("status"=>1,"msg"=>"success","data"=>array());
            $post = Input("post.");
            $status = Db::table('s_member')->where(array('id'=>$this->userInfo['member_id']))->column('status');
            if($status == 2){
                $array_return['status']=0;
                $array_return['msg']="您的账号已被冻结,请联系客服人员";
                return json($array_return);
            }
            if ($post['type'] != 2) {
                $array_return['status']=0;
                $array_return['msg']="请勿非法调用";
                return json($array_return);
            }

            if ($post['type'] == 2 && empty($post['bank_id'])) {
                $array_return['status']=3;
                $array_return['msg']="请添加银行卡信息";
                return json($array_return);
            }
            
            $post['user_id']=$this->userInfo['id'];
            $post['ip'] = get_client_ip();
            $last_time = Db::table('s_withdraw')->where(array('user_id'=>$post['user_id']))->max('add_time');
            if(strtotime(date('Ymd'))<$last_time){
                $array_return['status']=4;
                $array_return['msg']="每人每天只能提现一次";
                return json($array_return);
            }
            
            $withdrwa = new withdrawLogic();
            $flag = $withdrwa->addData($post);
            if($flag){
                $phone = Db::table('s_member')->where(array('id'=>$this->userInfo['member_id']))->column('phone');
                $this->setMsg($phone,$post['money']);
                $array_return['msg']="提现成功";
            }else{
                $array_return['status']=0;
                $array_return['msg']="提现失败，余额不足";
            }
            
            return json($array_return);
        }else{
            $this->user=$this->user_mod->where(array('id'=>$this->userInfo['id']))->find();
            $banks = Db::table('s_users_bank')->where(array('user_id'=>$this->userInfo['id'],'status'=>1))->order('id desc')->find();
            $this->assign('banks', $banks);
        }
        
        return $this->fetch();
    }

    /**
     * 添加银行卡
     */
    public function addBank()
    {
        if(isPOST){
       		$post = Input("post.");
            $post['user_id']=$this->userInfo['id'];
            $result = Db::table('s_users_bank')->insert($post);
            if($result){
                $array_return['status']=1;
                $array_return['msg']="添加成功";
            }else{
                $array_return['status']=0;
                $array_return['msg']="添加失败";
            }
                       
            return json($array_return);
        }
    }

    /**
     * 提现说明
     */
    public function carryInfo()
    {
        return $this->fetch();
    }

    /**
     * 提现记录
     */
    public function carryLog()
    {
    	//get_type 1审核通过 2审核中 3不通过
        $type=!Input("get.type")?0:Input("get.type");
        $get_type = Input("get.get_type");

        $drwa_logic = new withdrawLogic();
        $data= $drwa_logic->getLog(array("type"=>$type,"user_id"=>$this->userInfo['id'],"get_type"=>$get_type));
        $this->data=$data;
        $this->type=$type;
        $this->get_type=$get_type;
        
        return $this->fetch();
    }

    /**
     * 提现审批记录
     */
    public function examineLog()
    {
        $id = Input("get.id");
        $this->data=Db::table('s_withdraw')->where(array('id'=>$id))->find();
        return $this->fetch();
    }

    /**
     * 自动提现
     */
    public function autoCarry(){
        return $this->fetch();
    }

    /**
     * 红包记录
     */
    public function redBagLog()
    {
        return $this->fetch();
    }

    /**
     *  收入记录
     */
    public function income()
    {
        $id = Input("get.id");
        $user_info=session("user_info");
        $data=$this->capital_logic->getHistoryLogInfo(array("user_id"=>$user_info['id'],"id"=>$id));
        $this->data=$data;
        return $this->fetch();
    }

    /**
     *  收入记录
     * 
     */
    public function statement(){
        $get_type = Input("get.get_type");
        $user_info=session("user_info");
        $type=!Input("get.type")?0:Input("get.type");
        switch ($get_type) {
            case 1:
                $this->title="销售贡献";
                break;
            case 2:
                $this->title="打赏贡献";
                break;
            case 3:
                $this->title="赠送用户贡献";
                break;
            case 4:
                $this->title="赠送小创投贡献";
                break;
            case 8:
                $this->title="用户贡献";
                break;
            case 9:
                $this->title="粉丝贡献";
                break;
            case 10:
                $this->title="年费贡献";
                break;
            default:
                $this->title="流水记录";
                break;
        }
        $data=$this->capital_logic->getHistoryLog(array("type"=>$type,"get_type"=>$get_type,"user_id"=>$user_info['id']));
        $this->data=$data;
        $this->get_type=$get_type;
        $this->type=$type;

        return $this->fetch();
    }

    /**
     *  消费
     */
    public function consume()
    {
        $user_info=session("user_info");
        $user_consume = Db::table('s_user_consume')->where(['user_id'=>$user_info['id']])->select();
        foreach ($user_consume as $key=>$value){
            $user_consume[$key]['create_time'] = date('Y-m-d H:i:s',$value['create_time']);
        }

        $this->assign('consume',$user_consume);
        $this->id=$this->userInfo['unionid'];

        return $this->fetch();
    }

    /**
     *  流水记录详情
     */
    public function statementInfo()
    {
        return $this->fetch();
    }

    /**
     * 分销
     */
    public function distribute()
    {
        $type = Input('type');
        if ($type == 1) {
            $order=Db::table('s_vip_order')->where(array("id"=>Input('order_id')))->find();
            $vip_list = Db::table('s_vip_list')->where(array('id' => $order['vip_list_id']))->find();
            $vip_group = Db::table('s_vip_group')->where(array('id' => $vip_list['vip_group_id']))->find();

            $userData['give_vip1_count']=$vip_group['give_vip1_count'];
            $userData['give_vip2_count']=$vip_group['give_vip2_count'];
            $userData['give_vip3_count']=$vip_group['give_vip3_count'];
            $userData['give_vip4_count']=$vip_group['give_vip4_count'];
            $userData['give_vip5_count']=$vip_group['give_vip5_count'];
            $userData['vip_id']=$vip_list['id'];
            $userData['vip_group_id']=$vip_list['vip_group_id'];
            $userData['vip_orver_time']=strtotime('+1 year');//1年过期
            $this->user_mod->where(array('id'=>$this->userInfo['id']))->update($userData);
            $data['order_sn']=$order['order_no'];//微信订单号
            $data['user_id']=$this->userInfo['id'];
            $data['object_id']=$vip_list['id'];
            $data['as']=2;//减少
            $data['money']=$vip_list['money'];
            $data['msg']="会员购买";
            $data['add_time']=time();
            Db::table('s_capital_log')->insert($data);

            $FansLogic=$this->fans_logic;$data['type']=3;
            $FansLogic->nexus(array(
                'user_id'=>$order['user_id'],
                'vip_id'=>$vip_list['id'],
                'level'=>$vip_group['level'],
                'money'=>$vip_list['money'],
                'order_sn'=>$order['order_no'],
                'vip_name'=>$vip_group['vip_name']
            ));//分佣计算
        }

        if ($type == 2) {
            $userInfo = $this->user_mod->where(array('id'=>$this->userInfo['id']))->find();
            $userData['vip_orver_time']=strtotime('+1 year');//1年过期
            $this->user_mod->where(array('id'=>$this->userInfo['id']))->update($userData);
            $vip_group = Db::table('s_vip_group')->where(array('id' => $userInfo['vip_group_id']))->find();

            $data['order_sn']=getOrderSn();//订单号
            $data['user_id']=$this->userInfo['id'];
            $data['object_id']=$userInfo['vip_group_id'];
            $data['as']=2;//减少
            $data['money']=$vip_group['annual_fee'];
            $data['msg']="会员续费";
            Db::table('s_capital_log')->insert($data);

            $FansLogic=$this->fans_logic;$data['type']=3;
            $FansLogic->nexus(array(
                'user_id'=>$userInfo['id'],
                'vip_id'=>$userInfo['vip_id'],
                'level'=>$vip_group['level'],
                'money'=>$vip_group['annual_fee'],
                'order_sn'=>$data['order_sn'],
                'vip_name'=>$vip_group['vip_name']
            ));//分佣计算
        }

        header('Location: '.url("Home/User/usercenter"));
    }
    
    /**
     * 发送短信
     * @param $phone
     * @param $code
     * @return array
     */
    public function setMsg($phone,$option)
    {
    	$content="您已成功申请兑换：{$option}小秘币，系统将会在3个工作日内审核并发放，退订回复TD";//要发送的短信内容

        $url = 'http://intapi.253.com/send/json';
        $post_data['account']       = 'I4612514';
        $post_data['password']      = 'b8fW1R4HZkd53e';
        $post_data['msg'] = '【洋小秘】'.$content;
        $post_data['mobile']    = '86'.$phone;
            //$post_data = array();
        $res = $this->request_post($url, $post_data,true);
        if($res['code'] == 0){
        	Db::table('s_new_msg')->insert(array('msgid'=>$res['msgid'],'mobile'=>$post_data['mobile'],'code'=>$content,'addtime'=>time()));
        }
            
        return array('msg'=>$res['error']==''?'发送成功':'发送失败','status'=>$res['code']);
    }
     
    //  小秘币转赠领取记录
    public function record()
    {
        return $this->fetch();
    }

     //  兑换页面
    public function change()
    {
        $this->assign('money',$this->userInfo['money']);
        return $this->fetch();
    }

    //  提交审核
    public function Subaudit()
    {
 		return $this->fetch();
	}

	// 设置密码
	public function szpwd()
	{
	    $this->assign('phone', $this->userInfo['phone']);
		return $this->fetch();
	}

	// 审核页面
	public function Auditre()
	{
	    $this->assign('phone', $this->userInfo['phone']);
		return $this->fetch();
	}
	
	//支付
	public function zf()
	{
 		return $this->fetch();
	}
}
