<?php
namespace app\modules\index\controller;

use app\modules\index\baseController;
use app\modules\index\model\demoModel;
use app\modules\index\model\testModel;

class indexController extends baseController
{

    public $layout = 'index';

    public function indexAction()
    {
        echo '==========================';
        //var_dump(demoModel::db());exit;
        $res = demoModel::db()->where('ttile', '版本包')
                              ->fetch();
        $res2 = testModel::db()->where('ttile3', 'test')
                        ->all();
        //var_dump($this->model('demo')->getSql()) ;
        var_dump($res, $res2);
        exit;

        $res =  $this->display();
        var_dump($res);
    }
    public function testAction($a)
    {
        echo $uri    = parse_url($_SERVER['REQUEST_URI']);
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
        $arr = explode('/', $uri);
        $modules    = empty($arr[1]) ? 'index' : $this->filter($arr[1]);
        $controller = empty($arr[2]) ? 'index' : $this->filter($arr[2]).'Controller';
        $action     = empty($arr[3]) ? 'index' : $this->filter($arr[3]).'Action';

        var_dump($modules, $controller, $action);
         //var_dump(basename($uri));
    }

    /**
     * 安全过滤
     * @param  string  $name 节点名字
     * @return string
     */
    public function filter($name)
    {
        $res = str_replace(' ', '', $name);
        echo '<img src="/test.jpg"><img src="/test.css"><img src="/test.ico">';
        return $res;
    }
}
