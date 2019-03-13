<?php

namespace app\home\controller;
use app\common\controller\Base;

class Android extends Base
{

    //新版4.0相册编辑页面
    public function photo_edit()
    {
        $this->assign('unionid',Input('get.unionid',0));//用户unionID
        $this->assign('preview',Input('get.preview',0));//相册id

        return  $this->fetch();
    }
    
    // 相册排序页面
    public function photo_sort()
    {
        $this->title="此模板所有类型";
        $this->assign('id',Input('get.unionid',0));//用户unionid
        return  $this->fetch();
    }

    // 选择音乐
    public function photo_music()
    {
        $this->assign('unionid',Input('get.unionid',0));//用户unionid
        return  $this->fetch();
    }
    
    //添加模板
    public function all()
    {
        $this->title="此模板所有类型";
        $this->assign('id',Input('get.unionid',0));//用户unionid

        $this->sid=Input('get.sid',0);
        $this->mids=Input('get.mids',0);
        return  $this->fetch();
    }
    
    //相册分享页面
    public function share_msg()
    {
        $this->title="此模板所有类型";
        $this->assign('id',Input('get.unionid',0));//用户unionid
        return  $this->fetch();
    }
    
    // 新版4.0贺卡贺卡编辑页
    public function greet_edit()
    {
        $this->assign('id',Input('get.user_id',0));//用户id
        $this->assign('unionid',Input('get.unionid',0));//用户unionID
        return  $this->fetch();
    }
    
    // 新版4.0贺卡贺卡选择音乐
    public function greet_music()
    {
        $this->assign('id',Input('get.user_id',0));//用户id
        $this->assign('unionid',Input('get.unionid',0));//用户unionID
        return  $this->fetch();
    }
    
    // 设置分享
    public function greet_share()
    {
        $this->assign('id',Input('get.user_id',0));//用户id
        $this->assign('unionid',Input('get.unionid',0));//用户unionID
        return  $this->fetch();
    }
}