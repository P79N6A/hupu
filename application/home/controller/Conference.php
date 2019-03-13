<?php
/** 参会签到*/
namespace app\home\controller;
use think\Db;

class Conference
{
    private $unionid;
    public function _initialize()
    {
        $this->unionid = Db::table('s_user_info')->where('id=468')->column('unionid');
        $this->assign('unionid',$this->unionid);
    }

    /**
     *参会证
     */
    public function Conference()
    {        
        return $this->fetch("Conference/att_login");
    }
	
    public function photograph()
	{
        $this->nameid = Input('get.nameid');
        return $this->fetch();
    }
    
	public function card()
	{
        $this->nameid = Input('get.nameid');
        return $this->fetch();
    }
    
	public function show()
	{
        return $this->fetch();
    }

	public function att_login()
	{
        if (isPOST) { exit; }
        if (empty($_GET['openid'])) {
            header('Location:/getunionid.php?target_url='.urlencode('http://wx.yxm360.com/Home/Conference/att_login'));
            exit;
        } else {
            if ($_GET['openid'] == null || $_GET['openid'] == '') exit;
            $this->assign('yxmuseropenid', $_GET['openid']);
        }
        
        return $this->fetch();
    }

	public function center()
	{
		return $this->fetch();
    }

    /**
     * 生成
     * 微信
     * 支付包
     */
    public function adescription()
    {
    	if(isPOST){
        	$this->error("访问错误");exit;
        } 
        	
        $nameid = Input('get.nameid');
        $order_id = Input("get.order_id");
        if(!empty($nameid)){
            //第一次访问，生成订单
            //1判断是否存在订单
        	$counts = Db::table("s_signin_usre")->alias('a')->leftJoin("s_activity_order b","a.s_userid=b.s_activity_userid")
        	->leftJoin("s_activity c","a.s_activity_id=c.s_activity_id")            
        	->field('a.*,b.*,c.a_price')->where(array("a.s_useruuid"=>$nameid))->find();
                
        	$price=700;
        	if($counts["s_vtitle"]=="1"){
        		$price=1280;
        	}                   
        	if($counts["s_order_id"]==null || $counts["s_order_id"]==""){                 
        		$order_id=getOrderSn();
        		$mydata=array("s_order_no"=>$order_id,"s_order_createtime"=>date('Y-m-d H:i:s'),"s_order_start"=>1     
        		,"s_activity_userid"=>$counts["s_userid"],"s_activity_userphone"=>$counts["s_userphone"]         
        		,"s_order_price"=>$price,"s_user_wxopenid"=>$counts["openid"],"s_user_id"=>$counts["id"]);
                        
               Db::table("s_activity_order")->data($mydata)->insert();
            }else{
                Db::table("s_activity_order")->where(array("s_order_no"=>$counts["s_order_no"]))->data(array("s_user_wxopenid"=>$counts["openid"]))->update();
                $order_id=$counts["s_order_no"];
            }
        }
                
        if(empty($order_id)){ $this->error("访问错误");exit;}
		$referer = Input("get.referer");
        if ($referer !=null ){
        	$order = Db::table("s_activity_order")->where(array("s_order_no"=>$order_id))->find();
            if ($order['s_order_start'] == 1 ){
                header("location:".url("Home/User/usercenter"));
            }
        }

        $order = Db::table('s_activity_order')->alias('u')->field('u.*')->where(array('u.s_order_no'=>$order_id))->find();  
        //判断是否是是微信公众号环境start
        $user_agent = $_SERVER['HTTP_USER_AGENT'];
        if (strpos($user_agent, 'MicroMessenger') === false) {
            // 不是微信浏览器
        	$type = 6;
            $is_wechat = 0;
            $openid = "";
            $this->error("请前往微信公众号，从微信公众号打开系统");exit;
        }else{
            $type = 2;// 是微信环境
            $is_wechat = 1;
            $openid = $order['s_user_wxopenid'];
            if($order['s_user_wxopenid']==''){
            	$this->error("您暂时无法进行支付,请在次登录后支付");exit;
            }
        }

        $data=['connect_id'=>$order['s_order_id'],'type' =>2,'order_type'=>7,
                'pay_money' =>$order['s_order_price'],'pay_title'=>"签到费用",
              	'pay_stitle'=>"签到费用1",'pay_tag'=>'费用','mark'=>"",'openid' => $order['s_user_wxopenid'],
              'order_sn'=>$order['s_order_no'],];
                
        $res=get_obj('pay_test')->add_pay_log($data);
        $res=(json_decode($res,true));

        $this->assign('pay_info',$res['obj']);
        $this->assign('order',$order);
        $this->assign('type',2);
        $this->assign('order_id',$order_id);
        if ($referer == 1){
        	$this->assign('referer',1);
        }else{
            $this->assign('referer',0);
        }
        $this->price=$price;
        $this->nameid = Input('get.nameid');
                
    	return $this->fetch();
    }
    
    public  function  in_detail()
    {
        $this->phone = Input('get.phone');
        return $this->fetch();
    }

}