<?php

namespace app\admin\model;


class AdminUser extends Common
{

    protected $pk = 'user_id';

    /**
     * 编辑信息
     * @param $param
     */
    public function editMember($param)
    {
        try {
//            allowField 过滤post数组中的非数据表字段数据
//            $result = $this->validate('MemberValidate')->allowField(true)->save($param, ['manid' => $param['id']]);
            $result = $this->allowField(true)->save($param, ['user_id' => $param['id']]);


            if (false === $result) {
                return ['code' => 0, 'data' => '', 'msg' => $this->getError()];
            } else {
                return ['code' => 1, 'data' => '', 'msg' => '编辑成功'];
            }
        } catch (\Exception $e) {
            return ['code' => -1, 'data' => '', 'msg' => $e->getMessage()];
        }
    }



}
