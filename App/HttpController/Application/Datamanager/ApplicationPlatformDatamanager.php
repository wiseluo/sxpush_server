<?php
namespace App\HttpController\Application\Datamanager;

use App\Model\ApplicationPlatformModel;

class ApplicationPlatformDatamanager
{
    public function get($id)
    {
        return ApplicationPlatformModel::create()->where(['id'=> $id, 'delete_time'=> 0])->get();
    }

    public function find($where)
    {
        return ApplicationPlatformModel::create()->where($where)->where('delete_time', 0)->get();
    }

    public function save($data)
    {
        return ApplicationPlatformModel::create($data)->save();
    }

    public function update($data, $where)
    {
        $application_platform = ApplicationPlatformModel::create();
        $res = $application_platform->update($data, $where);
        if($res) {
            return $application_platform->lastQueryResult()->getAffectedRows();
        }else{
            return false;
        }
    }

    public function delete($where)
    {
        return ApplicationPlatformModel::create()->destroy($where);
    }

    public function softDelete($where)
    {
        $application_platform = ApplicationPlatformModel::create();
        $res = $application_platform->update(['delete_time'=> time()], $where);
        if($res) {
            return $application_platform->lastQueryResult()->getAffectedRows();
        }else{
            return false;
        }
    }

}
