<?php

namespace app\lib\rabbitmq;

use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

/**
 * Class RabbitMq
 * @package common\utils\sync
 */
class RabbitMq
{
    /**
     * 引入日志操作方法
     */
    use logTrait;

    /**
     * 推送数据
     * @param string $queueName 队列名字
     * @param mixed  $message   消息内容
     * @return array
     */
    public static function send($queueName, $message = '', $config = [])
    {
        if (empty($queueName)) {
            return ['code' => 445, 'msg' => '队列名不能为空'];
        }
        empty($config) && $config   = config('rabbitmq');
        if (empty($config)) {
            return ['code' => 443, 'msg' => '队列配置错误，请检查并重试'];
        }
        $exchange = $queueName;
        $routeKey = $queueName;
        //统一数据格式
        is_array($message) && $message = json_encode($message);
        //连接中间件
        $ququeClient = new RabbitMQCommand($config, $exchange, $queueName, $routeKey);
        //开始发送数据
        $res = $ququeClient->send($message);

        return $res ? ['code' => 200, 'msg' => 'success'] : ['code' => 444, 'msg' => 'fail'];
    }

    /**
     * 推送领券卡券详情到分站
     * @param string $queueName      队列名
     * @param string $dealMethodName 消费类的处理方法名
     * @return array|bool
     * @example php yii url
     */
    public static function get($queueName, $dealMethodName, $config = [])
    {
        if (empty($queueName) || empty($classObj) || empty($dealMethodName)) {
            return false;
        }
        empty($config) && $config   = config('rabbitmq');
        $exchange = $queueName;
        $routeKey = $queueName;
        if (empty($config) || empty($exchange) || empty($routeKey)) {
            return ['code' => 443, 'msg' => '队列配置错误，请检查并重试'];
        }
        //连接中间件
        $ququeClient    = new RabbitMQCommand($config, $exchange, $queueName, $routeKey);
        $calldClassName = get_called_class();
        $classObj       = new $calldClassName();
        $result         = $ququeClient->run([$classObj, $dealMethodName], false);

        die(json_encode($result));
    }

}