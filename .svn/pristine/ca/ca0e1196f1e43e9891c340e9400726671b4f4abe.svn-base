<?php

namespace app\admin\model;


class AdminLog extends Common
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

    /**
     * 管理员操作日志
     * admin_log
     * type: 0:提现操作 1:用户列表操作
     * object_id   对象ID
     * content   操作内容
     */

    public function adminLog($type, $object_id = null, $content = null)
    {
        $data = array(

//            'admin_id' => $_SESSION['admin']['user_id'],
            'admin_id' => 3333,

            'object_id' => $object_id,

            'content' => $content,

            'addtime' => time(),

            'type' => $type

        );

        $res = $this->save($data);

        if (false === $res) {
            return ['code' => 0, 'data' => $res, 'msg' => $this->getError()];
        } else {
            return ['code' => 1, 'data' => $res, 'msg' => '成功'];
        }

    }

}
