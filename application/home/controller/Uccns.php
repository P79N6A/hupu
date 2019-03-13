<?php
/** 官网*/
namespace app\home\controller;

class UccnsController 
{ 
	//商学院
    public function uccn()
    {
       	$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
        $url = "$protocol$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
       	$this->assign('share_url', $url);
        return $this->fetch();
    }
    
    //洋小秘
    public function yxm()
    {
        $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
        $url = "$protocol$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
       	$this->assign('share_url', $url);
        return $this->fetch();
    }
    
    //发展历程
     public function develop()
     {
        $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
        $url = "$protocol$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
       	$this->assign('share_url', $url);
        return $this->fetch();
     }
     
    //团队风采
     public function tmien()
     {
        $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
        $url = "$protocol$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
       	$this->assign('share_url', $url);
        return $this->fetch();
     }
     
     //公司荣誉
     public function honor()
     {
        $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
        $url = "$protocol$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
       	$this->assign('share_url', $url);
        return $this->fetch();
     }
}

