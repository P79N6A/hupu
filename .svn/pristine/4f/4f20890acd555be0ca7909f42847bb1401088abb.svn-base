<?php
/** 视频管理 */
namespace app\home\controller;
use app\common\controller\HomeBase;
use app\api\logic\ArticleList  as artLogic;

class Video extends HomeBase
{

    /**
     * 列表
     */
    public function index()
    {
    	$art_logic = new artLogic();
        $getVideoList	=  $art_logic->getVideoList();
        $getArticleVideo  =  $art_logic->getArticleVideo();
        $this->assign('userInfo',$this->userInfo);
        $this->assign('getVideoList',$getVideoList);
        $this->assign('getArticleVideo',$getArticleVideo);
        return  $this->fetch();
    }

    /**
     * 打赏
     */
    public function reward()
    {
        return  $this->fetch();
    }

        // 明鑫之声
    public function mingxin()
    {
   
        if (empty($_GET['openid'])) {
        	header('Location:/getunionid.php?target_url='.urlencode('http://mingxin365.com/Home/video/mingxin'));
        	exit;
        } else {
        	$datass = json_decode($_COOKIE["openid_contents"], true);
	        $this->assign('username', $datass['nickname']);
	        $this->assign('user_img', $datass['headimgurl']);
	        $this->assign('openid', $_GET['openid']);
        }
          
        return  $this->fetch();
    }
 
    // 详情
    public function video_detail()
    {
        $video_type = Input('get.detail');
        if (empty($_GET['openid'])) {
            header('Location:/getunionid.php?target_url='.urlencode('http://mingxin365.com/Home/video/video_detail&detail='.$video_type));
            exit;
        } else {
            $datass = json_decode($_COOKIE["openid_contents"], true);
            $this->assign('username', $datass['nickname']);
            $this->assign('user_img', $datass['headimgurl']);
            $this->assign('openid', $_GET['openid']);
        }
            
        $this->video_ = Input('get.detail');
        return  $this->fetch();
    }
}


