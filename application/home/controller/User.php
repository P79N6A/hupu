<?php
/** 用户管理 */
namespace app\home\controller;
use app\common\controller\HomeBase;
use app\api\logic\Member  as memberLogic;
use app\api\logic\User  as userLogic;
use app\api\logic\Cards  as cardLogic;
use think\Db;

class User extends HomeBase
{
    private $array_return = array("status"=>1,"msg"=>"success","data"=>[]);
    private $member_logic = null;
    private $user_logic = null;
    private $card_logic = null;

    public function initialize()
    {
        parent::initialize();
        $user_info = session("user_info");
        if(!empty($user_info)){
            $user_info = Db::table("s_user_info")->where("id=".$user_info['id'])->find();
            $user_info['vip_name'] = Db::table("s_vip_group")->where("id=".$user_info['vip_group_id'])->column("vip_name");
            $this->user_info=$user_info;
        }
 		
        $this->member_logic = new memberLogic();
 		$this->user_logic = new userLogic();
 		$this->card_logic = new cardLogic();
    }
    
    //获取地级市
    public function get_citys()
    {
        $listObj = Db::table('s_region');
        $where['top_parentid'] = Input('province_id');
        $where['level'] = 2;
        if (in_array(Input('province_id'),array(1,2,9,22,3561))) {
            $where['level'] = 3;
        }
        $list = $listObj->where($where)->select();
        $data=array('status'=>0,'city'=>$list);
        header("Content-type: application/json");
        exit(json_encode($data));
    }
    
    //获取地级县
    public function get_district()
    {
        $listObj = Db::table('s_region');
        $where['parent_id'] = Input('city_id');
        $where['level'] = 3;
        $list = $listObj->where($where)->select();
        $data=array('status'=>0,'district'=>$list);
        header("Content-type: application/json");
        exit(json_encode($data));
    }
    
    public function checkVip()
    {
        //检测是否购买会员
        $user_info = session("user_info");
        if($user_info['vip_id']==0){
            header("location:".url("Home/Capital/recharge",array("user_id"=>$user_info['id'])));
        }else{
            header("location:".url("Home/User/usercenter"));
        }
    }
    
    /**
     * 用户登陆
     */
    public function login()
    {
        $this->title="登录";

        if($this->request->isPost()){
            $res = Input("post.");
            $data = $this->user_logic->login($res);
            if($data){
                $this->array_return['msg']="登录成功";
                $wx_info = session('openid_contents') ? session('openid_contents') : '';
                Db::table('s_member')->where(array('id'=>$data['member_id']))->update(array('wx_info' => $wx_info,'last_log_time'=>time(),'last_log_ip'=>get_client_ip()));
            }else{
                $this->array_return['status']=0;
                $this->array_return['msg']="用户名或密码不正确";
            }
            return json($this->array_return);
        }else{
            $this->assign('openid',$_GET['openid']);
            return  $this->fetch();
        }
    }

    /**
     *  安全验证
     */
    public function safetyVerification()
    {
        return  $this->fetch();
    }

    /**
     * 忘记密码
     */
    public function findPassword()
    {
        if($this->request->isPost()){
            $res = Input("post.");
            $res['type']=2;
            $data=$this->member_logic->CheckUserPinCode($res);
            if(!$data){
                $this->array_return['status']=0;
                $this->array_return['msg']="验证码错误";
            }
            $res['id']= Db::table('s_member')->where(array("phone"=>$res['phone']))->column("id");
            $res['password_1']=$res['password1'];
            $res['password_2']=$res['password2'];
            $data = $this->user_logic->forgetPassWord($res);
            if(!$data){
                $this->array_return['status']=0;
                $this->array_return['msg']="两次密码不一样";
            }
            session("user_info",null);
            return json($this->array_return);
        }else{
            return  $this->fetch();
        }      
    }
    
    /**
     * 退出登录
     */
    public function checkout()
    {  
        Db::table('s_user_info')->update(array('id'=>$this->userInfo['id'],'is_quit'=>1));
        Db::table('s_member')->where(array('openid'=>session('openid')))->update(array('openid'=>''));
        session_unset();
    	session_destroy();
    	setcookie(session_name(),'',0,'/');
        //删除用户微信信息下次将无法自动登录
        header("Location:".url('Home/Mycenter/login'));
    }
    
    /**
     * 修改密码
     */
    public function changePassword()
    {
        if($this->request->isPost()){
            $user = $this->userInfo;
            $id   = $user['id'];
            $memberId = Db::table('s_user_info')->where(array('id'=>$id))->column("member_id");
            $password = Db::table('s_member')->where(array('id'=>$memberId))->column('password');
            $oldpwd   = md5($_POST['oldpwd']);
            if($password != $oldpwd){
                $this->array_return['status']=0;
                $this->array_return['msg']="原密码有误";
            }else{
                if($_POST['password1']!=$_POST['password2'] || empty($_POST['password1']) || empty($_POST['password2'])){
                     $this->array_return['status']=0;
                     $this->array_return['msg']="新密码不一致或为空";
                }else{
                    $res = Db::table('s_member')->where(array('id'=>$memberId))->update(array('password'=>md5($_POST['password2'])));//更新
                    if($res){
                        $this->array_return['status']=1;
                        $this->array_return['msg']="修改成功";
                    }else{
                        $this->array_return['status']=0;
                        $this->array_return['msg']="修改失败";
                    }
                }  
            }

            return json($this->array_return);
        }else{
            return  $this->fetch();
        }
    }

    /**
     * 判断是否存在此用户
     */
    public function is_user()
    {
        $phone = Input('post.phone');
        $res = Db::table('s_member')->where(array('phone'=>$phone))->find();
        if($res){
            return json(array('status'=>1,'msg'=>'已注册过'));
        }else{
            return json(array('status'=>0,'msg'=>'未注册'));
        }
    }

    public function sendCode()
    {
        //发送手机验证码
        if($this->request->isPost()){
            $res = Input("post.");
            $data=$this->member_logic->SendUserPinCode($res);
            return json($data);
        }
    }


    public function sendCodeForGrant()
    {
        //发送手机验证码
        if($this->request->isPost()){
            $res = Input("post.");
            $data=$this->member_logic->sendUserGrantCode($res);
            return json($data);
        }
    }

    public function checkPhone()
    {
        //检测验证码
        if($this->request->isPost()){
            $res = Input("post.");
            $data=$this->member_logic->CheckUserPinCode($res);
            if(!$data){
                $this->array_return['status']=0;
                $this->array_return['msg']="验证码错误";
            }
            return json($this->array_return);
        }
    }

    public function selectRegion(){
        //服务所属地
        $this->title="服务所属地";
        if($this->request->isPost()){
            $res = Input("post.");
            //防止没有推荐人的注册    
            if($res['rec_user_id'])
            {
                $flag = $this->user_logic->register($res);
                if($flag['status']==2){
                    $this->array_return['status']= 2;
                    $this->array_return['data']['id']=$flag['id'];
                    $this->array_return['msg']="该手机号已被注册";
                }elseif($flag['status']==0){
                    $this->array_return['status']=0;
                    $this->array_return['msg']="注册失败";
                }else{
                    $this->array_return['data']['id']=$flag['id'];
                    $this->array_return['msg']="注册成功";
                    $user_info = Db::table("s_user_info")->where(array("id"=>$flag['id']))->find();
                    session("user_info",$user_info);
                }
                return json($this->array_return);
            }
            else
            {
                $this->array_return['status']=0;
                $this->array_return['msg']='没有推荐人，不允许注册，注册失败';
                return json($this->array_return);
            }

        }else{
            $listObj = Db::table('s_region');
            $whereprovince['top_parentid'] = 0;
            $listprovince = $listObj->where($whereprovince)->order('highlight desc')->select();
            $this->age_list = Db::table('s_age')->select();
            $this->assign("province_list",$listprovince);
            return  $this->fetch();
        }   
    }

    /**
     * 注册
     */
    public function register()
    {
        $this->title="注册";
        if($this->request->isPost()){

        }else{
            $get = Input("get.");
            if(isset($_GET['share_id'])){
                $user = Db::table('s_user_info')->field("nick_name,headimg,member_id")->where(array("id"=>$get['share_id']))->find();
                $nick_name = $user['nick_name'];
                $phone = Db::table('s_member')->where(array("id"=>$user['member_id']))->column("phone");
                $content = "来自好友{$nick_name}({$phone})的推荐";
                $this->assign('content',$content);
                $this->headimg = $user['headimg'];
                $this->assign('headimg',$user['headimg']);
            } else {
                header("location:".getenv("HTTP_REFERER"));//没有推荐人，返回上一页
            }
            return  $this->fetch();
        }
    }


    /**
     * 个人中心
     */
    public function myInfo()
    {
    	is_vip();
        $data = Db::table('s_user_info')->alias('u')->leftJoin('s_member m ',' m.id = u.member_id')
            ->field('u.*,m.phone,m.openid')->where(['u.id'=>$this->userInfo['id']])->find();
           
        $day = timediff(time(),$data['vip_orver_time']);

        if($data['vip_orver_time']<time()){
            if($this->userInfo['vip_id'] == 8){
                header("location:".url("Home/Capital/recharge",array("user_id"=>$this->userInfo['id'])));
            }else{
                header('Location: '.url("Home/Capital/recharges"));
            }
            exit;
        }else if($day['day']< 31 && $data['is_send'] != 1 && $this->userInfo['vip_id'] != 8) {
            Db::table('s_user_info')->where(array('id'=>$data['id']))->update(array('is_send'=>1));
            sendWxTemplateMessages('SendEndInfo', array('wecha_id' => $data['openid'], 'href' => get_current_Host() . url('/Home/Capital/recharges'), 'first' => '您的会员服务在一个月内即将到期', 'keyword1' => '洋小秘会员', 'keyword2' => date('Y-m-d H:i:s',$data['vip_orver_time']),'remark' => '进入我的中心点击会员剩余可进行续费!'));
            $content="您的会员服务将会在一个月内到期";//要发送的短信内容
            $url = 'http://intapi.253.com/send/json';
            $post_data['account']       = 'I4612514';
            $post_data['password']      = 'b8fW1R4HZkd53e';
            $post_data['msg'] = '【洋小秘】'.$content;
            $post_data['mobile']    = '86'.$data['phone'];
            $res = request_post($url, $post_data,true);
            if($res['code'] == 0){
                Db::table('s_new_msg')->insert(array('msgid'=>$res['msgid'],'mobile'=>$post_data['mobile'],'code'=>$content,'addtime'=>time()));
            }
        }
        $data['vip_name'] = Db::table('s_vip_group')->where(array('id'=>$data['vip_group_id']))->value('vip_name');
        
        $this->assign('title', '我的中心');
        $this->assign('time', $day['day']);
        $this->assign('percentage', $round(($day['day']/365)*100));
        $this->assign('user', $data);
        return  $this->fetch();
    }


    public function usercenter()
    {
    	is_vip();      
        $data = Db::table('s_user_info')->alias('u')->leftJoin('s_member m ',' m.id = u.member_id')
            ->field('u.*,m.phone,m.openid')->where(['u.id'=>$this->userInfo['id']])->find();
        $day = timediff(time(),$data['vip_orver_time']);
      
        if($data['vip_orver_time']<time()){
            if($this->userInfo['vip_id'] == 8){
                header("location:".url("Home/Capital/recharge",array("user_id"=>$this->userInfo['id'])));
            }else{
                header('Location: '.url("Home/Capital/recharges"));
            }
            exit;
        }else if($day['day']< 31 && $data['is_send'] != 1 && $this->userInfo['vip_id'] != 8) {
            Db::table('s_user_info')->where(array('id'=>$data['id']))->update(array('is_send'=>1));
            sendWxTemplateMessages('SendEndInfo', array('wecha_id' => $data['openid'], 'href' => get_current_Host() . url('/Home/Capital/recharges'), 'first' => '您的会员服务在一个月内即将到期', 'keyword1' => '洋小秘会员', 'keyword2' => date('Y-m-d H:i:s',$data['vip_orver_time']),'remark' => '进入我的中心点击会员剩余可进行续费!'));
            $content="您的会员服务将会在一个月内到期";//要发送的短信内容
            $url = 'http://intapi.253.com/send/json';
            $post_data['account']       = 'I4612514';
            $post_data['password']      = 'b8fW1R4HZkd53e';
            $post_data['msg'] = '【洋小秘】'.$content;
            $post_data['mobile']    = '86'.$data['phone'];
            $res = request_post($url, $post_data,true);
            if($res['code'] == 0){
                Db::table('s_new_msg')->insert(array('msgid'=>$res['msgid'],'mobile'=>$post_data['mobile'],'code'=>$content,'addtime'=>time()));
            }
        }
        $data['vip_name'] = Db::table('s_vip_group')->where(array('id'=>$data['vip_group_id']))->value('vip_name');
		
        $this->assign('id', $data['id']);
        $this->assign('title', '我的中心');
        $this->assign('time', $day['day']);
        $this->assign('percentage', round(($day['day']/365)*100));
        $this->assign('user', $data);
        return  $this->fetch();
    }

    /**
     *  头像
     */
    public function avatar()
    {
        return  $this->fetch();
    }

    /**
     * 收藏
     */
    public function collection()
    {
        $this->title="个人收藏";
        $user_info = Db::table('s_user_info')->alias('u')->leftJoin(' s_member m','m.id = u.member_id')
            ->leftJoin(' s_vip_group v','v.id = u.vip_group_id')->field('u.nick_name,u.id,u.headimg,m.user_no,v.vip_name')
            ->where(array('u.id'=>$this->userInfo['id']))->find();
            
        $this->data=$this->card_logic ->getCollectionData($user_info['id']);
        $this->assign("userInfo",$user_info);
        return  $this->fetch();
    }
    /**
     * 交换V网
     */
    public function addCard()
    {
        if(Input('get.mycard_type') == 1){
            //当分享后主页收藏时  只收藏不加好友
            $visit_id = Input("get.visit_id");
            $res = Db::table('s_collection')->where(array('user_id'=>$this->userInfo['id'],'object_id'=>Input('get.id')))->find();
            if(!$res){
                $this->card_logic ->addCollection($this->userInfo['id'],Input('get.id'),$visit_id);
            }
            header("location:".url('Home/User/collection'));
        }
        
        $user_id = $this->userInfo['id'];
        if ($this->request->isPost()) {
            $where = array(
                'user_id' => $user_id,
                'object_id' => Input('post.id'),
                'msg'     => Input('post.text'),
            );
            if($this->card_logic ->applyCards($where)){
                $visit_id = Input("post.visit_id");
                $this->card_logic ->addCollection($this->userInfo['id'],Input('post.id'),$visit_id);
                if($visit_id){//通过分享
                    return json(array('status'=>1,'msg'=>'提交成功','url'=>url('Home/Nologin/card_jump',$this->userInfo['id'])));
                }else{//通过加好友
                    return json(array('status'=>1,'msg'=>'提交成功','url'=>url('Home/Trade/search')));
                }
            }else{
                return json(array('status'=>0,'msg'=>'请勿重复申请'));
            }
        }
        
        $id = Input('get.id');
        $result = Db::table('s_user_info')->find($id);
        $isCollection = Db::table('s_collection')->where(array('user_id'=>$this->userInfo['id'],'object_id'=>$id))->find();
        $this->assign('isCollection',$isCollection?1:0);
        $this->assign('id',$id);
        $this->assign('user_id',$user_id);
        $this->assign('result',$result);
        return  $this->fetch();
    }
    
    /**
     * 地区选择
     */
    public function region()
    {
        return  $this->fetch();
    }

    /**
     * 绑定微信
     */
    public function bindWeChat()
    {
        $info = $this->user_logic->getUserInfo($this->userInfo['member_id']);
        $wxInfo = array();
        if($info['wx_info']){
            $wxInfo = json_decode($info['wx_info'],true);
        }
        
        $wxInfo['head_img'] = $info['wx_ewm_url'];
        $this->assign('info',$wxInfo);
        return  $this->fetch();
    }

    //新微信绑定
    public function binding()
    {
        $this->phone=$this->userInfo['phone'];
        return  $this->fetch();
    }
    
    //解除微信绑定
    public function unbound()
    {
        $this->phone=$this->userInfo['phone'];
        return  $this->fetch();
    }

    /**
     * 绑定账号
     */
    public function bindAccount()
    {
        if($this->request->isPost()){
            // 检查账号，拿去ID
            $userInfo = $this->user_logic->getUserInfoOption(array('phone'=>Input('post.account'),'password'=>md5(Input('post.password'))));
            if(!$userInfo)
                $this->error('账号不存在');
            if($userInfo['id'] == $this->userInfo['id'])
                $this->error('自己不能与自己绑定');
            $this->user_logic->bindAccount($this->userInfo['id'],$userInfo['id']);
            header("location:".url('Home/User/changeAccount'));
        }
        
        return  $this->fetch();
    }

    /**
     * 账号切换
     */
    public function changeAccount()
    {
        if(Input('get.id') > 0){
            $data = $this->user_logic->changeAccount(Input('get.id'));
            session("user_info",$data);
            $openid=session('openid')?session('openid'):'';
            $wx_info=session('openid_contents')?session('openid_contents'):'';
            if($openid!=''){
                Db::table('s_member')->Where('id='.$data['member_id'])->Save(['openid'=>$openid,'wx_info'=>$wx_info]);
            }
            header("location:".url('Home/User/usercenter'));
        }
        
        $list = $this->user_logic->getBindUser($this->userInfo['id']);
        foreach($list as $k => $v){
            if($v['user_id'] == $this->userInfo['id']){
                $current = $v;
                unset($list[$k]);
                break;
            }
        }

        $list = array_merge(array($current),$list);
        $this->assign('list',$list);
        return  $this->fetch();
    }

    /**
     * 删除账号
     */
    public function delAccount()
    {
        if(Input('get.id') > 0){
            $user_id = Input('get.id');
            $result = Db::table('s_account_relate')->where(['user_id' => $user_id])->delete();
            if ($result) {
                header("location:".url('Home/User/changeAccount'));
            }
        }
    }

    /**
     * V网切换
     */
    public function changeCard()
    {
        return  $this->fetch();
    }

    /**
     * 解绑账号
     */
    public function unbindAccount()
    {
        return  $this->fetch();
    }

    /**
     * 私隐保护
     */
    public function security()
    {
        if($this->request->isPost()){
            $name = Input('post.name');
            $value = Input('post.value');
            Db::table('s_user_info')->where(array('id'=>$this->userInfo['id']))->update(array($name=>$value));
        }
        $this->assign('info',Db::table('s_user_info')->where(array('id'=>$this->userInfo['id']))->find());
        return  $this->fetch();
    }

    /**
     * 私隐保护详细
     */
    public function securityList()
    {
        if($this->request->isPost()){
            $name = Input('post.name');
            $value = Input('post.value');
            Db::table('s_user_info')->where(array('id'=>$this->userInfo['id']))->update(array($name=>$value));
        }
        $this->assign('info',Db::table('s_user_info')->where(array('id'=>$this->userInfo['id']))->find());
        return  $this->fetch();
    }
    
    public function test_pay()
    {
        $data=[
            'connect_id'=>1,
            'type' =>2,
            'order_type'=>1,
            'pay_money' =>$_GET['price'],
            'pay_title'=>'XX活动报名费用',
            'pay_stitle'=>'报名费用',
            'pay_tag'=>'费用',
            'openid'=>'oWlMg06NjGS_V_HRtdwP1IbMQGzM',
        ];
        //$res=get_obj('pay_test')->add_pay_log($data);
        //各种test 数据
        $data=[
            'logo'=>'/test.jpg',
            'country'=>1,
            'province'=>2,
            'city'=>3,
            'area'=>4,
            'name'=>'八月会',
            'intro'=>'八月商会',
            'user_id'=>2,
        ];
        $res=get_obj('trade_area')->trade_area_insert($data);
        var_dump($res);
    }
    
    /**
     *银行卡列表
     */
    public function banks()
    {
        if($this->request->isPost()){
            $post = Input("post.");
            $data['id']=$post['id'];
            $data['name']=$post['name'];
            $data['bank_name']=$post['bankname'];
            $data['branch_name']=$post['branchname'];
            $data['number']=$post['banknumber'];

            $type=$post['type'];//公众号值为1

            $res['type']=5;
            $res['phone']=$post['phone'];
            $res['code']=$post['code'];
            $result=$this->member_logic->CheckUserPinCode($res);
            if(!$result && $type == 1){
                $array_return['status']=0;
                $array_return['msg']="验证码错误";
                return json($array_return);
            }

            if(!$data['name']){
                $array_return['status']=0;
                $array_return['msg']="请输入开户名！！";
                return json($array_return);
            }
            if(!$data['bank_name']){
                $array_return['status']=0;
                $array_return['msg']="请输入银行名！！";
                return json($array_return);
            }

            if(!$data['id']){
                unset($data['id']);
                $data['user_id']=$this->userInfo['id'];
                $result = Db::table('s_users_bank')->insert($data);
                if($result){
                    $array_return['status']=1;
                    $array_return['msg']="银行卡资料绑定成功，您可以使用提现功能了！";

                }else{
                    $array_return['status']=0;
                    $array_return['msg']="银行卡资料绑定失败，请联系管理员！";
                }
            }else{
                $result = Db::table('s_users_bank')->update($data);
                if($result!==false){
                    $array_return['status']=1;
                    $array_return['msg']="银行卡资料修改成功，您可以使用提现功能了！";
                }else{
                    $array_return['status']=0;
                    $array_return['msg']="银行卡资料修改失败，请联系管理员！";
                }
            }
            return json($array_return);
        }else{
            if($this->userInfo['id'] == 1){
                $this->error('您不能绑定银行卡');
            }
            $this->title = '绑定银行卡';
            //获取注册手机号
            $banks = Db::table('s_users_bank')->where(array('user_id'=>$this->userInfo['id'],'status'=>1))->order('id desc')->find();
            $banks['zc_phone'] = Db::table('s_member')->where(array('id'=>$this->user_info['member_id']))->column('phone');

            $this->assign('banks', $banks);
            return  $this->fetch();
        }
    }

    /**
     * 隔空传
     */
    public function frimessage()
    {
        if($_POST){
            $post = Input('post.');
            if(empty($post['mobile'])||!preg_match("/^1[3456789]{1}\d{9}$/",$post['mobile'])){
                $this->array_return['code']=1;
                $this->array_return['msg']="手机号码格式错误!";
                return json($this->array_return);
            }
            $msg_num = Db::table('s_msg_num')->where(array('user_id'=>$post['user_id'],'add_time'=>array('gt',strtotime(date('Ymd')))))->count();

            if($msg_num>=10){
                return json(array('code'=>1,'msg'=>'您发送的条数已经达到今天上线'));
            }

            $resu = array();
            $resu['phone'] = $post['mobile'];
            $resu['user_id'] = $post['user_id'];
            $resu['add_time']=time();
            Db::table('s_msg_num')->insert($resu);
            $par=array('nick_name'=>$this->userInfo['nick_name'],'spopenid'=>$post['openid'],'user_id'=>$post['user_id']);
            $res=$this->member_logic->sendFriendMessage($post['mobile'],$par);
            if($res['status']=="0")
            {
                $this->array_return['code']=0;
                $this->array_return['msg']="发送成功";
            }else{
                $this->array_return['code']=1;
                $this->array_return['msg']="发送失败";
            }
            return json($this->array_return);

        }
        $this->openid = Db::table('s_member')->where(array('id'=>$this->user_info['member_id']))->column('openid');
        return  $this->fetch();
    }
    
    public function success2()
    {
		$this->title="注册成功";
        return  $this->fetch();
	}
	
	//收藏删除
    public function del_collection()
    {
        $id = Input('post.id');
        if($id){
            $res = Db::table('s_collection')->where(array('user_id'=>$this->userInfo['id'],'object_id'=>$id))->delete();
            if($res){
                return json(array('code'=>0,'msg'=>'操作成功'));
            }
        }else{
            return json(array('code'=>1,'msg'=>'操作失败'));
        }
    }
    
    //收藏备注
    public function bei_collection()
    {
        $id = Input('post.id');
        $name = Input('post.name');
        if($id){
            $res = Db::table('s_collection')->where(array('user_id'=>$this->userInfo['id'],'object_id'=>$id))->update(array('name'=>$name));
            if($res){
                return json(array('code'=>0,'msg'=>$name));
            }
        }else{
            return json(array('code'=>1,'msg'=>'操作失败'));
        }
    }
    
    //app下载
    public function app()
    {
        return  $this->fetch();
    }

    //异步通知微信公众号
 	public function sendMsg()
 	{
        $uid = isset($_POST['id']) ? $_POST['id'] : 0;
 		if (!$uid) { return; }
        //注册用户信息
        $newuser =  Db::table('s_user_info')->where(array('id'=>$uid))->field('id,member_id,nick_name,mobile,rec_user_id')->find();
        $new_mobile = substr($newuser['mobile'],0,3).'****'.substr($newuser['mobile'],7);
        $new_nick_name = substr($newuser['nick_name'],0,3).'****'.substr($newuser['nick_name'],7);

        $data = [ 'touser' => 'openid', 'template_id' => 'hyrBnari32A6rsTzJy340KzIyVe3yAaT-BkIoTOKRFg', 'url' => 'http://wx.yxm360.com/Home/Card/getMyFans',
		 'data' => [ 'first' => [ 'value' => '您有新的用户：'.$new_mobile, 'color' => '#173177', ],
		 'keyword1' => [ 'value' =>$new_nick_name, 'color' => '#173177', ],
		 'keyword2' => [ 'value' =>$new_mobile, 'color' => '#173177', ],
		 'keyword3' => [ 'value' =>date('Y年m月d日 H:i'), 'color' => '#173177', ],
		 'remark' => [ 'value' => '该用户刚刚注册，还没有成为创客，点击查看', 'color' => '#173177',]]];

 		$json = file_get_contents("http://wx.yxm360.com/ThinkPHP/Library/Vendor/wxsdkapi/access_token.php");
		$fullData = json_decode($json);//适应旧版
		$jsonData = $fullData->access_token;//适应旧版
		$access_token = $jsonData->value;

		//查找上级uid
		$fans =  Db::table('s_users_fans')->where(array('object_id' => $uid,'level'=>2))->field('user_id')->select();
		$uids = array_column($fans,'user_id');
        $uids = implode(',',$uids);
	 
        //查询openid
        $map = array();
        $map['id']  = array('in',$uids);
        $users = Db::table('s_user_info')->where($map)->field('member_id')->select();
        $uids = array_column($users,'member_id');
        $uids = implode(',',$uids);
    
        //查询openid
        $map['id']  = array('in',$uids);
        $udata = Db::table('s_member')->where($map)->field('openid')->select();
		foreach ($udata as $val) 
		{
			 if (!$val['openid']) { continue ; }
			 $data['touser'] = $val['openid'];
			 $fp = fsockopen('api.weixin.qq.com', 80, $error, $errstr, 1);
			 $params = json_encode($data,JSON_UNESCAPED_UNICODE); 
 			 $http = "POST /cgi-bin/message/template/send?access_token={$access_token} HTTP/1.1\r\nHost: api.weixin.qq.com\r\nContent-type: application/x-www-form-urlencoded\r\nContent-Length: " . strlen($params) . "\r\nConnection:close\r\n\r\n$params\r\n\r\n";
			 fwrite($fp, $http); 
			 fclose($fp); 
		}
        //添加默认模板
        $banner = array(
            array(
                'user_id'=>$uid,
                'img_url'=>'https://wap.yxm360.com/Public/Home/images/img/banner1.png',
                'sort'=>0,
                'createtime'=>time(),
            ),
            array(
                'user_id'=>$uid,
                'img_url'=>'https://wap.yxm360.com/Public/Home/images/img/banner2.png',
                'sort'=>1,
                'createtime'=>time(),
            ),
            array(
                'user_id'=>$uid,
                'img_url'=>'https://wap.yxm360.com/Public/Home/images/img/banner3.png',
                'sort'=>2,
                'createtime'=>time(),
            ),
            array(
                'user_id'=>$uid,
                'img_url'=>'https://wap.yxm360.com/Public/Home/images/img/banner4.png',
                'sort'=>3,
                'createtime'=>time(),
            )
        );
        Db::table('s_user_img')->addAll($banner);
        $nav_type_id = Db::table('s_mode_type')->insert(array('title'=>'链接一切','user_id'=>$uid,'type'=>0,'addtime'=>date('Y-m-d H:i:s')));
        $nav = array(
            array(
                'user_id'=>$uid,
                'name'=>'对话中国品牌栏目',
                'icon_url'=>'https://wap.yxm360.com/Public/Home/images/img/nav1.png',
                'add_time'=>time(),
                'sort'=>0,
            ),
            array(
                'user_id'=>$uid,
                'name'=>'2.0发布会',
                'icon_url'=>'https://wap.yxm360.com/Public/Home/images/img/nav2.png',
                'add_time'=>time(),
                'sort'=>1,
            ),
            array(
                'user_id'=>$uid,
                'name'=>'1.0发布会',
                'icon_url'=>'https://wap.yxm360.com/Public/Home/images/img/nav3.png',
                'add_time'=>time(),
                'sort'=>2,
            ),
        );
        Db::table('s_users_nav')->addAll($nav);
        $show_type_id = Db::table('s_mode_type')->insert(array('title'=>'展示一切','user_id'=>$uid,'type'=>2,'addtime'=>date('Y-m-d H:i:s')));
        $show = array(
            array(
                'title'=>'英国前首相',
                'thumb'=>'https://wap.yxm360.com/Public/Home/images/img/show1.png',
                'user_id'=>$uid,
                'sort'=>0,
                'add_time'=>time(),
            ),
            array(
                'title'=>'影响力品牌',
                'thumb'=>'https://wap.yxm360.com/Public/Home/images/img/show2.png',
                'user_id'=>$uid,
                'sort'=>1,
                'add_time'=>time(),
            ),
            array(
                'title'=>'总冠名CCTV栏目',
                'thumb'=>'https://wap.yxm360.com/Public/Home/images/img/show3.png',
                'user_id'=>$uid,
                'sort'=>2,
                'add_time'=>time(),
            ),
            array(
                'title'=>'山西运城发布会',
                'thumb'=>'https://wap.yxm360.com/Public/Home/images/img/show4.png',
                'user_id'=>$uid,
                'sort'=>3,
                'add_time'=>time(),
            ),
            array(
                'title'=>'德国前总统',
                'thumb'=>'https://wap.yxm360.com/Public/Home/images/img/show5.png',
                'user_id'=>$uid,
                'sort'=>4,
                'add_time'=>time(),
            ),
            array(
                'title'=>'洋小秘创始人',
                'thumb'=>'https://wap.yxm360.com/Public/Home/images/img/show6.png',
                'user_id'=>$uid,
                'sort'=>5,
                'add_time'=>time(),
            ),
        );
        Db::table('s_card_content')->addAll($show);
    }
	
    //异步好友申请通知微信公众号
 	public function sendApplyMsg()
 	{
 	 	$uid = isset($_POST['id']) ? $_POST['id'] : 0;
 	 	$user_id = isset($_POST['user_id']) ? $_POST['user_id'] : 0;
 		if (!$uid) { return; }
 		$user = Db::table('s_user_info')->where(array('id'=>$uid))->field('id,member_id,nick_name,mobile')->find();//通知人信息 
 		$rec_member = Db::table('s_member')->where(array('id'=>$user['member_id']))->column('openid');
 		if ($rec_member) {  
 			$user = Db::table('s_user_info')->where(array('id'=>$user_id))->field('id,member_id,nick_name,mobile')->find();//申请人信息 
 			sendWxTemplateMessages('SendAddFriends',array('wecha_id'=>$rec_member,'href'=>get_current_Host().url('/Home/Service/exchangev'), 'first' => '您有新的好友申请', 'keyword1' =>$user['nick_name'], 'keyword2' => $user['mobile'], 'keyword3'=> date("Y-m-d H:i:s"),'remark' => '该用户请求添加你为好友，快去审核吧，点击查看'));
 		}
     }
     
     public function square()
     {
     	$this->id=$this->userInfo['id'];
        return  $this->fetch();
     }
}