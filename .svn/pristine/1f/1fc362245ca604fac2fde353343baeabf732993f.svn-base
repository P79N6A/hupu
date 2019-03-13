<?php
/** 贺卡管理 */
namespace app\home\controller;
use app\common\controller\HomeBase;
use app\api\logic\Gcard  as gcardLogic;
use app\api\logic\GcardType  as cardTypeLogic;
use think\Db;

class Greeting extends HomeBase
{

	private $gcard_logic = null;
	 
 	function initialize()
 	{
 		parent::initialize();
        $this->gcard_logic = new gcardLogic();
    }
    /**
     * 贺卡
     */
    public function index()
    {
        $this->title="节日贺卡";
        $this->Type=$this->gcard_logic->getHomeGcard();
        $data['user_id']=$this->userInfo['id'];
        $data['is_home']="1";
        $this->Gcard=$this->gcard_logic->getUsersGcard($data);
        return $this->fetch();
    }

    /**
     *  我的贺卡
     */
    public function mine()
    {
        $data['user_id']=$this->userInfo['id'];
        $this->Gcard=$this->gcard_logic->getUsersGcard($data);
        return $this->fetch();
    }

    /**
     *  贺卡模板
     */
    public function templet()
    {
        $id=Input("get.id");
        $data['gcard_type_id']=$id;
        $this->type=Db::table('s_gcard_type')->where('id='.$id)->column('name');
        $this->data=$this->gcard_logic->getGCardList($data);
        return $this->fetch();
    }

    /**
     *  贺卡模板分类
     */
    public function templetCategory()
    {
        return $this->fetch();
    }
    
    public function delGcard()
    {    
        if(isPOST){
            $res=Input('post.');
            $data['user_id']=$this->userInfo['id'];
            $data['id']=$res['id'];
            $this->gcard_logic->delUsersGcard($data);
        }          
    }
    
    /**
     *  贺卡模板预览
     */
    public function templetPreview()
    {
        // 注意 URL 一定要动态获取，不能 hardcode.
        $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
        $url = "$protocol$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
        $this->assign('share_url', $url);
        $id=Input("get.id");
        $data = $this->gcard_logic->getGCardInfo(array('id'=>$id));
        $this->data=$data;
        if(substr($data['pic'],0,4) == 'http'){
            $imgUrl = $data['pic'];
        }else{
            $imgUrl = 'http://'.$_SERVER['HTTP_HOST'].$data['pic'];
        }
        $this->assign('sharetitle',$data['title']);
        $this->assign('desc',str_replace(PHP_EOL, ' ', $data['content']));
        $this->assign('imgUrl',$imgUrl);
        return $this->fetch();
    }

    /**
     *  制作贺卡
     */
    public function createCard()
    {
        $id=Input('get.id');
        if(isPOST){
            $res=Input('post.');
            $data['user_id']=$this->userInfo['id'];
            $data['gcard_id']=$res['gcard_id'];
            $id=$this->gcard_logic->addUsersGcard($data);
            if($id){
                $return['status']=1;
                $return['msg']="success";
                $return['data']['id']=$id;
            }else{
                $return['status']=0;
                $return['msg']="网络错误";
                $return['data']=array();
            }
            return json($return);
            exit;
        }
        $this->data=$this->gcard_logic->getHeka($id);
        $this->assign('userInfo',$this->userInfo);
        return $this->fetch();
    }
    
    public function editUsersGcard()
    {
        if(isPOST){
            $post=Input('post.');
            switch ($post['do']) {
                case 'card_word':
                    $jump_url=url('Home/Greeting/createCardStart',array('id'=>$post['id'],'do'=>$post['do']));
                    break;
                case 'cardEditBg':
                    $jump_url=url('Home/Greeting/createCardBackground',array('id'=>$post['id'],'do'=>$post['do']));
                    break;
                case 'music':
                    $jump_url=url('Home/Greeting/createCardMusic',array('id'=>$post['id'],'do'=>$post['do']));
                    break;
                case 'cardWechatShare':
                    $jump_url=url('Home/Greeting/createCardShare',array('id'=>$post['id'],'do'=>$post['do']));
                    break;
                case 'cardPreview':
                    $jump_url=url('Home/Greeting/createCardLayout',array('id'=>$post['id'],'do'=>$post['do']));
                    break;
                default:
                    # code...
                    break;
            }
            if(substr($_SERVER['HTTP_HOST'],0,1) == 'w'){
                $start_name = 'greeting';
            }else{
                $start_name = 'test';
            }
            switch ($post['type']) {
                case 'cardEditBg':
                    if(!empty($_FILES['pic']['name'])){
                        $Upload = new \Admin\Controller\UploadsController();
                        $end_name = trim(strrchr($_FILES['pic']['name'], '.'),'.');
                        if($end_name == 'jpeg'){
                            $save_name = $start_name.'/'.date('Ymd').'/'.time().rand(1,9999).'.jpg';
                        }else{
                            $save_name = $start_name.'/'.date('Ymd').'/'.time().rand(1,9999).'.'.$end_name;
                        }

                        $post['pic'] = $Upload->upload_video_to_aliyun($save_name,$_FILES['pic']);
                    }
                    break;
                case 'cardWechatShare':
                    if(!empty($_FILES['share_thumb']['name'])){
                        $Upload = new \Admin\Controller\UploadsController();
                        $end_name = trim(strrchr($_FILES['share_thumb']['name'], '.'),'.');
                        if($end_name == 'jpeg'){
                            $save_name = $start_name.'/'.date('Ymd').'/'.time().rand(1,9999).'.jpg';
                        }else{
                            $save_name = $start_name.'/'.date('Ymd').'/'.time().rand(1,9999).'.'.$end_name;
                        }

                        $post['share_thumb'] = $Upload->Uploads($save_name,$_FILES['share_thumb']);
                    }
                    break;
                default:
                    # code...
                    break;
            }
            $post['user_id']=$this->userInfo['id'];
            unset($post['do']);
            
            
            $this->gcard_logic->editUsersGcard($post);
            if(empty($post['jump_url'])){
                header("Location:".$jump_url);
            }else{
                $jump_url=$post['jump_url'];
                header("Location:".$jump_url);
            }
            exit;
        }
    }
    
    /**
     *  制作贺卡 开始
     */
    public function createCardStart()
    {
        $get=Input('get.');
        $res['type']=$get['do'];
        $res['id']=$get['id'];
        $res['user_id']=$this->userInfo['id'];
        $data=$this->gcard_logic->getUsersGcardInfo($res);
        $this->data=$data;
        $this->content=$data['gcard_textList'];
        return $this->fetch();
    }

    /**
     *  制作贺卡 背景
     */
    public function createCardBackground()
    {
        $get=Input('get.');
        $res['type']=$get['do'];
        $res['id']=$get['id'];
        $res['user_id']=$this->userInfo['id'];
        $data=$this->gcard_logic->getUsersGcardInfo($res);

        $this->data=$data;
        return $this->fetch();
    }

    /**
     *  制作贺卡 音乐
     */
    public function createCardMusic()
    {
        $get=Input('get.');
        $res['type']=$get['do'];
        $res['id']=$get['id'];
        $data=$this->gcard_logic->getUsersGcardInfo($res);
        $data['music_name'] = Db::table('s_music')->where(array("id"=>$data['music_id']))->column('name');
        $this->music = Db::table('s_music')->select();
        $this->data=$data;
        return $this->fetch();
    }

    /**
     *  制作贺卡 分享
     */
    public function createCardShare()
    {
        $get=Input('get.');
        $res['type']=$get['do'];
        $res['id']=$get['id'];
        $res['user_id']=$this->userInfo['id'];
        $data=$this->gcard_logic->getUsersGcardInfo($res);

        $this->data=$data;
        return $this->fetch();
    }

    /**
     *  制作贺卡 布局
     */
    public function createCardLayout()
    {
        $get=Input('get.');
        $res['type']=$get['do'];
        $res['id']=$get['id'];
        $res['user_id']=$this->userInfo['id'];
        $data=$this->gcard_logic->getUsersGcardInfo($res);
        $this->data=$data;
        
        return $this->fetch();
    }

    // 新版4.0贺卡首页
    public function greet_index()
    {
         $this->id=$this->userInfo['id'];
         return $this->fetch();
    }

    // 新版4.0贺卡贺卡编辑页
    public function greet_edit()
    {
         return $this->fetch();
    }

    	// 选择音乐
	public function greet_music()
	{
		return $this->fetch();
    }
    
    	// 设置分享
	public function greet_share()
	{
		return $this->fetch();
	}
}