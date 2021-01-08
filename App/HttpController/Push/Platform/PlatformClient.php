<?php
namespace App\HttpController\Push\Platform;

use EasySwoole\Component\Di;

use App\HttpController\Application\Datamanager\ApplicationPlatformDatamanager;
use App\HttpController\Client\Datamanager\ClientDatamanager;
use App\HttpController\Push\Datamanager\MsgNotificationDatamanager;

class PlatformClient
{
    protected $gatewayConfig = [
        'ios' => [
            'ios_cert_sandbox' => '', // pem格式推送开发版证书本地绝对路径
            'ios_cert_production' => '', // pem格式推送发布版证书本地绝对路径
        ],
        'huawei' => [
            'appid' => '',
            'appsecret' => '',
            'activity_class' => '',
        ],
        'xiaomi' => [
            'package_name' => '', //包名
            'appsecret' => ''
        ],
        'oppo' => [
            'appkey' => '',
            'appsecret' => ''
        ],
        'vivo' => [
            'appid' => '',
            'appkey' => '',
            'appsecret' => ''
        ],
    ];

    private static $effective_device_types = array('ios', 'android');
    private static $android_types = array('huawei', 'xiaomi', 'oppo', 'vivo');

    public function __construct()
    {
        Di::getInstance()->set('ApplicationPlatformDatamanager', ApplicationPlatformDatamanager::class);
        Di::getInstance()->set('ClientDatamanager', ClientDatamanager::class);
        Di::getInstance()->set('MsgNotificationDatamanager', MsgNotificationDatamanager::class);
    }

    public function saveMsgNotification($sxappkey, $data)
    {
        $msg_data = [
            'status' => 1,
            'sxappkey' => $sxappkey,
            'title' => $data['notification']['android']['title'] ?: $data['notification']['ios']['title'],
            'body' => $data['notification']['android']['alert'] ?: $data['notification']['ios']['alert'],
            'num' => count($data['audience']['registration_id']),
        ];
        $res = Di::getInstance()->get('MsgNotificationDatamanager')->save($msg_data);
        if($res) {
            return ['status' => 1, 'msg' => '保存通知消息成功'];
        }else{
            return ['status' => 0, 'msg' => '保存通知消息失败'];
        }
    }

    public function getPlatform($sxappkey, $platform_type)
    {
        $platform = Di::getInstance()->get('ApplicationPlatformDatamanager')->find(['sxappkey'=> $sxappkey, 'platform'=> $platform_type]);
        if($platform == null) {
            return ['status' => 0, 'msg' => '应用平台未设置;'];
        }else{
            return ['status' => 1, 'data' => $platform];
        }
    }

    //根据注册id取推送token
    public function getTokenByregistrationId($registration_ids)
    {
        $application_client = Di::getInstance()->get('ClientDatamanager')->select(['registration_id'=> [$registration_ids, 'in']]);
        if(count($application_client) == 0) {
            return ['status' => 0, 'msg' => '未绑定注册id;'];
        }
        $token = [];
        foreach($application_client as $k => $v) {
            if($v['platform_token']) {
                $token[] = [
                    'platform'=> $v['platform'], //平台：'ios', 'huawei', 'xiaomi', 'oppo', 'vivo'
                    'platform_token'=> $v['platform_token'],
                ];
            }
        }
        if(count($token) == 0) {
            return ['status' => 0, 'msg' => '未上报推送平台token;'];
        }
        return ['status' => 1, 'data' => $token];
    }

    /*
     * 向各平台发送推送 
     * param  sxappkey：商翔应用appkey  data：推送数据
     * 
     */
    public function send($sxappkey, $data)
    {
        $msg_res = $this->saveMsgNotification($sxappkey, $data);
        if($msg_res['status'] == 0) {
            return ['status' => 0, 'msg' => $msg_res['msg']];
        }
        $token_res = $this->getTokenByregistrationId($data['audience']['registration_id']);
        if($token_res['status'] == 0) {
            return ['status' => 0, 'msg' => $token_res['msg']];
        }
        $token = $token_res['data'];
        //分平台token
        $gateway = [];
        foreach($token as $k => $v) {
            if(array_key_exists($v['platform'], $gateway)) {
                $gateway[$v['platform']][] = $v['platform_token'];
            }else{
                $gateway[$v['platform']] = [$v['platform_token']];
            }
        }

        //分平台推送
        $msg = '';
        foreach($gateway as $m => $n) {
            if($m == 'ios') {
                $msg .= $this->createGateway($sxappkey, 'ios', $n, $data['notification']['ios'], $data['options']);
            }else{
                $msg .= $this->createGateway($sxappkey, $m, $n, $data['notification']['android'], $data['options']);
            }
        }
        return ['status' => 1, 'msg' => $msg];
    }

    public function createGateway($sxappkey, $platform_type, $token, $notification, $options)
    {
        $platform_res = $this->getPlatform($sxappkey, $platform_type);
        if($platform_res['status'] == 0) {
            return $platform_type .' push fail-'. $platform_res['msg'];
        }
        $platform = $platform_res['data'];
        //设置平台配置参数
        $gatewayConfig = [];
        foreach($this->gatewayConfig[$platform_type] as $k => $v) {
            $gatewayConfig[$k] = $platform[$k];
        }

        $className = $this->formatGatewayClassName($platform_type);
        $gateway = $this->makeGateway($className, $gatewayConfig);
        $res = $gateway->push($token, $notification, $options);
        if($res['status']) {
            return $platform_type .' push seccess;';
        }else{
            return $platform_type .' push fail-'. $res['msg'] .';';
        }
    }

    protected function formatGatewayClassName($name)
    {
        $gateway = ucfirst($name);
        return __NAMESPACE__ ."\\". $gateway ."\\{$gateway}Gateway";
    }

    protected function makeGateway($gateway, $gatewayConfig)
    {
        if (!class_exists($gateway)) {
            throw new \Exception(sprintf('Gateway "%s" not exists.', $gateway));
        }
        return new $gateway($gatewayConfig);
    }

}
