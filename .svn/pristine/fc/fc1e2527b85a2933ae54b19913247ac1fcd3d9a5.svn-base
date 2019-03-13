<?php
/** 共享平台 */
namespace app\home\controller;
use app\common\controller\ShareBase;
use app\api\logic\VisitLog  as visitLogic;
use app\api\logic\ArticleList  as artLogic;
use think\Db;

class Share extends ShareBase
{
	private $visit_logic = null;
	private $art_logic = null;
	
    function _initialize()
    {
        parent::_initialize();
        $this->art_logic = new artLogic();
        $this->visit_logic = new visitLogic();
        $this->assign('user',$this->userInfo);
    }
    
    /**
     * 预览文章
     */
    public function previewArticle()
    {
        $id=input('get.id');
        $res = [];
        $res['id']=$id;

       	$source = input('get.source');
        if ($source == "share")
        {
           $share_id = input('get.share_id');
           $share_user_id = input('get.share_user_id');
           $share_openid = session("share_openid");
           $nickname = session("nickname");
           $headimgurl = session("headimgurl");
           
           if ($share_openid && $share_id && $share_user_id)
           {
           		$share_log_clicked_count = $this->get_clicked_count($share_id,$share_openid);
                if ($share_log_clicked_count < 1){
                	$share_activity =  Db::table('s_share_list')->where(['id'=>$share_id])->field('count,clicked_count,single_amount')->find();
                    if ($share_activity['clicked_count'] < $share_activity['count']){

                    	Db::table('s_share_list')->where(['id'=>$share_id])->setInc('clicked_count',1);
                        $data['share_id'] = $share_id;
                        $data['share_user_id'] = $share_user_id;
                        $data['money'] = $share_activity['single_amount'];
                        $data['browser_nickname'] = $nickname;
                        $data['browser_headimg'] = $headimgurl;
                        $data['browser_openid'] = $share_openid;
                        $data['create_time'] = time();
                            
                        Db::table('s_share_log')->insert($data);
                        Db::table('s_share_user_list')->where(['share_id'=>$share_id,'share_user_id'=>$share_user_id])->setInc('total_money',$share_activity['single_amount']);
                        Db::table('s_user_info')->where(['id'=>$share_user_id])->setInc('money',$share_activity['single_amount']);
						$this->consume_log($share_user_id, $share_activity['single_amount']);
                    }
           		}
           }

           $share_list = Db::table('s_share_list')->where(['id'=>$share_id])->find();
           $data=$this->art_logic->getArticleInfo($res);
           $data['content'] = htmlspecialchars_decode($data['content']);
           //过渡处理
           $data['content'] = str_replace('width:','',$data['content']);
           $adverts = Db::table('s_advert')->where(['user_id' => $data['user_id']])->select();
            // 注意 URL 一定要动态获取，不能 hardcode.
            $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
            $url = "$protocol$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
            $this->assign('share_url', $url);

           $link_url = 'http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
           $share_title = $share_list['title'];
           $imgUrl = $share_list['cover_img'];
           $content = $share_list['content'];

           $this->assign('tid',$id);
           $this->assign('data',$data);
           $this->assign('ids',$id);
           $this->assign("hidden",1);
           $this->assign('sharetitle', $share_title);
           $this->assign('desc',$content);
           $this->assign('link', $link_url);
           $this->assign('imgUrl', $imgUrl);
           $this->assign('title', $share_list['title']);
           $this->assign('userId', $data['user_id']);
           $this->assign('adverts', $adverts);
           $this->assign('last', $adverts[count($adverts)-1]);
           $this->assign('first', $adverts[0]);
       }else{
           $data = $this->art_logic->getArticleInfo($res);
           $data['content'] = htmlspecialchars_decode($data['content']);
           //过渡处理
           $data['content'] = str_replace('width:','',$data['content']);
           $adverts = Db::table('s_advert')->where(['user_id' => $data['user_id']])->select();
            // 注意 URL 一定要动态获取，不能 hardcode.
            $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
            $url = "$protocol$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
            $this->assign('share_url', $url);
            
           $link_url = "";
           $share_title = "";
           $imgUrl = "";

           $this->assign('tid',$id);
           $this->assign('data',$data);
           $this->assign('ids',$id);
           $this->assign('sharetitle', $share_title);
           $this->assign('desc',"");
           $this->assign('link', $link_url);
           $this->assign('imgUrl', $imgUrl);
           $this->assign('title', $data['title']);
           $this->assign("hidden",0);
           $this->assign('userId', $data['user_id']);
           $this->assign('adverts', $adverts);
           $this->assign('last', $adverts[count($adverts)-1]);
           $this->assign('first', $adverts[0]);

       }

        return  $this->fetch();
    }
    
	//记录用户消费日志
    private function consume_log($user_id,$amount)
    {
		$share_consume = array();
    	$share_consume['user_id'] = $user_id;
        $share_consume['money'] = $amount;
        $share_consume['type'] = 1;
        $share_consume['create_time'] = time();        
        Db::table('s_user_consume')->insert($share_consume);
        
        $share_consume = array();
        $share_consume['user_id'] = $user_id;
        $share_consume['order_sn'] = getOrderSn();
        $share_consume['object_id'] = $user_id;
        $share_consume['type'] = 99;
        $share_consume['money'] = $amount;
        $share_consume['msg'] = 'V网分享推广——获益';
        $share_consume['add_time'] = time();
        Db::table('s_capital_log')->insert($share_consume);
    }
    
    private function get_clicked_count($share_id,$share_openid)
    {
        $map['share_id'] = $share_id;
        $map['browser_openid'] = $share_openid;
        $share_log_clicked_count = Db::table('s_share_log')->where($map)->count();
        return $share_log_clicked_count;
    }

    /**
     *新的我的V网，只判断shareID，不能编辑
     */
    public function myCard()
    {
        if(!input('get.user_id')) {
            // 没有带id
            //判断是否通过分享来的，不是分享来的就是要查看自己的V网，首先需要登录，其次需要判断是不是VIP,没登录要求登录，登录了判断是不是VIP
            if($this->userInfo['id']) {
                is_vip();
            }else{
                header("location:".url("Home/Mycenter/login"));
            }
        }else{
            if(input('get.user_id') != $this->userInfo['id']){
                //添加浏览记录
                $visit_id = $this->visit_logic->addVisitLog(array('object_id'=>input('get.user_id'),'user_id'=>$this->userInfo['id']));
            }
        }

        $source = input('get.source');
        if ($source == "share"){
            $share_id = input('get.share_id');
            $share_user_id = input('get.share_user_id');
            $share_list = Db::table('s_share_list')->where(array('id'=>$share_id))->field('id,cover_img,content,title')->find();
            
               $share_openid = session("share_openid");
               $nickname = session("nickname");
               $headimgurl = session("headimgurl");
               if ($share_openid && $share_id && $share_user_id){
                   $share_log_clicked_count = $this->get_clicked_count($share_id,$share_openid);
                   if ($share_log_clicked_count < 1)
                   {
                       $share_activity =  Db::table('s_share_list')->where(['id'=>$share_id])->field('count,clicked_count,single_amount')->find();
                       if ($share_activity['clicked_count'] < $share_activity['count']){
                           Db::table('s_share_list')->where(['id'=>$share_id])->setInc('clicked_count',1);
                           $data['share_id'] = $share_id;
                           $data['share_user_id'] = $share_user_id;
                           $data['money'] = $share_activity['single_amount'];
                           $data['browser_nickname'] = $nickname;
                           $data['browser_headimg'] = $headimgurl;
                           $data['browser_openid'] = $share_openid;
                           $data['create_time'] = time();
                           Db::table('s_share_log')->insert($data);

                           Db::table('s_share_user_list')->where(['share_id'=>$share_id,'share_user_id'=>$share_user_id])->setInc('total_money',$share_activity['single_amount']);
                           Db::table('s_user_info')->where(['id'=>$share_user_id])->setInc('money',$share_activity['single_amount']);
						   $this->consume_log($share_user_id, $share_activity['single_amount']);
                       }
                   }
               }

            $user_id = input('get.user_id');

            $this->assign("user_id",$user_id);
            $this->assign('isshare',1);
            $this->assign("hidden",0);

            $user = Db::table('s_user_info')->where(array('id'=>$user_id))->field('id,style,unionid')->find();
            $this->title = $user['nick_name'] . "的V网";
            $this->isdisplay = 0;


                // 注意 URL 一定要动态获取，不能 hardcode.
                $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
                $url = "$protocol$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
                $this->assign('share_url', $url);


                $link_url = 'http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
                $contents = cutstr_html($share_list['content']);
                $contents =str_replace('"', '\'', $contents);
                $this->assign('desc', preg_replace('/\s/is','', $contents));
                $this->assign('unionid', $user['unionid']);
                $this->assign('sharetitle', $share_list['title']);
                $this->assign('link_url',$link_url);
                $this->assign('imgUrl',$share_list['cover_img']);
				$this->assign("share_user_id",$share_user_id);
                return  $this->fetch('Share/Vhomepage');
        }else{
            $user_id = input('get.user_id')?input('get.user_id'):$this->userInfo['id'];
            if (is_null($user_id)) {
                $this->userInfo = null;
                header("location:".url("Home/Mycenter/login"));
            }

            $this->assign("user_id",$user_id);
            $this->assign('isshare',1);
            $this->assign("hidden",1);

            $user = Db::table('s_user_info')->where(array('id'=>$user_id))->field('id,unionid,nick_name,style,share_my_introduct,headimg')->find();

            $this->unionid = input('get.user_id')?$user['unionid']:$this->userInfo['unionid'];
            if ($this->userInfo['id'] == $user_id) {
                $this->title = "我的V网";
                $this->isdisplay = 1;
            } else {
                    $this->title = $user['nick_name'] . "的V网";
                    $this->isdisplay = 0;
                }

                // 注意 URL 一定要动态获取，不能 hardcode.
                $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
                $url = "$protocol$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
                $this->assign('share_url', $url);
                $this->assign('link_url',"");
                $this->assign('sharetitle', "");
                $this->assign('desc', "");
                $this->assign('imgUrl', "");

        		return  $this->fetch('Share/Vhomepage');
        }
    }

    public function preview()
    {
        $source = input('get.source');
        if ($source == "share"){
            $share_id = input('get.share_id');
            $share_user_id = input('get.share_user_id');
            $user_id = input('get.uid');
            $share_openid = session("share_openid");
            $nickname = session("nickname");
            $headimgurl = session("headimgurl");

            if ($share_openid && $share_id && $share_user_id){

                $share_log_clicked_count = $this->get_clicked_count($share_id,$share_openid);
                if ($share_log_clicked_count < 1){
                    $share_activity =  Db::table('s_share_list')->where(['id'=>$share_id])->field('count,clicked_count,single_amount')->find();
                    if ($share_activity['clicked_count'] < $share_activity['count']){
                        Db::table('s_share_list')->where(['id'=>$share_id])->setInc('clicked_count',1);

                        $data['share_id'] = $share_id;
                        $data['share_user_id'] = $share_user_id;
                        $data['money'] = $share_activity['single_amount'];
                        $data['browser_nickname'] = $nickname;
                        $data['browser_headimg'] = $headimgurl;
                        $data['browser_openid'] = $share_openid;
                        $data['create_time'] = time();
                        Db::table('s_share_log')->insert($data);

                        Db::table('s_share_user_list')->where(['share_id'=>$share_id,'share_user_id'=>$share_user_id])->setInc('total_money',$share_activity['single_amount']);
                        Db::table('s_user_info')->where(['id'=>$share_user_id])->setInc('money',$share_activity['single_amount']);
						$this->consume_log($share_user_id, $share_activity['single_amount']);
                    }
                }
            }

            $share_list = Db::table('s_share_list')->where(['id'=>$share_id])->find();
            // 注意 URL 一定要动态获取，不能 hardcode.
            $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
            $url = "$protocol$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
            $this->assign('share_url', $url);
            $link_url = 'http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];

            $this->assign('sharetitle', $share_list['title']);
            $this->assign('desc',$share_list['content']);
            $this->assign('link_url',$link_url);
            $this->assign('imgUrl',$share_list['cover_img']);

            $user_info = Db::table('s_user_info')->where(['id'=>$user_id])->field('unionid')->find();
            $this->assign('id',$user_id);
            $this->assign('unionid',$user_info['unionid']);
            $this->assign('sid',input('get.id',0));
            $this->assign('hidden',1);
            $this->assign('mid',input('get.mid',0));
            $this->assign('pids',input('get.pids',0));
            $this->assign('cids',input('get.cids',0));
            return  $this->fetch();
        }else{
            $userid = input('get.userid',0);
            $this->assign('unionid',input('get.unionid',0));
            $this->assign('userid',input('get.uid',0));
            $this->assign('hidden',0);

            // 获取列表中的id
            $this->assign('sid',input('get.id',0));
            $this->assign('mid',input('get.mid',0));
            $this->assign('pids',input('get.pids',0));
            $this->assign('cids',input('get.cids',0));

            return  $this->fetch();
        }
  	}

    //test
    public function my_Activity_detail()
    {
        $source = input('get.source');
        if ($source == "share"){
            $share_id = input('get.share_id');
            $share_user_id = input('get.share_user_id');
            $user_id = input('get.uid');
            $id = input('get.id');
            $share_openid = session("share_openid");
            $nickname = session("nickname");
            $headimgurl = session("headimgurl");

            if ($share_openid && $share_id && $share_user_id)
            {
                $share_log_clicked_count = $this->get_clicked_count($share_id,$share_openid);
                if ($share_log_clicked_count < 1){
                    $share_activity =  Db::table('s_share_list')->where(['id'=>$share_id])->field('count,clicked_count,single_amount')->find();
                    if ($share_activity['clicked_count'] < $share_activity['count']){
                        Db::table('s_share_list')->where(['id'=>$share_id])->setInc('clicked_count',1);
                        $data['share_id'] = $share_id;
                        $data['share_user_id'] = $share_user_id;
                        $data['money'] = $share_activity['single_amount'];
                        $data['browser_nickname'] = $nickname;
                        $data['browser_headimg'] = $headimgurl;
                        $data['browser_openid'] = $share_openid;
                        $data['create_time'] = time();
                        Db::table('s_share_log')->insert($data);

                        Db::table('s_share_user_list')->where(['share_id'=>$share_id,'share_user_id'=>$share_user_id])->setInc('total_money',$share_activity['single_amount']);
                        Db::table('s_user_info')->where(['id'=>$share_user_id])->setInc('money',$share_activity['single_amount']);
						$this->consume_log($share_user_id, $share_activity['single_amount']);
                    }
                }
            }

            $share_list = Db::table('s_share_list')->where(['id'=>$share_id])->find();
            // 注意 URL 一定要动态获取，不能 hardcode.
            $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
            $url = "$protocol$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
            $this->assign('share_url', $url);
            $link_url = 'http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];

            $this->assign('sharetitle', $share_list['title']);
            $this->assign('desc',$share_list['content']);
            $this->assign('link_url',$link_url);
            $this->assign('imgUrl',$share_list['cover_img']);

            $user_info = Db::table('s_user_info')->where(['id'=>$share_list['user_id']])->field('unionid')->find();
            $this->assign('id',$id);
            $this->assign('unionid',$user_info['unionid']);
            $this->assign('hidden',1);
            return  $this->fetch();
        }else{
            $this->assign('id',0);
            $this->assign('hidden',0);
            return  $this->fetch();
        }
    }
}