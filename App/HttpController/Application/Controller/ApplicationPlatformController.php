<?php
namespace App\HttpController\Application\Controller;

use EasySwoole\Component\Di;
use App\HttpController\WebApiBase;
use App\HttpController\Application\Service\ApplicationPlatformService;
use App\HttpController\Application\Validate\ApplicationPlatformValidate;

class ApplicationPlatformController extends WebApiBase
{
    public function __construct()
    {
        parent::__construct();
        Di::getInstance()->set('ApplicationPlatformService', ApplicationPlatformService::class);
    }

    public function read()
    {
        $id = $this->request()->getRequestParam('id');
        $res = Di::getInstance()->get('ApplicationPlatformService')->readService($id);
        if($res['status']) {
            return $this->writeJson(200, $res['msg'], $res['data']);
        }else{
            return $this->writeJson(400, $res['msg']);
        }
    }

    public function saveAndroid()
    {
        $param = $this->request()->getRequestParam();
        $validate_ret = ApplicationPlatformValidate::getInstance()->check('saveAndroid', $param);
        if($validate_ret !== true) {
            return $this->writeJson(400, $validate_ret);
        }
        $param['icon'] = isset($param['icon']) ? $param['icon'] : '';
        $param['user_id'] = $this->request_user_id;
        $res = Di::getInstance()->get('ApplicationPlatformService')->saveAndroidService($param);
        if($res['status']) {
            return $this->writeJson(200, $res['msg']);
        }else{
            return $this->writeJson(400, $res['msg']);
        }
    }

    public function saveIos()
    {
        $param = $this->request()->getRequestParam();
        $validate_ret = ApplicationPlatformValidate::getInstance()->check('saveIos', $param);
        if($validate_ret !== true) {
            return $this->writeJson(400, $validate_ret);
        }
        $res = Di::getInstance()->get('ApplicationPlatformService')->saveIosService($param);
        if($res['status']) {
            return $this->writeJson(200, $res['msg']);
        }else{
            return $this->writeJson(400, $res['msg']);
        }
    }

}
