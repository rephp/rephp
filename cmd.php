#!/usr/bin/env php
<?php
namespace xy;

//加载compose自动加载类
require __DIR__ . '/vendor/autoload.php';

//启动
(new app(__DIR__))->run('cmd');