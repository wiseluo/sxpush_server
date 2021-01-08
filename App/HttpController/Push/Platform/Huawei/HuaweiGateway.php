<?php
namespace App\HttpController\Push\Platform\Huawei;

include_once (dirname(__FILE__) . '/push_admin/Application.php');

use App\HttpController\Push\Platform\Gateway;

use push_admin\Application;

class HuaweiGateway extends Gateway
{
    ### Token Server for push msg and top subscribe/unsubscribe 令牌服务器####
    private $hw_token_server = "https://oauth-login.cloud.huawei.com/oauth2/v2/token";
    ### Push Server address 推送服务器####
    private $hw_push_server = "https://push-api.cloud.huawei.com/v1/{appid}/messages:send";

    public function push($token, $notification, $options)
    {
        // $message = 
        //     '{
        //         "data": '. json_encode($notification['extras']) .',
        //         "android": {
        //             "notification": {
        //                 "title": " '. $notification['title'] .' ",
        //                 "body": " '. $notification['alert'] .' ",
        //                 "color": "#AACCDD",
        //                 "click_action": {
        //                     "type": 3
                            
        //                 }
                
        //             }
        //         },
        //         "token": '. json_encode($token) .'
        //     }';
        $message = [
            'android' => [
                'notification' => [
                    'title' => $notification['title'],
                    'body' => $notification['alert'],
                    'color' => "#AACCDD",
                    'channel_id' => "996",
                    'badge' => [  //通知消息角标控制
                        'add_num' => count($token),   //应用角标累加数字非应用角标实际显示数字，为大于0小于100的整数
                        'class' => $this->config->get('activity_class'),  //应用入口Activity类全路径
                        //'set_num' => 1, //角标设置数字，大于等于0小于100的整数。如果set_num与add_num同时存在时，以set_num为准
                    ],
                    'click_action' => [
                        'type' => 3,  //点击后打开应用App
                    ],
                ],
            ],
            'token' => $token
        ];
        //自定义消息负载，支持普通字符串或者JSON格式字符串。样例："your data"，"{'param1':'value1','param2':'value2'}"。
        if(isset($notification['extras']) && count($notification['extras']) > 0) {
            $message['data'] = json_encode($notification['extras']);
        }
        //自定义页面中intent的实现，请参见指定intent参数​。当type为1时，字段intent和action至少二选一
        if(isset($notification['click_action_activity']) && $notification['click_action_activity'] != '') {
            $message['android']['notification']['click_action'] = [
                'type' => 1,
                'intent' => $notification['click_action_activity'],
            ];
        }
        //设置通过action打开应用自定义页面时，本字段填写要打开的页面activity对应的action。当type为1时，字段intent和action至少二选一。
        if(isset($notification['click_action_action']) && $notification['click_action_action'] != '') {
            $message['android']['notification']['click_action'] = [
                'type' => 1,
                'action' => $notification['click_action_action'],
            ];
        }
        //设置打开特定URL，本字段填写需要打开的URL，URL使用的协议必须是HTTPS协议，取值样例：https://example.com/image.png。
        //当type为2时必选。如果是游戏类应用，不支持设置特定URL。
        if(isset($notification['click_action_url']) && $notification['click_action_url'] != '') {
            $message['android']['notification']['click_action'] = [
                'type' => 2,
                'url' => $notification['click_action_url'],
            ];
        }
        $application = new Application($this->config->get('appid'), $this->config->get('appsecret'), $this->hw_token_server, $this->hw_push_server);
        //$application->validate_only(true); //测试消息
        $result = $application->push_send_msg($message);
        var_dump($result);
        /*
        object(stdClass)#165 (3) {
          ["code"]=>
          string(8) "80000000"
          ["msg"]=>
          string(7) "Success"
          ["requestId"]=>
          string(24) "160222347104120774003201"
        }
        */
        if($result == null) {
            return ['status' => 0, 'msg' => "huawei accesstoken is empty!"];
        }else if($result->code == 80000000) {
            return ['status' => 1, 'msg' => 'huawei success'];
        }else{
            return ['status' => 0, 'msg' => 'huawei '. $result->msg];
        }
    }

}