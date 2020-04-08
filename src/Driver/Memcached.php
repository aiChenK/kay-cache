<?php
/**
 * Created by PhpStorm.
 * User: aiChenK
 * Date: 2020-04-03
 * Time: 09:48
 */

namespace KayCache\Driver;

/**
 * KakCache\Driver\Memcached
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
 * @method \Memcached handler()
 */
class Memcached extends AbstractDriver
{
    protected $options = [
        'host'       => '127.0.0.1',
        'port'       => 11211,
        'username'   => '',
        'password'   => '',
        'lifetime'   => 0,
        'prefix'     => '',
        'timeout'    => 1,
        'persistent' => true,       // 长链接
        'servers'    => [],         // 多服务器（有此项则忽略host,port）
        'serialize'  => 'php',
        'option'     => [],
    ];

    public function __construct(array $options = [])
    {
        if (!extension_loaded('memcached')) {
            throw new \BadFunctionCallException('extension `memcached` not support');
        }
        if (!empty($options)) {
            $this->options = array_merge($this->options, $options);
        }
        parent::__construct($this->options);

        // 处理连接地址
        if (empty($this->options['servers'])) {
            $this->options['servers'][] = [
                'host'   => $this->options['host'],
                'port'   => $this->options['port'],
                'weight' => 1,
            ];
        }

        // 初始化实例
        $this->_handler = new \Memcached($this->options['persistent'] ? 'persistent' : '');
        $this->_handler->addServers($this->options['servers']);
        if (!empty($this->options['option'])) {
            $this->_handler->setOptions($this->options['option']);
        }
        if ($this->options['timeout'] > 0) {
            $this->_handler->setOption(\Memcached::OPT_CONNECT_TIMEOUT, intval($this->options['timeout'] * 1000));
        }
        if ($this->options['username'] !== '') {
            $this->_handler->setSaslAuthData($this->options['username'], $this->options['password']);
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

        return $this->_handler->set($key, $value, $ttl);
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
     * 批量获取
     *
     * @param iterable $keys
     * @param null $default
     * @return array|iterable|mixed
     */
    public function getMultiple($keys, $default = null)
    {
        $data = $this->_handler->getMulti((array) $keys);
        foreach ($keys as $key) {
            if (!array_key_exists($key, $data)) {
                $data[$key] = $default;
            }
        }
        return $data;
    }

    /**
     * 批量设置
     *
     * @param iterable $values
     * @param null $ttl
     * @return bool
     */
    public function setMultiple($values, $ttl = null)
    {
        $ttl = $this->getLifetime($ttl);
        return $this->_handler->setMulti((array) $values, $ttl);
    }

    /**
     * 批量删除
     *
     * @param iterable $keys
     * @return bool
     */
    public function deleteMultiple($keys)
    {
        $result = $this->_handler->deleteMulti((array) $keys);
        foreach ($result as $code) {
            if ($code === \Memcached::RES_FAILURE){
                return false;
            }
        }
        return true;
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
        return $this->getFilteredKeys($this->_handler->getAllKeys(), $prefix, $realKey);
    }
}