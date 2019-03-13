<?php
//phpinfo();

$path = __DIR__;

if(strpos($path,'ublic')){
    $base_path = '/';
}else{
    $base_path = '/public';
}
var_dump($_SERVER['DOCUMENT_ROOT'].'2222');
var_dump(realpath(__DIR__));
//var_dump(define('APP_ROOT', realpath(__DIR__ . '/../../../') . '/'););