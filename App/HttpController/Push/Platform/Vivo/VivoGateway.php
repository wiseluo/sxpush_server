<?php
namespace App\HttpController\Push\Platform\Vivo;

use App\HttpController\Push\Platform\Gateway;

use vivo_push\Push;

include_once(dirname(__FILE__). '/autoload.php');

class VivoGateway extends Gateway
{
    public function push($token, $notification, $options)
    {
        try {
            $client = new Push($this->config->get('appid'), $this->config->get('appkey'), $this->config->get('appsecret'));
            $authToken = $client->getAuthToken(); // 有效期24小时
            $client->setTitle($notification['title'])
                   ->setContent($notification['alert'])
                   ->setNotifyType(1) //通知类型 1:无，2:响铃，3:振动，4:响铃和振动
                   ->setSkipType(1) //点击跳转类型 1：打开APP首页 2：打开链接 3：自定义 4:打开app内指定页面
                   ->setClassification(1) //消息类型 0：运营类消息，1：系统类消息。不填默认为0
                   ->setAuthToken($authToken);
            foreach($token as $k => $v) {
                $client->addRegistrationId($v);            // 添加需要发送设备的 registration_id, 最多 1000 个
            }

            // $client->getAuthTokenExpiresTime();           // 获取 auth_token 过期时间
            // $client->setSkipContent('http://www.xxx.com'); // 跳转内容,跳转类型为2时，跳转内容最大1000个字符，跳转类型为3或4时，跳转内容最大1024个字符

            if(isset($notification['extras']) && count($notification['extras']) > 0) {
                $client->setClientCustomMap($notification['extras']); // 客户端自定义键值对 (数组类型)
            }

            if(isset($notification['click_action_activity']) && $notification['click_action_activity'] != '') {
                $client->setSkipType(4);
                $client->setSkipContent($notification['click_action_activity']);
            }
            if(isset($notification['click_action_action']) && $notification['click_action_action'] != '') {
                $client->setSkipType(4);
                $client->setSkipContent($notification['click_action_action']);
            }
            if(isset($notification['click_action_url']) && $notification['click_action_url'] != '') {
                $client->setSkipType(2);
                $client->setSkipContent($notification['click_action_url']);
            }

            $result = $client->send();                  // registration_id 推送
            var_dump($result);
            /*
            array(2) {
                ["result"]=>
                int(10302)
                ["desc"]=>
                string(14) "regId不合法"
            }
            {
                "requestId":       "25509283-3767-4b9e-83fe-b6e55ac6b123",
                "result": 0,
                "desc": "请求成功",
                "invalidUsers": [
                    {
                        "status": 1,
                        "userid": "12345678901234567890122"
                    },
                    {
                        "status": 1,
                        "userid":     "15554239157791000000009"
                    }
                ]
            }
            */
            if($result['result'] == 0) {
                return ['status' => 1, 'msg' => 'vivo success'];
            }else{
                return ['status' => 0, 'msg' => 'vivo '. $result['desc']];
            }
        }catch(\Exception $e) {
            var_dump($e->getMessage());
            return ['status' => 0, 'msg' => 'vivo '. $e->getMessage()];
        }
    }

}