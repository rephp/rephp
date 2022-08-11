<?php
namespace app\common;

use \rephp\core\controller;

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
