<?php
/** 视频管理 */
namespace app\home\controller;
use app\common\controller\ShareBase;

class VideoBrand extends ShareBase
{

    /**
     * 打赏
     */
    public function reward()
    {
        return json();
    }

        // 明鑫之声
    public function mingxin()
    {
        if (empty($_GET['openid'])) {
        	header('Location:/getunionid.php?target_url='.urlencode('http://mingxin365.com/Home/video/mingxin'));
        	exit;
        } 
        
        $datass = json_decode($_COOKIE["openid_contents"], true);
	    $this->assign('username', $datass['nickname']);
	    $this->assign('user_img', $datass['headimgurl']);
	    $this->assign('openid', $_GET['openid']);
	    
        return json();
    }
 
    // 详情
    public function video_detail()
    {
        $video_type = Input('get.detail');
        if (empty($_GET['openid'])) {
           header('Location:/getunionid.php?target_url='.urlencode('http://mingxin365.com/Home/video/video_detail&detail='.$video_type));
           exit;
        }
                 
        $datass = json_decode($_COOKIE["openid_contents"], true);
        $this->assign('username', $datass['nickname']);
        $this->assign('openid', $_GET['openid']);
            
        $this->video_ = Input('get.detail');
        return json();
    }
}


