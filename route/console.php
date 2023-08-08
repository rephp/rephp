<?php
return [
    'zufang/baoan'=> ['method'=>'get', 'class'=>'app\\console\\zufang\\controller\\baoanController@runAction', 'desc'=>'宝安租房'],
    'zufang/lg'=> ['method'=>'get', 'class'=>'app\\console\\zufang\\controller\\lgController@runAction', 'desc'=>'龙岗租房'],
    'zufang/city'=> ['method'=>'get', 'class'=>'app\\console\\zufang\\controller\\cityController@runAction', 'desc'=>'深圳市租房'],
    'zufang/sz'=> ['method'=>'get', 'class'=>'app\\console\\zufang\\controller\\szController@runAction', 'desc'=>'深圳市租房2'],
    'test' => ['method'=>'get', 'class'=>'app\\console\\test\\controller\\testController@runAction', 'desc'=>'tttt test'],
    //'test3' => ['method'=>'get', 'class'=>'app\\console\\memeber\\controller\\testController@test2Action', 'desc'=>'tttt test'],
    'index/test/test4' => ['method'=>'get', 'desc'=>'tttt test'],
    'index/test/test2' => ['method'=>'get', 'class'=> 'app\\modules\\index\\controller\\testController@testAction', 'desc'=>'tttt test'],
];
