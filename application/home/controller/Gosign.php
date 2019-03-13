<?php
/** 报名参加活动的 */
namespace app\home\controller;
use think\Db;

class Gosign
{

    public function att_login()
    {
        if ($this->request->isPost()) { exit; }
        if (empty($_GET['openid'])) {
            header('Location:/getunionid.php?target_url=' . urlencode('http://wx.yxm360.com/Home/Gosign/att_login'));
            exit;
        } else {
            if ($_GET['openid'] == null || $_GET['openid'] == '') exit;
            $this->assign('yxmuseropenid', $_GET['openid']);
        }
        
        return  $this->fetch();
    }
    
    public function att_login_nag()
    {
        if ($this->request->isPost()) {
            exit;
        }
        if (empty($_GET['openid'])) {
            header('Location:/getunionid.php?target_url=' . urlencode('http://wx.yxm360.com/Home/Gosign/att_login_nag'));
            exit;
        } else {
            if ($_GET['openid'] == null || $_GET['openid'] == '') exit;
            $this->assign('yxmuseropenid', $_GET['openid']);
        }
        return  $this->fetch();
    }
    
    public function sign()
    {
        if (empty($_GET['yxmuseropenid'])) {
            $this->error("非法访问报名");
            exit;
        }
        $this->superior_phone = $_GET['superior_phone'];
        $this->yxmuseropenid = $_GET['yxmuseropenid'];
        $this->uuid = $_GET['uuid'];

        return  $this->fetch();
    }

    public function orderlist()
    {
        $this->superior_phone = $_GET['superior_phone'];
        $this->yxmuseropenid = $_GET['yxmuseropenid'];
        return  $this->fetch();
    }

    public function meeting()
    {
        $this->uuid = Input('get.uuid');
        return  $this->fetch();
    }

    public function signsuccess()
    {
        $uuid = Input('get.uuid');
        if (empty($uuid)) {
            $this->error("非法访问报名");
            exit;
        }
        $userinfo = Db::table("s_hk_user")->where(array("uuid" => $uuid, "start" => 2))->
        field("id_username,referee_name,user_top_imgurl,user_idimgurl_fan,
        user_idimgurl_just,user_phone,id_sex,id_number,id_nation,id_dateofbirth,user_qiimgurl_just,id_adress,user_qiimgurl_fan,uuid,age")->find();
        if ($userinfo == null) {
            $this->error("非法访问报名成功");
            exit;
        }
        
        $this->assign('obj',$userinfo);
        return  $this->fetch();
    }

    public function signtwo()
    {
        $uuid = Input('get.uuid');
        $order_id = Input("get.order_id");
        $yxmuseropenid = Input("get.yxmuseropenid");
        if (empty($uuid)) {
            $this->error("非法访问,用户uuid不存在");
            exit;
        }

        $counts = Db::table("s_hk_user")->alias('a')
            ->leftJoin("s_hk_order b","a.order_id=b.id")
            ->field('a.*,b.id as bid,b.s_order_createtime,b.s_order_no,b.s_order_start,b.s_order_userid,b.s_order_price')
            ->where(array("a.uuid" => $uuid))->find();
            
        if ($yxmuseropenid != null && $yxmuseropenid != "") {
            $counts["yxmuseropenid"] = $yxmuseropenid;
        }

        if ($counts["s_order_no"] == null || $counts["s_order_no"] == "" || $counts["s_order_no"] == 0) {
            $order_id = getOrderSn();

            $mydata = array("s_order_no" => $order_id, "s_order_createtime" => date('Y-m-d H:i:s'), "s_order_start" => 1
            , "s_order_userid" => $counts["id"], "s_order_username" => $counts["id_username"]
            , "s_order_price" => $counts["order_pri"], "s_order_openid" => $counts["yxmuseropenid"], "s_order_type" => 1);
            $a = Db::table("s_hk_order")->data($mydata)->insert();
            Db::table("s_hk_user")->where(array("uuid" => $uuid))->update(array("order_id" => $a));
        } else {
            $mydata = array("s_order_userid" => $counts["id"], "s_order_username" => $counts["id_username"]
            , "s_order_price" => $counts["order_pri"], "s_order_openid" => $counts["yxmuseropenid"], "s_order_type" => 1);
            Db::table("s_hk_order")->where(array("s_order_no" => $counts["s_order_no"]))->update($mydata);
            $order_id = $counts["s_order_no"];
        }
        $order = Db::table("s_hk_order")->where(array("s_order_no" => $order_id))->find();
        //判断是否是是微信公众号环境start
        $user_agent = $_SERVER['HTTP_USER_AGENT'];
        if (strpos($user_agent, 'MicroMessenger') === false) {
            // 不是微信浏览器
            $type = 6;
            $is_wechat = 0;
            $openid = "";

        } else {
            $type = 2;
            // 是微信环境
            $is_wechat = 1;
            $openid = $order['s_order_openid'];
            if ($order['s_order_openid'] == '') {
                $this->error("您暂时无法进行支付,openid不存在");
                exit;
            }
        }
        $data = [
            'connect_id' => $order['id'],
            'type' => 2,
            'order_type' => 8,
           'pay_money' => $order['s_order_price'],
            //'pay_money' => 0.01,
            'pay_title' => "香港大会报名费用",
            'pay_stitle' => "香港大会报名费用",
            'pay_tag' => '香港大会报名费用',
            'mark' => "",
            'openid' => $order['s_order_openid'],
            'order_sn' => $order['s_order_no'],
        ];
        $res = get_obj('pay_test')->add_pay_log($data);
        $res = (json_decode($res, true));
        $this->assign('pay_info', $res['obj']);
        $this->assign('order', array("uuid" => $counts["uuid"],
            "order_type" => $counts["order_type"],
            "user_phone" => $counts["user_phone"],
            "order_pri" => $counts["order_pri"],
            "yxmuseropenid" => $counts["yxmuseropenid"],
            "user_qiimgurl_just" => $counts["user_qiimgurl_just"] == '' ? '/Public/Home/images/wh/icon_shenfenzheng@2x.png' : $counts["user_qiimgurl_just"],
            "user_qiimgurl_fan" => $counts["user_qiimgurl_fan"] == "" ? '/Public/Home/images/wh/icon_sfz@2x.png' : $counts["user_qiimgurl_fan"]
        ));

        $this->assign('uuid', $uuid);
        $this->assign('yxmuseropenid', $openid);
        return  $this->fetch();
    }

    // 加入微信
    public function iswx()
    {
        return  $this->fetch();
    }
}