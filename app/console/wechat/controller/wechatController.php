<?php

namespace app\console\member\controller;

use app\console\baseController;

use app\lib\rabbitmq\rabbitMq;
use app\lib\rabbitmq\rabbitMqLib;
use app\common\logic\logLogic;

error_reporting(E_ALL ^ E_NOTICE);

class wechatController extends baseController
{

    /**
     * 发送消息
     * php cmd /wechat-message/send
     */
    public function sendAction()
    {
        $queueName      = 'weichat_message_push';
        $dealMethodName = 'sendWechatMessage';
        $result         = rabbitMq::startDealMsg($queueName, $dealMethodName, $this);
        die(print_r($result, 1));
    }

    /**
     * 推送到微信
     */
    public function sendWechatMessage($envelope, $queue)
    {
        $queueName  = 'weichat_message_push';
        $msg        = $envelope->getBody();
        $envelopeID = $envelope->getDeliveryTag();
        //开始处理业务
        $message = json_decode($msg, true);

        //记录入库
        try {
            //活动统计
            logLogic::log($queueName, ['进入数据', 'data' => $message]);
            //如果数据有异常则抛出队列
            if (empty($message['msg']) || empty($message['webhook_url'])) {
                logLogic::log($queueName, ['参数为空，跳过', $message]);
                $queue->ack($envelopeID);
                return ['code' => 200, 'msg' => 'success'];
            }

            $messageData = [
                'msgtype'  => 'markdown',
                'markdown' => [
                    'content'        => $message['msg'],
                    'mentioned_list' => [
                        '@all',
                    ],
                ],
            ];
            $res         = self::sendPost($message['webhook_url'], $messageData, 'json');
            logLogic::log($queueName, ['', '推送结果' => $res]);
        } catch (\Exception $e) {
            $logData = [
                '推送企业微信消息失败',
                'error_msg'  => $e->getMessage(),
                'error_code' => $e->getCode(),
                'file'       => $e->getFile(),
                'line'       => $e->getLine(),
                'queue_data' => $message,
            ];
            logLogic::log($queueName, $logData);
            //重新推送队列
            return $this->rePush($queueName, $message, $queue, $envelopeID, $e->getMessage());
        }
        //删除确认
        $queue->ack($envelopeID);

        return ['code' => 200, 'msg' => 'success'];
    }

    /**
     * POST 请求
     * @param string $url     请求链接
     * @param array  $data    携带的参数
     * @param string $method  post类型, json, jsonRaw, [default=form]
     * @param int    $timeout 超时时间
     * @return string $result    返回获取的内容
     */
    public static function sendPost($url, $data, $method = '', $headers = [], $timeout = 30)
    {
        $method  = strtolower(trim($method));
        $timeout = (int)$timeout;
        empty($timeout) && $timeout = 30;
        switch ($method) {
            case 'json':
                $postdata = json_encode($data);
                $options  = [
                    'http' => [
                        'method'  => 'POST', //注意要大写
                        'timeout' => $timeout,//单位秒
                        'header'  => empty($headers) ? 'Content-type:application/json' : array_map(function ($h, $v) {
                            return "$h: $v";
                        }, array_keys($headers), $headers),
                        'content' => $postdata,
                    ],
                ];
                break;
            case 'jsonRaw':
                $options = [
                    'http' => [
                        'method'  => 'POST', //注意要大写
                        'timeout' => $timeout,//单位秒
                        'header'  => 'Content-type:application/json',
                        'content' => $data,
                    ],
                ];
                break;
            default:
                $postdata = http_build_query($data);
                $options  = [
                    'http' => [
                        'method'  => 'POST', //注意要大写
                        'timeout' => $timeout,//单位秒
                        'header'  => empty($headers) ? 'Content-type:application/x-www-form-urlencoded' : array_map(function ($h, $v) {
                            return "$h: $v";
                        }, array_keys($headers), $headers),
                        'content' => $postdata,
                    ],
                ];
                break;
        }
        $context = stream_context_create($options);
        $result  = file_get_contents($url, false, $context);

        return $result;
    }

}
