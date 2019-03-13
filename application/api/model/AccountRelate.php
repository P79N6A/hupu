<?php
//用户信息 数据层
namespace app\api\model;
use think\Model;

class AccountRelate extends Model
{
    protected $table = 's_account_relate';
    
 	public function user_info()
    {
        return $this->hasOne('user_info','id','user_id');
    }
}