<?php
/**
 * Created by IntelliJ IDEA.
 * User: 14562
 * Date: 2018/8/22
 * Time: 9:42
 */
namespace app\api\controller;
use app\common\controller\ApiBase;
use think\Db;

class Video extends Controller 
{
    const __OK__ = '0'; //请求成功
    const __ERROR__ = '1'; //参数错误
    const __ERROR1__ = '2'; //没有绑定
    const __ERROR2__ = '3';//数据库错误
    private $array_return = array("ResultType"=>1,"Message"=>"success","AppendData"=>"");
    
 	/*
     * 视频列表
     */
    public  function  lists()
    {
        $n = $_REQUEST['n'] ? intval($_REQUEST['n']) : 1;
        $type = $_REQUEST['type'] ? intval($_REQUEST['type']) : 1;
        $pageSize = $_REQUEST['s'] ? intval($_REQUEST['s']) : 8;
        $fields ="a.id,a.name,a.url,a.praise";
        $m = Db::table("s_video");
        $map = array('type_id'=>$type);
        $pageTotal = $m->alias('a')->where($map)->count();

        $list = $m->alias('a')->order('a.type_id desc')->field($fields)->where($map)->limit(($n-1)* $pageSize,$pageSize)->select();
        $datalist = array("list"=>$list,"pageTotal"=>$pageTotal,"pageSize"=>$pageSize,"pageNum"=>$n);
        if ($datalist) {
            $this->array_return['ResultType'] = self::__OK__;
            $this->array_return['Message'] = "操作成功";
            $this->array_return['AppendData'] = $datalist;
        } else {
            $this->array_return['ResultType'] = self::__ERROR2__;
            $this->array_return['Message'] = "操作失败";
        }
        
        return json($this->array_return);
    }
	
    /*
     * 视频分类
     */
    public function category()
    {
        $data = Db::table("s_video_type")->where(array("status"=>1))->select();
        if ($data) {
            $this->array_return['ResultType'] = self::__OK__;
            $this->array_return['Message'] = "操作成功";
            $this->array_return['AppendData'] = $data;
        } else {
            $this->array_return['ResultType'] = self::__ERROR2__;
            $this->array_return['Message'] = "操作失败";
        }
        
        return json($this->array_return);
    }

    /**
     * 视频详情
     */
    public  function  detail()
    {
        $id = $_REQUEST['id'] ? intval($_REQUEST['id']) : 0;
        if (!$id) {
        	return json(['ResultType'=>self::__ERROR__,'Message'=>"缺少参数"]);
        }
        
 		$info = Db::table("s_video")->where(array("id"=>$id))->find();
        if ($info) {
        	$data = Db::table("s_video_rewardlog")->where(array("video_id"=>$id))->select();
            $this->array_return['ResultType'] = self::__OK__;
            $this->array_return['Message'] = "操作成功";
            $this->array_return['AppendData'] = array('info'=>$info,'ListData'=>$data);
        } else {
            $this->array_return['ResultType'] = self::__ERROR2__;
            $this->array_return['Message'] = "操作失败";
        }
        
        return json($this->array_return);
    }

    
    /**
     * 用户点赞
     */
    public function praise()
    {
        $id = $_REQUEST['id'] ? intval($_REQUEST['id']) : 0;
        if (!$id) {
        	return json(['ResultType'=>self::__ERROR__,'Message'=>"缺少参数"]);
        }
        
        $res = Db::table("s_video")->where(array("id"=>$id))->setInc('praise'); ;
        if ($res) {
            $this->array_return['ResultType'] = self::__OK__;
            $this->array_return['Message'] = "操作成功";
        } else {
            $this->array_return['ResultType'] = self::__ERROR2__;
            $this->array_return['Message'] = "操作失败";
        }
        
        return json($this->array_return);
    }
    
    /**
     * 视频打赏
     */
    public function reward()
    {
        $openid = $_REQUEST['openid'] ? $_REQUEST['openid'] : '';
    	if(!$openid) {
            $this->array_return['ResultType'] = self::__ERROR__;
            $this->array_return['Message'] = "非法访问";
            return json($this->array_return);
        }
      	
        $id = $_REQUEST['id'] ? intval($_REQUEST['id']) : 0;
        $money = $_REQUEST['money'] ? intval($_REQUEST['money']) : 0;
    	if(!$money && !$id) {
            $this->array_return['ResultType'] = self::__ERROR__;
            $this->array_return['Message'] = "缺少参数";
            return json($this->array_return);
        }
        
    	//判断是否是是微信公众号环境start
        $user_agent = $_SERVER['HTTP_USER_AGENT'];
        // 判断是不是微信浏览器
        if (strpos($user_agent, 'MicroMessenger') === false) {
            $type = 6;// 支付类型，6是H5支付
            $is_wechat = 0;
        	$source = "浏览器H5";
        } else {
            $type = 2;// 支付类型，2是公众号支付
            $is_wechat = 1;
        	$source = "服务号";
        }
            
        $uid = $_REQUEST['user_id'] ? intval($_REQUEST['user_id']) : 0;
        $nick_name = $_REQUEST['nick_name'] ? $_REQUEST['nick_name'] : '';
        $avatar = $_REQUEST['avatar'] ? $_REQUEST['avatar'] : '';
        $data = array('video_id'=>$id,'user_id'=>$uid,'openid'=>$openid,'nick_name'=>$nick_name,'avatar'=>$avatar,'pay_source'=>$source,'money'=>$money,'add_time'=>time());
        $reward_id = Db::table('s_video_rewardlog')->insert($data);
        if ($reward_id) 
        {	
            $data = [
           		'connect_id'=>$reward_id,
                'type' =>$type,
                'order_type'=>3,
                'pay_money' =>0.01,
                'pay_title'=>"打赏",
                'pay_stitle'=>"打赏",
                'pay_tag'=>'费用',
                'mark'=>json_encode(array('object_id'=>$uid)),
            	'openid'=>$openid,
            ];
                
            $pay_info=get_obj('pay_test')->add_pay_log($data);
            $pay_info=(json_decode($pay_info,true));
            $pay_obj = $pay_info['obj'];
            $pra = $pay_obj['pra'];
            if ($is_wechat == 1){
           		$pra=(json_decode($pra,true));     
            }

            $pra['source'] = $is_wechat;
            if ($pra['mweb_url'] !=null){
           		$mweb_url = $pay_info['obj']['pra']['mweb_url'];
                $pra['mweb_url'] = $mweb_url;
            }

            $this->array_return['data'] = $pra;
            $this->array_return['ResultType'] = self::__OK__;
            $this->array_return['Message'] = "操作成功";
        } else {
            $this->array_return['ResultType'] = self::__ERROR2__;
            $this->array_return['Message'] = "发起支付失败";
        }
        
        return json($this->array_return);
    }
    
}