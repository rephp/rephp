<?php
use \NoahBuscher\Macaw\Macaw;

Macaw::get('', 'app\\modules\\index\\controller\\testController@testAction');

Macaw::get('test/(:num)', 'app\\modules\\index\\controller\\testController@test2Action');
Macaw::dispatch();