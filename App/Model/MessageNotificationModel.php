<?php
namespace App\Model;

use EasySwoole\ORM\AbstractModel;

class MessageNotificationModel extends AbstractModel
{
    protected $tableName  = 'sp_message_notification';
    protected $primaryKey = 'id';
    protected $autoTimeStamp = true;
    
}