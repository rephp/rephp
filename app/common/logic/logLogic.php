<?php

namespace app\common\logic;

use rephp\core\logic;
use app\common\model\logModel;

class logLogic extends logic
{
    /**
     * 日志写入数据库
     * @param string $file_name 文件名
     * @param array  $data      数据
     */
    public static function log($file_name, $data)
    {
        is_array($data) && $data = print_r($data, 1);
        try{
            $model       = new logModel();
            $insert_data = [
                'category' => $file_name,
                'level'    => 1,
                'message'  => $data,
                'log_time' => time(),
                'uid'      => 0,
                'logtype'  => 'Exception',
            ];
            $res = $model->insert($insert_data);
        }catch (\Exception $e){
            //自动重连机制
            dbLogic::checkReconnect($e, $model);

            //再重试一次，仍然不能写入日志则抛日志入队列
            try {
                $res = $model->insert($insert_data);
            }catch (\Exception $e) {
                //自动重连机制
                dbLogic::checkReconnect($e, $model);
                //抛日志入队列
                $rabbitMq = new RabbitMq();
                $push_res = $rabbitMq->send('log_queue', $insert_data);
                $res = $push_res['code']==200 ? true : false;
            }
        }

        return $res;
    }

}
