<?php

namespace app\lib;

use Elasticsearch\ClientBuilder;

/**
 * elasticsearch搜索引擎封装类
 * @package app\lib
 */
class es
{
    protected $client;
    protected $lastConnectTime = 0;
    protected $timeout         = 30;
    protected $connectTimeout  = 1800;

    /**
     * 获取es客户端
     * @return \Elasticsearch\Client
     */
    protected function getClient()
    {
        $currentTime = time();
        $expireTime  = $this->lastConnectTime + $this->connectTimeout;
        if ($currentTime > $expireTime) {
            $config   = config('es', []);
            $port     = empty($config['port']) ? 9200 : $config['port'];
            $host     = empty($config['host']) ? '127.0.0.1' : $config['host'];
            $username = empty($config['username']) ? '' : $config['username'];
            $password = empty($config['password']) ? '' : $config['password'];
            //拼接连接字符串
            $preStr     = empty($username) ? '' : 'http://' . $username . ':' . $password . '@';
            $connectArr = [];
            is_array($host) || $host = [$host];
            foreach ($host as $hostAddress) {
                $connectArr[] = $preStr . $hostAddress . ':' . $port;
            }
            $this->client          = ClientBuilder::create()->setHosts($connectArr)->build();
            $this->lastConnectTime = time();
            $this->timeout         = empty($config['timeout']) ? 30 : $config['timeout'];
            $this->connectTimeout  = empty($config['connect_timeout']) ? 1800 : $config['connect_timeout'];
        }

        return $this->client;
    }

    /**
     * 创建索引
     * @param string $indexName   index的名字不能是大写和下划线开头
     * @param int    $shardsNum   碎片数量
     * @param int    $replicasNum 副本数量
     * @return array
     */
    public function createIndex($indexName, $shardsNum = 5, $replicasNum = 0)
    {
        $params = [
            'index'   => $indexName, //index的名字不能是大写和下划线开头
            'timeout' => $this->timeout,
            'body'    => [
                'settings' => [
                    'number_of_shards'   => $shardsNum,
                    'number_of_replicas' => $replicasNum,
                ],
            ],
        ];
        return $this->getClient()->indices()->create($params);
    }

    /**
     * 检查Index是否存在
     * @param string $indexName 索引名字
     * @return bool
     */
    public function checkIndexExists($indexName)
    {
        $params = [
            'index' => $indexName,
        ];
        return $this->getClient()->indices()->exists($params);
    }

    /**
     * 删除一个Index
     * @param string $indexName 索引名字
     * @return boolean
     */
    public function delIndex($indexName)
    {
        $params = [
            'index'              => $indexName,
            'timeout'            => $this->timeout,
            'ignore_unavailable' => true,
        ];
        if ($this->checkIndexExists($indexName)) {
            $res = $this->getClient()->indices()->delete($params);
            return $res;
        }

        return true;
    }


    /**
     * 创建索引内字段映射
     * @param string $indexName        索引名字
     * @param array  $columnList       字段配置列表,元数据字段包括： _index, _type, _id, _source.
     *                                 基本数据类型: string, long, boolean, ip
     *                                 JSON 分层数据类型: object, nested(嵌套)
     *                                 特殊类型: geo_point, geo_shape, completion
     * @param array  $source           文档的原始 JSON 信息定义,如果禁用 _source.enabled将会有一些其它影响，比如：update API 将无法使用等等。
     * @return array
     */
    public function createMapping($indexName, $columnList, $source = ['enabled' => true])
    {
        $params = [
            'index'   => $indexName,
            'timeout' => $this->timeout,
            'body'    => [
                'mytype' => [
                    '_source'    => $source,
                    'properties' => $columnList,
                ],
            ],
        ];
        $this->getClient()->indices()->putMapping($params);
    }

    /**
     * 获取Index的映射信息
     * @param string $indexName 索引名字
     * @return array
     */
    public function getMapping($indexName)
    {
        $params = [
            'index' => $indexName,
        ];
        return $this->getClient()->indices()->getMapping($params);
    }

    /**
     * 创建或者修改文档
     * @param string $indexName 索引名字
     * @param array  $data      数据内容
     * @param int    $id        可以手动指定id，也可以不指定随机生成
     * @return array
     */
    public function addDoc($indexName, array $data, $id = 0)
    {
        $params = [
            'index'   => $indexName,
            'timeout' => $this->timeout,
            'body'    => $data,
        ];
        //可以手动指定id，也可以不指定随机生成
        empty($id) || $params['id'] = $id;
        return $this->getClient()->index($params);
    }

    /**
     * 删除一个文档
     * @param string $indexName 索引名字
     * @param int    $id        文档id
     * @return array
     */
    public function delDoc($indexName, $id)
    {
        $params = [
            'index'   => $indexName,
            'timeout' => $this->timeout,
            'id'      => $id,
        ];
        return $this->getClient()->delete($params);
    }

    /**
     * 更新一个文档内容
     * @param string $indexName 索引名字
     * @param int    $id        文档id
     * @param array  $data      文档内容
     * @return array|callable
     */
    public function updateDoc($indexName, $id, array $data)
    {
        $params = [
            'index'   => $indexName,
            'timeout' => $this->timeout,
            'id'      => $id,
            'body'    => [
                'doc' => $data,
            ],
        ];
        return $this->getClient()->update($params);
    }

    /**
     * 根据id查询一条数据内容
     * @param string $indexName 索引名字
     * @param int    $id        文档id
     * @return array
     */
    public function getDoc($indexName, $id)
    {
        $params = [
            'index' => $indexName,
            'id'    => $id,
        ];
        return $this->getClient()->get($params);
    }

    /**
     * 根据id查询一批数据内容
     * @param string $indexName 索引名字
     * @param array  $idArr     文档id列表
     * @return array
     */
    public function getDocs($indexName, $idArr)
    {
        $params = [
            'index' => $indexName,
            'body'  => ['ids' => (is_array($idArr) ? $idArr : [$idArr])],
        ];
        return $this->getClient()->mget($params);
    }

    public function search($indexName, $keyword, $page = 1, $size = 20, $highlightColumnArr = [], $highlightPreTags = '', $highlightPostTags = '')
    {
        //偏移量
        $size = (int)$size;
        $page = (int)$page;
        empty($size) && $size = config('es.size', 50);
        empty($page) && $page = 1;
        $offset = ($page - 1) * $size;
        /*处理高亮start*/
        $highlightConfig = [];
        if (!empty($highlightColumnArr)) {
            is_array($highlightColumnArr) || $highlightColumnArr = [$highlightColumnArr];
            $highlightConfig['pre_tags']  = empty($highlightPreTags) ? ['<em style="color: #ff0000;">'] : [$highlightPreTags];
            $highlightConfig['post_tags'] = empty($highlightPostTags) ? ['</em>'] : [$highlightPostTags];
            foreach ($highlightColumnArr as $highlightColumnName) {
                $highlightConfig['fields'][$highlightColumnName] = new \stdClass();
            }
        }
        /*处理高亮end*/

        $params = [
            'index'   => $indexName,
            'timeout' => $this->timeout,
            'size'    => $size,
            'from'    => $offset,
            'body'    => [
                //查询内容
                'query'     => [
                    'match' => [//匹配
                                'goods_name' => $word//匹配字段
                    ],
                ],
                'highlight' => $highlightConfig,
            ],
        ];

        return $this->getClient()->search($params);
    }
}
