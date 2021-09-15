<?php
namespace app\modules\index\model;

use \rephp\core\model;

class demoModel extends model
{
    //protected $db = 'default';
    protected $table = 'test';

    public function test()
    {
        return 'bbbb';
    }
}