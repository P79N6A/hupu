<?php
/** 贺卡管理 */
namespace app\home\controller;
use app\common\controller\HomeBase;
use think\Db;

class Poster extends HomeBase
{
    /**
     * 海报
     */
    public function index()
    {
        $this->id=$this->userInfo['id'];
        $cids = Db::table('s_poster_type')->order('sort asc')->column('id');
        $this->cids=$_GET['cids']?$_GET['cids']:$cids;
        $this->title="海报秀";
        return $this->fetch();
    }
    
	public function coldfusion()
	{
        $this->id=$this->userInfo['id'];
        $this->ids=$_GET['id'];
        $this->tid=$_GET['tid'];
        return $this->fetch();
	}
	
  	public function amend()
  	{
        $this->id=$this->userInfo['id'];
        $this->ids=$_GET['id'];
        $this->tid=$_GET['tid'];
        return $this->fetch();
    }
    
    public function newamend()
    {
        $this->id=$this->userInfo['id'];
        $this->ids=$_GET['id'];
        $this->tid=$_GET['tid'];
        return $this->fetch();
	}
	
	public function wallpaper()
	{
		$this->title="锁屏壁纸";
        $this->id=$this->userInfo['id'];
        $this->ids=$_GET['id']; 
        return $this->fetch();
	}
	
	public function preview()
	{
        $this->id=$this->userInfo['id'];
        $this->ids=$_GET['id']; 
        $this->myid=$_GET['myid']; 
        return $this->fetch();
    }
        
    // 锁屏壁纸一级页面
    public function lockwallpaper()
    {
        $this->id=$this->userInfo['id'];
        return $this->fetch();
    }

    // 锁屏壁纸二级预览
    public function lockpreview()
    {
        $this->id=$this->userInfo['id'];
        return $this->fetch();
    }

    // 锁屏壁纸二级预览
    public function previewsava()
    {
        $this->id=$this->userInfo['id'];
        return $this->fetch();
    }

    // 海报秀首页
    public function poster_index()
    {
        $this->id=$this->userInfo['id'];
        return $this->fetch();
    }
           
     // 海报秀编辑页
    public function poster_edit()
    {
     	$this->id=$this->userInfo['id'];
        return $this->fetch();
    }
} 
