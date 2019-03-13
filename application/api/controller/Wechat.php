<?php
namespace app\api\controller;
use app\common\controller\ApiBase;
use think\Db;
use app\api\logic\Withdraw as withdrawLogic;

class Wechat extends  ApiBase
{
    /**
     * 微信信息
     */
    public function is_wechat()
    {
        $member = Db::table('s_member')->where(array('id' => $this->userInfo['member_id']))->find();
        if(empty($member)){
        	$this->array_return['ResultType'] = self::__ERROR2__;
            $this->array_return['Message'] = "操作失败";
        }
        
        
        if ($member['bind_wechat']) {
            $wxInfo = json_decode($member['bind_wechat'], true);
            $info = array('status' => 1, 'nickname' => $wxInfo['nickname'], 'headimg' => $wxInfo['headimgurl']);
            $this->array_return['ResultType'] = self::__ERROR1__;
            $this->array_return['Message'] = "已绑定";
            $this->array_return['AppendData'] = $info;
        } else {
            $wxInfo = json_decode($member['wx_info'], true);
            $info = array('status' => 0, 'nickname' => $wxInfo['nickname'], 'headimg' => $wxInfo['headimgurl']);
            if ($wxInfo) {
                $this->array_return['ResultType'] = self::__OK__;
                $this->array_return['Message'] = "操作成功";
                $this->array_return['AppendData'] = $info;
            } else {
            	$this->array_return['ResultType'] = self::__ERROR__;
                $this->array_return['Message'] = "未获取到微信信息";
            }
        }
            
        return json($this->array_return);
    }

    /**
     * 绑定微信
     */
    public function bind_wechat()
    {
        $data = Db::table('s_mobile_code')->alias('mc')->leftJoin(' s_member m','m.phone = mc.phone')
            ->where(array('mc.type' => 8, 'm.id' => $this->userInfo['member_id']))
            ->field('mc.code,m.wx_info,m.id')->order('mc.id desc')->find();
            
        if ($data['code'] == $this->_reqparam['code'] && $this->_reqparam['code']) {
            $wxInfo = json_decode($data['wx_info'], true);
            $info = array('status' => 1, 'nickname' => $wxInfo['nickname'], 'headimg' => $wxInfo['headimgurl']);
            if (!$info['nickname']) {
                $this->array_return['ResultType'] = self::__ERROR2__;
                $this->array_return['Message'] = "未获得微信信息";
                $this->array_return['AppendData'] = '';
            } else {
                Db::table('s_member')->where(array('id' => $data['id']))->update(array('bind_wechat' => $data['wx_info']));
                if ($wxInfo) {
                    $this->array_return['ResultType'] = self::__OK__;
                    $this->array_return['Message'] = "绑定成功";
                    $this->array_return['AppendData'] = $info;
                } else {
                    $this->array_return['ResultType'] = self::__ERROR2__;
                    $this->array_return['Message'] = "操作失败";
                }
            }
        } else {
            $this->array_return['ResultType'] = self::__ERROR1__;
            $this->array_return['Message'] = "验证码不对";
        }
        
        return json($this->array_return);
    }

    /**
     * 解绑微信
     */
    public function untie_wechat()
    {
        $res = Db::table('s_member')->where(array('id' => $this->userInfo['member_id']))->update(array('bind_wechat' =>''));
        if ($res) {
            $this->array_return['ResultType'] = self::__OK__;
            $this->array_return['Message'] = "解绑成功";
        } else {
            $this->array_return['ResultType'] = self::__ERROR2__;
            $this->array_return['Message'] = "解绑失败";
        }
        
        return json($this->array_return);
    }

    /**
     * 提现界面
     */
    public function capital_index()
    {
        $last_time = Db::table('s_withdraw')->where(array('user_id'=>$this->userInfo['id'],'status'=>array('neq',3)))->max('add_time');
        if(strtotime(date('Ymd'))<$last_time){
            $this->array_return['ResultType'] = self::__ERROR1__;
            $this->array_return['Message'] = "每人每天只能提现一次";
            return json($this->array_return);
        }
        $banks = array(
            'bank_id'=>'',
            'bank_name'=>'',
            'number'=>'',
            'wx_nickname'=>'',
            'wx_headimg'=>''
        );
       $bank = Db::table('s_users_bank')->field('id,bank_name,number')->where(array('user_id'=>$this->userInfo['id'],'status'=>1))->order('id desc')->find();
       if($bank){
           $banks['bank_id'] = $bank['id'];
           $banks['bank_name'] = $bank['bank_name'];
           $banks['number'] = substr($bank['number'],-4);
       }
       $wx = Db::table('s_member')->field('bind_wechat,capital_pwd')->where(array('id'=>$this->userInfo['member_id']))->find();
       if(!$wx['capital_pwd']){
           $this->array_return['ResultType'] = self::__ERROR__;
           $this->array_return['Message'] = "请设置密码";
           return json($this->array_return);
       }
        if($wx['bind_wechat']){//已绑定微信
            $wxInfo = json_decode($wx['bind_wechat'], true);
            $banks['wx_nickname'] =  $wxInfo['nickname'];
            $banks['wx_headimg'] =  $wxInfo['headimgurl'];
        }
        if($banks){
            $banks['img'] = 'http://'.$_SERVER['HTTP_HOST'].'/Public/Home/images/tixiantongzhi.png';//提现税收图片
            $this->array_return['ResultType'] = self::__OK__;
            $this->array_return['Message'] = "操作成功";
            $this->array_return['AppendData'] = $banks;
        } else {
            $this->array_return['ResultType'] = self::__ERROR2__;
            $this->array_return['Message'] = "操作失败";
        }
        
        return json($this->array_return);
    }
    
    /**
     *提现
     */
    public function capital_out()
    {
        $type = $this->_reqparam['type'];//1微信   2 银行卡
        $member = Db::table('s_member')->field('status,bind_wechat,capital_pwd')->where(array('id'=>$this->userInfo['member_id']))->find();
        if($member['status'] == 2){
            $this->array_return['ResultType'] = self::__ERROR1__;
            $this->array_return['Message'] = "您的账号已被冻结,请联系客服人员";
            return json($this->array_return);
        }
        if(!$this->_reqparam['pwd'] || (md5($this->_reqparam['pwd']) != $member['capital_pwd'])){
            $this->array_return['ResultType'] = self::__ERROR1__;
            $this->array_return['Message'] = "提现密码未填或不正确";
            return json($this->array_return);
        }
        $last_time = Db::table('s_withdraw')->where(array('user_id'=>$this->userInfo['id'],'status'=>array('neq',3)))->max('add_time');
        if(strtotime(date('Ymd'))<$last_time){
            $this->array_return['ResultType'] = self::__ERROR1__;
            $this->array_return['Message'] = "每人每天只能提现一次";
            return json($this->array_return);
        }
        $add = array(
            'user_id'=>$this->userInfo['id'],
            'money'=>$this->_reqparam['money'],
            'ip'=>get_client_ip(),
            'type'=>$type,
            'bank_id' => $type == 2?$this->_reqparam['bank_id'] : 0
        );
        if($type == 2 && $this->_reqparam['bank_id'] == 0){
            $this->array_return['ResultType'] = self::__ERROR1__;
            $this->array_return['Message'] = "请添加银行卡信息";
            return json($this->array_return);
        }
        if($type == 1 && $this->_reqparam['money'] > 2000){//微信提现最多1000
            $this->array_return['ResultType'] = self::__ERROR1__;
            $this->array_return['Message'] = "微信提现最多1000";
            return json($this->array_return);
        }
        if($type != 1 && $type != 2){
            $this->array_return['ResultType'] = self::__ERROR1__;
            $this->array_return['Message'] = "参数有误";
            return json($this->array_return);
        }
         
        $draw_mod = new withdrawLogic();
        $flag = $draw_mod->addData($add);
        if($flag){
            //推送通知
            $openid = json_decode($member['bind_wechat'],true)['openid'];
            if($type == 1){
                $remark = '系统将会在工作时间2小时内审核并发放';
            }else{
                $remark = '系统将会在3个工作日内审核并发放';
            }
            sendWxTemplateMessages('SendPutForward',array( 'wecha_id' => $openid,'href'=>get_current_Host().url('/Home/Income/income'), 'first' => '您已成功申请兑换：'.$add['money'].'小秘币', 'keyword1' =>$this->userInfo['nick_name'], 'keyword2' => date("Y-m-d H:i:s"), 'keyword3'=> '小秘币兑换','remark' => $remark));
            $this->array_return['ResultType'] = self::__OK__;
            $this->array_return['Message'] = "提现成功";
        }else{
            $this->array_return['ResultType'] = self::__ERROR1__;
            $this->array_return['Message'] = "提现失败，余额不足";
        }
        
        return json($this->array_return);
    }
    
    /**
     * 设置支付密码
     */
    public function set_pay_password()
    {
        $pwd = $this->_reqparam['pwd'];
        $re_pwd = $this->_reqparam['re_pwd'];
        $code = $this->_reqparam['code'];
        $data = Db::table('s_mobile_code')->alias('mc')->leftJoin(' s_member m','m.phone = mc.phone')
            ->field('mc.code')->where(array('m.id'=>$this->userInfo['member_id'],'mc.type'=>9))
            ->order('mc.id desc')->find();
        if(($code == $data['code']) && ($pwd == $re_pwd) && $code && $pwd){
            $res = Db::table('s_member')->where(array('id'=>$this->userInfo['member_id']))->update(array('capital_pwd'=>md5($pwd)));
            if ($res) {
                $this->array_return['ResultType'] = self::__OK__;
                $this->array_return['Message'] = "操作成功";
            } else {
                $this->array_return['ResultType'] = self::__ERROR2__;
                $this->array_return['Message'] = "操作失败";
            }
        } else {
            $this->array_return['ResultType'] = self::__ERROR1__;
            $this->array_return['Message'] = "验证码不对或密码不一致";
        }
        
        return json($this->array_return);
    }

}