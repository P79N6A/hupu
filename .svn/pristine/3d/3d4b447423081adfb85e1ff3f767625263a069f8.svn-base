<?php
//用户信息 数据层
namespace app\api\model;
use think\Model;

class KCustomerService extends Model
{

    protected $table = 's_k_customer_service';
    // 关系映射与hibernate sql 优化不高，局限
    protected $_link = array(
        'k_customer_log' => array(
            'mapping_type'      =>  self::HAS_MANY,
            'class_name'    => 'KCustomerLog',
            'foreign_key'       => 'sid',
            'mapping_key'         => 'sid',
            'condition'=>'datediff(current_timestamp,createtime)=0'//'DateDiff(dd,createtime,getdate())=0'//datediff(current_timestamp,createtime)<=1
        ),
    );
}