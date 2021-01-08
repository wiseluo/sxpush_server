<?php

namespace App\Utility;

use EasySwoole\Jwt\Jwt;
use EasySwoole\Component\Singleton;

class JWTManager
{
    use Singleton;

    private $secret_key = '478bc4979656985b9274d30c537c5990'; // md5('sxpush')

    public function encode($user)
    {
        $jwtObject = Jwt::getInstance()
            ->setSecretKey($this->secret_key) // 秘钥
            ->publish();

        $jwtObject->setAlg('HMACSHA256'); // 加密方式
        $jwtObject->setAud('user'); // 用户
        $jwtObject->setExp(time()+86400); // 过期时间 一天
        $jwtObject->setIat(time()); // 发布时间
        $jwtObject->setIss('sxpush'); // 发行人
        $jwtObject->setJti(md5(time())); // jwt id 用于标识该jwt
        $jwtObject->setNbf(time()+60*5); // 在此之前不可用
        $jwtObject->setSub('主题'); // 主题

        // 自定义数据
        $data = [
            "id" => $user['id'],
            'phone' => $user['phone'],
            'username' => $user['username'],
        ];
        $jwtObject->setData($data);

        // 最终生成的token
        return $jwtObject->__toString();
    }

    public function decode(string $token)
    {
        return Jwt::getInstance()->setSecretKey($this->secret_key)->decode($token);
    }

}