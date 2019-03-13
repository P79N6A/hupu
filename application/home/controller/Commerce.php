<?php
/** 商会管理 */
namespace app\home\controller;
use app\common\controller\HomeBase;
class Commerce extends HomeBase{
    /**
     * 我的
     */
    public function mine()
    {
        return $this->fetch();
    }
    /**
     * 商会（列表）
     */
    public function commerceList()
    {
        return $this->fetch();
    }
    /**
     * 商会动态（列表）
     */
    public function moments()
    {
        return $this->fetch();
    }
    /**
     * 动态2（列表）
     */
    public function moments2()
    {
        return $this->fetch();
    }
    /**
     * 消息（列表）
     */
    public function message()
    {
        return $this->fetch();
    }
    /**
     * 商会二维码
     */
   	public function qrCode()    {
        return $this->fetch();
    }
    /**
     * 商会介绍
     */
    public function info()    {
        return $this->fetch();
    }
    /**
     *  创建商会
     */
    public function create()    {
        return $this->fetch();
    }
    /**
     *  商会管理
     */
    public function regulate()    {
        return $this->fetch();
    }
    /**
     *  商会成员管理
     */
    public function member()    {
        return $this->fetch();
    }
    /**
     *  商会广告管理
     */
    public function advert()    {
        return $this->fetch();
    }
}