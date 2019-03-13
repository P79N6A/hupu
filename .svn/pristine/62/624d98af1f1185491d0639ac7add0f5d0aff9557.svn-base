<?php

namespace app\admin\controller;

use app\admin\model\VotePrize;
use app\admin\model\VoteUser;
use think\Db;
use think\Request;

/**
 * 签到管理Controller
 */
class Vote  extends Base
{

    /**
     * 奖品管理
     */
    public function prize_management() {
        return $this->fetch();
    }
    /**
     * 奖品管理
     */
    public function lecturer_management_to() {


        return $this->fetch();
    }

    /**
     * 中奖管理
     */
    public function winning_management() {
        return $this->fetch();
    }
    /**
     * 讲师管理
     */
    public function lecturer_management(){
        return $this->fetch();
    }


    /**
     * 分页得到所有
     */
    public function  prizePage()
    {

        //        分页必备参数
        $Nowpage = input('get.page') ? input('get.page') : 1;
        $limits = input('get.limit');// 获取总条数

        $condition = [];//查询条件

        $data = Db::table("s_vote_prize")->order('pprobability desc');

        $lists = $data->page($Nowpage, $limits)->select();
        $count = $data->count();
        return toJson(0, '', $count, $lists);
    }



    /**
     * 分页查找投票用户
     */
    public  function  voteUsers(){

        //        分页必备参数
        $Nowpage = input('get.page') ? input('get.page') : 1;
        $limits = input('get.limit');// 获取总条数
        $condition = [];//查询条件

        $param = input('dname');//类别
        if ('' != $param) {
            $condition[] = ['C.dname','like',"%{$param}%"];
        }

        $data = Db::name("vote_record")
            ->alias('A')
            ->join('vote_recored_user B','B.yxmopenid=A.yxmopenid','left')
            ->join('vote_user C','C.uuuid=A.vuuid')
            ->field('C.uname,C.uuuid,A.createtime,B.nickname,B.headimgurl')
            ->where($condition);

        $lists = $data->page($Nowpage, $limits)->select();
        $count = $data->count();
        return toJson(0, '', $count, $lists);


    }


    /**
     * 分页查找投票用户
     */
    public  function  voteTeacher(){


        //        分页必备参数
        $Nowpage = input('get.page') ? input('get.page') : 1;
        $limits = input('get.limit');// 获取总条数
        $condition = [];//查询条件

        $param = input('dname');//类别
        if ('' != $param) {
            $condition[] = ['C.dname','like',"%{$param}%"];
        }

        $param = input('openid');//类别
        if ('' != $param) {
            $condition1[] = ['createtime','egt',date('Y-m-d 00:00:00')];
            $condition1[] = ['yxmopenid','=','openid'];
            $vuuids= Db::name("vote_record")->where($condition1)->field("vuuid")->select();

        }
        if(empty($res["isfalg"])){
            $fields="a.uname,a.u_vote_num,a.u_img_url,a.u_img_top,a.uuuid,a.u_number,a.u_sort,a.u_number";
        }else{
            $fields="a.uid,a.uname,a.u_vote_num,a.u_img_url,a.u_img_top,a.uuuid,a.u_number,a.u_sort,a.createtime,u_sharing_audio,u_sharing_title
           ,u_sharing_dis";
        }


        $data = Db::name("vote_user")
            ->alias('A')
//            ->join('vote_recored_user B','B.yxmopenid=A.yxmopenid','left')
//            ->join('vote_user C','C.uuuid=A.vuuid')
//            ->field('C.uname,C.uuuid,A.createtime,B.nickname,B.headimgurl')
            ->order('A.u_vote_num desc')
            ->where($condition);

        $lists = $data->page($Nowpage, $limits)->select();
        $count = $data->count();
        return toJson(0, '', $count, $lists);

    }



    public function editsPrize()
    {
        $model = new VotePrize();
        if (request()->isAjax()) {
            $param = input('post.');

            $flag = $model->editMember($param);

            return json(["code" => $flag["code"], "data" => $flag["data"], "msg" => $flag["msg"]]);
        }
    }
    public function editsVote()
    {
        $model = new VoteUser();
        if (request()->isAjax()) {
            $param = input('post.');

            $flag = $model->editMember($param);

            return json(["code" => $flag["code"], "data" => $flag["data"], "msg" => $flag["msg"]]);
        }
    }

    /**
     * 新增和修改商品
     */
    public function i_u_vote_prize()
    {
        $res = $this->request->post();
        if(empty($res["pid"])){
            $re = Db::table("s_vote_prize")->insert($res);
        }else{
            $re = Db::table("s_vote_prize")->where(array("pid"=>$res["pid"]))->save($res);
        }
        if ($re) {
            $array_return['ResultType'] = 1;
            $array_return['Message'] = "操作成功";
            $array_return['AppendData'] = $re;
        } else {
            $array_return['ResultType'] = 3;
            $array_return['Message'] = "操作失败";
        }

        return json($array_return);
    }


    public function voteAdd(Request $request)
    {

        if ($request->isAjax()) {

            $param = $request->param();
//            $param['addtime'] = time();

            $model = new VotePrize();
            $flag = $model->insertMember(json_encode($param));

            return json(['code' => $flag['code'], 'data' => $flag['data'], 'msg' => $flag['msg']]);
        }


        return $this->fetch();
    }
    public function voteUserAdd(Request $request)
    {
        if ($request->isAjax()) {

            $param = $request->param();
            $param['createtime'] = date('Y-m-d H:i:s');
            $param['u_sharing_dis'] = '  ';

            $model = new VoteUser();
            $flag = $model->insertMember(json_encode($param));

            return json(['code' => $flag['code'], 'data' => $flag['data'], 'msg' => $flag['msg']]);
        }


        return $this->fetch();
    }


    /**
     * 图片上传
     */
    public function uploads_img()
    {
        if ($_FILES['file'] != null) {

            $upload = new Upload();
            $img = $upload->uploadImage();
            $array_return['ResultType'] = 1;
            $array_return['Message'] = "操作成功";
            $array_return['AppendData'] = $img;
        }else{
            $array_return['ResultType'] = 3;
            $array_return['Message'] = "缺少参数";
        }

        return json($array_return);
    }




    /**
     * 删除选中的记录
     * @return \think\response\Json
     */
    public function delSelecteds()
    {
        $ids = input('ids');
        return deletes('vote_prize', $ids, 'pid');
    }


    /**
     * 删除单个实例
     * @return mixed
     */
    public function del()
    {
        $id = input('id');

        $flag = deletes('vote_prize', $id, 'pid');
        return $flag;
    }

    /**
     * 删除单个实例
     * @return mixed
     */
    public function delUser()
    {
        $id = input('id');

        if(input('ids')){
            $id = input('ids');
        }

        $flag = deletes('vote_user', $id, 'uid');
        return $flag;
    }

}