<?php
/** 卡片管理 */
namespace app\home\controller;
use think\Db;
use app\common\controller\HomeBase;
use app\api\model\UserInfo  as userModel;
use app\api\model\UsersNav  as navModel;
use app\api\model\UserView as viewModel;

use app\api\logic\UsersFans  as fansLogic;
use app\api\logic\Cards  as cardsLogic;
use app\api\logic\User  as userLogic;
use app\api\logic\VisitLog  as visitLogic;

include_once './ThinkPHP/Library/Vendor/Alioss/autoload.php';


class Card extends HomeBase
{
	private $user_mod = null;
	private $nav_mod = null;
	private $fans_logic = null;
	private $card_logic = null;
	private $user_logic = null;
	private $visit_logic = null;
	 
 	function initialize()
 	{
 		parent::initialize();
        $this->user_mod = new userModel();
        $this->nav_mod = new navModel();
        $this->fans_logic = new fansLogic();
        $this->card_logic = new cardsLogic();
        $this->user_logic = new userLogic();
        $this->visit_logic = new visitLogic();
    }
    
    /**
     * 上传V网图文图片
     */
    public function upImgCard()
    {
        $array_return=array("error"=>0,"filename"=>"","original"=>"","state"=>"SUCCESS","title"=>"","url"=>"");

        if(!empty($_FILES['upfile']['name']) && $_FILES['upfile']['size'] <= 3145728){
 
            if(substr($_SERVER['HTTP_HOST'],0,1) == 'w'){
                $start_name = 'cards';
            }else{
                $start_name = 'test';
            }
            if(substr($_FILES['pic']['type'],-3)=='png'){
                $save_name = $start_name.'/'.date('Ymd').'/'.time().rand(1,9999).'.png';
            }else{
                $save_name = $start_name.'/'.date('Ymd').'/'.time().rand(1,9999).'.jpg';
            }
            $Upload = new \Admin\Controller\UploadsController();
            $img = $Upload->Uploads($save_name,$_FILES['pic']);
            if($img){
                $array_return['filename']=$img;
                $array_return['url']=$array_return['filename'];
            }
        }else{
            $array_return['error']=1;
            $array_return['state']="图片大于3M或没有上传图片";
        }
        return json($array_return);
    }
    /**
     * 删除展示内容
     */
    public function  delCard()
    {
        $array_return=array("status"=>1,"msg"=>"success");
        $where['user_id']=$this->userInfo['id'];
        $where['id']=Input("post.id");
        $res = Db::table('s_card_content')->where($where)->find();
        if($res){
            $is = Db::table('s_card_content')->where($where)->delete();
            if($is){
                $re = Db::table('s_card_content_res')->where(array('card_content_id'=>$where['id']))->find();
                if($re){
                    Db::table('s_card_content_res')->where(array('card_content_id'=>$where['id']))->delete();
                }
                $array_return['msg']="删除成功";
            }else{
                $array_return['status']=0;
                $array_return['msg']="删除失败";
            }
        }
        
        return json($array_return);
    }
    /**
     * 展示内容
     */
    public function showCard()
    {
        $user_id=$this->userInfo['id'];

        $data = $this->card_logic->getCardContent($user_id);
        foreach ($data as &$v){
            if(strlen($v['title']) > 60){
                $v['title'] = substr($v['title'],0,60).'...';
            }
        }
        $this->data= $data;
        return $this->fetch();
    }
    
    public function sharePush()
    {
        //增加分享次数
        if(isPOST){
            $post=Input("post.");
            $this->visit_logic->upShare(['id'=>$post['id']]);
        }
    }

    /**
     * V网图文
     */
    public function showListCard()
    {
        if(isPOST){

        }else{
            $get=Input("get.");
            $where['id']=$get['id'];
            $data=Db::table('s_card_content')->where($where)->find();
            preg_match_all('/<\s*img\s+[^>]*?src\s*=\s*(\'|\")(.*?)\\1[^>]*?\/?\s*>/i',htmlspecialchars_decode($data['content']),$match);
            $data['content'] = preg_replace('/\<.+?\>/','',htmlspecialchars_decode($data['content']));

            $this->data_res = Db::table('s_card_content_res')->where(array('card_content_id'=>$get['id']))->select();
            $this->data = $data;
            $this->data_imgs = $match[2];
            $this->assign('user_id',$this->userInfo['id']);
        }
        
        return $this->fetch();
    }

    /**
     * 我的V网
     */
    public function myCard123()
    {
        $this->assign('isshare',0);
        //保存我的V网
        if(isPOST){
            $res=Input('post.');
            $nav_new_add=json_decode(htmlspecialchars_decode($res['nav_new_add']),true);
            foreach ($nav_new_add as $key => $value) {
                //添加导航
                $data['user_id']=$res['id'];
                $data['name']=$value['name'];
                $data['icon_url']=$value['img'];
                $data['jump_url']=$value['url'];
                $this->nav_mod->insert($data);
            }
            if(!empty($res['nav_new_del'])){
                //删除导航
                $this->nav_mod->where(array('id'=>array('in',$res['nav_new_del'])))->delete();
            }
            $this->user_logic->upUserInfo($res);
            header("location:".url("Home/Nologin/card_jump",array('id'=>Input('post.id'))));
        }

        if (cookie('openid')) {
            $openid=cookie('openid');
            $member_id = Db::table("s_member")->where(['openid'=>$openid])->column('id');
            $this->userInfo = Db::table("s_userInfo")->where(array('member_id'=>$member_id))->find();
            if ($this->userInfo['is_quit']) {
                $this->userInfo = null;
                header("location:".url("Home/Mycenter/login"));
            }
        }
        
        $user_id=Input('get.id')?Input('get.id'):$this->userInfo['id'];
        $this->assign("user_id",$user_id);
        if(empty($this->userInfo['id']) && empty($user_id)){
            if(!$_GET['openid']){
                $url=get_current_url();
                header('Location:/getunionid.php?target_url='.urlencode($url));
            }
        }
        $isshare=Input('get.isshare')?Input('get.isshare'):0;
        if(Input('get.id') == $this->userInfo['id']){
            $this->assign('isdisplay',1);
            if($isshare == 1){
                $this->assign('isshare',1);
            }
        }
        if(empty(Input('get.id')) && $this->userInfo['id']){
            $this->assign('isdisplay',1);
        }
        $share_id=empty($this->userInfo['id'])?$user_id:$this->userInfo['id'];
        $data=$this->user_logic->getUserData($user_id,$this->userInfo['id']);
        if($data['style']==3 || $data['style']==4){
            $data['background_img']=$data['headimg'];
        }
        if(!empty($data['background_img']) && getimagesize($data['background_img'])){

            $image = new \Think\Image();
            $image->open($data['background_img']);
            $size=getimagesize($data['background_img']);
            $data['background_size']=$size[1]/2.88;
        }
 
        if(empty($data['text_color'])){
            $data['text_color']="#000";
        }
        if(empty($data['link_color'])){
            $data['link_color']="#000";
        }
        if(empty($data['title_color'])){
            $data['title_color']="#fff";
        }
        if(empty($data['background_color'])){
            $data['background_color']="#fff";
        }
        if($this->userInfo['id']==$user_id){
            $this->title="我的V网";
        }else{
            $this->title=$data['nick_name']."的V网";
        }
        if(!empty($data['headimg']) && getimagesize($data['headimg'])){
            $image = new \Think\Image();
            $image->open($data['headimg']);
            $save_name='/Uploads/head/'."head_".time().".jpg";
            $image->thumb(100, 100,3)->update('.'.$save_name);
            $data['headimg']=substr($save_name,1);
        }
        //todo 默认音乐打开
        $data['auto_music'] = 1;
        $music=Db::table("s_music")->select();
        $this->nav_select_list = Db::table('s_navImg')->order('add_time desc')->select();
        $this->assign('music',$music);
        $this->assign('result',$data);
        $this->assign('share_id',$share_id);
        $this->assign('userInfo',$this->userInfo);

        //初始化
        if(!class_exists('\JSSDK'))
        {
            vendor('wxsdkapi.jssdk');
            vendor('wxpayapi.lib.WxPay#Config');
        }
        $jssdk = new \JSSDK(config('WEIXINPAY_CONFIG.APPID'),config('WEIXINPAY_CONFIG.APPSECRET'));
        $signPackage = $jssdk->GetSignPackage();
        $this->assign('signPackage',$signPackage);

        $this->assign('sharetitle',$data['nick_name']);
        $this->assign('desc',str_replace(PHP_EOL, ' ', $data['share_my_introduct']));
        $imgUrl = substr($this->userInfo['headimg'],1);
        if(!Input('get.id')){
            try {
                if(!empty($data['headimg']) && getimagesize($data['headimg'])){
                    $image = new \Think\Image();
                    if($image->open($this->userInfo['headimg'])){
                        $image->open($this->userInfo['headimg']);
                        $save_name='/Uploads/head/'."head_".time().".jpg";
                        $image->thumb(100, 100,3)->update('.'.$save_name);
                        $imgUrl=substr($save_name,1);
                        $imgUrl = 'http://wx.yxm360.com/'.$imgUrl;
                    }else{
                        $imgUrl = 'http://wx.yxm360.com/html/images/logo.jpg';
                    }
                }
            }catch (Exception $e) {
                $imgUrl = 'http://wx.yxm360.com/html/images/logo.jpg';
            }
        }else{
            $imgUrl='http://wx.yxm360.com/'.$data['headimg'];
        }

        $this->assign('imgUrl',$imgUrl);
        return $this->fetch();
    }

    /**
     *新的我的V网，只判断shareID，不能编辑
     */
    public function myCard()
    {
        $this->assign('isshare',1);
        if(!Input('get.id')) {
            //判断是否通过分享来的，不是分享来的就是要查看自己的V网，首先需要登录，其次需要判断是不是VIP,没登录要求登录，登录了判断是不是VIP
            if($this->userInfo['id']) {
                is_vip();
            }else{
                header("location:".url("Home/Mycenter/login"));exit;
            }
        }else{
            if(Input('get.id') != $this->userInfo['id']){
                //添加浏览记录
                $visit_id = $this->visit_logic->addVisitLog(array('object_id'=>Input('get.id'),'user_id'=>$this->userInfo['id']));
            }
        }
        $user_id=Input('get.id')?Input('get.id'):$this->userInfo['id'];
        if (is_null($user_id)) {
            $this->userInfo = null;
            header("location:".url("Home/Mycenter/login"));exit;
        }
        $this->assign("user_id",$user_id);
        $user = Db::table('s_user_info')->where(array('id'=>$user_id))->find();
        $this->unionid=Input('get.id')?$user['unionid']:$this->userInfo['unionid'];

        if ($this->userInfo['id'] == $user_id) {
            $this->title = "我的V网";
            $this->isdisplay = 1;
        } else {
            $this->title = $user['nick_name'] . "的V网";
            $this->isdisplay = 0;
        }
            //新版V网
            // 注意 URL 一定要动态获取，不能 hardcode.
        $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
        $url = "$protocol$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
        $this->assign('share_url', $url);
        $this->assign('sharetitle', $user['nick_name']);
        $contents = cutstr_html($user['share_my_introduct']);
        $contents =str_replace('"', '\'', $contents);
        $this->assign('desc', preg_replace('/\s/is','', $contents));

        if(substr($user['headimg'],0,4) == 'http'){
            $imgUrl = $user['headimg'];
        }else{
            $imgUrl = 'http://'.$_SERVER['HTTP_HOST'].$user['headimg'];
        }
            
        $this->assign('share_host', config('SHARE_HOST'));
        $this->assign('imgUrl', $imgUrl);
            
        return $this->fetch('Card/Vhomepage');
    }

    public function cardCode()
    {
        $user_id=Input('get.id');
        $user=$this->nav_mod->where("id=".$user_id)->find();
        $url='http://'.$_SERVER['HTTP_HOST'].url("Home/Nologin/card_jump/id/".$user_id);
        $user['card_url'] = $url;
        cardCode($user);
    }

    public function myCode()
    {
        $user_id=Input('get.id');
        $url='http://'.$_SERVER['HTTP_HOST'].url("Home/Nologin/card_jump/id/".$user_id);
        qrcode($url);
    }
    
    public function downvcf()
    {

    }
    
    public function myEwm()
    {
        //获取二维码
        $this->title="";
        $user_id=Input("get.id");
        //创建路径
        $qrcode_save_path="./Uploads/Ewm/";
        if(!is_dir($qrcode_save_path.date('Ymd'))){
            $a=mkdir($qrcode_save_path.date('Ymd'),0777,true);
        }
        //保存路径和文件名称
        $qrCode_file_name = $qrcode_save_path.date('Ymd').'/'.md5($user_id).'.png';
        //保存内容
        $url = 'http://'.$_SERVER['HTTP_HOST'].'/Home/Nologin/card_jump/id/'.$user_id;
        $userinfo = Db::table("s_userInfo")->where(['id'=>$user_id])->find();
        //生成qrcode
        $userImg=$userinfo['headimg'];
        if (!getimagesize('.'.$userImg)) { 
            $userImg="/Uploads/User/b.png";
	    	$head_img_path=$qrcode_save_path.date('Ymd').'/head_'.md5($user_id).'.png';
        }else{
		    $head_img_path=$qrcode_save_path.date('Ymd').'/head_'.md5($user_id).'.jpg';
		}
	
        qrcode2($url,4,$qrCode_file_name);
        $sharePublicIMg="./Uploads/bg.png";
        $userName="我是".$userinfo['nick_name'];
        $font_path = './Uploads/msyh.ttf';
        $file_name= $qrcode_save_path.date('Ymd').'/bg_'.md5($user_id).'.png';
        $image = new \Think\Image(); 
        $image->open('.'.$userImg);
        $image->thumb(480, 480,3)->update($head_img_path);

        $imgEven = A('Home/Img','Event');
		$this->img=$imgEven->update_userShareImg($sharePublicIMg,$head_img_path,$qrCode_file_name,$file_name,$userName,'',$font_path);
        return $this->fetch();
    }
    
    public function menuCard()
    {
        $data = $this->nav_mod->where(array('user_id'=>$this->userInfo['id']))->order('sort asc, id desc')->select();
        foreach ($data as &$v){
            if(strlen($v['name']) > 60){
                $v['name'] = substr($v['name'],0,60).'...';
            }
        }
        
        $this->nav= $data;
        $this->assign('title','菜单按钮');
        return $this->fetch();
    }

    /**
     * 最新菜单编辑与添加
     */
    public function showMenuCard()
    {
        if(isPOST){
            $post = Input('post.');
            if(substr($_SERVER['HTTP_HOST'],0,1) == 'w'){
                $start_name = 'Link';
            }else{
                $start_name = 'Test';
            }

            if($post['icon_url'] != ''){
                if(substr($post['icon_url'],0,5) == 'data:'){
                    $base64_image = str_replace(' ', '+', $post['icon_url']);
                    if (preg_match('/^(data:\s*image\/(\w+);base64,)/', $base64_image, $result)){
                        //匹配成功
                        if($result[2] == 'jpeg'){
                            $image_name = uniqid().rand(1000,9999).'.jpg';
                         }else{
                            $image_name = uniqid().rand(1000,9999).'.'.$result[2];
                        }
                        // 文件名称
                        $object = $start_name.'/'.date("Y-m-d",time())."/".$image_name;
                        if (strstr($base64_image,",")){
                            $base64_image = explode(',',$base64_image);
                            $base64_image = $base64_image[1];
                        }

                        $path = './Uploads/Article/'.date("Y-m-d",time());
                        if (!is_dir($path)){ //判断目录是否存在 不存在就创建
                            mkdir($path,0777,true);
                        }

                        $imageSrc=  './Uploads/Article/'.date("Y-m-d",time())."/".uniqid().rand(1000,9999).'.jpg';  //图片名字
                        file_put_contents($imageSrc, base64_decode($base64_image));//返回的是图片名
                        try{
                            $ossClient = new \OSS\OssClient(config('OSS_IMAGE')['accessKeyId'], config('OSS_IMAGE')['accessKeySecret'], config('OSS_IMAGE')['endpoint']);
                            $image_file =  $ossClient->uploadFile(config('OSS_IMAGE')['bucket'], $object, $imageSrc);
                            if ($image_file['status'] == true){
                                unlink($imageSrc);
                                $shift_url = str_replace(config('OSS_IMAGE')['oss_url'],config('OSS_IMAGE')['cdn_usls'],$image_file['url']);
                                $post['icon_url'] =  $shift_url."@!protected";
                            }else{
                                // 上传错误提示错误信息
                            }
                        } catch(OssException $e) {

                        }
                    }
                }
            }
            $post['jump_url'] = cutstr_html($post['jump_url']);
            $post['name'] = cutstr_html($post['name']);
            $data = array('name'=>$post['name'],'icon_url'=>$post['icon_url'],'sort'=>$post['sort'],'jump_url'=>$post['jump_url']);
            if($post['id']){
                Db::table('s_users_nav')->where(array('id'=>$post['id']))->data($data)->update();
            }else{
                $data['user_id'] = $this->userInfo['id'];
                $data['add_time'] = time();
                Db::table('s_users_nav')->insert($data);
            }
            header("location:".url('Home/Card/menuCard'));
        }else{
            $get=Input("get.");
            $where['id']=$get['id'];
            $data=$this->nav_mod->where($where)->find();
            $this->data = $data;
        }
        
        return $this->fetch();
    }

    /**
     * 我的V网二维码
     */
    public function myCardQRCode()
    {
        return $this->fetch();
    }

    /**
     * V网制作
     */
    public function CreateCard()
    {
        $this->title="V网制作";
        if(!class_exists('\JSSDK'))
        {
            vendor('wxsdkapi.jssdk');
            vendor('wxpayapi.lib.WxPay#Config');
        }
        $res = new \JSSDK(config('WEIXINPAY_CONFIG.APPID'),config('WEIXINPAY_CONFIG.APPSECRET'));
        $this->signPackage = $res->getSignPackage();

        if(isPOST){
            $post=Input('post.');
            if(empty($post['jump_url'])){
                $post['jump_url']=url('Home/Card/createCard');
            }
            if(!empty($post['background_id'])){
            	$foldername="bg_".date("Y-m-d",time());
            	$post['background_img']=$this->getmedia($post['background_id'],$foldername);
		    	$post['background_img']=substr($post['background_img'],1);
            }

            if(!empty($post['ewm_id'])){
            $foldername="ewm_".date("Y-m-d",time());
                $post['wx_ewm_url']=$this->getmedia($post['ewm_id'],$foldername);
		    $post['wx_ewm_url']=substr($post['wx_ewm_url'],1);
            }
            unset($post['background_id']);
            unset($post['head_id']);
            unset($post['ewm_id']);
            $jump_url=$post['jump_url'];
            unset($post['jump_url']);
            $this->nav_mod->where(array('id'=>$this->userInfo['id']))->update($post);
            header("Location:".$jump_url);
        }else{
        	is_vip();
        	$data = $this->nav_mod->where(array('id'=>$this->userInfo['id']))->find();
            $data['setting'] = getsqname($data['id']);
            $age_list = Db::table('s_age')->select();
            $this->age_list = $age_list;
            $this->data= $data;
        }
        $this->assign('userInfo',$this->userInfo);
        return $this->fetch();
    }

    private function getmedia($media_id,$foldername)
    {
        if (!file_exists("./Uploads/User_cert/".$foldername)) 
        {
          mkdir("./Uploads/User_cert/".$foldername, 0777, true);
        }
        $targetName = './Uploads/User_cert/'.$foldername.'/'.date('YmdHis').rand(1000,9999).'.jpg';
         $wxImg=A("Home/Img",'Event')->getWxImg2($media_id,$targetName);
        return $wxImg;
    }
    
    public function uploadBcard(){
        //识别V网
        $array_return=array("status"=>1,"data"=>[],"msg"=>"success");
        if(substr($_SERVER['HTTP_HOST'],0,1) == 'w'){
            $start_name = 'bcard';
        }else{
            $start_name = 'test';
        }
         if(!empty($_FILES['bcard']['name'])){
             $Upload = new \Admin\Controller\UploadsController();
             $end_name = trim(strrchr($_FILES['bcard']['name'], '.'),'.');
             if($end_name == 'jpeg'){
                 $save_name = $start_name.'/'.date('Ymd').'/'.time().rand(1,9999).'.jpg';
             }else{
                 $save_name = $start_name.'/'.date('Ymd').'/'.time().rand(1,9999).'.'.$end_name;
             }

             $bcard_path = $Upload->Uploads($save_name,$_FILES['bcard']);
             if($bcard_path){
                 $res=bcard_query($bcard_path);
             }
        }else{
            $array_return['status']=0;
            $array_return['msg']="上传V网失败";
        }
        if($res['status']!=0){
            $array_return['status']=0;
            $array_return['msg']="识别V网失败,".$res['msg'];
        }else{
            $data=$res['result'];
            if(isset($data['mobile'])){
                //手机号码
                $_data['mobile']=implode(",",$data['mobile']);
            }
            if(isset($data['position'])){
                //职位/部门
                $_data['position']=implode(",",$data['position']);
            }
            if(isset($data['name'])){
                //姓名
                $_data['nick_name']=$data['name'];
            }
            if(isset($data['tel'])){
                //固话
                $_data['telephone']=implode(",",$data['tel']);
            }
            if(isset($data['website'])){
                //网址
                $_data['site']=implode(",",$data['website']);
            }
            if(isset($data['address'])){
                //地址
                $_data['address']=implode(",",$data['address']);
            }
            if(isset($data['company'])){
                //公司名称
                $_data['company']=implode(",",$data['company']);
            }
            $array_return['status']=1;
            $array_return['data']=$_data;
        }

        return json($array_return);
    }
    
    /**
     *  展示内容
     */
    public function listContent()
    {
        return $this->fetch();
    }

    /**
     *  添加图文
     */
    public function addContent()
    {
        return $this->fetch();
    }

    /**
     *  删除图文
     */
    public function delContent()
    {
        return $this->fetch();
    }

    /**
     *  行业选择
     */
    public function chooseIndustry()
    {
        return $this->fetch();
    }

    /**
     *  菜单按钮
     */
    public function listMenu()
    {
        
    }
    /**
     *  添加菜单按钮
     */
    public function addMenu()
    {
        $this->title="菜单按钮";
        if(isPOST){
            $res=Input('post.');
            //添加导航
            $data['user_id']=$this->userInfo['id'];
            $data['name']=$res['name'];
            $data['icon_url']=$res['img'];
            $data['jump_url']=$res['url'];
            $data['sort']=$res['sort'];
            $id=$this->nav_mod->insert($data);
            if($id){
                $data['id']=$id;
                return json(array('status'=>1,'data'=>$data,'msg'=>'success'));exit;
            }else{
                return json(array('status'=>0,'data'=>[],'msg'=>'添加失败'));exit;
            }
        }
    }
    
    /**
     *  删除菜单按钮
     */
    public function delMenu()
    {
        if(isPOST){
            $res=Input('post.');
            //删除导航
            $id=$this->nav_mod->where(array('id'=>$res['id'],'user_id'=>$this->userInfo['id']))->delete();
            if($id){
                return json(array('status'=>1,'data'=>$id,'msg'=>'success'));exit;
            }else{
                return json(array('status'=>0,'data'=>[],'msg'=>'删除失败'));exit;
            }
        }
    }

    /**
     *  获取菜单按钮
     */
    public function getMenu()
    {
        if(isPOST){
            $res=Input('post.');
            $nav=$this->nav_mod->where(array('id'=>$res['id'],'user_id'=>$this->userInfo['id']))->find();
            if($nav){
                return json(array('status'=>1,'data'=>$nav,'msg'=>'success'));exit;
            }
        }
    }

    /**
     *  编辑菜单按钮
     */
    public function editMenu()
    {
        $this->title="菜单按钮";
        if(isPOST){
            $res=Input('post.');
            //添加导航
            $nav=$this->nav_mod->where(array('id'=>$res['id'],'user_id'=>$this->userInfo['id']))->find();
            $nav['name']=$res['name'];
            $nav['icon_url']=$res['img'];
            $nav['jump_url']=$res['url'];
            $nav['sort']=$res['sort'];
            $result=$this->nav_mod->update($nav);
            if($result === false){
                return json(array('status'=>0,'data'=>[],'msg'=>'编辑失败'));exit;
            }else{
                return json(array('status'=>1,'msg'=>'编辑成功'));exit;
            }
        }
    }

    /**
     *  V网风格
     */
    public function styleCard()
    {
        $this->title="风格设置";
         if(isPOST){
            $post=Input('post.');
            if(empty($post['jump_url'])){
                $post['jump_url']=url('Home/Card/NewCreatecard');
            }
            $jump_url=$post['jump_url'];
            unset($post['jump_url']);
            $this->nav_mod->where(array('id'=>$this->userInfo['id']))->update($post);
             header("Location:".$jump_url);
        }else{
            

        }
        $this->assign("userInfo",$this->userInfo);
        $this->data=$this->nav_mod->where(array('id'=>$this->userInfo['id']))->find();
        return $this->fetch();
    }

    /**
     *  背景音乐
     */
    public function musicCard()
    {
        $this->title="音乐设置";
        $where = array();
        if(isPOST){
            $post=Input('post.');
            if(isset($post['type'])){
                if($post['type']>0){
                    $where['type_id'] = $post['type'];
                    $this->type_id = $post['type'];
                }
            }else{
                if(empty($post['jump_url'])){
                    $post['jump_url']=url('Home/Card/NewCreatecard');
                }
                $jump_url=$post['jump_url'];
                unset($post['jump_url']);
                $this->nav_mod->where(array('id'=>$this->userInfo['id']))->update($post);
                header("Location:".$jump_url);
            }
        }

        $this->types = Db::table('s_music_type')->select();
        $this->data=$this->nav_mod->where(array('id'=>$this->userInfo['id']))->find();
        $this->music=Db::table("s_music")->where($where)->order('id asc')->select();
        return $this->fetch();
    }

    /**
     *  复制V网
     */
    public function copyCard()
    {
        if(isPOST){
            $res=Input('post.');
            $user_id=$this->userInfo['id'];
            $object_id=$res['user_id'];//复制人id
            $this->user_logic->copyCard($user_id,$object_id);
            $this->assign('id',$this->userInfo['unionid']);
            exit;
        }

        $this->assign('id',$this->userInfo['unionid']);
        $this->title="复制V网";
        return $this->fetch();
    }

    /**
     *  搜索V网
     */
    public function searchCard()
    {
        if(isPOST){
            $res=Input('post.');
            $where['is_copy_cards']=1;
            if($res['keyword']){
                $where['_string']='phone LIKE "%'.$res['keyword'].'%" or nick_name LIKE "%'.$res['keyword'].'%"';
                $view_mode = new viewModel();
                $data=$view_mode->where($where)->order('id desc')->limit(100)->select();
            }else{
                $data = Db::table('s_user_info')->alias('u')
                    ->join('inner join s_visit_log v','v.user_id = u.id')
                    ->leftJoin(' s_member m','m.id = u.member_id')
                    ->where(array('u.is_copy_cards'=>1))->group('u.id')
                    ->field('u.id,u.nick_name,m.phone,count(u.id) as cnt')
                    ->order('cnt desc')->limit(100)->select();

            }
            $this->data=array(
                'status'=>1,
                'msg'=>'success',
                'data'=>$data,
                'count'=>empty($data)?0:count($data)
            );
            return json($this->data);
            exit;
        }

    }

    // 新复制v网
    public function cardhome()
    {
        $this->title="复制V网";
        $this->assign('id',$this->userInfo['unionid']);
        $user_id = Input('get.user_id');

        if ($user_id){
            $this->assign('my_id',$user_id);
        }else{
            $this->error("缺少参数");
        }

        return $this->fetch();
    }

    /**
     * 他人V网
     */
    public function cardInfo()
    {
        $this->title="复制V网";
        return $this->fetch();
    }

    //获取粉丝
    public function getMyFans()
    {
    	$myFans = array();
        $myFans['level_1_count'] = Db::table('s_users_fans')->where(array('user_id'=>$this->userInfo['id'],'level'=>1))->count();
        $myFans['level_2_count'] = Db::table('s_users_fans')->where(array('user_id'=>$this->userInfo['id'],'level'=>2))->count();
        $RecUser = $this->fans_logic->getRecUser(array('user_id'=>$this->userInfo['id']));

        $user = Db::table('s_user_info')->alias('u')->leftJoin(' s_member m','m.id = u.member_id')
                ->leftJoin(' s_vip_group v','v.id = u.vip_group_id')->field('m.phone,u.nick_name,u.headimg,u.vip_group_id,v.vip_name')
                ->where(array('u.id'=>$this->userInfo['id']))->find();
                
        $this->assign('userInfo',$user);
        $this->assign('MyFans',$myFans);
        $this->assign('RecUser',$RecUser);
            
        return $this->fetch();
    }

    //获取粉丝列表
    public function MyFanslist()
    {
    	$where['user_id'] = $this->userInfo['id'];
        $vip_group_id = 1;
        $where['vip_group_id'] = $vip_group_id;
        if(isPOST){
                $vip_group_id = Input('post.vip_group_id');
                $where['vip_group_id'] = $vip_group_id;
            }
            
         $myFans = $this->fans_logic->getFans($where);
         $this->assign('userInfo',$this->userInfo);
         $this->assign('MyFans',$myFans);
         $this->assign('zjfans',$myFans['level_1']);
         $this->assign('vip_group_id',$vip_group_id);
         return $this->fetch();
    }


    //获取粉丝列表
    public function MyJjFanslist()
    {
        $where['user_id'] = $this->userInfo['id'];
        $vip_group_id = 1;
        $where['vip_group_id'] = $vip_group_id;

        if(isPOST){
            $vip_group_id = Input('post.vip_group_id');
            $where['vip_group_id'] = $vip_group_id;
        }

        $myFans = $this->fans_logic->getFans($where);
        foreach ($myFans['level_2'] as &$v){
            if($v['username']){
                $v['username'] = substr($v['username'],0,3).'****'.substr($v['username'],7,10);
            }
        }

        $this->assign('userInfo',$this->userInfo);
        $this->assign('MyFans',$myFans);
        $this->assign('zjfans',$myFans['level_2']);
        $this->assign('vip_group_id',$vip_group_id);
        return $this->fetch();
    }
    
    public function contactUs()
    {
        if($this->userInfo['vip_id']>3){
            return $this->fetch('Card/addcontact');
        }else{
            return $this->fetch();
        }
    }
    
    /**
     * 意见反馈
     */
    public function feedback()
    {
        return $this->fetch();
    }
    
    //增加反馈
    public function addFeedBack()
    {
       header("Access-Control-Allow-Origin: *");
       if(isPOST){
            $res=Input('post.');
            if(!isset($res['contact'])){
                $this->ajax_return['status']=0;
                $this->ajax_return['msg']="联系方式不能为空";
                return json($this->ajax_return);exit;
            }
            if(!isset($res['username'])){
                $this->ajax_return['status']=0;
                $this->ajax_return['msg']="用户姓名不能为空";
                return json($this->ajax_return);exit;
            }
            if(!isset($res['content'])){
                $this->ajax_return['status']=0;
                $this->ajax_return['msg']="内容不能为空";
                return json($this->ajax_return);exit;
            }
            $data['user_id']     =  $this->userInfo['id'];
            $data['contact']     =  $res['contact'];
            $data['content']     =  $res['content'];
            $data['status']      =  0;
            $data['add_time']    =  time();
            $id=Db::table('s_opinion')->insert($data);
            if($id){
                $data['id']=$id;
                return json(array('status'=>1,'data'=>$data,'msg'=>'success'));exit;
            }else{
                return json(array('status'=>0,'data'=>[],'msg'=>'添加失败'));exit;
            }
       }else{
            return $this->fetch();
       }
    }


    /**
     * banner轮播图的展示
     */
    public function showBanner()
    {

        if($_POST){
            $post = Input('post.');
            if($post['type']==1){
                $img = array();
                if(substr($_SERVER['HTTP_HOST'],0,1) == 'w'){
                    $start_name = 'Banner';
                }else{
                    $start_name = 'Test';
                }
                foreach ($post as $k=>$v){

                    $pic = substr($k,0,4);

                    if($pic == 'pic_'){
                        if(substr($v,0,5) == 'data:'){
                            //上传base64图
                            $base64_image = str_replace(' ', '+', $v);
                            if (preg_match('/^(data:\s*image\/(\w+);base64,)/', $base64_image, $result)){

                                //匹配成功
                                if($result[2] == 'jpeg'){
                                    $image_name = uniqid().rand(1000,9999).'.jpg';
                                }else{
                                    $image_name = uniqid().rand(1000,9999).'.'.$result[2];
                                }


                                // 文件名称
                                $object = $start_name.'/'.date("Y-m-d",time())."/".$image_name;
                                if (strstr($base64_image,",")){
                                    $base64_image = explode(',',$base64_image);
                                    $base64_image = $base64_image[1];
                                }

                                $path = './Uploads/Article/'.date("Y-m-d",time());
                                if (!is_dir($path)){ //判断目录是否存在 不存在就创建
                                    mkdir($path,0777,true);
                                }
                                $imageSrc=  './Uploads/Article/'.date("Y-m-d",time())."/".uniqid().rand(1000,9999).'.jpg';  //图片名字
                                file_put_contents($imageSrc, base64_decode($base64_image));//返回的是图片名


                                try{

                                    $ossClient = new \OSS\OssClient(config('OSS_IMAGE')['accessKeyId'], config('OSS_IMAGE')['accessKeySecret'], config('OSS_IMAGE')['endpoint']);
                                    $image_file =  $ossClient->uploadFile(config('OSS_IMAGE')['bucket'], $object, $imageSrc);
                                    if ($image_file['status'] == true){

                                        unlink($imageSrc);

                                        $shift_url = str_replace(config('OSS_IMAGE')['oss_url'],config('OSS_IMAGE')['cdn_usls'],$image_file['url']);
                                        $img[] = $shift_url."@!protected";


                                    }else{
                                        // 上传错误提示错误信息

                                    }
                                } catch(OssException $e) {

                                }
                            }
                        }else{
                            $img[] = $v;
                        }
                    }
                }
                // var_dump($old);die;
                if($img){
                    $old = Db::table('s_user_img')->where(array('user_id'=>$this->userInfo['id']))->select();
                    if($old){
                        Db::table('s_user_img')->where(array('user_id'=>$this->userInfo['id']))->delete();
                    }
                    $banner_list = array();
                    foreach ($img as $k=>$j){
                        $banner_list[] = array('img_url'=>$j,'user_id'=>$this->userInfo['id'],'sort'=>$k,'createtime'=>time());
                    }
                    Db::table('s_user_img')->addAll($banner_list);
                }
                header("location:".url('Home/Card/NewCreatecard'));
            }else if($post['type']==0){
                $old = Db::table('s_user_img')->where(array('user_id'=>$this->userInfo['id']))->select();
                if($old){
                    Db::table('s_user_img')->where(array('user_id'=>$this->userInfo['id']))->delete();

                }
                header("location:".url('Home/Card/NewCreatecard'));
            }

        }

        $list = Db::table('s_user_img')->where(array('user_id'=>$this->userInfo['id']))->select();
        
        $this->_list = $list;
        return $this->fetch();
    }

   //新版v网基本资料编辑
   public function Basicinfor()
   {
        $this->title="基本资料编辑";

        if(isPOST){

            $post=Input('post.');

            if(substr($_SERVER['HTTP_HOST'],0,1) == 'w'){
                $start_name = 'user';
            }else{
                $start_name = 'test';
            }

            $Upload = new \Admin\Controller\UploadsController();
            if(empty($post['jump_url'])){
                $post['jump_url']=url('Home/Card/NewCreatecard');
            }

            if(!empty($post['background_id'])){
            	$foldername="bg_".date("Y-m-d",time());
                $post['background_img']=$this->getmedia($post['background_id'],$foldername);
		        $post['background_img']=substr($post['background_img'],1);
            }

            if(!empty($post['ewm_id'])){
                $foldername="ewm_".date("Y-m-d",time());
                $post['wx_ewm_url']=$this->getmedia($post['ewm_id'],$foldername);
		        $post['wx_ewm_url']=substr($post['wx_ewm_url'],1);
                $new_img = 'http://'.$_SERVER['HTTP_HOST'].$post['wx_ewm_url'];
                $end_name = trim(strrchr($post['wx_ewm_url'], '.'),'.');
                $save_name = $start_name.'/'.date('Ymd').'/'.time().rand(1,9999).'.'.$end_name;
                $post['wx_ewm_url'] = $Upload->Uploads($save_name,$new_img,2);
            }
            unset($post['background_id']);
            unset($post['head_id']);
            unset($post['ewm_id']);
            $jump_url=$post['jump_url'];
            unset($post['jump_url']);
            unset($post['sq_type_id']);
            if($post['address']){
                $search = array(" ","　","\n","\r","\t");
                $replace = array("","","","","");
                $post['address'] = str_replace($search, $replace, $post['address']);
            }
            $this->nav_mod->where(array('id'=>$this->userInfo['id']))->update($post);
            $li = Db::table('s_user_info')->field('id,nick_name,mobile,headimg,position,company,industry_tag,address,share_my_introduct,wx_ewm_url,email,sq_type_id')->where(array('id'=>$this->userInfo['id']))->find();

            $aa =0;
            if($li['nick_name'] != $li['mobile']){
                $aa += 1;
            }
            if(substr($li['headimg'], 0, 4) == 'http'){
                $aa += 1;
            }
            if($li['position'] != ''){
                $aa += 1;
            }
            if($li['company'] != ''){
                $aa += 1;
            }
            if($li['industry_tag'] != ''){
                $aa += 1;
            }
            if($li['address'] != ''){
                $aa += 1;
            }
            if($li['share_my_introduct'] != ''){
                $aa += 1;
            }
            if($li['wx_ewm_url'] != ''){
                $aa += 1;
            }
            if($li['email'] != ''){
                $aa += 1;
            }
            if($li['sq_type_id'] != ''){
                $aa += 1;
            }

            if($aa != 0){
                Db::table('s_user_info')->where(array('id'=>$li['id']))->update(array('completion'=>$aa*10));
                if($aa == 10){
                    //完善信息加积分
                    $log=Db::table('s_inte_log')->where(array('user_id'=>$this->userInfo['id'],'inte_id'=>2,'type'=>0))->count();
                    if($log<=0){
                        $dat['user_id']=$this->userInfo['id'];
                        $dat['inte']=Db::table('s_inte')->where(array('id'=>2))->column('inte');
                        $dat['addtime']=time();
                        $dat['inte_id']=2;
                        $zj = Db::table("s_user_info")->where(array('id'=>$this->userInfo['id']))->setInc('inte',$dat['inte']);
                        if($zj){
                            Db::table('s_inte_log')->insert($dat);
                        }
                    }
                }
            }

            header("Location:".$jump_url);
        }else{
        	is_vip();
        	$data = $this->nav_mod->where(array('id'=>$this->userInfo['id']))->find();
            $data['setting'] = getsqname($data['id']);
            $age_list = Db::table('s_age')->select();
            $this->age_list = $age_list;
            $this->data= $data;
        }

        $this->assign('userInfo',$this->userInfo);
        return $this->fetch();
    }
	
	//	新版V网制作
	public function NewCreateCard()
	{
        $this->title="V网制作";
        is_vip();
        $data = $this->nav_mod->where(array('id'=>$this->userInfo['id']))->find();
        $data['banner_img'] = Db::table('s_user_img')->field('img_url')->where(array('user_id'=>$this->userInfo['id']))->order('sort asc')->limit(4)->select();
        $data['nav_img'] = Db::table('s_users_nav')->field('icon_url,name')->where(array('user_id'=>$this->userInfo['id']))->order('sort asc')->limit(3)->select();
        $data['product_img'] = Db::table('s_product')->field('cover_img,title')->where(array('user_id'=>$this->userInfo['id']))->order('id desc')->limit(3)->select();
        $data['card_img'] = Db::table('s_card_content')->field('thumb,title')->where(array('user_id'=>$this->userInfo['id']))->order('sort asc')->limit(3)->select();
        $this->data= $data;
        $this->assign('userInfo',$this->userInfo);
        return $this->fetch();
    }

      // 新v网首页
    public function Vhomepage()
    {
        $this->title="我的V网";
        $this->assign('id',$this->userInfo['unionid']);
        return $this->fetch();
    }

    // 新版v网
    public function newgreencard()
    {
        $this->title="我的V网";

        $this->assign('id',$this->userInfo['unionid']);
        $this->assign('openid',$this->userInfo['id']);
        $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
        $url = "$protocol$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
        $this->assign('share_url', $url);
        $this->assign('sharetitle', $this->userInfo['nick_name']);

        $contents = cutstr_html($this->userInfo['share_my_introduct']);
        $contents =str_replace('"', '\'', $contents);
        $this->assign('desc', preg_replace('/\s/is','', $contents));

        if(substr($this->userInfo['headimg'],0,4) == 'http'){
           $imgUrl = $this->userInfo['headimg'];
        }else{
           $imgUrl = 'http://'.$_SERVER['HTTP_HOST'].$this->userInfo['headimg'];
        }
           
        $this->assign('share_host', config('SHARE_HOST'));
        $this->assign('imgUrl', $imgUrl);
        $this->style = Input('get.style');
           
        return $this->fetch();
    }

    /**
     * 判断图片是否存在
     * @param $url
     * @return bool
     */
    private function img_exists($url)
    {
        $filesize = @getimagesize($url);
        if ($filesize) {
            return true;
        }else{
            return false;
        }
    }
}