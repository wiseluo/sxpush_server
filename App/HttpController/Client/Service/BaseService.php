<?php
namespace App\HttpController\Client\Service;

use EasySwoole\Component\Di;
use App\HttpController\Client\Datamanager\ClientDatamanager;
use App\HttpController\Application\Datamanager\ApplicationPlatformDatamanager;

class BaseService
{
    public function __construct()
    {
        Di::getInstance()->set('ClientDatamanager', ClientDatamanager::class);
        Di::getInstance()->set('ApplicationPlatformDatamanager', ApplicationPlatformDatamanager::class);
    }

    public function __get($name)
    {
        return Di::getInstance()->get($name);
    }
}