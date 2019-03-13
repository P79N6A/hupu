<?php
/**
 *  代理商系统
 * @javatom
 * @王浩
 * 2018-10-23
 * 控制类
 */
namespace app\home\controller;

class WapAgent
{
    /**
     * 代理申请页面
     */
    public function Agent_open()
    {
        $this->pojouuid = Input('get.pojouuid');
        return $this->fetch();
    }
    
    /**
     * 代理升级页面
     */
    public function Agent_open_goup()
    {
        $this->pojouuid = Input('get.pojouuid');
        return $this->fetch();
    }
    
    /**
     * 代理升级页面
     */
    public function Agent_open_update()
    {
        $this->pojouuid = Input('get.pojouuid');
        return $this->fetch();
    }
    
    /**
     * 我的代理页面
     */
    public function AgentList()
    {
        return $this->fetch();
    }
    
    public function Agent_show()
    {
        $this->pojouuid = Input('get.pojouuid');
        return $this->fetch();
    }

    public function sign()
    {
        return $this->fetch();
    }

    public function passreset()
    {
        return $this->fetch();
    }

    public function retrievepass()
    {
        return $this->fetch();
    }

    public function vip_center()
    {
        return $this->fetch();
    }

}