<?php
//消息表 数据层
class MsgList extends Model
{
    protected $table = 's_msg_list';
    
    
 	public function user_info()
    {
        return $this->hasOne('user_info','id','object_id');
    }
}