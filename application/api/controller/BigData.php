<?php
namespace app\api\controller;
use think\Controller;
use think\Db;

class BigData extends Controller 
{
    const __OK__ = '0'; //请求成功
    const __ERROR__ = '1'; //参数错误
    const __ERROR1__ = '2'; //没有绑定
    const __ERROR2__ = '3';//数据库错误

    private $array_return = array("ResultType"=>1,"Message"=>"success","AppendData"=>[]);
    private $_reqparam;
    
    public function initialize()
    {
         $this->_reqparam = $this->request->param();
    }

    /**
     * javatom
     * 20180613
     * 1.得到近七天的会员增长总数，缴费总数，未缴费总数
     * http://localhost:801/Api/BigData/get_vip_7
     */
    public function  get_vip_7()
    {
        $sql = "SELECT DATE_FORMAT(FROM_UNIXTIME(add_time),'%Y-%m-%d')  as time ,count(*)as num,sum(case vip_group_id when 1 then 1 else 0 end) as 'chuanke',sum(case vip_group_id when 0 then 1 else 0 end) as 'weifuhuei',
		sum(case vip_group_id when 8 then 1 else 0 end) as 'yekatiyan' FROM s_user_detail where DATE_SUB(CURDATE(), INTERVAL 7 DAY) <=FROM_UNIXTIME(add_time)
		GROUP BY  time";
        
        $this->kkexecute($sql);
    }

    /**
     * javatom
     * 20180613
     * 1.得到近30天的会员增长总数，缴费总数，未缴费总数
     * http://localhost:801/Api/BigData/get_vip_30
     */
    public function  get_vip_30()
    {
        $sql = "SELECT DATE_FORMAT(FROM_UNIXTIME(add_time),'%Y-%m-%d')  as time ,
		count(*)as num,
		sum(case vip_group_id when 1 then 1 else 0 end) as 'chuanke',
		sum(case vip_group_id when 0 then 1 else 0 end) as 'weifuhuei',
		sum(case vip_group_id when 8 then 1 else 0 end) as 'yekatiyan'
		FROM s_user_detail where DATE_SUB(CURDATE(), INTERVAL 30 DAY) <=FROM_UNIXTIME(add_time)
		GROUP BY  time";
        
        $this->kkexecute($sql);
    }
    
    /**
     * javatom
     * 20180613
     * 1.到近个六月的会员增长总数，缴费总数，未缴费总数
     * http://localhost:801/Api/BigData/get_vip_now_6
     */
    public function  get_vip_now_6()
    {
        $sql = "SELECT DATE_FORMAT(FROM_UNIXTIME(add_time),'%Y-%m')  as time ,
		count(*)as num,
		sum(case vip_group_id when 1 then 1 else 0 end) as 'chuanke',
		sum(case vip_group_id when 0 then 1 else 0 end) as 'weifuhuei',
		sum(case vip_group_id when 7 then 1 else 0 end) as 'yekatiyan'
		FROM s_user_detail where FROM_UNIXTIME(add_time) between date_sub(now(),interval 6 month) and now()
		GROUP BY  time";
        
        $this->kkexecute($sql);
    }
    
    /**
     * javatom
     * 20180613
     * 4.得到近六月份以来各个年龄段详细数据
     * http://localhost:801/Api/BigData/get_vip_age
     * sum(case age_id when 1 then 1 else 0 end) as '10岁以下',
    sum(case age_id when 2 then 1 else 0 end) as '11-20岁',
    sum(case age_id when 3 then 1 else 0 end) as '21-25岁',
    sum(case age_id when 4 then 1 else 0 end) as '26-30岁',
    sum(case age_id when 5 then 1 else 0 end) as '31-35岁',
    sum(case age_id when 6 then 1 else 0 end) as '36-40岁',
    sum(case age_id when 7 then 1 else 0 end) as '41-45岁',
    sum(case age_id when 8 then 1 else 0 end) as '50岁以上'
     */
    public function  get_vip_age_now_6()
    {
        $sql = "select DATE_FORMAT(FROM_UNIXTIME(add_time),'%Y-%m')  as time,count(*) as num ,
		sum(case age_id when 1 then 1 else 0 end) as 'data1',
		sum(case age_id when 2 then 1 else 0 end) as 'data2',
		sum(case age_id when 3 then 1 else 0 end) as 'data3',
		sum(case age_id when 4 then 1 else 0 end) as 'data4',
		sum(case age_id when 5 then 1 else 0 end) as 'data5',
		sum(case age_id when 6 then 1 else 0 end) as 'data6',
		sum(case age_id when 0 then 1 else 0 end) as 'data7',
		sum(case age_id when 7 then 1 else 0 end) as 'data8'
		from s_user_detail  where FROM_UNIXTIME(add_time) between date_sub(now(),interval 6 month) and now()
		GROUP BY time";
        
        $this->kkexecute($sql);
    }

    /**
     * javatom
     * 20180613
     * 4.得到所有的年龄段的数据
     * http://localhost:801/Api/BigData/get_vip_age_all
     */
    public function  get_vip_age_all()
    {
        $sql = "select DATE_FORMAT(FROM_UNIXTIME(add_time),'%Y-%m')  as time,count(*) as num ,
		sum(case age_id when 1 then 1 else 0 end) as 'data1',
		sum(case age_id when 2 then 1 else 0 end) as 'data2',
		sum(case age_id when 3 then 1 else 0 end) as 'data3',
		sum(case age_id when 4 then 1 else 0 end) as 'data4',
		sum(case age_id when 5 then 1 else 0 end) as 'data5',
		sum(case age_id when 6 then 1 else 0 end) as 'data6',
		sum(case age_id when 0 then 1 else 0 end) as 'data7',
		sum(case age_id when 7 then 1 else 0 end) as 'data8'
		from s_user_detail GROUP BY time";
        
        $this->kkexecute($sql);
    }

    /**
     * javatom
     * 20180613
     * 5.得到各个省份用户的创客，创业家，创导，创业领袖，未付费,体验用户
     * http://localhost:801/Api/BigData/get_vip_province_all
     */
    public function  get_vip_province_all()
    {
        $datatype=$this->_reqparam["datatype"];
        if ($datatype==0) {
            $datatype="a.city_id";
        }elseif ($datatype==1) {
            $datatype="a.province_id";
        }
        $sql = "select b.region_name,count(*) as num,
		sum(case vip_group_id when 1 then 1 else 0 end) as 'chuanke',
		sum(case vip_group_id when 3 then 1 else 0 end) as 'chuangto',
		sum(case vip_group_id when 4 then 1 else 0 end) as 'chuangyejia',
		sum(case vip_group_id when 5 then 1 else 0 end) as 'chuangyedaoshi',
		sum(case vip_group_id when 6 then 1 else 0 end) as 'chuanyelinxiu',
		sum(case vip_group_id when 0 then 1 else 0 end) as 'weifuhuei',
		sum(case vip_group_id when 7 then 1 else 0 end) as 'yekatiyan'
		from s_user_detail a left join s_region b','$datatype=b.region_id GROUP BY b.region_name";
        
        $this->kkexecute($sql);
    }

    /**
     *
     * 得到客服记录
     * 1.需要查询六次数据库
     * 2.type 类型
     * 1,今天，2，昨天，3，后天，4.近七天，5，本周，6，上周，7当前月份，8距离当前现在6个月的数据，9本年数据
     */
    public function get_customer_list()
    {
        $end = $this->_reqparam["end"];
        $start = $this->_reqparam["start"];
        $where="where 1=1 ";
       
        if(!empty($end)){
            $where = $where." and a.createtime < '".$end."'";
        }else{
            $where = $where." and a.createtime < '".date('Y-m-d H:i:s')."'";
        }
        if(!empty($start)){
            $where = $where." and a.createtime > '".$start."'";
        }else{
            $where = $where." and a.createtime > '".date('Y-m-d'.' 00:00:00',time())."'";
        }

        $sql = "select b.cname,count(*) as num from `s_k_reception` a
		LEFT JOIN s_k_customer_service b','a.one_customer_id = b.sid
		".$where." GROUP BY cname";
        //得到客服
        $list1 = Db::query($sql);
        $sql = "select b.cname,count(*) as num from `s_k_reception` a
		LEFT JOIN s_k_customer_service b','a.two_customer_id = b.sid
		".$where." and a.two_customer_id is not NULL GROUP BY cname";
        $list2 = Db::query($sql);
        //得到初次问题
        $sql = "select b.cname,count(*) as num from `s_k_posts` a LEFT JOIN s_k_customer_service b','a.customer_id = b.sid".$where."  GROUP BY cname";
        $list3 = Db::query($sql);
        $sql = "select b.cname,count(*) as num from `s_k_call` a LEFT JOIN s_k_customer_service b','a.customer_id = b.sid".$where."  GROUP BY cname";
        $list4 = Db::query($sql);
        //and isfalg=1 and isfalg is not null
        $sql = "select cname from s_k_customer_service where c_start=2 ";
        $list5 = Db::query($sql);
        $result = array("list1"=>$list1,"list2"=>$list2,"list3"=>$list3,"list4"=>$list4,"list5"=>$list5);
                    
        $this->array_return['ResultType'] = self::__ERROR2__;
        $this->array_return['Message'] = "操作失败";
        if ($result) 
        {
            $this->array_return['ResultType'] = self::__OK__;
            $this->array_return['Message'] = "操作成功";
            $this->array_return['AppendData'] = $returndata;
        }
        
        return json($this->array_return);
    }

    /**简单封装，用于换回模板数据
     * @param $sql
     * @param $type
     */
    public function kkexecute($sql)
    {
        if(strstr($sql,"select") || strstr($sql,"SELECT")){
            $result = Db::query($sql);
        }else{
            $result = Db::execute($sql);
        }
        
        $this->array_return['ResultType'] = self::__ERROR2__;
        $this->array_return['Message'] = "操作失败";
        if ($result) {
            $this->array_return['ResultType'] = self::__OK__;
            $this->array_return['Message'] = "操作成功";
            $this->array_return['AppendData'] = $result;
        } 
        
        return json($this->array_return);
    }
}