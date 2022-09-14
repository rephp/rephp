<?php

namespace app\modules\index\model;

use rephp\core\model;

class testModel extends model
{
    //protected static $db = 'default';
    protected static $table = 'link_cp';

    public function test()
    {
        return 'bbbb';
    }
}
