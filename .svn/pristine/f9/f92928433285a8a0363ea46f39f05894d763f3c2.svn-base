<?php

namespace app\admin\model;


class SystemArticle extends Common
{
    protected $pk = "id";

    /**
     * 编辑信息
     * @param $param
     */
    public function editMember($param)
    {
        try {
//            allowField 过滤post数组中的非数据表字段数据
//            $result = $this->validate('MemberValidate')->allowField(true)->save($param, ['manid' => $param['id']]);
            $pk = $this->pk;
            $result = $this->allowField(true)->save($param, [$pk => $param['id']]);


            if (false === $result) {
                return ['code' => 0, 'data' => $result, 'msg' => $this->getError()];
            } else {
                return ['code' => 1, 'data' => $result, 'msg' => '编辑成功'];
            }
        } catch (\Exception $e) {
            return ['code' => -1, 'data' => '', 'msg' => $e->getMessage()];
        }
    }

}
