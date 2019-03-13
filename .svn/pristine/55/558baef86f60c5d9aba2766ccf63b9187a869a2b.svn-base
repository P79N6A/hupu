<?php
/** 报活动报名*/
namespace app\home\controller;
use app\common\controller\HomeBase;
use think\Db;

class Activity extends HomeBase 
{
    // 添加报名
	public function Addactivity()
	{
        $this->assign('title','添加活动');
        return  $this->fetch();
    }

    // 添加表格
    public function Editform()
    {
        $this->assign('title','添加表格');
        return  $this->fetch();
    }

    // 活动首页
    public function EventsPlaza()
    {
        $this->assign('title','活动广场');
        return  $this->fetch();
    }

    // 我的页面
    public function Myform()
    {
        $this->assign('title','我的活动');
        return  $this->fetch();
    }

    // 预览页面
    public function Preform()
    {
        $this->assign('title','预览页面');
        return  $this->fetch();
    }

    //详情预览
    public function Fullpreview()
    {
        $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
        $url = "$protocol$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
        $this->assign('share_url', $url);

        $this->assign('title','详情预览');
        return  $this->fetch();
    }
    
    //test
    public function enterfor()
    {
        $act = Db::table('s_sign_up')->field('title,content')->where(array('id'=>Input('get.id')))->find();
        $contents = cutstr_html($act['content']);
        $contents =str_replace('"', '\'', $contents);
        $this->assign('desc', preg_replace('/\s/is','', $contents));
        $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
        $url = "$protocol$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
        
        $this->assign('share_url', $url);
        $this->assign('title',$act['title']);
        return  $this->fetch();
    }
}
