<?php
namespace app\modules\index\controller;


use app\modules\index\logic\testLogic;
use rephp\component\container\container;

class testController{

    public function indexAction()
    {
        echo 'xxxxxxxxxxxxxxxxxxxxxxxxxxx';
    }
    public function testAction($a){
        echo $uri    = parse_url($_SERVER['REQUEST_URI']);
        var_dump($a);
        echo '======================';
    }

    public function test2Action($a){
        $env = env('CONFIG.CONFIG_PATH2');
        var_dump($env);
            $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
            echo '<pre>';
        var_dump($uri);
        $arr = explode('/', $uri);
        $modules    = empty($arr[1]) ? 'index' : $this->filter($arr[1]);
        $controller = empty($arr[2]) ? 'index' : $this->filter($arr[2]).'Controller';
        $action     = empty($arr[3]) ? 'index' : $this->filter($arr[3]).'Action';

        var_dump($modules,$controller,$action);

        $get = container::getContainer()->get('request')->get;
         echo '<pre>';print_r($get);

    }

    /**
     * 安全过滤
     * @param  string  $name 节点名字
     * @return string
     */
    public function filter($name)
    {
        $res = str_replace(' ', '', $name);
        return $res;
    }
}