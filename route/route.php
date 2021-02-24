<?php
use rephp\framework\component\route\route;


route::get('index/test(:all)', 'app\\modules\\index\\controller\\testController@testAction');
route::get('index/test/test2(:all)', 'app\\modules\\index\\controller\\testController@testAction');
