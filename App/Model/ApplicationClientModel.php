<?php
namespace App\Model;

use EasySwoole\ORM\AbstractModel;

class ApplicationClientModel extends AbstractModel
{
    protected $tableName  = 'sp_application_client';
    protected $primaryKey = 'id';
    protected $autoTimeStamp = true;
    
}