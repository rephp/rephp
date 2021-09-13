<?php
namespace app\modules\index\model;

use \rephp\core\model;

class demoModel extends model
{
    public $table = 'test';

    public function test()
    {
        return 'bbbb';
    }
}