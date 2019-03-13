<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006~2018 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------

// +----------------------------------------------------------------------
// | 应用设置
// +----------------------------------------------------------------------

return [
    // 应用名称
    'app_name'               => '',
    // 应用地址
    'app_host'               => '',
    // 应用调试模式
    'app_debug'              => true,
    // 应用Trace
    'app_trace'              => true,
    // 是否支持多模块
    'app_multi_module'       => true,
    // 入口自动绑定模块
    'auto_bind_module'       => false,
    // 注册的根命名空间
    'root_namespace'         => [],
    // 默认输出类型
    'default_return_type'    => 'html',
    // 默认AJAX 数据返回格式,可选json xml ...
    'default_ajax_return'    => 'json',
    // 默认JSONP格式返回的处理方法
    'default_jsonp_handler'  => 'jsonpReturn',
    // 默认JSONP处理方法
    'var_jsonp_handler'      => 'callback',
    // 默认时区
    'default_timezone'       => 'Asia/Shanghai',
    // 是否开启多语言
    'lang_switch_on'         => false,
    // 默认全局过滤方法 用逗号分隔多个
    'default_filter'         => '',
    // 默认语言
    'default_lang'           => 'zh-cn',
    // 应用类库后缀
    'class_suffix'           => false,
    // 控制器类后缀
    'controller_suffix'      => false,

    // +----------------------------------------------------------------------
    // | 模块设置
    // +----------------------------------------------------------------------

    // 默认模块名
    'default_module'         => 'index',
    // 禁止访问模块
    'deny_module_list'       => ['common'],
    // 默认控制器名
    'default_controller'     => 'Index',
    // 默认操作名
    'default_action'         => 'index',
    // 默认验证器
    'default_validate'       => '',
    // 默认的空模块名
    'empty_module'           => '',
    // 默认的空控制器名
    'empty_controller'       => 'Error',
    // 操作方法前缀
    'use_action_prefix'      => false,
    // 操作方法后缀
    'action_suffix'          => '',
    // 自动搜索控制器
    'controller_auto_search' => true,

    // +----------------------------------------------------------------------
    // | URL设置
    // +----------------------------------------------------------------------

    // PATHINFO变量名 用于兼容模式
    'var_pathinfo'           => 's',
    // 兼容PATH_INFO获取
    'pathinfo_fetch'         => ['ORIG_PATH_INFO', 'REDIRECT_PATH_INFO', 'REDIRECT_URL'],
    // pathinfo分隔符
    'pathinfo_depr'          => '/',
    // HTTPS代理标识
    'https_agent_name'       => '',
    // IP代理获取标识
    'http_agent_ip'          => 'X-REAL-IP',
    // URL伪静态后缀
    'url_html_suffix'        => 'html',
    // URL普通方式参数 用于自动生成
    'url_common_param'       => false,
    // URL参数方式 0 按名称成对解析 1 按顺序解析
    'url_param_type'         => 0,
    // 是否开启路由延迟解析
    'url_lazy_route'         => false,
    // 是否强制使用路由
    'url_route_must'         => false,
    // 合并路由规则
    'route_rule_merge'       => false,
    // 路由是否完全匹配
    'route_complete_match'   => false,
    // 使用注解路由
    'route_annotation'       => false,
    // 域名根，如thinkphp.cn
    'url_domain_root'        => '',
    // 是否自动转换URL中的控制器和操作名
    'url_convert'            => false,
    // 默认的访问控制器层
    'url_controller_layer'   => 'controller',
    // 表单请求类型伪装变量
    'var_method'             => '_method',
    // 表单ajax伪装变量
    'var_ajax'               => '_ajax',
    // 表单pjax伪装变量
    'var_pjax'               => '_pjax',
    // 是否开启请求缓存 true自动缓存 支持设置请求缓存规则
    'request_cache'          => false,
    // 请求缓存有效期
    'request_cache_expire'   => null,
    // 全局请求缓存排除规则
    'request_cache_except'   => [],
    // 是否开启路由缓存
    'route_check_cache'      => false,
    // 路由缓存的Key自定义设置（闭包），默认为当前URL和请求类型的md5
    'route_check_cache_key'  => '',
    // 路由缓存类型及参数
    'route_cache_option'     => [],

    // 默认跳转页面对应的模板文件
    'dispatch_success_tmpl'  => Env::get('think_path') . 'tpl/dispatch_jump.tpl',
    'dispatch_error_tmpl'    => Env::get('think_path') . 'tpl/dispatch_jump.tpl',

    // 异常页面的模板文件
    'exception_tmpl'         => Env::get('think_path') . 'tpl/think_exception.tpl',

    // 错误显示信息,非调试模式有效
    'error_message'          => '页面错误！请稍后再试～',
    // 显示错误信息
    'show_error_msg'         => true,
    // 异常处理handle类 留空使用 \think\exception\Handle
    'exception_handle'       => '',

    //*********************************个推******************************************
    'IGt'                    => array(
        'HOST'               => 'http://sdk.open.api.igexin.com/apiex.htm',
        'APPKEY'             => 'orx6EDffPC9ePo1yvtxj22',
        'APPID'              => '2M7WiJskFn8r1k0EqQuik6',
        'MASTERSECRET'       => 'uAJGMajExZA1LNNUF8KdF3'
        ),
        
    //***********************************URL设置**************************************
    'MODULE_ALLOW_LIST'     => array('home','admin','mobile','api', 'wechat','agent','agency'),    //允许访问列表
    'DEFAULT_MODULE'        => 'home',
    'URL_MODEL'             => 3,

    //*********************************微信支付******************************************
    'WEIXINPAY_CONFIG'       => array(
        'APPID'              => 'wx4cc2b44cf67fbf21', // 微信支付APPID
        'APPSECRET'          => '5fe63a1a6bfe9b5adb7d8a3da7080fd4',  //公众帐号secert
        'MCHID'         =>'1512255861',
        'KEY'         =>'ea9a841c863650d610c165392a59d36e',
        'NOTIFY_URL'         => 'http://wx.yxm360.com/hotel/notify/', // 接收支付状态的连接
        'TOKEN'              =>  'ANTIPHON',
		'XCX_APPID'         =>'wx96d696b078c646e5',
        'XCX_MCHID'         =>'1497167762',
        'XCX_KEY'         =>'javatomjavatomjavatomjavatomjava',
        'XCX_APPSECRET'         =>'f886fd741a9afbb2df1a8138cd8d31d9',
        'APP_APPID'=>'wx6eb3f7a03d8aa91f',
        'APP_MCHID'=>'1512255861',
        'APP_APPSECRET'=>'ea9a841c863650d610c165392a59d36e',
     ),
        
	/**************************************阿里云OSS图片上传************************************************/
    'OSS_IMAGE'=>array(
        'accessKeyId'=>'LTAIJAvioFjOqdKv',
        'accessKeySecret'=>'ITE3TtTDck3AnFCyCIKPlX2i3LbRY9',
        'endpoint'=>'http://oss-cn-shanghai.aliyuncs.com',
        'bucket'=>'yangxiaomi',
        'oss_url'=>'http://yangxiaomi.oss-cn-shanghai.aliyuncs.com/',
        'cdn_usl'=>'http://oss.mingxin001.com/',
        'cdn_usls'=>'https://oss.mingxin001.com/'
    ),

    /**************************************阿里云OSS视频上传************************************************/
    'OSS_VIDEO'=>array(
        'accessKeyId'=>'LTAIJAvioFjOqdKv',
        'accessKeySecret'=>'ITE3TtTDck3AnFCyCIKPlX2i3LbRY9',
        'endpoint'=>'http://oss-cn-shanghai.aliyuncs.com',
        'bucket'=>'yangxiaomivideo',
        'oss_url'=>'http://yangxiaomivideo.oss-cn-shanghai.aliyuncs.com/',
        'cdn_usl'=>'http://video.mingxin001.com/',
    ),
    
	'scale_1'=>0.4,//1级比例
	'scale_2'=>0.35,//2级比例
    'scale_3'=>0.05,//管理奖励，创投A发展创投B后，创投B下的所有新增创客，创投A都要拿到5%的团队管理奖励
    'withdraw_rate'=>0.036,//提现手续费比例

	
];
