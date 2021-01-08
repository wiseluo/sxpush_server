<?php
namespace App\HttpController\Application\Service;

class ApplicationPlatformService extends BaseService
{
    public function readService($id)
    {
        $app_platform = $this->ApplicationPlatformDatamanager->get($id);
        if($app_platform) {
            return ['status' => 1, 'msg' => '获取成功', 'data'=> $app_platform];
        }else{
            return ['status' => 0, 'msg' => '应用不存在'];
        }
    }

    public function saveAndroidService($param)
    {
        $app = $this->ApplicationDatamanager->get($param['application_id']);
        if($app == null) {
            return ['status' => 0, 'msg' => '该应用不存在'];
        }
        $app_platform = $this->ApplicationPlatformDatamanager->find(['application_id'=> $param['application_id'], 'platform'=> $param['platform']]);
        if($app_platform) {
            return ['status' => 0, 'msg' => '该应用平台已设置'];
        }
        $data = [
            'application_id'=> $param['application_id'],
            'sxappkey'=> $app['sxappkey'],
            'platform'=> $param['platform'],
            'package_name'=> $param['package_name'],
            'app_type'=> $param['app_type'],
            'appid'=> $param['appid'],
            'appkey'=> $param['appkey'],
            'appsecret'=> $param['appsecret'],
            'activity_class'=> $param['activity_class'],
        ];
        $res = $this->ApplicationPlatformDatamanager->save($data);
        if($res) {
            return ['status' => 1, 'msg' => '应用配置成功'];
        }else{
            return ['status' => 0, 'msg' => '应用配置失败'];
        }
    }

    public function saveIosService($param)
    {
        $app = $this->ApplicationDatamanager->get($param['application_id']);
        if($app == null) {
            return ['status' => 0, 'msg' => '该应用不存在'];
        }
        $app_platform = $this->ApplicationPlatformDatamanager->find(['application_id'=> $param['application_id'], 'platform'=> 'ios']);
        if($app_platform) {
            return ['status' => 0, 'msg' => '该应用平台已设置'];
        }
        $data = [
            'application_id'=> $param['application_id'],
            'sxappkey'=> $app['sxappkey'],
            'platform'=> 'ios',
            'appid'=> $param['appid'],
            // 'apns_auth_key'=> $param['apns_auth_key'],
            // 'apns_auth_key_id'=> $param['apns_auth_key_id'],
            // 'team_id'=> $param['team_id'],
            'ios_cert_sandbox'=> $param['ios_cert_sandbox'],
            'ios_cert_production'=> $param['ios_cert_production'],
        ];
        $res = $this->ApplicationPlatformDatamanager->save($data);
        if($res) {
            return ['status' => 1, 'msg' => '应用配置成功'];
        }else{
            return ['status' => 0, 'msg' => '应用配置失败'];
        }
    }

}