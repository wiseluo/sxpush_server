<?php
namespace App\Model;

use EasySwoole\ORM\AbstractModel;

class UserModel extends AbstractModel
{
    protected $tableName  = 'sp_user';
    protected $primaryKey = 'id';
    protected $autoTimeStamp = true;
    
}