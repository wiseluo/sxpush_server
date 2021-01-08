<?php
namespace App\HttpController\Push;

use App\HttpController\PushApiBase;
use App\HttpController\Push\Payload\PushClient;
use App\HttpController\Push\Platform\PlatformClient;
use App\HttpController\Push\Validate\PushValidate;

class PushController extends PushApiBase
{
    public function push()
    {
        try {
            //验证并重组数据
            //$param = $this->request()->getRequestParam();
            $content = $this->request()->getBody()->__toString(); //获取以非form-data或x-www-form-urlenceded编码格式POST提交的原始数据(json)
            $param = json_decode($content, true);
            if($param == null) {
                return $this->writeJson(400, '参数为空');
            }
            $validate_ret = PushValidate::getInstance()->check('push', $param);
            if($validate_ret !== true) {
                return $this->writeJson(400, $validate_ret);
            }
            $push_client = new PushClient();
            $push_payload = $push_client->push()
                ->setPlatform($param['platform']);
            if(isset($param['audience']) && isset($param['audience']['registration_id'])) {
                $push_payload->addRegistrationId($param['audience']['registration_id']);
            }
            if(isset($param['notification']) && isset($param['notification']['ios']) && isset($param['notification']['ios']['alert'])) {
                $push_payload->iosNotification($param['notification']['ios']['alert'], $param['notification']['ios']);
            }
            if(isset($param['notification']) && isset($param['notification']['android']) && isset($param['notification']['android']['alert'])) {
                $push_payload->androidNotification($param['notification']['android']['alert'], $param['notification']['android']);
            }
            if(isset($param['options']) && is_array($param['options'])) {
                $push_payload->options($param['options']);
            }
            $response = $push_payload->build();
            //var_dump($response);
            $platform_client = new PlatformClient();
            $res = $platform_client->send($this->push_request_sxappkey, $response);
            if($res['status']) {
                return $this->writeJson(200, $res['msg']);
            }else{
                return $this->writeJson(400, $res['msg']);
            }
        } catch (\Exception $e) {
            return $this->writeJson(400, $e->getMessage());
        }

    }

}
