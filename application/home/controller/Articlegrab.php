<?php
/** 文章管理 */
namespace app\home\controller;
use think\Db;
use app\common\controller\HomeBase;
use app\api\logic\ArticleList  as artLogic;

class Articlegrab extends HomeBase 
{
	private $art_logic = null;
	
    function _initialize()
    {
        parent::_initialize();
        $this->art_logic = new artLogic();
        $this->assign('user',$this->userInfo);
    }

    public function createReward()
    {
        //发起文章支付
        if($this->request->isPost())
        {
            $array_return = array("status"=>1,"msg"=>"success","data"=>array());
	        //判断是否是是微信公众号环境start
	        $openid = $_REQUEST['openid'] ? $_REQUEST['openid'] : '';
	    	if(!$openid) {
	            $this->array_return['ResultType'] = self::__ERROR__;
	            $this->array_return['Message'] = "非法访问";
	            return json($this->array_return);
	        }
	      	
	        $article_id = $_REQUEST['article_id'] ? intval($_REQUEST['article_id']) : 0;
	        $money = $_REQUEST['money'] ? intval($_REQUEST['money']) : 0;
	    	if(!$money && !$article_id) {
	            $this->array_return['ResultType'] = self::__ERROR__;
	            $this->array_return['Message'] = "缺少参数";
	            return json($this->array_return);
	        }
	        
        	if(!array($money,array(1,5,10,20,50,100))) {
	            $this->array_return['ResultType'] = self::__ERROR__;
	            $this->array_return['Message'] = "参数错误";
	            return json($this->array_return);
	        }
	        
            $user_id = Db::table('s_article_list')->where(array('id'=>$article_id))->column('user_id');
        	if(!$user_id) {
	            $this->array_return['ResultType'] = self::__ERROR__;
	            $this->array_return['Message'] = "文章不存在";
	            return json($this->array_return);
	        }
	        
	    	//判断是否是是微信公众号环境start
	        $user_agent = $_SERVER['HTTP_USER_AGENT'];
	        // 判断是不是微信浏览器
	        if (strpos($user_agent, 'MicroMessenger') === false) {
	            $type = 6;// 支付类型，6是H5支付
	            $is_wechat = 0;
	        	$source = "浏览器H5";
	        } else {
	            $type = 2;// 支付类型，2是公众号支付
	            $is_wechat = 1;
	        	$source = "服务号";
	        }

        	$nick_name = $avatar = '';
			$uinfo = session("user_info");
		   	if ($uinfo) {
				$nick_name = $uinfo['nick_name'];
				$avatar = $uinfo['headimg'];
		   	}else {   
				$nick_name = session("nickname"); 
				$avatar = session("headimgurl"); 
		    }
   	
	        $data = array('article_id'=>$article_id,'user_id'=>$user_id,'openid'=>$openid,'nick_name'=>$nick_name,'avatar'=>$avatar,'pay_source'=>$source,'money'=>$money,'add_time'=>time());
	        $reward_id = Db::table('s_article_rewardlog')->insert($data);
        
            $_data['object_id'] = $user_id;
			//$_data['type']=$post['type'];//2文章打赏 4视频打赏
			$json = json_encode($_data);
            if($reward_id){
                $data = [
                    'connect_id'=>$reward_id,
                    'type' =>$type,
                    'order_type'=>3,
                    'pay_money' =>$money,
                    'pay_title'=>"打赏",
                    'pay_stitle'=>"打赏",
                    'pay_tag'=>'费用',
                    'mark'=>$json,
                    'openid'=>$openid,
                ];
                $pay_info=get_obj('pay_test')->add_pay_log($data);
                $pay_info=(json_decode($pay_info,true));
                $pay_obj = $pay_info['obj'];
                $pra = $pay_obj['pra'];
				//var_dump($pra);
				//exit();
                if ($is_wechat == 1){
                    $pra=(json_decode($pra,true));
                }

                $pra['source'] = $is_wechat;
                if ($pra['mweb_url'] !=null){
                    $mweb_url = $pay_info['obj']['pra']['mweb_url'];
                    $pra['mweb_url'] = $mweb_url;
                }
                $array_return['data'] = $pra;
            }else{
                $array_return['status']=0;
                $array_return['msg']="发起支付失败";
            }
            return json($array_return);
        }
    }

    /**
     *  我的软文 列表
     */
    public function myListArticle()
    {
        $this->title="我的软文";
        $data = input('get.');
        $this->assign('type_id', $data['type_id']);

        //当前登录用户的文章列表
        $map['user_id'] = $this->userInfo['id'];
        if ( $data['title'] ) $map['title'] = $data['title'];
        if ( $data['type_id'] ) $map['atype_id'] = $data['type_id'];
        $map['page'] = $data['page'] ? $data['page'] : 1;
        $map['pageNum'] = $data['pageNum'] ? $data['pageNum'] : 20;
        list($list, $page, $count) = $this->art_logic->getArticle($map);
        foreach ($list as $key => $value) {
            $content=cutstr_html($value['content']);
            $list[$key]['content']=mb_substr($content,0,50,'utf-8');
            if($value['add_time'] >strtotime(date('Ymd'))) {
                $list[$key]['add_time'] = date('Y-m-d H:i:s', $value['add_time']);
            }else{
                $list[$key]['add_time']=date('Y-m-d',$value['add_time']);
            }
            if($value['thumb']=='/'){
                $list[$key]['thumb']='';
            }
        }

        $this->article_type = $this->art_logic->getType( ['user_id' => $this->userInfo['id']] );
        $this->assign('list', $list);
        $this->assign('page', $page);
        $this->assign('count', $count);
        
        return  $this->fetch();
    }

    public function uploadimg()
    {
        if($this->request->isPost()){
            $data=array('status'=>1,'url'=>'','msg'=>'');
            if(!empty($_FILES['upload_img']['name'])){
                if(substr($_SERVER['HTTP_HOST'],0,1) == 'w'){
                    $start_name = 'artile';
                }else{
                    $start_name = 'test';
                }
                $Upload = new \Admin\Controller\UploadsController();
                $end_name = trim(strrchr($_FILES['upload_img']['name'], '.'),'.');
                if($end_name == 'jpeg'){
                    $save_name = $start_name.'/'.date('Ymd').'/'.time().rand(1,9999).'.jpg';
                }else{
                    $save_name = $start_name.'/'.date('Ymd').'/'.time().rand(1,9999).'.'.$end_name;
                }
                $data['url'] = $Upload->Uploads($save_name,$_FILES['upload_img']);
            }else{
                $data['status']=0;
            }
            return json($data);
        }
    }

     /**
     * 添加（修改）文章
     */
    public function writeArticle()
    {
        $this->ids=$this->userInfo['id'];
        //获取文章分类
        $article_type = $this->art_logic->getType( ['user_id' => $this->userInfo['id']] );
        //底部广告是否开启
        $condition['article_advert'] = $this->userInfo['article_advert'] ? 1 : 0;
        //微缩V网是否开启
        $condition['article_cards'] = $this->userInfo['article_cards'] ? 1 : 0;
        //是否开户打赏功能
        $condition['article_reward'] = $this->userInfo['article_reward'] ? 1 : 0;

        $this->assign('condition', $condition);
        $this->assign('article_type', $article_type);
        return  $this->fetch();
    }

    /**
     * 添加（修改）文章
     */
    public function addArticle()
    {
        $this->title="添加文章";
        $this->ids=$this->userInfo['id'];
        if ( $this->request->isPost() ) {
            $rules = [
                ['name', 'require', '文章标题不能为空'],
                ['content', 'require', '文章内容不能为空'],
                ['atype_id', 'require', '请选择文章分类'],
                ['thumb', 'require', '请上传封面图片'],
            ];

            $articleListMod = Db::table('s_article_list');
            if ( !$data = $articleListMod->validate($rules)->create() ) return json(['status' => 0, 'msg' => $articleListMod->getError()]);
            if ( isset($data['id']) && $data['id'] > 0 ) {
                //编辑文章
                $data['content']=htmlspecialchars_decode($data['content']);

                if ( $articleListMod->update($data) ) {
                    return json(['status' => 1, 'msg' => '编辑文章成功']);
                } else {
                    return json(['status' => 0, 'msg' => $articleListMod->getError()]);
                }
            } else {
                //添加文章
                if ( isset($data['id']) ) unset($data['id']);
                $data['user_id'] = $this->userInfo['id'];
                $data['add_time'] = time();
                $userInfo = Db::table('s_user_info')->where('id='.$this->userInfo['id'])->find();
                $userInfo['article_reward'] = input('article_reward');
                $result = Db::table('s_user_info')->update($userInfo);
                if ($result === false) {
                    return json(['status' => 0, 'msg' => '保存失败']);
                }
                if ( $articleListMod->insert($data) ) {
                    return json(['status' => 1, 'msg' => '添加文章成功']);
                } else {
                    return json(['status' => 0, 'msg' => $articleListMod->getError()]);
                }
            }
        } else {
            $this->userInfo = Db::table('s_user_info')->where('id='.$this->userInfo['id'])->find();
            $id = input('get.id', 0, 'intval');
            $title = '添加文章';
            if ( $id > 0 ) {

                $res = $this->art_logic->getArticleInfo(['user_id' => $this->userInfo['id'], 'id' => $id]);
                $res['content']=htmlspecialchars_decode($res['content']);
                $this->assign('res', $res);
                $title = '编辑文章';
            }
            $this->assign('title', $title);
            //获取文章分类
            $article_type = $this->art_logic->getType( ['user_id' => $this->userInfo['id']] );
            //底部广告是否开启
            $condition['article_advert'] = $this->userInfo['article_advert'] ? 1 : 0;
            //微缩V网是否开启
            $condition['article_cards'] = $this->userInfo['article_cards'] ? 1 : 0;
            //是否开户打赏功能
            $condition['article_reward'] = $this->userInfo['article_reward'] ? 1 : 0;
            $this->assign('condition', $condition);
            // var_dump($res);die;
            $this->assign('article_type', $article_type);
            return  $this->fetch();
        }
    }

    /**
     * 预览文章
     */
    public function previewArticle()
    {
        $id = input('get.id');
        $res = [];
        $res['id']=$id;
        $this->uid = input('get.uid');
        
		$data = $this->art_logic->getArticleInfo($res);
		$user_id = $data['user_id'];
        $data['content'] = htmlspecialchars_decode($data['content']);
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
          
        $getopenid_ = $this->userInfo['openid'];
        $nick_name = $this->userInfo['nick_name'];

        $reward_num = Db::table('s_article_rewardlog')->where(array('article_id'=>$id,'status'=>1))->count();
        $article_cards = Db::table('s_user_info')->where(array('id'=>$data['user_id']))->column('article_cards');
        
        $this->assign('share_url', $url);
        $this->assign('article_cards',$article_cards);
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

        return  $this->fetch();
    }

    /**
     * 预览文章
     */
    public function previewArticle123()
    {
        $id = input('get.id');
        $res = [];
        $res['id']=$id;
        $this->uid = input('get.uid');
        //判断是否是是微信公众号环境start
        $user_agent = $_SERVER['HTTP_USER_AGENT'];
        if (strpos($user_agent, 'MicroMessenger') === false) {
            // 不是微信浏览器
            // 支付类型，6是H5支付
//            $source = "浏览器H5";
            $data['ip'] = get_client_ip();
            $data['create_time'] = time();
            $reward_user_id = DB::table('s_reward_user')->insert($data);
        }else{
            // 支付类型，2是公众号支付
            // 是微信环境
            $openid = cookie('openid');
            // 另一种实例化写法
            $reward_user = new \Api\Model\RewardUserModel();
            $result = $reward_user->where(['openid'=>$openid])->find();
            if (!$result){
                $member = Db::table('s_member')->where(['openid'=>$openid])->field('id')->find();
                $data = [];
                if (!$member){
                    $data['is_member'] = 0;
                }else{
                    $data['is_member'] = 1;
                    $data['member_id'] = $member['id'];
                }
                $data['openid'] = $openid;
                $data['create_time'] = time();
                $reward_user_id = $reward_user->insert($data);
            }else{
                $reward_user_id = $result['id'];
            }
        }

        $data=$this->art_logic->getArticleInfo($res);
        $data['content'] = htmlspecialchars_decode($data['content']);
        //过渡处理
        $data['content'] = str_replace('width:','',$data['content']);
        $adverts = Db::table('s_advert')->where(['user_id' => $data['user_id']])->select();
        //初始化
        if (!class_exists('\JSSDK')) {
         	require_once EXTEND_PATH.'/wxsdkapi/jssdk.php';
            require_once EXTEND_PATH.'/wxpayapi/lib/WxPay.Config.php';
        }
        $jssdk = new \JSSDK(config('WEIXINPAY_CONFIG.APPID'), config('WEIXINPAY_CONFIG.APPSECRET'));
        $signPackage = $jssdk->GetSignPackage();
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

        $this->assign('tid',$id);
        $this->assign('data',$data);
        $this->assign('reward_user_id',$reward_user_id);
        $this->assign('ids',$id);
        $this->assign('signPackage', $signPackage);
        $this->assign('sharetitle', $share_title);
        $this->assign('desc',$content);
        $this->assign('link', $link_url);
        $this->assign('imgUrl', $imgUrl);
        $this->assign('title', $data['title']);
        $this->assign('userId', $data['user_id']);
        $this->assign('adverts', $adverts);
        $this->assign('last', $adverts[count($adverts)-1]);
        $this->assign('first', $adverts[0]);

        return  $this->fetch();
    }

    /**
     * 预览文章
     */
    public function previewArticles()
    {
        $id = input('get.id');
        $res['id']=$id;
        $data=$this->art_logic->getArticleInfo($res);
        $data['content'] = htmlspecialchars_decode($data['content']);
        $data['content'] = str_replace('width:','',$data['content']);

        $this->data = $data;
        $adverts = Db::table('s_advert')->where(['user_id' => $data['user_id']])->select();
        //初始化
        if (!class_exists('\JSSDK')) {
          	require_once EXTEND_PATH.'/wxsdkapi/jssdk.php';
            require_once EXTEND_PATH.'/wxpayapi/lib/WxPay.Config.php';
        }
        
        $jssdk = new \JSSDK(config('WEIXINPAY_CONFIG.APPID'), config('WEIXINPAY_CONFIG.APPSECRET'));
        $signPackage = $jssdk->GetSignPackage();

        $link_url = 'http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
        $content = mb_substr(cutstr_html($data['content']),0,51,'utf-8');
        $content = str_replace(PHP_EOL, ' ', str_replace('"','',$content));
        $this->assign('signPackage', $signPackage);
        $this->assign('sharetitle', $data['title']);
        $this->assign('desc',$content);
        $this->assign('link', $link_url);
        if(substr($data['thumb'],0,4) == 'http'){
            $imgUrl = $data['thumb'];
        }else{
            $imgUrl = 'http://'.$_SERVER['HTTP_HOST'].$data['thumb'];
        }
        $this->imgUrl=$imgUrl;
        $this->title=$data['title'];
        $this->adverts=$adverts;
        $this->last=$adverts[count($adverts)-1];
        $this->first=$adverts[0];
        $this->userId = $data['user_id'];
        return  $this->fetch();
    }


    /**
     * 删除文章
     */
    public function delArticle() 
    {
        $id = input('post.id', 0, 'intval');
        if ( $id <= 0 ) return json(['status' => 0, 'msg' => '参数错误']);

        $articleListMod = Db::table('s_article_list');
        $res = $articleListMod->find($id);
        if ( !$res ) return json(['status' => 0, 'msg' => '文章信息不存在']);
        if ( $res['user_id'] != $this->userInfo['id'] ) return json(['status' => 0, 'msg' => '无操作权限']);

        if ( $articleListMod->delete($id) ) {
            return json(['status' => 1, 'msg' => '删除成功']);
        } else {
            return json(['status' => 0, 'msg' => $articleListMod->getError()]);
        }
    }

    /**
     *  文章广场 列表
     */
    public function listArticle()
    {
        $this->title="文章广场";
        $data = input('get.');
        $map = [];
        $this->user_id = $this->userInfo['id'];
        if ( $data['type_id'] ) $map['atype_id'] = $data['type_id'];
        $map['page'] = $data['page'] ? $data['page'] : 1;
        $map['is_video']=0;
        $map['user_id']=0;
        //超过2个月之前的文章删除
        list($list, $page, $count) = $this->art_logic->getArticle($map);
        foreach ($list as $key => $value) {
            $content=cutstr_html($value['content']);
            $list[$key]['content']=mb_substr($content,0,50,'utf-8');

        }
        $this->article_type = $this->art_logic->getType( ['user_id' => 0] );
        $this->assign('list', $list);
        $this->assign('page', 2);
        $this->assign('count', $count);
        if(isAjax) {
            return json(array('result'=>$list, 'page' => $data['page'] + 1));
            exit;
        }
        
        return  $this->fetch();
    }

    /**
     * 删除文章分类
     */
    public function delArticleCategory()
    {
        $id = input('post.id', 0, 'intval');
        if ( $id <= 0 ) return json(['status' => 0, 'msg' => '参数错误']);

        $articleTypeMod = Db::table('s_article_type');
        $res = $articleTypeMod->find($id);
        if ( !$res ) return json(['status' => 0, 'msg' => '分类信息不存在']);
        if ( $res['user_id'] != $this->userInfo['id'] ) return json(['status' => 0, 'msg' => '无操作权限']);

        if ( $articleTypeMod->delete($id) ) {
            return json(['status' => 1, 'msg' => '删除成功']);
        } else {
            return json(['status' => 0, 'msg' => $articleTypeMod->getError()]);
        }
    }

    /**
     * 添加（修改）文章分类(列表)
     */
    public function addArticleCategory()
    {
        $articleLogic = $this->art_logic;
        $user_id = $this->userInfo['id'];
        $this->user_id = $this->userInfo['id'];
        if ( $this->request->isPost() ) {
            $data = input('post.');
            if ( isset($data['id']) && $data['id'] > 0 ) {
                $arr = [
                    'id' => $data['id'],
                    'name' => $data['name'],
                ];
                if ( Db::table('s_article_type')->update($arr) ) {
                    return json(['status' => 1, 'msg' => '编辑分类成功']);
                } else {
                    return json(['status' => 0, 'msg' => $articleLogic->getError()]);
                }
            } else {
                //添加
                $arr = [
                    'user_id' => $user_id,
                    'name' => $data['name'],
                    'add_time' => time(),
                ];
                if ( Db::table('s_article_type')->insert($arr) ) {
                    return json(['status' => 1, 'msg' => '添加分类成功']);
                } else {
                    return json(['status' => 0, 'msg' => $articleLogic->getError()]);
                }
            }
        } else {
            //查找当前用户已添加的文章分类列表
            $article_type =$articleLogic->getType( ['user_id' => $user_id] );
            $this->assign('article_type', $article_type);
            return  $this->fetch();
        }
    }

    /**
     *  编辑微缩V网
     */
    public function addMicroCard()
    {
        if ( $this->request->isPost() ) {
            $data = input('post.');
            $arr = [
                'id' => $this->userInfo['id'],
                'share_my_introduct' => $data['share_my_introduct'],
                'share_my_advantage' => $data['share_my_advantage'],
                'share_my_resource' => $data['share_my_resource'],
                'article_cards' => $data['article_cards'],
            ];
            $userInfoMod = Db::table('s_user_info');
            if ( $userInfoMod->update($arr) ) {
                return json(['status' => 1, 'msg' => '保存成功']);
            } else {
                return json(['status' => 0, 'msg' => $userInfoMod->getError()]);
            }
        } else {
            $this->userInfo = Db::table('s_user_info')->where('id='.$this->userInfo['id'])->find();
            $this->assign('users', $this->userInfo);
            return  $this->fetch();
        }
    }

    /**
     *  文章捉取
     */
    public function catchArticle()
    {
        $this->title="文章抓取";
        $this->id=$_GET['id'];
        if ( $this->request->isPost() ) {
            try {
                $url = input('post.url', '', 'trim');
                if ( $url ) {
                    $user_id=$this->userInfo['id'];
                    $id = $this->art_logic->captArticle($url,$user_id);
                    if ( $id ) {
                        //TODO 保存文章内容
                        return json(['status' => 1, 'msg' => "抓取成功" ,"id"=>$id]);
                    }else{
                        return json(['status' => 0, 'msg' => "抓取失败" ]);
                    }
                }
            } catch(\Exception $e) {
                return json(['status' => 0, 'msg' => $e->getMessage()]);
            }
        } else {
            return  $this->fetch();
        }
     }

     /**
     *  文章捉取
     */
    public function catchArticles()
    {
        $this->title="抓取文章";
        if ( $this->request->isPost() ) {
            try {
                $url = input('post.url', '', 'trim');
                if ( $url ) {
                    $user_id=$this->userInfo['id'];
                    $id = $this->art_logic->captArticle($url,$user_id);
                    if ( $id ) {
                        //TODO 保存文章内容
                        return json(['status' => 1, 'msg' => "抓取成功" ,"id"=>$id]);
                    }else{
                        return json(['status' => 0, 'msg' => "抓取失败" ]);
                    }
                }
            } catch(\Exception $e) {
                return json(['status' => 0, 'msg' => $e->getMessage()]);
            }
        } else {
            return  $this->fetch();
        }
     }

     //文章领取
	public function get()
	{
		$this->title="文章推广";
		$this->openid = $this->userInfo['openid'];
		$this->id = $this->userInfo['id'];
    	return  $this->fetch();
	}


	public function artpreview()
	{
		$this->openid = $this->userInfo['openid'];
		$id = $this->userInfo['id'];
        $this->kid = $_GET['kid'];
        $this->aid = $_GET['aid'];
        $this->sid = $_GET['id'];
        $data = Db::table('s_user_extend_article')->alias('ua')->leftJoin(' s_system_article sa','sa.id = ua.s_article_id')
            ->field('sa.title,sa.id,sa.content,sa.cover_img')->where(array('ua.id'=>$_GET['aid']))->find();

        $this->erweima = Db::table('s_user_info')->where(array('id'=>$id))->column('wx_ewm_url');
        // 注意 URL 一定要动态获取，不能 hardcode.
        $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
        $url = "$protocol$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
        $this->assign('share_url', $url);
        $link_url = 'http://'.$_SERVER['HTTP_HOST'].'/index.php?s=/Home/Nologin/share_article/id/'.$id.'/kid/'.$_GET['kid'].'/aid/'.$_GET['aid'].'/user_id/'.$id;
        $content = mb_substr(cutstr_html($data['content']),0,20,'utf-8').'...';
        $content = str_replace(PHP_EOL, ' ', str_replace('"','',$content));
        $this->title=$data['title'];

        $this->assign('sharetitle', $data['title']);
        $this->assign('desc',$content);
        $this->assign('link', $link_url);
        $this->assign('imgUrl', $data['cover_img']);
        $this->id = $id;
    	return  $this->fetch();
	}
	
	public function personal()
	{
		$this->title="个人中心";
		$this->openid = $this->userInfo['openid'];
		$this->id = $this->userInfo['id'];
    	return  $this->fetch();
	}
	
	public function write()
	{
    	return  $this->fetch();
    }
    
	// 我的文章列表
	public function articlelist()
	{
		$data = input('get.');
		$this->assign('type_id', $data['type_id']);
		
		//当前登录用户的文章列表
		$map['user_id'] = $this->userInfo['id'];
		if ( $data['title'] ) $map['title'] = $data['title'];
		if ( $data['type_id'] ) $map['atype_id'] = $data['type_id'];
		$map['page'] = $data['page'] ? $data['page'] : 1;
		$map['pageNum'] = $data['pageNum'] ? $data['pageNum'] : 20;
		list($list, $page, $count) = $this->art_logic->getArticle($map);
		foreach ($list as $key => $value) 
		{
			$content=cutstr_html($value['content']);
		    $list[$key]['content']=mb_substr($content,0,50,'utf-8');
		    if($value['add_time'] >strtotime(date('Ymd'))) {
		    	$list[$key]['add_time'] = date('Y-m-d H:i:s', $value['add_time']);
		    }else{
		        $list[$key]['add_time']=date('Y-m-d',$value['add_time']);
		    }
		    if($value['thumb']=='/'){
		        $list[$key]['thumb']='';
		    }
		}

		$this->article_type = $this->art_logic->getType( ['user_id' => $this->userInfo['id']] );
		$this->assign('list', $list);
		$this->assign('page', $page);
		$this->assign('count', $count);
	    return  $this->fetch();
	}
				
	// 微缩V网			
	public function vnote()
	{
		$this->title="V网信息";
		if ( $this->request->isPost() ) {	
			$data = input('post.');
			$arr = ['id' => $this->userInfo['id'],'share_my_introduct' => $data['share_my_introduct'],
				    'share_my_advantage' => $data['share_my_advantage'],'share_my_resource' => $data['share_my_resource'],
				    'article_cards' => $data['article_cards'],];
			$userInfoMod = Db::table('s_user_info');
			if ( $userInfoMod->update($arr) ) {
				return json(['status' => 1, 'msg' => '保存成功']);
			} else {
				return json(['status' => 0, 'msg' => $userInfoMod->getError()]);
			}	
		} else {
			$this->userInfo = Db::table('s_user_info')->where('id='.$this->userInfo['id'])->find();
			$this->assign('users', $this->userInfo);
			return  $this->fetch();		
		}		
	}

        // 新版文章的
    public function  new_article()
    {
     	$this->title="文章";
        return  $this->fetch();        
    }
    
    public function articleeditor()
    {
        $this->assign('id',$this->userInfo['id']);
      	$article_cards = Db::table('s_user_info')->where(array('id'=>$this->userInfo['id']))->column('article_cards');
//    	var_dump($article_cards);die;
      	$this->assign('article_cards',$article_cards);
        $this->assign('ar_id',input("get.ar_id")); 
        return  $this->fetch();
    }
}