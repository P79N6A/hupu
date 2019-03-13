<?php
namespace app\home\controller;
use think\Db;
use Think\Controller;
use app\api\logic\ArticleList  as artLogic;

class Nologin extends Controller {

	private $art_logic = null;
	
    function _initialize()
    {
        parent::_initialize();
        $this->art_logic = new artLogic();
    }
    
	//产品预览
    public function propreview()
    {
        $this->unionid= input('get.unionid');//$this->userInfo['unionid'];
        $this->type_id = input('get.typeid');//分类id
        $this->p_id = input('get.p_id');//产品id
        $this->title = input('get.title');

        // 注意 URL 一定要动态获取，不能 hardcode.
        $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
        $url = "$protocol$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
        $this->assign('share_url', $url);
        $link_url = 'http://'.$_SERVER['HTTP_HOST'].'/index.php?s=/Home/Nologin/propreview/p_id/'.input('get.p_id').'/type_id/'. input('get.typeid').'/unionid/'.input('get.unionid');
        $content = mb_substr(cutstr_html(title),0,20,'utf-8').'...';
        $content = str_replace(PHP_EOL, ' ', str_replace('"','',$content));
        $this->title=$data['title'];
        $this->assign('sharetitle', $data['title']);
        $this->assign('desc',$content);
        $this->assign('link', $link_url);
        $this->assign('imgUrl', $data['cover_img']);

        return  $this->fetch();
    }

    //添加图文预览
    public function preview()
    {
        $card_content_id = input('get.show_id');
        $this->unionid=input('get.unionid');
        $this->user_id = Db::table('s_user_info')->where(array('unionid'=>input('get.unionid')))->column('id');
        $card = Db::table('s_card_content')->find($card_content_id);
        // 注意 URL 一定要动态获取，不能 hardcode.
        $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
        $url = "$protocol$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
        $this->assign('share_url', $url);
        $this->assign('sharetitle', $card['title']);
        $desc = str_replace("&nbsp;","",strip_tags($card['content']));
        $this->assign('desc', str_replace(PHP_EOL, ' ',$desc ));
        $card['content'] = str_replace("\n","<br/>",$card['content']);

        $this->assign('imgUrl',$card['thumb']);
        $this->assign('link',get_current_url());

        $this->type_id = input('get.type_id');
        $this->show_id = $card_content_id;
        $this->title = input('get.title');
        return  $this->fetch();
    }

    //新首页
    public function index()
    {
        $unionid=input("get.unionid");
        $this->unionid=$unionid;
        $userinfo = Db::table("s_user_info")->where(array("unionid"=>$unionid))->field("id,nick_name,share_my_introduct,headimg")->find();
        $this->title= $userinfo['nick_name']."的V网";
        $this->id=$userinfo["id"];
        $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
        $url = "$protocol$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
        $this->assign('share_url', $url);
        $this->assign('sharetitle', $userinfo['nick_name']);

        $contents = cutstr_html($userinfo['share_my_introduct']);
        $contents =str_replace('"', '\'', $contents);
        $this->assign('desc', preg_replace('/\s/is','', $contents));
        if(substr($userinfo['headimg'],0,4) == 'http'){
            $imgUrl = $userinfo['headimg'];
        }else{
            $imgUrl = 'http://'.$_SERVER['HTTP_HOST'].$userinfo['headimg'];
        }
        
        $this->assign('share_host', config('SHARE_HOST'));
        $this->assign('imgUrl', $imgUrl);
        $this->style = input('get.style');
        return  $this->fetch();
    }

    /**
     *新V网跳转
     */
    //跳转
    public function card_jump()
    {
        $id = input('get.id');
        $unionid = input('get.unionid');
        if($id || $unionid){
            if($id){
                $where['id'] = $id;
            }else{
                $where['unionid'] = $unionid;
            }
            
            $user = Db::table('s_user_info')->field('id,unionid,style')->where($where)->find();
			//v网访问统计
			user_stats($user['id'],$user['id']);
            if($user['style'] == 5){
                header("location:".url("Home/NewCard/mycard/id/".$user['id'].'/style/'.$user['style']));
            }elseif($user['style'] == 6 || $user['style'] == 8 ||$user['style'] == 10 ||$user['style'] == 12 || $user['style'] == 14 || $user['style'] == 15 ||$user['style'] == 16){
                header("location:".url("Home/Nologin/newgreencard/id/".$user['unionid'].'/openid/'.$user['id'].'/style/'.$user['style']));
            }elseif($user['style'] == 7 || $user['style'] == 9 || $user['style'] == 11 || $user['style'] == 13){
                header("location:".url("Home/Nologin/index/unionid/".$user['unionid'].'/style/'.$user['style']));
            }
        }else{
            header("location:".url("Home/User/usercenter"));
        }
    }
    
    /**
     * 地图页面
     */
    public function diTu()
    {
        $address = input('get.address');
        $this->address = $address;
        return  $this->fetch();
    }

    // 新版v网
    public function newgreencard()
    {
        $id = input("get.id");
        $user_id = input('get.openid');
        
        $userinfo = Db::table("s_user_info")->where(array("unionid"=>$id))->field("nick_name,share_my_introduct,headimg")->find();
        $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
        $url = "$protocol$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
        $this->assign('share_url', $url);
        $this->assign('sharetitle', $userinfo['nick_name']);
        $this->assign('title', $userinfo['nick_name'].'的v网');

        $contents = cutstr_html($userinfo['share_my_introduct']);
        $contents =str_replace('"', '\'', $contents);
        $this->assign('desc', preg_replace('/\s/is','', $contents));
        if(substr($userinfo['headimg'],0,4) == 'http'){
            $imgUrl = $userinfo['headimg'];
        }else{
            $imgUrl = 'http://'.$_SERVER['HTTP_HOST'].$userinfo['headimg'];
        }
              
        $this->assign('openid',$user_id);
        $this->assign('id',$id);
        $this->assign('share_host', config('SHARE_HOST'));
        $this->assign('imgUrl', $imgUrl);
        $this->style = input('get.style');
        return  $this->fetch();
    }

    /**
     * V网内容详情
     */
    public function contentDetail()
    {
        $card_content_id = input('card_content_id');
        $card = Db::table('s_card_content')->find($card_content_id);
        $lists = Db::table('s_card_content_res')->where(array('card_content_id'=>$card_content_id))->select();
        // 注意 URL 一定要动态获取，不能 hardcode.
        $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
        $url = "$protocol$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
        $this->assign('share_url', $url);
        $this->assign('sharetitle', $card['title']);
        $desc = str_replace("&nbsp;","",strip_tags($card['content']));
        $this->assign('desc', str_replace(PHP_EOL, ' ',$desc ));
        $card['content'] = str_replace("\n","<br/>",$card['content']);
        $card['content'] = str_replace('contenteditable="true"','',$card['content']);
        $this->assign('imgUrl',$card['thumb']);
        $this->assign('link',get_current_url());
        $this->_card = $card;
        $this->_content_detail= $lists;
        return  $this->fetch();
    }

    /**
     * v网产品列表
     */
    public function product_list()
    {
        $this->user_id = input('get.user_id');
        return  $this->fetch();
    }

    /**
     * v网产品详情页
     */
    public function product_detail()
    {
        $this->product_id = input('get.product_id');
        $this->user_id = input('get.user_id');
        $list = Db::table('s_user_info')->where(array('id'=>input('get.user_id')))->field('wx_ewm_url,mobile')->find();
        $this->wx_erweima = $list['wx_ewm_url'];
        $this->mobile = $list['mobile'];
        $list = Db::table('s_product')->where(array('id'=>$this->product_id))->find();
        // 注意 URL 一定要动态获取，不能 hardcode.
        $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
        $url = "$protocol$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
        $this->assign('share_url', $url);
        $this->sharetitle = $list['title'];
        $this->desc = $list['title'];
        $this->imgUrl = $list['cover_img'];
        $this->assign('link',get_current_url());
        return  $this->fetch();
    }

    /**
     * 海报分享
     */
    public function share_img()
    {
        // 注意 URL 一定要动态获取，不能 hardcode.
        $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
        $url = "$protocol$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
        $this->assign('share_url', $url);
        $list = Db::table('s_user_poster')->where(array('id'=>input('get.id')))->find();
        $this->sharetitle = $list['title'];
        $this->desc = $list['content'];
        $this->imgUrl = $list['img'];
        $this->title=$list['title'];
        $this->img = $list['img'];
        $this->ids=$_GET['id'];
        return  $this->fetch();
    }

    /**
     * 文章分享
     */
    public function share_article()
    {
        $this->kid = $_GET['kid'];
        $this->aid = $_GET['aid'];
        $this->sid = $_GET['id'];
        //初始化
        $id = input('get.user_id');
        Db::table('s_user_extend_article')->where(array('id'=>input('get.aid')))->setInc('visit');
        // 注意 URL 一定要动态获取，不能 hardcode.
        $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
        $url = "$protocol$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
        $this->assign('share_url', $url);
        $data = Db::table('s_user_extend_article')->alias('ua')->leftJoin(' s_system_article sa','sa.id = ua.s_article_id')
            ->field('sa.title,sa.id,sa.content,sa.cover_img,ua.visit')->where(array('ua.id'=>$_GET['aid']))->find();
        $this->erweima = Db::table('s_user_info')->where(array('id'=>$id))->column('wx_ewm_url');
        $link_url = 'http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'].'/user_id/'.$id;
        $content = mb_substr(cutstr_html($data['content']),0,20,'utf-8').'...';
        $content = str_replace(PHP_EOL, ' ', str_replace('"','',$content));
        $share_title = str_replace(PHP_EOL, ' ', str_replace('"','',$data['title']));
        $this->assign('sharetitle', $share_title);
        $this->assign('desc',$content);
        $this->assign('link', $link_url);
        $this->assign('imgUrl', $data['cover_img']);
        $this->id = $id;
        //浏览量获取积分
        if((floor($data['visit']/100) - floor(($data['visit']-1)/100) == 1) && $data['visit']>99){
            $dat['user_id']=$id;
            $dat['inte'] = Db::table('s_inte')->where(array('id'=>7))->column('inte');
            $dat['addtime']=time();
            $dat['inte_id']=7;
            $zj = Db::table("s_user_info")->where(array('id'=>$dat['user_id']))->setInc('inte',$dat['inte']);
            if($zj){
                Db::table('s_inte_log')->insert($dat);
            }
        }
        
        return  $this->fetch('Articlegrab/artpreview');
    }

    /***
     * 我的推广码
     */
    public function myCode()
    {
        if(input('get.unionid')){
            $user_id = Db::table('s_user_info')->where(array('unionid'=>input('get.unionid')))->column('id');
        }else{
            $user_id=input('get.id');
        }
        $url='http://'.$_SERVER['HTTP_HOST'].url("Home/Nologin/card_jump/id/".$user_id);
        qrcode($url);
    }

    /**
     * 一键导入通讯录
     */
    public function cardCode()
    {
        if(input('get.unionid')){
            $user_id = Db::table('s_user_info')->where(array('unionid'=>input('get.unionid')))->column('id');
        }else{
            $user_id=input('get.id');
        }
        $user = Db::table('s_user_info')->where("id=".$user_id)->find();
        $url='http://'.$_SERVER['HTTP_HOST'].'/Home/Nologin/card_jump/id/'.$user_id;
        $user['card_url'] = $url;
        cardCode($user);
    }

    /**
     * 相册分享
     */
    public function share_photo()
    {
        // 注意 URL 一定要动态获取，不能 hardcode.
        $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
        $url = "$protocol$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
        $this->assign('share_url', $url);
        $this->uid=input('get.uid');//unionID
        $this->sid=input('get.id',0);//我的相册id
        $this->mid=input('get.mid',0);//1:自己的预览  2：模板的预览
        $this->pids=input('get.pids',0);//内容id（选填）
        $this->cids=input('get.cids',0);
        $list = Db::table('s_user_album')->where(array('id'=>input('get.preview')))->find();
        $this->sharetitle = $list['share_title'];
        $this->desc = $list['share_content'];
        $this->imgUrl = $list['cover_img'];
        $this->title=$list['share_title'];

        $this->share=input('get.share',0);
        $this->modify=input('get.modify',0);
        $this->preview=input('get.preview',0);

        return  $this->fetch();
    }

    /**
     * 贺卡分享
     */
    public function gcard_share()
    {
        // 注意 URL 一定要动态获取，不能 hardcode.
        $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
        $url = "$protocol$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
        $this->assign('share_url', $url);
        $this->assign('gid', $_GET['gid']);
        $list = Db::table('s_users_gcard')->where(array('id'=>input('get.gid')))->find();
        $this->sharetitle = $list['share_title'];
        $this->desc = $list['share_content'];
        if(substr($list['share_thumb'],0,4) != 'http'){
            $list['share_thumb'] = 'http://'.$_SERVER['HTTP_HOST'].$list['share_thumb'];
        }
        $this->imgUrl = $list['share_thumb'];
        $this->share_jump = 'http://'.$_SERVER['HTTP_HOST'].url("Home/Nologin/gcard_share/id/".$_GET['id'].'/gid/'.$list['id']);
        $this->id = $_GET['id'];
        return  $this->fetch('Holidaycard/showview');
    }

	 // 报名页面分享后所有人都可以查看
     //报名详情
     public function enterfor()
     {
        $this->assign('unionid',input('get.unionid'));
        $this->assign('id',input('get.id'));
        $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
        $url = "$protocol$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
        $this->assign('share_url', $url);
        $this->title="参与活动";
        return  $this->fetch();
    }
    
    //报名页面
    public function Coactivities()
    {
        $this->title="活动页面";
        return  $this->fetch('Activity/Coactivities');
    }

    //详情预览
    public function Fullpreview()
    {
        $this->assign('unionid',input('get.unionid'));
        $this->assign('id',input('get.id'));
        $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
        $url = "$protocol$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
        $this->assign('share_url', $url);
        $this->title="详情预览";
        return  $this->fetch();
    }

    /**
     * 预览文章
     */
    public function previewArticle()
    {
 		$id=input('get.id');
        $res = [];
        $res['id']=$id;
        $this->uid=input('get.uid');

        $openid = session('openid');
		$data = $this->art_logic->getArticleInfo($res);
		$user_id = $data['user_id'];
		//文章访问统计
		user_stats($user_id,$id);
       
        //过渡处理
        $data['content'] = str_replace('width:','',$data['content']);
        $adverts = Db::table('s_advert')->where(['user_id' => $data['user_id']])->select();

        // 注意 URL 一定要动态获取，不能 hardcode.
        $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
        $url = "$protocol$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";

        $link_url = 'http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
        $content = mb_substr(cutstr_html($data['content']),0,51,'utf-8');
        $content = str_replace(PHP_EOL, ' ', str_replace('"','',$content));
        $share_title = str_replace(PHP_EOL, ' ', str_replace('"','',$data['title']));

        if(substr($data['thumb'],0,4) == 'http'){
            $imgUrl = $data['thumb'];
        }else{
            $imgUrl = 'http://'.$_SERVER['HTTP_HOST'].$data['thumb'];
        }

        //添加积分
        if((floor($data['visit']/100) - floor(($data['visit']-1)/100)) == 1 && $data['visit']>99){
            $dat['user_id']=$data['user_id'];
            $dat['inte'] = Db::table('s_inte')->where(array('id'=>7))->column('inte');
            $dat['addtime']=time();
            $dat['inte_id']=7;
            $zj = Db::table("s_user_info")->where(array('id'=>$dat['user_id']))->setInc('inte',$dat['inte']);
            if($zj){
                Db::table('s_inte_log')->insert($dat);
            }
        }
         
  		$getopenid_ = $_SESSION['share_openid'];//微信openid
        $nick_name = $_SESSION['nickname'] ;//微信昵称
    	$reward_num = Db::table('s_article_rewardlog')->where(array('article_id'=>$id,'status'=>1))->count();
        $article_cards = Db::table('s_user_info')->field('article_cards,wx_ewm_url')->where(array('id'=>$data['user_id']))->find();
        
        $this->assign('share_url', $url);
        $this->assign('article_cards',$article_cards['article_cards']);
        $this->assign('wx_ewm_url',$article_cards['wx_ewm_url']);
        $this->assign('tid',$id);
        $this->assign('data',$data);
        $this->assign('reward_num',$reward_num);
        $this->assign('ids',$id);
        $this->assign('sharetitle', $share_title);
        $this->assign('desc',$content);
        $this->assign('link', $link_url);
        $this->assign('imgUrl', $imgUrl);
        $this->assign('title', $data['title']);
        $this->assign('userId', $data['user_id']);
        $this->assign('adverts', $adverts);
        $this->assign('last', $adverts[count($adverts)-1]);
        $this->assign('first', $adverts[0]);
        $this->assign('share_host', config('SHARE_HOST'));
        
        $this->assign('getopenid_', $getopenid_);
        $this->assign('names',$nick_name);
        return  $this->fetch('Articlegrab/previewArticle');
    }

    //支付宝支付
    public function zf()
    {
        $this->vipid = input('get.vipid');
        $this->user_id = input('get.user_id');
        return  $this->fetch();
    }
    
    //app下载
    public function app()
    {
        return  $this->fetch();
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