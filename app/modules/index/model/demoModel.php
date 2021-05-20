<?php
namespace app\modules\index\model;

use \rephp\core\model;

class demoModel extends model
{
    public $table = 'test2';

    public function test()
    {
        return 'bbbb';
    }
}