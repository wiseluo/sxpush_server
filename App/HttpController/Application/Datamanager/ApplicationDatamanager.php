<?php
namespace App\HttpController\Application\Datamanager;

use App\Model\ApplicationModel;

class ApplicationDatamanager
{
    public function get($id)
    {
        return ApplicationModel::create()->where(['id'=> $id, 'delete_time'=> 0])->get();
    }

    public function find($where)
    {
        return ApplicationModel::create()->where($where)->where('delete_time', 0)->get();
    }

    public function save($data)
    {
        return ApplicationModel::create($data)->save();
    }

    public function update($data, $where)
    {
        $application = ApplicationModel::create();
        $res = $application->update($data, $where);
        if($res) {
            return $application->lastQueryResult()->getAffectedRows();
        }else{
            return false;
        }
    }

    public function delete($where)
    {
        return ApplicationModel::create()->destroy($where);
    }

    public function softDelete($where)
    {
        $application = ApplicationModel::create();
        $res = $application->update(['delete_time'=> time()], $where);
        if($res) {
            return $application->lastQueryResult()->getAffectedRows();
        }else{
            return false;
        }
    }

    public function select()
    {
        return ApplicationModel::create()->where('delete_time', 0)->all();
    }

    public function list($param)
    {
        $where['user_id'] = $param['user_id'];
        $where['delete_time'] = 0;
        $model = ApplicationModel::create()->page($param['page'], $param['page_size']);
        // 列表数据
        $list = $model->where($where)
            ->field('id,name,icon,sxappkey,create_time')
            ->withTotalCount()
            ->all();
        $total = $model->lastQueryResult()->getTotalCount();
        return ['total'=> $total, 'list'=> $list];
    }

}
