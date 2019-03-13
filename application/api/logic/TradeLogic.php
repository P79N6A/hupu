<?php
/*
**********
消息列表逻辑层
************ */
namespace app\api\logic;
use think\Model;use app\api\model\Sqtype  as sqtypeModel;

class TradeLogic extends Model
{
	private $Trade = null;
    function __construct()
    {
        $this->Trade= new sqtypeModel();
    }
    public function getSqTypeList($options=array(),$orderBy = '')
    {
        //获取消息列表
        if(empty($options))
        {
            array_push($options,array('parent_id'=>0));
        }
        if(empty($orderBy))
        {
            $orderBy='id';
        }
        $list = $this->Trade->order($orderBy)->where($options)->select();
        return $list;
    }
}