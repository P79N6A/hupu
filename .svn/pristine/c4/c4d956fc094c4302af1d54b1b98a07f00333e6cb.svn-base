<?php
/** 选择模板 */
namespace app\home\controller;
use app\common\controller\HomeBase;

class Selecttemplate extends HomeBase
{

	public function  choicetemplate()
	{
        $this->title="选择模板";
        return  $this->fetch();    
    }
        
    public function  templatelist()
    {
        $this->title="选择模板";
        return  $this->fetch();        
    }

     // 已编辑模板
     public function  all_template()
     {
        $this->id=$this->userInfo['id'];
        $this->title="已编辑模板";
        return  $this->fetch();
            
     }

     // 编辑模板海报秀和锁屏壁纸
     public function  edit_template()
     {
        $this->id=$this->userInfo['id'];
        $this->title="编辑";
        return  $this->fetch();
            
     }

     // 编辑模板相册和贺卡
     public function  edit_photocard()
     {
        $this->id=$this->userInfo['id'];
        $this->title="编辑";
        return  $this->fetch();       
     }
}