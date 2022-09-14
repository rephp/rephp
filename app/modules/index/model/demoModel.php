<?php

namespace app\modules\index\model;

use rephp\core\model;

class demoModel extends model
{
    protected static $db    = 'default';
    protected static $table = 'test';

    public function test()
    {
        return 'bbbb';
    }
}
