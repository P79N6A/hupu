<?php/***********V网夹逻辑************ */namespace app\api\logic;use think\Model;use app\api\model\UserInfo  as userModel;use app\api\model\MsgList  as msgModel;use app\api\model\Cards  as cardsModel;use app\api\model\Member  as memberModel;use app\api\model\CardsGroup  as groupModel;use app\api\model\Collection  as collectModel;use app\api\model\VisitLog  as visitModel;use app\api\model\CardContent  as contentModel;class Cards extends Model{    private $User=null;    private $Msg=null;    private $Cards=null;    private $CardsGroup=null;    private $Member=null;    private $Collection=null;    private $VisitLog=null;    private $CardContent=null;    function __construct()    {        $this->User= new userModel();        $this->Msg= new msgModel();        $this->Cards=new cardsModel();        $this->Msg= new msgModel();        $this->Member= new memberModel();        $this->CardsGroup=new groupModel();        $this->Collection=new collectModel();        $this->VisitLog= new visitModel();        $this->CardContent= new contentModel();    }    public function saveCardContent($options=array())    {        //编辑V网正文        $this->CardContent->create($options);        $is=$this->CardContent->update();        return $is;    }    public function addCardContent($options=array())    {        //添加V网正文        $this->CardContent->create($options);        $id=$this->CardContent->insert();        return $id;    }    public function delCardContent($options=array())    {        //删除V网正文        $this->CardContent->create($options);        $is=$this->CardContent->delete();        return $is;    }    public function getCardContent($user_id)    {        //获取V网正文        $where['user_id'] = $user_id;        $order = "sort asc,add_time desc";        $data = $this->CardContent->where($where)->order($order)->select();        return $data;    }    public function addCollection($user_id=0,$object_id=0,$visit_id=0)    {        //加入收藏        $data = array();        $data['object_id'] = $object_id;        $data['user_id'] = $user_id;        $data['add_time'] = time();        if($visit_id!=0) {            $this->VisitLog->where(array('user_id'=>$user_id,'object_id'=>$object_id))->update(array('is_follow'=>1));//浏览记录更新关注记录        }        $result = $this->Collection->insert($data);        return $result;    }    public function getCollectionData($user_id)    {        //获取收藏数据列表        $data=array();        $data['data'] = Db::table('s_collection')->alias('c')->leftJoin(' s_user_detail u','u.id = c.object_id')            ->field('u.headimg,u.nick_name,u.id,u.unionid,c.name,c.add_time')            ->where(array('c.user_id'=>$user_id))->order('c.id desc')->select();        $data['count']=count($data['data']);        foreach ($data['data'] as $key => $value) {            if(substr($value['headimg'],0,4) != 'http')                $data['data'][$key]['headimg'] = 'https://wap.yxm360.com'.$value['headimg'];            if($value['name']){                $data['data'][$key]['nick_name']=$value['name'];            }        }        return $data;    }    public function operateCards($options=array())    {        //申请V网操作        $res=$this->Msg->where(array('id'=>$options['id']))->find();        if($res){            if($options['status']==1){                //通过的操作                $data=array();                $user=$this->User->where(array('id'=>$res['object_id']))->find();                $nick_name = $user['nick_name'];                $data['object_id'] = $res['object_id'];                $data['realname'] = $nick_name;                $phone = $this->Member->where(array('id'=>$user['member_id']))->column('phone');				$data['phone'] = $phone;                $data['user_id'] = $res['user_id'];                $data['type']=1;                                $this->addCards($data);                $data=array();                $user=$this->User->where(array('id'=>$res['user_id']))->find();                $nickname = $user['nick_name'];                $data['object_id'] = $res['user_id'];                $data['realname'] = $nickname;                $friphone = $this->Member->where(array('id'=>$user['member_id']))->column('phone');				$data['phone'] = $friphone;                $data['user_id'] = $res['object_id'];                $data['type']=1;                $this->addCards($data);                $this->addCollection($res['object_id'],$res['user_id']);                //添加环信好友				$url = 'http://'.$_SERVER['HTTP_HOST'].'/index.php?s=/Api/Yun163/handleApply';                https_request($url,array('uname'=>$phone,'friend'=>$friphone,'nick_name'=>$nick_name,'nickname'=>$nickname));            }                        return $this->Msg->where(array('id'=>$options['id']))->update(array('status'=>$options['status']));        }else{            return $this->Msg->where(array('id'=>$options['id']))->update(array('status'=>$options['status']));        }        return false;    }    public function applyCards($options=array())    {        //申请交换V网        $data=array();        $id=0;        $res=$this->Msg->where(array('user_id'=>$options['user_id'],'object_id'=>$options['object_id'],'type'=>1))->find();        if(!$res){            $data['user_id']=$options['user_id'];//申请人            $data['object_id']=$options['object_id'];//申请对象id            $data['type']=1;//申请对象id            $data['msg']=$options['msg'];            $data['add_time']=time();            $data['status']=2;            $id=$this->Msg->insert($data);        }        return $id;    }    public function addCards($options=array())    {        //添加V网 type 1=线上收藏 2拍照收藏        $id=0;        switch ($options['type']) {            case 1:                $data=array();                $data['object_id']=$options['object_id'];                $data['realname']=$options['realname'];                $data['phone']=$options['phone'];                $data['user_id']=$options['user_id'];                $data['add_time']=time();                $data['type']=1;                $id=$this->Cards->insert($data);                break;            case 2:                $data=array();                $data['object_id']=0;                $data['realname']=$options['realname'];                $data['phone']=$options['telephone'];                $data['user_id']=$options['user_id'];                $options['data']=array(                    'thumb'=>$options['thumb'],//V网图                    'company'=>$options['company'],//公司                    'position'=>$options['position'],//职务                    'email'=>$options['email'],//邮箱                    'wxnum'=>$options['wxnum'],//微信                    'qq'=>$options['qq'],//QQ                    'telephone'=>$options['telephone'],//电话（小程序是手机号）                    'site'=>$options['site'],//网址                    'address'=>$options['address'],//地址                    'fax'=>$options['fax'],//传真（小程序）                    'tel'=>$options['tel'],//电话（小程序                    'zipcode'=>$options['zipcode'],//邮政编码（小程序）                );                $data['data']=json_encode($options['data']);                $data['type']=2;                $data['add_time']=time();                $id=$this->Cards->insert($data);                break;            default:                # code...                break;        }        return $id;    }    public function delCards($options=array())    {        //删除V网        $where['id']=array('in',$options['id']);        $id=$this->Cards->where($where)->delete();        return $id;    }    public function addCardsGroup($options=array())    {        //添加V网分组        $data=array();        $data['user_id']=$options['user_id'];        $data['name']=$options['name'];        $data['add_time']=time();        $id=$this->CardsGroup->insert($data);        return $id;    }    public function delCardsGroup($options=array())    {        //删除V网分组        $where['id']=$options['group_id'];        $id=$this->CardsGroup->where($where)->delete();        $this->Cards->where(array('group_id'=>$where['id']))->update(array('group_id'=>0));        return $id;    }    public function setCardsGroup($options=array())    {        //添加V网分组        $where['id']=array('in',$options['ids']);        $id=$this->Cards->where($where)->update(array('group_id'=>$options['group_id']));        return $id;    }    public function getCards($options=array())    {        //获取V网列表        if(!empty($options['type'])){            $where['type']=$options['type'];        }        if(!empty($options['group_id'])){            $where['group_id']=$options['group_id'];        }        if(!empty($options['keyword'])){            $where['_complex'] = array(                '_logic' => 'or',                'phone' => array('like',"%{$options['keyword']}%"),                'realname' => array('like',"%{$options['keyword']}%"),            );        }        $where['user_id']=$options['user_id'];        $data=$this->Cards->where($where)->relation(true)->select();        $list=array();        foreach ($data as $key => $value) {            $value['data'] = $value['data'] ? json_decode($value['data']) : '';            $value['data']->thumb_fullurl = ToOpenWxImage($value['data']->thumb);            if (!empty($value['user_info']))            {                $value['user_info']['background_img']=ToOpenWxImage($value['user_info']['background_img']);                $value['user_info']['shareimg']=ToOpenWxImage($value['user_info']['shareimg']);                $value['user_info']['qrcodeimg']=ToOpenWxImage($value['user_info']['qrcodeimg']);                $value['user_info']['headimg']=ToOpenWxImage($value['user_info']['headimg']);                $value['user_info']['wx_ewm_url']=ToOpenWxImage($value['user_info']['wx_ewm_url']);            }else{                $value['user_info']['headimg']=ToOpenWxImage('/Uploads/User/b.png');            }            $data[$key]['pin']=$this->getFirstCharter($value['realname']);//首字母            $list[$data[$key]['pin']][]=$value;        }        ksort($list);        return $list;    }    /**     * @param array $options     * @return array     * 用于APP端请求     */    public function getCardsForAPP($options=array())    {        //获取V网列表        if(!empty($options['type'])){            $where['type']=$options['type'];        }        if(!empty($options['group_id'])){            $where['group_id']=$options['group_id'];        }        if(!empty($options['keyword'])){            $where['_complex'] = array(                '_logic' => 'or',                'phone' => array('like',"%{$options['keyword']}%"),                'realname' => array('like',"%{$options['keyword']}%"),            );        }                $where['c.user_id']=$options['user_id'];        $data = Db::table('s_cards')->alias('c')->leftJoin(' s_user_detail u','u.id = c.object_id')            ->field('c.id,c.realname,c.phone,u.headimg,u.unionid')->where($where)->select();        return $data;    }    public function getCardsInfo($options=array())    {        //获取V网详情        $where['id']=$options['id'];        $info=$this->Cards->relation(true)->where($where)->find();        if($info['data']) {            $res = json_decode($info['data'], true);            unset($info['data']);            $data = array_merge($info, $res);        }else{            $data = $info;        }        return $data;    }    public function getCardsList($options=array())    {        //获取V网列表 status=1通过 2申请中 3不通过        if(!empty($options['object_id']) && !empty($options['user_id']))        {            $where['_complex'] = array(                '_logic' => 'or',                'object_id' => $options['object_id'],                'user_id' => $options['user_id'],            );        }else if(!empty($options['object_id'])){            $where['object_id']=$options['object_id'];        }else if(!empty($options['user_id'])){            $where['user_id']=$options['user_id'];        }                $where['type']=1;        $where['status']=empty($options['status'])?2:$options['status'];        $list=$this->Msg->where($where)->relation(true)->order('id desc')->select();        return $list;    }    public function getCardsList_xcx($options=array(),$limit='')    {        //获取V网列表 status=1通过 2申请中 3不通过        if(!empty($options['object_id']) && !empty($options['user_id'])){            $where['_complex'] = array(                '_logic' => 'or',                'object_id' => $options['object_id'],                'user_id' => $options['user_id'],            );        }else if(!empty($options['object_id'])){            $where['object_id']=$options['object_id'];        }else if(!empty($options['user_id'])){            $where['user_id']=$options['user_id'];        }        $where['type']=1;        $where['status']=empty($options['status'])?2:$options['status'];        $list=$this->Msg->where($where)->order('add_time desc')->limit($limit)->relation(true)->select();        foreach ($list as $key=>$value)        {            $list[$key]['add_time_xcx']=date('Y-m-d H:i:s',$list[$key]['add_time']);        }        return $list;    }    public function getApplyInfo($id)    {        return $this->Msg->where(array('id'=>$id))->relation(true)->find();    }    public function editCards($options=array())    {        //编辑V网        $data=array();        $where['id']=$options['id'];        $data['realname']=$options['realname'];        $data['phone']=$options['phone'];        $options['data']=array(            'thumb'=>$options['thumb'],//V网图            'company'=>$options['company'],//公司            'position'=>$options['position'],//职务            'email'=>$options['email'],//邮箱            'wxnum'=>$options['wxnum'],//微信            'qq'=>$options['qq'],//QQ            'telephone'=>$options['telephone'],//电话            'site'=>$options['site'],//网址            'address'=>$options['address'],//地址        );        $data['data']=json_encode($options['data']);        $id=$this->Cards->where($where)->update($data);        return $id;    }    public function getCardsGroup($options=array())    {        //获取V网分组列表        $where['user_id']=$options['user_id'];        $data=$this->CardsGroup->where($where)->select();        return $data;    }    //php获取中文字符拼音首字母    public function getFirstCharter($str)    {        if(empty($str)){return '';}        $fchar=ord($str{0});        if($fchar>=ord('A')&&$fchar<=ord('z')) return strtoupper($str{0});        $s1=iconv('UTF-8','gb2312',$str);        $s2=iconv('gb2312','UTF-8',$s1);        $s=$s2==$str?$s1:$str;        $asc=ord($s{0})*256+ord($s{1})-65536;        if($asc>=-20319&&$asc<=-20284) return 'A';        if($asc>=-20283&&$asc<=-19776) return 'B';        if($asc>=-19775&&$asc<=-19219) return 'C';        if($asc>=-19218&&$asc<=-18711) return 'D';        if($asc>=-18710&&$asc<=-18527) return 'E';        if($asc>=-18526&&$asc<=-18240) return 'F';        if($asc>=-18239&&$asc<=-17923) return 'G';        if($asc>=-17922&&$asc<=-17418) return 'H';        if($asc>=-17417&&$asc<=-16475) return 'J';        if($asc>=-16474&&$asc<=-16213) return 'K';        if($asc>=-16212&&$asc<=-15641) return 'L';        if($asc>=-15640&&$asc<=-15166) return 'M';        if($asc>=-15165&&$asc<=-14923) return 'N';        if($asc>=-14922&&$asc<=-14915) return 'O';        if($asc>=-14914&&$asc<=-14631) return 'P';        if($asc>=-14630&&$asc<=-14150) return 'Q';        if($asc>=-14149&&$asc<=-14091) return 'R';        if($asc>=-14090&&$asc<=-13319) return 'S';        if($asc>=-13318&&$asc<=-12839) return 'T';        if($asc>=-12838&&$asc<=-12557) return 'W';        if($asc>=-12556&&$asc<=-11848) return 'X';        if($asc>=-11847&&$asc<=-11056) return 'Y';        if($asc>=-11055&&$asc<=-10247) return 'Z';        return 'Special';    }}