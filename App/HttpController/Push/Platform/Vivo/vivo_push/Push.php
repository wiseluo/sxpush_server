<?php
namespace vivo_push;

use vivo_push\Http\Http;
use vivo_push\Http\Request;
use vivo_push\Http\Response;

class Push
{
    private $_authTokenInfo;
    private $_clientId;
    private $_clientKey;
    private $_clientSecret;
    private $_http;
    private $auth_token;
    private $auth_token_expires_time;
    private $title;
    private $content;
    private $notifyType;
    private $skipType;
    private $classification;
    private $skipContent;
    private $clientCustomMap;
    private $registration_id;

    //推送鉴权接口
    private $auth_url = 'https://api-push.vivo.com.cn/message/auth';
    //单推接口
    private $single_push_url = 'https://api-push.vivo.com.cn/message/send';
    //保存群推消息公共体接口
    private $save_message_content_url = 'https://api-push.vivo.com.cn/message/saveListPayload';
    //批量推送用户接口
    private $multi_push_url = 'https://api-push.vivo.com.cn/message/pushToList';

    public function __construct($client_id, $client_key, $client_secret)
    {
        $this->_clientId = $client_id;
        $this->_clientKey = $client_key;
        $this->_clientSecret = $client_secret;
        $this->_http = new Request();
        $this->_http->setHttpVersion(Http::HTTP_VERSION_1_1);
    }

    private function getAuthTokenInfo()
    {
        $timestamp = $this->getTimestamp();
        //$this->auth_token_expires_time = $timestamp;
        $data = [
            'appId' => $this->_clientId,
            'appKey' => $this->_clientKey,
            'timestamp' => $timestamp
        ];
        //$sign = md5(trim($this->_clientId.$this->_clientKey.$timestamp.$this->_clientSecret));
        $data['sign'] = $this->generateSign($data);
        
        $response = $this->_http->post($this->auth_url, array(
            'data' => json_encode($data),
            'headers' => [
                'Content-Type' => 'application/json;charset=utf-8'
            ],
        ));
        $this->_authTokenInfo = $response->getResponseArray();
        return $this->_authTokenInfo;
    }

    public function getAuthToken()
    {
        if(!$this->_authTokenInfo){
            $this->_authTokenInfo = $this->getAuthTokenInfo();
        }
        $auth_token = '';
        if(isset($this->_authTokenInfo['result']) && $this->_authTokenInfo['result'] == 0){
            $auth_token = $this->_authTokenInfo['authToken'];
        }
        if(!$auth_token){
            throw  new \Exception("获取 auth_token 失败:". $this->_authTokenInfo['desc']);
            return null;
        }
        return $auth_token;
    }

    public function getAuthTokenExpiresTime()
    {
        if(!$this->_authTokenInfo){
            $this->_authTokenInfo = $this->getAuthTokenInfo();
        }
        $expires_time = '';
        if(isset($this->_authTokenInfo['result']) && $this->_authTokenInfo['result'] == 0){
            $expires_time = floor($this->auth_token_expires_time / 1000) + 86400;
        }
        return $expires_time;
    }

    public function setNotifyType($notifyType = '')
    {
        $this->notifyType = $notifyType;
        return $this;
    }

    public function setSkipType($skipType = '')
    {
        $this->skipType = $skipType;
        return $this;
    }

    public function setClassification($classification = '')
    {
        $this->classification = $classification;
        return $this;
    }

    public function setTitle($title = '')
    {
        $this->title=$title;
        return $this;
    }

    public function setContent($content = '')
    {
        $this->content = $content;
        return $this;
    }

    public function setSkipContent($skipContent = '')
    {
        $this->skipContent = $skipContent;
        return $this;
    }

    public function setClientCustomMap($clientCustomMap = array())
    {
        $this->clientCustomMap = $clientCustomMap;
        return $this;
    }

    public function setAuthToken($auth_token="")
    {
        $this->auth_token = $auth_token;
        return $this;
    }

    public function addRegistrationId($registration_id = '')
    {
        $this->registration_id[] = $registration_id;
        return $this;
    }

    public function send()
    {
        if(empty($this->registration_id)){
            throw new \Exception("必须设置 registration_id");
        }
        $registration_id = array_unique($this->registration_id);
        if (count($registration_id) >= 2 && count($registration_id) <= 1000) {
            return $this->pushMultiNotify($registration_id);
        } else if (count($registration_id) == 1) {
            $registration_id = array_pop($registration_id);
            return $this->pushSingleNotify($registration_id);
        } else {
            throw new \Exception("vivo超过推送通道单次最大设备数:1000");
        }
    }

    private function build()
    {
        $data = array(
            'title' => $this->title,
            'content' => $this->content,
            'notifyType' => $this->notifyType,
            'skipType' => $this->skipType,
            'classification' => $this->classification,
            'requestId' => uniqid(),
        );
        
        if(!empty($this->skipType) && $this->skipType > 1){
            $data['skipContent'] = $this->skipContent;
        }
        
        if(is_array($this->clientCustomMap) && count($this->clientCustomMap) > 0){
            $data['clientCustomMap'] = $this->clientCustomMap;
        }
        
        return $data;
    }

    protected function pushSingleNotify($regId)
    {
        $data = $this->build();
        $data['regId'] = $regId;
        $response = $this->_http->post($this->single_push_url, array(
            'data' => json_encode($data),
            'headers' => [
                'authToken'=> $this->auth_token,
                'Content-Type' => 'application/json;charset=utf-8',
            ],
        ));
        
        return $response->getResponseArray();
    }

    protected function saveMessageToCloud()
    {
        $data = $this->build();
        $response = $this->_http->post($this->save_message_content_url, array(
            'data' => json_encode($data),
            'headers' => [
                'authToken'=> $this->auth_token,
                'Content-Type' => 'application/json;charset=utf-8',
            ],
        ));
        
        return $response->getResponseArray();
    }

    protected function pushMultiNotify($regIds)
    {
        $msg_data = $this->saveMessageToCloud();
        if(empty($msg_data) || $msg_data['result'] != 0){
            return $msg_data;
        }

        $data = [
            'regIds' => $regIds,
            'taskId' => $msg_data['taskId'],
            'requestId' => uniqid(),
        ];
        $response = $this->_http->post($this->multi_push_url, array(
            'data' => json_encode($data),
            'headers' => [
                'authToken'=> $this->auth_token,
                'Content-Type' => 'application/json;charset=utf-8',
            ],
        ));
        
        return $response->getResponseArray();
    }

    protected function getTimestamp()
    {
        list($msec, $sec) = explode(' ', microtime());
        return (float)sprintf('%.0f', (floatval($msec) + floatval($sec)) * 1000);
    }

    protected function generateSign($data)
    {
        $strToSign = implode('',[
            $this->_clientId,
            $this->_clientKey,
            $data['timestamp'],
            $this->_clientSecret
        ]);
        return bin2hex(hash('md5', $strToSign, true));
    }

}