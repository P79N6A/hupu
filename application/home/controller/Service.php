<?php
/** 新V网首页*/
namespace app\home\controller;
use app\common\controller\HomeBase;

class Service extends HomeBase
{
    // 访客统计
    public function guest()
    {
        return  $this->fetch();
    }  
    
    // 交换V网
    public function exchangev()
    {
        return  $this->fetch();
    }

    // 点赞与收藏
    public function pration()
    {
        return  $this->fetch();
    } 
     
    // 我消息
    public function comments()
    {
        return  $this->fetch();
    } 
}