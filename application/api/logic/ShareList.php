<?php
/*
**********
分享逻辑层
************
 */
namespace app\api\logic;
use think\Db;
use think\Model;

class ShareList extends Model
{
    private $pageNum=10;

    public function getShareList($options=array())
    {
        //获取共享活动列表
        $where = array();
        $where['expire_time'] = array('gt',time());
        if(!empty($options['page'])){
            if(empty($options['pageNum'])){
                $options['pageNum']=$this->pageNum;
            }
            $options['limit']=($options['page']-1)*$options['pageNum'].",".$options['pageNum'];
        }

        $where['expire_time'] = array('gt',time());
        $list = Db::table('s_share_list')->alias('s')->leftJoin(' s_user_detail u','u.id = s.user_id')
            ->field('s.id,s.user_id,s.amount,s.count,s.share_user_count,s.activity_id,s.activity_url,s.expire_time,s.cover_img,s.content,s.single_amount,s.clicked_count,u.nick_name,u.headimg')
            ->where($where)->where('s.count > s.clicked_count')->order('s.id DESC')->limit($options['limit'])->select();
            
        return $list ? $list : false;
    }

    public function getMyPublishList($options=array())
    {
        //获取共享活动列表
        $where = array();
        if(!empty($options['page'])){
            if(empty($options['pageNum'])){
                $options['pageNum']=$this->pageNum;
            }
            $options['limit']=($options['page']-1)*$options['pageNum'].",".$options['pageNum'];
        }

        $where['user_id'] = $options['user_id'];
        $list = Db::table('s_share_list')
            ->field('id,amount,count,share_user_count,activity_id,activity_url,expire_time,cover_img,content,single_amount,clicked_count,`status`')
            ->where($where)->order('id DESC')->limit($options['limit'])->select();

        return $list ? $list : false;
    }

    public function getMyShareList($options=array())
    {
        //获取共享活动列表
        $where = array();
        if(!empty($options['page'])){
            if(empty($options['pageNum'])){
                $options['pageNum']=$this->pageNum;
            }
            $options['limit']=($options['page']-1)*$options['pageNum'].",".$options['pageNum'];
        }

        $where['u.share_user_id'] = $options['user_id'];
        $list = Db::table('s_share_user_list')->alias('u')
            ->leftJoin(' s_share_list s','u.share_id = s.id')
            ->field('u.share_id,u.share_user_id,u.total_money,s.amount,s.count,s.share_user_count,s.activity_id,s.activity_url,s.expire_time,s.content,s.single_amount,s.clicked_count,s.title,s.cover_img,s.content')
            ->where($where)->order('s.id DESC')->limit($options['limit'])->select();

        return $list ? $list : false;
    }

    public function getMyPayDetail($options=array())
    {
        //获取共享活动列表
        $where = array();
        if(!empty($options['page'])){
            if(empty($options['pageNum'])){
                $options['pageNum']=$this->pageNum;
            }
            $options['limit']=($options['page']-1)*$options['pageNum'].",".$options['pageNum'];
        }

        $where['l.share_user_id'] = $options['share_user_id'];
        $where['l.share_id'] = $options['share_id'];
        $list = Db::table('s_share_log')->alias('l')
            ->leftJoin(' s_share_list s','s.id = l.share_id')
            ->field('l.money,l.create_time,l.browser_nickname,l.browser_headimg,s.activity_id')
            ->where($where)->order('l.id DESC')->limit($options['limit'])->select();

        if ($list){
            foreach ($list as $key=>$value){
                if ($value['browser_nickname'] == ""){
                    $list[$key]['browser_nickname'] = "哈哈";
                }

                if ($value['browser_headimg'] == ""){
                    $list[$key]['browser_headimg'] = "http://oss.mingxin001.com/Share/2018-08-29/5b860cbd000c1.png@!protected";
                }
            }

            return $list;
        }else{
            return false;
        }
    }

    public function getMyActivityPayDetail($options=array())
    {
        //获取共享活动列表
        $where = array();
        if(!empty($options['page'])){
            if(empty($options['pageNum'])){
                $options['pageNum']=$this->pageNum;
            }
            $options['limit']=($options['page']-1)*$options['pageNum'].",".$options['pageNum'];
        }

        $where['u.share_id'] = $options['share_id'];
        $list = Db::table('s_share_user_list')->alias('u')
            ->leftJoin(' s_user_detail i','i.id = u.share_user_id')
            ->field('u.total_money,u.create_time,i.nick_name,i.headimg,i.id')
            ->where($where)->order('u.id DESC')->limit($options['limit'])->select();

		return $list ? $list : false;
    }
}