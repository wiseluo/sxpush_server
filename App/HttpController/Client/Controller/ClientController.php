<?php
namespace App\HttpController\Client\Controller;

use EasySwoole\Component\Di;
use App\HttpController\ClientApiBase;
use App\HttpController\Client\Service\ClientService;
use App\HttpController\Client\Validate\ClientValidate;

class ClientController extends ClientApiBase
{
    public function __construct()
    {
        parent::__construct();
        Di::getInstance()->set('ClientService', ClientService::class);
    }

    public function registerPush()
    {
        $param = $this->request()->getRequestParam();
        $validate_ret = ClientValidate::getInstance()->check('registerPush', $param);
        if($validate_ret !== true) {
            return $this->writeJson(400, $validate_ret);
        }
        $res = Di::getInstance()->get('ClientService')->registerPushService($param);
        if($res['status']) {
            return $this->writeJson(200, $res['msg'], $res['data']);
        }else{
            return $this->writeJson(400, $res['msg']);
        }
    }

    public function unregisterPush()
    {
        $param = $this->request()->getRequestParam();
        $validate_ret = ClientValidate::getInstance()->check('unregisterPush', $param);
        if($validate_ret !== true) {
            return $this->writeJson(400, $validate_ret);
        }
        $res = Di::getInstance()->get('ClientService')->unregisterPushService($param);
        if($res['status']) {
            return $this->writeJson(200, $res['msg']);
        }else{
            return $this->writeJson(400, $res['msg']);
        }
    }

    public function reportPushToken()
    {
        $param = $this->request()->getRequestParam();
        $validate_ret = ClientValidate::getInstance()->check('reportPushToken', $param);
        if($validate_ret !== true) {
            return $this->writeJson(400, $validate_ret);
        }
        $res = Di::getInstance()->get('ClientService')->reportPushTokenService($param);
        if($res['status']) {
            return $this->writeJson(200, $res['msg']);
        }else{
            return $this->writeJson(400, $res['msg']);
        }
    }
}
