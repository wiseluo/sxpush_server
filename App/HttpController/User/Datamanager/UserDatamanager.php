<?php
namespace App\HttpController\User\Datamanager;

use App\Model\UserModel;

class UserDatamanager
{
    public function get($id)
    {
        return UserModel::create()->where(['id'=> $id, 'delete_time'=> 0])->get();
    }

    public function find($where)
    {
        return UserModel::create()->where($where)->where('delete_time', 0)->get();
    }

    public function save($data)
    {
        return UserModel::create($data)->save();
    }

    public function update($data, $where)
    {
        $user = UserModel::create();
        $res = $user->update($data, $where);
        if($res) {
            return $user->lastQueryResult()->getAffectedRows();
        }else{
            return false;
        }
    }

    public function delete($where)
    {
        return UserModel::create()->destroy($where);
    }

    public function softDelete($where)
    {
        $user = UserModel::create();
        $res = $user->update(['delete_time'=> time()], $where);
        if($res) {
            return $user->lastQueryResult()->getAffectedRows();
        }else{
            return false;
        }
    }

    public function select()
    {
        return UserModel::create()->field('word')->where('delete_time', 0)->all();
    }

    public function list($param)
    {
        $where['delete_time'] = 0;
        $model = UserModel::create()->page($param['page'], $param['page_size']);
        // 列表数据
        $list = $model->where($where)->order('id', 'DESC')->withTotalCount()->all();
        $total = $model->lastQueryResult()->getTotalCount();
        return ['total'=> $total, 'list'=> $list];
    }

}
