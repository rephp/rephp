<?php

namespace app\console\zufang\controller;

use app\console\baseController;

class szController extends baseController
{
    public function runAction()
    {
        $file = ROOT_PATH.'runtime/data/sz.txt';
        $tip_start_time = file_exists($file) ? file_get_contents($file) : '2022-12-30 18:18:54';
        $tip_start_time = strtotime($tip_start_time);
        //过滤开始时间
        $content = $this->http_curl('http://zjj.sz.gov.cn/ztfw/zfbz/tzgg2017/index.html');
        preg_match('/<ul class=\"ftdt-list\">(.*?)<\/ul>/is', $content, $objStr);    //匹配ul标签里面的内容
        if (empty($objStr[1])) {
            $this->alert_me('sz查不到目标区域');
        }
        preg_match_all('/title="(.*?)"/is', $objStr[1], $titleArr);    //匹配li>title
        $titleArr = empty($titleArr[1]) ? [] : $titleArr[1];
        preg_match_all('/<span>(.*?)<\/span>(.*?)<\/li>/is', $objStr[1], $timeArr);
        $timeArr = empty($timeArr[1]) ? [] : $timeArr[1];
        $max_current_time = 0;
        //匹配信息，如有符合则直接推送结果提示
        foreach ($titleArr as $index=>$title) {
            if(empty($timeArr[$index])){
                continue;
            }
            //过滤开始时间
            $current_time = strtotime($timeArr[$index]);
            if ($tip_start_time >= $current_time) {
                continue;
            }
            if($max_current_time<$current_time){
                $max_current_time = $current_time;
            }
            //提醒
            $this->alert_me($title.'-'.date('Y-m-d', $current_time).'-sz');
        }
        empty($max_current_time) || file_put_contents($file, date('Y-m-d H:i:s', $max_current_time));

        exit('ok');
    }


    function alert_me($msg)
    {
        $webhook_url = 'https://qyapi.weixin.qq.com/cgi-bin/webhook/send?key=5bf6cd46-d068-4ec7-ab63-fd0eda7e02c4';
        $msg = strip_tags($msg);
        $messageData = [
            'msgtype'  => 'markdown',
            'markdown' => [
                'content'        => $msg,
                'mentioned_list' => [
                    '@all',
                ],
            ],
        ];
        return self::sendPost($webhook_url, $messageData, 'json');
    }

    function jsonp_decode($jsonp, $assoc = false)
    {
        $jsonp = trim($jsonp);
        if (isset($jsonp[0]) && $jsonp[0] !== '[' && $jsonp[0] !== '{') {
            $begin = strpos($jsonp, '(');
            if (false !== $begin) {
                $end = strrpos($jsonp, ')');
                if (false !== $end) {
                    $jsonp = substr($jsonp, $begin + 1, $end - $begin - 1);
                }
            }
        }

        return json_decode($jsonp, $assoc);
    }

    function http_curl($url, $data = [], $isPost = false, $header = [])
    {
        //初始化
        $curl = curl_init();
        if ($isPost) {
            //设置post方式提交
            curl_setopt($curl, CURLOPT_POST, 1);
            curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
        } else {
            if ($data) {
                $data = http_build_query($data);
                if (strpos($url, '?') !== false) {
                    $url = $url . '&' . $data;
                } else {
                    $url = $url . '?' . $data;
                }
            }
        }

        if ($header) {
            // 设置请求头
            curl_setopt($curl, CURLOPT_HTTPHEADER, $header);
        }
        //设置抓取的url
        curl_setopt($curl, CURLOPT_URL, $url);
        //设置头文件的信息作为数据流输出
        curl_setopt($curl, CURLOPT_HEADER, 0);
        //设置获取的信息以文件流的形式返回，而不是直接输出。
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        // 超时设置
        curl_setopt($curl, CURLOPT_TIMEOUT, 10);
        //跳过SSL证书检查
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, FALSE);
        //执行命令
        $response = curl_exec($curl);
        $result   = json_decode($response) ? json_decode($response, true) : $response;
        $error    = curl_error($curl);
        curl_close($curl);

        return $error ? $error : $result;
    }

    /**
     * POST 请求
     * @param string $url     请求链接
     * @param array  $data    携带的参数
     * @param string $method  post类型, json, jsonRaw, [default=form]
     * @param int    $timeout 超时时间
     * @return string $result    返回获取的内容
     */
    public static function sendPost($url, $data, $method = '', $headers = [], $timeout = 30)
    {
        $method  = strtolower(trim($method));
        $timeout = (int)$timeout;
        empty($timeout) && $timeout = 30;
        switch ($method) {
            case 'json':
                $postdata = json_encode($data);
                $options  = [
                    'http' => [
                        'method'  => 'POST', //注意要大写
                        'timeout' => $timeout,//单位秒
                        'header'  => empty($headers) ? 'Content-type:application/json' : array_map(function ($h, $v) {
                            return "$h: $v";
                        }, array_keys($headers), $headers),
                        'content' => $postdata,
                    ],
                ];
                break;
            case 'jsonRaw':
                $options = [
                    'http' => [
                        'method'  => 'POST', //注意要大写
                        'timeout' => $timeout,//单位秒
                        'header'  => 'Content-type:application/json',
                        'content' => $data,
                    ],
                ];
                break;
            default:
                $postdata = http_build_query($data);
                $options  = [
                    'http' => [
                        'method'  => 'POST', //注意要大写
                        'timeout' => $timeout,//单位秒
                        'header'  => empty($headers) ? 'Content-type:application/x-www-form-urlencoded' : array_map(function ($h, $v) {
                            return "$h: $v";
                        }, array_keys($headers), $headers),
                        'content' => $postdata,
                    ],
                ];
                break;
        }
        $context = stream_context_create($options);
        $result  = file_get_contents($url, false, $context);

        return $result;
    }
}















