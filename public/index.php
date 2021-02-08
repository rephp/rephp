<?php
use rephp\framework\app;
//加载composer自动加载类
require dirname(__DIR__) . '/vendor/autoload.php';
//设置app运行目录
$appPath = dirname(__DIR__).'app/';
//运行框架
(new app($appPath))->run();
