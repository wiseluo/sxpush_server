<?php
namespace App\HttpController;

use EasySwoole\EasySwoole\Core;
use EasySwoole\EasySwoole\Trigger;
use EasySwoole\Http\Message\Status;
use EasySwoole\RedisPool\Redis;

use App\HttpController\BaseController;
use App\Model\ApplicationModel;

// PUSH API的基类
class PushApiBase extends BaseController
{
    public $push_request_sxappkey; // public才会根据协程清除

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

        //获取请求应用信息
        $request_res = $this->getRequestApplication();
        if($request_res['code'] !== 200) {
            $this->json_res(['code'=> $request_res['code'], 'msg'=> $request_res['msg']]);
            return false;
        }
        return true;
    }

    // Authorization: Basic base64_auth_string
    function getRequestApplication()
    {
        $authorization = $this->request()->getHeader('authorization');
        if(empty($authorization)) {
            return ['code'=> 9010, 'msg'=> '缺少鉴权信息'];
        }
        $authorization_arr = explode(' ', $authorization[0]);
        if($authorization_arr[0] != 'Basic') {
            return ['code'=> 9010, 'msg'=> '缺少鉴权信息'];
        }
        $base64_auth_string = $authorization_arr[1];
        $redis = Redis::defer('redis');
        $application  = $redis->get('sxpush_push_application_'. $base64_auth_string);
        if($application) {
            $application = json_decode($application,true);
            //$this->request()->withAttribute('push_application', $application);
            $this->push_request_sxappkey = $application['sxappkey'];
            return ['code'=> 200, 'msg'=> '鉴权成功'];
        }else{
            $auth_string = base64_decode($base64_auth_string);
            if($auth_string == "") {
                return ['code'=> 9011, 'msg'=> '鉴权失败'];
            }
            //var_dump($auth_string);
            $auth_arr = explode(':', $auth_string);
            //var_dump($auth_arr);
            $application = ApplicationModel::create()->where(['sxappkey'=> $auth_arr[0], 'delete_time'=> 0])->get();
            if($application) {
                if($application['sxappsecret'] !== $auth_arr[1]) {
                    return ['code'=> 9011, 'msg'=> '鉴权失败'];
                }
                $redis->set('sxpush_push_application_'.$base64_auth_string, json_encode($application), 3600);
                //$this->request()->withAttribute('push_application', $application);
                $this->push_request_sxappkey = $application['sxappkey'];
                return ['code'=> 200, 'msg'=> '鉴权成功'];
            }else{
                return ['code'=> 9011, 'msg'=> '鉴权失败'];
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