<?php
namespace App\HttpController\Application\Service;

class ApplicationService extends BaseService
{
    public function indexService($param)
    {
        return $this->ApplicationDatamanager->list($param);
    }

    public function readService($id)
    {
        $app = $this->ApplicationDatamanager->get($id);
        if($app) {
            return ['status' => 1, 'msg' => '获取成功', 'data'=> $app];
        }else{
            return ['status' => 0, 'msg' => '应用不存在'];
        }
    }

    public function saveService($param)
    {
        $sxappkey = hash('sha1', $param['name'].time());
        $data = [
            'user_id'=> $param['user_id'],
            'name'=> $param['name'],
            'icon'=> $param['icon'],
            'sxappkey'=> $sxappkey,
            'sxappsecret'=> hash('sha1', $sxappkey.time()),
        ];
        $res = $this->ApplicationDatamanager->save($data);
        if($res) {
            return ['status' => 1, 'msg' => '新建应用成功'];
        }else{
            return ['status' => 0, 'msg' => '新建应用失败'];
        }
    }

    public function updateService($param)
    {
        $application = $this->ApplicationDatamanager->get($param['id']);
        if($application == null) {
            return ['status' => 0, 'msg' => '应用不存在'];
        }
        $data = [
            'name'=> $param['name'],
        ];
        $res = $this->ApplicationDatamanager->update($data, ['id'=> $param['id']]);
        if($res) {
            return ['status' => 1, 'msg' => '应用修改成功'];
        }else{
            return ['status' => 0, 'msg' => '应用修改失败'];
        }
    }

    public function deleteService($id)
    {
        $application = $this->ApplicationDatamanager->get($id);
        if($application == null) {
            return ['status' => 0, 'msg' => '应用不存在'];
        }

        $res = $this->ApplicationDatamanager->softDelete(['id'=> $id]);
        if($res) {
            $this->ApplicationPlatformDatamanager->softDelete(['application_id'=> $id]);
            return ['status' => 1, 'msg' => '删除成功'];
        }else{
            return ['status' => 0, 'msg' => '删除失败'];
        }
    }

    public function resetSxappserectService($param)
    {
        $application = $this->ApplicationDatamanager->find(['sxappkey'=> $param['sxappkey']]);
        if($application == null) {
            return ['status' => 0, 'msg' => '应用不存在'];
        }
        $data = [
            'sxappsecret'=> hash('sha1', $param['sxappkey'].time()),
        ];
        $res = $this->ApplicationDatamanager->update($data, ['sxappkey'=> $param['sxappkey']]);
        if($res) {
            return ['status' => 1, 'msg' => '秘钥重置成功'];
        }else{
            return ['status' => 0, 'msg' => '秘钥重置失败'];
        }
    }
}