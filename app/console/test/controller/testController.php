<?php

namespace app\console\test\controller;

use rephp\crontab\client;
use app\console\baseController;

class testController extends baseController
{
    public function runAction()
    {
        echo '==2============2012============';
        $res = $this->lib('es')->checkIndexExists('test');
        var_dump($res);
        exit;
    }
}
