<?php
return [
    'test' => ['method'=>'get', 'class'=>'app\\console\\test\\controller\\testController@runAction', 'desc'=>'tttt test'],
    'test3' => ['method'=>'get', 'class'=>'app\\console\\member\\controller\\testController@test2Action', 'desc'=>'tttt test'],
    'index/test/test4' => ['method'=>'get', 'desc'=>'tttt test'],
    'index/test/test2' => ['method'=>'get', 'class'=> 'app\\modules\\index\\controller\\testController@testAction', 'desc'=>'tttt test'],
];
