<?phpnamespace app\api\controller;use think\Controller;use think\Db;class DownloadApi extends Controller {    const __OK__ = '0'; //请求成功    const __ERROR__ = '1'; //参数错误    const __ERROR1__ = '2'; //没有绑定    const __ERROR2__ = '3';//数据库错误    const __ERROR4__ = '4';//未查询到数据    const __ERROR5__ = '5';//条件不符合    public $array_return = array("ResultType"=>1,"Message"=>"success","AppendData"=>[]);        public function initialize()    {         $this->request = Request::param();    }        /**     * 下载中心分类     */    public function download_type()    {        $res = Db::table('s_download_type')->select();        if ($res) {            $this->array_return['ResultType'] = self::__OK__;            $this->array_return['Message'] = "操作成功";            $this->array_return['AppendData'] = $res;        } else {            $this->array_return['ResultType'] = self::__ERROR2__;            $this->array_return['Message'] = "操作失败";        }                return json($this->array_return);    }        /**     * 链接一切分类列表     */    public function download_center()    {        $type = $this->_reqparam['type'];        $is_img = $this->_reqparam['is_img'];//1图片   2视频        $start = $this->_reqparam['start']?$this->_reqparam['start']:0;        $length = $this->_reqparam['length']?$this->_reqparam['length']:99;        if($type && $is_img){            $where['type_id'] = $type;            $where['is_img'] = $is_img;            $res = Db::table('s_download_center')->field('id,title,img')->where($where)->limit($start,$length)->select();            if ($res) {                $this->array_return['ResultType'] = self::__OK__;                $this->array_return['Message'] = "操作成功";                $this->array_return['AppendData'] = $res;            } else {                $this->array_return['ResultType'] = self::__ERROR2__;                $this->array_return['Message'] = "操作失败";            }        }else{            $this->array_return['ResultType'] = self::__ERROR__;            $this->array_return['Message'] = "缺少参数";        }                return json($this->array_return);    }}