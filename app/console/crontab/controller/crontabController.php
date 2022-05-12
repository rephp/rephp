<?php
namespace app\console\crontab\controller;

use rephp\crontab\client;

class crontabController
{
    public function runAction()
    {
        $cacheKey    = 'crontab';
        $redis       = $this->redis;
        $allTaskList = $redis->hvals($cacheKey);
        $taskList    = [];
        foreach ($allTaskList as $json) {
            $task = json_decode($json, true);
            if (empty($task['status'])) {
                continue;
            }
            $taskList[] = $task;
        }

        $client = new Client('/usr/bin/php /data/www-data/rephp/cmd', '/data/logs/');
        $res    = $client->add($taskList)->run();
        print_r($res);
        exit("\n" . '执行完毕');
    }
}
