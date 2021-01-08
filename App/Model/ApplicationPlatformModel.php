<?php
namespace App\Model;

use EasySwoole\ORM\AbstractModel;

class ApplicationPlatformModel extends AbstractModel
{
    protected $tableName  = 'sp_application_platform';
    protected $primaryKey = 'id';
    protected $autoTimeStamp = true;
    
}