<?php
/*
**********
浏览记录逻辑层
************
 */
namespace app\api\logic;
use think\Model;
use app\api\model\UserInfo  as userModel;
use app\api\model\Member  as memberModel;
use app\api\model\VisitLog  as visitModel;

class VisitLog extends Model
{
	
    private $User = null;
    private $VisitLog = null;
    function __construct()
    {
        $this->User = new userModel();
        $this->VisitLog = new visitModel();
    }
    
    public function addVisitLog($options=array())
    {
        //浏览记录添加
        $data=array();
        $data['object_id']=$options['object_id'];
        if (isset($options['user_id']))
        {
            $data['user_id']=$options['user_id'];
        }
        if (isset($options['ip']))
        {
            $data['ip']=$options['ip'];
        }
        $data['is_follow'] = 0;
        $data['is_share'] = 0;
        $data['add_time'] = time();
        $id = $this->VisitLog->insert($data);
        return $id;
    }
    
    public function isRepeat($options=array())
    {
        //判断是否重复
        if(isset($options['user_id'])){
            $where['user_id']=$options['user_id'];
        }
        if(isset($options['ip'])){
            $where['ip']=$options['ip'];
        }
        $where['object_id']=$options['user_id'];
        $time=strtotime(date('Y-m-d',time()));
        $where['_string']='add_time>'.$time.' and add_time<='.strtotime("+1 day",$time);
        if(isset($options['is_follow'])){
            $where['is_follow']=$options['is_follow'];
        }
        if(isset($options['is_share'])){
            $where['is_share']=$options['is_share'];
        }
        $is=$this->VisitLog->where($where)->find();
        return $is;
    }
    
    public function upShare($options=array())
    {
        //浏览记录分享更新
        $this->VisitLog->where(array('id'=>$options['id']))->update(array('is_share'=>1));
    }
    
    public function getLogData($options=array())
    {
        //获取浏览记录数据
        $data=array();
        $where['object_id']=$options['object_id'];
        $data['count']=$this->VisitLog->where($where)->count();//总浏览
        $time=strtotime(date('Y-m-d',time()));
        $where['_string']='add_time>'.strtotime("-1 day",$time).' and add_time<='.$time;
        $data['yesterday_count']=$this->VisitLog->where($where)->count();//昨天浏览
        $where['_string']='add_time>'.$time.' and add_time<='.strtotime("+1 day",$time);
        $data['today_count']=$this->VisitLog->where($where)->count();//今天浏览
        unset($where['_string']);
        $where['is_follow']=1;
        $data['share_count']=$this->VisitLog->where($where)->count();//分享次数
        $data['share_count']=empty($data['share_count'])?0:$data['share_count'];
        unset($where['is_follow']);
        $where['_string']='add_time>'.strtotime("-6 day",$time);
        //print_r($where);die();
        $res=$this->VisitLog->field("FROM_UNIXTIME(add_time, '%Y-%m-%d') as daytime")->where($where)->group("daytime")->select();
        foreach ($res as $key => $value) {
            $where=array();
            $daytime = $value['daytime'];
            unset($value['daytime']);
            $time=strtotime($daytime);
            $end_time=strtotime('+1 day',$time)-1;
            $where['object_id']=$options['object_id'];
            $where['_string']="add_time>".$time." and add_time<=".$end_time;
            
            $_data['count']=$this->VisitLog->where($where)->count();//浏览次数
            $where['ip']=0;
            $user_count=$this->VisitLog->field('user_id')->where($where)->group("user_id")->select();
            $user_count=count($user_count);
           
            unset($where['ip']);
            $where['user_id']=0;
            $ip_count=$this->VisitLog->field('ip')->where($where)->group("ip")->select();
            $ip_count=count($ip_count);
            unset($where['user_id']);
            $_data['count_2']=$user_count+$ip_count;//独立访客
            $where['is_follow']=1;
            $_data['share_count']=$this->VisitLog->where($where)->group('user_id,ip')->count();//关注人数
            $_data['share_count']=empty($_data['share_count'])?0:$_data['share_count'];
            $res[$key]['data']=array();
 
            $res[$key]['add_time'] = $daytime;
            $res[$key]['data']=$_data;
        }

        $data['data']=$res;
        return $data;
    }

}