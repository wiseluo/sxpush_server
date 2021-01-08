<?php
namespace App\HttpController\Push\Validate;

use EasySwoole\Component\Singleton;
use EasySwoole\Validate\Validate;

class PushValidate
{
    use Singleton;
    
    protected function validateRule(?string $action): ?Validate
    {
        $v = new Validate();
        switch ($action) {
            case 'push':
                $v->addColumn('platform', '推送平台设置')->required('不能为空');
                $v->addColumn('audience', '推送设备指定')->required('不能为空');
                $v->addColumn('notification', '通知内容体')->required('不能为空');
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