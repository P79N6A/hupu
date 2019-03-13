<?php

namespace app\admin\controller;

use app\admin\model\AuthGroup;
use think\App;
use think\Controller;
use think\Db;
use org\Verify;
use think\Request;

class Login extends Controller
{

    public function __construct(App $app = null)
    {
        parent::__construct($app);
        if ('loginout' != $this->request->action()) {
            //跳过检测以及主页权限
            if (is_array(session('admin'))) {
                $this->redirect('/admin/index');

            }
        }
    }

    /**
     * 登录页面
     * @return
     */
    public function index()
    {
        $this->assign('verify_type', config('verify_type'));
        return $this->fetch('/login');
    }


    /**
     * 登录操作
     * @return
     */
    public function doLogin()
    {
        $username = input("param.username");
        $password = input("param.password");

        if (config('verify_type') == 1) {
            $code = input("param.code");
        }

//        $result = $this->validate(compact('username', 'password'), 'AdminValidate');
//        if(true !== $result){
//            return json(['code' => -5, 'url' => '', 'msg' => $result]);
//        }
        $verify = new Verify();
        if (config('verify_type') == 1) {
            if (!$code) {
                return json(['code' => -4, 'url' => '', 'msg' => '请输入验证码']);
            }
            if (!$verify->check($code)) {
                return json(['code' => -4, 'url' => '', 'msg' => '验证码错误']);
            }
        }

        if(input('type')==1){
            $map[]  = ['vip','eq',0];

        }else{

            $map[]  = ['vip','neq',0];

        }
        $map[] = ['username','=',$username];
        $map[] = ['lock','=',0];

        $hasUser = Db::name('admin_user')->where($map)->find();

        if (empty($hasUser)) {
            return json(['code' => -1, 'url' => $map, 'msg' => '账户不存在']);
        }

//        if(md5(md5($password) . config('auth_key')) != $hasUser['password']){
//            writelog($hasUser['id'],$username,'用户【'.$username.'】登录失败：密码错误',2);
//            return json(['code' => -2, 'url' => '', 'msg' => '账号或密码错误']);
//        }
        if (md5(sha1(md5($password))) != $hasUser['password']) {
//            writelog($hasUser['id'],$username,'用户【'.$username.'】登录失败：密码错误',2);
            return json(['code' => -2, 'url' => '', 'msg' => '账号或密码错误']);
        }

        if (1 == $hasUser['lock']) {
//            writelog($hasUser['id'],$username,'用户【'.$username.'】登录失败：该账号被禁用',2);
            return json(['code' => -6, 'url' => '', 'msg' => '该账号被禁用']);
        }

        if(isset($hasUser['group_id'])){
           $group_id = $hasUser['group_id'];
        }else{
            $group_id = 4;
        }

       //获取该管理员的角色信息
        $user = new AuthGroup();
        $info = $user->getRoleInfo($group_id);
//
        session('admin', $hasUser);         //用户ID

        session('uid', $hasUser['user_id']);         //用户ID
        session('username', $hasUser['username']);  //用户名
//        session('portrait', $hasUser['portrait']); //用户头像
        session('role_id', $info['id']);    //角色名
        session('role_name', $info['title']);    //角色名
        session('rule', $info['rules']);        //角色节点
        session('name', $info['name']);         //角色权限

        //更新管理员状态
        $param = [
//            'mac' => $hasUser['mac'] + 1,
            'last_ip' => request()->ip(),
            'last_login' => time(),
//            'token' => md5($hasUser['username'] . $hasUser['password'])
        ];

        Db::name('admin_user')->where('user_id', $hasUser['user_id'])->update($param);
//        writelog($hasUser['id'],session('username'),'用户【'.session('username').'】登录成功',1);
        return json(['code' => 1, 'url' => url('Admin'), 'msg' => '登录成功！']);
    }


    /**
     * 验证码
     * @return
     */
    public function checkVerify()
    {
        $verify = new Verify();
        $verify->imageH = 32;
        $verify->imageW = 100;
        $verify->codeSet = '0123456789';
        $verify->length = 4;
        $verify->useNoise = false;
        $verify->fontSize = 14;
        return $verify->entry();
    }


    /**
     * 退出登录
     * @return
     */
    public function loginOut()
    {
        session('admin', null);
        session(null);
        cache('db_config_data', null);//清除缓存中网站配置信息
        $this->redirect('Login/index');
    }
}