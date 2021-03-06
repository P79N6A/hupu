<?php
/** 订单管理 */
namespace app\home\controller;
use app\common\controller\HomeBase;
use app\api\logic\ArticleList  as artLogic;
use app\api\logic\User  as userLogic;
use app\api\logic\VipList  as vipLogic;
use app\api\logic\ArticleList  as artLogic;
use think\Db;

class Order extends HomeBase
{

    private $array_return = array("ResultType"=>1,"Message"=>"success","AppendData"=>['name'=>'haohao']);
	private $art_logic = null;
	private $art_logic = null;
	private $user_logic = null;
	private $vip_logic = null; 
	
 	function initialize()
 	{
 		parent::initialize();
        $this->art_logic = new artLogic();
        $this->user_logic = new userLogic();
        $this->vip_logic = new vipLogic();
        $this->art_logic = new artLogic();
    }
    
    /**
     * 购买V网
     */
    public function buyCard()
    {
        return $this->fetch();
    }

    /**
     * 更换推荐人
     */
    public function changeRecommender()
    {
        return $this->fetch();
    }

    /**
     * 微信支付回调
     */
    public function payBack()
    {
        //支付回调
        $payResult =  file_get_contents('php://input');
        $config = [
            //互浦科技
          'APPID'              => 'wx4cc2b44cf67fbf21', // 微信支付APPID
          'APPSECRET'          => '5fe63a1a6bfe9b5adb7d8a3da7080fd4',  //公众帐号secert
           'MCHID'         =>'1512255861',
           'KEY'         =>'ea9a841c863650d610c165392a59d36e',
 			'NOTIFY_URL'=> 'http://wx.yxm360.com/index.php?s=/Home/Order/payBack/', // 接收支付状态的连
            'TOKEN'=>  'ANTIPHON',
        ];


        Vendor('WeixinPay.Weixinpay');
        $wxpay = new \Weixinpay($config);
        $result = $wxpay->notify();//回调参数
        $order = Db::table("s_payLog")->where(array("th_sn"=>$result['out_trade_no']))->find();
        $result['order_id'] = $order['connect_id'];
        if($order['order_type']==1 || $order['order_type'] == 6){
            //会员回调
            $flag=$this->vip_logic->BuyCallBack($result);
        }elseif($order['order_type']==2){
            //续费年费
            $result['data']=json_decode($order['mark'],1);
            $flag=$this->vip_logic->payCallback($result);
        }elseif($order['order_type']==3){
            //文章打赏
            $result['data']=json_decode($order['mark'],1);
			$result['data']['type'] = 2;
            $flag=$this->art_logic->PayCallback($result);
        }elseif($order['order_type']==4){
            //加入商会
            $result['data']=json_decode($order['mark'],1);
            $flag=$this->user_logic->payCallback($result);
        }elseif($order['order_type']==7){
            //参加会议
            $result['data']=json_decode($order['mark'],1);
            $flag=$this->user_logic->payComference($result);
        }elseif($order['order_type']==8){
            //参加会议
            $result['data']=json_decode($order['mark'],1);
            $flag=$this->user_logic->payHk($result);
        }elseif($order['order_type']==12){
            //参加会议
            $result['data']=json_decode($order['mark'],1);
            $flag=$this->user_logic->payXmb($result['data']);
        }

        file_put_contents(APP_ROOT.'/test.txt', PHP_EOL.json_encode($result).PHP_EOL,FILE_APPEND);
        echo "SUCCESS";
    }

    /**
     * 微信支付取消订单
     */
    public function closeOrder()
    {
        //初始化参数
        $out_trade_no = isset($_REQUEST['out_trade_no']) ? trim($_REQUEST['out_trade_no']) : '';
        $data = array('status'=>0,'msg' => '缺少参数');
        if (!$out_trade_no) { 
            echo json_encode($data);
            die();
        }

        Vendor('WeixinPay.Weixinpay');
        $wxpay = new \Weixinpay($config);
        $result = $wxpay->closeOrder($out_trade_no);//回调参数
        if($result['return_code'] == 'SUCCESS'){
            $ord_obj = Db::table('s_vip_order');
            $ord_obj->where('out_trade_no='.$out_trade_no.' and pay_status=0')->delete();
        }
    }

    /**
     * 将xml转为array
     * @param  string $xml xml字符串
     * @return array       转换得到的数组
     */
    private function toArray($xml)
    {
        //禁止引用外部xml实体
        libxml_disable_entity_loader(true);
        $result= json_decode(json_encode(simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA)), true);
        return $result;
    }

    /**
     * 确认订单
     */
    public function orderConfirm()
    {
        if($this->request->isPost()){

        }else{
            $order_id = Input("get.order_id");
            if(empty($order_id)){
                $this->error("访问错误");exit;
            }

            $referer = Input("get.referer");
            if ($referer !=null ){
                $order =  Db::table('s_vip_order')->where(array("id"=>$order_id))->find();
                if ($order['pay_status'] == 1 ){
                    header("location:".url("Home/User/usercenter"));
                }
            }

            $order = Db::table('s_user_info')->alias('u')->leftJoin(' s_user_info uf','uf.id = u.rec_user_id')
                ->leftJoin(' s_vip_order o','o.user_id = u.id')->leftJoin(' s_vip_list v','v.id = o.vip_list_id')
                ->leftJoin(' s_vip_group g','g.id = v.vip_group_id')->leftJoin(' s_member m','m.id = u.member_id')
                ->field('o.*,uf.id as share_id,uf.nick_name as share_name,g.vip_name,m.openid')
                ->where(array('o.id'=>$order_id))->find();

            //判断是否是是微信公众号环境start
            $user_agent = $_SERVER['HTTP_USER_AGENT'];

            if (strpos($user_agent, 'MicroMessenger') === false) {
                // 不是微信浏览器
                $type = 6;
                $is_wechat = 0;
                $openid = "";

            }else{
                $type = 2;
                // 是微信环境
                $is_wechat = 1;
                $openid = $order['openid']?$order['openid']:session('openid');
                if($openid==''){
                    $this->error("您暂时无法进行支付");exit;
                }
            }

            $data=[
                'connect_id'=>$order['id'],
                'type' =>$type,
                'order_type'=>1,
                'pay_money' =>$order['price'],
                //'pay_money' =>0.01,
                'pay_title'=>$order['vip_name'],
                'pay_stitle'=>$order['vip_name'],
                'pay_tag'=>'费用',
                'mark'=>"",
                'openid' => $openid,
                'order_sn'=>$order['order_no'],
            ];


            $res=get_obj('pay_test')->add_pay_log($data);
            $res=(json_decode($res,true));

            $host_url = get_current_host_url();
            if ($res['obj']['pra']['mweb_url'] !=null){
                $redirect_url = $host_url."/Home/Order/orderConfirm/order_id/".$order_id."/referer/1.html";
                $mweb_url = $res['obj']['pra']['mweb_url']."&redirect_url=".urlencode($redirect_url);
                $this->assign('mweb_url',$mweb_url);
            }

            $this->assign('pay_info',$res['obj']);
            $this->assign('order',$order);
            $this->assign('type',$type);
            $this->assign('is_wechat',$is_wechat);
            $this->assign('order_id',$order_id);

            if ($referer == 1){
                $this->assign('referer',1);
            }else{
                $this->assign('referer',0);
            }
			$this->title="确认订单";
            return $this->fetch();
        }
    }

    /**
     * 确认订单
     */
    public function orderConfirm2()
    {
        $order_id = Input("get.order_id");
        if(empty($order_id)){
            $this->error("访问错误");exit;
        }

        $order = Db::table('s_user_info')->alias('u')->leftJoin(' s_user_info uf','uf.id = u.rec_user_id')
            ->leftJoin(' s_vip_order o','o.user_id = u.id')->leftJoin(' s_vip_list v','v.id = o.vip_list_id')
            ->leftJoin(' s_vip_group g','g.id = v.vip_group_id')->leftJoin(' s_member m','m.id = u.member_id')
            ->field('o.*,uf.id as share_id,uf.nick_name as share_name,g.vip_name,m.openid')
            ->where(array('o.id'=>$order_id))->find();

        $this->assign('order',$order);
        $this->assign('order_id',$order_id);
        return $this->fetch();
    }

    /**
     * 确认订单
     */
    public function orderPay()
    {
        $order_id = Input("post.order_id");
        if(empty($order_id)){
            $this->error("访问错误");exit;
        }
        $flag = $this->vip_logic->buyByMiBi($order_id);
        if ($flag == 1){
            return json((array('status'=>1,'msg'=>'支付成功'));
        }elseif($flag == 0){
            return json((array('status'=>0,'msg'=>'支付失败'));
        }else{
            return json((array('status'=>2,'msg'=>'重复支付'));
        }
    }

    public function payConfirm()
    {
        if($this->request->isPost()){
            $order_id=Input("post.order_id");
            $order = Db::table('s_vip_order')->where(array("id"=>$order_id))->find();
            if ($order['pay_status'] == 1 ){
                $this->array_return['status']=1;
                $this->array_return['msg']="付款成功";
                return json(($this->array_return);
            }else{
                $this->array_return['status']=0;
                $this->array_return['msg']="付款未成功";
                return json(($this->array_return);
            }
        }
    }

    /**
     * 短信回调
     */
    public function code_back()
    {
        $msgid = Input('get.msgid');
        $mobile = Input('get.mobile');
        $status = Input('get.status');
        if($status){
            if($status == 'DELIVRD'){
                Db::table('s_new_msg')->where(array('mobile'=>$mobile,'msgid'=>$msgid))->update(array('status'=>1,'overtime'=>time()));
            }else{
                $code = Db::table('s_new_msg')->where(array('mobile'=>$mobile,'msgid'=>$msgid))->column('code');
                if($code){
                    $smsapi = "http://47.95.217.41:8088/";
                    $user = "shmingxin"; //短信平台帐号
                    $pass = 'shmingxin123321'; //短信平台密码
                    $content=$code;//要发送的短信内容
                    $sendurl = $smsapi."sms.aspx?action=send&userid=12&&account=".$user."&password=".$pass."&mobile=".substr($mobile,2)."&content=".$content."&sendTime=&extno=";
                    $res =simplexml_load_string(file_get_contents($sendurl)) ;
                    if(((String)$res->returnstatus)=='Success'){
                        Db::table('s_new_msg')->where(array('mobile'=>$mobile,'msgid'=>$msgid))->update(array('status'=>1,'overtime'=>time()));
                    }
                }
            }
        }
    }
}