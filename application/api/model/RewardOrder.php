<?php//购买列表 数据层namespace app\api\model;use think\Model;class RewardOrder extends Model{    protected $table = 's_reward_order';    public function createRewardOrder($reward_user_id,$user_id,$article_id,$money,$source)    {        $order_data['order_no'] = getRewardSn();        $order_data['reward_user_id'] = $reward_user_id;        $order_data['to_user_id'] = $user_id;        $order_data['article_id'] = $article_id;        $order_data['money'] = $money;        $order_data['pay_status'] = 0;        $order_data['create_time'] = time();        $order_data['pay_source'] = $source;        $reward_order_id = $this->insert($order_data);        return $reward_order_id;    }    }