<?php

// +----------------------------------------------------------------------
// | Workerman设置 仅对 php think worker:server 指令有效
// +----------------------------------------------------------------------
return [
    // 扩展自身需要的配置
    'protocol'       => 'http', // 协议 支持 tcp udp unix http websocket text
    'host'           => '127.0.0.1', // 监听地址
    'port'           => 7777, // 监听端口
    'socket'         => '', // 完整监听地址
    'context'        => [], // socket 上下文选项
    'worker_class'   => '', // 自定义Workerman服务类名 支持数组定义多个服务

    // 支持workerman的所有配置参数
    'name'           => 'spider',
    'count'          => 1,
    'daemonize'      => false,
    'pidFile'        => env('runtime_path') . 'worker.pid',

    // 支持事件回调
    // onWorkerStart
    'onWorkerStart'  => function ($worker) {
        app\worker\Spider::spider();
    },
    // onWorkerReload
    'onWorkerReload' => function ($worker) {

    },
    // onConnect
    'onConnect'      => function ($connection) {

    },
    // onMessage
    'onMessage'      => function ($connection, $data) {
        $connection->send('receive success');
    },
    // onClose
    'onClose'        => function ($connection) {

    },
    // onError
    'onError'        => function ($connection, $code, $msg) {
        echo "error [ $code ] $msg\n";
    },
];
