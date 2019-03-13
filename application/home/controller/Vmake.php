<?php
/** 新V网首页*/
namespace app\home\controller;
use app\common\controller\HomeBase;

class Vmake extends HomeBase 
{

    /**
     *新V网
     */
    public function vmakee()
    {
        return  $this->fetch();
    }

    public function editmake()
    {
        return  $this->fetch();
    }

    public function changexmb()
    {
        return  $this->fetch();
    }
}