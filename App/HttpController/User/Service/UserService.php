<?php
namespace App\HttpController\User\Service;

use EasySwoole\Redis\Redis;
use EasySwoole\RedisPool\Redis as RedisPool;
use App\Utility\JWTManager;

class UserService extends BaseService
{
    public function registerService($param)
    {
        $redis = RedisPool::defer('redis');
        $sms_code = $redis->get('smsCode'. $param['phone']);
        if($sms_code !== $param['sms_code']) {
            return ['status'=> 0, 'msg'=> '短信验证码错误'];
        }
        $user = $this->UserDatamanager->find(['username'=> $param['username']]);
        if($user) {
            return ['status'=> 0, 'msg'=> '账号已存在'];
        }else{
            $user_data = [
                'username'=> $param['username'],
                'password'=> password_hash($param['password'], PASSWORD_BCRYPT),
                'phone'=> $param['phone'],
            ];
            $user_id = $this->UserDatamanager->save($user_data);
            if($user_id) {
                return ['status'=> 1, 'msg'=> '注册成功'];
            }else{
                return ['status'=> 0, 'msg'=> '注册失败'];
            }
        }
    }

    public function loginService($param)
    {
        $redis = RedisPool::defer('redis');
        $verify_code = $redis->get('verifyCode'. $param['unid']);
        if($verify_code !== $param['verify_code']) {
            return ['status'=> 0, 'msg'=> '图形验证码错误'];
        }
        $user = $this->UserDatamanager->find(['username'=> $param['username']]);
        if($user == null) {
            return ['status'=> 0, 'msg'=> '账号密码错误'];
        }else{
            if(password_verify($param['password'], $user['password'])) {
                $token = JWTManager::getInstance()->encode($user);
                return ['status'=> 1, 'msg'=> '登录成功', 'data'=> $token];
            }else{
                return ['status'=> 0, 'msg'=> '账号密码错误'];
            }
        }
    }

    public function resetPasswordService($param)
    {
        $redis = RedisPool::defer('redis');
        $sms_code = $redis->get('smsCode'. $param['phone']);
        if($sms_code !== $param['sms_code']) {
            return ['status'=> 0, 'msg'=> '短信验证码错误'];
        }
        $user = $this->UserDatamanager->find(['username'=> $param['username']]);
        if($user) {
            $res = $this->UserDatamanager->update(['password'=> password_hash($param['password'], PASSWORD_BCRYPT)], ['id'=> $user['id']]);
            if($res) {
                return ['status'=> 1, 'msg'=> '密码重置成功'];
            }else{
                return ['status'=> 0, 'msg'=> '密码重置失败'];
            }
        }else{
            return ['status'=> 0, 'msg'=> '账号不存在'];
        }
    }

    public function logoutService($token)
    {
        $redis = RedisPool::defer('redis');
        $redis->del('User_token_'.$token);
        return true;
    }
}