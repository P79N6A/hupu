<?phpnamespace app\home\controller;use app\common\controller\HomeBase;use think\Db;class Index extends HomeBase{    public function index()    {    	header("location:".url('Home/User/usercenter'));    }    public function test()    {    	$data = Db::table("s_user_info")->select();    	foreach ($data as $key => $value) {    		if(empty($value['wx_ewm_url'])) { continue; }    		if(substr($value['wx_ewm_url'], 0,1)!='/'){    			Db::table('s_user_info')->where(['id'=>$value['id']])->update(['wx_ewm_url'=>'/'.$value['wx_ewm_url']]);    		}    	}    }}