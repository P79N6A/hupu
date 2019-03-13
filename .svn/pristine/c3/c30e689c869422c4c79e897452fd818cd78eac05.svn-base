<?php
/** 参会签到*/
namespace app\home\controller;
use think\Db;

class Customer
{
    private $unionid;
    public function _initialize()
    {
        $this->unionid = Db::table('s_user_info')->where('id=468')->column('unionid');
        $this->assign('unionid',$this->unionid);
    }
    
    /**
     *登录页面
     */
    public function att_login()
    {
        return $this->fetch();
    }
    
    /**
     *个人中心
     */
    public function center()
    {
        return $this->fetch();
    }

    /**
     * 问题添加页面
     */
    public  function  reception()
    {
        return $this->fetch();
    }

    /**
     * 问题完善页面
     */
    public  function  receptionend()
    {
        $this->rid = Input('get.rid');
        return $this->fetch();
    }

    /**
     * 测试上拉加载页面
     * 不符合要求
     * 存在bug
     */
    public  function  receptionlist()
    {
    	return $this->fetch();
    }
    
    /**
     * 接待分页条件查询也页面
     */
    public  function  receptionlist2()
    {
        $this->isstr = Input('get.isstr');
        return $this->fetch();
    }
    
    /**
     * 接待分页条件查询也页面
     */
    public  function  myreceptionlist()
    {
        $this->isstr = Input('get.isstr');
        return $this->fetch();
    }
    
    /**
     * 问题录入
     * 需要不符合
     * 不太方便
     */
    public  function  posts()
    {
        return $this->fetch();
    }
    
    /**
    *问题录入
     */
    public  function  posts2()
    {
        return $this->fetch();
    }
    
    /**
    	问题录入
     */
    public  function  call()
    {
        return $this->fetch();
    }
    
    /**
    	问题列表
     */
    public  function  calllist()
    {
        return $this->fetch();
    }

    /**
     * 查看接待详情
     */
    public  function  receptiondetails()
    {
        $this->rid = Input('get.rid');
        return $this->fetch();
    }

    /**
     * 问题补充
     */
    public function postsone()
    {
        $this->pid = Input('get.pid');
        return $this->fetch();
    }

    /**
     * 咨询记录
     */
    public  function callltails()
    {
        $this->cid= Input('get.cid');
        return $this->fetch();
    }

    /**
     * 问题list
     */
    public  function postslist()
    {
        $this->cid= Input('get.cid');
        return $this->fetch();
    }

    /**
     * 问题解答
     */
    public  function question()
    {
        $this->postid= Input('get.postid');
        return $this->fetch();
    }
    
    /**
     * 问题解答
     */
    public  function questionone()
    {
        
        $this->postid= Input('get.postid');
        return $this->fetch();
    }
}
