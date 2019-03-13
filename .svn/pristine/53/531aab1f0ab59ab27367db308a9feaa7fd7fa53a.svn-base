<?php
//用户信息 数据层
namespace app\api\model;
use think\Model;

class KPosts extends Model
{
    protected $table = 's_k_posts';
    // 关系映射与hibernate sql 优化不高，局限
    protected $_link = array(
        'k_posts_one' => array(
            'mapping_type'      =>  self::HAS_MANY,
            'class_name'    => 'KPostsOne',
            'foreign_key'       => 'pid',
            'mapping_key'         => 'pid'
        ),
    );
}