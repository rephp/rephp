<?php
use rephp\component\route\route;


route::get('index/test/test3(:all)', 'app\\modules\\index\\controller\\testController@test2Action');
//route::get('index/test2(:all)', 'app\\modules\\index\\controller\\testController@testAction');