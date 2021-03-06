<?php

namespace app\admin\controller;

use think\App;
use think\Exception;
use think\facade\Config;

class Upload extends Base
{

    protected $config_image;
    protected $config_video;

    public function __construct(App $app = null)
    {
        parent::__construct($app);

        $this->config_image = Config::get('OSS_IMAGE');
        $this->config_video = Config::get('OSS_VIDEO');
    }

    /**
     * @param $imgs
     * @param $start_name
     * @return string
     * @throws \OSS\Core\OssException
     * 上传阿里云图片
     * 上传 type= 0 file上传  1 base64上传
     */
    public function Uploads($save_name, $imgs, $type = 0)
    {
        $img = '';
        $config = $this->config_image;
        if ($type == 1) {
            //上传base64图
            $result = $this->base64_to_blob($imgs);
            $content = $result['blob'];
            //匹配成功
            try {
                $ossClient = new \OSS\OssClient($config['accessKeyId'], $config['accessKeySecret'], $config['endpoint']);
                $image_file = $ossClient->putObject($config['bucket'], $save_name, $content);
                if (isPresent($image_file['info']['url'])) {
                    $shift_url = str_replace($config['oss_url'], $config['cdn_usls'], $image_file['url']);
                    $img = $shift_url . "@!protected";
                } else {

                    // 上传错误提示错误信息

                }
            } catch (Exception $e) {

            }
        } else {
            try {

                $ossClient = new \OSS\OssClient($config['accessKeyId'], $config['accessKeySecret'], $config['endpoint']);

                $image_file = $ossClient->uploadFile($config['bucket'], $save_name, $imgs['tmp_name']);

                if ($image_file['info']['url'] != '') {
                    $shift_url = str_replace($config['oss_url'], $config['cdn_usls'], $image_file['info']['url']);
                    $img = $shift_url . "@!protected";


                } else {

                    // 上传错误提示错误信息

                }

            } catch (Exception $e) {

            }
        }
        return $img;

    }

    /**
     * 上传视频到阿里云
     * 不需要压缩的图片也上传这个方法
     */
    function upload_video_to_aliyun($save_name, $videos)
    {
        $video = '';
        $config = $this->config_video;

        try {
            $ossClient = new \OSS\OssClient($config['accessKeyId'], $config['accessKeySecret'], $config['endpoint']);
            $image_file = $ossClient->uploadFile($config['bucket'], $save_name, $videos['tmp_name']);
            if ($image_file['status'] == true) {
                $shift_url = str_replace($config['oss_url'], $config['cdn_usl'], $image_file['url']);
                $video = $shift_url;

            } else {
                // 上传错误提示错误信息

            }
        } catch (OssException $e) {

        }
        return $video;
    }


    /**
     *上传图片
     * @param $position
     * @return \think\response\Json
     */
    public function uploadImage()
    {

        //图片上传
        $img = "";
        if ($_FILES['file'] != null) {

            if (substr($_SERVER['HTTP_HOST'], 0, 1) == 'w') {
                $start_name = 'article';
            } else {
                $start_name = 'test';
            }
            $end_name = trim(strrchr($_FILES['file']['name'], '.'), '.');
            if ($end_name == 'jpeg') {
                $save_name = $start_name . '/' . date('Ymd') . '/' . time() . rand(1, 9999) . '.jpg';
            } else {
                $save_name = $start_name . '/' . date('Ymd') . '/' . time() . rand(1, 9999) . '.' . $end_name;
            }
            if(input('upload_type')){
                $type = input('upload_type');
            }else{
                $type = 0;

            }
            $img = $this->Uploads($save_name, $_FILES['file'],$type);

            //为音频做截取
            if (strpos($_FILES['file']['type'], 'mp')!==false) {
                $img = substr($img,0,-11);
            }
        }
        return json_encode($img);
//        dump($img);
//        return json_encode("https://oss.mingxin001.com/test/20190117/15477167573721.png@!protected");
    }

    //会员头像上传
    public function uploadface()
    {
        $file = request()->file('file');
        $info = $file->move(ROOT_PATH . 'public' . DS . 'uploads/face');
        if ($info) {
            echo $info->getSaveName();
        } else {
            echo $file->getError();
        }
    }

    public function base64_to_blob($base64Str)
    {
        if ($index = strpos($base64Str, 'base64,', 0)) {
            $blobStr = substr($base64Str, $index + 7);
            $typestr = substr($base64Str, 0, $index);
            preg_match("/^data:(.*);$/", $typestr, $arr);
            return ['blob' => base64_decode($blobStr), 'type' => $arr[1]];
        }
        return false;
    }


}