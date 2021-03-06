<?php
/** 商脉管理 */namespace app\home\controller;
use think\Db;use app\common\controller\HomeBase;

class Business extends HomeBase{
    /**
     * 商脉搜索
     */
    public function index()    {
        $this->type = Db::table("s_sqtype")->where("parent_id=0")->select();
        $listObj = Db::table('s_region');
        $whereprovince['top_parentid'] = 0;
        $listprovince = $listObj->where($whereprovince)->select();
        $this->assign("province_list",$listprovince);
        return  $this->fetch();
    }
    /**
     * 商脉搜索结果
     */
    public function search()    {
        $get = Input("get.");
        $this->keyword=empty($get['keyword'])?"全部":$get['keyword'];
        $sqname=getsqname2(urldecode($get['type_id']));
        $this->sqname=empty($sqname)?"全部":$sqname;
        $this->province_name = Db::table("s_region")->where(['region_id'=>$get['province_id']])->column("region_name");
        $this->city_name = Db::table("s_region")->where(['region_id'=>$get['city_id']])->column("region_name");
        if(!empty($get['province_id'])){
            $where['province_id'] = Input("province_id");
        }
        if(!empty($get['city_id'])){
            $where['city_id'] = Input("city_id");
        }
        if(!empty($get['keyword'])){
            $where['_string']="nick_name LIKE '%".$get['keyword']."%'";
        }
        if(!empty($get['type_id'])){
            $array=explode(",", $get['type_id']);
            $sql="";
            foreach ($array as $key => $value) {
                $sql.= "FIND_IN_SET('".$value."',sq_type_id)";
                if($key!=(count($array)-1)){
                    $sql.=" or ";
                }
            }
            $where['_string']=$sql;
        }
        $where['sq_is_search']=1;
        $this->data = Db::table("s_userInfo")->where($where)->select();
        $userInfo = Db::table("s_userInfo")->where(array("id"=>$this->userInfo['id']))->find();
        $this->assign("userInfo",$userInfo);
        $this->assign("type_id",urldecode($get['type_id']));
        return  $this->fetch();
    }
     /**
     * 商脉选择行业
     */
    public function select()    {
        if($this->request->isPost()){
            $post = Input("post.");
            $post['id']=$this->userInfo['id'];
            Db::table("s_userInfo")->update($post);
            header("location:".url("Home/Business/setting"));
        }else{
            $this->type = Db::table("s_sqtype")->where("parent_id=0")->select();
            $this->data = Db::table("s_userInfo")->where(array("id"=>$this->userInfo['id']))->find();
        }
        return  $this->fetch();
    }

    /**
     * 商脉搜索设置
     */
    public function setting()    {
        if ($this->request->isPost()) {
            $post = Input("post.");
            $post['id']=$this->userInfo['id'];
            Db::table("s_userInfo")->update($post);
            header("location:".url("Home/Trade/search"));
        }
        $this->data = Db::table("s_userInfo")->where(array("id"=>$this->userInfo['id']))->find();
        return  $this->fetch();
    }
}