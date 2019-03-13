<?php
namespace app\api\controller;
use app\common\controller\ApiBase;
use app\api\logic\CapitalLog  as capitalLogic;

class Capital extends  ApiBase 
{
    /**
     *  搜索V网
     */
    public function consume()
    {
        if(!$this->request->isPost())
        {
       		return json(['ResultType'=>self::__ERROR__,'Message'=>"请求类型错误"]);
        }
            
        $res = $this->request->post();    
        $user_id = $this->userInfo['id'];   
        $map['page'] = $res['page'] ? $res['page'] : 1;  
        $map['pageNum'] = 15; 
        $map['user_id'] = $user_id;

        $capital_logic = new capitalLogic();
        $consume_list = $capital_logic->getConsume($map);
        foreach ($consume_list as $key=>$value){ 
        	$consume_list[$key]['create_time'] = date('Y-m-d H:i:s',$value['create_time']);
        	if ($value['type'] == 1){  
        		$consume_list[$key]['type'] = '共享广告';
        	}
        }
  
        $this->array_return['ResultType'] = self::__ERROR4__; 
        $this->array_return['Message'] = "未查询到数据";  
        if ($consume_list){   
        	$this->array_return['ResultType'] = self::__OK__; 
        	$this->array_return['Message'] = "操作成功";  
        	$this->array_return['AppendData'] = $consume_list;
        }
            
        return json($this->array_return);    
    }

}