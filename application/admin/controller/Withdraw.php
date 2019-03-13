<?php

namespace app\admin\controller;

use app\admin\model\UserDetail;
use think\App;
use think\facade\Config;
use think\Db;
use think\Request;

class Withdraw extends Base
{

    public $class;

    public function __construct(App $app = null)
    {
        parent::__construct($app);

        $this->class = getClass(get_class(), 1);

    }

    public function index()
    {
        //        当前类名称
        $class = $this->class;
        $this->assign('class', $class);

        $status = input('status', 5);
        $this->assign('status', $status);

        return $this->fetch();
    }


    public function getList()
    {
        //        分页必备参数
        $Nowpage = input('get.page') ? input('get.page') : 1;
        $limits = input('get.limit');// 获取总条数

        $condition = [];//查询条件

//        1已审核 2审核中 3不通过 4转账中
        $status = input('status');
        if (2 == $status) {
            $order = ['A.type asc,A.id asc'];
        } elseif (5 == $status) {
            $status ='';
            $order = ['A.id desc'];
        } else {
            $order = ['A.id desc'];
        }


        $param = $status;//类别
        if ('' != $param) {
            $condition[] = ['A.status', '=', $param];
        }
        $param = input('nick_name');//类别
        if ('' != $param) {
            $condition[] = ['B.nick_name', 'like', "%{$param}%"];
        }
        $param = input('money');//类别
        if ('' != $param) {
            $condition[] = ['A.money', '=', $param];
        }
        $param = input('type');//类别
        if ('' != $param) {
            if ($param == 3) {
                $condition[] = ['D.bank_name', 'like', '%邮政%'];
            } else {
                $condition[] = ['A.type', '=', $param];
            }
        }

        $start_at = input('start_at');
        $end_at = input('end_at');
        if ('' != $start_at) {
            $start_at = strtotime($param);

            if ('' != $end_at) {
                $end_at = strtotime($end_at);
                $condition[] = array('A.add_time', 'between', [$start_at, $end_at]);
            }
            if ($start_at) {
                $condition[] = array('A.add_time', '>=', $start_at);
            }
        }

        $model = $this->getModelClass();
        $data = $model
            ->alias("A")
            ->join('user_detail B ', ' B.user_id = A.user_id', 'left')
            ->join('user C ', ' C.id = B.user_id', 'left')
            ->join('users_bank D ', ' D.id = A.bank_id', 'left')
            ->order($order)
            ->where($condition)
            ->field('A.*,B.nick_name,C.phone,D.bank_name as bankname,D.name as bname,D.branch_name as branchname,D.number as bnumber');

        $lists = $data->page($Nowpage, $limits)->select();
        $count = $data->count();

        foreach ($lists as $k => $l) {
            $lists[$k]['fee'] = $l['money'] * Config::get('withdraw_rate');
            $lists[$k]['true_money'] = $l['money'] - $lists[$k]['fee'];
            $admin_log = DB::name('admin_log')
                ->alias('A')
                ->join('admin_user au', 'au.user_id = A.admin_id', 'left')
                ->where(array('A.object_id' => $l['id'], 'A.type' => 0))
                ->find();
            $lists[$k]['audit_time'] = $admin_log['addtime'];
            $lists[$k]['admin_name'] = $admin_log['username'];
        }


        return toJson(0, '', $count, $lists);

    }


    public
    function add(Request $request)
    {

        if ($request->isAjax()) {

            $param = $request->param();
            $param['addtime'] = time();

            $model = $this->getModelClass();
            $flag = $model->insertMember(json_encode($param));

            return json(['code' => $flag['code'], 'data' => $flag['data'], 'msg' => $flag['msg']]);
        }

        return $this->fetch();
    }


    public
    function edits()
    {
        $model = $this->getModelClass();
        if (request()->isAjax()) {
            $param = input('post.');

            $flag = $model->editMember($param);

            return json(["code" => $flag["code"], "data" => $flag["data"], "msg" => $flag["msg"]]);
        }
    }


    /**
     * 修改字段
     * @return \think\response\Json
     */
    public
    function editStatus()
    {
        $id = input('param.id');

        $field = input('field', 'status');

        $class = $this->class;
        $table = Db::name($class)->where('id', $id);
        //判断当前状态情况
        $status = $table->value($field);

        if ($status == 1) {
            $flag = $table->setField([$field => 2]);
            return json(['code' => 1, 'data' => $flag, 'msg' => '成功']);

        } else {
            $flag = $table->setField([$field => 1]);
            return json(['code' => 0, 'data' => $flag, 'msg' => 'ok']);

        }

    }

    /**
     * 删除选中的记录
     * @return \think\response\Json
     */
    public
    function delSelecteds()
    {
        $ids = input('ids');
        $class = getClass(get_class());
        return deletes($class, $ids, '');
    }


    /**
     * 删除单个实例
     * @return mixed
     */
    public
    function del()
    {
        $id = input('id');
        $model = $this->getModelClass();
        $flag = $model->del($id, get_class());
        return $flag;
    }


    /**
     * 提现到微信
     */

    public
    function handleWechat()
    {
        $id = input('id');
        if ($id <= 0)
            return json(["code" => 0, "data" => '', "msg" => '参数错误']);

        $status = input('status', 0);
        $withdraw = Db::name('withdraw')->alias('w')
            ->join('user_detail u ', 'u.id = w.user_id')
            ->join('user m ', 'm.id = u.member_id')
            ->field('w.user_id,w.add_time,m.bind_wechat,w.status,w.money')
            ->where(array('w.id' => $id))
            ->find();

        $openid = json_decode($withdraw['bind_wechat'], true)['openid'];
        $fee = Config::get('withdraw_rate');
        $out_money = $withdraw['money'] * (1 - $fee);
        if ($withdraw['status'] == 2 && $openid) {

            $put_forward = new PutForwardController();
            $res = $put_forward->put_forward($openid, $out_money);

            if ($res['result_code'] == 'SUCCESS') {
                $user = Db::name('user_detail')->find($withdraw['user_id']);
                $user['frozen_money'] -= $withdraw['money'];
                $user['already_money'] += $withdraw['money'];

                $result = new  UserDetail();
                $result = $result->save($user, ['user_id' => $withdraw['user_id']]);
                if ($result === false) {
                    return json(["code" => 0, "data" => '', "msg" => '失败']);
                } else {
                    $admin = new \app\admin\model\AdminLog();
                    $admin->adminLog(0, $id, '微信提现审核通过');
                }
                $res = Db::name('withdraw')->where(array('id' => $id))->update(array('status' => $status));
                //保存审核状态
                if ($res) {
                    $this->setMsg($openid, $user['nick_name'], date('Y-m-d H:i:s', $withdraw['add_time']), 0, 1);
                    return json(["code" => 1, "data" => '', "msg" => '操作成功']);

                } else {
                    return json(["code" => 0, "data" => '', "msg" => '转账成功,状态更新失败']);
                }
            } else {
                return json(["code" => 0, "data" => '', "msg" => $res[0]]);
            }
        } else {
            return json(["code" => 0, "data" => '', "msg" => '未绑定微信']);
        }
    }

    public
    function handle()
    {
        $id = input('id');
        if ($id <= 0)
            return json(["code" => 0, "data" => '', "msg" => '参数错误']);

        $status = input('status');
        $withdraw = Db::name('withdraw')->find($id);
        $user = Db::name('user_detail')->find($withdraw['user_id']);

        if ($status == 1) {
            //审核通过
            $user['frozen_money'] -= $withdraw['money'];
            $user['already_money'] += $withdraw['money'];

            $admin = new \app\admin\model\AdminLog();
            $admin->adminLog(0, $id, '微信提现审核通过');
        } elseif ($status == 3) {
            //审核不通过
            $user['frozen_money'] -= $withdraw['money'];
            $user['money'] += $withdraw['money'];

            $admin = new \app\admin\model\AdminLog();
            $admin->adminLog(0, $id, '提现审核不通过');
        }

        $result = new  UserDetail();
        $result = $result->save($user, ['user_id' => $withdraw['user_id']]);
        if ($result === false) {
            return json(["code" => 0, "data" => '', "msg" => '失败']);
        }


        $res = Db::name('withdraw')->where(array('id' => $id))->update(array('status' => $status));

        //保存审核状态
        if ($res !== false) {

            $openid = Db::name('user')->find($user['user_id']);

            if ($openid['openid']) {

                if ($status == 4) {

                    $this->setMsg($openid, $user['nick_name'], date('Y-m-d H:i:s', $withdraw['add_time']));
                } elseif ($status == 3) {

                    $this->setMsg($openid, $user['nick_name'], date('Y-m-d H:i:s', $withdraw['add_time']), 1);
                }
            }

            return json(["code" => 1, "data" => '', "msg" => '操作成功']);

        } else {
            return json(["code" => 0, "data" => '', "msg" => '操作失败']);
        }
    }


    /**
     * 发送短信
     * @param $phone
     * @param $code
     * @return array
     */
    public
    function setMsg($openid, $nick_name, $time, $type = 0, $is_wx = 0)
    {

        if ($type == 0) {//审核通过
            $content = "您的小秘币兑换申请已由银行发放到您绑定的银行卡账户";
            $remark = "24小时内请注意查收";
            if ($is_wx) {
                $content = "您的小秘币兑换申请已发送到您的微信钱包";
                $remark = "2小时内请注意查收";
            }

//            sendWxTemplateMessages('SendPutForward', array('wecha_id' => $openid, 'href' => get_current_Host() . url('/Home/Income/income'), 'first' => $content, 'keyword1' => $nick_name, 'keyword2' => $time, 'keyword3' => '小秘币兑换', 'remark' => $remark));
//        } elseif ($type == 1) {//审核不通过
//            $content = "您的小秘币兑换申请未通过";
//
//            sendWxTemplateMessages('SendPutForward', array('wecha_id' => $openid, 'href' => get_current_Host() . url('/Home/Income/income'), 'first' => $content, 'keyword1' => $nick_name, 'keyword2' => $time, 'keyword3' => '小秘币兑换', 'remark' => '如有疑问请咨询021-64889537'));
        }

    }

}
