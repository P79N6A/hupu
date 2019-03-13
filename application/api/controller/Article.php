<?php
namespace app\api\controller;
use app\common\controller\ApiBase;
use think\Db;
use app\api\logic\ArticleList  as artLogic;

class Article extends  ApiBase 
{
	private $art_logic = null;
	 
 	function initialize()
 	{
 		parent::initialize();
        $this->art_logic = new artLogic();
    }
    
    /**
     * 获取文章列表
     */
    public function get_article_list()
    {
    	$title = input('param.title','');
    	$type_id = input('param.type_id','');  
        //当前登录用户的文章列表
        $map['user_id'] = $this->userInfo['id'];
        if ($title ){
            $map['title'] = $title;
        }
        if ( $type_id ) {
            $map['atype_id'] = $type_id;
        }

        $page = input('param.page',1);  
        $map['page'] = $page;
        $map['pageNum'] = 8;
         
        list($list, $page, $count) = $this->art_logic->getArticle($map);
        
        if (!$list){
            return json(['ResultType'=>self::__ERROR2__,'Message'=>"操作失败"]);
        }

        foreach ($list as $key => $value) {
            $content = cutstr_html($value['content']);
            $list[$key]['content']=mb_substr($content,0,50,'utf-8');
            if($value['add_time'] >strtotime(date('Ymd'))) {
                $list[$key]['add_time'] = date('Y-m-d H:i:s', $value['add_time']);
            }else{
                $list[$key]['add_time']=date('Y-m-d',$value['add_time']);
            }
            if($value['thumb']=='/'){
                $list[$key]['thumb']='';
            }
        }

        $return_data['current_page'] = $page;
        $return_data['list'] = $list;
        $this->array_return['ResultType'] = self::__OK__;
        $this->array_return['Message'] = "操作成功";
        $this->array_return['AppendData'] = $return_data;

        return json($this->array_return);
    }

    /**
     * 添加（修改）文章
     */
    public function addArticle()
    {
        $this->title="添加文章";
        $this->ids = $this->userInfo['id'];
        $this->unionid = $this->userInfo['unionid'];
        if ( $this->request->isPost() ) 
        {
            $rules = [
                ['name', 'require', '文章标题不能为空'],
//             	['content', 'require', '文章内容不能为空'],
                ['thumb', 'require', '请上传封面图片'],
            ];
            
            $art_logic = Db::table('s_article_list');
            if (!$data = $art_logic->validate($rules)->create()) {
            	return json(['status' => 0, 'msg' => $art_logic->getError()]);
            }

            if ( isset($data['id']) && $data['id'] > 0 ) {
                //编辑文章
                $data['content']=htmlspecialchars_decode($data['content']);
                if ($art_logic->update($data)) {
                    return json(['status' => 1, 'msg' => '编辑文章成功']);
                } else {
                    return json(['status' => 0, 'msg' => $art_logic->getError()]);
                }
            } else {
                //添加文章
                if ( isset($data['id']) ) unset($data['id']);
                $data['user_id'] = $this->userInfo['id'];
                $data['add_time'] = time();
                $data['content']=htmlspecialchars_decode($data['content']);
            	if (input('article_reward')) {
	                $userInfo['article_reward'] = input('article_reward');
	                Db::table('s_user_detail')->where('id='.$this->userInfo['id'])->update($userInfo);
	            }
	            
	            $art_id = $art_logic->insert($data);
                if ($art_id) {
                    return json(['status' => 1, 'msg' => '添加文章成功','art_id'=>$art_id]);
                } else {
                    return json(['status' => 0, 'msg' => $art_logic->getError()]);
                }
            }
        }  
    }

    /**
     * 获取文章详情
     */
    public function get_article_info()
    {        
    	$user_id = $this->userInfo['id'];
        $id = input('get.art_id', 0, 'intval');
        $title = '添加文章';
        if ($id > 0) {
        	$title = '编辑文章';
       		$res = $this->art_logic->getArticleInfo(['user_id'=>$user_id, 'id'=>$id]);
            $res['content'] = htmlspecialchars_decode($res['content']);
            $return_data['res'] = $res;
        }
            
        $return_data['title'] = $title;
        //获取文章分类    
        $article_type = $this->art_logic->getType(['user_id'=>$user_id]);
        //底部广告是否开启
        $condition['article_advert'] = $this->userInfo['article_advert'] ? 1 : 0;
        //微缩V网是否开启
        $condition['article_cards'] = $this->userInfo['article_cards'] ? 1 : 0;
        //是否开户打赏功能
        $condition['article_reward'] = $this->userInfo['article_reward'] ? 1 : 0;
         
        $return_data['condition'] = $condition;
        $return_data['article_type'] = $article_type;
        $this->array_return['ResultType'] = self::__OK__;
        $this->array_return['Message'] = "操作成功";
        $this->array_return['AppendData'] = $return_data;
        return json($this->array_return);   
    }

}