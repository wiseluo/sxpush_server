<?php
namespace App\HttpController\Push\Datamanager;

use App\Model\MessageNotificationModel;

class MsgNotificationDatamanager
{
    public function get($id)
    {
        return MessageNotificationModel::create()->where(['id'=> $id, 'delete_time'=> 0])->get();
    }

    public function find($where)
    {
        return MessageNotificationModel::create()->where($where)->where('delete_time', 0)->get();
    }

    public function save($data)
    {
        return MessageNotificationModel::create($data)->save();
    }

    public function update($data, $where)
    {
        $msg_notification = MessageNotificationModel::create();
        $res = $msg_notification->update($data, $where);
        if($res) {
            return $msg_notification->lastQueryResult()->getAffectedRows();
        }else{
            return false;
        }
    }

    public function delete($where)
    {
        return MessageNotificationModel::create()->destroy($where);
    }

    public function softDelete($where)
    {
        $msg_notification = MessageNotificationModel::create();
        $res = $msg_notification->update(['delete_time'=> time()], $where);
        if($res) {
            return $msg_notification->lastQueryResult()->getAffectedRows();
        }else{
            return false;
        }
    }

    public function select($where)
    {
        return MessageNotificationModel::create()->where($where)->where('delete_time', 0)->all();
    }

}
