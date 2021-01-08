<?php
namespace App\HttpController\User\Service;

use EasySwoole\Component\Di;
use App\HttpController\User\Datamanager\UserDatamanager;

class BaseService
{
    public function __construct()
    {
        Di::getInstance()->set('UserDatamanager', UserDatamanager::class);
    }

    public function __get($name)
    {
        return Di::getInstance()->get($name);
    }
}