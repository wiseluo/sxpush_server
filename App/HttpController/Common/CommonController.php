<?php

namespace App\HttpController\Common;

use EasySwoole\VerifyCode\Conf;
use EasySwoole\VerifyCode\VerifyCode;
use EasySwoole\Redis\Redis;
use EasySwoole\RedisPool\Redis as RedisPool;

use App\HttpController\BaseController;

// 公用类
class CommonController extends BaseController
{
    // 返回unid
    public function getUnid()
    {
        $code_hash = uniqid().uniqid();
        $this->response()->write($code_hash);
        return true;
    }

}