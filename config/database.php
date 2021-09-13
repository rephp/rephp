<?php
return [
    'default' => [
        [
            'host'       => '127.0.0.1',
            'port'       => 3306,
            'username'   => 'root',
            'password'   => '123456',
            'database'   => 'test',
            'charset'    => 'utf8',
            'presistent' => false,
            'type'       => 'master',
        ],
        [
            'host'       => '127.0.0.1',
            'port'       => 3306,
            'username'   => 'root',
            'password'   => '123456',
            'database'   => 'test',
            'charset'    => 'utf8',
            'presistent' => false,
            'type'       => 'slave',
        ],
    ],
    'log'     => [
        [
            'host'       => '127.0.0.1',
            'port'       => 3306,
            'username'   => 'root',
            'password'   => '123456',
            'database'   => 'log_db',
            'charset'    => 'utf8',
            'presistent' => false,
            'type'       => 'master',
        ],
        [
            'host'       => '127.0.0.1',
            'port'       => 3306,
            'username'   => 'root',
            'password'   => '123456',
            'database'   => 'log_db',
            'charset'    => 'utf8',
            'presistent' => false,
            'type'       => 'slave',
        ],
    ],
];