<?php
namespace app\modules\index\controller;


use app\modules\index\logic\testLogic;

class testController{

    public function testAction($a){
        echo $uri    = parse_url($_SERVER['REQUEST_URI']);
        var_dump($a);
    }

    public function test2Action($a){
            $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
            echo '<pre>';
        var_dump($uri);
        $arr = explode('/', $uri);
        $modules    = empty($arr[1]) ? 'index' : $this->filter($arr[1]);
        $controller = empty($arr[2]) ? 'index' : $this->filter($arr[2]).'Controller';
        $action     = empty($arr[3]) ? 'index' : $this->filter($arr[3]).'Action';

        var_dump($modules,$controller,$action);
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
        return $res;
    }
}