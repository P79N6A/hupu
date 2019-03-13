<?php
namespace app\api\controller;
use app\common\controller\ApiBase;
use think\Controller;
use think\Db;

class VcardApi extends  ApiBase 
{
    /**
     * v网复制
     */
    public function copy_card()
    {
        $banner = $this->_reqparam['banner'];//0:不操作  1:追加  2:替换
        $link = $this->_reqparam['link'];//0:不操作  1:追加  2:替换
        $product = $this->_reqparam['product'];//0:不操作  1:追加  2:替换
        $show_list = $this->_reqparam['show_list'];//0:不操作  1:追加  2:替换
        $user_id = $this->_reqparam['user_id'];//被复制人的id

        $copy_user = Db::table('s_user_detail')->where(array('id'=>$user_id))->column('id');
        if(!$copy_user || !$user_id){
            $this->array_return['ResultType'] = self::__ERROR1__;
            $this->array_return['Message'] = "参数有误";
            return json($this->array_return);
        }

        if($banner == 1 || $banner == 2){//banner
            $banner_array = array();
            if($banner == 1){
                $banner_sort = Db::table('s_user_img')->where(array('user_id'=>$this->userInfo['id']))->max('sort');
                $banner_count = Db::table('s_user_img')->where(array('user_id'=>$this->userInfo['id']))->count();
            }else{
                $res = Db::table('s_user_img')->where(array('user_id'=>$this->userInfo['id']))->find();
                if($res){
                    Db::table('s_user_img')->where(array('user_id'=>$this->userInfo['id']))->delete();
                }
                $banner_sort = 0;
            }
            $banners = Db::table('s_user_img')->where(array('user_id'=>$user_id))->order('sort asc')->select();
            foreach ($banners as $k=>$value){
                if($banner_count >= 9){
                    break;
                }
                $banner_count++;
                $banner_sort++;
                $_data['user_id']=$this->userInfo['id'];
                $_data['img_url']=$value['img_url'];
                $_data['createtime']=time();
                $_data['sort'] = $banner_sort;
                $banner_array[] = $_data;
            }
            Db::table('s_user_img')->where(array('user_id'=>$this->userInfo['id']))->addAll($banner_array);
        }
        
        if($link == 1 || $link == 2){//链接一切
            $nav_array = array();
            if($link == 1){
                $nav_sort = Db::table('s_users_nav')->where(array('user_id'=>$this->userInfo['id']))->max('sort');
            }else{
                $res = Db::table('s_users_nav')->where(array('user_id'=>$this->userInfo['id']))->find();
                if($res){
                    Db::table('s_users_nav')->where(array('user_id'=>$this->userInfo['id']))->delete();
                }
                $nav_sort = 0;
            }
            $users_nav = Db::table('s_users_nav')->where(array('user_id'=>$user_id))->order('sort asc')->select();
            foreach ($users_nav as $k=>$value){
                $nav_sort++;
                $_data1['user_id']=$this->userInfo['id'];
                $_data1['name']=$value['name'];
                $_data1['icon_url']=$value['icon_url'];
                $_data1['jump_url']=$value['jump_url'];
                $_data1['add_time']=time();
                $_data1['sort'] = $nav_sort;
                $nav_array[] = $_data1;
            }
            Db::table('s_users_nav')->where(array('user_id'=>$this->userInfo['id']))->addAll($nav_array);
        }
        
        if($product == 1 || $product == 2){//产品中心
            if($product == 2){
                $p_old = Db::table('s_product')->field('id')->where(array('user_id'=>$this->userInfo['id']))->select();
                if($p_old){
                    $ids = '';
                    foreach ($p_old as $v){
                        $ids .= $v['id'].',';
                    }
                    $ids = rtrim($ids, ',');
                    Db::table('s_product')->field('id')->where(array('user_id'=>$this->userInfo['id']))->delete();
                    $d_old = Db::table('s_product_detail')->where(array('product_id'=>array('in',$ids)))->find();
                    if($d_old){
                        Db::table('s_product_detail')->where(array('product_id'=>array('in',$ids)))->delete();
                    }
                    $i_old = Db::table('s_product_img')->where(array('product_id'=>array('in',$ids)))->find();
                    if($i_old){
                        Db::table('s_product_img')->where(array('product_id'=>array('in',$ids)))->delete();
                    }
                }
            }
            $products = Db::table('s_product')->where(array('user_id'=>$user_id))->select();
            $detail_array = array();
            $img_array = array();
            foreach ($products as $value){
                $_data2['user_id']=$this->userInfo['id'];
                $_data2['title']=$value['title'];
                $_data2['cover_img']=$value['cover_img'];
                $_data2['type']=$value['type'];
                $_data2['type_id']=$value['type_id'];
                $_data2['address']=$value['address'];
                $_data2['price']=$value['price'];
                $_data2['is_home']=$value['is_home'];
                $_data2['addtime']=time();
                $pro_id = Db::table('s_product')->insert($_data2);
                if($pro_id){
                    $detail = Db::table('s_product_detail')->where(array('product_id'=>$value['id']))->select();
                    if($detail){
                        foreach ($detail as $d){
                            $_d['title'] = $d['title'];
                            $_d['content'] = $d['content'];
                            $_d['product_id'] = $pro_id;
                            $_d['addtime'] = time();
                            $detail_array[] = $_d;
                        }
                    }
                    $img = Db::table('s_product_img')->where(array('product_id'=>$value['id']))->select();
                    if($img){
                        foreach ($img as $i){
                            $_i['img'] = $i['img'];
                            $_i['type'] = $i['type'];
                            $_i['sort'] = $i['sort'];
                            $_i['product_id'] = $pro_id;
                            $_i['addtime'] = time();
                            $img_array[] = $_i;
                        }
                    }
                }
            }
            Db::table('s_product_detail')->addAll($detail_array);
            Db::table('s_product_img')->addAll($img_array);
        }

        if($show_list == 1 || $show_list == 2){//展示一切
            if($show_list == 1){
                $c_sort = Db::table('s_card_content')->where(array('user_id'=>$this->userInfo['id']))->max('sort');
            }else{
                $c_old = Db::table('s_card_content')->where(array('user_id'=>$this->userInfo['id']))->select();
                if($c_old){
                    $c_ids = '';
                    foreach ($c_old as $v){
                        $c_ids .= $v['id'].',';
                    }
                    $c_ids = rtrim($c_ids, ',');
                    Db::table('s_card_content')->where(array('user_id'=>$this->userInfo['id']))->delete();
                    $cr_old = Db::table('s_card_content_res')->where(array('card_content_id'=>array('in',$c_ids)))->find();
                    if($cr_old){
                        Db::table('s_card_content_res')->where(array('card_content_id'=>array('in',$c_ids)))->delete();
                    }
                }

                $c_sort = 0;
            }
            $content = Db::table('s_card_content')->where(array('user_id'=>$user_id))->order('sort asc')->select();
            $res_array = array();
            foreach ($content as $c){
                $c_sort++;
                $con['title'] = $c['title'];
                $con['titles'] = $c['titles'];
                $con['thumb'] = $c['thumb'];
                $con['content'] = $c['content'];
                $con['user_id'] = $this->userInfo['id'];
                $con['add_time'] = time();
                $con['sort'] = $c_sort?$c_sort:0;
                $new_id = Db::table('s_card_content')->insert($con);
                if($new_id){
                    $res = Db::table('s_card_content_res')->where(array('card_content_id'=>$c['id']))->select();
                    foreach ($res as $r){
                        $result['card_content_id'] = $new_id;
                        $result['type'] = $r['type'];
                        $result['data_url'] = $r['data_url'];
                        $result['thumb'] = $r['thumb'];
                        $result['sort'] = $r['sort'];
                        $result['user_id'] = $this->userInfo['id'];
                        $result['add_time'] = time();
                        $res_array[] = $result;
                    }
                }
            }
            if($res_array){
                Db::table('s_card_content_res')->addAll($res_array);
            }
        }
        
        $this->array_return['ResultType'] = self::__OK__;
        $this->array_return['Message'] = "操作成功";
        return json($this->array_return);
    }

    /**
     * 4.0的v网复制
     * */
    public function new_copy_card()
    {
        $link = $this->_reqparam['link'];//0:不操作  2:替换
        $product = $this->_reqparam['product'];//0:不操作  2:替换
        $video = $this->_reqparam['video'];//0:不操作  2：替换
        $show_list = $this->_reqparam['show_list'];//0:不操作  2:替换
        $uid = $this->userInfo['id'];//复制人的id
        $user_id = $this->_reqparam['user_id'];//被复制人的id
        
        $copy_user = Db::table('s_user_detail')->where(array('id'=>$user_id))->column('id');
        if(!$copy_user || !$user_id || !$uid){
            $this->array_return['ResultType'] = self::__ERROR1__;
            $this->array_return['Message'] = "参数有误";
            return json($this->array_return);
        }

        if($link == 2){//链接一切
            $nav_type = array();
            $nav_array = array();
            $type_res = Db::table('s_mode_type')->where(array('user_id'=>$uid,'type'=>0))->find();
            if($type_res){
                Db::table('s_mode_type')->where(array('user_id'=>$uid,'type'=>0))->delete();
            }
            $nav_types = Db::table('s_mode_type')->where(array('user_id'=>$user_id,'type'=>0))->order('sort asc')->select();
            foreach ($nav_types as $k=>$value){
                $_data1_type['user_id']=$uid;
                $_data1_type['title']=$value['title'];
                $_data1_type['type']=0;
                $_data1_type['sort']=$value['sort'];
                $_data1_type['mode_id']=$value['mode_id'];
                $_data1_type['addtime']=date('Y-m-d H:i:s');
                $nav_type[] = $_data1_type;
            }
            if($nav_type){
                Db::table('s_mode_type')->where(array('user_id'=>$uid,'type'=>0))->addAll($nav_type);
            }
            $res = Db::table('s_users_nav')->where(array('user_id'=>$uid))->find();
            if($res){
                Db::table('s_users_nav')->where(array('user_id'=>$uid))->delete();
            }
            $users_nav = Db::table('s_users_nav')->where(array('user_id'=>$user_id))->order('sort asc')->select();
            foreach ($users_nav as $k=>$value){
                $_data1['user_id']=$uid;
                $_data1['name']=$value['name'];
                $_data1['icon_url']=$value['icon_url'];
                $_data1['jump_url']=$value['jump_url'];
                $_data1['add_time']=time();
                $_data1['sort'] = $value['sort'];
                $_data1['tab'] = $value['tab'];
                $_data1['type_id'] = $value['type_id'];
                $nav_array[] = $_data1;
            }
            if($nav_array){
                Db::table('s_users_nav')->addAll($nav_array);
            }
        }
        if($product == 2){//产品中心
            $pro_type = array();
            $p_type_res = Db::table('s_mode_type')->where(array('user_id'=>$uid,'type'=>3))->find();
            if($p_type_res){
                Db::table('s_mode_type')->where(array('user_id'=>$uid,'type'=>3))->delete();
            }
            $type_product = Db::table('s_mode_type')->where(array('user_id'=>$user_id,'type'=>3))->order('sort asc')->select();
            foreach ($type_product as $k=>$value){
                $_data2_type['user_id']=$uid;
                $_data2_type['title']=$value['title'];
                $_data2_type['type']=3;
                $_data2_type['sort']=$value['sort'];
                $_data2_type['mode_id']=$value['mode_id'];
                $_data2_type['addtime']=date('Y-m-d H:i:s');
                $pro_type[] = $_data2_type;
            }
            if($pro_type){
                Db::table('s_mode_type')->where(array('user_id'=>$uid,'type'=>3))->addAll($pro_type);
            }

            $p_old = Db::table('s_product')->field('id')->where(array('user_id'=>$uid))->select();
            if($p_old){
                $ids = '';
                foreach ($p_old as $v){
                    $ids .= $v['id'].',';
                }
                $ids = rtrim($ids, ',');
                Db::table('s_product')->where(array('user_id'=>$uid))->delete();
                $d_old = Db::table('s_product_detail')->where(array('product_id'=>array('in',$ids)))->find();
                if($d_old){
                    Db::table('s_product_detail')->where(array('product_id'=>array('in',$ids)))->delete();
                }
                $i_old = Db::table('s_product_img')->where(array('product_id'=>array('in',$ids)))->find();
                if($i_old){
                    Db::table('s_product_img')->where(array('product_id'=>array('in',$ids)))->delete();
                }
            }

            $products = Db::table('s_product')->where(array('user_id'=>$user_id))->select();
            $detail_array = array();
            $img_array = array();
            foreach ($products as $value){
                $_data2['user_id']=$uid;
                $_data2['title']=$value['title'];
                $_data2['cover_img']=$value['cover_img'];
                $_data2['type']=$value['type'];
                $_data2['type_id']=$value['type_id'];
                $_data2['address']=$value['address'];
                $_data2['price']=$value['price'];
                $_data2['is_home']=$value['is_home'];
                $_data2['new_type']=$value['new_type'];
                $_data2['describe']=$value['describe'];
                $_data2['info']=$value['info'];
                $_data2['sort']=$value['sort'];
                $_data2['addtime']=time();
                $pro_id = Db::table('s_product')->insert($_data2);
                if($pro_id){
                    $detail = Db::table('s_product_detail')->where(array('product_id'=>$value['id']))->select();
                    if($detail){
                        foreach ($detail as $d){
                            $_d['title'] = $d['title'];
                            $_d['content'] = $d['content'];
                            $_d['product_id'] = $pro_id;
                            $_d['addtime'] = time();
                            $detail_array[] = $_d;
                        }
                    }
                    $img = Db::table('s_product_img')->where(array('product_id'=>$value['id']))->select();
                    if($img){
                        foreach ($img as $i){
                            $_i['img'] = $i['img'];
                            $_i['type'] = $i['type'];
                            $_i['sort'] = $i['sort'];
                            $_i['product_id'] = $pro_id;
                            $_i['addtime'] = time();
                            $img_array[] = $_i;
                        }
                    }
                }
            }
            Db::table('s_product_detail')->addAll($detail_array);
            Db::table('s_product_img')->addAll($img_array);
        }
        if($video == 2){//视频中心
            $video_type = array();
            $v_type_res = Db::table('s_mode_type')->where(array('user_id'=>$uid,'type'=>1))->find();
            if($v_type_res){
                Db::table('s_mode_type')->where(array('user_id'=>$uid,'type'=>1))->delete();
            }
            $type_video = Db::table('s_mode_type')->where(array('user_id'=>$user_id,'type'=>1))->order('sort asc')->select();
            foreach ($type_video as $k=>$value){
                $_data4_type['user_id']=$uid;
                $_data4_type['title']=$value['title'];
                $_data4_type['type']=1;
                $_data4_type['sort']=$value['sort'];
                $_data4_type['mode_id']=$value['mode_id'];
                $_data4_type['addtime']=date('Y-m-d H:i:s');
                $video_type[] = $_data4_type;
            }
            if($video_type){
                Db::table('s_mode_type')->where(array('user_id'=>$uid,'type'=>1))->addAll($video_type);
            }
            $video_array = array();
            $res = Db::table('s_user_video')->where(array('user_id'=>$uid))->find();
            if($res){
                Db::table('s_user_video')->where(array('user_id'=>$uid))->delete();
            }
            $users_nav = Db::table('s_user_video')->where(array('user_id'=>$user_id))->order('sort asc')->select();
            foreach ($users_nav as $k=>$value){
                $_data4['user_id']=$uid;
                $_data4['title']=$value['title'];
                $_data4['video']=$value['video'];
                $_data4['video_cover']=$value['video_cover'];
                $_data4['addtime']=date('Y-m-d H:i:s');
                $_data4['sort'] = $value['sort'];
                $_data4['type_id'] = $value['type_id'];
                $video_array[] = $_data4;
            }
            if($video_array){
                Db::table('s_user_video')->addAll($video_array);
            }
        }
        if($show_list == 2){//展示一切
            $show_type = array();
            $s_type_res = Db::table('s_mode_type')->where(array('user_id'=>$uid,'type'=>2))->find();
            if($s_type_res){
                Db::table('s_mode_type')->where(array('user_id'=>$uid,'type'=>2))->delete();
            }
            $type_product = Db::table('s_mode_type')->where(array('user_id'=>$user_id,'type'=>2))->order('sort asc')->select();
            foreach ($type_product as $k=>$value){
                $_data3_type['user_id']=$uid;
                $_data3_type['title']=$value['title'];
                $_data3_type['type']=2;
                $_data3_type['sort']=$value['sort'];
                $_data3_type['mode_id']=$value['mode_id'];
                $_data3_type['addtime']=date('Y-m-d H:i:s');
                $show_type[] = $_data3_type;
            }
            if($show_type){
                Db::table('s_mode_type')->where(array('user_id'=>$uid,'type'=>2))->addAll($show_type);
            }
            $c_old = Db::table('s_card_content')->where(array('user_id'=>$uid))->select();
            if($c_old){
                $c_ids = '';
                foreach ($c_old as $v){
                    $c_ids .= $v['id'].',';
                }
                $c_ids = rtrim($c_ids, ',');
                Db::table('s_card_content')->where(array('user_id'=>$uid))->delete();
                $cr_old = Db::table('s_card_content_res')->where(array('card_content_id'=>array('in',$c_ids)))->find();
                if($cr_old){
                    Db::table('s_card_content_res')->where(array('card_content_id'=>array('in',$c_ids)))->delete();
                }
            }
            $c_sort = 0;
            $content = Db::table('s_card_content')->where(array('user_id'=>$user_id))->order('sort asc')->select();
            $res_array = array();
            foreach ($content as $c){
                $c_sort++;
                $con['title'] = $c['title'];
                $con['titles'] = $c['titles'];
                $con['thumb'] = $c['thumb'];
                $con['content'] = $c['content'];
                $con['type_id'] = $c['type_id'];
                $con['user_id'] = $this->userInfo['id'];
                $con['add_time'] = time();
                $con['sort'] = $c_sort?$c_sort:0;
                $new_id = Db::table('s_card_content')->insert($con);
                if($new_id){
                    $res = Db::table('s_card_content_res')->where(array('card_content_id'=>$c['id']))->select();
                    foreach ($res as $r){
                        $result['card_content_id'] = $new_id;
                        $result['type'] = $r['type'];
                        $result['data_url'] = $r['data_url'];
                        $result['thumb'] = $r['thumb'];
                        $result['sort'] = $r['sort'];
                        $result['user_id'] = $uid;
                        $result['add_time'] = time();
                        $res_array[] = $result;
                    }
                }
            }
            if($res_array){
                Db::table('s_card_content_res')->addAll($res_array);
            }
        }
        
        $this->array_return['ResultType'] = self::__OK__;
        $this->array_return['Message'] = "操作成功";
        return json($this->array_return);
    }
    
    /**
     * 4.0新版v网复制
     */
    public function copy_index()
    {
        $user_id = $this->_reqparam['user_id'];//被复制人id
        if($user_id){
            $data = array();
            $data['link'] = Db::table('s_mode_type')->field('title,mode_id')->where(array('user_id'=>$user_id,'type'=>0))->select();
            $data['link_count'] = Db::table('s_mode_type')->where(array('user_id'=>$this->userInfo['id'],'type'=>0))->count();
            $data['video'] = Db::table('s_mode_type')->field('title,mode_id')->where(array('user_id'=>$user_id,'type'=>1))->select();
            $data['video_count'] = Db::table('s_mode_type')->where(array('user_id'=>$this->userInfo['id'],'type'=>1))->count();
            $data['show'] = Db::table('s_mode_type')->field('title,mode_id')->where(array('user_id'=>$user_id,'type'=>2))->select();
            $data['show_count'] = Db::table('s_mode_type')->where(array('user_id'=>$this->userInfo['id'],'type'=>2))->count();;
            $data['product'] = Db::table('s_mode_type')->field('title,mode_id')->where(array('user_id'=>$user_id,'type'=>3))->select();
            $data['product_count'] = Db::table('s_mode_type')->where(array('user_id'=>$this->userInfo['id'],'type'=>3))->count();
            if($data){
                $this->array_return['ResultType'] = self::__OK__;
                $this->array_return['Message'] = "操作成功";
                $this->array_return['AppendData'] = $data;
            }
        }else{
            $this->array_return['ResultType'] = self::__ERROR__;
            $this->array_return['Message'] = "缺少参数";
        }
        
        return json($this->array_return);
    }
    
    /**
     * 4.0新版的v网复制
     * */
    public function news_copy_card()
    {
        $type = $this->_reqparam['type'];//0:链接一切分类   1:视频分类   2:展示一切分类  3：产品中心
        $mode_id = $this->_reqparam['mode_id'];//分类id（以逗号隔开的字符串）
        $uid = $this->userInfo['id'];//复制人的id
        $user_id = $this->_reqparam['user_id'];//被复制人的id
        if(!in_array($type,array(0,1,2,3))){
            $this->array_return['ResultType'] = self::__ERROR1__;
            $this->array_return['Message'] = "参数有误";
            return json($this->array_return);
        }
        $copy_user = Db::table('s_user_detail')->where(array('id'=>$user_id))->column('id');
        if(!$copy_user || !$user_id || !$uid){
            $this->array_return['ResultType'] = self::__ERROR1__;
            $this->array_return['Message'] = "参数有误";
            return json($this->array_return);
        }
        $type_id_arr = explode(',',$mode_id);
        $old_link_mode_count = Db::table('s_mode_type')->where(array('user_id'=>$uid,'type'=>$type))->count();
        if((count($type_id_arr)+$old_link_mode_count) >4){
            $this->array_return['ResultType'] = self::__ERROR1__;
            $this->array_return['Message'] = "您最多只能复制".(4-$old_link_mode_count).'个栏目';
            return json($this->array_return);
        }
        $max_mode  = Db::table('s_mode_type')->where(array('user_id'=>$uid,'type'=>$type))->max('mode_id');
        foreach ($type_id_arr as $type_id){
            $old_link_mode = Db::table('s_mode_type')->where(array('user_id'=>$user_id,'type'=>$type,'mode_id'=>$type_id))->find();
            if($old_link_mode){
                $old_link_mode_count++;
                $max_mode++;
                $add_new_mode = array(
                    'user_id'=>$uid,
                    'title'=>$old_link_mode['title'],
                    'sort'=>$old_link_mode_count,
                    'mode_id'=>$max_mode,
                    'type'=>$type,
                    'addtime'=>date('Y-m-d H:i:s')
                );
                Db::table('s_mode_type')->insert($add_new_mode);
            }
            if($type == 0){//链接一切
                $nav_array = [];
                $users_nav = Db::table('s_users_nav')->where(array('user_id'=>$user_id,'type_id'=>$type_id))->select();
                foreach ($users_nav as $k=>$value){
                    $_data1['user_id']=$uid;
                    $_data1['name']=$value['name'];
                    $_data1['icon_url']=$value['icon_url'];
                    $_data1['jump_url']=$value['jump_url'];
                    $_data1['add_time']=time();
                    $_data1['sort'] = $value['sort'];
                    $_data1['tab'] = $value['tab'];
                    $_data1['type_id'] = $max_mode;
                    $nav_array[] = $_data1;
                }
                if($nav_array){
                    Db::table('s_users_nav')->addAll($nav_array);
                }
            }elseif($type == 3){//产品中心
                $products = Db::table('s_product')->where(array('user_id'=>$user_id,'new_type'=>$type_id))->select();
                $detail_array = array();
                $img_array = array();
                foreach ($products as $value){
                    $_data2['user_id']=$uid;
                    $_data2['title']=$value['title'];
                    $_data2['cover_img']=$value['cover_img'];
                    $_data2['type']=$value['type'];
                    $_data2['type_id']=$value['type_id'];
                    $_data2['address']=$value['address'];
                    $_data2['price']=$value['price'];
                    $_data2['is_home']=$value['is_home'];
                    $_data2['new_type']=$max_mode;
                    $_data2['describe']=$value['describe'];
                    $_data2['info']=$value['info'];
                    $_data2['sort']=$value['sort'];
                    $_data2['addtime']=time();
                    $pro_id = Db::table('s_product')->insert($_data2);
                    if($pro_id){
                        $detail = Db::table('s_product_detail')->where(array('product_id'=>$value['id']))->select();
                        if($detail){
                            foreach ($detail as $d){
                                $_d['title'] = $d['title'];
                                $_d['content'] = $d['content'];
                                $_d['product_id'] = $pro_id;
                                $_d['addtime'] = time();
                                $detail_array[] = $_d;
                            }
                        }
                        $img = Db::table('s_product_img')->where(array('product_id'=>$value['id']))->select();
                        if($img){
                            foreach ($img as $i){
                                $_i['img'] = $i['img'];
                                $_i['type'] = $i['type'];
                                $_i['sort'] = $i['sort'];
                                $_i['product_id'] = $pro_id;
                                $_i['addtime'] = time();
                                $img_array[] = $_i;
                            }
                        }
                    }
                }
                Db::table('s_product_detail')->addAll($detail_array);
                Db::table('s_product_img')->addAll($img_array);
            }elseif($type == 1){//视频中心
                $video_array = array();
                $users_nav = Db::table('s_user_video')->where(array('user_id'=>$user_id,'type_id'=>$type_id))->select();
                foreach ($users_nav as $k=>$value){
                    $_data4['user_id']=$uid;
                    $_data4['title']=$value['title'];
                    $_data4['video']=$value['video'];
                    $_data4['video_cover']=$value['video_cover'];
                    $_data4['addtime']=date('Y-m-d H:i:s');
                    $_data4['sort'] = $value['sort'];
                    $_data4['type_id'] = $max_mode;
                    $video_array[] = $_data4;
                }
                if($video_array){
                    Db::table('s_user_video')->addAll($video_array);
                }
            }elseif($type == 2){//展示一切
                $c_sort = 0;
                $content = Db::table('s_card_content')->where(array('user_id'=>$user_id,'type_id'=>$type_id))->select();
                $res_array = array();
                foreach ($content as $c){
                    $c_sort++;
                    $con['title'] = $c['title'];
                    $con['titles'] = $c['titles'];
                    $con['thumb'] = $c['thumb'];
                    $con['content'] = $c['content'];
                    $con['type_id'] = $max_mode;
                    $con['user_id'] = $uid;
                    $con['add_time'] = time();
                    $con['sort'] = $c_sort?$c_sort:0;
                    $new_id = Db::table('s_card_content')->insert($con);
                    if($new_id){
                        $res = Db::table('s_card_content_res')->where(array('card_content_id'=>$c['id']))->select();
                        foreach ($res as $r){
                            $result['card_content_id'] = $new_id;
                            $result['type'] = $r['type'];
                            $result['data_url'] = $r['data_url'];
                            $result['thumb'] = $r['thumb'];
                            $result['sort'] = $r['sort'];
                            $result['user_id'] = $uid;
                            $result['add_time'] = time();
                            $res_array[] = $result;
                        }
                    }
                }
                if($res_array){
                    Db::table('s_card_content_res')->addAll($res_array);
                }
            }
        }
        
        $this->array_return['ResultType'] = self::__OK__;
        $this->array_return['Message'] = "操作成功";
        return json($this->array_return);
    }

    /**
     * v网夹列表
     */
    public function card_list()
    {
        $where['user_id']= $this->userInfo['id'];
        if($this->_reqparam['group_id']){
            $where['group_id'] = $this->_reqparam['group_id'];//分组id
        }
        if($this->_reqparam['type']){
            $where['type'] = $this->_reqparam['type'];//来源  1:线上收藏  2:拍照收纳
        }
        if($this->_reqparam['keyword']){
            $where['keyword'] = $this->_reqparam['keyword'];//关键字
        }
        $lists = Db::table('s_cards')->alias('c')->leftJoin(' s_user_detail u','u.id = c.user_id')
            ->leftJoin(' s_user_detail ui','ui.id = c.object_id')
            ->where($where)->select();
            
        if ($lists) {
            $this->array_return['ResultType'] = self::__OK__;
            $this->array_return['Message'] = "操作成功";
            $this->array_return['AppendData'] = $lists;
        } else {
            $this->array_return['ResultType'] = self::__ERROR2__;
            $this->array_return['Message'] = "操作失败";
        }
        
        return json($this->array_return);
    }

}