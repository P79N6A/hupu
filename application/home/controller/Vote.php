<?php
/**
 *  投票系统
 * @javatom
 * @王浩
 * 201809026
 * 控制类
 */
namespace app\home\controller;
use think\Db;

class Vote
{
    /**
     *投票展示页面
     * 自动登录页面
     */
    public function voteindex()
    {
        if (isPOST) { exit; }
        $user_agent = $_SERVER['HTTP_USER_AGENT'];
        if (strpos($user_agent, 'MicroMessenger') === false) {
           exit;
        }
        
        if (empty($_GET['openid'])) {
            header('Location:/getunionid.php?target_url='.urlencode('http://wx.yxm360.com/Home/Vote/voteindex'));
            exit;
        }else {
             if ($_GET['openid'] == null || $_GET['openid'] == '') exit;
             $datass = json_decode($_COOKIE["openid_contents"], true);
             $count = Db::table("s_vote_recored_user")->where(array("yxmopenid" => $_GET['openid']))->select();
             if (count($count)<=0) {
                 if($datass["subscribe"]==0||$datass["subscribe"]==null||$datass["subscribe"]=="0") {
                     exit;
                 }
                 Db::table("s_vote_recored_user")->insert(array(
                     "yxmopenid" => $_GET['openid'],
                     "subscribe" => $datass["subscribe"],
                     "nickname" => $datass["nickname"],
                     "sex" => $datass["sex"],
                     "language" => $datass["language"],
                     "city" => $datass["city"],
                     "province" => $datass["province"],
                     "country" => $datass["country"],
                     "headimgurl" => $datass["headimgurl"]));
             }else{
                 if($count[0]["subscribe"]==1||$count[0]["subscribe"]=="1"){
                     $this->openid = $_GET['openid'];
                     return $this->fetch();
                 }else{
                     if($datass["subscribe"]==0||$datass["subscribe"]==null||$datass["subscribe"]=="0") {
                         exit;
                     }
                 }
             }
        }
    }

    public function draw()
    {
    	$this->openid=Input('get.openid');
        return $this->fetch();
    }
        
    public  function  my_prize()
    {
    	$this->openid=Input('get.openid');
        return $this->fetch();
    }

    public function rank()
    {
    	$this->openid=Input('get.openid');
        return $this->fetch();
    }
    
    public function explain()
    {
        $this->openid=Input('get.openid');
        $this->title="体验卡使用说明";
        return $this->fetch();
    }

}
