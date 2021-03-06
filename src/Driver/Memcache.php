<?php
/**
 * Created by PhpStorm.
 * User: aiChenK
 * Date: 2020-03-31
 * Time: 14:44
 */

namespace KayCache\Driver;

/**
 * KakCache\Driver\Memcache
 *
 * // 实例化
 * $cache = new Memcache([
 *      'host'       => '127.0.0.1',
 *      'port'       => 11211,
 *      'lifetime'   => 3600,
 *      'prefix'     => 'test_'
 * ]);
 *
 * // 多服务器
 * $cache = new Memcache([
 *      'servers'  => [
 *          [
 *              'host'      => '127.0.0.1',
 *              'port'      => 11211,
 *              'weight'    => 1
 *          ],
 *          [
 *              'host'      => '127.0.0.1',
 *              'port'      => 11212,
 *              'weight'    => 2
 *          ]
 *      ]
 *      'lifetime'   => 0,
 *      'prefix'     => 'test_'
 * ]);
 *
 * @method \Memcache handler()
 */
class Memcache extends AbstractDriver
{
    protected $options = [
        'host'       => '127.0.0.1',
        'port'       => 11211,
        'lifetime'   => 0,
        'prefix'     => '',
        'timeout'    => 1,
        'persistent' => true,       // 长链接
        'servers'    => [],         // 多服务器（有此项则忽略host,port）
        'serialize'  => 'php'       // 序列化方式（php/json）
    ];

    public function __construct(array $options = [])
    {
        if (!extension_loaded('memcache')) {
            throw new \BadFunctionCallException('extension `memcache` not support');
        }
        if (!empty($options)) {
            $this->options = array_merge($this->options, $options);
        }
        parent::__construct($this->options);

        // 处理连接地址
        if (!$this->options['servers']) {
            $this->options['servers'][] = [
                'host'   => $this->options['host'],
                'port'   => $this->options['port'],
                'weight' => 1,
            ];
        }

        // 初始化实例
        $this->_handler = new \Memcache();
        foreach ($this->options['servers'] as $server) {
            $this->_handler->addServer(
                $server['host'],
                $server['port'],
                $this->options['persistent'],
                $server['weight'] ?? 1,
                $this->options['timeout']
            );
        }

        $this->setSerializer();
    }

    /**
     * 设置序列化类
     */
    private function setSerializer()
    {
        $this->initSerializer();
    }

    /**
     * 读取
     *
     * @param string $key
     * @param null $default
     * @return mixed|null
     */
    public function get($key, $default = null)
    {
        $key   = $this->getCacheKey($key);
        $value = $this->_handler->get($key);
        return $value ? $this->getUnserializeData($value) : $default;
    }

    /**
     * 写入
     *
     * @param string $key
     * @param mixed $value
     * @param null $ttl
     * @return bool
     */
    public function set($key, $value, $ttl = null)
    {
        $key   = $this->getCacheKey($key);
        $value = $this->getSerializeData($value);
        $ttl   = $this->getLifetime($ttl);

        return $this->_handler->set($key, $value, 0, $ttl);
    }

    /**
     * 删除
     *
     * @param string $key
     * @return bool
     */
    public function delete($key)
    {
        $key = $this->getCacheKey($key);
        $this->_handler->delete($key);
        return true;
    }

    /**
     * 清空
     *
     * @return bool
     */
    public function clear()
    {
        return $this->_handler->flush();
    }

    /**
     * 判断存在
     *
     * @param string $key
     * @return bool
     */
    public function has($key)
    {
        $key = $this->getCacheKey($key);
        return !!$this->_handler->get($key);
    }

    /**
     * 获取key列表
     *
     * @param string $prefix
     * @param bool $realKey     --获取真实key（否则去除全局前缀）
     * @return array
     */
    public function queryKeys($prefix = '', bool $realKey = false)
    {
        $keys = [];
        $allSlabs   = $this->_handler->getExtendedStats('slabs');
        $allSlabIds = [];
        foreach ($allSlabs as $server => $slabs) {
            if (!$slabs) {
                continue;
            }
            foreach ($slabs as $slabId => $slabInfo) {
                if (!is_numeric($slabId)) {
                    continue;
                }
                if (isset($allSlabIds[$slabId])) {
                    continue;
                }
                $allSlabIds[$slabId] = 1;
            }
        }
        $allSlabIds = array_keys($allSlabIds);
        foreach ($allSlabIds AS $slabId) {
            if (!is_numeric($slabId)) {
                continue;
            }
            $dump = $this->_handler->getExtendedStats('cachedump', (int) $slabId);
            foreach ($dump AS $arrVal) {
                if (!$arrVal) {
                    continue;
                }
                foreach ($arrVal AS $k => $v) {
                    $keys[] = trim($k);
                }
            }
        }
        return $this->getFilteredKeys($keys, $prefix, $realKey);
    }
}