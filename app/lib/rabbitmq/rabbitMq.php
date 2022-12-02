<?php

namespace app\lib\rabbitmq;

use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

/**
 * Class rabbitMq
 * @package common\utils\sync
 */
class rabbitMq
{
    /**
     * 推送数据
     * @param string $queueName 队列名字
     * @param mixed  $message   消息内容
     * @return array
     */
    public function send($queueName, $message = '')
    {
        $config   = config('rabbitmq');
        $exchange = $queueName . '_exchange';
        $routeKey = $queueName . '_route';
        if (empty($config)) {
            return ['code' => 443, 'msg' => '队列配置错误，请检查并重试'];
        }
        //统一数据格式
        is_array($message) && $message = json_encode($message);
        //连接中间件
        $ququeClient = new rabbitMqLib($config, $exchange, $queueName, $routeKey);
        //开始发送数据
        $res = $ququeClient->send($message);

        return $res ? ['code' => 200, 'msg' => 'success'] : ['code' => 444, 'msg' => 'fail'];
    }

    /**
     * 请求数据，并回调方法执行代码
     * @param string $queueName          队列名
     * @param string $callbackMethodName 回调方法名
     * @param object $callbackObject     回调对象
     * @return array
     */
    public function startDealMsg($queueName, $callbackMethodName, $callbackObject)
    {
        $config   = config('rabbitmq');
        $exchange = $queueName . '_exchange';
        $routeKey = $queueName . '_route';
        if (empty($config)) {
            return ['code' => 443, 'msg' => '队列配置错误，请检查并重试'];
        }
        //连接中间件
        $ququeClient = new rabbitMqLib($config, $exchange, $queueName, $routeKey);
        $result      = $ququeClient->run([$callbackObject, $callbackMethodName], false);

        die(json_encode($result));
    }

}