<?php/** 留言管理 */namespace app\home\controller;use think\Db;use app\common\controller\HomeBase;use app\api\logic\User  as userLogic;
class Chat extends HomeBase{	private $user_logic = null;	  	function initialize() 	{ 		parent::initialize();        $this->user_logic = new userLogic();    }    
    /**
     * 我的留言
     */
    public function myMessage()    {
        if(Input('get.id') >0 && Input('get.id')!=$this->userInfo['id']){// 给别人的留言
            $where = array('object_id'=>$this->userInfo['id'],'user_id'=>Input('get.id'));
        }else {// 别人给我的留言
            $where = array('user_id'=>$this->userInfo['id']);
        }
        $this->title = '我的留言';
        $message = $this->user_logic->getMyMsg($where);
        $this->assign('result',$message);
        $this->assign('regulate',Input('get.regulate','') ? 1 : 0 );
        $this->assign('userInfo',$this->userInfo);
        return $this->fetch();
    }
    /**
     * 留言详情
     */
    public function messageInfo()    {
        if(Input('get.user_id') == $this->userInfo['id']){
            $my = 1;
        }
        $message = $this->user_logic->getMyMsgInfo(Input('get.id'),$my);
        $this->assign('result',$message);
        return $this->fetch();
    }
    /**
     * 添加留言
     */
    public function addMessage()    {
        if(isPOST){
            if($this->user_logic->addBlog(Input('post.id'),$this->userInfo['id'],Input('post.content'))){
                header("location:".url("Home/Chat/myMessage",array('id'=>Input('post.id'))));
            }
        }
        $userInfo = Db::table('s_user_info')->find(Input('get.id'));
        $this->assign('userInfo',$userInfo);
        $this->assign('user_id',Input('get.id'));
        return $this->fetch();
    }
    /**
     *  设置信息状态(已读)
     */
    public function setStatus()    {
        $post = Input('post.');
        $this->user_logic->setMsgRead(array('id'=>array('in',empty($post['ids']) ? array(-1) : $post['ids'])));
        header("location:".$_SERVER['HTTP_REFERER']);
    }
    /**
     *  删除
     */
    public function delete()    {
        $post = Input('post.');
        $this->user_logic->getMyMsgDel(array('id'=>empty($post['ids']) ? array(-1) : array('in',$post['ids'])));
        header("location:".$_SERVER['HTTP_REFERER']);
    }
    /**
     * 签到领取积分
     */
    public function sign()    {
		$this->openid = $this->userInfo['openid'];
		$this->id = $this->userInfo['id'];
		$this->inte = Db::table('s_user_info')->where(array('id'=>$this->userInfo['id']))->column('inte');
    	return $this->fetch();
    }
    //龙虎榜
    public function charts()    {
    	$this->title="龙虎榜";
		$this->openid = $this->userInfo['openid'];
		$this->id = $this->userInfo['id'];
    	return $this->fetch();
	}
	 public function product()	 {
    	$this->title="产品列表";
		$this->openid = $this->userInfo['openid'];
        $this->pid=$_GET['pid'];        
		$this->id = $this->userInfo['id'];
    	return $this->fetch();
	}
	 public function group()	 {
    	$this->title="产品编辑";
		$this->openid = $this->userInfo['openid'];
		$this->id = $this->userInfo['id'];
        $this->pid=$_GET['id'];
        if($_POST){
            $this->pid=$_POST['pid'];
            if($_POST["img"]){
                foreach($_POST["img"] as $k=>$v){
                    unset($_POST['pid']);
                    $img[]=$v;
                }
                $this->img=$img;
                $_SESSION["product_img_banner"]=$img;
            }
            if($_POST["img_detail"]){
                foreach($_POST["img_detail"] as $k=>$v){
                    unset($_POST['pid']);
                    $img_detail[]=$v;
                }
                $this->img_detail=$img_detail;
            }  
        }
    	return $this->fetch();
	}
    public function productbanner()    {
        $this->title="产品轮播图";
        $this->id = $this->userInfo['id'];
        $this->pid=$_GET['pid'];
        return $this->fetch();
    }
    public function details()    {
        $this->title="产品详情图";
        $this->id = $this->userInfo['id'];
        $this->pid=$_GET['pid'];
        return $this->fetch();
    }
    public function parameter()    {
        $this->id = $this->userInfo['id'];
        $this->pid=$_GET['pid'];
        return $this->fetch();
    }
    public function groups()    {
        $this->title="产品添加";
        $this->openid = $this->userInfo['openid'];
        $this->id = $this->userInfo['id'];
        $this->pid=$_GET['pid'];
        return $this->fetch();
    }
    //积分列表
    public function integral()    {
       	$this->id = $this->userInfo['id'];
       	$this->inte = Db::table('s_user_info')->where(array('id'=>$this->userInfo['id']))->column('inte');
        return $this->fetch();
    }
    //积分展示
    public function Integral_record()    {
       	$this->id = $this->userInfo['id'];
        return $this->fetch();
    }
    //积分转赠
    public function choose()    {
        return $this->fetch();
    }
	//  积分转赠选项
    public function Integral_type()    {
        return $this->fetch();
    }
	//  积分赠送数值
    public function give()    {
       	$user = Db::table('s_user_info')->alias('u')->leftJoin(' s_member m','m.id = u.member_id')			->where(array('u.id'=>Input('get.nameid')))->field('u.id,u.nick_name,u.headimg,m.phone')->find();
       	if(substr($user['headimg'],0,4) != 'http'){
       		$user['headimg'] = 'http://'.$_SERVER['HTTP_HOST'].$user['headimg'];
       	}
       	$this->user = $user;
        return $this->fetch();
    }
    //  积分转赠领取记录
    public function record()    {
        return $this->fetch();
    }
}