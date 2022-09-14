<?php

namespace app\lib;

/**
 * 自定义lib必须使用app\lib命名空间，然后在modules中可使用$this->lib('demo')->test();调用
 * @package app\lib
 */
class demo
{
    public function test()
    {
        return true;
    }
}
