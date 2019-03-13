<?php
namespace app\api\controller;
use app\common\controller\ApiBase;
use think\Db;
use app\api\Model\UserView  as uviewLogic;
use app\api\logic\Cards  as cardsLogic;

class Card extends  ApiBase 
{
	private $uview = null;
	private $cards = null;
	
    function __construct()
    {
    	
        $this->uview = new uviewLogic();
        $this->cards = new cardsLogic();
    }
    
    /**
     *  搜索V网
     */
    public function search_card()
    {
        if($this->request->isPost())
        {
        	$res = $this->request->post();
            $where['is_copy_cards']=1;
            if($res['keyword']){
                $where['_string']='phone LIKE "%'.$res['keyword'].'%" or nick_name LIKE "%'.$res['keyword'].'%"';
                $data=$this->uview->where($where)->order('id desc')->limit(100)->select();
            }else{
                $data = Db::table('s_user_detail')->alias('u')
                    ->join('inner join s_visit_log v','v.user_id = u.id')
                    ->leftJoin(' s_member m','m.id = u.member_id')
                    ->where(array('u.is_copy_cards'=>1))->group('u.id')
                    ->field('u.id,u.headimg,u.nick_name,u.unionid,m.phone,count(u.id) as cnt')
                    ->order('cnt desc')->limit(100)->select();
            }
            
            $data_arr =array('status'=>1,'msg'=>'success','data'=>$data,'count'=>empty($data)?0:count($data));
            $this->array_return['ResultType'] = self::__OK__;
            $this->array_return['Message'] = "操作成功";
            $this->array_return['AppendData'] = $data_arr;

            return json($this->array_return);
        }else{
            return json(['ResultType'=>self::__ERROR__,'Message'=>"请求类型错误"]);
        }
    }

    public function get_card_wallet()
    {
        if($this->request->isPost())
        {
            $where['keyword'] = input('post.keyword')?input('post.keyword'):'';
            $where['user_id'] = $this->userInfo['id'];
            $list = $this->cards->getCardsForAPP($where);
            $this->array_return['ResultType'] = self::__OK__;
            $this->array_return['Message'] = "操作成功";
            $this->array_return['AppendData'] = $list;
            return json($this->array_return);
        }else{
            return json(['ResultType'=>self::__ERROR__,'Message'=>"请求类型错误"]);
        }
    }
    
    public function del_card_linkman()
    {
        if($this->request->isPost()){
           $card_id = input('post.card_id');
           $card_id = explode(',',$card_id);//以,隔开的id字符串
           if (!$card_id){
               return json(['ResultType'=>self::__ERROR__,'Message'=>"缺少参数"]);
           }

           $where['user_id'] = $this->userInfo['id'];
           $where['id'] = array('in',$card_id);
           $res =  Db::table('s_cards')->where($where)->delete();
           if ($res)
           {
                $this->array_return['ResultType'] = self::__OK__;
                $this->array_return['Message'] = "操作成功";
                $this->array_return['AppendData'] = "OK";
            }else{
                $this->array_return['ResultType'] = self::__ERROR4__;
                $this->array_return['Message'] = "操作成功";
                $this->array_return['AppendData'] = "ERROR";
            }
            return json($this->array_return);
        }else{
            return json(['ResultType'=>self::__ERROR__,'Message'=>"请求类型错误"]);
        }
    }
}