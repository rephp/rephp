<?php

namespace app\modules\index\controller;

use app\common\baseController;
use app\modules\index\model\demoModel;
use app\modules\index\model\testModel;
use app\lib\es;

class indexController extends baseController
{
    public $layout = 'index';

    public function indexAction()
    {
        echo '====e==========2012============';
        $es = new es();
        /*$res = $es->createIndex('test2');
        var_dump($res);
        $columnList = [
            'id' => [
                'type' => 'integer',
            ],
            'title' => [
                'type'=> 'text',
            ],
            'content' => [
                'type'=> 'text',
            ],
            'tags' => [
                'type' => 'text',
                'fields' => [
                    'keyword' => [
                        'type' => 'keyword',
                        'ignore_above' => 256,
                    ],
                ],
            ],
        ];

        $res = $es->createMapping('test2', $columnList);
        var_dump($res);exit;
        $res = $es->getMapping('test2');
        print_r($res);*/
//        $columnList = [
//            'id' => [
//                'type' => 'integer', // 整型
//            ],
//            'title' => [
//                'type' => 'text', // 字符串型
//            ],
//            'content' => [
//                'type' => 'text',
//            ],
//        ];
//        $res = $es->createMapping('test' , $columnList);
//        $res = $es->addDoc('test', ['title'=>'test title', 'tags'=>'test1,test2,test3']);
//        $res2 = $es->addDoc('test', ['title'=>'aaaaa title', 'tags'=>'aaaaa tags']);
//        var_dump($res, $res2);exit;
        //$res = $es->getMapping('test');
        $query =  [
            'constant_score' => [ //非评分模式执行
                                  'filter' => [ //过滤器，不会计算相关度，速度快
                                                'term' => [ //精确查找，不支持多个条件
                                                            'title' => 'title'
                                                ]
                                  ]
            ]
        ];

        $res = $es->search('test', $query, 1, 111, ['title']);
echo '<pre>';
        print_r($res);
        exit;
        //var_dump(demoModel::db());exit;
        var_dump(get());
        $trans = demoModel::startTrans();
        try {
            demoModel::updates(['id' => 5], ['ttile' => '版本包2225']);
            demoModel::updates(['id' => 16], ['ttile' => '3333']);
            $trans->commit();
        } catch (\Exception $e) {
            $trans->rollBack();
        }

        $res  = demoModel::db()->where('id', 5)
                         ->fetch();
        $res2 = testModel::db()->where('ttile3', 'test')
                         ->all();
        $res3 = testModel::getTableName();
        //var_dump($this->model('demo')->getSql()) ;
        var_dump($res, $res2, $res3);


        return  $this->display();

    }

    public function testAction($a)
    {
        echo $uri = parse_url($_SERVER['REQUEST_URI']);
        echo '<img src="/test.jpg"><img src="/test.css"><img src="/test.ico">';
        var_dump($a);
    }

    public function test2Action()
    {
        echo 'ddddddddddddddddd';
        $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        echo '<img src="/test.jpg"><img src="/test.css"><img src="/test.ico">';
        echo '<pre>';
        var_dump($uri);
        $arr        = explode('/', $uri);
        $modules    = empty($arr[1]) ? 'index' : $this->filter($arr[1]);
        $controller = empty($arr[2]) ? 'index' : $this->filter($arr[2]) . 'Controller';
        $action     = empty($arr[3]) ? 'index' : $this->filter($arr[3]) . 'Action';

        var_dump($modules, $controller, $action);
        //var_dump(basename($uri));
    }

    /**
     * 安全过滤
     * @param string $name 节点名字
     * @return string
     */
    public function filter($name)
    {
        $res = str_replace(' ', '', $name);
        echo '<img src="/test.jpg"><img src="/test.css"><img src="/test.ico">';
        return $res;
    }
}
