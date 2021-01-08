<?php
namespace App\HttpController\Push\Platform\Xiaomi;

use App\HttpController\Push\Platform\Gateway;

use xmpush\Builder;
use xmpush\HttpBase;
use xmpush\Sender;
use xmpush\Constants;
use xmpush\Stats;
use xmpush\Tracer;
use xmpush\Feedback;
use xmpush\DevTools;
use xmpush\Subscription;
use xmpush\TargetedMessage;
use xmpush\Region;

include_once(dirname(__FILE__) . '/autoload.php');

class XiaomiGateway extends Gateway
{
    public function push($regIdList, $notification, $options)
    {
        // 常量设置必须在new Sender()方法之前调用
        Constants::setPackage($this->config->get('package_name')); //your app packagename
        Constants::setSecret($this->config->get('appsecret')); //your app secret

        //$payload = '{"test":1,"ok":"It\'s a string"}';

        $sender = new Sender();

        $message = new Builder();
        $message->title($notification['title']);  // 通知栏的title
        $message->description($notification['alert']); // 通知栏的descption
        $message->passThrough(0);  // 这是一条通知栏消息，如果需要透传，把这个参数设置成1,同时去掉title和descption两个参数

        //设置要发送的消息内容, 不允许全是空白字符（透传消息回传给app, 为必填字段, 非透传可选）
        //携带的数据，点击后将会通过客户端的receiver中的onReceiveMessage方法传入。
        //对于预定义点击行为，payload会通过点击进入的界面的intent中的extra字段获取，而不会调用到onReceiveMessage方法。
        if(isset($notification['extras']) && count($notification['extras']) > 0) {
            $message->payload(json_encode($notification['extras']));
        }
        if(isset($notification['click_action_activity']) && $notification['click_action_activity'] != '') {
            $message->extra(Builder::notifyEffect, 2);
            $message->extra(Builder::intentUri, $notification['click_action_activity']);
        }
        if(isset($notification['click_action_action']) && $notification['click_action_action'] != '') {
            $message->extra(Builder::notifyEffect, 2);
            $message->extra(Builder::intentUri, $notification['click_action_action']);
        }
        if(isset($notification['click_action_url']) && $notification['click_action_url'] != '') {
            $message->extra(Builder::notifyEffect, 3);
            $message->extra(Builder::webUri, $notification['click_action_url']);
        }
        //$message->extra(Builder::notifyEffect, 1); // 此处设置预定义点击行为，1为打开app
        $message->extra(Builder::notifyForeground, 1); // 应用在前台是否展示通知，如果不希望应用在前台时候弹出通知，则设置这个参数为0
        $message->notifyId(0); // 通知类型。最多支持0-4 5个取值范围，同样的类型的通知会互相覆盖，不同类型可以在通知栏并存
        $message->build();
        if(count($regIdList) == 1) {
            $result = $sender->send($message, $regIdList[0])->getRaw();
        }else{
            $result = $sender->sendToIds($message, $regIdList)->getRaw();
        }
        var_dump($result);
        /*
        array(5) {
            ["result"]=>
            string(5) "error"
            ["reason"]=>
            string(17) "No valid targets!"
            ["trace_id"]=>
            string(22) "Xcm58061602298211208Nt"
            ["code"]=>
            int(20301)
            ["description"]=>
            string(18) "发送消息失败"
        }
        array(6) {
            ["result"]=>
            string(2) "ok"
            ["trace_id"]=>
            string(22) "Xdm57188602565449242Ut"
            ["code"]=>
            int(0)
            ["data"]=>
            array(3) {
                ["day_acked"]=>
                string(1) "0"
                ["id"]=>
                string(22) "sdm57188602565449273KF"
                ["day_quota"]=>
                string(5) "50000"
            }
            ["description"]=>
            string(6) "成功"
            ["info"]=>
            string(34) "Received push messages for 1 REGID"
        }
        */
        if($result['code'] == 0) {
            return ['status' => 1, 'msg' => 'success'];
        }else{
            return ['status' => 0, 'msg' => $result['description'] .':'. $result['reason']];
        }
    }

}