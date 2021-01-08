<?php
namespace App\HttpController;

use EasySwoole\Http\AbstractInterface\AbstractRouter;
use FastRoute\RouteCollector;
use EasySwoole\Http\Request;
use EasySwoole\Http\Response;

class Router extends AbstractRouter
{
    function initialize(RouteCollector $routeCollector)
    {
        /* 开发者网站接口 */
        $routeCollector->get('/web/get_unid', '/Common/CommonController/getUnid'); // 获取unid
        $routeCollector->get('/web/verify_code', '/Common/VerifyCodeController/verifyCode'); // 图形验证码
        $routeCollector->post('/web/send_sms_code', '/Common/SmsCodeController/sendSmsCode'); // 发送短信验证码
        // 上传
        $routeCollector->post('/web/app/upload/ios_cert','/Common/Upload/iosCert'); // 上传ios证书

        //开发者账号
        $routeCollector->post('/web/user/register', '/User/Controller/UserController/register');
        $routeCollector->post('/web/user/login', '/User/Controller/UserController/login');
        $routeCollector->post('/web/user/reset_password', '/User/Controller/UserController/resetPassword');
        $routeCollector->post('/web/user/logout', '/User/Controller/UserController/logout');

        $routeCollector->get('/web/user/info', '/User/Controller/UserController/info');

        // 开发者应用
        $routeCollector->get('/web/application', '/Application/Controller/ApplicationController/index');
        $routeCollector->get('/web/application/{id:\d+}', '/Application/Controller/ApplicationController/read');
        $routeCollector->post('/web/application', '/Application/Controller/ApplicationController/save');
        $routeCollector->put('/web/application/{id:\d+}', '/Application/Controller/ApplicationController/update');
        $routeCollector->delete('/web/application/{id:\d+}', '/Application/Controller/ApplicationController/delete');
        $routeCollector->post('/web/application/reset_sxappsecret', '/Application/Controller/ApplicationController/resetSxappserect'); //重置秘钥

        //开发者应用平台设置
        $routeCollector->get('/web/application_platform/{id:\d+}', '/Application/Controller/ApplicationPlatformController/read');
        $routeCollector->post('/web/application_platform/android', '/Application/Controller/ApplicationPlatformController/saveAndroid');
        $routeCollector->post('/web/application_platform/ios', '/Application/Controller/ApplicationPlatformController/saveIos');


        /* 客户端接口 */
        $routeCollector->post('/client/register_push', '/Client/controller/ClientController/registerPush'); //客户端注册推送
        $routeCollector->post('/client/unregister_push', '/Client/controller/ClientController/unregisterPush'); //客户端注销推送
        $routeCollector->post('/client/report_push_token', '/Client/controller/ClientController/reportPushToken'); //客户端上报平台推送token

        /* 开发者服务器推送接口 */
        $routeCollector->post('/push', '/Push/PushController/push');

        // test
        $routeCollector->get('/test/{id:\d+}', function (Request $request, Response $response) {
            $response->write("this is router test ,your id is {$request->getQueryParam('id')}");//获取到路由匹配的id
            return false;//不再往下请求,结束此次响应
        });
    }
}