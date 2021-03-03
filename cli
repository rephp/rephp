#!/usr/bin/env php
<?php
use rephp\app;
$uri = $argv[1];
in_array(substr($uri,0,1), ['/', '\\']) || $uri = '/'.$uri;
define('CLI_URI', $uri);
$_SERVER['REQUEST_URI']    = $uri;
$_SERVER['REQUEST_METHOD'] = 'GET';

//加载composer自动加载类
require __DIR__ . '/vendor/autoload.php';

//设置app运行目录
$appPath = __DIR__.'/app/';
//运行
(new app())->run($appPath);
