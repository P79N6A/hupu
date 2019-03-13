<?php
/*
**********
用户注册表逻辑层
************
 */
namespace app\api\logic;
use think\Model;
use app\api\model\Member  as memberModel;
use app\api\model\MobileCode  as codeModel;

class Member extends Model
{
    private $Member=null;
    private $Code=null;
    private $code_expire_time=160;//验证码过期时间
    private $array_return=array("status"=>1,"msg"=>"success","data"=>[]);

    function __construct()
    {
        $this->Member = new memberModel();
        $this->Code = new codeModel();
    }

    public function add($options=array())
    {
        $data['phone']=$options['phone'];
        $data['password']=$options['password'];
        $data['reg_ip']=get_client_ip();
        if(!empty($options['openid']))
        {
            $data['openid']=$options['openid'];
            $sdata['openid']='';
            $this->Member->where(array('openid'=>$data['openid']))->update($sdata);
        }
        if(!empty($options['spopenid']))
        {
            $data['spopenid']=$options['spopenid'];
            $sdata['spopenid']='';
            $this->Member->where(array('spopenid'=>$data['spopenid']))->update($sdata);
        }

        $data['wx_info']=!empty($options['wx_info'])?$options['wx_info']:'';
        $data['status']=1;
        $data['add_time']=time();
        $data['reg_time']=time();
        $id=$this->Member->insert($data);
        $save['user_no']=getStudentID($id);
        $this->Member->where(array('id'=>$id))->update($save);
        return $id;
    }

    public function isExist($options)
    {
        $result=$this->Member->where($options)->find();
        return $result;
    }
    
    public function setCode2($phone,$code)
    {
        $statusStr = array(
            "0" => "短信发送成功",
            "-1" => "参数不全",
            "-2" => "服务器空间不支持,请确认支持curl或者fsocket，联系您的空间商解决或者更换空间！",
            "30" => "密码错误",
            "40" => "账号不存在",
            "41" => "余额不足",
            "42" => "帐户已过期",
            "43" => "IP地址限制",
            "50" => "内容含有敏感词"
        );
        $smsapi = "http://47.95.217.41:8088/";
        $user = "shmingxin"; //短信平台帐号
        $pass = 'shmingxin123321'; //短信平台密码
        $content="您的验证码为：{$code},请妥善保管";//要发送的短信内容
        $sendurl = $smsapi."sms.aspx?action=send&userid=12&&account=".$user."&password=".$pass."&mobile=".$phone."&content=".$content."&sendTime=&extno=";
        $result =simplexml_load_string(file_get_contents($sendurl)) ;
        return array("msg"=>((String)$result->message),"status"=>((String)$result->returnstatus)=='Success'?0:1);
    }

    private function setCode($phone,$code,$status=0)
    {
        $content = '您的验证码为：'.$code.',请妥善保管';
        $url = 'http://intapi.253.com/send/json';
        $post_data['account']       = 'I4612514';
        $post_data['password']      = 'b8fW1R4HZkd53e';
        $post_data['msg'] = '【洋小秘】'.$content;
        if(substr($phone,0,1)=='1'){
            $mobile = '86'.$phone;
        }else{
            $mobile = $phone;
        }

        $post_data['mobile'] = $mobile;
        $res = request_post($url, $post_data,true);
        if($res['code'] == 0){
            Db::table('s_new_msg')->insert(array('msgid'=>$res['msgid'],'mobile'=>$post_data['mobile'],'code'=>$content,'addtime'=>time()));
        }
        
        return array('msg'=>$res['error']==''?'发送成功':'发送失败','status'=>$res['code']);
    }

    //隔空传
    /**
     * @param $phone 手机号
     * @param $option  一些选项，例如姓名，spopenid,您好，我是XXX，这是我的个人名片https://wap.yxm360.com/pages/share.html?openid=o0pwC0e4lYkyN0VYlDcJmqDl36B8，点击链接即可打开，了解我的一切。【洋小秘】
     * @return array
     *
     */
    public function sendFriendMessage($mobile,$option)
    {
        $statusStr = array(
            "0" => "短信发送成功",
            "-1" => "参数不全",
            "-2" => "服务器空间不支持,请确认支持curl或者fsocket，联系您的空间商解决或者更换空间！",
            "30" => "密码错误",
            "40" => "账号不存在",
            "41" => "余额不足",
            "42" => "帐户已过期",
            "43" => "IP地址限制",
            "50" => "内容含有敏感词"
        );
//        $smsapi = "http://47.94.241.105:9001/";
        $smsapi = "http://47.95.217.41:8088/";
        $user = "shmingxin"; //短信平台帐号
//        $pass = 'shmingxin@123'; //短信平台密码
        $pass = 'shmingxin123321'; //短信平台密码
        $option['nick_name'] = str_replace(' ', '', $option['nick_name']);
//        $content="您好,我是{$option['nick_name']},这是我的个人V网".urlencode("https://wap.yxm360.com/pages/share.html?openid={$option['spopenid']}").",点击链接即可打开了解我的一切";//要发送的短信内容
        $content="您好,我是{$option['nick_name']},点击链接即可打开了解我的一切,这是我的个人V网:".urlencode("http://wx.yxm360.com/index.php?s=/Home/Nologin/card_jump/id/{$option['user_id']}.html");//要发送的短信内容
        $sendurl = $smsapi."sms.aspx?action=send&userid=12&&account=".$user."&password=".$pass."&mobile=".$mobile."&content=".$content."&sendTime=&extno=";
        $result =simplexml_load_string(file_get_contents($sendurl)) ;
        return array("msg"=>((String)$result->message),"status"=>((String)$result->returnstatus)=='Success'?0:1);
    }

    public function SendUserPinCode($options=array())
    {
        //发送验证码
        $array_return=$this->array_return;
        $where['phone']=$options['phone'];
        $where['type']=$options['type'];
        $_code=$this->Code->where($where)->order('id desc')->find();
        if($_code) {
            $time = time();
            if ($_code['expire_time'] >= $time) {
                $array_return['status'] = 0;
                $array_return['msg'] = "您的操作太频繁了，获取失败！";
                return $array_return;
            }
        }
        $data['type']=$options['type'];
        $data['phone']=$options['phone'];
        $data['add_time']=time();
        $data['expire_time']=time()+$this->code_expire_time;
        $data['code']=rand(1000,9999);
        $res=$this->setCode($data['phone'],$data['code']);
        if($res['status']!="0"){
            $array_return['status']=0;
            $array_return['msg']="验证码获取失败,".$res['msg'];
            return $array_return;
        }

        $ret=$this->Code->insert($data);
        if($ret)
        {
            return $this->array_return;
        }else{
            $array_return['status'] = 0;
            $array_return['msg'] = "数据库错误，获取失败！";
            return $array_return;
        }
    }


    public function sendUserGrantCode($options=array())
    {
        //发送验证码
        $array_return=$this->array_return;
        $where['phone']=$options['phone'];
        $where['type']=$options['type'];
        $_code=$this->Code->where($where)->order('id desc')->find();
        if($_code) {
            $time = time();
            if ($_code['expire_time'] >= $time) {
                $array_return['status'] = 0;
                $array_return['msg'] = "您的操作太频繁了，获取失败！";
                return $array_return;
            }
        }

        $data['type']=$options['type'];
        $data['phone']=$options['phone'];
        $data['add_time']=time();
        $data['expire_time']=time()+$this->code_expire_time;
        $data['code']=rand(1000,9999);
        $res=$this->setCodeForGrant($data['phone'],$data['code']);
        if($res['status']!="0")
        {
            $array_return['status']=0;
            $array_return['msg']="验证码获取失败,".$res['msg'];
            return $array_return;
        }
        $ret=$this->Code->insert($data);
        if($ret)
        {
            return $this->array_return;
        }else{
            $array_return['status'] = 0;
            $array_return['msg'] = "数据库错误，获取失败！";
            return $array_return;
        }
    }


    public function setCodeForGrant($phone,$code,$status=0)
    {
        $content = '您的验证码为：'.$code.',用于转赠小秘币，请妥善保管';
        $url = 'http://intapi.253.com/send/json';
        $post_data['account']       = 'I4612514';
        $post_data['password']      = 'b8fW1R4HZkd53e';
        $post_data['msg'] = '【洋小秘】'.$content;
        if(substr($phone,0,1)=='1'){
            $mobile = '86'.$phone;
        }else{
            $mobile = $phone;
        }

        $post_data['mobile'] = $mobile;
        $res = request_post($url, $post_data,true);
        if($res['code'] == 0){
            Db::table('s_new_msg')->insert(array('msgid'=>$res['msgid'],'mobile'=>$post_data['mobile'],'code'=>$content,'addtime'=>time()));
        }
        
        return array('msg'=>$res['error']==''?'发送成功':'发送失败','status'=>$res['code']);
    }


    public function sendAdminLoginCode($options=array())
    {
        //发送验证码
        $array_return=$this->array_return;
        $where['phone']=$options['phone'];
        $where['type']=$options['type'];
        $_code =  $this->Code->where($where)->order('id desc')->find();
        if($_code) {
            $time = time();
            if ($_code['expire_time'] >= $time) {
                $array_return['status'] = 0;
                $array_return['msg'] = "您的操作太频繁了，获取失败！";
                return $array_return;
            }
        }

        $data['type']=$options['type'];
        $data['phone']=$options['phone'];
        $data['add_time']=time();
        $data['expire_time']=time()+$this->code_expire_time;
        $data['code']=rand(1000,9999);
        $res=$this->setCodeForLogin($data['phone'],$data['code']);
        if($res['status']!="0")
        {
            $array_return['status']=0;
            $array_return['msg']="验证码获取失败,".$res['msg'];
            return $array_return;
        }

        $ret=$this->Code->insert($data);
        if($ret)
        {
            return $this->array_return;
        }else{
            $array_return['status'] = 0;
            $array_return['msg'] = "数据库错误，获取失败！";
            return $array_return;
        }
    }


    public function setCodeForLogin($phone,$code,$status=0)
    {
        $content = '您的验证码为：'.$code.',用于管理后台登录，请妥善保管';
        $url = 'http://intapi.253.com/send/json';
        $post_data['account']       = 'I4612514';
        $post_data['password']      = 'b8fW1R4HZkd53e';
        $post_data['msg'] = '【洋小秘】'.$content;
        if(substr($phone,0,1)=='1'){
            $mobile = '86'.$phone;
        }else{
            $mobile = $phone;
        }

        $post_data['mobile'] = $mobile;
        $res = request_post($url, $post_data,true);
        if($res['code'] == 0){
            Db::table('s_new_msg')->insert(array('msgid'=>$res['msgid'],'mobile'=>$post_data['mobile'],'code'=>$content,'addtime'=>time()));
        }
        
        return array('msg'=>$res['error']==''?'发送成功':'发送失败','status'=>$res['code']);
    }

    public function CheckUserPinCode($options=array())
    {
        //判断验证码
        $where['phone']=$options['phone'];
        $where['type']=$options['type'];
        $where['code']=$options['code'];
        $is=$this->Code->where($where)->order('id desc')->find();
        $flag=false;
        $time=time();
        if($is && ($is['expire_time'])>$time)
        {
            $flag=true;
        }
        return $flag;
    }

    //统计当天的条数
    public function code_num($options=array()){

    }
}