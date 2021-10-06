<?php
namespace app\modules\index\controller;


use app\modules\index\logic\testLogic;
use rephp\component\container\container;

class testController{

    public function indexAction()
    {
        echo 'xxx-xxxxxxxxxxxxxxxxxxxxxxxx---';
    }
    public function testAction($a){
         $uri    = parse_url($_SERVER['REQUEST_URI']);
         var_dump($uri);
        var_dump($a);
        echo '======================';
    }

    public function test2Action(){
        echo 'ddddddddddddddddd';
        $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        echo '<img src="/static/test.jpg"><img src="/static/test.css"><img src="/test.ico">';
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