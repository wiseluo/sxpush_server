<?php
namespace App\HttpController;

use EasySwoole\EasySwoole\Core;
use EasySwoole\EasySwoole\Trigger;
use EasySwoole\Http\Message\Status;
use EasySwoole\RedisPool\Redis as RedisPool;

use App\HttpController\BaseController;
use App\Model\UserModel;
use App\Utility\JWTManager;

// Client API的基类
class ClientApiBase extends BaseController
{
    private $secret_key = 'sxpush_client';

    function index()
    {
        $this->actionNotFound('index');
    }

    protected function actionNotFound(?string $action): void
    {
        $this->writeJson(Status::CODE_NOT_FOUND);
    }

    public function onRequest(?string $action): ?bool
    {
        if (!parent::onRequest($action)) {
            return false;
        }
        $token = $this->request()->getRequestParam('token'); //使用bcrypt加密
        $timestamp = $this->request()->getRequestParam('timestamp');
        if( (time() *1000 - $timestamp) > 60000) { //毫秒 13位
            $this->json_res(['code'=> 400, 'msg'=> 'token过期']);
            return false;
        }
        $secret = $this->secret_key .'_'. $timestamp;
        //var_dump(password_hash($secret, PASSWORD_DEFAULT));
        if(password_verify($secret, $token)) {
            return true;
        }else{
            $this->json_res(['code'=> 400, 'msg'=> '验证失败']);
            return false;
        }
    }

    protected function onException(\Throwable $throwable): void
    {
        if (Core::getInstance()->isDev()) {
            $this->writeJson(500, null, $throwable->getMessage());
        } else {
            //拦截错误进日志
            Trigger::getInstance()->throwable($throwable);
            $this->writeJson(500, null, '系统繁忙,请稍后再试');
        }
    }
}