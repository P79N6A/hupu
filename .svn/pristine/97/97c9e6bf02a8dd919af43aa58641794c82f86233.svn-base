<?php
//用户信息 数据层
namespace app\api\model;
use think\Model;

class ApiResourcesOne extends Model
{

    protected $table = 's_api_resources';
    protected $_link = array(
        'two' => array(
            'mapping_type'      =>  self::HAS_MANY,
            'foreign_key'       => 'r_group_paterid',
            'class_name'    => 'ApiResourcesTwo',
            'mapping_key'         => 'r_id',
            'mapping_fields'=>'r_id,r_name,r_url,r_img,r_sort,r_describe'
        ),
    );

}