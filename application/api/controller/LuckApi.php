<?php
namespace app\api\controller;
use think\Controller;
use app\common\controller\ApiBase;
use think\Db;

class LuckApi extends  ApiBase 
{
    /**
     * 获取当前用户积分
     */
    public function get_integral()
    {
        $uid = $this->userInfo['id'];
        $res = Db::table("s_user_detail")->where('id='.$uid)->field('inte')->find();
        if ($res) {
            $this->array_return['ResultType'] = self::__OK__;
            $this->array_return['Message'] = "操作成功";
            $this->array_return['AppendData'] = $res;
        } else {
            $this->array_return['ResultType'] = self::__ERROR2__;
            $this->array_return['Message'] = "操作失败";
        }
        
        return json($this->array_return);
    }

    /**
     * 获取抽奖参数
     */
    public function get_parameter()
    {
        $res = Db::table("s_luck")->field('id,name,url,inventory,true_prize,status')->where(array('online'=>0))->select();
        if ($res) {
            $this->array_return['ResultType'] = self::__OK__;
            $this->array_return['Message'] = "操作成功";
            $this->array_return['AppendData'] = $res;
        } else {
            $this->array_return['ResultType'] = self::__ERROR2__;
            $this->array_return['Message'] = "操作失败";
        }
        
        return json($this->array_return);
    }

    /**
     * 获取img图片地址
     */
    public function get_imgs()
    {
        $name = $this->_reqparam['name'];
        $res = Db::table("s_luck")->where(array('name'=>$name))->field('id,url')->find();

        if ($res) {
            $this->array_return['ResultType'] = self::__OK__;
            $this->array_return['Message'] = "操作成功";
            $this->array_return['AppendData'] = $res;
        } else {
            $this->array_return['ResultType'] = self::__ERROR2__;
            $this->array_return['Message'] = "操作失败";
        }
        
        return json($this->array_return);
    }

    /**
     * 收货地址列表
     */
    public function get_address()
    {
        $uid = $this->userInfo['id'];
        $res = Db::table("s_luck_address")->alias('l')->leftJoin(' s_region r','r.region_id = l.province_id')
            ->leftJoin(' s_region re','re.region_id = l.city_id')->field('l.*,r.region_name as province_name,re.region_name as city_name')
            ->where(array('l.user_id'=>$uid))->select();
        if ($res) {
            $this->array_return['ResultType'] = self::__OK__;
            $this->array_return['Message'] = "操作成功";
            $this->array_return['AppendData'] = $res;
        } else {
            $this->array_return['ResultType'] = self::__ERROR2__;
            $this->array_return['Message'] = "操作失败";
        }
        
        return json($this->array_return);
    }

    /**
     * 删除收货地址
     */
    public function del_address()
    {
        $uid = $this->userInfo['id'];
        $id = $this->_reqparam['aid'];
        if($id){
            $res = Db::table("s_luck_address")->where(array('user_id'=>$uid,'id'=>$id))->delete();
            if ($res) {
                $this->array_return['ResultType'] = self::__OK__;
                $this->array_return['Message'] = "删除成功";
                $this->array_return['AppendData'] = $res;
            } else {
                $this->array_return['ResultType'] = self::__ERROR2__;
                $this->array_return['Message'] = "删除失败";
            }    
        }else{
            $this->array_return['ResultType'] = self::__ERROR__;
            $this->array_return['Message'] = "缺少参数";
        }
        
        return json($this->array_return);
    }

     /**
     *  新增收货地址
     */
    public function add_address()
    {
        $oid = $this->_reqparam['oid'];
        $user_id = $this->userInfo['id'];
        $data['user_id'] = $user_id;
        $data['collect_people'] = $this->_reqparam['name'];
        $data['telephone'] = $this->_reqparam['tel'];
//        $data['address'] = $this->_reqparam['address'];
        if($this->_reqparam['province_id']){
            $data['province_id'] = $this->_reqparam['province_id'];
        }
        if($this->_reqparam['city_id']){
            $data['city_id'] = $this->_reqparam['city_id'];
        }
        $data['detailed_address'] = $this->_reqparam['detail_add'];
        $data['is_enabled'] = $this->_reqparam['is_true']?$this->_reqparam['is_true']:1;
        $data['add_time'] = time();
        if($data['collect_people'] && $data['telephone'] && $data['detailed_address']){
            if($oid>0){
                $dat['is_enabled']=0;
                $clear = Db::table('s_luck_address')->where(['user_id' => $user_id])->update($dat);
                $res = Db::table('s_luck_address')->where(['id' => $oid])->update($data);
                if ($res) {
                    $this->array_return['ResultType'] = self::__OK__;
                    $this->array_return['Message'] = "修改成功";
                    $this->array_return['AppendData'] = $res;
                } else {
                    $this->array_return['ResultType'] = self::__ERROR2__;
                    $this->array_return['Message'] = "修改失败";
                }
            }else{
                $dat['is_enabled']=0;
                $clear = Db::table('s_luck_address')->where(['user_id' => $user_id])->update($dat);
                $res = Db::table('s_luck_address')->insert($data);
                if ($res) {
                    $this->array_return['ResultType'] = self::__OK__;
                    $this->array_return['Message'] = "新增成功";
                    $this->array_return['AppendData'] = $res;
                } else {
                    $this->array_return['ResultType'] = self::__ERROR2__;
                    $this->array_return['Message'] = "新增失败";
                }
            }
        }else{
            $this->array_return['ResultType'] = self::__ERROR__;
            $this->array_return['Message'] = "缺少参数";
        }
        
        return json($this->array_return);
    }

    /**
     *  获取奖品列表
     */    
    public function luck_list()
    {
        $id = $this->userInfo['id'];
        $type = $this->_reqparam['type'];
        $start = $this->_reqparam['start']?$this->_reqparam['start']:0;
        $length = $this->_reqparam['length']?$this->_reqparam['length']:10;
        $where['o.uid'] = $id;
        if(isset($type)){
            if($type == 2){
                $where['l.status'] = array('in',array(2,3));
            }else{
                $where['l.status'] = $type;
            }
        }
        // if($type){
            // $res = Db::table('s_luck_order')->where('uid='.$id)->select();
            $res = Db::table("s_luck_order")->alias('o')
                ->where($where)
                ->join('LEFT JOIN s_luck l','o.lid=l.id')
                ->field('o.*,l.name,l.url,l.status,l.content')
                ->order('o.id desc')
                ->limit($start,$length)
                ->select();

                foreach ($res as $k=>$v){
                    if($v['status'] == 2){
                        $code = Db::table('s_pay_code')->where(array('id'=>$v['aid'],'status'=>2))->column('code');
                        if($code){
                            $res[$k]['content'] = $code;
                        }
                    }elseif($v['status'] == 3){
                        $code =  Db::table('s_oil_card')->where(array('id'=>$v['aid'],'status'=>1))->field('code,code_pwd')->find();
                        if($code){
                            $res[$k]['content'] = $code['code'];
                            $res[$k]['content_pwd'] = $code['code_pwd'];
                        }
                    }
                }

            if ($res) {
                $this->array_return['ResultType'] = self::__OK__;
                $this->array_return['Message'] = "操作成功";
                $this->array_return['AppendData'] = $res;
            } else {
                $this->array_return['ResultType'] = self::__ERROR2__;
                $this->array_return['Message'] = "操作失败";
            }
        // }else{
        //     $this->array_return['ResultType'] = self::__ERROR__;
        //     $this->array_return['Message'] = "缺少参数";
        // }
        
        return json($this->array_return);
    }

    /**
     *  滚动字幕
     */
    public function luck_font()
    {
       $res = Db::table("s_luck_order")->alias('o')->join(' LEFT JOIN s_luck l','o.lid=l.id')->join(' LEFT JOIN s_user_detail u','o.uid=u.id')->field('o.id as id,l.name,u.mobile')->order('o.id desc')->limit(10)->select();
       if ($res) {
            $this->array_return['ResultType'] = self::__OK__;
            $this->array_return['Message'] = "获取成功";
            $this->array_return['AppendData'] = $res;
        } else {
            $this->array_return['ResultType'] = self::__ERROR2__;
            $this->array_return['Message'] = "获取失败";
        }
        
        return json($this->array_return);
    }

    /**
     *  修改奖品地址
     */
    public function luck_edit()
    {
       $aid = $this->_reqparam['aid'];
       $lucks = $this->_reqparam['lucks'];
       if($aid && $lucks){
            $arr=explode(",",$lucks);
           foreach($arr as $v){
                $dat['aid']=$aid;
                $tail = Db::table("s_luck_address")->where('id='.$aid)->field('collect_people,telephone,province_id,city_id,detailed_address')->find();
                $dat['collect_people']=$tail['collect_people'];
                $dat['telephone']=$tail['telephone'];
                $dat['province_id']=$tail['province_id'];
                $dat['city_id']=$tail['city_id'];
                $dat['detailed_address']=$tail['detailed_address'];
                $dat['is_true']=2;
                Db::table('s_luck_order')->where('id='.$v)->update($dat);
           }
           if ($lucks) {
                $this->array_return['ResultType'] = self::__OK__;
                $this->array_return['Message'] = "操作成功";
            } else {
                $this->array_return['ResultType'] = self::__ERROR2__;
                $this->array_return['Message'] = "操作失败";
            }
       }else{
            $this->array_return['ResultType'] = self::__ERROR__;
            $this->array_return['Message'] = "缺少参数";
       }
       
       return json($this->array_return);
    }

    /**
     *  获取积分方法列表
     */
    public function integral_method()
    {
       $res = Db::table("s_inte")->where('type=0')->field('id,title')->select();  
       if ($res) {
            $this->array_return['ResultType'] = self::__OK__;
            $this->array_return['Message'] = "获取成功";
            $this->array_return['AppendData'] = $res;
        } else {
            $this->array_return['ResultType'] = self::__ERROR2__;
            $this->array_return['Message'] = "修改失败";
        }
        
        return json($this->array_return);
    } 
 
    //抽奖随机数
    public function get_rand($proArr) 
    {   
        $result = '';   
        //概率数组的总概率精度  
        $proSum = array_sum($proArr);   
        //概率数组循环    
        foreach ($proArr as $key => $proCur) {   
            $randNum = mt_rand(1, $proSum);               
            if ($randNum <= $proCur) {   
                $result = $key;                         
                break;   
            } else {   
                $proSum -= $proCur;                       
            }   
        }   
        unset ($proArr);   
        return $result;   
    } 

    /**
     *  获取抽中奖品接口
     */
    public function get_lucklist()
    {
       $uid = $this->userInfo['id'];
       $inte = Db::table("s_user_detail")->where(array('id'=>$uid))->column('inte');
       $jian = Db::table("s_inte")->where(array('id'=>5))->column('inte');
       $state=0;
       if($inte<$jian){
           $state=1;
       }else{
           $jixu=Db::table("s_user_detail")->where(array('id'=>$uid))->setDec('inte',$jian);
           if($jixu){
                $prize_arr = Db::table("s_luck")->field('name as prize,true_prize as rate')->where(array('online'=>0))->select();
                foreach ($prize_arr as $key => $val) {     
                    $prize_arr[$key]['id'] = $key;     
                }
                foreach ($prize_arr as $key => $val) {
                    $arr[$val['id']] = $val['rate'];     
                }     
                $rid = $this->get_rand($arr); //根据概率获取奖项id
                // $res['yes'] = $prize_arr[$rid]['prize']; //中奖项 
                $res['yes']=Db::table("s_luck")->where(array('name'=>$prize_arr[$rid]['prize']))->find();
                $cyid=Db::table("s_luck")->order('id asc')->limit(1)->column('id');
                if($res['yes']['id']!=$cyid){
                    Db::table("s_luck")->where(array('id'=>$res['yes']['id']))->setDec('inventory');
                    if($res['yes']['inventory']<=1){
                        //当没有库存的时候  中奖几率改为0
                        Db::table("s_luck")->where(array('id'=>35))->setInc('true_prize',$res['yes']['true_prize']);
                        Db::table("s_luck")->where(array('id'=>$res['yes']['id']))->update(array('true_prize'=>0));
                    }
                }
                $data['uid'] = $uid;
                $data['lid'] = $res['yes']['id'];
                $data['aid'] = 0;
               if($res['yes']['status']==0) {
                   //积分
                   $data['is_true'] = 1;//订单状态结束
                   Db::table("s_user_detail")->where(array('id'=>$uid))->setInc('inte', $res['yes']['content']);
               }elseif($res['yes']['status'] == 2) {
                   //月卡
                   $pay_id = Db::table('s_pay_code')->where(array('type' => 1, 'status' => 0))->order('id asc')->column('id');
                   if ($pay_id) {
                       $data['is_true'] = 1;//订单状态结束
                       Db::table('s_pay_code')->where(array('id' => $pay_id))->update(array('status' => 2));
                       $data['aid'] = $pay_id;
                   }else{
                       $this->array_return['ResultType'] = self::__ERROR2__;
                       $this->array_return['Message'] = "修改失败";
                       return json($this->array_return);
                   }
               }elseif($res['yes']['status'] == 3){
                    //油卡
                   $oil_id = Db::table('s_oil_card')->where(array('status'=>0))->order('id asc')->column('id');
                   if($oil_id){
                       $data['is_true'] = 1;//订单状态结束
                       Db::table('s_oil_card')->where(array('id'=>$oil_id))->update(array('status'=>1));
                       $data['aid'] = $oil_id;
                   }else{
                       $this->array_return['ResultType'] = self::__ERROR2__;
                       $this->array_return['Message'] = "修改失败";
                       return json($this->array_return);
                   }
               }else{
                   $data['is_true'] = 0;
               }
                $data['addtime'] = time();
                $data['type'] = $res['yes']['status'];
                $last_id = Db::table('s_luck_order')->insert($data);
                $dl['user_id'] = $uid;
                $dl['inte'] = $jian;
                $dl['addtime'] = time();
                $dl['type'] = 1;
                $dl['inte_id'] = 5;
                Db::table('s_inte_log')->insert($dl);
                
                $res['yes']['oid']=$last_id;
                unset($prize_arr[$rid]); //将中奖项从数组中剔除，剩下未中奖项     
                shuffle($prize_arr); //打乱数组顺序     
                for($i=0;$i<count($prize_arr);$i++){     
                    // $pr[] = $prize_arr[$i]['prize'];
                    $pr[] = Db::table("s_luck")->where(array('name'=>$prize_arr[$i]['prize']))->field('id,name,url,add_time')->find();
                }     
                $res['no'] = $pr;   //未中奖项 
                $inte=Db::table("s_user_detail")->where(array('id'=>$uid))->column('inte');
                $res['inte'] = $inte; 
           }
       }
      
        if($state==0){
            $this->array_return['ResultType'] = self::__OK__;
            $this->array_return['Message'] = "获取成功";
            if($this->_reqparam['is_app'] == 1){
                $this->array_return['AppendData'] = $res['yes'];
            }else{
                $this->array_return['AppendData'] = $res;
            }
        }else if($state==1){
            if($this->_reqparam['is_app'] == 1){
                $this->array_return['ResultType'] = self::__ERROR1__;
            }else{
                $this->array_return['ResultType'] = self::__OK__;
            }
           $this->array_return['Message'] = "很遗憾您抽奖积分不足";
           $this->array_return['AppendData'] = $inte;
        }else{
           $this->array_return['ResultType'] = self::__ERROR2__;
           $this->array_return['Message'] = "修改失败"; 
        }
        
        return json($this->array_return);
    }  

     /**
     *  获取奖品信息接口
     */
    public function get_luckparam()
    {
       $res = Db::table("s_luck")->field('id,name,url,add_time')->where(array('online'=>0))->select(); //->field('name as prize,true_prize as rate')
       if ($res) {
            $this->array_return['ResultType'] = self::__OK__;
            $this->array_return['Message'] = "获取成功";
            $this->array_return['AppendData'] = $res;
        } else {
            $this->array_return['ResultType'] = self::__ERROR2__;
            $this->array_return['Message'] = "修改失败";
        }
        
        return json($this->array_return);
    }

}