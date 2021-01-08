<?php
namespace App\HttpController\Client\Validate;

use EasySwoole\Component\Singleton;
use EasySwoole\Validate\Validate;

class ClientValidate
{
    use Singleton;
    
    protected function validateRule(?string $action): ?Validate
    {
        $v = new Validate();
        switch ($action) {
            case 'registerPush':
                $v->addColumn('aaid', '匿名设备标识')->required('不能为空');
                $v->addColumn('package_name', '应用包名')->required('不能为空');
                $v->addColumn('platform', '应用平台类型')->required('不能为空')->inArray(['ios','huawei','xiaomi','oppo','vivo']);
                $v->addColumn('timestamp', '时间戳')->required('不能为空')->length(13,'长度错误');
                break;
            case 'unregisterPush':
                $v->addColumn('registration_id', 'registration_id')->required('不能为空');
                $v->addColumn('timestamp', '时间戳')->required('不能为空')->length(13,'长度错误');
                break;
            case 'reportPushToken':
                $v->addColumn('registration_id', 'registration_id')->required('不能为空');
                $v->addColumn('platform_token', '应用平台token')->required('不能为空');
                $v->addColumn('timestamp', '时间戳')->required('不能为空')->length(13,'长度错误');
                break;
        }
        return $v;
    }

    public function check(?string $action, $param)
    {
        $v = $this->validateRule($action);
        $ret = $v->validate($param);
        return $ret ? true : "{$v->getError()->getField()}@{$v->getError()->getFieldAlias()}:{$v->getError()->getErrorRuleMsg()}";
    }
}