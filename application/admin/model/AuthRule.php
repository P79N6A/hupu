<?php

namespace app\admin\model;
use think\exception\PDOException;
use think\Model;
use think\Db;

class AuthRule extends Model
{

//    protected $name = "auth_rule";
    protected $pk = "id";

    // 开启自动写入时间戳字段
    protected $autoWriteTimestamp = true;

    /**
     * [getAllMenu 获取全部菜单]
     * @author [田建龙] [864491238@qq.com]
     */
    public function getAllMenu()
    {
        return $this->order('id asc')->select();
    }

    /**
     * [getNodeInfo 获取节点数据]
     * @author [田建龙] [864491238@qq.com]
     */
    public function getNodeInfo($id)
    {
        $con[] = ['name','<>',''];
        $con[] = ['pid','<>',-1];
        $result = $this->field('id,title,pid')->where($con)->select();
        $str = "";
        $role = new AuthGroup();
        $rule = $role->getRuleById($id);

        if(!empty($rule)){
            $rule = explode(',', $rule);
        }
        foreach($result as $key=>$vo){
            $str .= '{ "id": "' . $vo['id'] . '", "pId":"' . $vo['pid'] . '", "name":"' . $vo['title'].'"';

            if(!empty($rule) && in_array($vo['id'], $rule)){
                $str .= ' ,"checked":1';
            }

            $str .= '},';
        }

        return "[" . substr($str, 0, -1) . "]";
    }


    /**
     * [getMenu 根据节点数据获取对应的菜单]
     * @author [田建龙] [864491238@qq.com]
     */
    public function getMenu($nodeStr = '')
    {
        //超级管理员没有节点数组
        $where = empty($nodeStr) ? 'status = 1' : 'status = 1 and id in('.$nodeStr.')';
        $result = Db::name('auth_rule')->where($where)->order('sort asc')->select();
//        dump($result);
//        dump(444333);
        $menu = prepareMenu($result);
        return $menu;
    }



    /**
     * [insertMenu 添加菜单]
     * @author [田建龙] [864491238@qq.com]
     */
    public function insertMenu($param)
    {
        try{
            $result = $this->save($param);
            if(false === $result){
//                writelog(session('uid'),session('username'),'用户【'.session('username').'】添加菜单失败',2);
                return ['code' => -1, 'data' => '', 'msg' => $this->getError()];
            }else{
//                writelog(session('uid'),session('username'),'用户【'.session('username').'】添加菜单成功',1);
                return ['code' => 1, 'data' => '', 'msg' => '添加菜单成功'];
            }
        }catch( PDOException $e){
            return ['code' => -2, 'data' => '', 'msg' => $e->getMessage()];
        }
    }



    /**
     * [editMenu 编辑菜单]
     * @author [田建龙] [864491238@qq.com]
     */
    public function editMenu($param)
    {
        try{
            $result =  $this->save($param, ['id' => $param['id']]);
            if(false === $result){
//                writelog(session('uid'),session('username'),'用户【'.session('username').'】编辑菜单失败',2);
                return ['code' => 0, 'data' => '', 'msg' => $this->getError()];
            }else{
//                writelog(session('uid'),session('username'),'用户【'.session('username').'】编辑菜单成功',1);
                return ['code' => 1, 'data' => '', 'msg' => '编辑菜单成功'];
            }
        }catch( PDOException $e){
            return ['code' => 0, 'data' => '', 'msg' => $e->getMessage()];
        }
    }



    /**
     * [getOneMenu 根据菜单id获取一条信息]
     * @author [田建龙] [864491238@qq.com]
     */
    public function getOneMenu($id)
    {
        return $this->where('id', $id)->find();
    }



    /**
     * [delMenu 删除菜单]
     * @author [田建龙] [864491238@qq.com]
     */
    public function delMenu($id)
    {
        try{
            $this->where('id', $id)->delete();
//            writelog(session('uid'),session('username'),'用户【'.session('username').'】删除菜单成功',1);
            return ['code' => 1, 'data' => '', 'msg' => '删除菜单成功'];
        }catch( PDOException $e){
            return ['code' => 0, 'data' => '', 'msg' => $e->getMessage()];
        }
    }
}