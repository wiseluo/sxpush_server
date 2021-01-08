<?php

namespace App\HttpController\Common;

use EasySwoole\VerifyCode\Conf;
use EasySwoole\VerifyCode\VerifyCode;
use EasySwoole\Redis\Redis;
use EasySwoole\RedisPool\Redis as RedisPool;

use App\HttpController\BaseController;

// 验证码类
class VerifyCodeController extends BaseController
{
    // 返回图形验证码key
    public function verifyCodeKey()
    {
        $code_hash = uniqid().uniqid();
        $this->response()->write($code_hash);
        return true;
    }

    //返回图形验证码图片
    public function verifyCode()
    {
        $unid = $this->request()->getRequestParam('unid');
        if($unid == '') {
            $this->response()->write('');
            return true;
        }
        $config = new Conf();
        $config->setUseNoise(true);
        $code = new VerifyCode($config);
        $num = mt_rand(1000,9999);

        //invoke方式 
        RedisPool::invoke('redis', function (Redis $redis) use ($unid,$num) {
            $redis->set('verifyCode'. $unid, $num, 600);
        });

        $this->response()->withStatus(200);
        $this->response()->withHeader('Content-Type','image/png');
        $this->response()->write($code->DrawCode($num)->getImageByte());
    }

}