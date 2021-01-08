<?php
return [
    'SERVER_NAME' => "sxpush_produce",
    'MAIN_SERVER' => [
        'LISTEN_ADDRESS' => '0.0.0.0',
        'PORT' => 9501,
        'SERVER_TYPE' => EASYSWOOLE_WEB_SERVER, //可选为 EASYSWOOLE_SERVER  EASYSWOOLE_WEB_SERVER EASYSWOOLE_WEB_SOCKET_SERVER,EASYSWOOLE_REDIS_SERVER
        'SOCK_TYPE' => SWOOLE_TCP,
        'RUN_MODEL' => SWOOLE_PROCESS,
        'SETTING' => [
            'worker_num' => 8,
            'reload_async' => true,
            'max_wait_time'=>3
        ],
        'TASK'=>[
            'workerNum'=>4,
            'maxRunningNum'=>128,
            'timeout'=>15
        ]
    ],
    'TEMP_DIR' => '/tmp/sxpush_produce/',
    'LOG_DIR' => null,

    'MYSQL' => [
        'host' => '127.0.0.1',
        'port' => '3306',
        'user' => 'root',//数据库用户名
        'password' => '123456',//数据库密码
        'database' => 'sxpush',//数据库
        'timeout' => '5',
        'charset' => 'utf8',
        'POOL_MAX_NUM' => '6',
        'POOL_TIME_OUT' => '0.1'
    ],

    /*################ REDIS CONFIG ##################*/
    'REDIS'         => [
        'host'          => '127.0.0.1',
        'port'          => '6379',
        'auth'          => '',
        'POOL_MAX_NUM'  => '6',
        'POOL_TIME_OUT' => '0.1',
    ],

];
