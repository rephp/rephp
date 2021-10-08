<?php
namespace app\modules\index\model;

use \rephp\core\model;

class testModel extends model
{
    //protected $db = 'default';
    protected $table = 'link_cp';

    public function test()
    {
        return 'bbbb';
    }
}