<?php
namespace app\console;

use \rephp\core\controller;
set_time_limit(0);
ini_set('default_socket_timeout', 60);
/**
 * 基类
 * @package app\modules\index
 */
class baseController extends controller
{

    /**
     * 输出json
     * @param mixed $data  要输出的数据
     * @return void
     */
    public function json($data)
    {
        exit(json_encode( $data, 448 ));
    }

}
