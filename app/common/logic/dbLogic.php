<?php
/**
 * 检查数据库连接状态
 */

namespace app\common\logic;

use rephp\core\logic;

class dbLogic extends logic
{
    /**
     * 自动重连尝试
     * @param $e
     * @throws \Exception
     */
    public static function checkReconnect($e, &$model)
    {
        $offset_1         = stripos($e->getMessage(), 'MySQL server has gone away');
        $offset_2         = stripos($e->getMessage(), 'Lost connection to MySQL server');
        $offset_3         = stripos($e->getMessage(), 'Error while sending QUERY packet');
        $mysql_error_list = [
            2006,//MySQL server has gone away
            2013,//Lost connection to MySQL server
            1040,//已到达数据库的最大连接数，请加大数据库可用连接数
            1043,//无效连接
            1081,//不能建立Socket连接
            1158,//网络错误，出现读错误，请检查网络连接状况
            1159,//网络错误，读超时，请检查网络连接状况
            1160,//网络错误，出现写错误，请检查网络连接状况
            1161,//网络错误，写超时，请检查网络连接状况
            1203,//当前用户和数据库建立的连接已到达数据库的最大连接数，请增大可用的数据库连接数或重启数据库
            1205,//加锁超时
        ];
        $error_code = empty($e->errorInfo[1]) ? 0 : $e->errorInfo[1];
        if ( ($offset_1 !== false) || ($offset_2 !== false) || ($offset_3 !== false) || in_array($error_code, $mysql_error_list) ) {
            $model->reConnect();
            $model->queryRaw('SET SESSION wait_timeout=86400');
        }
    }

}