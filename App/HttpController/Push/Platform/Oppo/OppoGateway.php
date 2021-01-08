<?php
namespace App\HttpController\Push\Platform\Oppo;

use App\HttpController\Push\Platform\Gateway;

use oppo_push\Push;

include_once(dirname(__FILE__). '/autoload.php');

class OppoGateway extends Gateway
{
    public function push($token, $notification, $options)
    {
        try {
            // AppKey 与 MasterSecret(非AppSecret为appserversecret，注册应用时生成)
            $client = new Push($this->config->get('appkey'), $this->config->get('appsecret'));
            $authToken = $client->getAuthToken(); // 有效期24小时
            $client->setTitle($notification['title'])
                   ->setContent($notification['alert'])
                   ->setAuthToken($authToken);
            foreach($token as $k => $v) {
                $client->addRegistrationId($v);            // 添加需要发送设备的 registration_id, 最多 1000 个
            }

            // $client->getAuthTokenExpiresTime();           // 获取 auth_token 过期时间
            // $client->setIntent('xxx.xxx.xxx');           // 打开应用内页的 intent action
            // $client->setActionUrl('http://www.xxx.com');  // 打开网页

            // $client->setActionParameters({Parameters});   // 打开应用内页或网页时传递的参数 (数组或json类型)

            if(isset($notification['extras']) && count($notification['extras']) > 0) {
                $client->setActionParameters(json_encode($notification['extras']));
            }

            if(isset($notification['click_action_activity']) && $notification['click_action_activity'] != '') {
                $client->setIntent($notification['click_action_activity']);
            }
            if(isset($notification['click_action_action']) && $notification['click_action_action'] != '') {
                $client->setIntent($notification['click_action_action']);
            }
            if(isset($notification['click_action_url']) && $notification['click_action_url'] != '') {
                $client->setActionUrl($notification['click_action_url']);
            }
            $result = $client->send(); // registration_id 推送
            var_dump($result);
            /*
            array(3) {
                ["code"]=>
                int(0)
                ["data"]=>
                array(4) {
                    [10000]=>
                    array(1) {
                        [0]=>
                        string(6) "222333"
                    }
                    ["task_id"]=>
                    string(24) "5f87f36d715e082216621216" // taskId
                    ["message_id"]=>
                    string(36) "3694411-1-3-5f87f36da8e7aa9e3384a907" // 消息Id
                    ["status"]=>
                    string(12) "call_success"
                }
                ["message"]=>
                string(7) "Success"
            }
            */
            if($result['code'] == 0) {
                return ['status' => 1, 'msg' => 'oppo success'];
            }else{
                return ['status' => 0, 'msg' => 'oppo '. $result['message']];
            }
        }catch(\Exception $e) {
            var_dump($e->getMessage());
            return ['status' => 0, 'msg' => 'oppo '. $e->getMessage()];
        }
    }

}