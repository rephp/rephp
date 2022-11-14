<?php

namespace app\lib;

use Elasticsearch\ClientBuilder;

/**
 * elasticsearch搜索引擎封装类
 * @package app\lib
 */
class es
{
    protected $config;
    protected $client;

    /**
     * 初始化es对象
     * es constructor.
     * @param array $config es配置
     * @return void
     */
    public function __construct(array $config)
    {
        $this->config = $config;
        $port         = empty($config['port']) ? 9200 : $config['port'];
        $host         = empty($config['host']) ? '127.0.0.1' : $config['host'];
        $username     = empty($config['username']) ? '' : $config['username'];
        $password     = empty($config['password']) ? '' : $config['password'];
        //拼接连接字符串
        $connectionStr = empty($username) ? '' : 'http://' . $username . ':' . $password . '@';
        $connectionStr .= $host . ':' . $port;
        $this->client  = ClientBuilder::create()->setHosts([$connectionStr])->build();
    }

    /**
     * 创建索引
     * @param     $indexName   index的名字不能是大写和下划线开头
     * @param int $shardsNum   碎片数量
     * @param int $replicasNum 副本数量
     * @return array
     */
    public function createIndex($indexName, $shardsNum = 5, $replicasNum = 0)
    {
        $params = [
            'index' => $indexName, //index的名字不能是大写和下划线开头
            'body'  => [
                'settings' => [
                    'number_of_shards'   => $shardsNum,
                    'number_of_replicas' => $replicasNum,
                ],
            ],
        ];
        return $this->client->indices()->create($params);
    }

    /**
     * 创建索引内字段映射
     * @param string $indexName        索引名字
     * @param array  $columnList       字段配置列表,元数据字段包括： _index, _type, _id, _source.
     *                                 基本数据类型: string, long, boolean, ip
     *                                 JSON 分层数据类型: object, nested(嵌套)
     *                                 特殊类型: geo_point, geo_shape, completion
     * @param string $type             映射类型
     * @param array  $source           文档的原始 JSON 信息定义,如果禁用 _source.enabled将会有一些其它影响，比如：update API 将无法使用等等。
     * @return array
     */
    public function createMapping($indexName, $columnList, $type = 'autofelix_table', $source = ['enabled' => true])
    {
        $params = [
            'index' => $indexName,
            'type'  => $type,
            'body'  => [
                'mytype' => [
                    '_source'    => $source,
                    'properties' => $columnList,
                ],
            ],
        ];
        $this->client->indices()->putMapping($params);
    }

    /**
     * 添加文档
     * @param string $indexName 索引名字
     * @param array  $data      数据内容
     * @param string $type      类型
     * @param int    $id        可以手动指定id，也可以不指定随机生成
     * @return array
     */
    public function addDoc($indexName, array $data, $type = 'autofelix_table', $id = 0)
    {
        $params = [
            'index' => $indexName,
            'type'  => $type,
            'body'  => $data,
        ];
        //可以手动指定id，也可以不指定随机生成
        empty($id) || $params['id'] = $id;
        return $this->client->index($params);
    }

    /**
     * 根据id查询一条数据内容
     * @param string $indexName 索引名字
     * @param int    $id        你插入数据时候的id
     * @param string $type
     * @return array
     */
    public function getDetail($indexName, $id, $type = 'autofelix_table')
    {
        $params = [
            'index' => $indexName,
            'type'  => $type,
            'id'    => $id,
        ];
        return $this->client->get($params);
    }

    public function search($indexName, $type='autofelix_table')
    {
        $params = [
            'index' => $indexName,
            'type'  => $type,
            'body'  => [
                'query' => [
                    'constant_score' => [ //非评分模式执行
                                          'filter' => [ //过滤器，不会计算相关度，速度快
                                                        'term' => [ //精确查找，不支持多个条件
                                                                    'first_name' => '飞',
                                                        ],
                                          ],
                    ],
                ],
            ],
        ];

        return $this->client->search($params);
    }
}
