<?php

namespace App\HttpController\Common;

use EasySwoole\Validate\Validate;
use EasySwoole\Redis\Redis;
use EasySwoole\RedisPool\Redis as RedisPool;

use App\HttpController\BaseController;
use App\HttpController\Common\SmsHelper;

// 验证码类
class SmsCodeController extends BaseController
{
    protected function validateRule(?string $action): ?Validate
    {
        $v = new Validate();
        switch ($action){
            case 'sendSmsCode':
                $v->addColumn('unid', 'unid')->required('不能为空')->length(26,'长度错误');
                $v->addColumn('phone', '手机号')->required('不能为空')->length(11,'长度错误');
                $v->addColumn('verify_code', '图形验证码')->required('不能为空')->length(4,'长度错误');
                break;
            
        }
        return $v;
    }

    public function sendSmsCode()
    {
        $param = $this->request()->getRequestParam();
        $v = $this->validateRule('sendSmsCode');
        $ret = $v->validate($param);
        if(!$ret) {
            return $this->writeJson(400, "{$v->getError()->getField()}@{$v->getError()->getFieldAlias()}:{$v->getError()->getErrorRuleMsg()}");
        }
        $redis = RedisPool::defer('redis');
        $verify_code = $redis->get('verifyCode'. $param['unid']);
        if($verify_code !== $param['verify_code']) {
            return $this->writeJson(400, "图形验证码错误");
        }
        $code = mt_rand(1000,9999);
        //变量模板ID
        $template = '380228';
        $content = "【商翔】您好，您的验证码是：". $code ."，有效期为5分钟。如非本人操作，请忽略此短信。";
        $res = SmsHelper::sendSMS('', '', $param['phone'], $content, $template);
        $res = json_decode($res);
        if ($res->code == 0) {
            $redis->set('smsCode'. $param['phone'], $code, 600);
            return $this->writeJson(200, "短信发送成功");
        } else {
            return $this->writeJson(400, '短信发送失败! 状态：' . $res->message);
        }
    }

}