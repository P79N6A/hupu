<?php
namespace app\api\model;
use think\Model;

class UserView extends Model
{
	public $viewFields  = array(
        'UserInfo'      => array('_table'=>'s_user_detail','*','_type'=>'LEFT'),
        'Member'    => array('_table'=>'s_member','phone','_on'=>'UserInfo.member_id = Member.id','_type'=>'LEFT'),
    );
}

