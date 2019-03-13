<?php
use think\Db;

function toJson($code=0,$msg='',$count=0,$data){

    $json = ['code' => $code, 'msg' => $msg, 'count' => $count, 'data' => $data];

    return json($json);
}



/**
 * 删除选中的记录
 * @param $table [表名,不需要前缀]
 * @param $ids [id]
 * @param $manids [manid]字段主键
 * @return \think\response\Json
 */
function deletes($table, $ids,$manid='')
{
    try {
        $success = 0;//成功的记录数
        $error = 0;//失败的记录数
        if (strlen($ids) > 0) {
            $arrIds = explode(',', $ids);//将字符串转为数组
            $count = count($arrIds);//数组长度
            if ($count > 0) {
                for ($i = 0; $i < $count; $i++) {
                    if($manid!=''){
                        $flag = Db::name($table)->where($manid, $arrIds[$i])->delete();

                    }else{
                        $flag = Db::name($table)->where('id', $arrIds[$i])->delete();

                    }

                    if ($flag > 0) {
                        $success++;
                    } else {
                        $error++;
                        continue;
                    }
                }
                if ($success == $count) {
                    return json(['code' => 1, 'data' => '', 'msg' => $success . '条记录删除成功']);
                } else {
                    return json(['code' => 0, 'data' => [$count,$ids], 'msg' => $error . '条记录删除失败']);
                }
            }
        }
    } catch (\Exception $e) {
        return json(['code' => $e->getCode(), 'data' => '', 'msg' => $e->getMessage()]);
    }

    return true;
}



/**
 * 下划线转驼峰
 * 思路:
 * step1.原字符串转小写,原字符串中的分隔符用空格替换,在字符串开头加上分隔符
 * step2.将字符串中每个单词的首字母转换为大写,再去空格,去字符串首部附加的分隔符.
 */
function camelize($uncamelized_words,$separator='_')
{
    $uncamelized_words = $separator. str_replace($separator, " ", strtolower($uncamelized_words));
    return ltrim(str_replace(" ", "", ucwords($uncamelized_words)), $separator );
}

/**
 * 驼峰命名转下划线命名
 * 思路:
 * 小写和大写紧挨一起的地方,加上分隔符,然后全部转小写
 */
function uncamelize($camelCaps,$separator='_')
{
    return strtolower(preg_replace('/([a-z])([A-Z])/', "$1" . $separator . "$2", $camelCaps));
}


function getClass($class,$type='0'){
    $controller = $class;
    $array = explode('\\',$controller);
    $array = array_pop($array);
    if(1 == $type){
//        驼峰法
        $controller = $array;
    }else{
        $controller = uncamelize($array);
    }
    return $controller;
}

function isBlank($object)
{
    if (is_null($object) || '' === $object || (is_array($object) && count($object) < 1)) {
        return true;
    }
    return empty($object);
}

function isPresent($object)
{
    return !isBlank($object);
}


/**
 * 整理菜单树方法
 * @param $param
 * @return array
 */
function prepareMenu($param)
{
    $parent = []; //父类
    $child = [];  //子类

    foreach($param as $key=>$vo){

        if($vo['pid'] == 0){
            $vo['href'] = '#';
            $parent[] = $vo;
        }else{
            $vo['href'] = url($vo['name'],'',false); //跳转地址
            $child[] = $vo;
        }
    }

    foreach($parent as $key=>$vo){
        foreach($child as $k=>$v){

            if($v['pid'] == $vo['id']){
                $parent[$key]['child'][] = $v;
            }
        }
    }
    unset($child);
    return $parent;
}