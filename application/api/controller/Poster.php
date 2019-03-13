<?php
namespace app\api\controller;
use app\common\controller\ApiBase;
use think\Db;
require_once EXTEND_PATH.'/Alioss/autoload.php';

class Poster extends ApiBase 
{
	/**
     * 海报合成
     */
	public function create_poster() 
	{
		if ($_POST) 
		{
			$user_id = $this->userInfo['id'];
			$title = input('post.title','');
			$content = input('post.content','');
			$type_id = input('post.type_id','');
			$end_name = trim(strrchr($_FILES['img']['name'], '.') , '.');
			if ($end_name == 'jpeg') {
				$save_name = "Poster" . '/' . date('Y-m-d') . '/' . time() . rand(1, 9999) . '.jpg';
			} else {
				$save_name = "Poster" . '/' . date('Y-m-d') . '/' . time() . rand(1, 9999) . '.' . $end_name;
			}
			try {
				$conf = config('OSS_IMAGE');
				$ossClient = new OSSOssClient($conf['accessKeyId'], $conf['accessKeySecret'], $conf['endpoint']);
				$image_file = $ossClient->uploadFile($conf['bucket'], $save_name, $_FILES['img']['tmp_name']);
				if ($image_file['status'] == true) {
					$shift_url = str_replace($conf['oss_url'], $conf['cdn_usl'], $image_file['url']);
					$result_url = $shift_url . "@!protected";
					$add['img'] = $result_url;
				} else {
					// 上传错误提示错误信息
					$this->array_return['ResultType'] = self::__ERROR2__;
					$this->array_return['Message'] = "上传操作失败";
				}
			}
			catch(OssException $e) {
				$this->array_return['ResultType'] = self::__ERROR2__;
				$this->array_return['Message'] = "上传操作失败";
			}
			$add['user_id'] = $user_id;
			$add['title'] = $title;
			$add['content'] = $content;
			$add['addtime'] = time();
			$add['type_id'] = $type_id;
			$res = Db::table('s_user_poster')->insert($add);
			if ($res) {
				$this->array_return['ResultType'] = self::__OK__;
				$this->array_return['Message'] = "操作成功";
				$this->array_return['AppendData'] = $add['img'];
			} else {
				$this->array_return['ResultType'] = self::__ERROR2__;
				$this->array_return['Message'] = "上传操作失败";
			}
			return json($this->array_return);
		} else {
			return json(['ResultType' => self::__ERROR5__, 'Message' => "请求类型错误"]);
		}
	}
	
	/**
     *壁纸生成
     */
	public function create_wallpaper()
	{
		$user_id = $this->userInfo['id'];
		$w_id = input('post.wid');
		if (!$w_id) {
			return json(['ResultType' => self::__ERROR__, 'Message' => "未传递壁纸ID"]);
		}
		if ($user_id && $w_id) {
			$old = Db::table('s_user_wallpaper')->where(array( 'user_id' => $user_id,'wid' => $w_id))->find();
			if ($old) {
				$this->array_return['ResultType'] = self::__OK__;
				$this->array_return['Message'] = "操作成功";
				$this->array_return['AppendData'] = $old;
			} else {
				$add = array( 'user_id' => $user_id,'wid' => $w_id, 'addtime' => time());
				$end_name = trim(strrchr($_FILES['img']['name'], '.') , '.');
				if ($end_name == 'jpeg') {
					$save_name = "Wallpaper" . '/' . date('Y-m-d') . '/' . time() . rand(1, 9999) . '.jpg';
				} else {
					$save_name = "Wallpaper" . '/' . date('Y-m-d') . '/' . time() . rand(1, 9999) . '.' . $end_name;
				}
				try {
					$conf = config('OSS_IMAGE');
					$ossClient = new OSSOssClient($conf['accessKeyId'], $conf['accessKeySecret'], $conf['endpoint']);
					$image_file = $ossClient->uploadFile($conf['bucket'], $save_name, $_FILES['img']['tmp_name']);
					if ($image_file['status'] == true) {
						$shift_url = str_replace($conf['oss_url'], $conf['cdn_usl'], $image_file['url']);
						$result_url = $shift_url . "@!protected";
						$add['img'] = $result_url;
					} else {
						$this->array_return['ResultType'] = self::__ERROR2__;
						$this->array_return['Message'] = "操作失败";
					}
				}
				catch(OssException $e) {
					$this->array_return['ResultType'] = self::__ERROR2__;
					$this->array_return['Message'] = "操作失败";
				}
				$res = Db::table('s_user_wallpaper')->insert($add);
				if ($res) {
					$this->array_return['ResultType'] = self::__OK__;
					$this->array_return['Message'] = "操作成功";
					$this->array_return['AppendData'] = $add['img'];
				} else {
					$this->array_return['ResultType'] = self::__ERROR2__;
					$this->array_return['Message'] = "操作失败";
				}
			}
		} else {
			$this->array_return['ResultType'] = self::__ERROR__;
			$this->array_return['Message'] = "缺少参数";
		}
		
		return json($this->array_return);
	}
}
