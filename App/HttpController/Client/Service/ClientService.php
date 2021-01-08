<?php
namespace App\HttpController\Client\Service;

class ClientService extends BaseService
{
    public function registerPushService($param)
    {
        $application_platform = $this->ApplicationPlatformDatamanager->find(['package_name'=> $param['package_name'], 'platform'=> $param['platform']]);
        if($application_platform == null) {
            return ['status'=> 0, 'msg'=> '应用平台不存在'];
        }
        $client = $this->ClientDatamanager->find(['aaid'=> $param['aaid'], 'application_platform_id'=> $application_platform['id']]);
        if($client) {
            //在该平台的该应用已存在该aaid
            return ['status'=> 1, 'msg'=> '已存在', 'data'=> ['registration_id'=> $client['registration_id']]];
        }else{
            $registration_id = hash('sha1', $param['aaid'].$application_platform['appid'].time());
            $client_data = [
                'application_platform_id'=> $application_platform['id'],
                'platform' => $param['platform'],
                'aaid'=> $param['aaid'],
                'registration_id'=> $registration_id,
            ];
            $res = $this->ClientDatamanager->save($client_data);
            if($res) {
                return ['status'=> 1, 'msg'=> '注册成功', 'data'=> ['registration_id'=> $registration_id]];
            }else{
                return ['status'=> 0, 'msg'=> '注册失败'];
            }
        }
    }

    public function unregisterPushService($param)
    {
        $client = $this->ClientDatamanager->find(['registration_id'=> $param['registration_id']]);
        if($client == null) {
            return ['status'=> 0, 'msg'=> '应用客户端不存在'];
        }
        $res = $this->ClientDatamanager->softDelete(['id'=> $client['id']]);
        if($res) {
            return ['status'=> 1, 'msg'=> '注销成功', 'data'=> ['registration_id'=> $registration_id]];
        }else{
            return ['status'=> 0, 'msg'=> '注销失败'];
        }
    }

    public function reportPushTokenService($param)
    {
        $client = $this->ClientDatamanager->find(['registration_id'=> $param['registration_id']]);
        if($client == null) {
            return ['status'=> 0, 'msg'=> '应用客户端不存在'];
        }
        $res = $this->ClientDatamanager->update(['platform_token'=> $param['platform_token']], ['id'=> $client['id']]);
        if($res) {
            return ['status'=> 1, 'msg'=> '上报成功'];
        }else{
            return ['status'=> 0, 'msg'=> '上报失败'];
        }
    }
}