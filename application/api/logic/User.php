<?php
/*
**********
用户表逻辑层
************
* */
namespace app\api\logic;
use think\Db;
use think\Model;
use think\facade\Cache;
use app\api\model\UserInfo  as userModel;
use app\api\model\MsgList  as MsgModel;
use app\api\model\Blog  as bogModel;
use app\api\model\Member  as memberModel;
use app\api\model\VipGroup  as groupModel;
use app\api\model\VipList  as vipModel;
use app\api\model\VipOrder  as orderModel;
use app\api\model\UsersNav  as usersNavModel;
use app\api\model\Music  as musicModel;
use app\api\logic\UsersFans  as fansLogic;
use app\api\logic\Member  as memberLogic;
use app\api\logic\VisitLog  as visitLogic;
use app\api\model\CardContent  as cntModel;
use app\api\model\AccountRelate  as accountModel;
use app\common\event\imgUpload  as imgModel;

class User extends Model
{
	private $User = null;
	private $Member = null;
	private $FansLogic = null;
	private $Vip = null;
	private $Order = null;
	private $VipGroup = null;
    private $MemberLogic = null;
    private $Blog = null;
    private $Music = null;
    private $UsersNav = null;
    private $VisitLogLogic = null;
	private $CardContent = null;
	private $Area = null;
	private $UserRelate = null;

    function __construct()
    {
        $this->User = new userModel();
        $this->Member = new memberModel();
        $this->FansLogic = new fansLogic();
        $this->VipGroup = new groupModel();
        $this->Vip = new vipModel();
        $this->Order = new orderModel();
        $this->Blog = new bogModel();
        $this->Music = new musicModel();
        $this->UsersNav = new usersNavModel();
        $this->MemberLogic = new memberLogic();
        $this->VisitLogLogic = new visitLogic();
        $this->CardContent = new cntModel();
        $this->UserRelate = new accountModel();

        // 这是什么意思？
        $Cache = Cache::get('area');  //设置缓存标示
        $CacheCityTree = Cache::get('AreaTree');

        // 判断是否有这个查询缓存
        if(!$Cache||!$CacheCityTree){
            //$Cache 中是缓存的标示(每个查询都对应一个缓存 即 不同的查询有不同的缓存)
            $Cache = Db::table('s_region')->Cache('area',86400)->select();
            $province = Db::table('s_region')->field('region_id,region_name,level,parent_id')->where(array('level'=>1))->select();
            foreach ($province as $pkey=>$pvalue) {
                $citys = Db::table('s_region')->field('region_id,region_name,level,parent_id')->where(array('level'=>2))->where(['parent_id'=>$pvalue['region_id']])->select();
                $province[$pkey]['ChildList']=$citys;
                foreach ($citys as $cid=>$cvalue) {
                    $province[$pkey]['ChildList'][$cid]['ChildList'] = Db::table('s_region')->field('region_id,region_name,level,parent_id')->where(array('level'=>3))->where(['parent_id'=>$province[$pkey]['ChildList'][$cid]['region_id']])->select();
                }
            }

            Cache::set('AreaTree',$province,86400);
        }

        $this->Area=$Cache;
    }

    public function changeRec($data=array())
    {
    	//更改推荐人
        //更改用户信息表 TODO::增加事物处理
       $result= Db::table('s_user_detail')->where(array('id'=>$data['object_id']))->update(array('rec_user_id'=>$data['user_id']));
       if($result!==false)
       {
           $this->FansLogic->changeRec($data);//新推荐人添加粉丝
       }
    }


    public function getUserData($user_id,$object_id = 0,$style = 0 )
    {
        $data = array();
        $data['object_id'] = $user_id;
        if($object_id!=0){
            $data['user_id'] = $object_id;
        }else{
             $data['ip'] = get_client_ip();
        }
        $visit_id =$this->VisitLogLogic->addVisitLog($data);//历史浏览记录
        //获取我的V网数据
        $field=array(
            'u.id',
            'u.style',//V网风格
            'u.headimg',//头像
            'u.background_img',//背景图
            'u.nick_name',//昵称
            'u.position',//职位
            'u.company',//公司名称
            'u.industry_tag',//行业标签
            'u.mobile',//手机号
            'u.telephone',//固话
            'u.wxnum',//微信号
            'u.qq',//QQ号
            'u.email',//电子邮箱
            'u.site',//个人网址
            'u.address',//地址
            'u.music_id',//音乐id
            'u.auto_music',//是否自动播放
            'u.is_nickname',//是否显示姓名
            'u.is_phone',//是否显示手机号
            'u.is_tel',//是否显示固话
            'u.is_email',//是否显示电子邮箱
            'u.is_qq',//是否显示qq
            'u.is_wechat',//是否显示微信
            'u.sq_is_search',//商圈是否显示
            'u.is_copy_cards',//是否被复制
            'u.wx_ewm_url',//微信二维码链接
            'u.text_color',//正文颜色
            'u.background_color',//背景颜色
            'u.title_color',//标题颜色
            'u.link_color',//链接颜色
            'u.vip_group_id',
            'u.add_time',
	        'u.province_id',
	        'u.city_id',
	        'u.district_id',
//            'u.music_id',
	        'u.vip_orver_time',
            'u.is_map',//是否显示qq
            'u.share_my_introduct',//简历
            'u.sex',//性别
            'u.age_id',//年龄段
            'u.sq_type_id',//行业
            'u.inte',//积分
            'u.member_id',
            'u.unionid',//用户唯一id,调接口时用
            'u.completion',//完善度
            'u.show_list',//展示一切的样式 0 三个一排 1 一个一排
            'm.phone',//注册手机号
            'm.user_no',//用户显示id
            'vg.vip_name as vip_group_name',//用户会员名
            'mc.url as music_url',//音乐url
            'mc.name as music_name',//音乐名

        );

        $user = Db::table('s_user_detail')->alias('u')->leftJoin(' s_member m','m.id = u.member_id')
            ->leftJoin(' s_vip_group vg','vg.id = u.vip_group_id')->leftJoin(' s_music mc','mc.id = u.music_id')
            ->field($field)->where(array('u.id'=>$user_id))->find();

        $user['AddDays']=intval((time()-$data['add_time'])/86400/1000);//转换为天数
        $user['headimg']=ToOpenWxImage($user['headimg']);
        $user['background_img']=ToOpenWxImage($user['background_img']);
        // 微信二维码
        $user['wx_ewm_url']=ToOpenWxImage($user['wx_ewm_url']);
         // 商圈id
        $type_array = explode(",",$user['sq_type_id']);
        $sq_type_array = Db::table('s_sqtype')->where(array('id'=>array('in',$type_array)))->field('id,name')->select();
        $user['home_product'] = Db::table('s_product')->field('id,cover_img,title')->where(array('user_id'=>$user_id,'is_home'=>1))->select();
        $user['sq_type_id'] = $sq_type_array;
        //省市区
        if ($user['province_id']){
            $user['province_name'] = $this->Area[ array_search($user['province_id'],array_column($this->Area,'region_id'))]['region_name'];
        }

        if ($user['city_id'])
            $user['city_name'] =$this->Area[array_search($user['city_id'],array_column($this->Area,'region_id'))]['region_name'];
        if ($user['district_id'])
            $user['district_name'] =$this->Area[array_search($user['district_id'],array_column($this->Area,'region_id'))]['region_name'];
        // 历史浏览记录
        $user['visit_id']=$visit_id;
        $user['UserNavList']=$this->UsersNav->where(array("user_id"=>$user['id']))->order('sort asc')->select();//获取导航列表

        foreach ($user['UserNavList'] as $key=>$value) {
            if (strlen($user['UserNavList'][$key]['name'])>10)
                $user['UserNavList'][$key]['nameTo4'] = chinesesubstr($user['UserNavList'][$key]['name'],0,10).'..';
            else
            	$user['UserNavList'][$key]['nameTo4'] = $user['UserNavList'][$key]['name'];
            	$user['UserNavList'][$key]['icon_url'] = ToOpenWxImage($user['UserNavList'][$key]['icon_url']);
        }
        
        $user['UserContentList'] = $this->CardContent->where(array("user_id"=>$user['id']))->order("sort asc,add_time desc")->select();//获取导航列表
        if($style != 5) {
            foreach ($user['UserContentList'] as $key => $value) {
                $user['UserContentList'][$key]['content'] = htmlspecialchars_decode($value['content']);
                $user['UserContentList'][$key]['ResList'] = Db::table('s_card_content_res')->where(array('card_content_id' => $user['UserContentList'][$key]['id']))->select();
                $contentImgs = PickupImgUrl($user['UserContentList'][$key]['content']);
                foreach ($contentImgs as $item) {
                    $user['UserContentList'][$key]['ResList'][] = array('data_url' => $item, 'type' => 1, 'Id' => 0);
                }
                $contentVideos = PickupVideoUrl($user['UserContentList'][$key]['content']);
                foreach ($contentVideos as $item) {
                    $user['UserContentList'][$key]['ResList'][] = array('data_url' => $item, 'type' => 3, 'Id' => 0);
                }
                $user['UserContentList'][$key]['content'] = strip_tags($user['UserContentList'][$key]['content']);//去除HTML代码
            }
        }
        $userImgList = Db::table('s_user_img')->where(array('user_id'=>$user['id']))->order('sort desc')->select();
        $user['UserImgList'] = $userImgList;

        return $user;
    }

    public function  addUsersNav($options=array())
    {
        //添加用户导航
        $data['name'] = $options['name'];//导航名称
        $data['add_time'] = time();
        $data['user_id'] = $options['user_id'];
        $data['icon_url'] = $options['icon_url']['origin'];
        $data['icon_url_c'] = $options['icon_url']['compress'];
        $data['content'] = $options['content'];
        $data['jump_url'] = $options['jump_url'];
        $data['sort'] = $options['sort'];
        $nav_id = $options['id'];
        $success = true;
        $ResList = $options['ResList'];
       
        if($nav_id)
        {
            $data['id'] = $options['id'];
            $nav_icon_url=Db::table('s_users_nav')->where(array('id'=>$nav_id))->column('icon_url');
            if ($nav_icon_url!=$data['icon_url']) { unlink($nav_icon_url); }
           
            Db::startTrans();
            try 
            {
            	Db::table('s_users_nav')->update($data);
                $ResList_C=Db::table('s_users_nav_res')->where(array('s_users_nav_id'=>$nav_id))->select($data);
                foreach ( $ResList_C as $key=>$value) {
                    $finded = false;
                    foreach ($ResList as $item) {
                        if(array_search($value['id'],$item)) {
                           $finded = true;
                           break;
                        }
                    }
                    if(!$finded)
                    {
                        //删除文件
                        unlink($ResList_C[$key]['data_url']);
                        unlink($ResList_C[$key]['thumb']);
                        //删除纪录
                        Db::table('s_users_nav_res')->where(array('id'=>$value['id']))->delete();
                    }
                }
                if(sizeof($ResList))
                {
                    foreach ($ResList as $item) {
                        if($item['id'])
                        {
                            $item["add_time"]=time();
                            $item['s_users_nav_id']=$nav_id;
                            Db::table('s_users_nav_res')->update($item);
                        }else{
                            $item["add_time"]=time();
                            $item['s_users_nav_id']=$nav_id;
                            Db::table('s_users_nav_res')->insert($item);
                        }
                    }
                }
              	Db::commit();
			} catch (\Exception $e) {
			    // 回滚事务
			    Db::rollback();
			    $success = false;
			}
        }else{
            Db::startTrans();
            try 
            {
            	$id = Db::table('s_users_nav')->insert($data);
                if(sizeof($ResList))
                {
                    foreach ($ResList as $item) {
                    	$item["add_time"]=time();
                        $item['s_users_nav_id']=$id;
                        Db::table('s_users_nav_res')->insert($item);
                    }
                }
                Db::commit();
			} catch (\Exception $e) {
			    // 回滚事务
			    Db::rollback();
			    $success = false;
			}
        }
         
        return $success;
    }

    public function  ApiDelUsersNav($options=array())
    {
        //添加用户导航
        $users_nav_id = $options['id'];
        if (!$users_nav_id) { return false; }

        $ResList_C = Db::table('s_users_nav_res')->where(array('s_users_nav_id'=>$users_nav_id))->select($data);
        foreach ( $ResList_C as $key=>$value) {
        	unlink($ResList_C['key']['data_url']);
            unlink($ResList_C['key']['thumb']);
        }

        $nav_icon_url=Db::table('s_users_nav')->where(array('id'=>$users_nav_id))->column('icon_url');
        if(!empty($nav_icon_url)) { unlink($nav_icon_url); }
            
        $success= Db::table('s_users_nav')->where(array('id'=>$users_nav_id))->delete();
        return $success;
    }

    public function  ApiDelUsersContent($options=array())
    {
        //添加用户导航
        $cnt_id = $options['id'];
        $success = false;
        if(!$cnt_id) { return $success; }
            
        $data['id']=$options['id'];
        $ResList_C=Db::table('s_card_content_res')->where(array('card_content_id'=>$cnt_id))->select($data);
     	$thumb = Db::table('s_card_content')->where(array('id'=>$cnt_id))->column('thumb');
        Db::startTrans();
        try {
	       	Db::table('s_card_content_res')->where(array('card_content_id'=>$cnt_id))->delete();
	        Db::table('s_card_content')->where(array('id'=>$cnt_id))->delete();
            Db::commit();
            $success = true;
		} catch (\Exception $e) {
		    // 回滚事务
		    Db::rollback();
		}
		
     	foreach ( $ResList_C as $key=>$value) {
        	unlink($ResList_C['key']['data_url']);
        	unlink($ResList_C['key']['thumb']);    
        }
        
    	if(!empty($thumb)){unlink($thumb);}
    
        return $success;
    }

    public function  addUsersContentXcx($options=array())
    {
        //添加用户导航
        $data['title'] = $options['title'];//导航名称
        $data['add_time'] = time();
        $data['user_id'] = $options['user_id'];
        $data['titles'] = $options['titles'];
        $data['thumb'] = $options['thumb'];
        $data['sort'] = $options['sort'];
        $data['content'] = $options['content'];
        $cnt_id = $options['id'];
        $success = true;
        $ResList = $options['ResList'];
        if ($options['is_app'] == 1) {
            $ResList = str_replace('&quot;','"',$ResList);
            $ResList = json_decode($ResList,true);
        }
        
        if($cnt_id)
        {
            $data['id']= $cnt_id;
            $nav_icon_url=Db::table('s_card_content')->where(array('id'=>$cnt_id))->column('thumb');
            if($nav_icon_url!=$data['thumb'])
            {
                unlink($nav_icon_url);
            }
            
            Db::startTrans();
			try {
				Db::table('s_card_content')->update($data);
                $ResList_C=Db::table('s_card_content_res')->where(array('card_content_id'=>$cnt_id))->select($data);
                foreach ( $ResList_C as $key=>$value) {
                    $finded=false;
                    foreach ($ResList as $item) {
                        if(array_search($value['id'],$item)) {
                            $finded=true;
                            break;
                        }
                    }
                    if(!$finded)
                    {
                        //删除文件
                        unlink($ResList_C[$key]['data_url']);
                        unlink($ResList_C[$key]['thumb']);
                        //删除纪录
                        Db::table('s_card_content_res')->where(array('id'=>$value['id']))->delete();
                    }
                }
                if(sizeof($ResList))
                {
                    foreach ($ResList as $item) {
                        if($item['id'])
                        {
                            $item["add_time"]=time();
                            $item['card_content_id']=$cnt_id;
                            $success&&Db::table('s_card_content_res')->update($item);
                        }else {
                            $item["add_time"]=time();
                            $item['card_content_id']=$cnt_id;
                            $success&&Db::table('s_card_content_res')->insert($item);
                        }
                    }
                }
               	Db::commit();
        	} catch (\Exception $e) {
			    // 回滚事务
			    Db::rollback();
			    $success = false;
			}

        }else{
        	Db::startTrans();
			try {
	            Db::table('s_card_content')->insert($data);
	            $res_data = array();
	            if(count($ResList))
	            {
	            	foreach ($ResList as $item) {
	                	$item["add_time"]=time();
	                    $item['card_content_id']=$id;
	                    $item['user_id']=$data['user_id'];
	                	$res_data = $item;
	                }
	            }
	            if($res_data)
	            {
	            	 Db::table('s_card_content_res')->insertAll($res_data);
	            }
            	Db::commit();
        	} catch (\Exception $e) {
			    // 回滚事务
			    Db::rollback();
			    $success = false;
			}
        }
         
        return $success;
    }

    public function getUsersNav($user_id,$id)
    {
        #获取菜单项
        $nav=$this->UsersNav->where(array('user_id'=>$user_id,'id'=>$id))->find();
        if(!$nav)
        {
            return null;
        }
        if (strlen($nav['name'])>10)
            $nav['nameTo4'] = chinesesubstr($nav['name'],0,10).'..';
        else
            $nav['nameTo4'] = $nav['name'];
        $nav['icon_url'] = ToOpenWxImage($nav['icon_url']);
        $nav['icon_url_c'] = ToOpenWxImage($nav['icon_url_c']);
        $nav['jump_url'] = $nav['jump_url'];//TODO:跳转自动 加上域名
        $nav['add_time_formated'] =date("Y-m-d H:i:s",$nav['add_time']) ;
        #获取菜单资源项
        $nav['ResList'] = Db::table('s_users_nav_res')->where(array('s_users_nav_id'=>$nav['id']))->select();
        foreach  ($nav['ResList'] as $key=>$value) {
            $nav['ResList'][$key]['add_time_formated']=date("Y-m-d H:i:s",$nav['ResList'][$key]['add_time']) ;
        }
        
        return $nav;
    }

    public function getUsersContentXcx($user_id,$id)
    {
        #获取内容项
        $nav=$this->CardContent->where(array('user_id'=>$user_id,'id'=>$id))->find();
        if(!$nav)
        {
            return null;
        }
        if (strlen($nav['title'])>10)
            $nav['titleTo4'] = chinesesubstr($nav['name'],0,10).'..';
        else
            $nav['titleTo4'] = $nav['name'];

        $nav['add_time_formated'] =date("Y-m-d H:i:s",$nav['add_time']) ;
        #获取菜单资源项
        $nav['ResList'] = Db::table('s_card_content_res')->where(array('card_content_id'=>$nav['id']))->select();
        foreach  ($nav['ResList'] as $key=>$value) {
            $nav['ResList'][$key]['add_time_formated']=date("Y-m-d H:i:s",$nav['ResList'][$key]['add_time']) ;
        }
        
        return $nav;
    }

    public function delUsersNav($user_id,$id)
    {
        //删除用户导航
        $flag=$this->UsersNav->where(array('user_id'=>$user_id,'id'=>$id))->delete();
        return $flag;
    }


    public function upUserInfo($options=array())
    {
        //编辑用户信息
        $data=array();
        if(isset($options['headimg'])){
            //更改用户头像
            $data['headimg']=$options['headimg'];
        }
        if(isset($options['mobile'])){
            //更改手机号
            $data['mobile']=$options['mobile'];
        }
        if(isset($options['company'])){
            //行业标签
            $data['company']=$options['company'];
        }
        if(isset($options['industry_tag'])){
            //行业标签
            $data['industry_tag']=$options['industry_tag'];
        }
        if(isset($options['wxnum'])){
            //更改微信号
            $data['wxnum']=$options['wxnum'];
        }
        if(isset($options['address'])){
            //更改地址
            $data['address']=$options['address'];
        }
        if(isset($options['nick_name'])){
            //更改昵称
            $data['nick_name']=$options['nick_name'];
        }
        if(isset($options['position'])){
            //更改职位
            $data['position']=$options['position'];
        }
        if(isset($options['music_id'])){
            //更改背景音乐
            $data['music_id']=$options['music_id'];
        }
        if(isset($options['auto_music'])){
            //更改自动播放
            $data['auto_music']=$options['auto_music'];
        }
        
        $this->User->where(array('id'=>$options['id']))->update($data);
    }
    
    public function addBlog($user_id,$object_id,$content)
    {
        //添加留言 user_id=留言人id object_id 自己的id
        $data['user_id']=$user_id;
        $data['object_id']=$object_id;
        $data['content']=$content;
        $data['status']=1;
        $data['is_read']=0;
        $data['add_time']=time();
        $data=$this->Blog->insert($data);
        
        return $data;
    }
    
    public function getMyMsg($where,$orderBy = 'add_time desc')
    {
        //获取我的留言列表
        $data=$this->Blog->where($where)->order($orderBy)->relation(true)->select();
        return $data;
    }
    
    public function getMyMsgInfo($id,$my=0)
    {
        //获取我的留言详情
        if($my != 0){
            $this->setMsgRead(array('id'=>$id));
        }
        $data=$this->Blog->where(array("id"=>$id))->relation(true)->find();
        return $data;
    }
    
    public function getMyMsgDel($where)
    {
        //获取我的留言删除
        $flag=$this->Blog->where($where)->delete();
        return $flag;
    }
    
    public function setMsgRead($where)
    {
        //设置留言已读
        $this->Blog->where($where)->update(array("is_read"=>1));
    }
    
    public function getPrivacy($user_id)
    {
        //获取隐私保护
        $field="is_search,is_copy_cards,is_nickname,is_phone,is_wechat,is_qq,is_tel,is_email";
        $data=$this->User->field($field)->where(array("id"=>$user_id))->find();
        return $data;
    }
    
    public function setPrivacy($user_id,$type,$value)
    {
        //设置隐私保护
        switch ($type) {
            case 1:
                //防止别人在商脉圈搜索
                $data['is_search']=$value;
                break;
            case 2:
                //防止被复制卡片
                $data['is_copy_cards']=$value;
                break;
            case 3:
                //姓名是否显示
                $data['is_nickname']=$value;
                break;
            case 4:
                //手机号码是否显示
                $data['is_phone']=$value;
                break;
            case 5:
                //微信是否显示
                $data['is_wechat']=$value;
                break;
            case 6:
                //QQ是否显示
                $data['is_qq']=$value;
                break;
            case 7:
                //固话是否显示
                $data['is_tel']=$value;
                break;
            case 8:
                //邮箱是否显示
                $data['is_email']=$value;
                break;
            default:
                # code...
                break;
        }
        
        $this->User->where(array('id'=>$user_id))->update($data);
    }

    public function register($options=array())
    {
    	//注册
    	$flag=['status'=>1,'id'=>0];
        $search = array(" ","　","\n","\r","\t");
        $replace = array("","","","","");
        $options['phone'] = str_replace($search, $replace, $options['phone']);
    	$password=md5($options['phone']);
    	$options['city_id'] =1;
    	 
    	$info = $this->Member->where(array('phone'=>$options['phone']))->find();
        if($info){
        	$res = Db::table("s_user_detail")->where(array('member_id'=>$info['id']))->column('id');
            $flag['id'] = $res;
        	$flag['status']=2;
            return $flag;
        }
        $openid=session('openid')?session('openid'):'';
        $wx_info=session('openid_contents')?session('openid_contents'):'';

    	$member_id = Db::table('s_member')->insert(array('reg_ip'=>get_client_ip(),'add_time'=>time(),'reg_time'=>time(),'user_no'=>getStudentID(date('s')),'phone'=>$options['phone'],'password'=>$password,'openid'=>$openid,'wx_info'=>$wx_info,'last_log_time'=>time(),'last_log_ip'=>get_client_ip()));
    	if(!$member_id){
    		$flag['status']=0;
    		return $flag;
    	}
        $data['member_id']=$member_id;
        $data['style']=5;
        $data['sex']=$options['sex'];
        $data['age_id']=$options['age_id'];
        if($data['sex'] == 1){
            $img = 'https://wap.yxm360.com/Public/Home/images/headimg/girl_'.rand(1,9).'.png';
        }else{
            $img = 'https://wap.yxm360.com/Public/Home/images/headimg/boy_'.rand(1,6).'.png';
        }
        $data['unionid'] = getRandomString(11);
        $old = Db::table('s_user_detail')->where(array('unionid'=>$data['unionid']))->find();
        if($old){
            $data['unionid'] = getRandomString(5).getRandomString(6);
            $old1 = Db::table('s_user_detail')->where(array('unionid'=>$data['unionid']))->find();
            if($old1){
                $data['unionid'] = date('YmdHis').mt_rand(10,99);
            }
        }
        $data['headimg']=$img;
        $data['nick_name']=$options['phone'];
    	$data['add_time']=time();
        $data['province_id']=isset($options['province_id'])?$options['province_id']:0;
        $data['city_id']=isset($options['city_id'])?$options['city_id']:0;
    	$data['mobile']=$options['phone'];
    	$data['rec_user_id']=isset($options['rec_user_id'])?$options['rec_user_id']:0;
		$data['remark']=isset($options['remark']) ? $options['remark'] : '';

    	Db::startTrans();
    	try {
    		$user_id = Db::table('s_user_detail')->insert($data);
    	    //注册成功提醒
            //sendWxTemplateMessages('SendBindInfo',array( 'wecha_id' => $openid,'href'=>get_current_Host().url('/Home/User/usercenter'), 'first' => '恭喜您己成功注册洋小秘！', 'keyword1' =>$options['phone'], 'keyword2' =>$options['phone'], 'keyword3'=> date("Y-m-d H:i:s"),'remark' => '温馨提醒：帐号为您手机号，密码默认也是您手机号，一定要及时修改哟！在您成为创客前无法创建自己的V网！！'));
    		if(!empty($options['rec_user_id'])){
                //用户注册加积分
                $dat['user_id']=$options['rec_user_id'];
                $dat['inte'] = Db::table("s_inte")->where('id=4')->column('inte');
                $dat['addtime']=time();
                $dat['inte_id']=4;
                Db::table("s_user_detail")->where(array('id'=>$dat['user_id']))->setInc('inte',$dat['inte']);
                Db::table('s_inte_log')->insert($dat);

	    		$this->addFans(array(
	    			'user_id'=>$data['rec_user_id'],
	    			'object_id'=>$user_id
	    		));//推荐人添加粉丝
                //告诉推荐人有人注册了还没有付款 挪到了上面的方法中
    		}
    		
    		Db::commit();
            $flag['id']=$user_id;
        } catch (\Exception $e) {
            $flag['status']=0;
            Db::rollback();
    	}
    	
    	return $flag;
    }
	
    //添加粉丝
    public function addFans($options=array())
    {	
        //查询用户信息
        $user = $this->User->where(array('id'=>$options['user_id']))->field('id,member_id,nick_name,vip_group_id,mobile,rec_user_id')->find();//推荐人信息 
        $newuser = $this->User->where(array('id'=>$options['object_id']))->field('id,member_id,nick_name,vip_group_id,mobile,rec_user_id')->find();//注册用户信息
        $msg_data = array('user_id'=>$options['user_id'],'object_id'=>$options['object_id'],'msg'=>'您有一位新的粉丝','type'=>2,'status'=>1,'is_read'=>0,'add_time'=>time());
        
        $fans_obj = Db::table('s_users_fans');
        $msg_obj = Db::table('s_msg_list');
        if ($user['rec_user_id'] == 0) {//没有上级,直接粉丝,参与分销
            $fans = $fans_obj->where(array('user_id'=>$options['user_id'],'object_id'=>$options['object_id'],'level'=>1))->find();
            //防止重复添加
            if($fans){ return; }

            $_data=array();
            $_data['user_id'] = $options['user_id'];
            $_data['object_id'] = $options['object_id'];
            $_data['level']=1;//我的直接粉丝
            $_data['is_distribute']=1;
            $_data['add_time']=time();
            $fans_obj->insert($_data);
                
            $msg_obj->insert($msg_data);//消息添加
            $rec_member = $this->Member->where(array('id'=>$user['member_id']))->column('openid');
            if($rec_member){
            	sendWxTemplateMessages('SendBindInfo',array( 'wecha_id' => $rec_member,'href'=>get_current_Host().url('/Home/Income/myfans'), 'first' => '您有新的粉丝：'.$newuser['mobile'], 'keyword1' =>$newuser['nick_name'], 'keyword2' => $newuser['mobile'], 'keyword3'=> date("Y-m-d H:i:s"),'remark' => '该用户刚刚注册，还没有成为创客，点击查看'));
            }
        } else {
	        //todo 推荐人有上级
	        $fans = $fans_obj->where(array('user_id'=>$options['user_id'],'object_id'=>$options['object_id'],'level'=>1))->find();
	        if (!$fans) {
                //防止重复添加
                $_data = array();
                $_data['user_id'] = $options['user_id'];
                $_data['object_id'] = $options['object_id'];
                $_data['level'] = 1;//我的直接粉丝
                $_data['is_distribute'] = 0;
                if ($user['vip_group_id'] >= 3) $_data['is_distribute']=1;//是创投以上角色,参与分销，即拿那35%；
                $_data['add_time'] = time();
                $fans_obj->insert($_data);
                
                $msg_obj->insert($msg_data);//消息添加);
	        	//发送模板消息
                $rec_member = $this->Member->where(array('id'=>$user['member_id']))->column('openid');
                if($rec_member){
                    sendWxTemplateMessages('SendBindInfo',array('wecha_id' => $rec_member,'href'=>get_current_Host().url('/Home/Income/myfans'), 'first' => '您有新的粉丝：'.$newuser['mobile'], 'keyword1' =>$newuser['nick_name'], 'keyword2' => $newuser['mobile'], 'keyword3'=> date("Y-m-d H:i:s"),'remark' => '该用户刚刚注册，还没有成为创客，点击查看'));
                }
                unset($_data);
            }
            
            //todo 推荐人的上级 添加相应的粉丝信息
            $fans = $fans_obj->where(array('object_id' => $user['id']))->field('user_id,is_distribute')->select();
            if (count($fans)) 
            {
            	//初始化	
            	$user_arr = array();
                $fans_array = array();
                $msg_array = array();
                $_data = array('object_id'=>$options['object_id'],'level'=>2,'add_time'=>time());
                $data = array('object_id'=>$options['object_id'],'msg'=>'您有一位新的粉丝','type'=>2,'status'=>1,'is_read'=>0,'add_time'=>time());
                $uids = array_column($fans,'user_id');
                $uids = implode(',',$uids);
                $map = array();
                $map['id']  = array('in',$uids);
                $users = $this->User->where($map)->field('id,member_id,vip_group_id')->select();//上级人信息
                //遍历我的所有上级，关联这个新粉丝
                foreach ($users as $item) {
                	$user_arr[$item['id']] = $item;
                }
                
                //遍历我的所有上级，关联这个新粉丝
                foreach ($fans as $fan) {
                    $uid = $fan['user_id'];
                	$fanUser = $user_arr[$uid];
                    $_data['user_id'] = $uid;
                    if ($user['vip_group_id'] < 3 && $fanUser['vip_group_id'] >= 3 && $fan['is_distribute']) {
                    	$_data['is_distribute'] = 1;//我不是创投以上，我的上级是创投以上，则参与分销
                    }else{
                    	$_data['is_distribute'] = 0;
                    } 
                    $fans_array[] = $_data;
                    
                    $data['user_id'] = $uid;
                    $msg_array[] = $data;            
                }
                
                //批量插入数据表
                $fans_obj->insertAll($fans_array);
                $msg_obj->insertAll($msg_array);    
            }
        }
    }

     //
    public function xcx_register($options=array())
    {
        //注册
        $flag=['status'=>0,'id'=>0];//0、成功  1，己存在,2：没有传openid，没有授权,3、添加meber失败,4、添加用户失败 其它数值 是新增加的userid,5：没有推荐人
        $password=md5($options['mobile']);
        if(!isset($options['recordUser']))
        {
            $flag['status']=5;
            return $flag;
        }
        if($this->Member->where(array('phone'=>$options['mobile']))->find()){
            $flag['status']=1;
            return $flag;
        }
        $openid=$options['spopenid'];
        if($openid ==  null)
        {
            $flag['status']=2;
            return $flag;
        }
        
        $wx_info=$options['openid_contents'];
        $member_id = Db::table('s_member')->insert(array('phone'=>$options['mobile'],'password'=>$password,'spopenid'=>$openid,'wx_info'=>$wx_info));
        if(!$member_id){
            $flag['status']=3;
            return $flag;
        }
        $data['member_id']=$member_id;
        $data['style']=5;
        if($data['sex'] == 1){
            $img = 'https://wap.yxm360.com/Public/Home/images/headimg/girl_'.rand(1,9).'.png';
        }else{
            $img = 'https://wap.yxm360.com/Public/Home/images/headimg/boy_'.rand(1,6).'.png';
        }
        $data['unionid'] = getRandomString(11);
        $old = Db::table('s_user_detail')->where(array('unionid'=>$data['unionid']))->find();
        if($old){
            $data['unionid'] = getRandomString(5).getRandomString(6);
            $old1 = Db::table('s_user_detail')->where(array('unionid'=>$data['unionid']))->find();
            if($old1){
                $data['unionid'] = date('YmdHis').mt_rand(10,99);
            }
        }
        $data['headimg']=$img;
        $data['nick_name']=$options['mobile'];
        $data['sex']=$options['sex'];
        $data['age_id']=$options['age_id'];
        $data['add_time']=time();
        $data['province_id']=isset($options['province_id'])?$options['province_id']:0;
        $data['city_id']=isset($options['city_id'])?$options['city_id']:0;
        $data['mobile']=$options['mobile'];
        $data['rec_user_id']=$options['recordUser']['id'];
        $data['spopenid']=$options['spopenid'];
        $user_id=Db::table('s_user_detail')->insert($data);
        if($user_id){
            //注册成功提醒
            //sendWxTemplateMessages('SendBindInfo',array( 'wecha_id' => $openid,'href'=>get_current_Host().url('/Home/User/usercenter'), 'first' => '恭喜您己成功注册洋小秘！', 'keyword1' =>$options['phone'], 'keyword2' =>$options['phone'], 'keyword3'=> date("Y-m-d H:i:s"),'remark' => '温馨提醒：帐号为您手机号，密码默认也是您手机号，一定要及时修改哟！在您成为创客前无法创建自己的V网！！'));
            if(!empty($data['rec_user_id'])){
                //用户注册加积分
                $dat['user_id']=$data['rec_user_id'];
                $dat['inte'] = Db::table("s_inte")->where('id=4')->column('inte');
                $dat['addtime']=time();
                $dat['inte_id']=4;
                Db::table("s_user_detail")->where(array('id'=>$dat['user_id']))->setInc('inte',$dat['inte']);
                Db::table('s_inte_log')->insert($dat);

                $this->FansLogic->addFans(array(
                    'user_id'=>$data['rec_user_id'],
                    'object_id'=>$user_id
                ));//推荐人添加粉丝
                //告诉推荐人有人注册了还没有付款 挪到了上面的方法中
            }
            $flag['id']=$user_id;
        }else{
            $flag['status']=4;
        }
        
        return $flag;
    }


    public function login($options=array())
    {
        //登陆
		if(!$options ) { return false; }
       	//判断是否是是微信公众号环境start
       	$user_agent = $_SERVER['HTTP_USER_AGENT'];
       	$is_wechat = strpos($user_agent, 'MicroMessenger') ? true : false;
       	// 非微信浏览器禁止浏览
       	$member = array();
       	$openid = isset($options['openid']) ? $options['openid'] : '';
       	if($options['username']&&$options['password'])
       	{
       	   $phone = $options['username'];
           $password = md5($options['password']);
           $member = $this->Member->where(array('phone'=>$phone,'password'=>$password,'status'=>1))->find();
           if($is_wechat && $member) {
           	  //该微信登录本帐号，会将以前登录过的其它帐号解除
           	  $this->Member->where(array('openid'=>session('openid')))->update(array('openid'=>''));
              $this->Member->where(array('id'=>$member['id']))->update(array('openid'=>session('openid')));
           }
       	}elseif($openid){       
        	$member = $this->Member->where(array('openid'=>$openid))->find();
       	}
        //用户不存在返回fase    
	    if (!$member) { return false;}

        $user = $this->User->where(array('member_id'=>$member['id']))->find();
        $user['vip_group_name'] = $this->VipGroup->where(array('id'=>$user['vip_group_id']))->column('vip_name');
        $user['phone'] = $member['phone'];
        $user['openid'] = $openid;
        
        $headimg = isset($options['headimg']) ? $options['headimg'] : '';
        $this->User->update(array('wx_header'=>$headimg,'is_quit'=>0),array('member_id'=>$member['id']));//更新头象
		//session保存用户信息
        session("user_info",$user);
        
        return $user;
    }

    // 登录接口
    public function xcxlogin($options=array())
    {
        //登陆
        $openid=$options['name'];
        $wx_id = $options['wx_id'];
        if($wx_id){
            // 查找范围缩小一下
            $user = Db::table('s_user_detail')->alias('u')
                ->leftJoin(' s_member m ',' m.id = u.member_id')
                ->field('u.*')
                ->where(array('u.id'=>$wx_id,'m.status'=>1))->find();
        }else{
            $member = $this->Member->where(array('spopenid'=>$openid,'status'=>1))->find();
            $user = $this->User->where(array('member_id'=>$member['id']))->find();
        }

        if(!$user){
            return false;
        }

        if($options['isMy'])
        {
            // last_log_time 数据库没有
           $this->Member->where(array('id'=>$user['member_id']))->update(array('last_log_time'=>date('Y-m-d H:i:s',time()),'last_log_ip'=>get_client_ip()));//更新登陆信息
            if ($options['headimg'] != null){
                $this->User->where(array('id'=>$user['id']))->update(array('wx_header'=>$options['headimg']));//更新头象
            }
            $result=$this->getUserData($user['id']);
        }else {
            //TODO: 增加访问纪录
            if($wx_id){
                $vistOpenid=$options['res_id'];
                $visterId= $this->User->where(array('id'=>$vistOpenid))->column('id');
            }else{
                $vistOpenid=$options['spopenid'];
                $visterId= $this->User->where(array('spopenid'=>$vistOpenid))->column('id');
            }
            $result=$this->getUserData($user['id'],$visterId);
        }

        $result['UserId']=$openid?$openid:$user['spopenid'];
        return $result;
    }


    public function forgetPassWord($options=array())
    {
        //忘记密码
        $flag=true;
        if($options['password_1']!=$options['password_2'] || empty($options['password_1']) || empty($options['password_2'])){
        	$flag=false;
        }else{
        	$this->Member->where(array('id'=>$options['id']))->update(array('password'=>md5($options['password_2'])));//更新
        }
        
        return $flag;
    }
    
    public function getBindUser($user_id)
    {
        //获取绑定用户列表
        $currentUser = $this->UserRelate->where(array('user_id'=>$user_id))->find();
        if(!$currentUser)
            return array();
        $list = $this->UserRelate->where(array('group_id'=>$currentUser['group_id']))->relation(true)->select();
        
        return $list;
    }
    
    public function copyCard($user_id,$object_id)
    {
        $user=$this->User->where("id=".$object_id)->find();
        $data['text_color']=$user['text_color'];
        $data['background_color']=$user['background_color'];
        $data['title_color']=$user['title_color'];
        $data['link_color']=$user['link_color'];
        $data['background_url']=$user['background_url'];
        $data['bg_opacity']=$user['bg_opacity'];
        $data['style']=$user['style'];
        $this->User->where("id=".$user_id)->update($data);
        $nav=$this->UsersNav->where("user_id=".$object_id)->select();
        $nav_sort = $this->UsersNav->where("user_id=".$object_id)->max('sort');
        $nav_array = array();
        foreach ($nav as $key => $value) {
            $nav_sort++;
            $_data['user_id']=$user_id;
            $_data['name']=$value['name'];
            $_data['icon_url']=$value['icon_url'];
            $_data['jump_url']=$value['jump_url'];
            $_data['add_time']=time();
            $_data['sort'] = $nav_sort;
            $nav_array[] = $_data;
        }
        $this->UsersNav->insertAll($nav_array);

        $content = $this->CardContent->where(array('user_id'=>$object_id))->order('sort asc')->select();
        $sort = $this->CardContent->where(array('user_id'=>$user_id))->max('sort');
        $res_array = array();
        foreach ($content as $c){
            $sort++;
            $con['title'] = $c['title'];
            $con['titles'] = $c['titles'];
            $con['thumb'] = $c['thumb'];
            $con['content'] = $c['content'];
            $con['user_id'] = $user_id;
            $con['add_time'] = time();
            $con['sort'] = $sort;
            $new_id = $this->CardContent->insert($con);
            if($new_id){
                $res = Db::table('s_card_content_res')->where(array('card_content_id'=>$c['id']))->select();
                foreach ($res as $r){
                    $result['card_content_id'] = $new_id;
                    $result['type'] = $r['type'];
                    $result['data_url'] = $r['data_url'];
                    $result['thumb'] = $r['thumb'];
                    $result['user_id'] = $user_id;
                    $result['add_time'] = time();
                    $res_array[] = $result;
                }
            }
        }
        if($res_array){
            Db::table('s_card_content_res')->insertAll($res_array);
        }
    }
    
    public function getUserInfo($id)
    {
        return $this->Member->where(array('id'=>$id))->find();//更新登陆信息
    }
    
    public function getUserInfoOption($option)
    {
        if($option['unionid']) {
            $res = $this->User->where(array('unionid' => $option['unionid']))->find();
        }elseif($option['id']){
            $res = $this->User->where(array('id' => $option['id']))->find();
        }else{
            $member = $this->Member->where($option)->find(); 
            $res=$this->User->where(array('member_id'=>$member['id']))->find();   
        }
        
        return $res;
    }
    
    public function bindAccount($user_id,$accountId)
    {
        $currentUser = $this->UserRelate->where(array('user_id'=>$user_id))->find();
        $accountInfo = $this->UserRelate->where(array('user_id'=>$accountId))->find();
        $groupId = $currentUser['group_id'];
        if(!$currentUser){
            $groupId = $accountInfo['group_id'];
            if(!$accountInfo) {
                $maxId = $this->UserRelate->max('group_id');
                $groupId = $maxId ? $maxId+1 : 1;
            }
            $this->UserRelate->insert(array('group_id'=>$groupId,'user_id'=>$user_id,'time'=>time(),'ip'=>get_client_ip()));
        }else{
            // 检查账号是否被绑定过
            if($accountInfo){
                return false;
            }
        }
        $addId = $this->UserRelate->insert(array('group_id'=>$groupId,'user_id'=>$accountId,'time'=>time(),'ip'=>get_client_ip()));
        
        return $addId;
    }
    
    public function changeAccount($id)
    {
        $user=$this->User->where(array('id'=>$id))->find();
        $member=$this->Member->where(array('id'=>$user['member_id']))->find();
        if(!$member){
            return false;
        }
        $this->Member->where(array('id'=>$member['id']))->update(array('last_log_time'=>time(),'last_log_ip'=>get_client_ip()));//更新登陆信息

        $user['vip_group_name']=$this->VipGroup->where(array('id'=>$user['vip_group_id']))->column('vip_name');

        return $user;
    }

    public function getVipLevl($member_id)
    {
        $group_level=$this->User->where(array('member_id'=>$member_id))->field('vip_group_id')->find();
        return $group_level;
    }

    public function getUserBySpOpenid($spopenid)
    {
        $user=$this->User->where(array('spopenid'=>$spopenid))->find();
        return $user;
    }

    public function GetOrCreateshareimg($spopenid,$recreate)
    {
        //TODO:判断用户是否存在
        $userinfo=$this->User->where(array('spopenid'=>$spopenid))->find();
        if(!$userinfo)
        {
            return null;
        }

        $font_path = './Uploads/msyh.ttf';
        $sharePublicIMg="./Uploads/bg.png";
        $qrcode_save_path="./Uploads/Xcx/";

        if(!is_null($userinfo['shareimg']) &&$userinfo['shareimg']!='' &&getimagesize($userinfo['shareimg'])&&!$recreate)
        {
          return ToOpenWxImage( $userinfo['shareimg']);
        }

        $userName="我是".$userinfo['nick_name'];
        $qrcodeimg=$userinfo['qrcodeimg'];
        $thumbhead=$userinfo['thumbhead'];
        $user_id=$userinfo['id'];

        //判断用户是否有二维码
        if(is_null( $qrcodeimg)||$qrcodeimg==''||!getimagesize($qrcodeimg)||$recreate)
        {
            if(!is_dir($qrcode_save_path.date('Ymd'))){
                $a=mkdir($qrcode_save_path.date('Ymd'),0777,true);
            }
            //保存路径和文件名称
            $qrCode_file_name = $qrcode_save_path.date('Ymd').'/ewm_'.md5($user_id).'.png';
            //保存内容
            $url = 'http://'.$_SERVER['HTTP_HOST'].'/share?uid='.$spopenid;
            //背景、头象、二维码(个人网址）、文字
            qrcode2($url,4,$qrCode_file_name);
            $qrcodeimg=$qrCode_file_name;
        }

        if(is_null($thumbhead) ||$thumbhead==''||!getimagesize($thumbhead)||$recreate)
        {
            if(!is_dir($qrcode_save_path.date('Ymd'))){
                $a=mkdir($qrcode_save_path.date('Ymd'),0777,true);
            }
            $userImg=$userinfo['headimg'];
            if (!$userImg||!getimagesize('.'.$userImg)) {
                $userImg="/Uploads/User/b.png";
                $head_img_path=$qrcode_save_path.date('Ymd').'/head_'.md5($user_id).'.png';
            }else {
                $head_img_path = $qrcode_save_path . date('Ymd') . '/head_' . md5($user_id) . '.jpg';
            }
            //保存内容
            $image = new \Think\Image();
            $image->open('.'.$userImg);
            $image->thumb(480, 480,3)->update($head_img_path);
            //$userinfo->update(array('thumbhead'=>$head_img_path));
            $thumbhead=$head_img_path;

        }

        $file_name= $qrcode_save_path.date('Ymd').'/share_'.md5($user_id).'.png';
        $imgEven = new imgModel();
        $img=$imgEven->update_usershareimg($sharePublicIMg,$head_img_path,$qrCode_file_name,$file_name,$userName,'',$font_path);
        $this->User->update(array('id'=>$user_id,'shareimg'=>$img,'thumbhead'=>$thumbhead,'qrcodeimg'=>$qrcodeimg));

        return ToOpenWxImage($img) ;
    }

    public function updateXcxOpenid($info)
    {
        Db::startTrans();
        try {
	        Db::table('s_user_detail')->where(array('member_id'=>$info['id']))->update(['spopenid'=>$info['openid']]);
	        Db::table('s_member')->where(array('id'=>$info['id']))->update(['spopenid'=>$info['openid']]);
            Db::commit();
            return true;
        } catch (\Exception $e) {
            Db::rollback();
            return false;
        }
    }

    public function payComference($options=array())
    {
	    $mobj = Db::table("s_activity_order")->where(array("s_order_id"=>$options['order_id'],"s_order_start"=>1))->find();
	    if($mobj ==  null){
	        return 0;
	    }
	    $m = Db::table("s_activity_order")->where(array("s_order_id" =>$options['order_id']))->data(
	        array("s_order_pay_time"=>date('Y-m-d H:i:s'),"s_order_dis"=>$options['out_trade_no'],"s_order_start"=>2))->update();
	    $d = Db::table("s_signin_usre")->where(array("s_userid"=>$mobj["s_activity_userid"]))->update(array("s_start"=>2));
	    
	    return 1;
	}
	
    public function payXmb($options=array())
    {
        $mobj = Db::table("s_xmb_order")->where(array("order_no"=>$options['order_no'],"pay_status"=>1))->find();
        if ($mobj ==  null) {return 0; }
       
        Db::startTrans();
        try {
	        $m=Db::table("s_xmb_order")->where(array("order_no" =>$options['order_no']))->data(array("pay_status"=>2))->update();
	        $data['order_sn']=$options['order_no'];//订单号
	        $data['user_id']=$options["user_id"];
	        $data['as']=1;//减少
	        $data['money']=$options['price'];
	        $data['type']=12;
	        $data['msg']="小秘充值";
	        $data['add_time']=time();
	        $data['status']=2;
	        Db::table("s_capital_log")->insert($data);
	        Db::table("s_user_detail")->where(array("id"=>$options["user_id"]))-> setInc('money',$options['price']);
            Db::commit();
            return 1;
        } catch (\Exception $e) {
		    // 回滚事务
            file_put_contents(APP_ROOT.'/test.txt', PHP_EOL.json_encode($mobj).PHP_EOL,FILE_APPEND);
            Db::rollback();
            return 0;
        }
    }
    
    public function payHk($options=array())
    {
    	$res = 0;
        $info = Db::table("s_hk_order")->where(array("id"=>$options['order_id'],"s_order_start"=>1))->find();
        if($info ==  null){
            return $res;
        }
        
        $data = array("s_order_pay_time"=>date('Y-m-d H:i:s'),"s_order_dis"=>$options['out_trade_no'],"s_order_start"=>2);
		Db::startTrans();
		try {
	        $m=Db::table("s_hk_order")->where(array("id" =>$options['order_id']))->data($data)->update();
	        $d=Db::table("s_hk_user")->where(array("id"=>$info["s_order_userid"]))->update(array("start"=>2));
		 	Db::commit();
		    $res = 1;
		} catch (\Exception $e) {
			file_put_contents(APP_ROOT.'/test.txt', PHP_EOL.json_encode($mobj).PHP_EOL,FILE_APPEND);           
		    // 回滚事务
		    Db::rollback();
        }
        
        return $res;
    }

    public function payCallback($options=array())
    {
        //加入商会回调
        if($options['result_code']!="SUCCESS" || !isset($options['attach'])){
            return 0;
        }
        
        $data = $options['data'];
        $member = Db::table('s_user_trade_area_member')->where(array('trade_id' => $data['trade_id'],'user_id' => $data['user_id']))->find();
        if ($member) {
            return 1;
        }
        $trade = Db::table('s_user_trade_area')->find($data['trade_id']);
        $member['trade_id'] = $trade['id'];
        $member['user_id'] = $data['user_id'];
        $member['user_level'] = 1;
        $member['status'] = 1;
        $member['add_time'] = time();
        $res = Db::table('s_user_trade_area_member')->insert($member);
        if ($res) {
            $message['type'] = 1;
            $message['title'] = '加入商会';
            $message['content'] = '加入商会';
            $message['send_time'] = time();
            $message['status'] = 2;
            $message['trade_id'] = $trade['id'];
            $message['target_id'] = $trade['user_id'] ;
            $message['source_id'] = $data['user_id'];
            Db::table('s_usre_trade_msg')->insert($message);
        }
        
        $log['pay_sn']=$options['transaction_id'];//微信订单号
        $log['order_sn']=$options['out_trade_no'];//商户订单号
        $log['user_id']= $trade['user_id'];
        $log['object_id']=$data['user_id'];
        $log['as']=2;//减少
        $log['money']=$trade['money'];
        $log['type']=6;
        $log['msg']="加入商会";
        $log['add_time'] = time();
        $log['pay_callback']=json_encode($options);//第三方支付回调
        Db::table('s_capital_log')->insert($log);
        $userInfo = Db::table('s_user_detail')->find($trade['user_id']);
        $userInfo['money'] += $trade['money'];
        Db::table('s_user_detail')->update($userInfo);
        
        return 1;
    }
    
    public function GetAreas()
    {
       $result = Cache::get('AreaTree');
       return ($result);
    }


    public function SaveMyCard($options=array())
    {
        $data = array();
        if($options['userInfo']['id'])
        {
            $data['id']=$options['userInfo']['id'];
        }else{
            return false;
        }
        
        $ResList=$options['ResList'];
        if(isset($options['id']))
        {$data['id']=$options['id'];}
        if(isset($options['background_img']))
        {$data['background_img']=$options['background_img'];}
        if(isset($options['member_id']))
        {$data['member_id']=$options['member_id'];}
        if(isset($options['headimg']))
        {$data['headimg']=$options['headimg'];}
        if(isset($options['nick_name']))
        {$data['nick_name']=$options['nick_name'];}
        if(isset($options['sex']))
        {$data['sex']=$options['sex'];}
        if(isset($options['age_id']))
        {$data['age_id']=$options['age_id'];}
        if(isset($options['position']))
        {$data['position']=$options['position'];}
        if(isset($options['company']))
        {$data['company']=$options['company'];}
        if(isset($options['industry_tag']))
        {$data['industry_tag']=$options['industry_tag'];}
        if(isset($options['mobile']))
        {$data['mobile']=$options['mobile'];}
        if(isset($options['telephone']))
        {$data['telephone']=$options['telephone'];}
        if(isset($options['wxnum']))
        {$data['wxnum']=$options['wxnum'];}
        if(isset($options['qq']))
        {$data['qq']=$options['qq'];}
        if(isset($options['email']))
        {$data['email']=$options['email'];}
        if(isset($options['site']))
        {$data['site']=$options['site'];}
        if(isset($options['address']))
        {$data['address']=$options['address'];}
        if(isset($options['music_id']))
        {$data['music_id']=$options['music_id'];}
        if(isset($options['vip_group_id']))
        {$data['vip_group_id']=$options['vip_group_id'];}
        if(isset($options['vip_id']))
        {$data['vip_id']=$options['vip_id'];}
        if(isset($options['visit_count']))
        {$data['visit_count']=$options['visit_count'];}
        if(isset($options['share_my_introduct']))
        {$data['share_my_introduct']=$options['share_my_introduct'];}
        if(isset($options['share_my_advantage']))
        {$data['share_my_advantage']=$options['share_my_advantage'];}
        if(isset($options['share_my_resource']))
        {$data['share_my_resource']=$options['share_my_resource'];}
        if(isset($options['wx_ewm_url']))
        {$data['wx_ewm_url']=$options['wx_ewm_url'];}
        if(isset($options['article_advert']))
        {$data['article_advert']=$options['article_advert'];}
        if(isset($options['article_reward']))
        {$data['article_reward']=$options['article_reward'];}
        if(isset($options['article_cards']))
        {$data['article_cards']=$options['article_cards'];}
        if(isset($options['rec_user_id']))
        {$data['rec_user_id']=$options['rec_user_id'];}
        if(isset($options['is_copy_cards']))
        {$data['is_copy_cards']=$options['is_copy_cards'];}
        if(isset($options['is_nickname']))
        {$data['is_nickname']=$options['is_nickname'];}
        if(isset($options['is_phone']))
        {$data['is_phone']=$options['is_phone'];}
        if(isset($options['is_tel']))
        {$data['is_tel']=$options['is_tel'];}
        if(isset($options['is_email']))
        {$data['is_email']=$options['is_email'];}
        if(isset($options['is_qq']))
        {$data['is_qq']=$options['is_qq'];}
        if(isset($options['is_wechat']))
        {$data['is_wechat']=$options['is_wechat'];}
        if(isset($options['status']))
        {$data['status']=$options['status'];}
        if(isset($options['vip_orver_time']))
        {$data['vip_orver_time']=$options['vip_orver_time'];}
        if(isset($options['text_color']))
        {$data['text_color']=$options['text_color'];}
        if(isset($options['background_color']))
        {$data['background_color']=$options['background_color'];}
        if(isset($options['title_color']))
        {$data['title_color']=$options['title_color'];}
        if(isset($options['link_color']))
        {$data['link_color']=$options['link_color'];}
        if(isset($options['background_url']))
        {$data['background_url']=$options['background_url'];}
        if(isset($options['add_time']))
        {$data['add_time']=$options['add_time'];}
        if(isset($options['give_vip1_count']))
        {$data['give_vip1_count']=$options['give_vip1_count'];}
        if(isset($options['give_vip2_count']))
        {$data['give_vip2_count']=$options['give_vip2_count'];}
        if(isset($options['give_vip3_count']))
        {$data['give_vip3_count']=$options['give_vip3_count'];}
        if(isset($options['give_vip4_count']))
        {$data['give_vip4_count']=$options['give_vip4_count'];}
        if(isset($options['give_vip5_count']))
        {$data['give_vip5_count']=$options['give_vip5_count'];}
        if(isset($options['already_money']))
        {$data['already_money']=$options['already_money'];}
        if(isset($options['frozen_money']))
        {$data['frozen_money']=$options['frozen_money'];}
        if(isset($options['bind_user_id']))
        {$data['bind_user_id']=$options['bind_user_id'];}
        if(isset($options['is_search']))
        {$data['is_search']=$options['is_search'];}
        if(isset($options['city_id']))
        {$data['city_id']=$options['city_id'];}
        if(isset($options['district_id']))
        {$data['district_id']=$options['district_id'];}
        if(isset($options['province_id']))
        {$data['province_id']=$options['province_id'];}
        if(isset($options['money']))
        {$data['money']=$options['money'];}
        if(isset($options['openid']))
        {$data['openid']=$options['openid'];}
        if(isset($options['wx_info']))
        {$data['wx_info']=$options['wx_info'];}
        if(isset($options['style']))
        {$data['style']=$options['style'];}
        if(isset($options['bg_opacity']))
        {$data['bg_opacity']=$options['bg_opacity'];}
        if(isset($options['sq_type_id']))
        {$data['sq_type_id']=$options['sq_type_id'];}
        if(isset($options['is_map']))
        {$data['is_map']=$options['is_map'];}
        if(isset($options['sq_keyword']))
        {$data['sq_keyword']=$options['sq_keyword'];}
        if(isset($options['sq_is_search']))
        {$data['sq_is_search']=$options['sq_is_search'];}
        if(isset($options['sq_is_share']))
        {$data['sq_is_share']=$options['sq_is_share'];}
        if(isset($options['is_quit']))
        {$data['is_quit']=$options['is_quit'];}
        if(isset($options['spopenid']))
        {$data['spopenid']=$options['spopenid'];}
        if(isset($options['unionid']))
        {$data['unionid']=$options['unionid'];}
        if(isset($options['leader_id']))
        {$data['leader_id']=$options['leader_id'];}
        if(isset($options['shareImg']))
        {$data['shareImg']=$options['shareImg'];}
        if(isset($options['qrCodeImg']))
        {$data['qrCodeImg']=$options['qrCodeImg'];}
        if(isset($options['thumbHead']))
        {$data['thumbHead']=$options['thumbHead'];}
        if(isset($options['wx_header']))
        {$data['wx_header']=$options['wx_header'];}
        if(isset($options['send_vip1_count']))
        {$data['send_vip1_count']=$options['send_vip1_count'];}
        if(isset($options['send_vip2_count']))
        {$data['send_vip2_count']=$options['send_vip2_count'];}
        if(isset($options['send_vip3_count']))
        {$data['send_vip3_count']=$options['send_vip3_count'];}
        if(isset($options['send_vip4_count']))
        {$data['send_vip4_count']=$options['send_vip4_count'];}
        if(isset($options['send_vip5_count']))
        {$data['send_vip5_count']=$options['send_vip5_count'];}
        if(isset($options['user_no']))
        {$data['user_no']=$options['user_no'];}
        if(isset($options['auto_music']))
        {$data['auto_music']=$options['auto_music'];}
      
        Db::startTrans();
        try {
        	Db::table('s_user_detail')->update($data);
            $ResList_C=Db::table('s_user_img')->where(array('user_id'=>$data['id']))->select();
            foreach ( $ResList_C as $key=>$value) {
                $finded=false;
                foreach ($ResList as $item) {
                    if(array_search($value['id'],$item)) {
                        $finded=true;
                        break;
                    }
                }
                if(!$finded)
                {
                    //删除文件
                    unlink($ResList_C[$key]['img_url']);
                    unlink($ResList_C[$key]['data_compress_url']);
                    //删除纪录
                    Db::table('s_user_img')->where(array('id'=>$value['id']))->delete();
                }
            }
            if(sizeof($ResList))
            {
                foreach ($ResList as $item) {
                    if($item['id'])
                    {
                        $item["createtime"]=time();
                        Db::table('s_user_img')->update($item);
                    }else{
                        $item["createtime"]=time();
                        $item['user_id']=$data['id'];
                        Db::table('s_user_img')->insert($item);
                    }
                }
            }
            Db::commit();
            return true;
		} catch (\Exception $e) {
         	Db::rollback();
         	return false;
        }
    }

    /**
     * @param array $options
     * @return array|bool|mixed|string
     * 小程序支付会员
     */
    public function XcxBuyVip($options=array())
    {
        $options['user_id']=$options['user']['id'];
        $options['vip_id']=$options['vip']['id'];

        $order_id= $this->Vip->buyVip($options);
        $order= $this->Order->where(array("id"=>$order_id))->find();
        $order['share_id']=$options['user']['rec_user_id'];
        $order['share_name']=$this->User->where(array("id"=>$order['share_id']))->column("nick_name");
        $group=$this->VipGroup->where(array("id"=>$options['vip']['vip_group_id']))->find();
        $order['vip_name']=$group['vip_name'];
        $openid=$options['spopenid'];
        if($openid==''){
            $this->error("您暂时无法进行支付");exit;
        }
        $data=[
            'connect_id'=>$order_id,
            'type' =>5, //微信小程序支付
            'order_type'=>1,
            'pay_money' =>$order['price'],
            //'pay_money' =>0.01,
            'pay_title'=>$order['vip_name'],
            'pay_stitle'=>$order['vip_name'],
            'pay_tag'=>'费用',
            'mark'=>"",
            'openid'=>$openid,
            'order_sn'=>$order['order_no'],
        ];
      
        $res=get_obj('pay_test')->add_pay_log($data);
        $res=(json_decode($res,true));
        return $res;
    }

    /**
     * @param array $options
     * @return array|bool|mixed|string
     * 小程序支付年费
     */
 	public function XcxBuyYear($options=array())
    {
        $options['user_id'] = $options['user']['id'];
        $openid = $options['spopenid'];
        if($openid==''){
            $this->error("您暂时无法进行支付");exit;
        }
        $_data['user_id']=$options['user_id'];
        $_data['day']=365;
        $data=[
            'connect_id'=>$_data['user_id'],
            'type' =>5,//小程序支付
            'order_type'=>2,//年费回调类型
            'pay_money' =>$options['price'],
            //'pay_money' =>0.01,
            'pay_title'=>"会员续费",
            'pay_stitle'=>"会员续费",
            'pay_tag'=>'续费',
            'mark'=>json_encode($_data),
            'openid'=>$openid,
        ];
       
        //$res=get_obj('pay_test')->add_pay_log($data);
        $res=(json_decode($res,true));
        return $res;
    }

}