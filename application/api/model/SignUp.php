<?php
// vip订单列表 数据层namespace app\api\model;use think\Model;
class SignUp  extends Model
{
    protected $table = 's_sign_up';
    
	public function detail()
    {
        return $this->hasOne('sign_detail','sign_id')->field('title');
    }
    
	public function user()
    {
        return $this->hasOne('user_info','id','user_id')->field('id,nick_name,headimg');
    }
}