<?php
namespace app\api\controller;
use Common\Controller\AgentApiBase;
use think\Db;

class AgencyAdmin extends AgentApiBase 
{
    /**
     * 数据转移
     */
    public function upimglist() 
    {
        $res = $this->request->post();
        $id = $res["id"];
        $list = Db::table("s_api_file")->where(array("jid" => $id))->select();
        $a1 = 0;
        $a2 = 0;
        $a3 = 0;
        $a4 = 0;
        for ($i = 0; $i < count($list); $i++) {
            if ($list[$i]["type"] == 3) {
                $res["nagent_contracts"][$a3] = array(
                    "name" => "合同记录",
                    "url" => $list[$i]["xurl"]
                );
                $a3 = $a3 + 1;
                continue;
            }
            if ($list[$i]["type"] == 1) {
                $res["nagent_idimgs"][$a1] = array(
                    "name" => "身份证",
                    "url" => $list[$i]["xurl"]
                );
                $a1 = $a1 + 1;
                continue;
            }
            if ($list[$i]["type"] == 2) {
                $res["nagent_transfer_record"][$a2] = array(
                    "name" => "转账记录",
                    "url" => $list[$i]["xurl"]
                );
                $a2 = $a2 + 1;
                continue;
            }
            if ($list[$i]["type"] == 4) {
                $res["study_assessment_img"][$a4] = array(
                    "name" => "学习考核",
                    "url" => $list[$i]["xurl"]
                );
                $a4 = $a4 + 1;
                continue;
            }
        }
        
        $a = Db::table("s_api_nagent_apply")->where(array("three_id" => $id))->update(array("nagent_contracts" => json_encode($res["nagent_contracts"]) ,
            "study_assessment_img" => json_encode($res["study_assessment_img"]),"nagent_transfer_record" => json_encode($res["nagent_transfer_record"]),
            "nagent_idimgs" => json_encode($res["nagent_idimgs"])));
        
        $this->array_return['ResultType'] = self::__OK__;
        $this->array_return['Message'] = "操作成功";
        $this->array_return['AppendData'] = $a;
        return json($this->array_return);
    }
    
    /**
     * 数据同步
     */
    public function upsupid() 
    {
        $res = $this->request->post();
        $id = $res["id"];
        $nid = Db::table("s_api_contract")->where(array("using_id" => $id))->column("nid");
        if ($nid != null && $nid != 0) {
            Db::table("s_api_nagent_apply")->where(array("three_id" => $id))->update(array("contract_number" => $nid));
            $api_nagentnid = Db::table("s_api_nagent_apply")->where(array("three_id" => $id))->column("nid");
            Db::table("s_api_contract")->where(array(array("using_id" => $id)))->update(array("using_id" => $api_nagentnid));
        }
        
        $this->array_return['ResultType'] = self::__OK__;
        $this->array_return['Message'] = "操作成功";
        $this->array_return['AppendData'] = $nid;
        return json($this->array_return);
    }
 
    /**
     * @throws \OSS\Core\OssException
     */
    public function upload_img() 
    {
        $res = $this->request->post();
        //http://htest.yxm360.com
        $jurl = $res['url'];
        $url = "http://htest.yxm360.com" . $res['url'];
        //语言文件地址
        $end_name = trim(strrchr($url, '.') , '.');
        $file = './Uploads/imgaaa/' . md5($url) . '.' . $end_name; //本地缓存音源的路径 避免同样提示音重复去百度生成
        $data = file_get_contents(mb_convert_encoding($url, 'gbk', 'utf-8'));
        file_put_contents($file, $data);
        $start_name = "agencyadm";
        $end_name = trim(strrchr($file, '.') , '.');
        if ($end_name == 'jpeg') {
            $save_name = $start_name . '/' . date('Y-m-d') . rand(1, 99) . '/' . time() . rand(1, 9999) . '.jpg';
        } else {
            $save_name = $start_name . '/' . date('Y-m-d') . rand(1, 99) . '/' . time() . rand(1, 9999) . '.' . $end_name;
        }
        try {
        	$conf = config('OSS_IMAGE');
            $ossClient = new OSSOssClient($conf['accessKeyId'], $conf['accessKeySecret'], $conf['endpoint']);
            $image_file = $ossClient->uploadFile($conf['bucket'], $save_name, $file);
            if ($image_file['status'] == true) {
                $shift_url = str_replace($conf['oss_url'], $conf['cdn_usl'], $image_file['url']);
                $img = $shift_url . "@!protected";
                unlink(substr($file, 2));
            } else {
                // 上传错误提示错误信息     
            }
        }catch(OssException $e) {
        	
        }
        
        $m = Db::table("s_api_file")->where(array("jurl" => $jurl))->update(array("xurl" => $img));    
        $this->array_return['ResultType'] = self::__ERROR2__;
        $this->array_return['Message'] = $img;
        if ($m) {
            $this->array_return['ResultType'] = self::__OK__;
            $this->array_return['Message'] = "操作成功";
            $this->array_return['AppendData'] = $img;
        }
        
        return json($this->array_return);
    }
    
    /**
     * 分页查询数据
     *
     */
    public function file_list() 
    {
        $res = $this->request->post();
        $n = $res['n'] ? $res['n'] : 1;
        $pageSize = 100;
        $map = array();
        $map["jid"] = array("GT",2719);    
        
        $m = Db::table("s_api_file");
        $pageTotal = $m->alias("a")->where($map)->count();
        $list = $m->alias("a")->field("a.*")->where($map)->limit(($n - 1) * $pageSize, $pageSize)->select();
        $datalist = array("list" => $list,"pageTotal" => $pageTotal,"pageSize" => $pageSize,"pageNum" => $n);
        
        $this->array_return['ResultType'] = self::__ERROR2__;
        $this->array_return['Message'] = "操作失败";
        if ($datalist) {
            $this->array_return['ResultType'] = self::__OK__;
            $this->array_return['Message'] = "操作成功";
            $this->array_return['AppendData'] = $datalist;
        }
        
        return json($this->array_return);
    }
     
    /**
     * 增加修改代理数据
     */
    public function i_u_nagent_apply_data() 
    {
        $res = $this->request->post();
        $res["expiration_of_contract_time"] = date('Y-m-d H:i:s', strtotime($res["createtime"] . "+1 year"));
        $res["uuid"] =  getRandomString(12);
        $res["nagent_contracts"] = json_encode($res["nagent_contracts"]);
        $res["nagent_transfer_record"] = json_encode($res["nagent_transfer_record"]);
        $res["nagent_idimgs"] = json_encode($res["nagent_idimgs"]);
        $res["study_assessment_img"] = json_encode($res["study_assessment_img"]);
        $m = Db::table("s_api_nagent_apply")->insert($res);
        $res["nagent_contracts"] = json_decode($res["nagent_contracts"], true);
        $res["nagent_transfer_record"] = json_decode($res["nagent_transfer_record"], true);
        $res["nagent_idimgs"] = json_decode($res["nagent_idimgs"], true);
        $res["study_assessment_img"] = json_decode($res["study_assessment_img"], true);
        if ($res["three_id"] > 2719) {
            for ($i = 0; $i < count($res["nagent_contracts"]); $i++) {
                $dataList[$i] = array(
                    'xid' => $m,
                    'jid' => $res["three_id"],
                    'type' => 3,
                    'jurl' => $res["nagent_contracts"][$i]["url"]
                );
            }
            $c = count($dataList);
            for ($i = 0; $i < count($res["nagent_idimgs"]); $i++) {
                $dataList[$i + $c] = array(
                    'xid' => $m,
                    'jid' => $res["three_id"],
                    'type' => 1,
                    'jurl' => $res["nagent_idimgs"][$i]["url"]
                );
            }
            $c = count($dataList);
            for ($i = 0; $i < count($res["nagent_transfer_record"]); $i++) {
                $dataList[$i + $c] = array(
                    'xid' => $m,
                    'jid' => $res["three_id"],
                    'type' => 2,
                    'jurl' => $res["nagent_transfer_record"][$i]["url"]
                );
            }
            $c = count($dataList);
            for ($i = 0; $i < count($res["study_assessment_img"]); $i++) {
                $dataList[$i + $c] = array(
                    'xid' => $m,
                    'jid' => $res["three_id"],
                    'type' => 4,
                    'jurl' => $res["study_assessment_img"][$i]["url"]
                );
            }
            Db::table("s_api_file")->addAll($dataList);
        }
        
        $this->kkajaxReturn($m);
    }
    
    
    /**
     * 增加修改合同
     */
    public function i_u_contract_data() 
    {
        $res = $this->request->post();
        $data = array();
        if (!empty($res["contract_id"])) {
            $data["contract_id"] = $res["contract_id"];
        }
        if (!empty($res["contract_type_id"])) {
            $data["contract_type_id"] = $res["contract_type_id"];
        }
        if (!empty($res["contract_type"])) {
            $data["contract_type"] = $res["contract_type"];
        }
        if (!empty($res["create_time"])) {
            $data["create_time"] = $res["create_time"];
        }
        if (!empty($res["edit_create_time"])) {
            $data["edit_create_time"] = $res["edit_create_time"];
        }
        if (!empty($res["using_id"])) {
            $data["using_id"] = $res["using_id"];
        }
        if (!empty($res["purchaser_id"])) {
            $data["purchaser_id"] = $res["purchaser_id"];
        }
        if (!empty($res["start"])) {
            $data["start"] = $res["start"];
        }
        $m = Db::table("s_api_contract")->insert($data);
        $this->kkajaxReturn($m);
    }
    
    /**
     * 数据同步
     */
    public function spi_nagent_user() 
    {
        $q = Db::table("s_api_nagent_user")->where(array("nagent_account" => input('post.phone')
        ))->field("nid,nagent_topimgs,nagent_vip1_num,nagent_type_id")->find();
        
        $sql = "select count(*) as num ,sum(case nagent_type_id when 1 then 1 else 0 end) as 'one',
		sum(case nagent_type_id when 2 then 1 else 0 end) as 'two',sum(case nagent_type_id when 3 then 1 else 0 end) as 'three'
		from s_api_nagent_apply where  superior_phone='" . input('post.phone') . "'";
        //1
        $returndata = Db::query($sql);
        //2
        $m = Db::table("s_api_contract")->where(array("purchaser_id" => $q["nagent_topimgs"],))->update(array("purchaser_id" => $q["nid"]));
        //3
        $m = Db::table("s_api_nagent_apply")->where(array("superior_phone" => input('post.phone')))->update(array("superior_id" => $q["nid"]));
        if ($q["nagent_type_id"] == 2) {
            $m = Db::table("s_api_nagent_user")->where(array(
                "nagent_phone" => input('post.phone') ,
            ))->update(array(
                "nagent_vip1_num" => (intval($q["nagent_vip1_num"]) - intval($returndata[0]["one"]))
            ));
        } else if ($q["nagent_type_id"] == 3) {
            $m = Db::table("s_api_nagent_user")->where(array(
                "nagent_phone" => input('post.phone') ,
            ))->update(array(
                "nagent_vip2_num" => (intval($q["nagent_vip1_num"]) - (intval($returndata[0]["one"]) + intval($returndata[0]["two"])))
            ));
        } else if ($q["nagent_type_id"] == 4) {
            $m = Db::table("s_api_nagent_user")->where(array(
                "nagent_phone" => input('post.phone') ,
            ))->update(array(
                "nagent_vip3_num" => ($q["nagent_vip1_num"] - (intval($returndata[0]["one"]) + intval($returndata[0]["two"]) + intval($returndata[0]["three"])))
            ));
        } else {
            $m = Db::table("s_api_nagent_user")->where(array("nagent_phone" => input('post.phone') ,
            ))->update(array(
                "nagent_vip3_num" => 0,
                "nagent_vip1_num" => 0,
                "nagent_vip2_num" => 0
            ));
        }
        
        $this->kkajaxReturn($q);
    }
    
    /**
     * 增加修改合同
     */
    public function spi_nagent_user0000000()
    {
        $q = Db::table("s_api_nagent_user")->where(array(
            "nagent_account" => 00000000000,
            "nagent_pwd" => 00000000000
        ))->field("nid,nagent_topimgs")->find();
        $m = Db::table("s_api_contract")->where(array(
            "purchaser_id" => $q["nagent_topimgs"],
            "contract_type_id" => 1
        ))->update(array("purchaser_id" => 5));
        $m = Db::table("s_api_contract")->where(array(
            "purchaser_id" => $q["nagent_topimgs"],
            "contract_type_id" => 2
        ))->update(array("purchaser_id" => 6));
        $m = Db::table("s_api_contract")->where(array(
            "purchaser_id" => $q["nagent_topimgs"],
            "contract_type_id" => 3
        ))->update(array("purchaser_id" => 7));
        //
        $m = Db::table("s_api_nagent_apply")->where(array(
            "superior_type_id" => 4,
            "superior_phone" => '00000000000',
            "nagent_type_id" => 3
        ))->update(array(
            "superior_id" => $q["nid"],
            "superior_type_id" => 4,
            "superior_phone" => '400920112703',
            "superior_type" => '创业领袖',
            "superior_name" => '洋小秘(创业领袖)',
        ));
        $m = Db::table("s_api_nagent_apply")->where(array(
            "superior_type_id" => 4,
            "superior_phone" => '00000000000',
            "nagent_type_id" => 2
        ))->update(array(
            "superior_id" => $q["nid"],
            "superior_type_id" => 3,
            "superior_phone" => '400920112702',
            "superior_type" => '创业导师',
            "superior_name" => '洋小秘(创业导师)',
        ));
        $m = Db::table("s_api_nagent_apply")->where(array(
            "superior_type_id" => 4,
            "superior_phone" => '00000000000',
            "nagent_type_id" => 1
        ))->update(array(
            "superior_id" => $q["nid"],
            "superior_type_id" => 2,
            "superior_phone" => '400920112701',
            "superior_type" => '创业家',
            "superior_name" => '洋小秘(创业家)',
        ));
        
        $this->kkajaxReturn($q);
    }
    
    /**
     * 删除代理商申请
     */
    public function del_agency_shengqi() 
    {
        $res = $this->request->post();
        if (empty($res["pojouuid"])) {
            $this->array_return['ResultType'] = self::__ERROR2__;
            $this->array_return['Message'] = "非法访问";
            return json($this->array_return);
        }
        
        $m = Db::table("s_api_nagent_apply")->where(array("uuid" => $res["pojouuid"]))->update(array("nstate" => - 1,));
        $iid = Db::table("s_api_nagent_apply")->where(array("uuid" => $res["pojouuid"]))->column("nid");
        //logtype 1 后台用户,2增加权限，3角色，4，代理商申请，5，代理商用户，6合同管理，7，审核配置
        $this->setAdminLog($this->userInfo["admin_id"], 4, $iid, serialize($res) , "非物理删除代理申请");
        $this->kkajaxReturn($m);
    }
    
    public function del_agency_shengqi_draft() 
    {
        $res = $this->request->post();
        if (empty($res["pojouuid"]))
        {
            $this->array_return['ResultType'] = self::__ERROR2__;
            $this->array_return['Message'] = "非法访问";
            return json($this->array_return);
        }

        $m = Db::table("s_api_nagent_apply_draft")->where(array("uuid" => $res["pojouuid"]))->delete();
        $this->kkajaxReturn($m);
    }
    
    /**
     * 数据不同意申请修改
     */
    public function shuju_tonyi_shengqi_no() 
    {
        $res = $this->request->post();
        if (empty($res["pojouuid"])) {
            $this->array_return['ResultType'] = self::__ERROR2__;
            $this->array_return['Message'] = "非法访问";
            return json($this->array_return);
        }
        $pojo = Db::table("s_api_nagent_apply")->where(array(
            "uuid" => $res["pojouuid"]
        ))->field("nstate,superior_name,nagent_name,nagent_type,superior_type_id,nagent_phone,nagent_type_id,superior_phone")->find();
        $m = Db::table("s_api_nagent_apply")->where(array(
            "uuid" => $res["pojouuid"],
            "nstate" => 4
        ))->update(array(
            "nstate" => $pojo["old_starte"],
            "old_starte" => 0
        ));
        //logtype 1 后台用户,2增加权限，3角色，4，代理商申请，5，代理商用户，6合同管理，7，审核配置
        $this->setAdminLog($this->userInfo["admin_id"], 4, $pojo["nid"], serialize($res) , "数据不同意申请修改");
        $this->kkajaxReturn($m);
    }
    
    /**
     * 数据同意申请修改
     */
    public function shuju_tonyi_shengqi() 
    {
        $res = $this->request->post();
        if (empty($res["pojouuid"])) {
            $this->array_return['ResultType'] = self::__ERROR2__;
            $this->array_return['Message'] = "非法访问";
            return json($this->array_return);
        }
        $pojo = Db::table("s_api_nagent_apply")->where(array("uuid" => $res["pojouuid"]
        ))->field("nid,nstate,superior_name,nagent_name,nagent_type,superior_type_id,nagent_phone,nagent_type_id,superior_phone")->find();
        $m = Db::table("s_api_nagent_apply")->where(array("uuid" => $res["pojouuid"],"nstate" => 4
        ))->update(array(
            "nstate" => 5,
        ));
        //logtype 1 后台用户,2增加权限，3角色，4，代理商申请，5，代理商用户，6合同管理，7，审核配置
        $this->setAdminLog($this->userInfo["admin_id"], 4, $pojo["nid"], serialize($res) , "数据同意申请修改");
        $this->kkajaxReturn($m);
    }
    
    /**
     * 批量添加
     */
    public function p_contract_add() 
    {
        $res = $this->request->post();
        $ctype = $res["ctype"];
        $min = $res["min"];
        $max = $res["max"];
        if (empty($ctype) || empty($min) || empty($max)) {
            $this->array_return['ResultType'] = self::__ERROR2__;
            $this->array_return['Message'] = "非法访问";
            return json($this->array_return);
        }
        $i = "";
        $itype = 1;
        if ($ctype == "创投") {
            $i = "YXM-CT-T" . "001";
            $itype = 1;
        } else if ($ctype == "创业家") {
            $i = "YXM-CY-Y" . "002";
            $itype = 2;
        } else if ($ctype = "创业导师") {
            $i = "YXM-CD-D" . "003";
            $itype = 3;
        } else {
            $i = "";
        }
        $min = intval($min);
        $max = intval($max);
        if ($min > $max) {
            $this->array_return['ResultType'] = 0;
            $this->array_return['Message'] = "参数错误";
            return json($this->array_return);
        }
        $b = 0;
        for ($c = $min; $c <= $max; $c++) {
            if ($c < 10) {
                $list[$b] = array(
                    "contract_id" => $i . "000" . $c,
                    "contract_type" => $ctype,
                    "contract_type_id" => $itype,
                    "create_time" => date('Y-m-d H:i:s') ,
                    "start" => 1
                );
                $b = $b + 1;
            } else if ($c < 100) {
                $list[$b] = array(
                    "contract_id" => $i . "00" . $c,
                    "contract_type" => $ctype,
                    "contract_type_id" => $itype,
                    "create_time" => date('Y-m-d H:i:s') ,
                    "start" => 1
                );
                $b = $b + 1;
            } else if ($c < 1000) {
                $list[$b] = array(
                    "contract_id" => $i . "0" . $c,
                    "contract_type" => $ctype,
                    "contract_type_id" => $itype,
                    "create_time" => date('Y-m-d H:i:s') ,
                    "start" => 1
                );
                $b = $b + 1;
            } else {
                $list[$b] = array(
                    "contract_id" => $i . $c,
                    "contract_type" => $ctype,
                    "contract_type_id" => $itype,
                    "create_time" => date('Y-m-d H:i:s') ,
                    "start" => 1
                );
                $b = $b + 1;
            }
        }
        $r = Db::table("s_api_contract")->addAll($list);
        //logtype 1 后台用户,2增加权限，3角色，4，代理商申请，5，代理商用户，6合同管理，7，审核配置
        $this->setAdminLog($this->userInfo["admin_id"], 6, 0, serialize($res) , "批量添加");
        $this->kkajaxReturn($r);
    }
    
    /**
     * 批量购买
     */
    public function p_contract_pay() 
    {
        $res = $this->request->post();
        $ctype = $res["ctype"];
        $min = $res["min"];
        $max = $res["max"];
        $payid = $res["payid"];
        if (empty($ctype) || empty($min) || empty($max) || empty($payid)) {
            $this->array_return['ResultType'] = self::__ERROR2__;
            $this->array_return['Message'] = "非法访问";
            return json($this->array_return);
        }
        $i = "";
        $itype = 1;
        if ($ctype == "创投") {
            $i = "YXM-CT-T" . "001";
            $itype = 1;
        } else if ($ctype == "创业家") {
            $i = "YXM-CY-Y" . "002";
            $itype = 2;
        } else if ($ctype = "创业导师") {
            $i = "YXM-CD-D" . "003";
            $itype = 3;
        } else {
            $i = "";
        }
        $min = intval($min);
        $max = intval($max);
        if ($min > $max) {
            $this->array_return['ResultType'] = 0;
            $this->array_return['Message'] = "参数错误";
            return json($this->array_return);
        }
        for ($c = $min; $c <= $max; $c++) {
            if ($c < 10) {
                $b = Db::table("s_api_contract")->where(array(
                    "contract_id" => $i . "000" . $c,
                    "contract_type" => $ctype,
                    "contract_type_id" => $itype
                ))->update(array(
                    "start" => 2,
                    "edit_create_time" => date('Y-m-d H:i:s') ,
                    "purchaser_id" => $payid
                ));
            } else if ($c < 100) {
                $b = Db::table("s_api_contract")->where(array(
                    "contract_id" => $i . "00" . $c,
                    "contract_type" => $ctype,
                    "contract_type_id" => $itype
                ))->update(array(
                    "start" => 2,
                    "edit_create_time" => date('Y-m-d H:i:s') ,
                    "purchaser_id" => $payid
                ));
            } else if ($c < 1000) {
                $b = Db::table("s_api_contract")->where(array(
                    "contract_id" => $i . "0" . $c,
                    "contract_type" => $ctype,
                    "contract_type_id" => $itype
                ))->update(array(
                    "start" => 2,
                    "edit_create_time" => date('Y-m-d H:i:s') ,
                    "purchaser_id" => $payid
                ));
            } else {
                $b = Db::table("s_api_contract")->where(array(
                    "contract_id" => $i . $c,
                    "contract_type" => $ctype,
                    "contract_type_id" => $itype
                ))->update(array(
                    "start" => 2,
                    "edit_create_time" => date('Y-m-d H:i:s') ,
                    "purchaser_id" => $payid
                ));
            }
        }
        //logtype 1 后台用户,2增加权限，3角色，4，代理商申请，5，代理商用户，6合同管理，7，审核配置
        $this->setAdminLog($this->userInfo["admin_id"], 6, 0, serialize($res) , "批量购买合同");
        $this->kkajaxReturn($b);
    }
    
    /**
     * 申请修改
     */
    public function shengqi_xiugai() 
    {
        $res = $this->request->post();
        if (empty($res["pojouuid"])) {
            $this->array_return['ResultType'] = self::__ERROR2__;
            $this->array_return['Message'] = "非法访问";
            return json($this->array_return);
        }
        $pojo = Db::table("s_api_nagent_apply")->where(array(
            "uuid" => $res["pojouuid"]
        ))->field("nid,nstate,superior_name,nagent_name,nagent_type,superior_type_id,nagent_phone,nagent_type_id,superior_phone")->find();
        $m = Db::table("s_api_nagent_apply")->where(array(
            "uuid" => $res["pojouuid"],
            "nstate" => array(
                "in",
                "1,6,10"
            )
        ))->update(array(
            "nstate" => 4,
            "old_starte" => $pojo["nstate"],
        ));
        if (empty($m)) {
            $this->array_return['ResultType'] = self::__ERROR2__;
            $this->array_return['Message'] = "操作失败";
            return json($this->array_return);
        }
        $configuser = Db::table("s_api_nagent_config")->where(array(
            "s_key_name" => array(
                "in",
                "toexamineSP,toexamineSPphone"
            )
        ))->select();
        for ($i = 0; $i < count($configuser); $i++) {
            if ($configuser[$i]["s_key_name"] == "toexamineSP") {
                $customerSP = $configuser[$i]["s_key_value"];
            } else {
                $customerSPphone = $configuser[$i]["s_key_value"];
            }
        }
        $this->Agency_send_msg(array(
            "phone" => $customerSPphone,
            "content" => $customerSP . "先生/女士：" . "系统收到" . $pojo["superior_name"] . "为" . $pojo["nagent_name"] . "的客服部申请请求.请尽快处理"
        ));
        //logtype 1 后台用户,2增加权限，3角色，4，代理商申请，5，代理商用户，6合同管理，7，审核配置
        $this->setAdminLog($this->userInfo["admin_id"], 4, $pojo["nid"], serialize($res) , "客服部申请请求修改");
        $this->kkajaxReturn($m);
    }
    
    /**
     * 数据通过审核
     */
    public function shuju() 
    {
        $res = $this->request->post();
        if (empty($res["pojouuid"]))
        {
            $this->array_return['ResultType'] = self::__ERROR2__;
            $this->array_return['Message'] = "非法访问";
            return json($this->array_return);
        }
        
        $pojo = Db::table("s_api_nagent_apply")->where(array("uuid" => $res["pojouuid"]))->field("nid,nstate,superior_name,nagent_name,nagent_type,superior_type_id,nagent_phone,nagent_type_id,superior_phone")->find();
        $admin = Db::table("s_api_admin")->where(array("uuid" => $_COOKIE['uuid']))->column("admin_id");
        $isuser = Db::table("s_api_nagent_user")->where(array("nagent_phone" => $pojo["nagent_phone"]))->count();
        // 启动事务
		Db::startTrans();
		try {
        	$c = Db::table("s_api_nagent_apply")->where(array("uuid" => $res["pojouuid"]))->update(array("nstate" => 10,"three_id" => $admin,"three_edittime" => date('Y-m-d H:i:s') ,"createtime" => date('Y-m-d H:i:s')));
            //如果推荐人是创业导师或者创业领袖则为申请人生成后台账号
            if ($pojo["nagent_type_id"] >= 2) {
                if ($isuser <= 0) {
                    $userpojo = array(
                        "nagent_uuid" =>  getRandomString(12) ,
                        "nagent_createtime" => date('Y-m-d H:i:s') ,
                        "nagent_name" => $pojo["nagent_name"],
                        "nagent_phone" => $pojo["nagent_phone"],
                        "nagent_account" => $pojo["nagent_account"],
                        "nagent_pwd" => $pojo["nagent_pwd"],
                        "nagent_type" => $pojo["nagent_type"],
                        "nagent_start" => 1,
                        "nagent_type_id" => $pojo["nagent_type_id"]
                    );
                    if ($pojo["nagent_type_id"] == 2) {
                        $userpojo["nagent_vip1_num"] = 35;
                        $userpojo["nagent_vip2_num"] = 0;
                        $userpojo["nagent_vip3_num"] = 0;
                    } else if ($pojo["nagent_type_id"] == 3) {
                        $userpojo["nagent_vip1_num"] = 0;
                        $userpojo["nagent_vip2_num"] = 35;
                        $userpojo["nagent_vip3_num"] = 0;
                    } else if ($pojo["nagent_type_id"] == 4) {
                        $userpojo["nagent_vip1_num"] = 0;
                        $userpojo["nagent_vip2_num"] = 0;
                        $userpojo["nagent_vip3_num"] = 35;
                    }
                    $d = Db::table("s_api_nagent_user")->insert($userpojo);
                } else {
                    if ($pojo["nagent_type_id"] == 2) {
                        $d = Db::table("s_api_nagent_user")->where(array(
                            "nagent_phone" => $pojo["nagent_phone"]
                        ))->update(array(
                            "nagent_vip2_num" => 35,
                            "nagent_type" => "创业导师",
                            "nagent_type_id" => 3
                        ));
                    } else if ($pojo["nagent_type_id"] == 3) {
                        $d = Db::table("s_api_nagent_user")->where(array(
                            "nagent_phone" => $pojo["nagent_phone"]
                        ))->update(array(
                            "nagent_vip3_num" => 35,
                            "nagent_type" => "创业领袖",
                            "nagent_type_id" => 4
                        ));
                    }
                }
            }
            //执行江奇的代码
            //public function up_agent($phone,$res_phone,$vip_id)
            $falg = $this->up_agent($pojo["nagent_phone"], $pojo["superior_phone"], intval($pojo["nagent_type_id"]) + 3);
            if ($falg != "ok") {
                $model->rollback();
                $this->array_return['ResultType'] = $falg;
                $this->array_return['Message'] = "操作失败1";
                return json($this->array_return);
            }
            Db::commit();
            //
            //发给申请人
            if ($pojo["nagent_type_id"] >= 2) {
                $this->Agency_send_msg(array(
                    "phone" => $pojo["nagent_phone"],
                    "content" => $pojo["nagent_name"] . "先生/女士：" . "您的申请已审核通过，您的代理商账号为：" . $pojo["nagent_phone"] . ",初始密码为您的账号"
                ));
            } else {
                $this->Agency_send_msg(array(
                    "phone" => $pojo["nagent_phone"],
                    "content" => $pojo["nagent_name"] . "先生/女士：" . "您的申请已审核通过",
                ));
            }
            //发给推荐人
            if (strpos($pojo["superior_phone"], "4009201127") === false) {
                $this->Agency_send_msg(array(
                    "phone" => $pojo["superior_phone"],
                    "content" => $pojo["superior_name"] . "先生/女士：" . "您为" . $pojo["nagent_name"] . "提交的申请已通过审核"
                ));
            }
            //发给监测者
            $configuser = Db::table("s_api_nagent_config")->where(array("s_key_name" => array("in","monitorlist")))->find();
            $monitorlist = json_decode($configuser["s_key_name"]);
            for ($i = 0; $i < count($monitorlist); $i++) {
                $this->Agency_send_msg(array(
                    "phone" => $monitorlist[$i]["value"],
                    "content" => "尊敬的" . $monitorlist[$i]["name"] . "系统收到" . $pojo["superior_name"] . "为" . $pojo["nagent_name"] . "成为" . $pojo["nagent_type"] . "已经审核通过"
                ));
            }
        } catch (\Exception $e) {
           Db::rollback();
        }
        //logtype 1 后台用户,2增加权限，3角色，4，代理商申请，5，代理商用户，6合同管理，7，审核配置
        $this->setAdminLog($this->userInfo["admin_id"], 4, $pojo["nid"], serialize($res) , "申请通过");
        $this->kkajaxReturn($c);
    }
    
    //升级代理商
    public function up_agent($phone, $res_phone, $vip_id) 
    {
        //4,5,6,7
        if (strpos($res_phone, "4009201127") === false) {
        } else {
            $res_phone = "4009201127";
        }
        $user = Db::table("s_user_detail")->alias('u')->leftJoin(' s_member m','m.id = u.member_id')->field('u.nick_name,u.id')->where(array('m.phone' => $phone))->find();
        // $this->kkajaxReturn($user);
        if (empty($user) || empty($user["id"])) {
            return "error";
        }
        $res_user = Db::table("s_user_detail")->alias('u')->leftJoin(' s_member m','m.id = u.member_id')->field('u.nick_name,u.id')->where(array('m.phone' => $res_phone))->find();
        $vip = Db::table('s_vip_list')->alias('v')->leftJoin(' s_vip_group vg','vg.id = v.vip_group_id')->field('v.money,v.vip_group_id,vg.give_vip1_count,vg.give_vip2_count,vg.give_vip3_count,vg.give_vip4_count,vg.give_vip5_count,vg.vip_name')->where(array(
            'v.id' => $vip_id))->find();
        // 添加订单
        $order['order_no'] = getOrderSn();
        $order['user_id'] = $user['user_id'];
        $order['vip_list_id'] = $vip_id;
        $order['price'] = $vip['money'];
        $order['pay_status'] = 1;
        $order['add_time'] = time();
        $order_id = Db::table('s_vip_order')->insert($order);
        $userData['give_vip1_count'] = $vip['give_vip1_count'];
        $userData['give_vip2_count'] = $vip['give_vip2_count'];
        $userData['give_vip3_count'] = $vip['give_vip3_count'];
        $userData['give_vip4_count'] = $vip['give_vip4_count'];
        $userData['give_vip5_count'] = $vip['give_vip5_count'];
        $userData['vip_id'] = $vip_id;
        $userData['places_id'] = $res_user['id'];
        $userData['vip_group_id'] = $vip['vip_group_id'];
        $userData['vip_orver_time'] = strtotime('+1 year'); //1年过期
        Db::table('s_user_detail')->where(array(
            'id' => $user['id']
        ))->update($userData);
        $data['order_sn'] = $order['order_no']; //微信订单号
        $data['user_id'] = $user['id'];
        $data['object_id'] = $vip_id;
        $data['as'] = 2; //减少
        $data['money'] = $vip['money'];
        $data['msg'] = "会员购买";
        $data['type'] = 3;
        $data['add_time'] = time();
        Db::table('s_capital_log')->insert($data);

        Db::table('s_user_detail')->where(array(
            'id' => $res_user['id']
        ))->setDec('give_vip' . ($vip_id - 1) . '_count');
        if ($userData['vip_group_id'] > 2) { //成为创投以上角色，分销断开，从我开始
            //todo 获取我下面的粉丝,我是B，A->B->C->D,改变B对CD}
            $model = Db::table('s_users_fans');
            $fan = $model->where(array(
                'object_id' => $user['id'],
                'is_distribute' => 1
            ))->find();
            if ($fan) {
                $myFans = $model->where(array(
                    'user_id' => $user['id'],
                    'is_distribute' => 0
                ))->select(); //CD粉丝，改为B能参与分销
                if (count($myFans)) {
                    foreach ($myFans as $myFan) {
                        $myFan['is_distribute'] = 1;
                        $model->update($myFan);
                        $fans = $model->where(array(
                            'user_id' => $fan['user_id'],
                            'object_id' => $myFan['object_id'],
                            'is_distribute' => 1
                        ))->select(); //取消A对CD粉丝的分销权限
                        if ($fans) {
                            foreach ($fans as $f) {
                                $f['is_distribute'] = 0;
                                $model->update($f);
                            }
                        }
                    }
                }
            }
        }
        
        //添加日志
        $this->admin_log11(1, $user['id'], 'vip等级升级为' . $vip['vip_name']);
        return "ok";
    }
    
    /**
     * 管理员操作日志
     * type: 0:提现操作 1:用户列表操作
     * object_id   对象ID
     * content   操作内容
     */
    public function admin_log11($type, $object_id = null, $content = null) 
    {
        $data = array('admin_id' => $_SESSION['admin']['user_id'],
            'object_id' => $object_id,
            'content' => $content,
            'addtime' => time() ,
            'type' => $type
        );
        $res = Db::table('s_admin_log')->insert($data);
        
        return $res;
    }
    
    /**
     * 财务不通过审核
     */
    public function caiwuNo() 
    {
        $res = $this->request->post();
        if (empty($res["pojouuid"]) || empty($res["errlog"])) 
        {
            $this->array_return['ResultType'] = self::__ERROR2__;
            $this->array_return['Message'] = "非法访问";
            return json($this->array_return);
        }
        $pojo = Db::table("s_api_nagent_apply")->where(array("uuid" => $res["pojouuid"]))->field("nid,nstate,superior_name,superior_phone,nagent_name")->find();
        $admin = Db::table("s_api_admin")->where(array("uuid" => $_COOKIE['uuid']))->column("admin_id");
        
        Db::startTrans();  
        try {
	        $c = Db::table("s_api_nagent_apply")->where(array(
	            "uuid" => $res["pojouuid"]
	        ))->update(array(
	            "old_starte" => 0,
	            "nstate" => (intval($pojo["nstate"]) - 1) ,
	            "createtime" => date('Y-m-d H:i:s')
	        ));
	        $m = Db::table("s_api_audit_error_log")->insert(array(
	            "nagent_apply_id" => $pojo["nid"],
	            "uid" => $admin,
	            "dis" => $res["errlog"]
	        ));
      
            Db::commit();
            $configuser = Db::table("s_api_nagent_config")->where(array(
                "s_key_name" => array(
                    "in",
                    "customerSP,customerSPphone"
                )
            ))->select();
            for ($i = 0; $i < count($configuser); $i++) {
                if ($configuser[$i]["s_key_name"] == "customerSP") {
                    $customerSP = $configuser[$i]["s_key_value"];
                } else {
                    $customerSPphone = $configuser[$i]["s_key_value"];
                }
            }
            $this->Agency_send_msg(array(
                "phone" => $customerSPphone,
                "content" => $customerSP . "先生/女士：" . "系统收到" . $res["superior_name"] . "为" . $res["nagent_name"] . "的申请财务未通过.请尽快处理.点击此链接可快速处理" . "http://wx.yxm360.com/Agency/AgencyAdm/Agent_show/pojouuid/" . $res["pojouuid"]
            ));
        } catch (\Exception $e) {
		    // 回滚事务
		    Db::rollback();
        }
        //logtype 1 后台用户,2增加权限，3角色，4，代理商申请，5，代理商用户，6合同管理，7，审核配置
        $this->setAdminLog($this->userInfo["admin_id"], 4, $pojo["nid"], serialize($res) , "申请财务未通过");
        $this->kkajaxReturn($c);
    }
    
    /**
     * 数据部不通过审核
     */
    public function shujuNo() 
    {
        $res = $this->request->post();
        if (empty($res["pojouuid"]) || empty($res["errlog"])) {
            $this->array_return['ResultType'] = self::__ERROR2__;
            $this->array_return['Message'] = "非法访问";
            return json($this->array_return);
        }
        $pojo = Db::table("s_api_nagent_apply")->where(array("uuid" => $res["pojouuid"]
        ))->field("nid,nstate,superior_name,superior_phone,nagent_name")->find();
        $admin = Db::table("s_api_admin")->where(array("uuid" => $_COOKIE['uuid']))->column("admin_id");
        
        Db::startTrans();
		try {
	        $c = Db::table("s_api_nagent_apply")->where(array(
	            "uuid" => $res["pojouuid"]
	        ))->update(array(
	            "old_starte" => 0,
	            "nstate" => (intval($pojo["nstate"]) - 2) ,
	            "createtime" => date('Y-m-d H:i:s')
	        ));
	        $m = Db::table("s_api_audit_error_log")->insert(array(
	            "nagent_apply_id" => $pojo["nid"],
	            "uid" => $admin,
	            "dis" => $res["errlog"]
	        ));
          	Db::commit();
            $configuser = Db::table("s_api_nagent_config")->where(array("s_key_name" => array("in","customerSP,customerSPphone")))->select();
            for ($i = 0; $i < count($configuser); $i++) {
                if ($configuser[$i]["s_key_name"] == "customerSP") {
                    $customerSP = $configuser[$i]["s_key_value"];
                } else {
                    $customerSPphone = $configuser[$i]["s_key_value"];
                }
            }
            $this->Agency_send_msg(array(
                "phone" => $customerSPphone,
                "content" => $customerSP . "先生/女士：" . "系统收到" . $res["superior_name"] . "为" . $res["nagent_name"] . "的申请数据部未通过.请尽快处理" . "http://wx.yxm360.com/Agency/AgencyAdm/Agent_show/pojouuid/" . $res["pojouuid"]
            ));
        } catch (\Exception $e) {
            $model->rollback();
        }
        //logtype 1 后台用户,2增加权限，3角色，4，代理商申请，5，代理商用户，6合同管理，7，审核配置
        $this->setAdminLog($this->userInfo["admin_id"], 4, $pojo["nid"], serialize($res) , "申请数据部未通过");
        $this->kkajaxReturn($c);
    }
    
    /**
     * 财务通过审核
     */
    public function caiwu() 
    {
        $res = $this->request->post();
        if (empty($res["pojouuid"]))
        {
            $this->array_return['ResultType'] = self::__ERROR2__;
            $this->array_return['Message'] = "非法访问";
            return json($this->array_return);
        }
        
        $pojo = Db::table("s_api_nagent_apply")->where(array("uuid" => $res["pojouuid"]))->field("nid,nstate,superior_name,nagent_name")->find();
        $admin = Db::table("s_api_admin")->where(array("uuid" => $_COOKIE['uuid']))->column("admin_id");
  
        $c = Db::table("s_api_nagent_apply")->where(array("uuid" => $res["pojouuid"]))->update(array(
            "nstate" => (intval($pojo["nstate"]) + 1) ,
            "two_id" => $admin,"two_edittime" => date('Y-m-d H:i:s') ,"createtime" => date('Y-m-d H:i:s')
        ));
        
        if (!empty($c)) {
            $configuser = Db::table("s_api_nagent_config")->where(array("s_key_name" => array("in","toexamineSP,toexamineSPphone")))->select();
            for ($i = 0; $i < count($configuser); $i++) {
                if ($configuser[$i]["s_key_name"] == "toexamineSP") {
                    $customerSP = $configuser[$i]["s_key_value"];
                } else {
                    $customerSPphone = $configuser[$i]["s_key_value"];
                }
            }
            $this->Agency_send_msg(array(
                "phone" => $customerSPphone,
                "content" => $customerSP . "先生/女士：" . "系统收到" . $pojo["superior_name"] . "为" . $pojo["nagent_name"] . "的申请财务已经审核通过.请尽快处理.点击此链接可快速处理" . "http://wx.yxm360.com/Agency/AgencyAdm/Agent_show/pojouuid/" . $res["pojouuid"]
            ));
        } 
        
        //logtype 1 后台用户,2增加权限，3角色，4，代理商申请，5，代理商用户，6合同管理，7，审核配置
        $this->setAdminLog($this->userInfo["admin_id"], 4, $pojo["nid"], serialize($res) , "客服部打回审核");
        $this->kkajaxReturn($c);
    }
    
    /**
     * 客服不通过审核
     */
    public function kefuNo() 
    {
        $res = $this->request->post();
        if (empty($res["pojouuid"]) || empty($res["errlog"]))
        {
            $this->array_return['ResultType'] = self::__ERROR2__;
            $this->array_return['Message'] = "非法访问";
            return json($this->array_return);
        }
        $pojo = Db::table("s_api_nagent_apply")->where(array("uuid" => $res["pojouuid"]))->field("nid,nstate,superior_name,superior_phone,nagent_name")->find();
        $admin = Db::table("s_api_admin")->where(array("uuid" => $_COOKIE['uuid']))->column("admin_id");
        
        Db::startTrans();
        try {
	        Db::table("s_api_nagent_apply")->where(array("uuid" => $res["pojouuid"]
	        ))->update(array(
	            "old_starte" => $pojo["nstate"],
	            "nstate" => 9,
	            "createtime" => date('Y-m-d H:i:s')
	        ));
	        
	        Db::table("s_api_audit_error_log")->insert(array("nagent_apply_id" => $pojo["nid"],"uid" => $admin,"dis" => $res["errlog"]));
            Db::commit();
        } catch (\Exception $e) {
		    // 回滚事务
		    Db::rollback();
        }
        
        $this->Agency_send_msg(array("phone" => $pojo["superior_phone"],
                "content" => $pojo["superior_name"] . "先生/女士：" . "您为" . $pojo["nagent_name"] . "提交的代理申请未通过初审，请尽快修改，点击此链接可快速处理" . "http://wx.yxm360.com/Agency/AgencyAdm/Agent_show/pojouuid/" . $res["pojouuid"]
        ));
        //logtype 1 后台用户,2增加权限，3角色，4，代理商申请，5，代理商用户，6合同管理，7，审核配置
        $this->setAdminLog($this->userInfo["admin_id"], 4, $pojo["nid"], serialize($res) , "客服部打回申请");
        $this->kkajaxReturn($c);
    }
    
    /**
     * 客服通过审核
     */
    public function kefu() 
    {
        $res = $this->request->post();
        if (empty($res["pojouuid"]) || empty($res["payable_amount"]) || empty($res["sj_payable_amount"])) 
        {
            $this->array_return['ResultType'] = self::__ERROR2__;
            $this->array_return['Message'] = "非法访问";
            return json($this->array_return);
        }
        
        $pojo = Db::table("s_api_nagent_apply")->where(array("uuid" => $res["pojouuid"]))->field("nid,nstate,superior_name,nagent_name")->find();
        $admin = Db::table("s_api_admin")->where(array("uuid" => $_COOKIE['uuid']))->column("admin_id");
        
        
        $c = Db::table("s_api_nagent_apply")->where(array("uuid" => $res["pojouuid"]
        ))->update(array(
            "payable_amount" => $res["payable_amount"],
            "sj_payable_amount" => $res["payable_amount"],
            "nstate" => (intval($pojo["nstate"]) + 1) ,
            "one_id" => $admin,
            "one_edittime" => date('Y-m-d H:i:s') ,
            "createtime" => date('Y-m-d H:i:s')
        ));
        if (!empty($c)) {
            $model->commit();
            $configuser = Db::table("s_api_nagent_config")->where(array(
                "s_key_name" => array("in",
                    "financeSP,financeSPphone"
                )
            ))->select();
            for ($i = 0; $i < count($configuser); $i++) {
                if ($configuser[$i]["s_key_name"] == "financeSP") {
                    $customerSP = $configuser[$i]["s_key_value"];
                } else {
                    $customerSPphone = $configuser[$i]["s_key_value"];
                }
            }
            $this->Agency_send_msg(array(
                "phone" => $customerSPphone,
                "content" => $customerSP . "先生/女士：" . "系统收到" . $pojo["superior_name"] . "为" . $pojo["nagent_name"] . "的申请客服部已经审核通过.请尽快处理.点击此链接可快速处理" . "http://wx.yxm360.com/Agency/AgencyAdm/Agent_show/pojouuid/" . $res["pojouuid"]
            ));
        } 
        //logtype 1 后台用户,2增加权限，3角色，4，代理商申请，5，代理商用户，6合同管理，7，审核配置
        $this->setAdminLog($this->userInfo["admin_id"], 4, $pojo["nid"], serialize($res) , "客服部通过审核通过");
        $this->kkajaxReturn($c);
    }
    
    /**
     * 代理商发送短信
     */
    public function Agency_send_msg($option = array()) 
    {
        $smsapi = "http://47.95.217.41:8088/";
        $user = "shmingxin"; //短信平台帐号
        $pass = 'shmingxin123321'; //短信平台密码
        $sendurl = $smsapi . "sms.aspx?action=send&userid=12&&account=" . $user . "&password=" . $pass . "&mobile=" . $option['phone'] . "&content=" . $option['content'] . "&sendTime=&extno=";
        $result = simplexml_load_string(file_get_contents($sendurl));
        return (String)$result;
    }
    
    /**
     * 增加修改合同
     */
    public function i_u_contract() 
    {
        $res = $this->request->post();
        if (empty($res["nid"])) {
            $res["create_time"] = date('Y-m-d H:i:s');
            $res["edit_create_time"] = date("Y-m-d H:i:s");
            if (empty($res["purchaser_id"])) {
                $res["start"] = 1;
            } else {
                $res["start"] = 2;
            }
            $m = Db::table("s_api_contract")->insert($res);
            //logtype 1 后台用户,2增加权限，3角色，4，代理商申请，5，代理商用户，6合同管理，7，审核配置
            $this->setAdminLog($this->userInfo["admin_id"], 6, $m, serialize($res) , "后台增加合同");
            $this->kkajaxReturn($m);
        }
        
        $m = Db::table("s_api_contract")->where(array("nid" => $res["nid"]))->update($res);
        $this->setAdminLog($this->userInfo["admin_id"], 6, $res["nid"], serialize($res) , "后台增加合同");
        $this->kkajaxReturn($m);
    }
    
    /**
     * 分页查找申请
     */
    public function f_nagent_apply_draft() 
    {
        $res = $this->request->post();
        $n = $res['n'];
        if (empty($n)) {
            $n = 1;
        }
        
        $map = array();
        if (!empty($res['nagent_name'])) {
            $map['a.nagent_name'] = array(
                'like',
                "%" . $res['nagent_name'] . "%"
            );
        }
        if (!empty($res['nagent_phone'])) {
            $map['a.nagent_phone'] = array(
                'like',
                "%" . $res['nagent_phone'] . "%"
            );
        }
        if (!empty($res['superior_name'])) {
            $map['a.superior_name'] = array(
                'like',
                "%" . $res['superior_name'] . "%"
            );
        }
        if (!empty($res['nagent_type_id'])) {
            $map['a.nagent_type_id'] = $res['nagent_type_id'];
        }
        if (!empty($res['superior_type_id'])) {
            $map['a.superior_type_id'] = $res['superior_type_id'];
        }
        if (!empty($res['createtime_start']) || !empty($res['createtime_end'])) {
            if (!empty($res['createtime_start']) && empty($res['createtime_end'])) {
                $map['a.createtime'] = array(
                    'GT',
                    $res['createtime_start']
                );
            } else if (empty($res['createtime_start']) && !empty($res['createtime_end'])) {
                $map['a.createtime'] = array(
                    'EGT',
                    $res['createtime_end']
                );
            } else {
                $map['a.createtime'] = array(
                    'between',
                    array(
                        $res['createtime_start'],
                        $res['createtime_end']
                    )
                );
            }
        }
        if (!empty($res['state'])) {
            if (empty($res['type'])) {
                $map['a.nstate'] = $res['state'];
            } else if ($res['type'] == 4) {
                $map['a.nstate'] = array(
                    array(
                        "eq",
                        $res['state']
                    ) ,
                    array(
                        "eq",
                        "10"
                    )
                );
            } else if ($res['type'] == 5) {
                $map['a.nstate'] = array(
                    array(
                        "eq",
                        $res['state']
                    ) ,
                    array(
                        "eq",
                        "-1"
                    )
                );
            } else {
                $map['a.nstate'] = array(
                    "neq",
                    array(
                        10, -1
                    )
                );
            }
        }
        $pageSize = 10;
        $m = Db::table("s_api_nagent_apply_draft");
        $pageTotal = $m->alias("a")->leftJoin("s_api_contract b","a.contract_number=b.nid")->where($map)->count();
        $list = $m->alias("a")->leftJoin("s_api_contract b","a.contract_number=b.nid")->field("a.nagent_name,a.nagent_phone,a.nagent_type,a.superior_name,a.superior_phone,b.contract_id as contract_name,a.nstate,a.uuid")->where($map)->order('a.createtime desc')->limit(($n - 1) * $pageSize, $pageSize)->select();;
        $datalist = array(
            "list" => $list,
            "pageTotal" => $pageTotal,
            "pageSize" => $pageSize,
            "pageNum" => $n
        );
        $this->kkajaxReturn($datalist);
    }
    
    /**
     * 分页查找申请
     */
    public function f_nagent_apply() 
    {
        $res = $this->request->post();
        $n = $res['n'];
        if (empty($n)) {
            $n = 1;
        }
        $map = array();
        if (!empty($res['nagent_name'])) {
            $map['a.nagent_name'] = array(
                'like',
                "%" . $res['nagent_name'] . "%"
            );
        }
        if (!empty($res['nagent_phone'])) {
            $map['a.nagent_phone'] = array(
                'like',
                "%" . $res['nagent_phone'] . "%"
            );
        }
        if (!empty($res['superior_name'])) {
            $map['a.superior_name'] = array(
                'like',
                "%" . $res['superior_name'] . "%"
            );
        }
        if (!empty($res['nagent_type_id'])) {
            $map['a.nagent_type_id'] = $res['nagent_type_id'];
        }
        if (!empty($res['superior_type_id'])) {
            $map['a.superior_type_id'] = $res['superior_type_id'];
        }
        if (!empty($res['createtime_start']) || !empty($res['createtime_end'])) {
            if (!empty($res['createtime_start']) && empty($res['createtime_end'])) {
                $map['a.createtime'] = array(
                    'GT',
                    $res['createtime_start']
                );
            } else if (empty($res['createtime_start']) && !empty($res['createtime_end'])) {
                $map['a.createtime'] = array(
                    'EGT',
                    $res['createtime_end']
                );
            } else {
                $map['a.createtime'] = array(
                    'between',
                    array(
                        $res['createtime_start'],
                        $res['createtime_end']
                    )
                );
            }
        }
        if (!empty($res['state'])) {
            if (empty($res['type'])) {
                $map['a.nstate'] = $res['state'];
            } else if ($res['type'] == 4) {
                $map['a.nstate'] = array(
                    array(
                        "eq",
                        $res['state']
                    ) ,
                    array(
                        "eq",
                        "10"
                    )
                );
            } else if ($res['type'] == 5) {
                $map['a.nstate'] = array(
                    array(
                        "eq",
                        $res['state']
                    ) ,
                    array(
                        "eq",
                        "-1"
                    )
                );
            } else {
                $map['a.nstate'] = array(
                    "neq",
                    array(
                        10, -1
                    )
                );
            }
        }
        if (empty($res['state']) && !empty($res['type'])) {
            if ($res['type'] == 4) {
                $map['a.nstate'] = array(
                    "eq",
                    "10"
                );
            } else if ($res['type'] == 5) {
                $map['a.nstate'] = array(
                    "eq",
                    "-1"
                );
            } else {
                $map['a.nstate'] = array(
                    array(
                        "neq",
                        "10"
                    ) ,
                    array(
                        "neq",
                        "-1"
                    )
                );
            }
        }

        $pageSize = 10;
        $m = Db::table("s_api_nagent_apply");
        $pageTotal = $m->alias("a")->leftJoin("s_api_contract b","a.contract_number=b.nid")->where($map)->count();
        $list = $m->alias("a")->leftJoin("s_api_contract b","a.contract_number=b.nid")->field("a.nagent_name,a.nagent_phone,a.nagent_type,a.superior_name,a.superior_phone,a.createtime,b.contract_id as contract_name,a.nstate,a.uuid")->where($map)->order('a.createtime desc')->limit(($n - 1) * $pageSize, $pageSize)->select();;
        $datalist = array("list" => $list,"pageTotal" => $pageTotal,"pageSize" => $pageSize, "pageNum" => $n);
        
        $this->kkajaxReturn($datalist);
    }
    
    /**
     * 增加修改代理用户
     */
    public function i_u_nagent_user()
    {
        $res = $this->request->post();
        if (empty($res["nid"])) {
            $res["nagent_createtime"] = date('Y-m-d H:i:s');
            $res["nagent_uuid"] =  getRandomString(12);
            $m = Db::table("s_api_nagent_user")->insert($res);
            //logtype 1 后台用户,2增加权限，3角色，4，代理商申请，5，代理商用户，6合同管理，7，审核配置
            $this->setAdminLog($this->userInfo["admin_id"], 5, $m, serialize($res) , "后台录入代理商用户");
            $this->kkajaxReturn($m);
        }
        $m = Db::table("s_api_nagent_user")->where(array("nid" => $res["nid"]))->update($res);
        //logtype 1 后台用户,2增加权限，3角色，4，代理商申请，5，代理商用户，6合同管理，7，审核配置
        $this->setAdminLog($this->userInfo["admin_id"], 5, $res["nid"], serialize($res) , "后台修改代理商用户");
        $this->kkajaxReturn($m);
    }
    
    /**
     * 作废合同
     */
    public function tovoid_contract() 
    {
        $res = $this->request->post();
        $nID = $res['nid'];
        if (empty($nID)) {
            $this->array_return['ResultType'] = self::__ERROR2__;
            $this->array_return['Message'] = "操作失败";
            return json($this->array_return);
        }
        $m = Db::table("s_api_contract")->where(array("nid" => $nID))->update(array("start" => 4));
        //logtype 1 后台用户,2增加权限，3角色，4，代理商申请，5，代理商用户，6合同管理，7，审核配置
        $this->setAdminLog($this->userInfo["admin_id"], 6, $nID, serialize($res) , "后台作废合同");
        if ($m) {
            $this->array_return['ResultType'] = self::__OK__;
            $this->array_return['Message'] = "操作成功";
            $this->array_return['AppendData'] = $m;
        } else {
            $this->array_return['ResultType'] = self::__ERROR2__;
            $this->array_return['Message'] = "操作失败";
        }
        
        return json($this->array_return);
    }
    
    /**
     * 删除合同
     */
    public function del_contract() 
    {
        $res = $this->request->post();
        $nID = $res['nid'];
        if (empty($nID)) {
            $this->array_return['ResultType'] = self::__ERROR2__;
            $this->array_return['Message'] = "操作失败";
            return json($this->array_return);
        }
        $m = Db::table("s_api_contract")->where(array("nid" => $nID))->delete();
        //logtype 1 后台用户,2增加权限，3角色，4，代理商申请，5，代理商用户，6合同管理，7，审核配置
        $this->setAdminLog($this->userInfo["admin_id"], 6, $nID, serialize($res) , "后台物理删除合同");
        if ($m) {
            $this->array_return['ResultType'] = self::__OK__;
            $this->array_return['Message'] = "操作成功";
            $this->array_return['AppendData'] = $m;
        } else {
            $this->array_return['ResultType'] = self::__ERROR2__;
            $this->array_return['Message'] = "操作失败";
        }
        
        return json($this->array_return);
    }
    
    /**
     * 根据id查找用户
     */
    public function find_nagent_apply_by_id() 
    {
        $res = $this->request->post();
        $nid = $res['uuid'];
        if (empty($nid)) {
            $this->array_return['ResultType'] = self::__ERROR2__;
            $this->array_return['Message'] = "参数缺失";
            return json($this->array_return);
        }
        
        $m = Db::table("s_api_nagent_apply")->alias("a")->leftJoin("s_api_contract b","a.contract_number=b.nid")->field("a.*,b.contract_id as contract_name")->where(array("a.uuid" => $nid))->find();
        
        $this->kkajaxReturn($m);
    }
    
    /**
     * 增加修改代理数据
     */
    public function i_u_nagent_apply() 
    {
        $res = $this->request->post();
        $nid = $res['uuid'];
        $res["nagent_contracts"] = json_encode($res["nagent_contracts"]);
        $res["nagent_transfer_record"] = json_encode($res["nagent_transfer_record"]);
        $res["nagent_idimgs"] = json_encode($res["nagent_idimgs"]);
        $res["payment_voucher_img"] = json_encode($res["payment_voucher_img"]);
        $res["study_assessment_img"] = json_encode($res["study_assessment_img"]);

        $res = array_unique($res);
        if (empty($res["uuid"])) {
            if (empty($res["contract_name"])) {
                $this->array_return['ResultType'] = self::__ERROR2__;
                $this->array_return['Message'] = "合同编号不存在";
                return json($this->array_return);
            }
            $contract = Db::table("s_api_contract")->where(array("contract_id" => $res["contract_name"]))->column("nid");
            if ($contract == null || $contract == '' || $contract == 0) {
                $this->array_return['ResultType'] = self::__ERROR2__;
                $this->array_return['Message'] = "合同编号不存在";
                return json($this->array_return);
            }
            $res["contract_number"] = $contract;
            $res["createtime"] = date('Y-m-d H:i:s');
            $res["uuid"] =  getRandomString(12);
            $m = Db::table("s_api_nagent_apply")->insert($res);
            //logtype 1 后台用户,2增加权限，3角色，4，代理商申请，5，代理商用户，6合同管理，7，审核配置
            $this->setAdminLog($this->userInfo["admin_id"], 4, $m, serialize($res) , "后台增加代理商申请");
            $this->kkajaxReturn($m);
        }
        
        $apply = Db::table("s_api_nagent_apply")->where(array("uuid" => $nid))->field("nid,old_starte,nstate")->find();
        if ($apply["nstate"] == 5 || $apply["nstate"] == "5") {
            $res["old_starte"] = 0;
            $res["nstate"] = $apply["old_starte"];
        }
        $m = Db::table("s_api_nagent_apply")->where(array("uuid" => $nid))->update($res);
 
        //logtype 1 后台用户,2增加权限，3角色，4，代理商申请，5，代理商用户，6合同管理，7，审核配置
        $this->setAdminLog($this->userInfo["admin_id"], 4, $apply["nid"], serialize($res) , "后台修改代理商申请");
        $this->kkajaxReturn($m);
    }
    
    /**
     * 删除代理用户
     */
    public function del_nagent_user() 
    {
        $res = $this->request->post();
        $nID = $res['nid'];
        if (empty($nID)) {
            $this->array_return['ResultType'] = self::__ERROR2__;
            $this->array_return['Message'] = "操作失败";
            return json($this->array_return);
        }
        $m = Db::table("s_api_nagent_user")->where(array("nid" => $nID))->update(array("nagent_start" => 2));
        //logtype 1 后台用户,2增加权限，3角色，4，代理商申请，5，代理商用户，6合同管理，7，审核配置
        $this->setAdminLog($this->userInfo["admin_id"], 5, $res['nid'], serialize($res) , "非物理删除代理用户");
        if ($m) {
            $this->array_return['ResultType'] = self::__OK__;
            $this->array_return['Message'] = "操作成功";
            $this->array_return['AppendData'] = $m;
        } else {
            $this->array_return['ResultType'] = self::__ERROR2__;
            $this->array_return['Message'] = "操作失败";
        }
        
        return json($this->array_return);
    }
    
    public function f_contract() 
    {
        $res = $this->request->post();
        $n = $res['n'];
        if (empty($n)) {
            $n = 1;
        }
        $map = array();
        if (!empty($res['contract_id'])) {
            $map['a.contract_id'] = array(
                'like',
                "%" . $res['contract_id'] . "%"
            );
        }
        if (!empty($res['contract_type_id'])) {
            $map['a.contract_type_id'] = $res['contract_type_id'];
        }
        if (!empty($res['purchaser_id'])) {
            $map['a.purchaser_id'] = $res['purchaser_id'];
        }
        if (!empty($res['start'])) {
            $map['a.start'] = $res['start'];
        }
        if (!empty($res['using_id'])) {
            $map['a.using_id'] = $res['using_id'];
        }
        
        $pageSize = 10;
        $m = Db::table("s_api_contract");
        $pageTotal = $m->alias("a")->leftJoin("s_api_nagent_user as b","a.purchaser_id=b.nid")->leftJoin("s_api_nagent_apply as c","a.using_id=c.nid")->field("a.*,b.nagent_name,c.nagent_name as cnagent_name")->where($map)->count();
        $list = $m->alias("a")->leftJoin("s_api_nagent_user as b","a.purchaser_id=b.nid")->leftJoin("s_api_nagent_apply as c","a.using_id=c.nid")->field("a.*,b.nagent_name as using_name,c.nagent_name as purchaser_name")->where($map)->order('nid desc')->limit(($n - 1) * $pageSize, $pageSize)->select();
        $datalist = array(
            "list" => $list,
            "pageTotal" => $pageTotal,
            "pageSize" => $pageSize,
            "pageNum" => $n
        );
        if ($datalist) {
            $this->array_return['ResultType'] = self::__OK__;
            $this->array_return['Message'] = "操作成功";
            $this->array_return['AppendData'] = $datalist;
        } else {
            $this->array_return['ResultType'] = self::__ERROR2__;
            $this->array_return['Message'] = "操作失败";
        }
        
        return json($this->array_return);
    }
    
    /**
     * 分页查询数据代理商数据
     */
    public function f_nagent_user() 
    {
        $res = $this->request->post();
        $n = $res['n'] ? $res['n'] : 1;

        $map = array();
        if (!empty($res['nagent_start'])) {
            $map['nagent_start'] = $res['nagent_start'];
        }
        if (!empty($res['nagent_name'])) {
            $map['nagent_name'] = array(
                'like',
                "%" . $res['nagent_name'] . "%"
            );
        }
        if (!empty($res['nagent_phone'])) {
            $map['nagent_phone'] = array(
                'like',
                "%" . $res['nagent_phone'] . "%"
            );
        }
        if (!empty($res['nagent_type_id'])) {
            $map['nagent_type_id'] = $res['nagent_type_id'];
        }
        
        $pageSize = 10;
        $m = Db::table("s_api_nagent_user");
        $pageTotal = $m->where($map)->count();
        $list = $m->where($map)->order('nid desc')->limit(($n - 1) * $pageSize, $pageSize)->select();
        $datalist = array(
            "list" => $list,
            "pageTotal" => $pageTotal,
            "pageSize" => $pageSize,
            "pageNum" => $n
        );
        if ($datalist) {
            $this->array_return['ResultType'] = self::__OK__;
            $this->array_return['Message'] = "操作成功";
            $this->array_return['AppendData'] = $datalist;
        } else {
            $this->array_return['ResultType'] = self::__ERROR2__;
            $this->array_return['Message'] = "操作失败";
        }
        
        return json($this->array_return);
    }
    
    public function one_contract_pay() 
    {
        $res = $this->request->post();
        if (empty($res['using_id']) || empty($res['nID'])) {
            $this->array_return['ResultType'] = self::__ERROR2__;
            $this->array_return['Message'] = "操作失败";
            return json($this->array_return);
        }
        
        $data = array("purchaser_id" => $res['using_id'],"edit_create_time" => date('Y-m-d H:i:s') ,"start" => 2);
        $list = Db::table("s_api_contract")->where(array("nid" => $res['nID']))->update($data);
        //logtype 1 后台用户,2增加权限，3角色，4，代理商申请，5，代理商用户，6合同管理，7，审核配置
        $this->setAdminLog($this->userInfo["admin_id"], 6, $res['nID'], serialize($res) , "非批量确认购买人");
        $this->kkajaxReturn($list);
    }
    
    /**
     * 根据姓名搜索代理名和Id
     */
    public function find_nagent_by_name_list() 
    {
        $res = $this->request->post();
        if (empty($res['name'])) {
            $this->array_return['ResultType'] = self::__ERROR2__;
            $this->array_return['Message'] = "操作失败";
            return json($this->array_return);
        }
        $list = Db::table("s_api_nagent_user")->where(array(
            "nagent_name" => array(
                "like",
                '%' . $res['name'] . '%'
            )
        ))->field("nID as value,nagent_name as label,nagent_phone,nagent_type")->select();
        $this->kkajaxReturn($list);
    }
    
    /**
     * 审核配置页面init数据
     * 初始化数据
     */
    public function init_system_data() 
    {
        $m = Db::table("s_api_nagent_config")->select();
        foreach ($m as $key => $value) {
            $m[$key]["s_key_value"] = html_entity_decode($m[$key]["s_key_value"]);
        }
        $this->kkajaxReturn($m);
    }
    
    /**
     * 保存数据
     * 系统开发者
     *
     */
    public function save_system_data() 
    {
        $res = $this->request->post();
        if (empty($res["config"])) {
            $this->array_return['ResultType'] = self::__ERROR2__;
            $this->array_return['Message'] = "操作失败";
            return json($this->array_return);
        }
        
        $sql = '';
        foreach ($res["config"] as $key => $value) {
            $sql = $sql . 'update s_api_nagent_config set s_key_value=\'' . $value . '\' where s_key_name=\'' . $key . '\'; ';
        }
        $re = DB::execute($sql);
        //logtype 1 后台用户,2增加权限，3角色，4，代理商申请，5，代理商用户，6合同管理，7，审核配置
        $this->setAdminLog($this->userInfo["admin_id"], 7, 0, serialize($res) , "修改审核配置");
        $this->kkajaxReturn($re);
    }
    
    public function setAdminLog($admin_id, $logtype, $operation_data_id, $operation_data_str, $dis) 
    {
        $data = array(
            "admin_id" => $admin_id,
            "logtype" => $logtype,
            "operation_data_id" => $operation_data_id,
            "operation_data_str" => $operation_data_str,
            "dis" => $dis,
            "createtime" => date('Y-m-d H:i:s')
        );
        
        $admin = Db::table("s_api_log")->insert($data);
    }
    
    public function f_admin_log() 
    {
        $res = $this->request->post();
        $n = $res['n'];
        if (empty($n)) {
            $n = 1;
        }
        $map = array();
        if (!empty($res['admin_name'])) {
            $map['b.admin_name'] = array(
                'like',
                "%" . $res['admin_name'] . "%"
            );
        }
        if (!empty($res['logtype'])) {
            $map['a.logtype'] = $res['logtype'];
        }
        $pageSize = 10;
        $m = Db::table("s_api_log");
        $pageTotal = $m->alias("a")->leftJoin("s_api_admin as b","a.admin_id=b.admin_id")->field("a.*,b.admin_account")->where($map)->count();
        $list = $m->alias("a")->leftJoin("s_api_admin as b","a.admin_id=b.admin_id")->field("a.*,b.admin_account,b.admin_name")->where($map)->order('admin_id desc')->limit(($n - 1) * $pageSize, $pageSize)->select();
        $datalist = array(
            "list" => $list,
            "pageTotal" => $pageTotal,
            "pageSize" => $pageSize,
            "pageNum" => $n
        );
        if ($datalist) {
            $this->array_return['ResultType'] = self::__OK__;
            $this->array_return['Message'] = "操作成功";
            $this->array_return['AppendData'] = $datalist;
        } else {
            $this->array_return['ResultType'] = self::__ERROR2__;
            $this->array_return['Message'] = "操作失败";
        }
        
        return json($this->array_return);
    }
    
    /**
     *
     */
    public function agency_excel()
    {
        $res = $this->request->post();
        $n = $res['n'];
        if (empty($n)) {
            $n = 1;
        }
        $map = array();
        if (!empty($res['nagent_name'])) {
            $map['a.nagent_name'] = array(
                'like',
                "%" . $res['nagent_name'] . "%"
            );
        }
        if (!empty($res['nagent_phone'])) {
            $map['a.nagent_phone'] = array(
                'like',
                "%" . $res['nagent_phone'] . "%"
            );
        }
        if (!empty($res['superior_name'])) {
            $map['a.superior_name'] = array(
                'like',
                "%" . $res['superior_name'] . "%"
            );
        }
        if (!empty($res['nagent_type_id'])) {
            $map['a.nagent_type_id'] = $res['nagent_type_id'];
        }
        if (!empty($res['superior_type_id'])) {
            $map['a.superior_type_id'] = $res['superior_type_id'];
        }
        if (!empty($res['createtime_start']) || !empty($res['createtime_end'])) {
            if (!empty($res['createtime_start']) && empty($res['createtime_end'])) {
                $map['a.createtime'] = array(
                    'GT',
                    $res['createtime_start']
                );
            } else if (empty($res['createtime_start']) && !empty($res['createtime_end'])) {
                $map['a.createtime'] = array(
                    'EGT',
                    $res['createtime_end']
                );
            } else {
                $map['a.createtime'] = array(
                    'between',
                    array(
                        $res['createtime_start'],
                        $res['createtime_end']
                    )
                );
            }
        }
        if (!empty($res['state'])) {
            if (empty($res['type'])) {
                $map['a.nstate'] = $res['state'];
            } else if ($res['type'] == 4) {
                $map['a.nstate'] = array(
                    array(
                        "eq",
                        $res['state']
                    ) ,
                    array(
                        "eq",
                        "10"
                    )
                );
            } else if ($res['type'] == 5) {
                $map['a.nstate'] = array(
                    array(
                        "eq",
                        $res['state']
                    ) ,
                    array(
                        "eq",
                        "-1"
                    )
                );
            } else {
                $map['a.nstate'] = array(
                    "neq",
                    array(
                        10, -1
                    )
                );
            }
        }
        if (empty($res['state']) && !empty($res['type'])) {
            if ($res['type'] == 4) {
                $map['a.nstate'] = array(
                    "eq",
                    "10"
                );
            } else if ($res['type'] == 5) {
                $map['a.nstate'] = array(
                    "eq",
                    "-1"
                );
            } else {
                $map['a.nstate'] = array(
                    array(
                        "neq",
                        "10"
                    ) ,
                    array(
                        "neq",
                        "-1"
                    )
                );
            }
        }
     
        $m = Db::table("s_api_nagent_apply");
        $userMod = $m->alias("a")->leftJoin("s_api_contract b","a.contract_number=b.nid")->field("a.nagent_name,a.nagent_phone,
            a.nagent_type,a.superior_name,a.superior_phone,b.contract_id as contract_name,
            a.nstate,a.uuid,a.nagent_id,a.nagent_milebox,a.superior_type,a.createtime,a.remarks")->where($map)->order('a.createtime desc')->select();
        $selectionList = $res["selectionList"];
        $selectionList = explode(",", $selectionList);
        
        $nagent_name = false;
        $nagent_id = false;
        $nagent_type = false;
        $nagent_milebox = false;
        $superior_name = false;
        $superior_phone = false;
        $superior_type = false;
        $createtime = false;
        $contract_name = false;
        $remarks = false;
        $nstate = false;
        $nagent_phone = false;
        for ($i = 0; $i < count($selectionList); $i++) {
            if ($selectionList[$i] == "申请人姓名") {
                $nagent_name = true;
            }
            if ($selectionList[$i] == "申请人手机号") {
                $nagent_phone = true;
            }
            if ($selectionList[$i] == "申请人ID") {
                $nagent_id = true;
            }
            if ($selectionList[$i] == "申请人类型") {
                $nagent_type = true;
            }
            if ($selectionList[$i] == "申请人邮箱") {
                $nagent_milebox = true;
            }
            if ($selectionList[$i] == "推荐人姓名") {
                $superior_name = true;
            }
            if ($selectionList[$i] == "推荐人手机号") {
                $superior_phone = true;
            }
            if ($selectionList[$i] == "推荐人类型") {
                $superior_type = true;
            }
            if ($selectionList[$i] == "创建时间") {
                $createtime = true;
            }
            if ($selectionList[$i] == "合同编号") {
                $contract_name = true;
            }
            if ($selectionList[$i] == "备注") {
                $remarks = true;
            }
            if ($selectionList[$i] == "状态") {
                $nstate = true;
            }
        }
        
        $data = array();
        foreach ($userMod as $k => $goods_info) {
            if ($nagent_name) {
                $data[$k]['申请人姓名'] = $goods_info['nagent_name'];
            }
            if ($nagent_phone) {
                $data[$k]['申请人手机号'] = $goods_info['nagent_phone'];
            }
            if ($nagent_id) {
                $data[$k]['申请人ID'] = "\t" . $goods_info['nagent_id'];
            }
            if ($nagent_type) {
                $data[$k]['申请人类型'] = $goods_info['nagent_type'];
            }
            if ($nagent_milebox) {
                $data[$k]['申请人邮箱'] = $goods_info['nagent_milebox'];
            }
            if ($superior_name) {
                $data[$k]['推荐人姓名'] = $goods_info['superior_name'];
            }
            if ($superior_phone) {
                $data[$k]['推荐人手机号'] = $goods_info['superior_phone'];
            }
            if ($superior_type) {
                $data[$k]['推荐人类型'] = $goods_info['superior_type'];
            }
            if ($createtime) {
                $data[$k]['创建时间'] = "\t" . $goods_info['createtime'];
            }
            if ($contract_name) {
                $data[$k]['合同编号'] = "\t" . $goods_info['contract_name'];
            }
            if ($remarks) {
                $data[$k]['备注'] = $goods_info['remarks'];
            }
            if ($nstate) {
                $data[$k]['状态'] = $goods_info['nstate'];
            }
        }
        
        $headArr = array_keys($data[0]);
        // $headArr=array('用户唯一id','推荐人姓名','姓名','地址','身份证号','性别','民族','出生日期','手机号','年龄','缴纳费用','所属分公司','付费时间','是否线上报名');
        $filename = $res["file_name"] == null ? "代理商数据" : $res["file_name"];
        $this->getExcel($filename, $headArr, $data);
    }
    
    private function getExcel($fileName, $headArr, $data) 
    {
        //导入PHPExcel类库，因为PHPExcel没有用命名空间，只能inport导入
        require_once EXTEND_PATH.'/PHPExcel/PHPExcel.php';
        $date = date("Y_m_d", time());
        $fileName.= "_{$date}.xls";
        //创建PHPExcel对象，注意，不能少了\
        $objPHPExcel = new PHPExcel();
        $objProps = $objPHPExcel->getProperties();
        //设置表头
        $key = ord("A");
        //print_r($headArr);exit;
        foreach ($headArr as $v) {
            $colum = chr($key);
            $objPHPExcel->setActiveSheetIndex(0)->setCellValue($colum . '1', $v);
            $objPHPExcel->setActiveSheetIndex(0)->setCellValue($colum . '1', $v);
            $key+= 1;
        }
        $column = 2;
        $objActSheet = $objPHPExcel->getActiveSheet();
        //print_r($data);exit;
        foreach ($data as $key => $rows) { //行写入
            $span = ord("A");
            foreach ($rows as $keyName => $value) { // 列写入
                $j = chr($span);
                $objActSheet->setCellValue($j . $column, $value);
                $span++;
            }
            $column++;
        }
        $fileName = iconv("utf-8", "gb2312", $fileName);
        //重命名表
        //$objPHPExcel->getActiveSheet()->setTitle('test');
        //设置活动单指数到第一个表,所以Excel打开这是第一个表
        $objPHPExcel->setActiveSheetIndex(0);
        ob_end_clean(); //清除缓冲区,避免乱码
        header('Content-Type: application/vnd.ms-excel');
        header("Content-Disposition: attachment;filename=\"$fileName\"");
        header('Cache-Control: max-age=0');
        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
        $objWriter->update('php://output'); //文件通过浏览器下载
        
        exit;
    }
    
    /**
     * 重复代码
     * @param $datalist
     */
    function kkajaxReturn($datalist) 
    {
        if ($datalist) {
            $this->array_return['ResultType'] = self::__OK__;
            $this->array_return['Message'] = "操作成功";
            $this->array_return['AppendData'] = $datalist;
        } else {
            $this->array_return['ResultType'] = self::__ERROR2__;
            $this->array_return['Message'] = "操作失败";
        }
        
        return json($this->array_return);
    }
    
    /**
     * 根据uuid查找error_list
     */
    public function init_error_list() 
    {
        $res = $this->request->post();
        $uuid = $res["uuid"];
        if (empty($uuid)) {
            $this->array_return['ResultType'] = self::__ERROR2__;
            $this->array_return['Message'] = "非法访问";
            return json($this->array_return);
        }
        $m = Db::table("s_api_audit_error_log")->alias("a")->leftJoin("s_api_nagent_apply b","a.nagent_apply_id=b.nid")->field("a.dis")->where(array("b.uuid" => $uuid))->select();
        
        $this->kkajaxReturn($m);
    }

}

