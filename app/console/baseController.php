<?php

namespace app\console;

use rephp\core\controller;
use app\common\logic\logLogic;

set_time_limit(0);
ini_set('default_socket_timeout', 60);

/**
 * 基类
 * @package app\modules\index
 */
class baseController extends controller
{
    /**
     * 重新推送到队列
     */
    public function rePush($queueName, $message, $queue, $envelopeID, $errorMsg = '----error-------')
    {
        //删除老数据
        $queue->ack($envelopeID);
        $message['__fail_number__'] = empty($message['__fail_number__']) ? 1 : (int)$message['__fail_number__'] + 1;
        $retryNum                   = (int)config('rabbitmq_config.retry_num');
        empty($retryNum) && $retryNum = 5;
        if ($message['__fail_number__'] < $retryNum) {
            sleep(mt_rand(2, 4));
            $rabbitMq = new RabbitMq();
            $res      = $rabbitMq->send($queueName, $message);
            logLogic::log($queueName, ['处理失败', 'msg' => $message, 'error_msg' => $errorMsg]);
        } else {
            logLogic::log($queueName . '_throw', ['失败超过5次，抛出队列', 'msg' => $message, 'error_msg' => $errorMsg]);
        }

        return true;
    }

}
