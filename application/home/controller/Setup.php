<?php
/** 新V网首页*/
namespace app\home\controller;
use app\common\controller\HomeBase;

class Setup extends HomeBase
{
	// 新隐私保护  
    public function privacy()
    {
        return  $this->fetch();
    }
}