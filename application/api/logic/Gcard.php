<?php
/*
**********
节日贺卡逻辑层
************
 */
namespace app\api\logic;
use app\admin\model\User;

use think\Model;
use app\api\model\UserInfo  as userModel;
use app\api\model\Gcard  as cardModel;
use app\api\model\GcardType  as gctypeModel;
use app\api\model\Music  as musicModel;
use app\api\model\GcardText  as gtextModel;
use app\api\model\UsersGcard  as ucardModel;
use app\api\model\Member  as memberModel;

class Gcard extends Model
{
    private $User = null;
    private $Gcard = null;
    private $GcardType = null;
    private $Music = null;
    private $GcardText = null;
    private $UsersGcard = null;
	private $Member = null;
    
	function __construct()
    {
        $this->User = new userModel();
        $this->Gcard = new cardModel();
        $this->GcardType = new gctypeModel();
        $this->Music = new musicModel();
        $this->GcardText = new gtextModel();
        $this->UsersGcard = new ucardModel();
        $this->Member = new memberModel();
    }
    
    public function getHeka($id)
    {
        $data = $this->Gcard->where("id=".$id)->find();
        $data['content'] = $this->GcardText->where("gcard_id=".$id)->column('content');
        return $data;
    }
    
    public function getHomeGcard($options=array())
    {
        //获取首页贺卡分类
        $list = $this->GcardType->select();
        foreach ($list as $key => $value) {
            $card=$this->Gcard->where(array('gcard_type_id'=>$value['id']))->limit(1)->select();
            $list[$key]['pic']=$card[0]['pic'];
        }
        
        return $list;
    }
    
    public function getHomeGcard_xcx($options=array())
    {
        //获取首页贺卡分类
        $list = $this->GcardType->select();
        foreach ($list as $key => $value) {
            $card=$this->Gcard->where(array('gcard_type_id'=>$value['id']))->limit(1)->select();
            $list[$key]['pic']=$card[0]['pic'];
            $list[$key]['xcxpic']=ToOpenWxImage($card[0]['pic']);
        }
        
        return $list;
    }
    
    public function getGcardType($options=array())
    {
        //获取贺卡分类列表
        $list = $this->GcardType->select();
        return $list;
    }
    
    public function getGCardList($options=array())
    {
        //获取贺卡列表
        $limit="";
        $where=array();
        if(isset($options['gcard_type_id'])){
            $where['gcard_type_id']=$options['gcard_type_id'];
        }
        if(isset($options['is_home'])){
            $limit="0,3";
        }
        $list=$this->Gcard->where($where)->limit($limit)->select();
        
        return $list;
    }
    
    public function getGCardList_xcx($options=array())
    {
        //获取贺卡列表
        // 获取到了要访问的页面以及页面大小，下面开始分页
        $begin_position = (intval($options['current_page_number'])-1)*intval($options['page_size']);
        $limit=$begin_position.",".$options['page_size'];
        $where=array();
        if(isset($options['gcard_type_id'])){
            $where['gcard_type_id']=$options['gcard_type_id'];
        }
        if(isset($options['keys'])){
            $where['title']=array('like','%'.$options['keys'].'%');
        }

        $list=$this->Gcard->where($where)->limit($limit)->select();
        foreach ($list as $key => $value) {
            $list[$key]['pic']=ToOpenWxImage($list[$key]['pic']);
            $list[$key]['add_time_formated']=date("Y-m-d H:i:s",$list[$key]['add_time']);
        }

        return $list;
    }
    
    public function getGCardInfo($options=array())
    {
        //获取贺卡详情
        $limit="";
        $where['id']=$options['id'];
        $data=$this->UsersGcard->where($where)->find();
        if($data){
            $music=$this->Music->where(array('id'=>$data['music_id']))->find();
            $data['music_url']=!empty($music['path'])?$music['path']:$music['url'];
            $data['user']=$this->User->field("id,nick_name,headimg")->where(array('id'=>$data['user_id']))->find();
        }
        
        return $data;
    }
    
    public function getUsersGcard($options=array())
    {
        //获取用户贺卡列表
        $limit="";
        if(isset($options['gcard_type_id'])){
            $where['gcard_type_id']=$options['gcard_type_id'];
        }
        $where['user_id']=$options['user_id'];
        if(isset($options['is_home'])){
            $limit="0,3";
        }
        $list = $this->UsersGcard->where($where)->limit($limit)->select();
        return $list;
    }
    
    public function addUsersGcard($options=array())
    {
        //创建用户贺卡
        $data=array();
        $gcard=$this->Gcard->where(array("id"=>$options['gcard_id']))->find();
        $user=$this->User->where(array('id'=>$options['user_id']))->find();
        $phone=$this->Member->where(array('id'=>$user['member_id']))->column('phone');
        $data['title']="亲爱的朋友";
        $data['phone']=$phone;
        $data['content']=$this->GcardText->where(array('gcard_id'=>$options['gcard_id']))->column('content');
        $data['music_id']=$gcard['music_id'];
        $data['font_color']="#fff";
        $data['font_border_color']="#f90000";
        $data['share_title']="来自".$phone."的贺卡";
        $data['share_content']=$data['content'];
        $data['share_thumb']=$gcard['pic'];
        $data['pic']=$gcard['pic'];
        $data['content_y']=0;
        $data['gcard_id']=$options['gcard_id'];
        $data['user_id']=$options['user_id'];
        $data['add_time']=time();
        $id=$this->UsersGcard->insert($data);
        return $id;
    }
    
    public function addUsersGcard_xcx($options=array())
    {
        //创建用户贺卡
        $data=array();
        $gcard=$this->Gcard->where(array("id"=>$options['templateId']))->find();
        $user=$options['userInfo'];
        $phone=$this->Member->where(array('id'=>$user['member_id']))->column('phone');
        $data['title']="亲爱的".$options['gives'];
        $data['phone']=$phone;
        $data['content']=$options['blessings'];
        $data['music_id']=$options['audio'];
        $data['font_color']="#fff";
        $data['font_border_color']="#f90000";
        $data['share_title']="来自".$options['sends']."的贺卡";
        $data['share_content']=$options['blessings'];
        $data['share_thumb']=$options['header'];
        $data['pic']=$gcard['pic'];
        $data['content_y']=0;
        $data['gcard_id']=$options['templateId'];
        $data['user_id']=$user['id'];
        $data['add_time']=time();
        $data['id']=$options['id'];
        if(empty($options['id']))
        {
            $id=$this->UsersGcard->insert($data);
        }else{
            $id=$this->UsersGcard->update($data);
        }

        return $id;
    }
    
    public function editUsersGcard($options=array())
    {
        //编辑用户贺卡
        $data=array();
        $res=$this->UsersGcard->where(array('id'=>$options['id'],'user_id'=>$options['user_id']))->find();
        switch ($options['type']) {
            case 'card_word':
                $data['title']=$options['title'];
                $data['phone']=$options['phone'];
                $data['content']=$options['content'];
                
                break;
            case 'cardEditBg':
                $data['font_color']=$options['font_color'];
                $data['font_border_color']=$options['font_border_color'];
                $data['pic']=$options['pic'];
                break;
            case 'music':
                $data['music_id']=$options['music_id'];
                break;
            case 'cardWechatShare':
                $data['share_title']=$options['share_title'];
                $data['share_content']=$options['share_content'];
                $data['share_thumb']=$options['share_thumb'];
                break;
            case 'cardPreview':
                $data['content_y']=$options['content_y'];
                break;
            default:
                $data=$options;
                break;
            
        }
        
        $this->UsersGcard->where(array('id'=>$options['id'],'user_id'=>$options['user_id']))->update($data);
    }
    
    public function getUsersGcardInfo($options=array())
    {
        //获取用户贺卡内容
        $data=array();
        $field=array();
        $field[]="gcard_id";
        switch ($options['type']) {
            case 'card_word':
                $field[]="title";
                $field[]="phone";
                $field[]="content";
                
                break;
            case 'cardEditBg':
                $field[]="font_color";
                $field[]="pic";
                $field[]="font_border_color";
                break;
            case 'music':
                $field[]="music_id";
                break;
            case 'cardWechatShare':
                $field[]="share_title";
                $field[]="share_content";
                $field[]="share_thumb";
                break;
            case 'cardPreview':
                $field[]="content_y";
                $field[]="title";
                $field[]="phone";
                $field[]="content";
                $field[]="font_color";
                $field[]="pic";
                $field[]="font_border_color";
                break;
            default:
                break;
            
        }
        $data=$this->UsersGcard->where(array('id'=>$options['id'],'user_id'=>$options['user_id']))->field($field)->find();
        if($options['type']=="card_word")$data['gcard_textList']=$this->GcardText->where(array('gcard_id'=>$data['gcard_id']))->select();

        return $data;
    }

    /**javatom
     * @param array $options
     * @return usersGcard obj
     */
    public function getMyGcardList_xcx($options=array())
    {
        //当前页
        $thispage = intval($options['page']);
        if($thispage<=0) {
            $thispage=1;
        }
        //定死查询数
        $page_size=10;
        //拼接字符
        $limit=(($thispage-1)*$page_size).",".$page_size;
        $data = $this->UsersGcard->field('id,pic,title,user_id')->where(array('user_id'=>$options['userInfo']['id']))->limit($limit)->select();
        foreach ($data as $key=>$value)
        {
            $data[$key]['pic_url']=ToOpenWxImage($data[$key]['pic']);
            $data[$key]['share_thumb_url']=ToOpenWxImage($data[$key]['share_thumb']);
            $data[$key]['add_time_formated']=date("Y-m-d H:i:s",$data[$key]['add_time']);
        }
        
        return $data;
    }


    public function getUsersGcardInfoList_xcx($options=array())
    {
        //获取用户贺卡内容
        $data = $this->UsersGcard->where(array('user_id'=>$options['userInfo']['id']))->select();
        foreach ($data as $key=>$value)
        {
            $data[$key]['pic_url']=ToOpenWxImage($data[$key]['pic']);
            $data[$key]['share_thumb_url']=ToOpenWxImage($data[$key]['share_thumb']);
            $data[$key]['add_time_formated']=date("Y-m-d H:i:s",$data[$key]['add_time']);
        }
        return $data;
    }

    public function delUsersGcard($options=array())
    {
        //删除用户贺卡
        return $this->UsersGcard->where(array('id'=>$options['id'],'user_id'=>$options['user_id']))->delete();
    }
    
    public function getOneUsersGcard($options=array())
    {
        $where['id']=$options['id'];
        $where['user_id']=$options['userInfo']['id'];
        $data = $this->UsersGcard->where(array('id'=>$where['id'],'user_id'=>$where['user_id']))->find();
        if($data){
            $music=$this->Music->where(array('id'=>$data['music_id']))->find();
            $data['music_url']=!empty($music['path'])?$music['path']:$music['url'];
            if(!empty($data['music_url']))
            {
                $data['music_url']=ToOpenWxImage($data['music_url']);
            }
            $data['user']=$this->User->field("id,nick_name,headimg")->where(array('id'=>$data['user_id']))->find();
            if($data['user']){
                $data['user']['headimg']=ToOpenWxImage($data['user']['headimg']);
            }
            $data['add_time']=date('Y-m-d H:i:s',$data['add_time']);
            $data['pic']=ToOpenWxImage($data['pic']);
        }
        
        return $data;
    }

    public function oneGreetingCard_xcx($options=array())
    {
        $where['id']=$options['id'];
        $data=$this->UsersGcard->where(array('id'=>$where['id']))->find();
        if($data){
            $music=$this->Music->where(array('id'=>$data['music_id']))->find();
            $data['music_url']=!empty($music['path'])?$music['path']:$music['url'];
            if(!empty($data['music_url']))
            {
                $data['music_url']=ToOpenWxImage($data['music_url']);
            }
            $data['user']=$this->User->field("id,nick_name,headimg")->where(array('id'=>$data['user_id']))->find();
            if($data['user']){
                $data['user']['headimg']=ToOpenWxImage($data['user']['headimg']);
            }
            $data['add_time']=date('Y-m-d H:i:s',$data['add_time']);
            $data['pic']=ToOpenWxImage($data['pic']);
        }
        
        return $data;
    }

    public function getMusicList($options=array())
    {
        //删除用户贺卡
        $data=$this->Music->select();
        if ($data) {
            foreach ($data as  $key=>$value)
            {
                $data[$key]['music_url']=!empty($data[$key]['path'])?$data[$key]['path']:$data[$key]['url'];
                if(!empty($data[$key]['music_url']))
                {
                    $data[$key]['music_url']=ToOpenWxImage($data[$key]['music_url']);
                }
                $data[$key]['add_time']=date('Y-m-d H:i:s',$data[$key]['add_time']);
            }
        }
        return $data;
    }

}