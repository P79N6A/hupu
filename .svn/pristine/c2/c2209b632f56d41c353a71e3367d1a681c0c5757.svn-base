<?php
/** V网夹管理 */
namespace app\home\controller;
use app\common\controller\HomeBase;
use app\api\logic\Cards  as cardsLogic;
use think\Db;

class Vwebclip extends HomeBase{

	private $cards_logic = null;
	 
 	function initialize()
 	{
 		parent::initialize();
        $this->cards_logic = new cardsLogic();
    }
    
    /**
     *  V网夹
     */
    public function index()
    {	
        $where =array();
        if($this->request->isPost()){
            $where['group_id'] = Input('post.v_type');
            $where['type'] = Input('post.v_source');
            $where['keyword'] = Input('post.keyword');
        }
        
        $where['user_id'] = $this->userInfo['id'];
        $this->assign('keyword',Input('post.keyword'));
        $this->assign('myphone',$this->userInfo['phone']);
        
        return  $this->fetch();
    }

    /**
     *  管理V网
     */
    public function regulate()
    {
        return  $this->fetch();
    }

    /**
     *  我的卡包分类
     */
    public function walletCategory()
    {
        $cateList = $this->cards_logic->getCardsGroup(array('user_id'=>$this->userInfo['id']));
        $this->assign('cateList',$cateList);
        return  $this->fetch();
    }

    /**
     *  删除我的卡包分类
     */
    public function delWalletCategory()
    {
        if ( $this->cards_logic->delCardsGroup(array('group_id'=>Input('post.id'))) ) {
            return json(['status' => 1, 'msg' => '删除成功']);
        } else {
            return json(['status' => 0, 'msg' => '删除失败']);
        }
    }

    /**
     *  新增我的卡包分类
     */
    public function addWalletCategory()
    {

        if ( $this->cards_logic->addCardsGroup(array('name'=>Input('post.name'),'user_id'=>$this->userInfo['id'])) ) {
            return json(['status' => 1, 'msg' => '添加成功']);
        } else {
            return json(['status' => 0, 'msg' => '添加失败']);
        }
    }

    /**
     *  删除卡片搜藏
     */
    public function delCard()
    {
        if ( $this->cards_logic->delCards(array('id'=>Input('post.cards'))) ) {
            return json(['status' => 1, 'msg' => '删除成功']);
        } else {
            return json(['status' => 0, 'msg' => '删除失败']);
        }
    }

    /**
     *  设置分组
     */
    public function setGroup()
    {
        if ( $this->cards_logic->setCardsGroup(array('group_id'=>Input('post.cate'),'ids'=>Input('post.cards'))) ) {
            return json(['status' => 1, 'msg' => '设置成功']);
        } else {
            return json(['status' => 0, 'msg' => '设置失败']);
        }
    }

    /**
     *  添加卡片（相片）
     */
    public function photoCard()
    {
        if($this->request->isPost()) {
            // 上传图片
            $post = Input("post.");
            $mediaId = $post['mediaId'];
            $foldername = date("Y-m-d",time());
            $img=$this->getmedia($mediaId,$foldername);
            $this->fillCard($img,false);
            exit;
        }

        // 注意 URL 一定要动态获取，不能 hardcode.
        $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
        $url = "$protocol$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
        $this->assign('share_url', $url);
        return  $this->fetch();
    }

    private function getmedia($media_id,$foldername)
    {
        $wxImg=A("Home/Img",'Event')->getWxImg($media_id);
        if (!file_exists("./Uploads/User_cert/".$foldername)) {
          mkdir("./Uploads/User_cert/".$foldername, 0777, true);
        }
        $targetName = './Uploads/User_cert/'.$foldername.'/'.date('YmdHis').rand(1000,9999).'.jpg';
        file_put_contents($targetName,$wxImg['imgMedia']['mediaBody']);

        return $targetName;
    }

    /**
     *  添加卡片（手填）
     */
    public function fillCard($img = '',$isadd = true)
    {
        if($this->request->isPost() && $isadd){
            $data = $_POST;
            if($data['type']==2){
                $data['id']=$this->userInfo['id'];
                foreach ($data as $key => $value) {
                    if(empty($data[$key])){
                        unset($data[$key]);
                    }
                }
                Db::table('s_user_info')->update($data);
                header("location:".url("Home/Cardwallet"));
                exit;
            }

            $data['user_id'] = $this->userInfo['id'];
            $data['type'] = 2;
            $addId = $this->cards_logic->addCards($data);
            if($addId === false){
                $this->error("保存失败");
            }else{
                header("location:".url("Home/Cardwallet"));
            }
        }

        $message = '';
        $_data = array();

        if($img!=''){

            $res=card_query($img);
            if($res->code != 0){
                $message = "识别名片失败,".$res->desc;
            }else{
                $address = "";
                if (!$data->address[0]->item->locality){
                    $address = $data->address[0]->item->street;
                }else{
                    $address = $data->address[0]->item->locality.$data->address[0]->item->street;
                }

 
                if(isset($data->telephone[0]->item->number)){
                    //手机号码
                    $_data['mobile'] = $data->telephone[0]->item->number;
                }
                if(isset($data->position[0]->item)){
                    //职位/部门
                    $_data['position'] = "";
                }
                if(isset($data->formatted_name[0]->item)){
                    //姓名
                    $_data['nick_name'] = $data->formatted_name[0]->item;
                }
                if(isset($data->telephone[1]->item->number)){
                    //固话
                    $_data['telephone'] = $data->telephone[1]->item->number;
                }
                if(isset($data->telephone[1]->item->number)){
                    //网址
                    $_data['site'] = "";
                }
                if(isset($address)){
                    //地址
                    $_data['address'] = $address;
                }
                if(isset($data->organization[0]->item->name)){
                    //公司名称
                    $_data['company'] = $data->organization[0]->item->name;
                }
                if(isset($data->organization[0]->item->name)){
                    //邮箱
                    $_data['email'] = "";
                }

                if(isset($data->im[0]->item)){
                    $_data['qq'] = substr($data->im[0]->item,3);
                }
            }
        }

        $this->assign('message',$message);
        $this->assign('data',$_data);
        $this->assign('imageUrl',substr($img,1));
        return  $this->fetch('fillCard');
    }

    /**
     *  交换V网
     */
    public function exchange()
    {
    	$this->title="交换V网";
        if(!$_GET['type'])
            $_GET['type'] = 'mine';

        switch($_GET['type']){
            case 'mine':
                $where['object_id'] = $this->userInfo['id'];
                $where['status'] = array('in',array(1,2,3));
                break;
            case 'apply':
                $where['user_id'] = $this->userInfo['id'];
                $where['status'] = array('in',array(1,2,3));
                break;
            case 'success':
                $where['user_id'] = $this->userInfo['id'];
                $where['object_id'] = $this->userInfo['id'];
                $where['status'] = 1;
                break;
        }
        $list = $this->cards_logic->getCardsList($where);
        if($_GET['type'] == 'mine' ||$_GET['type'] == 'success' ){
            foreach($list as $k =>$v){
                if($v['object_id'] == $this->userInfo['id'])
                    $list[$k]['user_info'] = Db::table('s_user_info')->where(array('id'=>$v['user_id']))->find();
            }

        }
        
        $this->assign('result',$list);
        return  $this->fetch();
    }

    /**
     *  交换详情
     */
    public function exchangeInfo()
    {
        $id = Input('get.id');
        $info = $this->cards_logic->getApplyInfo($id);
        $this->assign('result',$info);
        $this->assign('isHandle',$info['user_id']!=$this->userInfo['id'] && $info['status']==2 ?  1 : 0);
        return  $this->fetch();
    }

    /**
     *  处理交换申请
     */
    public function handleApply()
    {
        $id = Input('post.id');
        $status = Input('post.status');

        $where = array(
            'id' => $id,
            'status' => $status,
        );

        if($this->cards_logic->operateCards($where)!==false){
            header("location:".url("Home/Cardwallet/index"));
        }else{
            $this->error('提交失败');
        }
    }

    /**
     *  卡片详情
     */
    public function info()
    {
        if($this->request->isPost()){
            $result = $this->cards_logic->editCards($_POST);
            header("location:".url("Home/Cardwallet/index"));
        }
        $id = Input('get.id');
        $result = $this->cards_logic->getCardsInfo(array('id'=>$id));
        $this->title = '个人详情';
        $this->assign('result',$result);
        return  $this->fetch();
    }
    
	/**
	 * 预览
	 */
	 public function preview()
	 {
        $id = Input('get.id');
        $result = $this->cards_logic->getCardsInfo(array('id'=>$id));
        $this->assign('result',$result);
        return  $this->fetch();
    }
    
    //拍照识别
    public function discern()
    {
        $this->assign('card_id',Input("get.card_id"));
        return  $this->fetch();
    }
    
}
