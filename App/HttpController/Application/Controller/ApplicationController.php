<?php
namespace App\HttpController\Application\Controller;

use EasySwoole\Component\Di;
use App\HttpController\WebApiBase;
use App\HttpController\Application\Service\ApplicationService;
use App\HttpController\Application\Validate\ApplicationValidate;

class ApplicationController extends WebApiBase
{
    public function __construct()
    {
        parent::__construct();
        Di::getInstance()->set('ApplicationService', ApplicationService::class);
    }

    public function index()
    {
        $param = $this->request()->getRequestParam();
        $data['page'] = isset($param['page']) ? $param['page'] : 1;
        $data['page_size'] = isset($param['page_size']) ? $param['page_size'] : 10;
        $data['user_id'] = $this->request_user_id;
        $res = Di::getInstance()->get('ApplicationService')->indexService($data);
        return $this->json_list($res['list'], $res['total'], $data['page'], $data['page_size']);
    }

    public function read()
    {
        $id = $this->request()->getRequestParam('id');
        $res = Di::getInstance()->get('ApplicationService')->readService($id);
        if($res['status']) {
            return $this->writeJson(200, $res['msg'], $res['data']);
        }else{
            return $this->writeJson(400, $res['msg']);
        }
    }

    public function save()
    {
        $param = $this->request()->getRequestParam();
        $validate_ret = ApplicationValidate::getInstance()->check('save', $param);
        if($validate_ret !== true) {
            return $this->writeJson(400, $validate_ret);
        }
        $param['icon'] = isset($param['icon']) ? $param['icon'] : '';
        $param['user_id'] = $this->request_user_id;
        $res = Di::getInstance()->get('ApplicationService')->saveService($param);
        if($res['status']) {
            return $this->writeJson(200, $res['msg']);
        }else{
            return $this->writeJson(400, $res['msg']);
        }
    }

    public function update()
    {
        $param = $this->request()->getRequestParam();
        $validate_ret = ApplicationValidate::getInstance()->check('update', $param);
        if($validate_ret !== true) {
            return $this->writeJson(400, $validate_ret);
        }
        $res = Di::getInstance()->get('ApplicationService')->updateService($param);
        if($res['status']) {
            return $this->writeJson(200, $res['msg']);
        }else{
            return $this->writeJson(400, $res['msg']);
        }
    }

    public function delete()
    {
        $id = $this->request()->getRequestParam('id');
        $res = Di::getInstance()->get('ApplicationService')->deleteService($id);
        if($res['status']) {
            return $this->writeJson(200, $res['msg']);
        }else{
            return $this->writeJson(400, $res['msg']);
        }
    }

    public function resetSxappserect()
    {
        $param = $this->request()->getRequestParam();
        $validate_ret = ApplicationValidate::getInstance()->check('resetSxappserect', $param);
        if($validate_ret !== true) {
            return $this->writeJson(400, $validate_ret);
        }
        $res = Di::getInstance()->get('ApplicationService')->resetSxappserectService($param);
        if($res['status']) {
            return $this->writeJson(200, $res['msg']);
        }else{
            return $this->writeJson(400, $res['msg']);
        }
    }
}
