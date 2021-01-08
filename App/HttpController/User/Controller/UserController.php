<?php
namespace App\HttpController\User\Controller;

use EasySwoole\Component\Di;
use App\HttpController\WebApiBase;
use App\HttpController\User\Service\UserService;
use App\HttpController\User\Validate\UserValidate;

class UserController extends WebApiBase
{
    //白名单
    protected $whiteList = ['login','register','send_sms_code','forgot_password'];

    public function __construct()
    {
        parent::__construct();
        Di::getInstance()->set('UserService', UserService::class);
    }

    public function register()
    {
        $param = $this->request()->getRequestParam();
        $validate_ret = UserValidate::getInstance()->check('register', $param);
        if($validate_ret !== true) {
            return $this->writeJson(400, $validate_ret);
        }
        $res = Di::getInstance()->get('UserService')->registerService($param);
        if($res['status']) {
            return $this->writeJson(200, $res['msg']);
        }else{
            return $this->writeJson(400, $res['msg']);
        }
    }

    public function login()
    {
        $param = $this->request()->getRequestParam();
        $validate_ret = UserValidate::getInstance()->check('login', $param);
        if($validate_ret !== true) {
            return $this->writeJson(400, $validate_ret);
        }
        $res = Di::getInstance()->get('UserService')->loginService($param);
        if($res['status']) {
            return $this->writeJson(200, $res['msg'], $res['data']);
        }else{
            return $this->writeJson(400, $res['msg']);
        }
    }

    public function resetPassword()
    {
        $param = $this->request()->getRequestParam();
        $validate_ret = UserValidate::getInstance()->check('resetPassword', $param);
        if($validate_ret !== true) {
            return $this->writeJson(400, $validate_ret);
        }
        $res = Di::getInstance()->get('UserService')->resetPasswordService($token);
        if($res['status']) {
            return $this->writeJson(200, $res['msg']);
        }else{
            return $this->writeJson(400, $res['msg']);
        }
    }

    public function logout()
    {
        $token = $this->request()->getRequestParam('token');
        $res = Di::getInstance()->get('UserService')->logoutService($token);
        return $this->writeJson(200, '退出成功');
    }

    public function info()
    {
        return $this->writeJson(200, $this->request_user_id);
    }
}
