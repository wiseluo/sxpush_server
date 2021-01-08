<?php
namespace App\HttpController\Client\Datamanager;

use App\Model\ApplicationClientModel;

class ClientDatamanager
{
    public function get($id)
    {
        return ApplicationClientModel::create()->where(['id'=> $id, 'delete_time'=> 0])->get();
    }

    public function find($where)
    {
        return ApplicationClientModel::create()->where($where)->where('delete_time', 0)->get();
    }

    public function save($data)
    {
        return ApplicationClientModel::create($data)->save();
    }

    public function update($data, $where)
    {
        $client = ApplicationClientModel::create();
        $res = $client->update($data, $where);
        if($res) {
            return $client->lastQueryResult()->getAffectedRows();
        }else{
            return false;
        }
    }

    public function delete($where)
    {
        return ApplicationClientModel::create()->destroy($where);
    }

    public function softDelete($where)
    {
        $client = ApplicationClientModel::create();
        $res = $client->update(['delete_time'=> time()], $where);
        if($res) {
            return $client->lastQueryResult()->getAffectedRows();
        }else{
            return false;
        }
    }

    public function select($where)
    {
        return ApplicationClientModel::create()->where($where)->where('delete_time', 0)->all();
    }

}
