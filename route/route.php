<?php
use \NoahBuscher\Macaw\Macaw;

Macaw::get('', 'app\\modules\\index\\controller\\testController@testAction');

Macaw::get('test/(:num)', 'app\\modules\\index\\controller\\testController@test2Action');

Macaw::$error_callback = function() {
    throw new Exception("路由无匹配项 404 Not Found");
};

Macaw::dispatch();