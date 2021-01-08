<?php
namespace App\HttpController\Application\Service;

use EasySwoole\Component\Di;
use App\HttpController\Application\Datamanager\ApplicationDatamanager;
use App\HttpController\Application\Datamanager\ApplicationPlatformDatamanager;

class BaseService
{
    public function __construct()
    {
        Di::getInstance()->set('ApplicationDatamanager', ApplicationDatamanager::class);
        Di::getInstance()->set('ApplicationPlatformDatamanager', ApplicationPlatformDatamanager::class);
    }

    public function __get($name)
    {
        return Di::getInstance()->get($name);
    }
}