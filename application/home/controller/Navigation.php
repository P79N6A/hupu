<?php
/** 栏目管理 */namespace app\home\controller;use app\common\controller\HomeBase;
class Navigation extends HomeBase
{
    /**
     * 添加栏目
     */
    public function addNavigation()
    {
        return $this->fetch();
    }
}