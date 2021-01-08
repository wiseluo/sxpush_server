<?php
namespace App\HttpController\Application\Validate;

use EasySwoole\Component\Singleton;
use EasySwoole\Validate\Validate;

class ApplicationValidate
{
    use Singleton;
    
    protected function validateRule(?string $action): ?Validate
    {
        $v = new Validate();
        switch ($action) {
            case 'save':
                $v->addColumn('name', '应用名称')->required('不能为空')->lengthMax(30, '最大长度不大于30位');
                break;
            case 'update':
                $v->addColumn('name', '应用名称')->required('不能为空')->lengthMax(30, '最大长度不大于30位');
                break;
            case 'resetSxappserect':
                $v->addColumn('sxappkey', 'sxappkey')->required('不能为空');
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