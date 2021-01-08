<?php
namespace App\HttpController;

use EasySwoole\EasySwoole\Core;
use EasySwoole\EasySwoole\Trigger;
use EasySwoole\Http\Message\Status;
use EasySwoole\RedisPool\Redis as RedisPool;

use App\HttpController\BaseController;
use App\Model\UserModel;
use App\Utility\JWTManager;

// WEB API的基类
class WebApiBase extends BaseController
{
    public $request_user_id; // public才会根据协程清除
    //白名单
    protected $whiteList = [];

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

        //白名单判断
        if (in_array($action, $this->whiteList)) {
            return true;
        }
        //获取登入信息
        $request_res = $this->getRequestUser();
        if($request_res['code'] !== 200) {
            $this->json_res(['code'=> $request_res['code'], 'msg'=> $request_res['msg']]);
            return false;
        }
        return true;
    }

    function getRequestUser()
    {
        $token = $this->request()->getRequestParam('token');
        if($token == '') {
            return ['code'=> 401, 'msg'=> 'token必填'];
        }
        $redis = RedisPool::defer('redis');
        //$redis->del('web_user_'.$token);
        $user = $redis->get('sxpush_web_user_'.$token);
        if($user) {
            $user  = json_decode($user,true);
            //$this->request()->withAttribute('request_user',$user);
            $this->request_user_id = (int)$user['id'];
            return ['code'=> 200, 'msg'=> '鉴权成功'];
        }else{
            try{
                $jwt = JWTManager::getInstance();
                $jwtObject = $jwt->decode($token);
                $status = $jwtObject->getStatus();
                switch ($status)
                {
                    case -1:
                        return ['code'=> 400, 'msg'=> 'token无效'];
                    case -2:
                        return ['code'=> 400, 'msg'=> 'token过期'];
                    case  1:
                        $token_data = $jwtObject->getData();
                        break;
                }
            }catch(\Exception $e) {
                //var_dump($e->getMessage());
                return ['code'=> 401, 'msg'=> 'token错误'];
            }
            $user = UserModel::create()->where(['id'=> $token_data['id'], 'delete_time'=> 0])->get();
            if($user) {
                $redis->set('sxpush_web_user_'.$token, json_encode($user), 3600);
                //$this->request()->withAttribute('request_user',$user);
                $this->request_user_id = (int)$user['id'];
                return ['code'=> 200, 'msg'=> '鉴权成功'];
            }else{
                return ['code'=> 401, 'msg'=> '用户不存在'];
            }
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