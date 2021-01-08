<?php
namespace App\Model;

use EasySwoole\ORM\AbstractModel;

class ApplicationModel extends AbstractModel
{
    protected $tableName  = 'sp_application';
    protected $primaryKey = 'id';
    protected $autoTimeStamp = true;
    
}