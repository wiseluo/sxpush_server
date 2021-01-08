<?php
namespace App\HttpController\Application\Validate;

use EasySwoole\Component\Singleton;
use EasySwoole\Validate\Validate;

class ApplicationPlatformValidate
{
    use Singleton;
    
    protected function validateRule(?string $action): ?Validate
    {
        $v = new Validate();
        switch ($action) {
            case 'saveAndroid':
                $v->addColumn('application_id', '应用id')->required('不能为空')->integer('必须是整数');
                $v->addColumn('platform', '应用平台类型')->required('不能为空')->inArray(['huawei','xiaomi','oppo','vivo']);
                $v->addColumn('package_name', '应用包名')->required('不能为空')->lengthMin(4, '最小大长度不小于4位')->lengthMax(64, '最大长度不大于64位');
                $v->addColumn('app_type', '应用类型')->required('不能为空')->inArray([1,2]);
                $v->addColumn('appid', '应用appid')->required('不能为空');
                $v->addColumn('appkey', '应用appkey')->required('不能为空');
                $v->addColumn('appsecret', '应用appsecret')->required('不能为空');
                $v->addColumn('activity_class', '应用入口Activity类全路径')->required('不能为空');
                break;
            case 'saveIos':
                $v->addColumn('application_id', '应用id')->required('不能为空')->integer('必须是整数');
                $v->addColumn('appid', 'Bundle ID')->required('不能为空');
                // $v->addColumn('apns_auth_key', 'APNs身份验证密钥')->required('不能为空');
                // $v->addColumn('apns_auth_key_id', '秘钥id')->required('不能为空');
                // $v->addColumn('team_id', '团队id')->required('不能为空');
                $v->addColumn('ios_cert_sandbox', 'ios开发版证书')->required('不能为空');
                $v->addColumn('ios_cert_production', 'ios正式版证书')->required('不能为空');
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