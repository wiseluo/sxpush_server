<?php
namespace App\HttpController\User\Validate;

use EasySwoole\Component\Singleton;
use EasySwoole\Validate\Validate;

class UserValidate
{
    use Singleton;
    
    protected function validateRule(?string $action): ?Validate
    {
        $v = new Validate();
        switch ($action) {
            case 'register':
                $v->addColumn('username', '账号')->required('不能为空')->lengthMax(30, '最大长度不大于30位')->func(function($param, $key) {
                    if(preg_match("/^([a-zA-Z0-9]+[_|\_|\.]?)*[a-zA-Z0-9]+@([a-zA-Z0-9]+[_|\_|\.]?)*[a-zA-Z0-9]+\.[a-zA-Z]{2,3}$/",$param[$key])){
                        return true;
                    }else{
                        return '邮箱格式不正确';
                    }
                });
                $v->addColumn('password', '密码')->required('不能为空')->lengthMin(8, '最小长度不小于8位')->lengthMax(25, '最大长度不大于25位')->alphaNum('必须是英文大小写和数字');
                $v->addColumn('phone', '手机号')->required('不能为空')->length(11,'长度错误')->func(function($param, $key) {
                    if(preg_match("/^1[3-9][0-9]\d{8}$/",$param[$key])){
                        return true;
                    }else{
                        return '格式不正确';
                    }
                });
                $v->addColumn('sms_code', '短信验证码')->required('不能为空')->length(4,'长度错误');
                $v->addColumn('agreement', '用户协议')->required('不能为空')->equal(1);
                break;
            case 'login':
                $v->addColumn('unid', 'unid')->required('不能为空')->length(26,'长度错误');
                $v->addColumn('username', '账号')->required('不能为空')->lengthMax(30, '最大长度不大于30位')->func(function($param, $key) {
                    if(preg_match("/^([a-zA-Z0-9]+[_|\_|\.]?)*[a-zA-Z0-9]+@([a-zA-Z0-9]+[_|\_|\.]?)*[a-zA-Z0-9]+\.[a-zA-Z]{2,3}$/",$param[$key])){
                        return true;
                    }else{
                        return '邮箱格式不正确';
                    }
                });
                $v->addColumn('password', '密码')->required('不能为空')->lengthMin(8, '最小长度不小于8位')->lengthMax(25, '最大长度不大于25位')->alphaNum('必须是英文大小写和数字');
                $v->addColumn('verify_code', '图片验证码')->required('不能为空')->length(4,'长度错误');
                break;
            case 'resetPassword':
                $v->addColumn('username', '账号')->required('不能为空')->lengthMax(30, '最大长度不大于30位')->func(function($param, $key) {
                    if(preg_match("/^([a-zA-Z0-9]+[_|\_|\.]?)*[a-zA-Z0-9]+@([a-zA-Z0-9]+[_|\_|\.]?)*[a-zA-Z0-9]+\.[a-zA-Z]{2,3}$/",$param[$key])){
                        return true;
                    }else{
                        return '邮箱格式不正确';
                    }
                });
                $v->addColumn('password', '新密码')->required('不能为空')->lengthMin(8, '最小长度不小于8位')->lengthMax(25, '最大长度不大于25位')->alphaNum('必须是英文大小写和数字');
                $v->addColumn('sms_code', '短信验证码')->required('不能为空')->length(4,'长度错误');
                break;
        }
        return $v;
    }

    public function check(?string $action, $param)
    {
        $v = $this->validateRule($action);
        $ret = $v->validate($param);
        return $ret ? true : "{$v->getError()->getField()}@{$v->getError()->getFieldAlias()}:{$v->getError()->getErrorRuleMsg()}";
    }
}